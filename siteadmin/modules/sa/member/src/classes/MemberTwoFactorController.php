<?php

namespace sa\member;

use Captcha\Captcha;
use Doctrine\Common\Util\Debug;
use Johnstyle\GoogleAuthenticator\GoogleAuthenticator;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\Redirect;
use sacore\utilities\Cookie;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use sacore\utilities\url;

class MemberTwoFactorController extends \sacore\application\controller {

    /**
     * Get the default resources for the module
     *
     * @author David Worley
     * @copyright Site Administrator
     * @since 2.00.0
     * @return  string
     */
    static function getDefaultResources()
    {
        return array(
            array('type'=>'css', 'path'=> app::get()->getRouter()->generate('member_css', ['file' => 'stylesheet.css']) )
        );
    }

    public function machineVerifyCode($request)
    {
        //Was a property, not sure if this is how this function should get the variable but the best I got right now
        $google_auth = $request->request->get('google_auth');
        if(is_null($google_auth))$google_auth = false;
        /** @var saMember $member */
        $user = modRequest::request('auth.user');

        /** @var saMemberDevice $saMemberDevices */
        $saMemberDevices = ioc::staticResolve('saMemberDevice');

        $now = new DateTime();
        $timeSinceSent = 0;
        if (isset($_SESSION['device_verify_code_time'])) {
            $interval = $now->diff($_SESSION['device_verify_code_time']);
            $timeSinceSent = $interval->days * 24 * 60;
            $timeSinceSent += $interval->h * 60;
            $timeSinceSent += $interval->i;
        }

        if ($request->request->get('phoneid')=='google_auth' || $google_auth) {
            $saMemberDevices::issueVerificationCode(Cookie::getCookie('machine_uuid'), $user, null, true);
        }
        else if ( !isset($_SESSION['device_verify_code_time']) || $timeSinceSent > 10 ) {

            $saMemberDevices::issueVerificationCode(Cookie::getCookie('machine_uuid'), $user, $request->request->get('phoneid'));
            $_SESSION['device_verify_code_time'] = $now;

        }

        $view = new \sacore\application\responses\View('verifymachinecode', $this->viewLocation() );
        return $view;
    }

    public function machineVerifyCodeVerify()
    {
        /** @var saMember $member */
        $user = modRequest::request('auth.user');

        /** @var saMemberDevice $saMemberDevices */
        $saMemberDevices = ioc::staticResolve('saMemberDevice');
        $result = $saMemberDevices::checkVerificationCode(Cookie::getCookie('machine_uuid'), $user, $_POST['code'], $_POST['description']);

        if ($result)
        {
            unset($_SESSION['device_verify_code_time']);
            return new Redirect( url::make('dashboard_home') );

        }
        else
        {
            $notify = new notification();
            $notify->addNotification('danger', 'Error', 'An error occurred while validating your code.' );
            return $this->machineVerify();
        }
    }



    public function machineVerify()
    {
        /** @var saMember $member */
        $member = modRequest::request('auth.member');

        /** @var saMemberUsers $user */
        $user = modRequest::request('auth.user');

        if (!$user || !$member) {
            return $this->error500('A user or member was not found.', true);
        }


        $usePhone = app::get()->getConfiguration()->get('member_two_factor_use_phone', true)->getValue();

        $mobile = array();
        $other = array();

        if ($usePhone) {
            $phones = $member->getPhones();
            /** @var saMemberPhone $phone */
            foreach ($phones as $phone) {
                if ($phone->getType() == strtolower('mobile')) {
                    $mobile[] = doctrineUtils::getEntityArray($phone);
                } else {
                    $other[] = doctrineUtils::getEntityArray($phone);
                }
            }
        }

        if ( !$user->getIsTwoFactorSetup() ) {

            return new Redirect(app::get()->getRouter()->generate('member_additionalauthsetup'));

        }
        else if (count($mobile)==0 && count($other)==0 && $user->getGoogleAuthenticatorKey()) {

            return $this->machineVerifyCode(true);
        }
        else {

            $view = new \sacore\application\responses\View('verifymachine', $this->viewLocation());
            $view->data['mobile'] = $mobile;
            $view->data['other'] = $other;
            $view->data['google_auth'] = $user->getGoogleAuthenticatorKey() ? true : false;
            return $view;

        }
    }

    ############################ TWO FACTOR ###################################


    public function twoFactorVerify() {

        /** @var saMember $member */
        $member = modRequest::request('auth.member');

        /** @var saMemberUsers $user */
        $user = modRequest::request('auth.user');

        if (!$user || !$member) {
            return $this->error500('A user or member was not found.', true);
        }

        $usePhone = app::get()->getConfiguration()->get('member_two_factor_use_phone', true)->getValue();

        $mobile = array();
        $other = array();

        if ($usePhone) {
            $phones = $member->getPhones();
            /** @var saMemberPhone $phone */
            foreach ($phones as $phone) {
                if ($phone->getType() == strtolower('mobile')) {
                    $mobile[] = doctrineUtils::getEntityArray($phone);
                } else {
                    $other[] = doctrineUtils::getEntityArray($phone);
                }
            }
        }

        if ( !$user->getIsTwoFactorSetup() ) {

            return new Redirect(app::get()->getRouter()->generate('member_additionalauthsetup'));

        }
        else if (count($mobile)==0 && count($other)==0 && $user->getGoogleAuthenticatorKey()) {

            return $this->twoFactorVerifyUserInput(true);
        }
        else {

            $view = new \sacore\application\responses\View('twofactorauth', $this->viewLocation());
            $view->data['mobile'] = $mobile;
            $view->data['other'] = $other;
            $view->data['google_auth'] = $user->getGoogleAuthenticatorKey() ? true : false;
            return $view;

        }

    }

    public function twoFactorVerifyUserInput($request)
    {
        /** @var auth $auth */
        $auth = ioc::staticGet('auth');
        $auth = $auth::getInstance();


        if ($request->request->get('phoneid') && $request->request->get('phoneid')!='google_auth') {
            $auth->issueTwoFactorSMSCallCode($request->request->get('phoneid'));
        }
        elseif ($request->request->get('phoneid')=='google_auth')
        {
            $auth->revokeTwoFactorSMSCallCode();
        }

        $view = new \sacore\application\responses\View('twofactorauthcode', $this->viewLocation() );
        $view->data['method'] = $auth->getTwoFactorValidationMethod();
        return $view;
    }

    public function twoFactorVerifyUserInputValidate($request)
    {

        /** @var auth $auth */
        $auth = ioc::staticGet('auth');
        $auth = $auth::getInstance();

        $validated = $auth->validateTwoFactorCode($request->request->get('code'));

        if ($validated) {
            return new Redirect( app::get()->getRouter()->generate('dashboard_home') );
        }
        else
        {
            $notify = new notification();
            $notify->addNotification('danger', 'Error', 'An error occurred while validating your code.' );
            return new Redirect( app::get()->getRouter()->generate('member_two_factor_verify_user_input') );
        }

    }



    ############################ AUTH SETUP ###################################

    public function additionalAuthSetup() {

        $view = new \sacore\application\responses\View('additional_auth_setup', $this->viewLocation());

        $view->data['usePhone'] = app::get()->getConfiguration()->get('member_two_factor_use_phone', true)->getValue();

        $google = new GoogleAuthenticator();
        $secretKey = $google->getSecretKey();

        $hostname = app::get()->getConfiguration()->get('site_url')->getValue();
        $hostname = preg_replace('/http:\/\/|http:\/\//', '', $hostname);

        $view->data['code'] = $secretKey;
        $view->data['qr_image'] = $google->getQRCodeUrl($hostname);

        return $view;

    }

    public function additionalAuthSetupTest($request) {

        $site_name = app::get()->getConfiguration()->get('site_name', true)->getValue();


        $view = new \sacore\application\responses\View('additional_auth_setup_test', $this->viewLocation());

        $view->data['method'] = $request->request->get('method_type');

        if ($view->data['method']=='ga') {
            $view->data['verification_data'] = $request->request->get('secret_code');
        }
        elseif ($view->data['method']=='sms') {
            $view->data['verification_data'] = preg_replace('/[^0-9]/', '', $request->request->get('sms_phone'));

            $now = new DateTime();
            $timeSinceSent = 0;
            if (isset($_SESSION['two_factor_test_code_time'])) {
                $interval = $now->diff($_SESSION['two_factor_test_code_time']);
                $timeSinceSent = $interval->days * 24 * 60;
                $timeSinceSent += $interval->h * 60;
                $timeSinceSent += $interval->i;
            }

            if ( !isset($_SESSION['two_factor_test_code_time']) || $timeSinceSent > 10 ) {
                $_SESSION['two_factor_test_code'] = rand(100000, 999999);
                modRequest::request('messages.sendSMS', '0',
                    array(
                        'phone' => preg_replace('/[^0-9]/', '', $view->data['verification_data']),
                        'body' => 'Your ' . $site_name . ' security code is: ' . $_SESSION['two_factor_test_code']
                    )
                );
                $_SESSION['two_factor_test_code_time'] = $now;
            }

        }
        elseif ($view->data['method']=='call') {
            $view->data['verification_data'] = preg_replace('/[^0-9]/', '', $request->request->get('call_phone'));

            $now = new DateTime();
            $timeSinceSent = 0;
            if (isset($_SESSION['two_factor_test_code_time'])) {
                $interval = $now->diff($_SESSION['two_factor_test_code_time']);
                $timeSinceSent = $interval->days * 24 * 60;
                $timeSinceSent += $interval->h * 60;
                $timeSinceSent += $interval->i;
            }

            if ( !isset($_SESSION['two_factor_test_code_time']) || $timeSinceSent > 10 ) {
                $_SESSION['two_factor_test_code'] = rand(100000, 999999);
                modRequest::request('messages.sendVoice', '0', array(
                        'phone' => preg_replace('/[^0-9]/', '', $view->data['verification_data']),
                        'body' => '{p|2} Hello {p|1} Thank You for using ' . $site_name . '. {p|1} Your security code is: {spell|' . $_SESSION['two_factor_test_code'] . '}. {p|1} Your security code is: {spell|' . $_SESSION['two_factor_test_code'] . '}. {p|1} Good Bye. '
                    )
                );
                $_SESSION['two_factor_test_code_time'] = $now;
            }
        }

        if (!$request->request->get('method_type')) {
            $view->data['verification_data'] = $_SESSION['verification_data'];
            $view->data['method'] = $_SESSION['method'];
        }
        else
        {
            $_SESSION['verification_data'] = $view->data['verification_data'];
            $_SESSION['method'] = $view->data['method'];
        }

        return $view;

    }

    public function additionalAuthSetupTestSubmit($request) {

        $notification = new notification();

        /** @var saMember $member */
        $member = modRequest::request('auth.member');

        /** @var saMemberUsers $user */
        $user = modRequest::request('auth.user');

        if (!$user || !$member) {
            return $this->error500('A user or member was not found.', true);
        }

        unset($_SESSION['two_factor_test_code_time']);

        if ($request->request->get('verification_method')=='sms' || $request->request->get('verification_method')=='call') {
            if ($_SESSION['two_factor_test_code']==$request->request->get('verification_code')) {
                /** @var saMemberPhone $phone */
                $phone = ioc::get('saMemberPhone', array('member'=>$member, 'phone'=>$request->request->get('verification_data'), 'type'=>($request->request->get('verification_method')=='sms' ? 'mobile' : 'personal')) );

                $user->setIsTwoFactorSetup(true);
                if (!$phone) {
                    $phone = ioc::get('saMemberPhone');
                    $phone->setMember($member);
                    $phone->setType( ($request->request->get('verification_method')=='sms' ? 'mobile' : 'personal') );
                    $phone->setPhone( $request->request->get('verification_data'));
                    $phone->setIsPrimary(false);
                }

                $phone->setIsActive(true);
                $phone->setUser($user);
                $user->addPhone($phone);
                app::$entityManager->persist($phone);
                app::$entityManager->flush();

                $notification->addNotification('success', 'Success', 'The additional authentication method has been successfully setup.');
                $view = new Redirect( '/dashboard' );
            }
            else
            {
                $notification->addNotification('error', 'Error','The authentication code did not match.');
                $view = new Redirect( app::get()->getRouter()->generate('member_additionalauthsetup_test'));
            }
        }
        else
        {
            $google = new GoogleAuthenticator($request->request->get('verification_data'));

            if ($google->verifyCode($request->request->get('verification_code'), 5)) {

                $user->setIsTwoFactorSetup(true);
                $user->setGoogleAuthenticatorKey($request->request->get('verification_data'));
                app::$entityManager->flush($user);

                $notification->addNotification('success', 'Success', 'The additional authentication method has been successfully setup.');
                $view = new Redirect( '/dashboard' );
            }
            else
            {
                $notification->addNotification('error', 'Error', 'The authentication code did not match.');
                $view = new Redirect( app::get()->getRouter()->generate('member_additionalauthsetup_test'));
            }
        }
        return $view;

    }
}