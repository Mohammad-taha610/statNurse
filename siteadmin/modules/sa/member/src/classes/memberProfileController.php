<?php
namespace sa\member;

use eye4tech\worm\db\saStates;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\controller;
use sacore\application\Event;
use sacore\application\jsonView;
use sacore\application\modelResult;
use sacore\application\modRequest;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\ValidateException;
use sa\files\FileUploadException;
use sa\files\ImageException;
use sa\files\saFile;
use sa\files\saImages;
use sacore\utilities\debug;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use sacore\utilities\url;

class memberProfileController extends controller
{

    /** @var saMemberRepository $saMemberRepo */
    private $saMemberRepo;
    /** @var saMember $saMember */
    private $saMember;

    public function __construct()
    {
        parent::__construct();

        $this->saMember = ioc::staticResolve('saMember');
        $this->saMemberRepo = app::$entityManager->getRepository($this->saMember);
    }

    static function getDefaultResources()
    {
        return array(
            array('type' => 'css', 'path' => app::get()->getRouter()->generate('member_css', ['file' =>'stylesheet.css'])),
            /*array('type'=>'css', 'path'=> '/components/fileupload/css/jquery.fileupload.css' ),
            array('type'=>'js', 'path'=> '/components/fileupload/js/vendor/jquery.ui.widget.js'),
            array('type'=>'js', 'path'=> '/components/fileupload/js/jquery.iframe-transport.js'),
            array('type'=>'js', 'path'=> '/components/fileupload/js/jquery.fileupload.js'),*/
            array('type' => 'css', 'path' => '/vendor/blueimp/jquery-file-upload/js/jquery.fileupload.css'),
            array('type' => 'js', 'path' => '/vendor/blueimp/jquery-file-upload/js/vendor/jquery.ui.widget.js'),
            array('type' => 'js', 'path' => '/vendor/blueimp/jquery-file-upload/js/jquery.iframe-transport.js'),
            array('type' => 'js', 'path' => '/vendor/blueimp/jquery-file-upload/js/jquery.fileupload.js'),
            array('type' => 'js', 'path' => app::get()->getRouter()->generate('member_js', ['file' => 'avatar.js'])),
        );
    }

    /**
     * @param static saImages $saImages
     * @throws \sa\files\FileUploadException
     */
    public function saveMemberAvatar($saImages, $member = null)
    {
        $view = new Json();
        
        try {
            if(!$member) {
                /** @var saMember $member */
                $member = modRequest::request('auth.member', false);
            }

            /** @var saFile $files */
            $files = $saImages::upload($_FILES['avatar'], 'Member Avatars');
            
            if($files->getIsCompletedFile()) {
                $member->setAvatar($files->getMedium()->getId());
                app::$entityManager->flush();
            }

            $files = array(
                'files' => array(
                    "name" => "Avatar",
                    "size" => 902604,
                    "url" => "http:\/\/example.org\/files\/picture1.jpg",
                    "thumbnailUrl" => "http:\/\/example.org\/files\/thumbnail\/picture1.jpg",
                    "deleteUrl" => "http:\/\/example.org\/files\/picture1.jpg",
                    "deleteType" => "DELETE"
                )
            );

            $view->data = $files;
        } catch(FileUploadException $e) {
            $view->data = array(
                "success" => false,
                "message" => "The file you attempted to upload is not an image. Please select a different file."
            );
        } catch(ImageException $e) {
            $view->data = array(
                "success" => false,
                "message" => "The file you attempted to upload is not an image. Please select a different file."
            );
        }

        return $view;
    }

    /**
     * @param Request $request
     */
    public function getMemberAvatar($request)
    {

        $member = modRequest::request('auth.member', false);
        $saFile = ioc::staticResolve('saFile');
        
        $avatar = app::$entityManager->find($saFile, $member->getAvatar());

        if ($avatar) {
            $fileResponse = new \sacore\application\responses\File(
                app::get()->getConfiguration()->get('uploadsDir')->getValue() . DIRECTORY_SEPARATOR . $avatar->getDiskFileName()
            );
            return $fileResponse;            
        }
    }

    /**
     * @param Request $request
     */
    public function getMemberMediumAvatar($request)
    {
        $saFile = ioc::staticResolve('saFile');
        $member = modRequest::request('auth.member', false);
        $mainAvatar = app::$entityManager->find($saFile, $member->getAvatar());
        /** @var \sa\files\saFile $avatar */
        $avatar = app::$entityManager->find($saFile, $mainAvatar->mdImage);

        if ($avatar) {
            $fileResponse = new \sacore\application\responses\File(
                app::get()->getConfiguration()->get('uploadsDir')->getValue() . DIRECTORY_SEPARATOR . $avatar->getDiskFileName()
            );
            return $fileResponse;            
        }
    }

    /**
     * @param Request $request
     */
    public function getMemberSmallAvatar($request)
    {
        $saFile = ioc::staticResolve('saFile');
        $member = modRequest::request('auth.member', false);
        $mainAvatar = app::$entityManager->find($saFile, $member->getAvatar());
        /** @var \sa\files\saFile $avatar */
        $avatar = app::$entityManager->find($saFile, $mainAvatar->xsImage);

        if ($avatar) {
            $fileResponse = new \sacore\application\responses\File(
                app::get()->getConfiguration()->get('uploadsDir')->getValue() . DIRECTORY_SEPARATOR . $avatar->getDiskFileName()
            );
            return $fileResponse;            
        }
    }

    /**
     * @param Request $request
     */
    public function getMemberMiniAvatar($request)
    {
        $saFile = ioc::staticResolve('saFile');
        $member = modRequest::request('auth.member', false);
        $mainAvatar = app::$entityManager->find($saFile, $member->getAvatar());
        /** @var \sa\files\saFile $avatar */
        $avatar = app::$entityManager->find($saFile, $mainAvatar->microImage);

        if ($avatar) {
            $fileResponse = new \sacore\application\responses\File(
                app::get()->getConfiguration()->get('uploadsDir')->getValue() . DIRECTORY_SEPARATOR . $avatar->getDiskFileName()
            );
            return $fileResponse;            
        }
    }

    public function editMember($request)
    {
        $member = modRequest::request('auth.member', false);
        $view = new View('profile', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_profile_save');
        $view->data['avatar'] = $member->getAvatar();

        if($member){
            $mData = $member->toArray();
            $view->data = array_merge($view->data, $mData);
        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        }

        return $view;
    }

    public function saveMember($request)
    {
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $notify = new notification();
        $member->setFirstName($request->request->all()['first_name']);
        $member->setMiddleName($request->request->all()['middle_name']);
        $member->setLastName($request->request->all()['last_name']);
        $member->setCompany($request->request->get('company'));
        $member->setHomepage($request->request->get('homepage'));
        $member->setComment($request->request->get('comment'));

        if($request->request->get('require_two_factor')){
            $member->setRequireTwoFactor(true);
        }else{
            $member->setRequireTwoFactor(false);
        }

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();

            $notify->addNotification('success', 'Success', 'Member saved successfully.');

//            Event::fire('member.post.changed', $member);
            return new Redirect(app::get()->getRouter()->generate('member_profile'));
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            return $this->editMember($request);
        }
    }


    // MEMBER USERNAMES
    public function viewUsers() {
            /** @var saMember $member */
            $member = modRequest::request('auth.member', false);

            $view = new View('member_users_list', $this->viewLocation());

            $primaryUser = null;
            $users = $member->getUsers();

            /** @var saMemberUsers $user */
            foreach ($users as $user) {
                if ($user->getIsPrimaryUser()) {
                    $primaryUser = $user;
                    break;
                }
            }

            $view->data['primary_user'] = ($primaryUser) ? doctrineUtils::getEntityArray($primaryUser) : null;
            $view->data['users'] = doctrineUtils::getEntityCollectionArray($users);
//            $view->data['users'] = [['id'=>1]];
            return $view;
    }

    public function editMemberUsers($request, $passData = false)
    {
        $usernameId = $request->getRouteParams()->get('id');
        if(is_null($usernameId)) $usernameId = 0;
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        if (empty($usernameId)) {
            $usernameId = 0;
        }

        $view = new view('member_user_edit', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_saveusernames', ['id' => $usernameId]);
        $view->data['memberId'] = $memberId;
        $view->data['usernameId'] = $usernameId;

        if ($usernameId) {
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array(
                'id' => $usernameId,
                'member' => $member
            ));
            if ($user) {
                $mData = doctrineUtils::convertEntityToArray($user);
                $view->data['email'] = $user->getEmail() ? $user->getEmail()->getId() : null;
                $view->data = array_merge($view->data, $mData);
            }

        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        } else {
            unset($view->data['password']);
        }

        $view->data['emails'] = doctrineUtils::convertEntityToArray($member->getEmails());

        return $view;
    }

    public function saveMemberUsers($request)
    {
        $usernameId = $request->getRouteParams()->get('id');
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        /** @var saMemberUsers $user */
        if ($usernameId) {
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array(
                'id' => $usernameId,
                'member' => $member
            ));
        } else {
            $user = ioc::resolve('saMemberUsers');
            $user->setDateCreated(new \sacore\application\DateTime());
            $member->addUser($user);
        }

        // If the current user is trying to deactivate their own account don't let them.
        if(auth::getAuthUser() == $user && !$request->request->get('is_active')) {
            $notify = new notification();
            $notify->addNotification('danger', 'Error', "You cannot deactivate your own account!");
            new Redirect(app::get()->getRouter()->generate('member_editusernames', $user->getId()));
            return;
        }

        $user->setLastName($request->request->get('last_name'));
        $user->setFirstName($request->request->get('first_name'));
        $user->setIsActive($request->request->get('is_active'));

        $user->setUsername($request->request->get('username'));
        $user->setMember($member);

        if (!empty($request->request->get('password'))) {
            $user->setPassword($request->request->get('password'));
        }

        if (!empty($request->get('email')) && $request->get('email') != 'add') {
            /** @var saMemberEmail $email */
            $email = app::$entityManager->find(ioc::staticResolve('saMemberEmail'), $request->request->get('email'));
            $user->setEmail($email);
        } elseif (!empty($request->request->get('email')) && $request->request->get('email') == 'add') {
            /** @var saMemberEmail $email */
            $saMemberEmail = ioc::staticResolve('saMemberEmail');
            $email = new $saMemberEmail();
            $email->setEmail($request->get('email_new'));
            $email->setIsActive(true);
            $email->setIsPrimary(false);
            $email->setType('N\A');
            $email->setMember($member);
            $user->setEmail($email);
        } else {
            $user->setEmail(null);
        }

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();

            $notify->addNotification('success', 'Success', 'User saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_users', ['id' =>$memberId]));
        } catch (ValidateException $e) {

            $member->removeUser($user);
            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            $this->editMemberUsers($request);
        }
    }

    public function deleteMemberUsers($request)
    {
        $usernameId = $request->getRouteParams()->get('id');
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array(
            'id' => $usernameId,
            'member' => $member
        ));

        $notify = new notification();

        try {
            if(count($member->getUsers()) == 1) {
                throw new ValidateException("Sorry, your account must have at least one username/password combination.");
            }

            app::$entityManager->remove($user);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'User deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_users', ['id' =>$memberId]));
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            return new Redirect(app::get()->getRouter()->generate('member_users'));
        }
    }

    // MEMBER PHONES
    public function viewPhoneNumbers()
    {
        /** @var saMember $member */
        $member = modRequest::request('auth.member');
        $phoneNumbers = $member->getPhones();
        $primaryPhone = null;

        /** @var saMemberPhone $phone */
        foreach ($phoneNumbers as $phone) {
            if ($phone->getIsPrimary()) {
                $primaryPhone = $phone;
                break;
            }
        }

        $view = new View('member_phone_list', $this->viewLocation());
        $view->data['phone_numbers'] = doctrineUtils::getEntityCollectionArray($phoneNumbers);
        $view->data['primary_phone'] = ($primaryPhone) ? doctrineUtils::getEntityArray($primaryPhone) : null;

        return $view;
    }


    public function editMemberPhone($request)
    {
        $phoneId = $request->getRouteParams()->get('id');
        if(is_null($phoneId)) $phoneId = 0;
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        if (empty($phoneId)) {
            $phoneId = 0;
        }

        $view = new View('member_phone_edit', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_savephone', ['id'=>$phoneId]);
        $view->data['memberId'] = $memberId;
        $view->data['phoneId'] = $phoneId;

        if ($phoneId) {
            $model = ioc::staticResolve('saMemberPhone');
            $phone = app::$entityManager->getRepository($model)->findOneBy(array(
                'id' => $phoneId,
                'member' => $member
            ));
            if ($phone) {
                $view->data = array_merge($view->data, $phone->toArray());
            }
        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        } else {
            unset($view->data['password']);
        }

        return $view;
    }

    public function saveMemberPhone($request)
    {
        $phoneId = $request->getRouteParams()->get('id');
        if(is_null($phoneId)) $phoneId = 0;
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        /** @var saMemberPhone $phone */
        if ($phoneId) {
            $model = ioc::staticResolve('saMemberPhone');
            $phone = app::$entityManager->getRepository($model)->findOneBy(array(
                'id' => $phoneId,
                'member' => $member
            ));
        } else {
            $phone = ioc::resolve('saMemberPhone');
            $member->addPhone($phone);
        }

        $phone->setPhone($request->request->get('phone'));
        $phone->setType($request->request->get('type'));
        $phone->setIsActive($request->request->get('is_active'));
        $phone->setIsPrimary($request->request->get('is_primary'));
        $phone->setMember($member);

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Phone saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_phone_numbers', ['id' =>$memberId]));
        } catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            $this->editMemberPhone($request);
        }
    }

    public function deleteMemberPhone($request)
    {
        $phoneId = $request->getRouteParams()->get('id');
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        $model = ioc::staticResolve('saMemberPhone');
        $phone = app::$entityManager->getRepository($model)->findOneBy(array('id' => $phoneId, 'member' => $member));

        $notify = new notification();

        try {
            app::$entityManager->remove($phone);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Phone deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_phone_numbers',['id' =>$memberId]));
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            $this->editMemberPhone($request);
        }
    }

    // MEMBER EMAILS
    public function viewEmailAddresses()
    {
        /** @var saMember $member */
        $member = modRequest::request('auth.member');
        $emails = $member->getEmails();
        $primaryEmail = null;

        /** @var saMemberEmail $email */
        foreach ($emails as $email) {
            if ($email->getIsPrimary()) {
                $primaryEmail = $email;
                break;
            }
        }

        $view = new View('member_emails_list');
        $view->data['emails'] = doctrineUtils::getEntityCollectionArray($emails);
        $view->data['primary_email'] = ($primaryEmail) ? doctrineUtils::getEntityArray($primaryEmail) : null;

        return $view;
    }

    public function editMemberEmail($request)
    {
        $saMemberEmail = ioc::staticGet('saMemberEmail');

        $emailId = $request->getRouteParams()->get('id');
        if (empty($emailId)) {
            $emailId = 0;
        }

        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        $view = new View('member_email_edit', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_saveemail', ['id' => $emailId]);
        $view->data['memberId'] = $memberId;
        $view->data['emailId'] = $emailId;

        if ($emailId) {
            $mData = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array(
                'id' => $emailId,
                'member' => $member
            ));

            if ($mData) {
                $view->data = array_merge($view->data, $mData->toArray());
            }
        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        } else {
            unset($view->data['password']);
        }

        return $view;
    }

    public function saveMemberEmail($request)
    {
        $emailId = $request->getRouteParams()->get('id');
        if(is_null($emailId)) $emailId = 0;
        $saMemberEmail = ioc::staticGet('saMemberEmail');
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();
        /** @var saMemberEmail $email */
        if ($emailId) {
            $email = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array(
                'id' => $emailId,
                'member' => $member
            ));

        } else {
            $email = ioc::resolve('saMemberEmail');
            $member->addEmail($email);
        }

        $email->setEmail($request->request->get('email'));
        $email->setType($request->request->get('type'));
        $email->setIsActive($request->request->get('is_active'));
        $email->setIsPrimary($request->request->get('is_primary'));
        $email->setMember($member);

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Email saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_email_addresses', ['id'=>$memberId]));
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            $this->editMemberEmail($request);
        }
    }

    public function deleteMemberEmail($request)
    {
        $emailId = $request->getRouteParams()->get('id');
        if(is_null($emailId)) $emailId = 0;
        $saMemberEmail = ioc::staticGet('saMemberEmail');
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        $email = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array(
            'id' => $emailId,
            'member' => $member
        ));

        $notify = new notification();

        try {
            app::$entityManager->remove($email);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Email deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_email_addresses', ['id' =>$memberId]) . '#edit-emailaddresses');
        } catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            return new Redirect(app::get()->getRouter()->generate('member_email_addresses', ['id' => $memberId]));
        }
    }

    /**
     * Displays a list of a user's addresses.
     */
    public function viewAddresses()
    {
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);

        $view = new View('member_addresses_list', $this->viewLocation());

        $primaryAddress = null;
        $addresses = $member->getAddresses();

        /** @var saMemberAddress $address */
        foreach ($addresses as $address) {
            if ($address->getIsPrimary()) {
                $primaryAddress = $address;
                break;
            }
        }

        $view->data['primary_address'] = ($primaryAddress) ? doctrineUtils::getEntityArray($primaryAddress) : null;
        $view->data['addresses'] = doctrineUtils::getEntityCollectionArray($addresses);
        return $view;
    }

    // MEMBER ADDRESSES

    public function editMemberAddress($request)
    {
        $saMemberAddress = ioc::staticGet('saMemberAddress');
        $addressId = $request->getRouteParams()->get('id');
        if (empty($addressId)) {
            $addressId = 0;
        }

        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();

        $view = new View('member_address_edit', $this->viewLocation(), false);
        $view->data['postRoute'] = app::get()->getRouter()->generate('member_saveaddress', ['id' => $addressId]);
        $view->data['memberId'] = $memberId;
        $view->data['addressId'] = $addressId;
        $view->data['states'] = app::$entityManager->getRepository(ioc::staticResolve('saState'))->findAll();
        $view->data['countries'] = app::$entityManager->getRepository(ioc::staticResolve('saCountry'))->findAll();


        if ($addressId) {
            /** @var saMemberAddress $mData */
            $mData = app::$entityManager->getRepository($saMemberAddress)->findOneBy( array('id'=>$addressId, 'member'=>$member ));

            if ($mData) {
                $view->data = array_merge($view->data, $mData->toArray());

                $view->data['state'] = $mData->getStateObject();

                $view->data['country'] = $mData->getCountryObject();
            }
        }

        if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        } else {
            unset($view->data['password']);
        }

        return $view;
    }

    public function saveMemberAddress($request)
    {
//        echo '<pre>' . print_r($_POST, true) . '</pre>'; exit;
        $addressId = $request->getRouteParams()->get('id');
        if(is_null($addressId)) $addressId=0;

        $saMemberAddress = ioc::staticGet('saMemberAddress');
        /** @var saMember $member */
        $member = modRequest::request('auth.member', false);
        $memberId = $member->getId();
        /** @var saMemberAddress $addressId */
        if ($addressId) {
            $address = app::$entityManager->getRepository($saMemberAddress)->findOneBy(array(
                'id' => $addressId,
                'member' => $member
            ));

        } else {
            $address = ioc::resolve('saMemberAddress');
            $member->addAddress($address);
        }

        $address->setStreetOne($request->request->get('street_one'));
        $address->setStreetTwo($request->request->get('street_two'));
        $address->setCity($request->request->get('city'));
        $address->setPostalCode($request->request->get('postal_code'));
        $address->setState($request->request->get('state'));
        $address->setCountry($request->request->get('country'));
        $address->setType($request->request->get('type'));
        $address->setIsActive($request->request->get('is_active'));
        $address->setIsPrimary($request->request->get('is_primary'));
        $address->setLatitude($request->request->get('latitude'));
        $address->setLongitude($request->request->get('longitude'));
        $address->setMember($member);

        $notify = new notification();

        try {
            app::$entityManager->persist($member);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Address saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_addresses',['id' =>$memberId]));
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            $this->editMemberAddress($request);
        }
    }

    /**
     * @param int $addressId
     * @throws \Exception
     */
    public function deleteMemberAddress($addressId = 0)
    {
        $saMemberAddress = ioc::staticGet('saMemberAddress');
        $member = modRequest::request('auth.member', false);

        $address = app::$entityManager->getRepository($saMemberAddress)->findOneBy(array(
            'id' => $addressId,
            'member' => $member
        ));

        $notify = new notification();

        try {
            app::$entityManager->remove($address);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Address deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('member_addresses'));
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error',
                'An error occurred while saving your changes. <br />' . $e->getMessage());
            return new Redirect(app::get()->getRouter()->generate('member_addresses_list'));
        }
    }
}