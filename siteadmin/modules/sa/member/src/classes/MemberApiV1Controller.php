<?php

namespace sa\member;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use sa\api\ApiController;
use sa\api\Responses\ApiJsonResponse;
use sacore\application\app;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Request;
use sacore\application\responses\ISaResponse;
use sacore\application\responses\Json;
use sacore\application\ValidateException;
use sa\messages\PushToken;
use sa\system\saCountry;
use sa\system\saCountryRepository;
use sa\system\saState;
use sa\system\saStateRepository;
use sa\system\saUser;
use sacore\utilities\doctrineUtils;

/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 9/1/2017
 * Time: 8:14 AM
 */

class MemberApiV1Controller extends ApiController {

    public function __construct()
    {
        $this->setRepo(ioc::getRepository('saMember'));
    }

    public function index(Request $request) : ISaResponse
    {
        throw new NotImplementedException('This method has not been implemented');

        return new Json();
    }

    public function count(Request $request) : ISaResponse
    {
        throw new NotImplementedException('This method has not been implemented');
    }

    public function get(Request $request) : ISaResponse
    {
        throw new NotImplementedException('This method has not been implemented');

        return new Json();
    }

    public function delete(Request $request)
    {
        return new NotImplementedException('This method has not been implemented');
    }

    /**
     * TODO : FIX THIS!!
     */
    public function update(Request $request) : ISaResponse
    {
        $data = [];

        $this->setRepo(ioc::getRepository('saMember'));
        $currentMember = modRequest::request('auth.member');

        //TODO: make a seperate api route to update emails, address, phone numbers, users, etc

        return parent::update($currentMember->getId(), $data);
    }

    public function deleteEmail(Request $request)
    {
        $this->setRepo(ioc::getRepository('saMember'));

        /* @var saMember $currentMember */
        $currentMember = modRequest::request('auth.member', false);

        $saMemberEmail = ioc::staticGet('saMemberEmail');

        $emailId = $data['id'];

        $email = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array(
            'id' => $emailId,
            'member' => $currentMember
        ));

        //email that is curent attached to a user
        try {
            app::$entityManager->remove($email);
            app::$entityManager->flush();
        } catch (ValidateException $e) {
            return array(
                'success' => false,
                'message' => 'Email is currently attached to a user' . $e
            );
        } catch(ForeignKeyConstraintViolationException $e) {
            return [
                'success' => false,
                'message' => 'This email address is currently in use and cannot be deleted'
            ];
        }

        //return doctrineUtils::getEntityCollectionArray($currentMember->getEmails());
        return array(
            'success' => true,
            'message' => 'Email was successfully removed'
        );
    }

    public function updateEmail(Request $request)
    {
        $message = '';
        $data = self::getRequestJsonArrayBody($request);
        $emailId = $data['id'] ?? 0;

        $this->setRepo(ioc::getRepository('saMember'));

        /** @var saMember $currentMember */
        $currentMember = modRequest::request('auth.member', false);

        /** @var ApiJsonResponse $jsonResponse */
        $jsonResponse = ioc::resolve('ApiJsonResponse');

        if($emailId == 0) {
            $Email = ioc::resolve('saMemberEmail');
            //doctrineUtils::setEntityData($data, $Email);
            $currentMember->addEmail($Email);
            $Email->setEmail($data['email']);
            $Email->setType($data['type']);
            $Email->setIsActive($data['is_active']);
            $Email->setIsPrimary($data['is_primary']);
            $Email->setMember($currentMember);

            try {
                app::$entityManager->persist($Email);
                app::$entityManager->flush();
                $message = 'Email has been added!';
            } catch (ValidateException $e) {
                /** @var ApiJsonResponse $response */
                $response = new $jsonResponse(502);
                $response->setResponseData([]);
                $response->setMessage('Email could NOT be added.');
                $response->setSuccess(false);

                return $response;
            }
        } else {
            $saMemberEmail = ioc::staticGet('saMemberEmail');

            /** @var saMemberEmail $Email */
            $Email = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array(
                'id' => $emailId,
                'member' => $currentMember
            ));

            try {
                doctrineUtils::setEntityData($data, $Email, true);
                app::$entityManager->flush();
                $message = 'Email has been updated!';
            } catch (ValidateException $e) {
                /** @var ApiJsonResponse $response */
                $response = new $jsonResponse(502);
                $response->setResponseData([]);
                $response->setMessage('Email could NOT be updated.');
                $response->setSuccess(false);

                return $response;
            }
        }

        /** @var ApiJsonResponse $response */
        $response = new $jsonResponse(200);
        $response->setResponseData(doctrineUtils::getEntityCollectionArray($currentMember->getEmails()));
        $response->setMessage($message);
        $response->setSuccess(true);

        return $response;
    }

    public function deleteUser(Request $request){

        $currentUser = modRequest::request('auth.user');
        $currentUserId = $currentUser->getId();

        /* @var saMember $member */
        $this->setRepo(ioc::getRepository('saMember'));
        $member = modRequest::request('auth.member', false);
        $userId = $data['id'];

        if($currentUserId === $userId){
            return array(
                'success' => false,
                'message' => 'Sorry, you can NOT delete the current user you are logged in with.'
            );
        }
        else {
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array(
                'id' => $userId,
                'member' => $member
            ));

            if (count($member->getUsers()) == 1) {
                return array(
                    'success' => false,
                    'message' => 'Sorry, your account must have at least one username/password combination.'
                );
            } else {
                app::$entityManager->remove($user);
                app::$entityManager->flush();
                //return doctrineUtils::getEntityCollectionArray($member->getUsers());
                return array(
                    'success' => true,
                    'message' => 'User was successfully removed'
                );
            }
        }
    }

    public function updateUser(Request $request)
    {
        $currentUser = modRequest::request('auth.user');
        $currentUserId = $currentUser->getId();

        $this->setRepo(ioc::getRepository('saMember'));
        /** @var saMember $member */
        $member = modRequest::request('auth.member');

        $userId = $data['id'];
        $userEmailId = $data['Email']['id'];

        if($currentUserId === $userId && !$data['is_active']){
            return array(
                'success' => false,
                'message' => 'Sorry, the current user you are logged in with MUST be an active user!'
            );
        } else {
            if (!$userId) {
                //its a new user
                /* @var saMemberUsers $user */
                $user = ioc::resolve('saMemberUsers');
                $user->setLastName($data['last_name']);
                $user->setFirstName($data['first_name']);
                $user->setUsername($data['username']);
                $user->setIsActive($data['is_active']);
                $user->setDateCreated(new \sacore\application\DateTime());
                $user->setMember($member);

                if (!empty($data['password'])) {
                    $user->setPassword($data['password']);
                }

                if (!$userEmailId) {
                    /* @var saMemberEmail $saMemberEmail */
                    $saMemberEmail = ioc::resolve('saMemberEmail');
                    //doctrineUtils::setEntityData($data, $Email);
                    $saMemberEmail->setEmail($data['Email']['email']);
                    $saMemberEmail->setType($data['Email']['type']);
                    $saMemberEmail->setIsActive($data['Email']['is_active']);
                    $saMemberEmail->setIsPrimary($data['Email']['is_primary']);
                    $saMemberEmail->setMember($member);
                    $user->setEmail($saMemberEmail);
                    $member->addEmail($saMemberEmail);
                } else {
                    /* @var saMemberEmail $saMemberEmail */
                    $saMemberEmail = ioc::getRepository('saMemberEmail')->findOneBy(array(
                        'id' => $userEmailId,
                        'member' => $member
                    ));

                    $user->setEmail($saMemberEmail);
                }

                try {
                    app::$entityManager->persist($saMemberEmail);
                    app::$entityManager->persist($user);
                    app::$entityManager->flush();
                } catch (ValidateException $e) {
                    return array(
                        'success' => false,
                        'message' => strip_tags($e->getMessage())
                    );
                }
            } else {
                // its an existing user
                //$saMemberUsers = ioc::staticGet('saMemberUsers');
                $saMemberUsers = ioc::staticResolve('saMemberUsers');

                /* @var saMemberEmail $Email */
                $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array(
                    'id' => $userId,
                    'member' => $member
                ));

                $user->setLastName($data['last_name']);
                $user->setFirstName($data['first_name']);
                $user->setUsername($data['username']);
                $user->setIsActive($data['is_active']);
                $user->setDateUpdated(new \sacore\application\DateTime());

                if (!empty($data['password'])) {
                    $user->setPassword($data['password']);
                }

                if ($userEmailId == 0) {
                    //TODO: is email is brand new
                    /* @var saMemberEmail $saMemberEmail */
                    $saMemberEmail = ioc::resolve('saMemberEmail');
                    $saMemberEmail->setEmail($data['Email']['email']);
                    $saMemberEmail->setType($data['Email']['type']);
                    $saMemberEmail->setIsActive($data['Email']['is_active']);
                    $saMemberEmail->setIsPrimary($data['Email']['is_primary']);
                    $saMemberEmail->setMember($member);
                    $user->setEmail($saMemberEmail);
                    $member->addEmail($saMemberEmail);

                    try {
                        app::$entityManager->persist($saMemberEmail);
                        app::$entityManager->flush();
                    } catch (ValidateException $e) {
                        return array(
                            'success' => false,
                            'message' => 'Issues occurred while updating your user information! ' . strip_tags($e->getMessage())
                        );
                    }
                } else {
                    //TODO: if email exists
                    $saMemberEmail = ioc::staticGet('saMemberEmail');

                    /* @var saMemberEmail $Email */
                    $Email = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array(
                        'id' => $userEmailId,
                        'member' => $member
                    ));
                    $user->setEmail($Email);

                    try{
                        app::$entityManager->flush();
                    } catch (ValidateException $e) {
                        return array(
                            'success' => false,
                            'message' => strip_tags($e->getMessage())
                        );
                    }
                }
            }

            return array(
                'success' => true,
                'message' => 'Your user information was updated!'
            );
        }
    }
    public function deleteAddress(Request $request)
    {

        /* @var saMember $member */
        $this->setRepo(ioc::getRepository('saMember'));
        $currentMember = modRequest::request('auth.member', false);

        $addressId = $data['ID'];

        $saMemberAddress = ioc::staticResolve('saMemberAddress');
        $address = app::$entityManager->getRepository($saMemberAddress)->findOneBy(array(
            'id' => $addressId,
            'member' => $currentMember
        ));
        try {
            app::$entityManager->remove($address);
            app::$entityManager->flush();
            return array(
                'success' => true,
                'message' => 'Your address information was deleted!'
            );
        } catch (ValidateException $e) {
            return array(
                'success' => false,
                'message' => 'Address could NOT be deleted: ' . $e
            );
        }
    }

    public function updateAddress(Request $request)
    {
        $this->setRepo(ioc::getRepository('saMember'));

        /* @var saMember $currentMember */
        $currentMember = modRequest::request('auth.member', false);

        $addressId = $data['ID'];

        /** @var saStateRepository $stateRepo */
        $stateRepo = ioc::getRepository('saState');
        /** @var saCountryRepository $countryRepo */
        $countryRepo = ioc::getRepository('saCountry');

        /** @var saCountry $country */
        $country = $countryRepo->findCountryByNameIdAbbr($data['Country']);

        /** @var saState $state */
        $state = $stateRepo->findStateByNameIdAbbr($data['State'], $country);

        //TODO: not all countries have states...need a work around
        if(!$state) {
            return array(
                'success' => false,
                'message' => 'Please make sure your state is spelled correctly!'
            );
        }
        else{
            if(!$addressId) {
                // this is a new address
                $saMemberAddress = ioc::resolve('saMemberAddress');
                $saMemberAddress->setStreetOne($data['StreetOne']);
                $saMemberAddress->setStreetTwo($data['StreetTwo']);
                $saMemberAddress->setCountry($data['Country']);
                $saMemberAddress->setCity($data['City']);
                $saMemberAddress->setState($data['State']);
                $saMemberAddress->setPostalCode($data['PostalCode']);
                $saMemberAddress->setType($data['Type']);
                $saMemberAddress->setIsPrimary($data['IsPrimary']);
                $saMemberAddress->setIsActive($data['IsActive']);
                $saMemberAddress->setMember($currentMember);

                try {
                    app::$entityManager->persist($saMemberAddress);
                    app::$entityManager->flush();
                } catch (ValidateException $e) {
                    return array(
                        'success' => false,
                        'message' => 'Address could NOT be created: ' . $e
                    );
                }
            } else{
                // this is an existing address
                $saMemberAddress = ioc::staticGet('saMemberAddress');

                /** @var saMemberAddress $Address */
                $Address = app::$entityManager->getRepository($saMemberAddress)->findOneBy(array(
                    'id' => $addressId,
                    'member' => $currentMember
                ));

                //doctrineUtils::setEntityData($data, $Address);
                $Address->setStreetOne($data['StreetOne']);
                $Address->setStreetTwo($data['StreetTwo']);
                $Address->setCountry($data['Country']);
                $Address->setCity($data['City']);
                $Address->setState($data['State']);
                $Address->setPostalCode($data['PostalCode']);
                $Address->setType($data['Type']);
                $Address->setIsPrimary($data['IsPrimary']);
                $Address->setIsActive($data['IsActive']);
                $Address->setMember($currentMember);

                //TODO: double check on isPrimary to see if several addresses can be primary address
                try {
                    app::$entityManager->flush();
                } catch (ValidateException $e) {
                    return array(
                        'success' => false,
                        'message' => 'Address could NOT be updated: ' . $e
                    );
                }
            }
        }
        return array(
            'success' => true,
            'message' => 'Your address information was updated!'
        );
    }

    public function deletePhone(Request $request)
    {
        /* @var saMember $member */
        $this->setRepo(ioc::getRepository('saMember'));
        $currentMember = modRequest::request('auth.member', false);

        $phoneId = $data['ID'];

        $saMemberPhone = ioc::staticResolve('saMemberPhone');
        $phone = app::$entityManager->getRepository($saMemberPhone)->findOneBy(array(
            'id' => $phoneId,
            'member' => $currentMember
        ));
        try {
            app::$entityManager->remove($phone);
            app::$entityManager->flush();
            return array(
                'success' => true,
                'message' => 'Your phone number information was deleted!'
            );
        } catch (ValidateException $e) {
            return array(
                'success' => false,
                'message' => 'Phone number could NOT be deleted: ' . $e
            );
        }
    }

    public function updatePhone(Request $request)
    {
        $this->setRepo(ioc::getRepository('saMember'));

        /* @var saMember $currentMember */
        $currentMember = modRequest::request('auth.member', false);

        $phoneId = $data['ID'];

        $phoneNumber = $data['PhoneNumber'];

        if($phoneId == 0){
            // its a new phone number
            $saMemberPhone = ioc::resolve('saMemberPhone');
            $saMemberPhone->setPhone($data['PhoneNumber']);
            $saMemberPhone->setType($data['Type']);
            $saMemberPhone->setIsActive($data['IsActive']);
            $saMemberPhone->setIsPrimary($data['IsPrimary']);
            $saMemberPhone->setMember($currentMember);

            try {
                app::$entityManager->persist($saMemberPhone);
                app::$entityManager->flush();
            } catch (ValidateException $e) {
                return array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
        } else {
            //its and existing phone number

            /** @var saMemberPhone $phoneRepo */
            $phoneRepo = ioc::getRepository('saMemberPhone');

            /** @var saMemberPhone $Phone */
            $Phone = $phoneRepo->findOneBy(array(
                'id' => $phoneId,
                'member' => $currentMember
            ));

            //doctrineUtils::setEntityData($data, $Address);
            //$Phone = ioc::resolve('saMemberPhone');
            $Phone->setPhone($data['PhoneNumber']);
            $Phone->setType($data['Type']);
            $Phone->setIsActive($data['IsActive']);
            $Phone->setIsPrimary($data['IsPrimary']);
            $Phone->setMember($currentMember);

            try {
                app::$entityManager->flush();
            } catch (ValidateException $e) {
                return array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
        }

        return array(
            'success' => true,
            'message' => 'Your Phone number information was updated!'
        );
    }

    public function create(Request $request) : ISaResponse
    {
        try {
            // Note: If member confirmation e-mails are enabled,
            // members will be pending by default when created.

            /** @var saMember $member */
            $data['email2'] = $data['email'];
            $data['password2'] = $data['password'];

            /** @var saMember $saMember */
            $saMember = ioc::staticResolve('saMember');
            $member = $saMember::memberSignUp($data, app::get()->getConfiguration()->get('member_confirmation_email')->getValue());
            $responseArray = [];
            $responseArray['member'] = doctrineUtils::getEntityArray($member);
            $responseArray['member']['date_created'] = $member->getDateCreated()->format('Y-m-d G:i:s', true);
            $responseArray['member']['customer_since_date'] = $member->getDateCreated()->format('Y-m-d G:i:s', true);
            $responseArray['success'] = true;

            return $responseArray;
        } catch(ValidateException $e) {
            return array('success' => false, 'message' => $e->getMessage());
        } catch(Exception $e) {
            return array('success' => false, 'message' => 'A Server error occurred creating the member.');
        }
    }

    public function isPending(Request $request)
    {
        /** @var saMember $member */
        $member = ioc::getRepository('saMember')->findOneBy(array('id' => $data['id']));

        if(!$member) {
            return array('member_status' => 'Member not found');
        }

        $isPending = null;

        if($member->getIsPending()) {
            $isPending = true;
        } else {
            $isPending = false;
        }

        return array('member_status' => $isPending);
    }

    public static function reset_password(Request $request) : Json
    {
        $data = self::getRequestJsonArrayBody($request);

        /** @var ApiJsonResponse $jsonResponse */
        $jsonResponse = ioc::resolve('ApiJsonResponse');
        
        try {
            /** @var saMemberUsers $saMemberUsers */
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $saMemberUsers::requestResetPassword($data['username']);
        }
        catch(\Exception $e) {
            /** @var ApiJsonResponse $response */
            $response = new $jsonResponse(502);
            $response->setMessage($e->getMessage());
            $response->setResponseData([]);
            $response->setSuccess(false);
            return $response;
        }

        /** @var ApiJsonResponse $response */
        $response = new $jsonResponse(200);
        $response->setMessage("Reset password link sent to the registered email.");
        $response->setResponseData([]);
        $response->setSuccess(true);
        return $response;
    }

    /**
     * @param Request $request
     * @return ApiJsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ModRequestAuthenticationException
     */
    public static function login_check(Request $request)
    {
        /** @var auth $auth */
        $auth = ioc::staticResolve('auth');
        $auth = $auth::getInstance();

        $isAuthenticated = (bool) $auth::isAuthenticated();

        $response = ioc::staticResolve('ApiJsonResponse');
        /** @var ApiJsonResponse $response */
        $response = new $response();
        $response->setSuccess(true);
        $response->setMessage(null);
        $response->setResponseData(['isAuthenticated' => $isAuthenticated]);

        return $response;
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * @api
     */
    public static function logoff (Request $request)
    {
        /** @var api $api */
        /** @var auth $auth */
        $auth = ioc::staticResolve('auth');
        $auth::getInstance()->logoff();
        return true;
    }

    public function login(Request $request) : Json
    {
        $statusCode = 200;
        $message = null;
        $success = true;

        /** @var ApiJsonResponse $jsonResponse */
        $jsonResponse = ioc::resolve('ApiJsonResponse');
        /** @var ApiJsonResponse $response */
        $response = new $jsonResponse(502);
        $response->setMessage($message);
        $response->setSuccess($success);

        $data = parent::getRequestJsonArrayBody($request);

        $auth = ioc::staticResolve('auth');
        /** @var auth $auth */
        $auth = $auth::getInstance();

        $login_type = null;


        if ($data['username'] && $data['password']) {
            $result = $auth->logon($data['username'], $data['password']);
            $member = $auth::getAuthMember();
            $login_type = 'username';
        } elseif ($data['key']) {
            $result = $auth->logon(null, null, $data['key'], 'api');
            $member = $auth::getAuthMember();
            $login_type = 'key';
        }

        if ($result) {
            $retAr = ['status' => true ];
            $retAr['login_key'] = $login_type=='username' ? $auth->issueMemberLoginKey('api')->getUuid() : $data['key'];
            $retAr['member'] = doctrineUtils::getEntityArray($member);
            $retAr['member']['date_created'] = $member->getDateCreated()->format('Y-m-d G:i:s', true);
            $retAr['member']['customer_since_date'] = $member->getDateCreated()->format('Y-m-d G:i:s', true);
            $retAr['groups'] = doctrineUtils::getEntityCollectionArray($member->getGroups());
            $retAr['member']['addresses'] = array();
            $retAr['member']['users'] = array();
            $retAr['member']['emails'] = array();

            if(!empty($data['token']) && !empty($data['device_uuid']) && !empty($data['platform'])) {
                /** @var saMemberUsers $user */
                $user = $auth::getAuthUser();

                /** @var PushToken $token */
                $token = ioc::getRepository('PushToken')->findOneBy(array(
                    'device_uuid' => $data['device_uuid'],
                    'platform' => $data['platform'],
                    'user_id' => $user->getId()
                ));

                if(!$token) {
                    $token = ioc::resolve('PushToken');
                }

                $token->setPlatform($data['platform']);
                $token->setDeviceUuid($data['device_uuid']);
                $token->setToken($data['token']);
                $token->setUserId($user->getId());

                app::$entityManager->persist($token);
                app::$entityManager->flush($token);
            }

            if ($member->getAddresses()) {
                $retAr['member']['addresses'] = doctrineUtils::getEntityCollectionArray($member->getAddresses());
            }

            if ($member->getUsers()) {
                /** @var saMemberUsers $user */
                foreach ($member->getUsers() as $user) {
                    $tmp = array();

                    $tmp['id'] = $user->getId();
                    $tmp['username'] = $user->getUsername();
                    $tmp['is_primary'] = $user->getIsPrimaryUser();
                    $tmp['is_active'] = $user->getIsActive();

                    $retAr['member']['users'][] = $tmp;
                }
            }

            if ($member->getEmails()) {
                $retAr['member']['emails'] = doctrineUtils::getEntityCollectionArray($member->getEmails());
            }
        } else {
            $statusCode = 401;
            $message = 'Invalid Username and/or Password. Please try again.';
            $retAr = null;
            $success = false;
        }

        /** @var ApiJsonResponse $jsonResponse */
        $jsonResponse = ioc::resolve('ApiJsonResponse');

        /** @var ApiJsonResponse $response */
        $response = new $jsonResponse($statusCode);
        $response->setResponseData($retAr);
        $response->setMessage($message);
        $response->setSuccess($success);

        return $response;
    }

    /**
     * @return array|void
     * @throws Exception
     */
    public static function getMyProfile()
    {
        /** @var saMember $me */
        $me = modRequest::request('auth.member');
        if ($me === null) {
            return;
        }
        $return_array = [];
        $users = $me->getUsers();

        /** @var saMemberUsers $user */
        foreach($users as $user) {
            $user_array = doctrineUtils::getEntityArray($user);
//            $user_array['email'] = doctrineUtils::getEntityCollectionArray($user->getEmail());
            $user_array['email'] = doctrineUtils::getEntityArray($user->getEmail());
            $return_array['users'][] = $user_array;
        }

        $memberArray = doctrineUtils::convertEntityToArray($me);

        foreach($return_array['users'] as &$userArray) {
            if(!$userArray['email']) {
                continue;
            }

            $userArray['email']['is_active'] = $userArray['email']['is_active'] ? true : false;
            $userArray['email']['is_primary'] = $userArray['email']['is_primary'] ? true : false;
        }

        foreach($memberArray['phones'] as &$phoneArray) {
            $phoneArray['is_active'] = $phoneArray['is_active'] ? true : false;
            $phoneArray['is_primary'] = $phoneArray['is_primary'] ? true : false;
        }

        foreach($memberArray['emails'] as &$emailArray) {
            $emailArray['is_active'] = $emailArray['is_active'] ? true : false;
            $emailArray['is_primary'] = $emailArray['is_primary'] ? true : false;
        }

        foreach($memberArray['addresses'] as &$addressArray) {
            $addressArray['is_active'] = $addressArray['is_active'] ? true : false;
            $addressArray['is_primary'] = $addressArray['is_primary'] ? true : false;
        }

        return array(
            'success' => true,
            'profile' => $memberArray,
            'profileUsers' => $return_array/*,
            'profileUsers' => doctrineUtils::convertEntityToArray($me2)*/
        );
    }
    /**
     * @return array|void
     * @throws Exception
     */
    public static function getCountries()
    {
        /** @var saCountryRepository $countries */
        $countries = ioc::getRepository('saCountry')->findAll();

        $arr = [];
        foreach($countries as $country) {
            $arr[] = [
                'id' => $country->getId(),
                'abbreviation' => $country->getAbbreviation(),
                'name' => $country->getName()
            ];
        }
        usort($arr, function($a, $b) { return strcmp($a['name'], $b['name']);});

        return array(
            'success' => true,
            'countries' => $arr
        );
    }

    public static function updatePushToken(Request $request)
    {
        /** @var saMemberUsers $user */
        $user = modRequest::request('auth.user');

        $data = self::getRequestJsonArrayBody($request);

        if(!$user) {
            return [
                'success' => false,
                'message' => 'Unauthorized'
            ];
        }

        if(empty($data['token']) || empty($data['platform']) || empty($data['device_uuid'])) {
            return [
                'success' => false,
                'message' => 'Missing data'
            ];
        }

        /** @var PushToken $pushToken */
        $pushToken = ioc::getRepository('PushToken')->findOneBy([
            'device_uuid' => $data['device_uuid'],
            'platform' => $data['platform'],
            'user_id' => $user->getId()
        ]);

        if(!$pushToken) {
            $pushToken = ioc::resolve('PushToken');
        }

        $pushToken->setToken($data['token']);
        $pushToken->setDeviceUuid($data['device_uuid']);
        $pushToken->setUserId($user->getId());
        $pushToken->setPlatform($data['platform']);

        app::$entityManager->persist($pushToken);
        app::$entityManager->flush($pushToken);

        return [
            'success' => true,
            'message' => 'Success'
        ];
    }
}
