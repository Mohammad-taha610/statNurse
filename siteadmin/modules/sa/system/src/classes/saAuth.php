<?php
namespace sa\system;

use sacore\application\app;
use sacore\application\controller;
use sacore\application\DateTime;
use sacore\application\Event;
use sacore\application\ioc;
use sacore\application\Middleware;
use sacore\application\modelResult;
use sacore\application\route;
use sacore\utilities\Cookie;
use \sacore\utilities\mcrypt;
use \sacore\utilities\url;

class saAuth
{
    private $authenticated = false;
    private $sessiontimeout = 1200;
    private $user_id = false;
    private $member_id = false;
    private $last_activity = false;
    private $ip_address = false;
    private $token = false;
    private $machine_uuid = false;
    private $machine_verified = false;
    private $type = false;
    protected $login_key = null;

    public function __construct()
    {
        $_SESSION['saAuthContext'] = $this;
        if (app::get()->getConfiguration()->get('sa_session_timeout')->getValue()) {
            $this->sessiontimeout = app::get()->getConfiguration()->get('sa_session_timeout')->getValue();
        }
        $this->machine_uuid = static::getMachineUUID();
    }



//    /**
//     * @param Event $event
//     * @return void
//     * @throws \sacore\application\IocDuplicateClassException
//     * @throws \sacore\application\IocException
//     */
//    public static function isAllowedToRunRoute(Event $event)
//    {
//        $auth = saAuth::getInstance();
//
//        /** @var route $routeInfo */
//        $routeInfo = $event->getData('routeInfo');
//        $allowed = $event->getData('allowed');
//
//        if ($routeInfo->protected && $routeInfo->getRouteType()=='sacore\application\saRoute') {
//            $route_permissions = $routeInfo->getRoutePermissions();
//
//            $app = app::getInstance();
//
//            if (!$auth->isAuthenticated()) {
//                $routeInfo = $app->findRoute($routeInfo->protected_login_route);
//                $allowed = false;
//            } elseif ( count($route_permissions)>0 ) {
//
//                /** @var saUser $user */
//                $user = $auth->getAuthUser();
//
//                if ( $user->getUserType()==$user::TYPE_DEVELOPER ) {
//
//                } elseif ( $user->getUserType()==$user::TYPE_SUPER_USER ) {
//
//                    if (in_array('developer', $route_permissions)) {
//                        $routeInfo = $app->findRouteById('sa_permission_denied');
//                        $allowed = false;
//                    }
//                } else {
//                    $module = $routeInfo->getModule(false);
//                    $user_permissions = $user->getPermissions();
//
//                    $user_permissions[ $module ] = isset( $user_permissions[ $module ] ) ? $user_permissions[ $module ] : array();
//
//                    $matches = array_intersect_key( array_flip($route_permissions), $user_permissions[ $module ] );
//
//                    if ( count($matches)==0 ) {
//                        $routeInfo = $app->findRouteById('sa_permission_denied');
//                        $allowed = false;
//                    }
//
//                    if ( in_array('developer', $route_permissions) && $allowed ) {
//                        $routeInfo = $app->findRouteById('sa_permission_denied');
//                        $allowed = false;
//                    }
//                }
//            }
//
//            $sa_login_two_factor = false;
//            if (app::get()->getConfiguration()->get('sa_login_two_factor')->getValue())
//                $sa_login_two_factor = app::get()->getConfiguration()->get('sa_login_two_factor')->getValue();
//
//            if ( $allowed && $sa_login_two_factor && $routeInfo->id!='sa_two_factor_verify' && $routeInfo->id!='sa_logoff' )
//            {
//                if ( !isset( $_SESSION['sa_login_two_factor_verified'] ) )
//                    $_SESSION['sa_login_two_factor_verified'] = false;
//
//                $verified = $_SESSION['sa_login_two_factor_verified'];
//                if (!$verified)
//                {
//                    $routeInfo = $app->findRouteById( 'sa_two_factor_verify' );
//                    $allowed = false;
//                }
//            }
//
//            $deviceVerify = false;
//            if (app::get()->getConfiguration()->get('sa_device_verify')->getValue())
//                $deviceVerify = app::get()->getConfiguration()->get('sa_device_verify')->getValue();
//
//            if ( $routeInfo->machine_protected && $allowed && $deviceVerify && !$sa_login_two_factor && $routeInfo->id!='sa_machineverifycode' && $routeInfo->id!='sa_logoff' )
//            {
//                /** @var saUserDevice $saUserDevice */
//                $saUserDevice = ioc::resolve('saUserDevice');
//                $verified = $saUserDevice::isDeviceVerified(static::getMachineUUID(), $auth->getAuthUser() );
//                if (!$verified)
//                {
//                    $routeInfo = $app->findRouteById( 'sa_machineverify' );
//                    $allowed = false;
//                }
//            }
//
//            /** @var saUser $user */
//            $user = $auth->getAuthUser();
//
//            if($user && $user->getUserType() != saUser::TYPE_SUPER_USER && $user->getisLocationRestricted()) {
//                $userIp = systemController::get_client_ip();
//
//                if(!in_array($userIp, $user->getAllowedLoginLocations())) {
//                    $allowed = false;
//                    $routeInfo = $app->findRouteById('sa_location_blocked');
//                }
//            }
//        }
//
//        $event->setData('routeInfo', $routeInfo);
//        $event->setData('allowed', $allowed);
//    }

    public static function getMachineUUID() {

        if ( empty( Cookie::getCookie('machine_uuid') ) ) {
            $uuid = static::guid();
//            setcookie('machine_uuid', $uuid, time()+31536000,'/');
            Cookie::setCookie('machine_uuid', $uuid, time()+31536000,'/');
        }
        else {
            $uuid = Cookie::getCookie('machine_uuid');
        }

        return $uuid;
    }

//    // WEB LOGIN
//    public static function webLogin($username, $password)
//    {
//        $auth = saAuth::getInstance();
//        $result = $auth->login($username, $password);
//
//        $_SESSION['canAccessRoute'] = array();
//
//        if (empty($_SESSION['invalidLogins'])) {
//            $_SESSION['invalidLogins'] = 0;
//        }
//
//        if ($result) {
//            $user = $auth->getAuthUser();
//            $_SESSION['invalidLogins'] = 0;
//
//            return new modelResult(array('status'=>modelResult::STATUS_SUCCESS));
//        } else {
//            $_SESSION['invalidLogins']++;
//
//            return new modelResult(array('status'=>modelResult::STATUS_FAIL, 'messages'=>'Invalid Username or Password.'));
//        }
//    }


    // AUTH FUNCTIONS *********************************************************************************************
    public static function getInstance()
    {
        return isset($_SESSION['saAuthContext']) ? $_SESSION['saAuthContext'] : new saAuth();
    }

    public function isAuthenticated($checkSession=true)
    {
        if ($checkSession) {
            $this->checksession();
        }

        return $this->authenticated;
    }

    public static function getAuthUser()
    {
        $auth = static::getInstance();
        if (!$auth->isAuthenticated()) {
//            var_export($_SESSION['saAuthContext']);
//            die();
            return null;
        }

        if ($auth->user_id>0) {
            $user = ioc::get('saUser', $auth->user_id );
        }
        return $user;
    }

    public static function getAuthUserId()
    {
        $auth = static::getInstance();
        if (!$auth->isAuthenticated()) {
            return null;
        }

        return $auth->user_id;
    }

    public static function getAuthType() {

        $auth = static::getInstance();
        if (!$auth->isAuthenticated()) {
            return null;
        }

        return $auth->type;
    }

    /* MAIN LOGIN FUNCTION THAT HANDLES BOTH THE API AND THE WEB LOGIN */
    public function login($username=false, $pass=false, $login_key = null, $login_key_type = null)
    {
        if(!empty($login_key)) {
            /** @var SaUserLoginKey $key */
            $key = app::$entityManager->getRepository( ioc::staticGet('SaUserLoginKey') )->findOneBy(array('uuid' => $login_key, 'revoked' => false, 'type' => $login_key_type));

            if(!$key) {
                return false;
            }

            return true;
        } else {
//            setcookie("sa_session_length", $this->sessiontimeout);
            Cookie::setCookie("sa_session_length", $this->sessiontimeout);
            $authStatus = $this->localLogin($username, $pass);

            if (!$authStatus) {
                $authStatus = $this->remoteLogin($username, $pass);
            }

            if (!$authStatus) {
                /** @var saUserLoginActivity $activity */
                $activity = ioc::get('saUserLoginActivity');
                $activity->setIpAddress(static::get_client_ip());
                $activity->setDate(new DateTime());
                $activity->setMachineUuid(static::getMachineUUID());
                $activity->setWasSuccess(false);
                $activity->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                app::$entityManager->persist($activity);
                app::$entityManager->flush($activity);
            }


            return $authStatus;
        }
    }

    private function remoteLogin($username=false, $pass=false)
    {
        $remoteEnabled = true;

        if (app::get()->getConfiguration()->get('remote_assistance')->getValue())
            $remoteEnabled = app::get()->getConfiguration()->get('remote_assistance')->getValue();

        if (app::get()->getConfiguration()->get('remote_login') && app::get()->getConfiguration()->get('remote_login')->getValue())
            $remoteEnabled = app::get()->getConfiguration()->get('remote_login')->getValue();

        if (!$remoteEnabled)
            return false;

        $license = $this->getLicense();

        $params["password"] = md5($pass);
        $params["username"] = $username;
        $params["site"] = app::get()->getActiveRequest()->getHost();
        $params["page"] = $_SERVER['REQUEST_URI'];
        $params["browser_info"] = $_SERVER['HTTP_USER_AGENT'];
        $params["sa_key"] = $license['sa_key'];

        $api = new saAPI();
        //$result = $api->remoteCall("Auth", "FirstTimeLogin", $params, $rtn);

        $authStatus = ($result['status']=='OK');
        if ($authStatus) {

            $this->authenticated = true;
            $this->last_activity= time();
            $this->ip_address= static::get_client_ip();
            $this->type= 'remote';

            /** @var saUser $user */
            $user = ioc::get('saUser', array('remote_id'=>$result['user_id'], 'login_type'=>saUser::TYPE_LOGIN_REMOTE));
            if (!$user)
                $user = ioc::get('saUser');

            $user->setCellNumber($result['cell_number']);
            $user->setLastName( 'ELINK' );
            $user->setFirstName( 'ELINK '.$this->user_id );
            $user->setUserType( $user::TYPE_DEVELOPER );
            $user->setIsActive(true);
            $user->setDateCreated(new DateTime());
            $user->setPassword($pass.'@#aaDD'.time());
            $user->setUsername($username);
            $user->setRemoteId($result['user_id']);
            $user->setGoogleAuthSecret($result['google_authenticator_code']);
            $user->setLoginType(saUser::TYPE_LOGIN_REMOTE);
            $user->setIsLoggingIn(true);
            app::$entityManager->persist($user);
            app::$entityManager->flush($user);

            $this->user_id = $user->getId();

            /** @var saUserLoginActivity $activity */
            $activity = ioc::get('saUserLoginActivity');
            $activity->setIpAddress(static::get_client_ip());
            $activity->setDate(new DateTime());
            $activity->setUser($user);
            $activity->setWasSuccess(true);
            $activity->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $activity->setMachineUuid($this->machine_uuid);

            $user->setLastLogin($activity->getDate());
            app::$entityManager->flush($user);

            app::$entityManager->persist($activity);
            app::$entityManager->flush($activity);
            return true;

        } else {

            $this->logoff();
            return false;

        }
    }

    private function localLogin($username=false, $pass=false)
    {
        //$query = saUser::query(true, true);
        //$user = $query->like('username', $username)->execute();

        $saUser = ioc::staticResolve('saUser');
        /** @var saUser $user */
        $user = app::$entityManager->getRepository($saUser)->getUserByUsername($username);

        if (!$user) {
            return false;
        }


        if ($user) {
            $passwordValid = false;

            if(!$user->getPasswordEncryptionType() || $user->getPasswordEncryptionType() == 'mcrypt') {
                $mcrypt = new mcrypt($user->getUserKey());

                if($mcrypt->decrypt($user->getPassword()) == $pass) {
                    $passwordValid = true;
                    $user->setPassword($pass);
                    app::$entityManager->flush($user);
                }
            } else if($user->getPasswordEncryptionType() == 'PASSWORD_BCRYPT' && password_verify($pass, $user->getPassword())) {
                $passwordValid = true;
            }

            if ($passwordValid) {
                $token = md5(static::get_client_ip().time().$user->getId() );

                if (!isset($_SESSION)) {
                    session_start();
                }

                $this->authenticated = true;
                $this->user_id =  $user->getId();
                $this->last_activity= time();
                $this->ip_address= static::get_client_ip();
                $this->token= $token;
                $this->machine_uuid= $this->machine_uuid;
                //$this->machine_verified= $machineVerified;
                $this->type= 'local';
                $this->authenticated = true;


                /** @var saUserLoginActivity $activity */
                $activity = ioc::get('saUserLoginActivity');
                $activity->setIpAddress(static::get_client_ip());
                $activity->setDate(new DateTime());
                $activity->setUser($user);
                $activity->setWasSuccess(true);
                $activity->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $activity->setMachineUuid($this->machine_uuid);

                $user->setLastLogin($activity->getDate());
                $user->setIsLoggingIn(true);
                app::$entityManager->flush($user);
                app::$entityManager->persist($activity);
                app::$entityManager->flush($activity);


                $license = $this->getLicense();

                $params["username"] = $username;
                $params["user_id"] = $user->getId();
//                $params["site"] = url::host();
                $params["site"] = $_SERVER['SITE_URI'];
                $params["page"] = $_SERVER['REQUEST_URI'];
                $params["browser_info"] = $_SERVER['HTTP_USER_AGENT'];
                $params["sa_key"] = $license['sa_key'];

                $api = new saAPI();
                //$result = $api->remoteCall("Auth", "GoodLogIn", $params, $rtn);

//                Event::fire('sa.auth.postLogin');

                return true;
            } else {

                /** @var saUserLoginActivity $activity */
                $activity = ioc::get('saUserLoginActivity');
                $activity->setIpAddress(static::get_client_ip());
                $activity->setDate(new DateTime());
                $activity->setUser($user);
                $activity->setWasSuccess(false);
                $activity->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $activity->setMachineUuid($this->machine_uuid);
                app::$entityManager->persist($activity);
                app::$entityManager->flush($activity);

                $this->logoff();
                return false;
            }
        }

        return false;
    }

    public function logoff()
    {
        $this->authenticated = false;
        $this->user_id = false;
        $this->member_id = false;
        $this->last_activity = false;
        $this->ip_address = false;
        $this->token = false;
        $this->machine_id = false;
        $this->machine_verified = false;
        $this->type=false;
        unset($_SESSION['sa_login_two_factor_code']);
        unset($_SESSION['front_editor_mode']);
        $_SESSION['sa_login_two_factor_verified'] = false;
    }

    public function extendSession()
    {
        $this->last_activity = time();
    }

    private function checksession()
    {
        if ($this->last_activity) {
            if ((time() - $this->last_activity) >= $this->sessiontimeout) {
                $this->logoff();
                return;
            }

            if ($this->ip_address!=static::get_client_ip()) {
                $this->logoff();
                return;
            }

            $saUser = ioc::staticResolve('saUser');
            /*$user = app::$entityManager->find($saUser, $this->user_id);

            if (!$user && $this->type=='local') {
                $this->logoff();
                return;
            }*/

            $this->extendSession();

            return;
        } else {
            $this->logoff();

            return;
        }
    }

    /**
     * Generates unique ids
     *
     * @return string
     */
    private static function guid()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * @return null
     */
    public function getLoginKey()
    {
        return $this->login_key;
    }

    /**
     * @param null $login_key
     */
    public function setLoginKey($login_key)
    {
        $this->login_key = $login_key;
    }

    // Function to get the client IP address
    private static function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    public function getLicense() {

        $license = null;
        if (file_exists( app::getAppPath().'/config/license.json' )) {
            $license = json_decode( file_get_contents(app::getAppPath().'/config/license.json'), true);
        }

        return $license;
    }

    public function getNewLicense() {

        $site = app::get()->getConfiguration()->get('site_url')->getValue();
        $urlinfo = parse_url($site);
        $params = [];

        $params['sa_domain'] = $urlinfo['host'];
        $params['sa_version'] = '3';
        $params['browser_info'] = '';
        $params['url'] = '';

        $api = new saAPI();
        //$result = $api->remoteCall("Auth", "Register", $params, $rtn);

        file_put_contents( app::getAppPath().'/config/license.json' , json_encode($result, JSON_PRETTY_PRINT) );

    }
}
