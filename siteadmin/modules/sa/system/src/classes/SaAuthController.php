<?php
namespace sa\system;

use Captcha\Captcha;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Johnstyle\GoogleAuthenticator\GoogleAuthenticator;
use \sacore\application\app;
use sacore\application\Event;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\File;
use sacore\application\responses\Redirect;
use sacore\application\responses\ResponseUtils;
use \sacore\application\saController;
use \sacore\application\modelResult;
use sacore\application\ValidateException;
use \sacore\application\responses\View;
use sacore\application\ViewException;
use sacore\utilities\doctrineUtils;
use sacore\application\DateTime;

use \sacore\utilities\url;
use \sacore\utilities\notification;
use sacore\utilities\UserAgent;


class SaAuthController extends saController {


    /**
     * @param Request $request
     * @return Redirect|View|string
     * @throws \Exception
     */
    public function attemptLogin($request)
    {

        try {
            $result = saAuth::getInstance()->login( $request->get('username'), $request->get('password') );
        }
        catch( TableNotFoundException $e ) {
            $log = doctrineUtils::updateSchema();
            return '<h1>Database Create/Repair</h1><br />This may be the first install and if it is then great!!! <br/><br/> <pre>'.print_r($log, true).'</pre>';
        }


        if ($result)
        {
            $login_redirect = (isset($_SESSION['sa_login_redirect'])) ? $_SESSION['sa_login_redirect'] : app::get()->getRouter()->generate('sa_dashboard');
            $_SESSION['invalidsaLoginAttempts'] = 0;


            BruteForceManager::forgive($request->getClientIp());

            return new Redirect( $login_redirect );
        }
        else
        {
            if (empty($_SESSION['invalidsaLoginAttempts']))
                $_SESSION['invalidsaLoginAttempts'] = 0;

            $_SESSION['invalidsaLoginAttempts']++;

            BruteForceManager::track($request->getClientIp());
            return $this->login($request);
        }
    }

    /**
     * @param Request $request
     * @return Redirect|View
     * @throws ViewException
     */
    public function login($request)
    {

        if (saAuth::getInstance()->isAuthenticated()) {
            return new Redirect( app::get()->getRouter()->generate('sa_dashboard') );
        }

        if ($_SESSION['invalidsaLoginAttempts'] > 3)
        {
            if( empty(app::get()->getConfiguration()->get('recaptcha_private')->getValue() ) || empty( app::get()->getConfiguration()->get('recaptcha_public')->getValue() ) ) {

                $view = new View(false, 'login_recaptcha_error');
                $said = app::get()->getConfiguration()->get('siteadmin_login_image_id')->getValue();
                if(!empty($said )) {
                    /** @var saImage $image */
                    $image = app::$entityManager->getRepository( ioc::staticGet('saImage') )->findOneBy(array('id' => $said ));

                    if($image) {
                        $view->data['site_img'] = $image->getId();
                    }
                }

                return $view;

            } else {
                $path = app::get()->getRouter()->generate('sa_humanverify');
                return new Redirect($path);
            }
        }

        $data = array();
        if ($passeddata)
        {
            $data = $passeddata;
            $data['error'] = true;
        }
        else
        {
            $data['error'] = false;
        }

        //$_SESSION['sa_login_redirect'] = (url::uri()==url::make('sa_login')) ? url::make('sa_dashboard') : url::uri();

        $view = new View('login_header,login,login_footer', $this->viewLocation() );
        $view->data = ['username'=>$request->get('username'), 'post_action'=>app::get()->getRouter()->generate('sa_loginattempt')];
        $view->data['siteadmin_login_image_id'] = app::get()->getConfiguration()->get('siteadmin_login_image_id')->getValue() ? app::get()->getConfiguration()->get('siteadmin_login_image_id')->getValue() : null;
        $view->data['siteadmin_login_bg'] = app::get()->getConfiguration()->get('siteadmin_login_bg')->getValue() ? app::get()->getConfiguration()->get('siteadmin_login_bg')->getValue() : null;
        $view->addMetaTag('test', 'test');
        $view->addMetaTag('test', 'viewport');


        return $view;
    }


    public function logoff()
    {
        $auth = saAuth::getInstance();
        $auth->logoff();

        return new Redirect(app::get()->getRouter()->generate('sa_login'));
    }

    public function humanVerify($request)
    {
        $captcha = new Captcha();
        $captcha->setPublicKey(app::get()->getConfiguration()->get('recaptcha_public'));
        $captcha->setPrivateKey(app::get()->getConfiguration()->get('recaptcha_private'));
        $captcha->setRemoteIp($request->getClientIp());

        $view = new View('humanverify', $this->viewLocation() );
        $view->setXSSSanitation(false);
        $view->data['recaptchaHTML'] = $captcha->html();
        return $view;
    }

    /**
     * @param Request $request
     * @return Redirect|View
     * @throws \Captcha\Exception
     */
    public function humanVerifyAttempt($request)
    {
        $captcha = new Captcha();
        $captcha->setPrivateKey(app::get()->getConfiguration()->get('recaptcha_private')->getValue());
        $captcha->setPublicKey(app::get()->getConfiguration()->get('recaptcha_public')->getValue());
        $captcha->setRemoteIp($request->getClientIp());

        $response = $captcha->check($request->get('g-recaptcha-response'));


        if ($response->isValid())
        {
            $_SESSION['invalidsaLoginAttempts'] = 0;
            return new Redirect( app::get()->getRouter()->generate('sa_login') );
        }
        else
        {
            $notify = new notification();
            $notify->addNotification('danger', 'Error', 'Invalid Code. Please try again.');
            return $this->humanVerify($request);
        }
    }


    /*****************************************    TESTED TO THIS POINT ************************************/

    public static function extendSession() {
        $auth = saAuth::getInstance();
        $auth->extendSession();
    }

    public static function logoffSession() {
        $auth = saAuth::getInstance();
        $auth->logoff();
    }

    public function ajaxSendTwoFactorVerifyCode() {

        $user = saAuth::getInstance()->getAuthUser();
        $code = rand(10000, 99999);
        $_SESSION['sa_login_two_factor_code'] = $code;
        $sms = modRequest::request('messages.sendSMS', '0',
            array(
                'phone' => preg_replace('/[^0-9]/', '', $user->getCellNumber()),
                'body' => 'Your Site Administrator security code is: ' . $code
            )
        );

    }


    public function ajaxSendVerifyCode()
    {
        $user = saAuth::getInstance()->getAuthUser();

        /** @var saUserDevice $saUserDevice */
        $saUserDevice = ioc::staticResolve('saUserDevice');
        /** @var saUserDevice $code */
        $code = $saUserDevice::issueVerificationCode( $saUserDevice::TYPE_SMS, saAuth::getMachineUUID(), $user);

        if ($code)
            return $code->getId();
        else
            return false;

    }

    public function ajaxCheckVerifyCodeStatus($data) {

        /** @var saUserDevice $saUserDevice */
        $saUserDevice = ioc::get('saUserDevice', $data['id']);
        if ($saUserDevice) {
            return $saUserDevice->getSmsMessage()->getStatus();
        }

        return null;

    }

    public function ajaxIssueGAUserSecretCode($data)
    {
        /** @var saUser $user */
        $user = ioc::get('saUser', $data['id']);
        $user->reissueGoogleAuthSecret();
        app::$entityManager->flush($user);

        return array('qr_image'=>$user->getGoogleAuthQRCode(), 'code'=>$user->getGoogleAuthSecret());

    }

    public function ajaxVerifyCode($data)
    {
        $user = saAuth::getInstance()->getAuthUser();

        /** @var saUserDevice $saUserDevice */
        $saUserDevice = ioc::staticResolve('saUserDevice');
        /** @var saUserDevice $code */
        $sa_device_verify_method = app::get()->getConfiguration()->get('sa_device_verify_method')->getValue();
        if ($sa_device_verify_method=='SMS') {
            $status = $saUserDevice::checkVerificationCode($saUserDevice::TYPE_SMS, saAuth::getMachineUUID(), $user, $data['code'], $_SERVER['HTTP_USER_AGENT']);
        }
        else if ($sa_device_verify_method=='Google Authenticator')
        {
            $status = $saUserDevice::checkVerificationCode($saUserDevice::TYPE_GOOGLE_AUTHENTICATOR, saAuth::getMachineUUID(), $user, $data['code'], $_SERVER['HTTP_USER_AGENT']);
        }

        return $status;
    }

    public function twoFactorVerifyCode($data) {

        $user = saAuth::getInstance()->getAuthUser();

        $sa_device_verify_method = app::get()->getConfiguration()->get('sa_device_verify_method')->getValue();
        if ($sa_device_verify_method=='SMS') {

            if ( $_SESSION['sa_login_two_factor_code'] != $data['code'] ) {
                $_SESSION['sa_login_two_factor_verified'] = false;
                unset($_SESSION['sa_login_two_factor_code']);
                return false;
            }

            $_SESSION['sa_login_two_factor_verified'] = true;
            return true;

        }
        else if ($sa_device_verify_method=='Google Authenticator')
        {

            $secret = $user->getGoogleAuthSecret();
            if (!$secret) {
                $_SESSION['sa_login_two_factor_verified'] = false;
                return false;
            }

            $google = new GoogleAuthenticator($secret);
            $c = $google->getCode();
            if ( !$google->verifyCode($data['code'], 5) ) {
                $_SESSION['sa_login_two_factor_verified'] = false;
                return false;
            }

            $_SESSION['sa_login_two_factor_verified'] = true;
            return true;

        }

    }

    public function twoFactorVerify() {

        $user = saAuth::getInstance()->getAuthUser();

        $sa_login_two_factor_method = app::get()->getConfiguration()->get('sa_login_two_factor_method')->getValue();


        if ($sa_login_two_factor_method=='SMS') {

            if ($user->getCellNumber()) {
                $view = new View( 'login_header,twofactor_verify,login_footer', $this->viewLocation());
                $view->data['mobile'] = $user->getCellNumber();
                $view->data['type'] = 'SMS';
                return $view;
            }
            else {
                $view = new View('login_header,verifymachineerror,login_footer', $this->viewLocation());
                $view->data['mobile'] = $user->getCellNumber();
                return $view;
            }

        }
        else if ($sa_login_two_factor_method=='Google Authenticator')
        {
            /** @var saUserDevice $saUserDevice */
            $saUserDevice = ioc::staticResolve('saUserDevice');
            $code = $saUserDevice::issueVerificationCode( $saUserDevice::TYPE_GOOGLE_AUTHENTICATOR, saAuth::getMachineUUID(), $user);

            $view = new View( 'login_header,twofactor_verify,login_footer', $this->viewLocation());
            $view->data['type'] = 'GA';
            return $view;
        }

    }

    public function loginLocationRestricted()
    {
        $view = new View('login_header,login_location_restricted,login_footer', static::viewLocation());
        $view->data['ip_address'] = systemController::get_client_ip();

        return $view;
    }

    public function machineVerify()
    {
        $user = saAuth::getInstance()->getAuthUser();
        $sa_device_verify_method = app::get()->getConfiguration()->get('sa_device_verify_method')->getValue();

        if ($sa_device_verify_method=='SMS') {

            if ($user->getCellNumber()) {
                $view = new View(null, 'login_header,verifymachine,login_footer', $this->viewLocation());
                $view->data['mobile'] = $user->getCellNumber();
                $view->data['type'] = 'SMS';
                return $view;
            }
            else {
                $view = new View(null, 'login_header,verifymachineerror,login_footer', $this->viewLocation());
                $view->data['mobile'] = $user->getCellNumber();
                return $view;
            }

        }
        else if ($sa_device_verify_method='Google Authenticator')
        {
            /** @var saUserDevice $saUserDevice */
            $saUserDevice = ioc::staticResolve('saUserDevice');
            $code = $saUserDevice::issueVerificationCode( $saUserDevice::TYPE_GOOGLE_AUTHENTICATOR, saAuth::getMachineUUID(), $user);

            $view = new View(null, 'login_header,verifymachine,login_footer', $this->viewLocation());
            $view->data['type'] = 'GA';
            return $view;
        }
    }
}