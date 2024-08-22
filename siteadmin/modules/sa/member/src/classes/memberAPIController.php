<?php
namespace sa\member;

use sa\api\api;
use \sacore\application\app;
use \sacore\application\controller;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;

use sa\files\saImage;
use sacore\utilities\doctrineUtils;

/**
 * @deprecated 
 * Class memberController
 * @package sa\member
 */
class memberAPIController extends controller
{
    /**
     * Check if a user is logged in. True for yes, False for no
     *
     * @param new api $api
     */
    public function isLoggedIn($api)
    {
        /** @var auth $auth */
        $auth = ioc::staticResolve('auth');
        $auth = $auth::getInstance();

        if ($auth->isAuthenticated()) {

            $api->response( 200, true );
        }
        else {

            $api->response( 200, false );

        }
    }

    public function login (api $api)
    {
        $auth = ioc::staticResolve('auth');
        /** @var auth $auth */
        $auth = $auth::getInstance();

        $postData = $api->getJsonPostDataAsArray ();

        // Prefer Username & Password authentication method if this data is supplied, otherwise if login
        // key is supplied, attempt to authenticate member
        if(!empty($postData['login_key']) && !isset($postData['username']) && !isset($postData['password'])) {
            /** @var saMemberUsers $user */
            $user = $auth->logon(null, null, $postData['login_key'], 'api');

            if($user) {
                /** @var saMember $member */
                $member = $auth->getAuthMember();
                $retAr = $api->bldSuccessArray();
                $retAr['member'] = $member->memberAsArray();
                $retAr['session_key'] = session_id();
                $retAr['groups'] = doctrineUtils::getEntityCollectionArray($member->getGroups());

                $retAr['member']['addresses'] = array();
                $retAr['member']['users'] = array();
                $retAr['member']['emails'] = array();
                $retAr['member']['phones'] = array();

                if($member->getAddresses()) {
                    $retAr['member']['addresses'] = doctrineUtils::getEntityCollectionArray($member->getAddresses());
                }
                
                if($member->getPhones()) {
                    $retAr['member']['phones'] = doctrineUtils::getEntityCollectionArray($member->getPhones());
                }

                if($member->getUsers()) {
                    /** @var saMemberUsers $user */
                    foreach($member->getUsers() as $user) {
                        $tmp = array();

                        $tmp['id'] = $user->getId();
                        $tmp['username'] = $user->getUsername();
                        $tmp['is_primary'] = $user->getIsPrimaryUser();
                        $tmp['is_active'] = $user->getIsActive();

                        $retAr['member']['users'][] = $tmp;
                    }
                }

                if($member->getEmails()) {
                    $retAr['member']['emails'] = doctrineUtils::getEntityCollectionArray($member->getEmails());
                }

                $api->response(200, $retAr);
            } else {
                $api->response(401, $api->bldErrorArray("Not Authenticated"));
            }
        }

        if (! isset ($postData['username'])) {
            $api->response (401, $api->bldErrorArray ("missing param (1)"));
            return;
        }
        if (! isset ($postData['password'])) {
            $api->response (401, $api->bldErrorArray ("missing param (2)"));
            return;
        }

        $result = $auth->logon($postData['username'], $postData['password']);

        if (! $result) {
            $api->response (401, $api->bldErrorArray ("Invalid User Name or Password"));
            return;
        }

        $member = modRequest::request('auth.member', false);
        $user = modRequest::request('auth.user', false);

        /** @var saMemberLoginKey $loginKey */
        $loginKey = ioc::getRepository('saMemberLoginKey')->findOneBy(array(
            'user' => $user, 
            'revoked' => false, 
            'type' => 'api'
        ));

        if(!$loginKey) {
            $loginKey = ioc::resolve('saMemberLoginKey');
            $loginKey->setUser($user);
            $loginKey->setType('api');
            $loginKey->setRevoked(false);
            $user->addLoginKey($loginKey);
            $auth->setLoginKey( $loginKey->getUuid() );
            app::$entityManager->persist($loginKey);
            app::$entityManager->flush($loginKey);
        }

        if (isset ($postData['token'])  &&  isset ($postData['service'])) {
            $this->tokenUpdate ($member, $postData['token'], $postData['service']) ;
        }

        app::$entityManager->flush();

        $retAr = $api->bldSuccessArray();
        $retAr['login_key'] = $loginKey->getUuid();
//    $retAr['member'] = doctrineUtils::convertEntityToArray($member);
        $retAr['member'] = $member->memberAsArray ();
        $retAr['session_key'] = session_id();
        $retAr['groups'] = doctrineUtils::getEntityCollectionArray($member->getGroups());
        $retAr['member']['addresses'] = array();
        $retAr['member']['users'] = array();
        $retAr['member']['emails'] = array();

        if($member->getAddresses()) {
            $retAr['member']['addresses'] = doctrineUtils::getEntityCollectionArray($member->getAddresses());
        }

        if($member->getUsers()) {
            /** @var saMemberUsers $user */
            foreach($member->getUsers() as $user) {
                $tmp = array();

                $tmp['id'] = $user->getId();
                $tmp['username'] = $user->getUsername();
                $tmp['is_primary'] = $user->getIsPrimaryUser();
                $tmp['is_active'] = $user->getIsActive();

                $retAr['member']['users'][] = $tmp;
            }
        }

        if($member->getEmails()) {
            $retAr['member']['emails'] = doctrineUtils::getEntityCollectionArray($member->getEmails());
        }

        $api->response(200, $retAr);
    }


    /**
     * @param new api $api
     */
    public function logoff ($api)
    {
        /** @var api $api */
        /** @var auth $auth */
        $auth = ioc::staticResolve('auth');
        $auth::getInstance()->logoff();
        $api->response(200, $api->bldSuccessArray() );
    }

    /**
     * @param new api $api
     * @return null
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    protected function innerAuthenticate ($api)
    {
        /** @var api $api */
        $me = modRequest::request('auth.member', false);
        
        if (!$me) {
            $api->response(401, $api->bldErrorArray ('not logged in'));
            return null;
        }
        
        return $me;
    }

    /**
     * @param new api $api
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    public function getMyProfile ($api)
    {
        $me = $this->innerAuthenticate ($api);
        if ($me === null)
            return;

        $this->addProfileToApi ($api, $me);
    }

    /**
     * @param new api $api
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    public function getProfile ($api, $member_id)
    {
        $me = $this->innerAuthenticate ($api);
        if ($me === null)
            return;

        $theMember = app::$entityManager->getRepository( ioc::staticResolve('saMember') )->findOneBy( array('id'=> $member_id));
        if (! $theMember) {
            $api->response (200, $api->bldErrorArray ("Member not found"));
            return;
        }
        $this->addProfileToApi ($api, $theMember);
    }

    /**
     * @param new api $api
     * @throws Exception
     */
    protected function addProfileToApi ($api, $theMember)
    {
        $retAr = $api->bldSuccessArray ();
        $retAr['profile'] = doctrineUtils::convertEntityToArray ($theMember);
        $api->response(200, $retAr);
    }

    /**
     * @param new api $api
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    public function getMyProfileImage ($api)
    {
        /** @var saMember $me */
        $me = modRequest::request('auth.member', false);
        
        if (!$me) {
            header('Content-Type:text/plain');
            echo ("No images available.");
            return;
        }

        /** @var saImage $img */
        $img = $me->getProfileImage();
        if (! $img) {
            header('Content-Type:text/plain');
            echo ("Member does not have a profile picture.");
            return;
        }

        header('Content-Type:image/jpeg');
        echo ($img->get());
    }
}
