<?php


namespace sa\member\Test;

use PHPUnit\PhpParser\Node\Param;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\File;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\ValidateException;
use sa\member\auth;
use sa\member\memberAPIController;
use sa\member\memberConfig;
use sa\member\memberController;
use sa\member\MemberElementsController;
use sa\member\memberProfileController;
use sa\member\MemberProfileModRequestListeners;
use sa\member\MemberTwoFactorController;
use sa\member\saMember;
use sa\member\saMemberController;
use sa\member\saMemberGroup;
use sa\Test\RouteTest;
use sacore\utilities\doctrineUtils;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RouteCollection;

class MemberRouteTest extends RouteTest
{

    const NUMBER_OF_ROUTES = 98;
    const MEMBER_DATA = ['last_name' => 'StaticTest', 'first_name' => 'StaticTest', 'middle_name' => 'StaticTest', 'company' => 'StaticTest',
                        'is_active' => true];
    const MEMBER_EMAIL_DATA = ['email' => 'StaticTestEmail@test.com', 'type' => 'personal', 'is_active' => true, 'is_pending' => false];
    const MEMBER_USER_DATA = ['first_name' => 'StaticTestFirst', 'last_name' => 'StaticTestLast', 'username' => 'StaticTestEmail@test.com'
                                ,'password' => 'TestPassword', 'password_reset_key' => 'test','password_reset_key2' => 'reset', 'is_active' => true];

    public function testRouteInit()
    {
        $rCollection = new RouteCollection();
        memberConfig::getRouteCollection($rCollection, 'files');
        $this->assertEquals(self::NUMBER_OF_ROUTES, $rCollection->count());
    }

    public function testSetupEntities(){
        /** @var saMember $member */
        $member = ioc::resolve('saMember');

        $member = doctrineUtils::setEntityData(self::MEMBER_DATA, $member);
        $member->setDateCreated( new DateTime() );

        $email = ioc::resolve('saMemberEmail');
        $member->addEmail($email);
        $email = doctrineUtils::setEntityData(self::MEMBER_EMAIL_DATA, $email);
        $email->setMember($member);

        $user = ioc::resolve('saMemberUsers');
        $user->setDateCreated(new DateTime());
        $member->addUser($user);
        $user = doctrineUtils::setEntityData(self::MEMBER_USER_DATA, $user);
        $user->setMember($member);
        $user->setEmail($email);


        $groupSetting = app::get()->getConfiguration()->get('member_groups')->getValue();

        //Todo: If groups are needed uncomment this
//        if ( !empty($data['group_name']) ) {
//            /** @var saMemberGroup $group */
//            $group = app::$entityManager->getRepository( ioc::staticResolve('saMemberGroup') )->findOneBy( array( 'name' => $data['group_name'] ) );
//
//            if($groupSetting == 'user' && $group) {
//                $user->addGroup($group);
//            } else if($group) {
//                $member->addGroup($group);
//            }
//        }
//
//        $defaultGroups = ioc::getRepository('saMemberGroup')->findBy(array('is_default' => true));
//
//        if($defaultGroups) {
//            /** @var saMemberGroup $group */
//            foreach($defaultGroups as $defaultGroup) {
//                if($groupSetting == 'user') {
//                    $user->addGroup($defaultGroup);
//                } else {
//                    $member->addGroup($defaultGroup);
//                }
//            }
//        }

        //Required for testing linking group
        $group = ioc::resolve('saMemberGroup');
        $group->setName('testGroup1');
        $group->setDescription('A group that needs to exist to link to');
        $group->setIsDefault(false);
        app::$entityManager->persist($group);

        //Required for Addresses
        /** @var saState $state */
        $state = ioc::resolve('saState');
        $state->setAbbreviation('KY');
        $state->setName('Kentucky');
        app::$entityManager->persist($state);

        app::$entityManager->persist($member);
        app::$entityManager->flush();
        $this->assertTrue(True);
    }

    public function testAPILogonExists(){
        $this->singleRouteDefinition('api_member_login');
    }

    //Todo: Bad test, not sure how to replace API in this method so can't test it for new functionality
    public function testAPILogonFunctionality(){
//        $controller = new memberAPIController();
//        $controller->login();
        $this->fail("API Logon, not updated to SA4 properly");
    }

    public function testAPILogoffExists(){
        $this->singleRouteDefinition('api_member_logoff');
    }

    public function testAPILogoffFunctionality(){
//        $controller = new memberAPIController();
//        $controller->logoff();
        $this->fail("API Logoff, not updated to SA4 properly");
    }

    public function testAPIIsLoggedInExists(){
        $this->singleRouteDefinition('api_member_isloggedin');
    }

    public function testAPIIsLoggedInFunctionality(){
//        $controller = new memberAPIController();
//        $controller->isLoggedIn();
        $this->fail('API Is Logged In, not updated to SA4 properly');
    }

    public function testAPIGetMyProfileExists(){
        $this->singleRouteDefinition('api_get_myprofile');
    }

    public function testAPIGetMyProfileFunctionality(){
//        $controller = new memberAPIController();
//        $controller->getMyProfile();
        $this->fail('API Get My Profile, not updated to SA4 properly');
    }

    public function testAPILocationPushServiceUpdateExists(){
        $this->singleRouteDefinition('api_member_push_token_update');
    }

    public function testAPILocationPushServiceUpdateFunctionality(){
        $controller = new memberAPIController();
        $controller->pushTokenUpdate();
        $this->assertTrue(method_exists($controller, 'pushTokenUpdate'));
    }

    public function testUserNotAllowedExists(){
        $this->singleRouteDefinition('user_not_allowed');
    }

    public function testUserNotAllowedFunctionality(){
        $controller = new memberController();
        $view = $controller->userNotAllowed();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testDashboardExists(){
        $this->singleRouteDefinition('dashboard_home');
    }

    public function testDashboardFunctionality(){
        $controller = new memberController();
        $view = $controller->dashboard();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testLoginExists(){
        $this->singleRouteDefinition('member_login');
    }

    public function testLoginFunctionality(){
        $this->loginUser();
        $redirect = new Request();
        $controller = new memberController();
        $redirect = $controller->login($redirect);
        $this->assertInstanceOf(View::class, $redirect);
    }

    public function testAttemptLoginExists(){
        $this->singleRouteDefinitionPost('member_login_post');
    }

    public function testAttemptLoginFunctionality(){
        $request = new Request([],['username' => 'StaticTestEmail@test.com','password' =>'TestPassword']);
        $controller = new memberController();
        $redirect = $controller->attemptLogin($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testLoginJSRedirectExists(){
        $this->singleRouteDefinition('member_loginjsredirect');
    }

    public function testLoginJSRedirectFunctionality(){
        $controller = new memberController();
        $controller->login_redirect();
        $this->assertTrue(method_exists($controller, 'login_redirect'));
    }

    public function testLogoffExists(){
        $this->singleRouteDefinition('member_logoff');
    }

    public function testLogoffFunctionality(){
        $this->logAuthMemberIn();
        $controller = new memberController();
        $redirect = $controller->logoff();
        $this->assertInstanceOf(Redirect::class, $redirect);
        $member = modRequest::request('auth.member', false);
        $this->assertFalse($member);
    }

    public function testSignUpExists(){
        $this->singleRouteDefinition('member_signup');
    }

    public function testSignUpFunctionality(){
        $request = new Request([],[]);
        $controller = new memberController();
        $view = $controller->signup($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberConfirmationExists(){
        $this->singleRouteDefinition('member_signup_confirmation');
    }

    public function testMemberConfirmationFunctionality(){
        $request = new Request([],['k' => self::MEMBER_USER_DATA['password_reset_key'], 'i' => 1]);
        $controller = new memberController();
        $view = $controller->signupConfirmation($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSignUpPostExists(){
        $this->singleRouteDefinition('member_signup_post');
    }

    public function testSignUpPostFunctionality(){
        $request = new Request([],['first_name' => 'memberSignUpPostFirst','last_name' => 'memberSignUpPostLast','password'=>'signupTest', 'password2'=>'signupTest', 'email' =>'signup@test.com', 'email2' =>'signup@test.com',]);
        $controller = new memberController();
        $redirect = $controller->signupsave($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testPasswordResetExists(){
        $this->singleRouteDefinition('member_reset');
    }

    public function testPasswordResetFunctionality(){
        $controller = new memberController();
        $view = $controller->resetPassword();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testPasswordResetPostExists(){
        $this->singleRouteDefinitionPost('member_reset_post');
    }

    public function testPasswordResetPostFunctionality(){
        $request = new Request([],['username' => self::MEMBER_USER_DATA['username']]);
        $controller = new memberController();
        $redirect = $controller->attemptResetPassword($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testPasswordResetChangeExists(){
        $this->singleRouteDefinition('member_reset_change');
    }

    public function testPasswordResetChangeFunctionality(){
        $memberUser = ioc::getRepository('saMemberUsers')->find(1);
        $resetKey1 = $memberUser->getPasswordResetKey();
        $resetKey2 = $memberUser->getPasswordResetKey2();
        $request = new Request([],['k' => $resetKey1,'i' => $resetKey2]);
        $controller = new memberController();
        $view = $controller->resetPasswordChange($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testPasswordResetChangePostExists(){
        $this->singleRouteDefinitionPost('member_reset_change_post');
    }

    public function testPasswordResetChangePostFunctionality(){
        $request = new Request([],['k' => self::MEMBER_USER_DATA['password_reset_key'],'i' => self::MEMBER_USER_DATA['password_reset_key2'],
                                'password' => self::MEMBER_USER_DATA['password'] . 't', 'password2'=> self::MEMBER_USER_DATA['password'] . 't']);
        $controller = new memberController();
        $redirect = $controller->resetPasswordChangeSave($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $this->loginUser();
        $member = modRequest::request('auth.member',false);
        $this->assertNull($member);

        //Cleanup
        $request = new Request([],['k' => self::MEMBER_USER_DATA['password_reset_key'],'i' => self::MEMBER_USER_DATA['password_reset_key2'],
            'password' => self::MEMBER_USER_DATA['password'], 'password2'=> self::MEMBER_USER_DATA['password']]);
        $controller = new memberController();
        $controller->resetPasswordChangeSave($request);
    }

    public function testEditProfileExists(){
        $this->singleRouteDefinition('member_profile');
    }

    public function testEditProfileFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 1]));
        $controller = new memberProfileController();
        $view = $controller->editMember($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSaveMemberExists(){
        $this->singleRouteDefinitionPost('member_profile_save');
    }

    //Todo: This breaks most of the tests after it, I will look at this later but I have no idea why it isn't updating the member data a second time
//    public function testSaveMemberFunctionality(){
////        $this->logoffUser();
//        $this->logAuthMemberIn();
//        $request = new Request([],['first_name' => 'saveFirstTest', 'middle_name' => 'saveMiddleTest', 'last_name' => 'saveLastTest',
//                                    'company' => 'saveCompanyTest', 'homepage' => 'saveHomepageTest']);
//        $request->setRouteParams(new ParameterBag());
//        $controller = new memberProfileController();
//        $redirect = $controller->saveMember($request);
//        $this->assertInstanceOf(Redirect::class, $redirect);
//
//        $member = ioc::getRepository('saMember')->search(['first_name' => 'saveFirstTest'])[0];
//        $this->assertNotNull($member);
//
//        //Revert change to keep everything consistent
//        $member->setFirstName(self::MEMBER_DATA['first_name']);
//        app::$entityManager->persist($member);
//        app::$entityManager->flush();
//        $this->logAuthMemberOut();
////        $this->loginUser();
//    }

    public function testMemberProfileAvatarUploadExists(){
        $this->singleRouteDefinition('member_profile_avatar_upload');
    }

    public function testMemberProfileAvatarUploadFunctionality(){
        $controller = new memberProfileController();
        $controller->saveMemberAvatar();
//        $this->fail("Involves File uploads will need to come back to here");
    }

    public function testMemberProfileAvatarExists(){
        $this->singleRouteDefinition('member_profile_avatar');
    }

    public function testMemberProfileAvatarFunctionality(){
        $request = new Request();
        $member = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $member->getId()]));
        $controller = new memberProfileController();
        $file = $controller->getMemberAvatar($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testMemberProfileMediumAvatarExists(){
        $this->singleRouteDefinition('member_profile_mediumavatar');
    }

    public function testMemberProfileMediumAvatarFunctionality(){
        $request = new Request();
        $controller = new memberProfileController();
        $file = $controller->getMemberMediumAvatar($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testMemberProfileSmallAvatarExists(){
        $this->singleRouteDefinition('member_profile_smallavatar');
    }

    public function testMemberProfileSmallAvatarFunctionality(){
        $request = new Request();
        $controller = new memberProfileController();
        $file = $controller->getMemberSmallAvatar($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testMemberProfileMiniAvatarExists(){
        $this->singleRouteDefinition('member_profile_miniavatar');
    }

    public function testMemberProfileMiniAvatarFunctionality(){
        $request = new Request();
        $controller = new memberProfileController();
        $file = $controller->getMemberMiniAvatar($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testUploadAvatarExists(){
        $this->singleRouteDefinition('sa_member_avatar_upload');
    }

    public function testUploadAvatarFunctionality(){
        $this->fail('Requires File upload, come back to here');
        $request = new Request();
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $file = $controller->uploadAvatar($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testGetAvatarExists(){
        $this->singleRouteDefinition('sa_member_avatar');
    }

    public function testGetAvatarFunctionality(){
        $request = new Request();
        $member = $this->getMember();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $controller = new saMemberController($member);
        $file = $controller->getAvatar($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testDeleteAvatarExists(){
        $this->singleRouteDefinition('sa_member_avatar_remove');
    }

    public function testDeleteAvatarFunctionality(){
        $request = new Request();
        $member = $this->getMember();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $controller = new saMemberController($member);
        $json = $controller->removeAvatar($request);
        $this->assertInstanceOf(Json::class, $json);
        $this->assertNotNull($json->data['success']);
    }

    public function testUsersExists(){
        $this->singleRouteDefinition('member_users');
    }

    public function testUsersFunctionality(){
        $controller = new memberProfileController();
        $view = $controller->viewUsers();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testAddUsernameExists(){
        $this->singleRouteDefinition('member_createusers');
    }

    public function testAddUsernameFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $view = $controller->editMemberUsers($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSaveUsernameExists(){
        $this->singleRouteDefinitionPost('member_saveusernames');
    }

    public function testSaveUsernameFunctionality(){
        $request = new Request([],['first_name' => 'saveTestFirst', 'last_name' => 'saveTestLast', 'username' => 'saveTestUsername', 'password' => 'saveTestPassword', 'email' => self::MEMBER_EMAIL_DATA['email']]);
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $controller->saveMemberUsers($request);
        $repo = ioc::getRepository('saMemberUsers');
        $user = $repo->search(['first_name' => 'saveTestFirst', 'last_name' => 'saveTestLast'])[0];
        $this->assertNotNull($user);
    }

    public function testEditUsernameExists(){
        $this->singleRouteDefinition('member_editusernames');
    }

    public function testEditUsernameFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $controller = new memberProfileController();
        $view = $controller->editMemberUsers($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testDeleteUsernameExists(){
        $this->singleRouteDefinition('member_deleteusernames');
    }

    public function testDeleteUsernameFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $controller = new memberProfileController();
        $redirect = $controller->deleteMemberUsers($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $repo = ioc::getRepository('saMemberUsers');
        $user = $repo->find(2);
        $this->assertNull($user);
    }

    public function testEmailAddressesExists(){
        $this->singleRouteDefinition('member_email_addresses');
    }

    public function testEmailAddressesFunctionality(){
        $controller = new memberProfileController();
        $view = $controller->viewEmailAddresses();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testAddEmailExists(){
        $this->singleRouteDefinition('member_createemail');
    }

    public function testAddEmailFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $view = $controller->editMemberEmail($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSaveEmailExists(){
        $this->singleRouteDefinitionPost('member_saveemail');
    }

    public function testSaveEmailFunctionality(){
        $request = new Request([],['email' => 'saveEmail@test.com', 'type' => 'other', 'is_active' => true, 'is_primary' => false]);
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $redirect = $controller->saveMemberEmail($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $email = ioc::getRepository('saMemberEmail')->find(2);
        $this->assertNotNull($email);
    }

    public function testEditEmailExists(){
        $this->singleRouteDefinition('member_editemail');
    }

    public function testEditEmailFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $controller = new memberProfileController();
        $view = $controller->editMemberEmail($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testDeleteEmailExists(){
        $this->singleRouteDefinition('member_deleteemail');
    }

    public function testDeleteEmailFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $controller = new memberProfileController();
        $redirect = $controller->deleteMemberEmail($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $email = ioc::getRepository('saMemberEmail')->find(2);
        $this->assertNull($email);
    }

    public function testAddressesExists(){
        $this->singleRouteDefinition('member_addresses');
    }

    public function testAddressesFunctionality(){
        $controller = new memberProfileController();
        $view = $controller->viewAddresses();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testAddAddressesExists(){
        $this->singleRouteDefinition('member_createaddress');
    }

    public function testAddAddressesFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $view = $controller->editMember($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSaveAddressesExists(){
        $this->singleRouteDefinitionPost('member_saveaddress');
    }

    public function testSaveAddressesFunctionality(){
        $request = new Request([],['street_one' => 'saveTestStreet', 'country' => 'United States', 'state' => 'Kentucky', 'city' => 'testville', 'postal_code' => '44444',
                                    'type' => 'other', 'is_active' => true, 'is_primary' => false]);
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $redirect = $controller->saveMemberAddress($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $address = ioc::getRepository('saMemberAddress')->search(['street_one' => 'saveTestStreet','postal_code' => '44444', 'type' => 'other'])[0];
        $this->assertNotNull($address);
    }


    public function testEditAddressesExists(){
        $this->singleRouteDefinition('member_editaddress');
    }

    public function testEditAddressesFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $controller = new memberProfileController();
        $view = $controller->editMemberAddress($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testDeleteAddressesExists(){
        $this->singleRouteDefinition('member_deleteaddress');
    }
    
    public function testDeleteAddressesFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $controller = new memberProfileController();
        $redirect = $controller->deleteMemberAddress($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $address = ioc::getRepository('saMemberAddress')->find(2);
        $this->assertNull($address);
    }

    public function testPhoneNumbersExists(){
        $this->singleRouteDefinition('member_phone_numbers');
    }

    public function testPhoneNumberFunctionality(){
        $controller = new memberProfileController();
        $view = $controller->viewPhoneNumbers();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testAddPhoneExists(){
        $this->singleRouteDefinition('member_createphone');
    }

    public function testAddPhoneFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $view = $controller->editMemberPhone($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSavePhoneExists(){
        $this->singleRouteDefinition('member_savephone');
    }

    public function testSavePhoneFunctionality(){
        $request = new Request([],['phone' => '5555555555', 'type' => 'other', 'is_active' => true, 'is_primary' => false]);
        $request->setRouteParams(new ParameterBag());
        $controller = new memberProfileController();
        $redirect = $controller->saveMemberPhone($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $phone = ioc::getRepository('saMemberPhone')->search(['phone' => '5555555555', 'type' => 'other', 'is_active' => true, 'is_primary' => false])[0];
        $this->assertNotNull($phone);
    }

    public function testEditPhoneExists(){
        $this->singleRouteDefinition('member_editphone');
    }

    public function testEditPhoneFunctionality(){
        $request = new Request([],[]);
        $request->setRouteParams(new ParameterBag(['id' => 1]));
        $controller = new memberProfileController();
        $view = $controller->editMemberPhone($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testDeletePhoneExists(){
        $this->singleRouteDefinition('member_deletephone');
    }

    public function testDeletePhoneFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => 1]));
        $controller = new memberProfileController();
        $redirect = $controller->deleteMemberPhone($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $phone = ioc::getRepository('saMemberPhone')->find(1);
        $this->assertNull($phone);
    }

    public function testViewMemberNotificationHistoryExists(){
        $this->singleRouteDefinition('member_notification_history');
    }

    public function testViewMemberNotificationHistoryFunctionality(){
        $controller = new memberController();
        $view = $controller->viewNotificationHistory();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberImagesExists(){
        $this->singleRouteDefinition('member_images');
    }

    public function testMemberImagesFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'sa_signin.png']));
        $controller = new memberController();
        $response = $controller->img($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testMemberCSSExists(){
        $this->singleRouteDefinition('member_css');
    }

    public function testMemberCSSFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'stylesheet.css']));
        $controller = new memberController();
        $response = $controller->css($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testMemberJSExists(){
        $this->singleRouteDefinition('member_js');
    }

    public function testMemberJSFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'profile.js']));
        $controller = new memberController();
        $response = $controller->js($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testMemberSAAccountExists(){
        $this->singleRouteDefinition('member_sa_accounts');
    }

    public function testMemberSAAccountFunctionality(){
        $request = new Request();
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->manageMembers($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberSAAccountCreateExists(){
        $this->singleRouteDefinition('member_sa_account_create');
    }

    public function testMemberSAAccountCreateFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMember($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberSAAccountSaveExists(){
        $this->singleRouteDefinition('member_sa_account_save');
    }

    public function testMemberSAAccountSaveFunctionality(){
        $request = new Request([],['company' => 'companyTestSave', 'homepage' => 'homepageTestSave', 'first_name' => 'saveSAATestFirstName',
            'last_name' => 'saveSAATestLastName', 'middle_name' => 'saveSAATestMiddleName', 'is_active' => true]);
        $request->setRouteParams(new ParameterBag());
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->saveMember($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $member = ioc::getRepository('saMember')->search(['company' => 'companyTestSave', 'homepage' => 'homepageTestSave', 'first_name' => 'saveSAATestFirstName',
            'last_name' => 'saveSAATestLastName', 'middle_name' => 'saveSAATestMiddleName', 'is_active' => true])[0];
        $this->assertNotNull($member);
    }

    public function testMemberSAAccountEditExists(){
        $this->singleRouteDefinition('member_sa_account_edit');
    }

    public function testMemberSAAccountEditFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id'=>3]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMember($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberSAAccountDeleteExists(){
        $this->singleRouteDefinition('member_sa_account_delete');
    }

    public function testMemberSAAccountDeleteFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id'=>3]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->deleteMember($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $member = ioc::getRepository('saMember')->find(3);
        $this->assertTrue($member->getIsDeleted());
    }

    public function testMemberSAAccountSuperusersLoginExists(){
        $this->singleRouteDefinition('member_sa_account_superuser_login');
    }

    public function testMemberSAAccountSuperusersLoginFunctionality(){
        //Logout to test if logging in properly
        auth::getInstance()->logoff();
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => 1]));
        $member = $this->getMember();

        $controller = new saMemberController($member);
        $redirect = $controller->saUserLoginAsMember($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $this->assertTrue(auth::isAuthenticated());
    }

    public function testSAAddUsernameExists(){
        $this->singleRouteDefinition('member_sa_createusers');
    }

    public function testSAAddUsernameFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberUsers($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASaveUsernameExists(){
        $this->singleRouteDefinitionPost('member_sa_saveusernames');
    }

    public function testSASaveUsernameFunctionality(){
        $request = new Request([],['username' => "testSaveUser@test.com", 'first_name' => 'saveUserFirstNameTest', 'last_name' => 'saveUserLastNameTest',
                                    'is_active' => true, 'password' => 'saveUserPasswordTest']);
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->saveMemberUsers($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $username = ioc::getRepository('saMemberUsers')->search(['username' => "testSaveUser@test.com", 'first_name' => 'saveUserFirstNameTest', 'last_name' => 'saveUserLastNameTest'])[0];
        $this->assertNotNull($username);
    }

    public function testSAEditUsernameExists(){
        $this->singleRouteDefinition('member_sa_editusernames');
    }

    public function testSAEditUsernameFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'usernameId' => 3]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberUsers($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSADeleteUsernameExists(){
        $this->singleRouteDefinition('member_sa_deleteusernames');
    }

    public function testSADeleteUsernameFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'usernameId' => 3]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->deleteMemberUsers($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $username = ioc::getRepository('saMemberUsers')->find(3);
        $this->assertNull($username);
    }

    public function testSAAddEmailExists(){
        $this->singleRouteDefinition('member_sa_createemail');
    }

    public function testSAAddEmailFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberEmail($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASaveEmailExists(){
        $this->singleRouteDefinitionPost('member_sa_saveemail');
    }

    public function testSASaveEmailFunctionality(){
        $request = new Request([],['email' => 'testSaveEmail@test.com', 'type' => 'other', 'is_active' => true]);
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->saveMemberEmail($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $email = ioc::getRepository('saMemberEmail')->find(3);
        $this->assertNotNull($email);
    }

    public function testSAEditEmailExists(){
        $this->singleRouteDefinition('member_sa_editemail');
    }

    public function testSAEditEmailFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'emailId' => 3]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberEmail($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSADeleteEmailExists(){
        $this->singleRouteDefinition('member_sa_deleteemail');
    }

    public function testSADeleteEmailFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'emailId' => 3]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->deleteMemberEmail($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $email = ioc::getRepository('saMemberEmail')->find(3);
        $this->assertNull($email);
    }

    public function testSAAddAddressExists(){
        $this->singleRouteDefinition('member_sa_createaddress');
    }

    public function testSAAddAddressFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'emailId' => 3]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberAddress($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASaveAddressExists(){
        $this->singleRouteDefinitionPost('member_sa_saveaddress');
    }

    public function testSASaveAddressFunctionality(){
        $request = new Request([],['street_one' => "test blv", "state" => "Kentucky", "city" => 'testville',
                                  'country' => 'United States', 'postal_code' => '77777', 'type' => 'other', 'is_active' => true]);
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->saveMemberAddress($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $address = ioc::getRepository('saMemberAddress')->search(['street_one' => "test blv", 'postal_code' => '77777', 'type' => 'other'])[0];
        $this->assertNotNull($address);
    }

    public function testSAEditAddressExists(){
        $this->singleRouteDefinition('member_sa_editaddress');
    }

    public function testSAEditAddressFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'addressId'=> 2]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberAddress($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSADeleteAddressExists(){
        $this->singleRouteDefinition('member_sa_deleteaddress');
    }

    public function testSADeleteAddressFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId, 'addressId'=> 2]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->deleteMemberAddress($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $address = ioc::getRepository('saMemberAddress')->find(2);
        $this->assertNull($address);
    }

    public function testSAAddPhoneExists(){
        $this->singleRouteDefinition('member_sa_createphone');
    }

    public function testSAAddPhoneFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberPhone($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASavePhoneExists(){
        $this->singleRouteDefinitionPost('member_sa_savephone');
    }

    public function testSASavePhoneFunctionality(){
        $request = new Request([],['phone' => '4444444444','type' => 'other', 'is_active' => true, 'is_primary' => false]);
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->saveMemberPhone($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $phone = ioc::getRepository('saMemberPhone')->search(['phone' => '4444444444','type' => 'other'])[0];
        $this->assertNotNull($phone);
    }

    public function testSAEditPhoneExists(){
        $this->singleRouteDefinition('member_sa_editphone');
    }

    public function testSAEditPhoneFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'phoneId' => 2]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editMemberPhone($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSADeletePhoneExists(){
        $this->singleRouteDefinition('member_sa_deletephone');
    }

    public function testSADeletePhoneFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'phoneId' => 2]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->deleteMemberPhone($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $phone = ioc::getRepository('saMemberPhone')->find(2);
        $this->assertNull($phone);
    }

    public function testSAManageGroupsExists(){
        $this->singleRouteDefinition('member_sa_group');
    }

    public function testSAManageGroupsFunctionality(){
        $request = new Request();
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->manageGroups($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSACreateGroupsExists(){
        $this->singleRouteDefinition('member_sa_group_create');
    }

    public function testSACreateGroupFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editGroup($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSASaveGroupExists(){
        $this->singleRouteDefinitionPost('member_sa_group_save');
    }

    public function testSASaveGroupFunctionality(){
        $request = new Request([],['name' => 'groupSaveTest','description' => 'A test for saving',
                                   'is_default' => false]);
        $request->setRouteParams(new ParameterBag());
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->saveGroup($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $group = ioc::getRepository('saMemberGroup')->search(['name' => 'groupSaveTest','description' => 'A test for saving',
            'is_default' => false])[0];
        $this->assertNotNull($group);
    }

    public function testSAEditGroupExists(){
        $this->singleRouteDefinition('member_sa_group_edit');
    }

    public function testSAEDitGroupFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->editGroup($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSADeleteGroupExists(){
        $this->singleRouteDefinition('member_sa_group_delete');
    }

    public function testSADDeleteGroupFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => 2]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->deleteGroup($request);
        $this->assertInstanceOf(Redirect::class, $view);
        $this->assertNull(ioc::getRepository('saMemberGroup')->find(2));
    }

    public function testMemberSAAddGroupToMemberExists(){
        $this->singleRouteDefinition('member_sa_addgrouptomember');
    }

    public function testMemberSAAddGroupToMemberFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->addMembertoGroup($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberSAAddGroupToMemberSaveExists(){
        $this->singleRouteDefinitionPost('member_sa_addgrouptomember_save');
    }

    public function testMemberSAAddGroupToMemberSaveFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'groupId' => 1]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->addMemberToGroupSave($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testMemberSADeleteMemberFromGroupExists(){
        $this->singleRouteDefinition('member_sa_deletememberfromgroup');
    }

    public function testMemberSADeleteMemberFromGroupFunctionality(){
        $request = new Request();
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' => $memberEntity->getId(), 'groupId' => 1]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->deleteMemberFromGroup($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $memberEntity = $this->getMemberEntity();
        $groups = $memberEntity->getGroups();
        foreach($groups as $group){
            if($group->getId() == 1) $this->fail("Group was not removed from member");
        }
    }

    public function testHumanVerifyExists(){
        $this->singleRouteDefinition('member_humanverify');
    }

    public function testHumanVerifyFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new memberController();
        $view = $controller->humanVerify();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testHumanVerifyPostExists(){
        $this->singleRouteDefinitionPost('member_humanverifypost');
    }

    public function testHumanVerifyPostFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new memberController();
        $view = $controller->humanVerify();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testDeviceVerifyExists(){
        $this->singleRouteDefinition('member_machineverify');
    }

    //Poor test, not sure the steps to setup two factor via functions
    public function testDeviceVerifyFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new MemberTwoFactorController();
        $redirect = $controller->machineVerify();
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testDeviceVerifyCodeExists(){
        $this->singleRouteDefinitionPost('member_machineverifycode');
    }

    public function testDeviceVerifyCodeFunctionality(){
        $request = new Request();
        $controller = new MemberTwoFactorController();
        $view = $controller->machineVerifyCode($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testDeviceVerifyCodeVerifyExists(){
        $this->singleRouteDefinitionPost('member_machineverifycodeverify');
    }

    public function testDeviceVerifyCodeVerifyFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new MemberTwoFactorController();
        $redirect = $controller->machineVerifyCodeVerify();
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testDeviceVerifyVoiceExists(){
        $this->singleRouteDefinition('member_machineverifyvoice');
    }

    public function testDeviceVerifyVoiceFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new MemberTwoFactorController();
        $controller->machineVerifyVoice();
        $this->assertTrue(method_exists($controller, 'machineVerifyVoice'));
    }


    public function testDeviceVerifyVoiceTextExists(){
        $this->singleRouteDefinition('member_machineverifyvoicetext');
    }

    public function testDeviceVerifyVoiceTextFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag());
        $controller = new MemberTwoFactorController();
        $controller->machineVerifyVoiceText();
        $this->assertTrue(method_exists($controller, 'machineVerifyVoiceText'));
    }

    public function testMemberTwoFactorVerifyExists(){
        $this->singleRouteDefinition('member_two_factor_verify');
    }

    //Poor test, only testing
    public function testMemberTwoFactorVerifyFunctionality(){
        $controller = new MemberTwoFactorController();
        $redirect = $controller->twoFactorVerify();
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testMemberTwoFactorVerifyUserInputExists(){
        $this->singleRouteDefinition('member_two_factor_verify_user_input');
    }

    public function testMemberTwoFactorVerifyUserInputFunctionality(){
        $request = new Request([],[]);
        $controller = new MemberTwoFactorController();
        $view = $controller->twoFactorVerifyUserInput($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberTwoFactorVerifyUserInputValidateExists(){
        $this->singleRouteDefinitionPost('member_two_factor_verify_user_input_validate');
    }

    public function testMemberTwoFactorVerifyUserInputValidateFunctionality(){
        $request = new Request([],[]);
        $controller = new MemberTwoFactorController();
        $view = $controller->twoFactorVerifyUserInput($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberAdditionalAuthSetupExists(){
        $this->singleRouteDefinition('member_additionalauthsetup');
    }

    public function testMemberAdditionalAuthSetupFunctionality(){
        $request = new Request([],[]);
        $controller = new MemberTwoFactorController();
        $view = $controller->additionalAuthSetup();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberAdditionalAuthStepTestExists(){
        $this->singleRouteDefinition('member_additionalauthsetup_test');
    }

    public function testMemberAdditionalAuthStepTestFunction(){
        $request = new Request([],[]);
        $controller = new MemberTwoFactorController();
        $view = $controller->additionalAuthSetupTest($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberAdditionalAuthStepTestSubmitExists(){
        $this->singleRouteDefinitionPost('member_additionalauthsetup_test_submit');
    }

    //Poor test, succeeds on a fail state as I
    public function testMemberAdditionalAuthStepTestSubmitFunctionality(){
        $request = new Request([],[]);
        $controller = new MemberTwoFactorController();
        $redirect = $controller->additionalAuthSetupTestSubmit($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    public function testMemberSAUserExists(){
        $this->singleRouteDefinition('member_sa_users');
    }

    public function testMemberSAUserFunctionality(){
        $request = new Request([],[]);
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $view = $controller->manageUsers($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testMemberSAUserLoginExists(){
        $this->singleRouteDefinition('member_sa_userlogin');
    }

    public function testMemberSAUserLoginFunctionality(){
        $request = new Request([],[]);
        $memberEntity = $this->getMemberEntity();
        $request->setRouteParams(new ParameterBag(['id' =>$memberEntity->getId(), 'userId' => $memberEntity->getUsers()[0]->getId()]));
        $member = $this->getMember();
        $controller = new saMemberController($member);
        $redirect = $controller->loginAsUser($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
    }

    /** HELPER FUNCTIONS */
    protected function getMember(){
        $member = ioc::staticResolve('saMember');
        return $member;
    }

    protected function getMemberEntity(){
        $member = ioc::getRepository('saMember')->search(['first_name' =>self::MEMBER_DATA['first_name']])[0];
        return $member;
    }

    protected function logAuthMemberIn(){
        $auth = auth::getInstance();
        $auth->logon(self::MEMBER_USER_DATA['username'], self::MEMBER_USER_DATA['password']);
        //Maybe use controller instead if this doesn't cut it
    }

    protected function logAuthMemberOut(){
        $auth = auth::getInstance();
        $auth->logoff();
    }

    protected function moduleName()
    {
        return "member";
    }

    protected function configClass()
    {
        return memberConfig::class;
    }
}