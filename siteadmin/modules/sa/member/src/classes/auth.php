<?php
namespace sa\member;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Johnstyle\GoogleAuthenticator\GoogleAuthenticator;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Event;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\utilities\Cookie;
use sacore\utilities\email;
use sacore\utilities\mcrypt;
use sacore\utilities\notification;

class auth
{
    protected $authenticated = null;
    protected $sessiontimeout = 2400;
    protected $last_session_db_check = null;
    protected $user_id = null;
    protected $member_id = null;
    protected $last_activity = null;
    protected $ip_address = null;
    protected $login_key = null;
    protected $machine_uuid = null;
    protected $machine_verified = null;
    protected $login_user = 'standard'; //SA user or standard
    protected $hard_login = false;
    protected $two_factor_validated = false;
    protected $two_factor_smscall_code = null;
    protected $last_sso_update = null;

    public function __construct()
    {
        if (!isset($_SESSION['authContext'])) {

            if (!Cookie::getCookie('machine_uuid')) {
                $uuid = $this->guid();
                Cookie::setCookie('machine_uuid', $uuid, time()+31536000,'/');
                //setcookie('machine_uuid', $uuid, time()+31536000,'/');
            }
            else {
                $this->machine_uuid = Cookie::getCookie('machine_uuid');
            }

            $_SESSION['authContext'] = $this;
        } else {
            throw new Exception('Please use the getInstance method to get the auth object.');
        }

        $autoLogoutEnabled = app::get()
            ->getConfiguration()
            ->get('member_session_timeout_enabled', false)
            ->getValue();

        if($autoLogoutEnabled) {
            $this->sessiontimeout = app::get()
                ->getConfiguration()
                ->get('member_session_timeout', false)
                ->getValue();
        }
    }

    /**
     * Method that gets called to see if the user has access to a route
     *
     * @param Event $event
     * @return bool|object|void
     */
    public static function isAllowedToRunRoute(Event $event)
    {
        $auth = static::getInstance();

        $app = app::get();
        $config = $app->getConfiguration();

        $routeInfo = $event->getData('routeInfo');
        $allowed = $event->getData('allowed');

        $exclude_two_factor = array(
            'member_machineverifycode',
            'member_machineverifycodeverify',
            'member_logoff',
            'member_login',
            'member_login_post',
            'member_two_factor_verify',
            'sa_login',
            'sa_logoff',
            'sa_loginattempt',
            'system_modrequest_route',
            'member_additionalauthsetup',
            'member_additionalauthsetup_test',
            'member_additionalauthsetup_test_submit',
            'member_two_factor_verify_user_input',
            'member_two_factor_verify_user_input_validate'
        );

        if (!$routeInfo || $routeInfo->getRouteType()!='sacore\application\route') {
            return;
        }

        if ($routeInfo->protected && !$routeInfo->excludeFromAuth) {
            $app = app::getInstance();
            if (!$auth->isAuthenticated()) {
                $routeInfo = $app->findRoute($routeInfo->protected_login_route);

                $allowed = false;
            }

            if ( $auth->isAuthenticated() && !$auth->isHardLogin() && $routeInfo->protected_hard ) {
                $routeInfo = $app->findRoute($routeInfo->protected_login_route);
                $allowed = false;
            }
        }


        $deviceVerify = $config->get('member_device_verify', true)->getValue();
        $login_two_factor = $config->get('member_two_factor_require', true)->getValue();

        if(!$login_two_factor){
            $member = modRequest::request('auth.member');
            if($member){
                $login_two_factor = $member->getRequireTwoFactor();
            }
        }


        if ( $routeInfo->protected && $routeInfo->machine_protected && $allowed && !$login_two_factor && $deviceVerify && !in_array($routeInfo->id, $exclude_two_factor) )
        {
            /** @var saMemberDevice $saMemberDevices */
            $saMemberDevices = ioc::resolve('saMemberDevice');
            $verified = $saMemberDevices::isDeviceVerified(Cookie::getCookie('machine_uuid'), $auth->getAuthUser() );
            if (!$verified)
            {
                $routeInfo = $app->findRouteById( 'member_machineverify' );
                $allowed = false;
            }
        }

        if ( !$auth->isTwoFactorValidated() && $routeInfo->protected && $allowed && $login_two_factor && !in_array($routeInfo->id, $exclude_two_factor) )
        {
            $routeInfo = $app->findRouteById( 'member_two_factor_verify' );
            $allowed = false;
        }

        if ( !empty($auth->login_key) && $auth->login_type=='username-password' ) {
            Cookie::setCookie('rememberme_key', $auth->login_key, time()+31536000,'/');
        }

        $setupSteps = modRequest::request('member.setup.get_steps');
        usort($setupSteps, function ($a, $b) {
            if ($a["priority"] > $b["priority"]) {
                return 1;
            } else if ($a["priority"] < $b["priority"]) {
                return -1;
            }

            return 0;
        });

        foreach($setupSteps as $step) {
            $shouldIntercept = $step["callback"]($event);

            if ($shouldIntercept) {
                $routeInfo = $step["route"];
                break;
            }
        }

        $event->setData('routeInfo', $routeInfo);
        $event->setData('allowed', $allowed);
    }

    /**
     * @return bool
     */
    public function isTwoFactorValidated()
    {
        return $this->two_factor_validated;
    }

    /**
     * @param bool $two_factor_validated
     */
    public function setTwoFactorValidated($two_factor_validated)
    {
        $this->two_factor_validated = $two_factor_validated;
    }





    /**
     * Returns the current auth object or creates a new one if one doesn't exist
     *
     * @return auth
     */
    public static function getInstance()
    {
        if (isset($_SESSION['authContext'])) {
            return $_SESSION['authContext'];
        }
        else {
            return ioc::get('auth');
        }

    }

    /**
     * Check to see if a member is logged in.
     * Also checks the session
     *
     * @param bool|true $checkSession
     * @return null
     * @throws ModRequestAuthenticationException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public static function isAuthenticated($checkSession=true)
    {
        $auth = static::getInstance();

        if ($checkSession) {
            $auth->checksession();
        }

        if ( !$auth->authenticated && Cookie::getCookie('rememberme_key') ) {
            $auth->logon(null, null, Cookie::getCookie('rememberme_key'), 'remember_me');
        }

        return $auth->authenticated;
    }


    /**
     * Get the current logged in user
     *
     * @return saMemberUsers
     * @throws ModRequestAuthenticationException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public static function getAuthUser()
    {
        $auth = static::getInstance();

        if (!$auth->isAuthenticated()) {
            return false;
        }

        if ($auth->user_id>0) {
            $user = app::$entityManager->find( ioc::staticResolve('saMemberUsers'), $auth->user_id );
        }


        return $user;
    }

    /**
     * Get the current logged in member
     *
     * @return bool|saMember
     * @throws ModRequestAuthenticationException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public static function getAuthMember()
    {
        $auth = static::getInstance();

        if (!$auth->isAuthenticated()) {
            return false;
        }

        if ($auth->member_id>0) {
            /** @var saMember $member */
            $member = app::$entityManager->find( ioc::staticResolve('saMember'), $auth->member_id );
        }

        return $member;
    }

    public static function getAuthMemberGroups()
    {
        $member = static::getAuthMember();
        if($member && $member->getGroups()) {
            return doctrineUtils::getEntityCollectionArray($member->getGroups());
        } else {
            return [];
        }
    }

    /**
     * Get the logged on user id
     *
     * @return bool|null
     */
    public static function getAuthUserId()
    {
        $auth = static::getInstance();

        if (!$auth->isAuthenticated()) {
            return false;
        }

        return $auth->user_id;
    }

    /**
     * Get the logged on member id without checking to see if the session is valid
     *
     * @return bool|null
     */
    public static function getAuthMemberId2()
    {
        $auth = static::getInstance();
        return $auth->member_id;
    }

    /**
     * Get the logged on member id
     *
     * @return bool|null
     */
    public static function getAuthMemberId()
    {
        $auth = static::getInstance();

        if (!$auth->isAuthenticated()) {
            return false;
        }

        return $auth->member_id;
    }

    /**
     * Get the user type of person logged in
     *
     * @return boolean|login_user
     */
    public static function getAuthLoginUser()
    {
        $auth = static::getInstance();

        if (!$auth->isAuthenticated()) {
            return false;
        }

        return $auth->login_user;
    }

    /**
     * Get the member id of sa user
     *
     * @return boolean|sa_user_id
     */
    public static function getAuthSaUserMemberId()
    {
        $auth = static::getInstance();

        if (!$auth->isAuthenticated()) {
            return false;
        }

        return $auth->sa_user_id;
    }

    // AUTH FUNCTIONS *********************************************************************************************

    /**
     * @param $username
     * @param $password
     * @param $remember_me not used
     * @param $login_key not used
     * @deprecated
     * @return bool
     */
    public static function login($username, $password, $remember_me=null, $login_key=null) {
        $auth = static::getInstance();
        return $auth->logon($username, $password, $login_key);
    }

    /**
     * @param saMemberUsers $user
     * @return bool
     * @throws ORMException
     */
    public function userObjectLogon($user)
    {
        unset($_SESSION['canAccessRoute']);

        $member = $user->getMember();

        if ( !$member->getIsActive() && !$member->getIsDeleted() ) {
            return false;
        }

        $_SESSION['invalidLogins'] = 0;

        if (!isset($_SESSION)) {
            session_start();
        }

        $this->authenticated = true;
        $this->user_id =  $user->getId();
        $this->member_id =  $user->getMember()->getId();
        $this->last_activity= time();
        $this->ip_address= static::get_client_ip();
        $this->machine_verified = false; // NOT IN USE
        $this->login_type = 'sso';
        $this->hard_login = true;

        $user->setLoginCount($user->getLoginCount() + 1);
        $user->setLastLogin(new DateTime());
        $user->setFreezeDateUpdate(true);
        $user->setUserMachineUuid($this->machine_uuid);
        $user->setUserAgent($_SERVER['HTTP_USER_AGENT']);

        app::$entityManager->persist($user);
        app::$entityManager->flush();

        $this->authenticated = true;

        return true;
    }

    /**
     * MAIN LOGIN FUNCTION THAT HANDLES BOTH THE API AND THE WEB LOGINS
     *
     * @param bool|false $username
     * @param bool|false $pass
     * @param null $login_key
     * @param null $login_key_type
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ModRequestAuthenticationException
     */
    public function logon($username=false, $pass=false, $login_key=null, $login_key_type=null)
    {
        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $saMemberLoginKey = ioc::staticResolve('saMemberLoginKey');

        $passwordValid = false;

        /** @var saMemberUsers $ssoUser */
        $ssoUser = modRequest::request('sso.user');

        if($ssoUser) {
            $passwordValid = true;
            $user = $ssoUser;
        } else if (!empty($login_key)) {
            /** @var saMemberLoginKey $key */
            $key = app::$entityManager->getRepository($saMemberLoginKey)->findOneBy(array('uuid'=> $login_key, 'revoked'=>false, 'type'=>$login_key_type));

            if (!$key) {
                return false;
            }

            /** @var saMemberUsers $user */
            $user = $key->getUser();

            if($user && $key) {
                $passwordValid = true;
            }
        } else {
            $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array('username'=> $username, 'is_active'=>true));
        }

        unset($_SESSION['canAccessRoute']);

        if (!$user) {
            return false;
        }

        $member = $user->getMember();

        if ( !$member->getIsActive() && !$member->getIsDeleted() ) {
            return false;
        }

        if (!$user->getPasswordEncryptionType() || $user->getPasswordEncryptionType()=='mcrypt') {
            $mcrypt = new mcrypt($user->getUserKey());
            if ( $mcrypt->decrypt($user->getPassword()) == $pass ) {
                $passwordValid = true;
                $user->setPassword($pass);
                app::$entityManager->flush($user);
            }
        } elseif ($user->getPasswordEncryptionType() == 'md5' && $user->getPassword() == md5($pass)) {
            $passwordValid = true;
            $user->setPassword($pass);
            app::$entityManager->flush($user);
        } elseif ($user->getPasswordEncryptionType() == 'PASSWORD_BCRYPT' && password_verify( $pass, $user->getPassword() )) {
            $passwordValid = true;
        }

        if ( $passwordValid ) {
            $_SESSION['invalidLogins'] = 0;

            if (!isset($_SESSION)) {
                session_start();
            }

            if (!empty($login_key) && empty($username)) {
                $logintype = 'key';
                $this->hard_login = false;
            } else {
                $logintype = 'username-password';
                $this->hard_login = true;
            }

            $this->authenticated = true;
            $this->user_id =  $user->getId();
            $this->member_id =  $user->getMember()->getId();
            $this->last_activity= time();
            $this->ip_address= static::get_client_ip();
            $this->login_key= $login_key;
            $this->machine_verified = false; // NOT IN USE
            $this->login_type = $logintype;

            $user->setLoginCount( $user->getLoginCount() + 1 );
            $user->setLastLogin( new \sacore\application\DateTime() );
            $user->setFreezeDateUpdate(true);
            $user->setUserMachineUuid($this->machine_uuid);
            $user->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            app::$entityManager->persist($user);
            app::$entityManager->flush();

            $this->authenticated = true;

            return true;
        } else {
            $this->logoff();

            $_SESSION['invalidLogins']++;
            return false;
        }

        return false;
    }

    public function issueMemberLoginKey($type='remember_me') {

        $auth = static::getInstance();

        $user = $auth->getAuthUser();

        /** @var saMemberLoginKey $loginKey */
        $loginKey = ioc::resolve('saMemberLoginKey');
        $loginKey->setUser($user);
        $loginKey->setType($type);
        $loginKey->setRevoked(false);
        $auth->setLoginKey( $loginKey->getUuid() );
        $user->addLoginKey($loginKey);
        app::$entityManager->persist($loginKey);
        app::$entityManager->flush();

        return $loginKey;
    }

    public static function saUserLogon($member_id = null, $username = null)
    {
        $auth = static::getInstance();

        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $saMemberLoginKey = ioc::staticResolve('saMemberLoginKey');
        $user = null;

        /** @var saMemberUsers $user */
        if($username)
            // if username is sent, login to that user
            $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array('username'=> $username, 'is_active'=>true));
        elseif($member_id) {
            // if member id is sent, find if there's a primary user with this member and login as that user
            $member = app::$entityManager->getRepository(ioc::staticResolve('saMember'))->find($member_id);
            $member_users = $member->getUsers();
            if($member_users) {
                foreach($member_users as $member_user)
                {
                    $user = $member_user;
                    if($member_user->getIsPrimaryUser())
                        break;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        unset($_SESSION['canAccessRoute']);

        if (!$user) {
            return false;
        }

        $member = $user->getMember();

        if ( !$member->getIsActive() ) {
            return false;
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        $auth->authenticated = true;
        $auth->user_id =  $user->getId();
        $auth->member_id =  $user->getMember()->getId();
        $auth->last_activity= time();
        $auth->ip_address= static::get_client_ip();
        $auth->machine_verified = true; // NOT IN USE
        $auth->login_user = 'sa_user';
        $auth->hard_login = true;
        $auth->two_factor_validated = true;

        $user->setFreezeDateUpdate(true);
        $user->setUserMachineUuid($auth->machine_uuid);
        $user->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        app::$entityManager->persist($user);
        app::$entityManager->flush();

        $auth->authenticated = true;
        return true;
    }

    /**
     *
     * Logs off a member
     *
     */
    public function logoff($revokeLoginKey = true)
    {
        unset($_SESSION['canAccessRoute']);

        if (!empty($this->login_key) && $revokeLoginKey)
        {
            /** @var saMemberLoginKey $key */
            $key = app::$entityManager->getRepository( ioc::staticResolve('saMemberLoginKey') )->findOneBy(array('uuid'=> $this->login_key));
            
            if($key) {
                $key->setRevoked(true);
                app::$entityManager->flush();
            }
        }

        $this->authenticated = null;
        $this->user_id = null;
        $this->member_id = null;
        $this->last_activity = null;
        $this->ip_address = null;
        $this->login_key = null;
        $this->machine_verified = null;
        $this->login_type = null;
        $this->login_user = null;
        $this->two_factor_validated = false;

        if ( isset($_SESSION['selectedAccount']) ) {
            unset($_SESSION['selectedAccount']);
        }

    }

    /**
     * Checks to make sure a session is valid
     * validates session time, ip, and machine id
     *
     */
    private function checksession()
    {
        if ($this->last_activity) {    
            if ((time() - $this->last_activity) >= $this->sessiontimeout && $this->login_type=='username-password') {
                $this->logoff();
                return;
            }

            if ($this->ip_address!=static::get_client_ip()  && $this->login_type=='username-password') {                
                $this->logoff();
                return;
            }

            if (is_object(app::$entityManager) && (!$this->last_session_db_check || time() - $this->last_session_db_check > 240) ) {
                $this->last_session_db_check = time();

                /** @var saMemberUsers $user */
                $user = ioc::getRepository('saMemberUsers')->findOneBy(array(
                    'id' => $this->user_id,
                    'is_active' => true
                ));

                if (!$user) {
                    $this->logoff();
                    return;
                }

                $allowMultipleLogins = false;

                if (!empty(app::get()->getConfiguration()->get('allow_multiple_logins'))) {
                    $allowMultipleLogins = app::get()->getConfiguration()->get('allow_multiple_logins')->getValue();
                }

                if ( $user->getUserMachineUuid() != $this->machine_uuid && !$allowMultipleLogins ) {
                    $notify = new notification();
                    $notify->resetNotifications();
                    $notify->addNotification('danger', 'Logged off', 'You have been logged off because your username and password have been used somewhere else.');
                    $this->logoff();
                    return;
                }

                $account = $user->getMember();

                if (!$account && !$account->getIsDeleted() && $account->getIsActive() )  {
                    $this->logoff();
                    return;
                }
            }

            $this->last_activity = time();

            return;
        } elseif ($this->login_type != 'key') {
            $this->logoff();
            return;
        }
    }

    /**
     * Generates unique ids
     *
     * @return string
     */
    private function guid()
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

    /**
     * @return null
     */
    public function getLoginUser()
    {
        return $this->login_user;
    }

    /**
     * @param null $login_user
     */
    public function setLoginUser($login_user)
    {
        $this->login_user = $login_user;
    }

    /**
     * @return bool
     */
    public function isHardLogin()
    {
        return $this->hard_login;
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

    public static function getUserSessionJs($data)
    {
        $autoLogoutEnabled = app::get()
            ->getConfiguration()
            ->get('member_session_timeout_enabled', false)
            ->getValue();

        // Reduce overhead by returning as quickly as
        // possible if this feature isn't enabled
        if(!$autoLogoutEnabled) {
            return $data;
        }

        $route = app::get()->activeRoute;

        if(!$route->protected || $route->getRouteType() == 'sacore\application\saRoute' ) {
            return $data;
        }

        /** @var saMemberUsers $user */
        $user = modRequest::request('auth.user');

        if(!$user) {
            return $data;
        }

        $data[] = array(
            'path' => '/member/profile/js/member-session.js',
            'priority' => 9
        );

        return $data;
    }

    public static function getUserSessionInlineJs($data)
    {
        $autoLogoutEnabled = app::get()
            ->getConfiguration()
            ->get('member_session_timeout_enabled', false)
            ->getValue();

        // Reduce overhead by returning as quickly as
        // possible if this feature isn't enabled
        if(!$autoLogoutEnabled) {
            return $data;
        }

        $route = app::get()->activeRoute;

        if(!$route->protected || $route->getRouteType() == '\sacore\application\saRoute' ) {
            return $data;
        }

        $sessionTimeoutAutoLogoutTimer = app::get()
            ->getConfiguration()
            ->get('member_session_timeout_interval')
            ->getValue();

        $sessionTimeoutWarningTimer = static::getSessionTimeoutWarningTimer();
        $countdownTime = gmdate('i:s', $sessionTimeoutAutoLogoutTimer);

        $timeout = app::get()
            ->getConfiguration()
            ->get('member_session_timeout', false)
            ->getValue();

        $inlineSessionConfigVars = 'var timeout = "' . $timeout . '";' . PHP_EOL;
        $inlineSessionConfigVars .= 'var sessionTimeoutAutoLogoutTimer = "' . $sessionTimeoutAutoLogoutTimer . '";' . PHP_EOL;
        $inlineSessionConfigVars .= 'var sessionTimeoutWarningTimer = "' . $sessionTimeoutWarningTimer . '";' . PHP_EOL;
        $inlineSessionConfigVars .= 'var countdownTime = "' . $countdownTime . '";' . PHP_EOL;

        $data[] = $inlineSessionConfigVars;

        return $data;
    }

    public static function getSessionTimeoutWarningTimer() {
        $sessionTimeoutConfigVal = app::get()
            ->getConfiguration()
            ->get('member_session_timeout', false)
            ->getValue();

        $sessionTimeoutAutoLogoutTimer = app::get()
            ->getConfiguration()
            ->get('member_session_timeout_interval')
            ->getValue();

        return $sessionTimeoutConfigVal - $sessionTimeoutAutoLogoutTimer;
    }

    public function extendSession() {
        $this->last_activity = time();
    }

    public static function getSessionAutoLogoutInterval() {
        return 180;
    }


    public function validateTwoFactorCode($code) {

        $user = $this->getAuthUser();

        $validated = false;

        if (!$this->two_factor_smscall_code) {

            $google = new GoogleAuthenticator($user->getGoogleAuthenticatorKey());
            if ($google->verifyCode($code, 5)) {
                $validated = true;
            }

        }
        elseif ($code && $code==$this->two_factor_smscall_code)
        {
            $validated = true;
            $this->revokeTwoFactorSMSCallCode();
        }

        if ($validated) {
            $this->two_factor_validated = true;
        }

        return $validated;

    }

    public function getTwoFactorValidationMethod() {

        if (!$this->two_factor_smscall_code) {

            return 'google_auth';

        }
        else
        {
            return 'sms_phone_code';
        }
    }

    /**
     * Revoke a security code
     */
    public function revokeTwoFactorSMSCallCode() {
        $this->two_factor_smscall_code = null;
    }

    /**
     * Issue a new security code for the logged in user
     *
     * @param int $phoneid
     * @param bool $force
     */
    public function issueTwoFactorSMSCallCode($phoneid, $force=false) {

        $user = $this->getAuthUser();

        $now = new DateTime();
        $timeSinceSent = 0;
        if (isset($_SESSION['device_verify_code_time'])) {
            $interval = $now->diff($_SESSION['device_verify_code_time']);
            $timeSinceSent = $interval->days * 24 * 60;
            $timeSinceSent += $interval->h * 60;
            $timeSinceSent += $interval->i;
        }

        if (!isset($_SESSION['two_factor_verify_code_time']) || $timeSinceSent > 10 || $force || !$this->two_factor_smscall_code) {
            $this->two_factor_smscall_code = rand(100000, 999999);
            $member = $user->getMember();
            /** @var saMemberPhone $phone */
            $phone = app::$entityManager->getRepository(ioc::staticResolve('saMemberPhone'))->findOneBy(array('id' => $phoneid, 'member' => $member));

            if ($phone && $phone->getType() == 'mobile') {
                modRequest::request('messages.sendSMS', '0', array('phone' => preg_replace('/[^0-9]/', '', $phone->getPhone()), 'body' => 'Your security code is: ' . $this->two_factor_smscall_code));
            } elseif ($phone) {
                modRequest::request('messages.sendVoice', '0', array(
                        'phone' => preg_replace('/[^0-9]/', '', $phone->getPhone()),
                        'body' => '{p|2} Hello {p|1} Thank You for using eye4techology. {p|1} Your security code is: {spell|' . $this->two_factor_smscall_code . '}. {p|1} Your security code is: {spell|' . $this->two_factor_smscall_code . '}. {p|1} Good Bye. '
                    )
                );
            }
            $_SESSION['two_factor_verify_code_time'] = $now;
        }
    }
}
