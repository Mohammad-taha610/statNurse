<?php
namespace sa\member;

use Captcha\Captcha;
use \sacore\application\app;
use \sacore\application\controller;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Request;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\ValidateException;

use sacore\utilities\Cookie;
use sacore\utilities\doctrineUtils;
use \sacore\utilities\notification;
use \sacore\utilities\url;


/**
 * Class memberController
 * @package sa\member
 */
class memberController extends controller {


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

	public static function ping() {

		$auth = auth::getInstance();
		/** @var saMemberUsers $user */
		$user = $auth->getAuthUser();
		if ($user) {
			$user->setLastActive(new DateTime());
			$user->setFreezeDateUpdate(true);
			app::$entityManager->flush($user);
		}

	}

	public static function memberLoginAsync($data) {
        $email = $data['email'];
        $password = $data['password'];

        if(auth::getAuthMember()) {
            return self::generateLoginAttemptResponse(true, '', array('member' => doctrineUtils::getEntityArray(auth::getAuthMember())));
        }

        $auth = auth::getInstance();
        $isLoggedIn = $auth->login($email, $password, false, null);
        
        if(!$isLoggedIn) {
            return self::generateLoginAttemptResponse(false, 'Invalid username or password');
        }

        /** @var saMember $member */
        $member = $auth->getAuthMember();

        if(!$member) {
            return self::generateLoginAttemptResponse(false, 'Invalid username or password');
        }

        return self::generateLoginAttemptResponse(true, '', array( 'member' => doctrineUtils::getEntityArray($member) ));
    }

    private static function generateLoginAttemptResponse($success = true, $errorMsg = '', $extras = array()) {
        $response = array();
        $response['success'] = $success;
        $response['errorMsg'] = $errorMsg;
        return array_merge($response, $extras);
    }

    public function userNotAllowed()
    {
        $auth = auth::getInstance();
        $member = $auth->getAuthMember();

        $view = new View('user_not_allowed', $this->viewLocation());
        return $view;
    }

	public function headerWidget($html='')
	{
		$auth = auth::getInstance();

		$data = array('is_logged_in'=>false);

		if (is_object($auth))
		{
			$member = $auth->getAuthMember();

            if ($auth::isAuthenticated()) {
				$data = $member ? doctrineUtils::convertEntityToArray( $member ) : array();
				$data['is_logged_in'] = true;
            }
		}

		$view = new view(false, 'memberHeaderWidget', self::viewLocation());
		$view->data = $data;
		$html .= $view->getHTML();

        return $html;
	}

	public function images($view)
	{
		$view = new view(false, $view, $this->moduleLocation().'/images/');
		$view->display();
	}

	public function dashboard()
	{
		$data = modRequest::request('member.dashboard');
		$view = new View('dashboard', $this->viewLocation() );
		$view->data['dashboard_items'] = $data;
		return $view;
	}

	public static function getDashboardItems($data)
	{
		$data[] = array('module' => 'member', 'icon' => 'fa-user', 'name' => 'My Profile', 'link' => app::get()->getRouter()->generate('member_profile'));
		return $data;
	}

	public function signup($request)
	{
	    $publicRegistrationEnabled = app::get()->getConfiguration()->get('enable_public_member_signup')->getValue();

	    if(!$publicRegistrationEnabled) {
	        return $this->error404(true);
        }

		$view = new \sacore\application\responses\View('signup', $this->viewLocation());
		$view->addJSResources( app::get()->getRouter()->generate('member_js', ['file' =>'af-pwstrength.js']));
		
		if ($request->request) {
            $view->data = $request->request->all();
        }
        
        return $view;
	}

    /**
     * @param Request $request
     * @return Redirect|\sacore\application\responses\View
     * @throws \Exception
     */
	public function signupsave($request)
	{
	    $saMember = ioc::staticResolve('saMember');
	    $publicRegistrationEnabled = app::get()->getConfiguration()->get('enable_public_member_signup')->getValue();

	    if(!$publicRegistrationEnabled) {
	        return $this->error404(true);
        }

		$notify = new notification();
		try {
            $saMember::memberSignUp($request->request->all());
            
            $auth = \sa\member\auth::getInstance();
            $auth->logon($request->request->get('email'), $request->request->get('password'));
            
            return new Redirect(app::get()->getRouter()->generate('dashboard_home'));
		} catch( ValidateException $e) {
			$notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage() );
			return $this->signup($request->request->all());
		}
	}

	public function resetPassword()
	{
		$view = new View('passwordreset', $this->viewLocation() );
		return $view;
	}

	/**
	 * @param Request $request
	 * @throws \Exception
     */
	public function resetPasswordChange($request)
	{
	    $saMemberUsers = ioc::staticResolve('saMemberUsers');
        /** @var saMemberUsers $user */
        $user = $saMemberUsers::getUserFromPasswordResetRequest($request->get('k'), $request->get('i'));

		if ($user)
		{
			$view = new View( 'passwordresetchange', $this->viewLocation() );
			$view->data['username'] = $user->getUsername();
			$view->data['k'] = $request->get('k');
			$view->data['i'] = $request->get('i');
			return $view;
		}
		else
		{
			$notify = new notification();
			$notify->addNotification('danger', 'Error', 'The password reset link you clicked is invalid. <br />');
			return new Redirect(app::get()->getRouter()->generate('member_login') );
		}
	}

	/**
	 * @param Request $request
	 * @throws \Exception
     */
	public function resetPasswordChangeSave($request)
	{
	    $saMemberUsers = ioc::staticResolve('saMemberUsers');
		$notify = new notification();

		if (empty($request->get('k')) || empty( $request->get('i')))
		{
			$notify->addNotification('danger', 'Error', 'An error occurred. Please check the request' );
			return new Redirect( app::get()->getRouter()->generate('member_reset') );
		}

        if ($request->request->get('password')!=$request->request->get('password2')) {

            $notify->addNotification('danger', 'Error', 'An error occurred. <br />Your passwords didn\'t match.' );
            return new Redirect(app::get()->getRouter()->generate('member_reset_change_post').'?k='.$request->get('k').'&i='.$request->get('i') );
        }

        /** @var saMemberUsers $user */
        $user = $saMemberUsers::getUserFromPasswordResetRequest($request->get('k'), $request->get('i'));

		try
		{
            $user->setPassword($request->request->get('password'));
            $user->setPasswordResetKey(null);
            $user->setPasswordResetKey2(null);

            $ttl = app::get()->getConfiguration()->get('password_reset_ttl')->getValue();
            $user->setPasswordResetTtl($ttl);

            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'The password has been successfully reset.');
			return new Redirect( app::get()->getRouter()->generate('member_login') );
		}
		catch(ValidateException $e)
		{
			$notify->addNotification('danger', 'Error', 'An error occurred. <br />'. $e->getMessage() );
			return new Redirect( app::get()->getRouter()->generate('member_reset_change_post').'?k='.$request->get('k').'&i='.$request->get('i') );
		}

	}

	/**
	 * @param Request $request
	 * @throws \Exception
     */
	public function attemptResetPassword($request)
	{
		$notify = new notification();
		$saMemberUsers = ioc::resolve('saMemberUsers');
		try
		{
            $result = $saMemberUsers::requestResetPassword($request->request->get('username'));
            $notify->addNotification('success', 'Success', 'If the email exists in our system you should receive an email shortly. <br />');
			return new Redirect( app::get()->getRouter()->generate('member_login') );
		}
		catch (Exception $e)
		{
            $notify->addNotification('success', 'Success', 'If the email exists in our system you should receive an email shortly. <br />');
            return new Redirect(app::get()->getRouter()->generate('member_reset') );
		}
	}

	public function modRequestLoginCustomRedirect($redirectUrl) {
	    $memberControllerClass = ioc::staticResolve('memberController');
	    /** @var memberController $memberControllerInst */
	    $memberControllerInst = new $memberControllerClass();
	    $memberControllerInst->login(false, $redirectUrl);
	}
	
	public function humanVerify()
    {
        try {

            $captcha = new Captcha();
            $captcha->setPublicKey(app::get()->getConfiguration()->get('recaptcha_public')->getValue());
            $captcha->setPrivateKey(app::get()->getConfiguration()->get('recaptcha_private')->getValue());
            $captcha->setRemoteIp($_SERVER['REMOTE_ADDR']);

            $view = new \sacore\application\responses\View('humanverify', $this->viewLocation());
            $view->setXSSSanitation(false);
            $view->data['recaptchaHTML'] = $captcha->html();
            return $view;
        }
        catch (\Exception $e) {

            return $this->error403(true);

        }
    }

    public function humanVerifyAttempt()
    {
        $captcha = new Captcha();
        $captcha->setPrivateKey(app::get()->getConfiguration()->get('recaptcha_private')->getValue());
        $captcha->setPublicKey(app::get()->getConfiguration()->get('recaptcha_public')->getValue());
        $captcha->setRemoteIp($_SERVER['REMOTE_ADDR']);

        $response = $captcha->check();

        if ($response->isValid())
        {
            $_SESSION['invalidLoginAttempts'] = 0;
            return new Redirect( app::get()->getRouter()->generate('member_login') );
        }
        else
        {
            $notify = new notification();
            $notify->addNotification('danger', 'Error', 'Invalid Code. Please try again.');
            return $this->humanVerify();
        }

    }

    public function login($request)
    {
		$user = modRequest::request('auth.user');
		$customRedirectUrl = $_SESSION['login_redirect'];
		if(!$user) {
	        if($customRedirectUrl) {
	            $_SESSION['login_redirect'] = $customRedirectUrl;
	        } else {
	            $_SESSION['login_redirect'] = ($_SERVER['REQUEST_URI']=='/member/login' || $_SERVER['REQUEST_URI']=='/member/attemptLogin') ? app::get()->getRouter()->generate('dashboard_home') : $_SERVER['REQUEST_URI'];
	        }

    		$ssoProviders = modRequest::request('sso.providers', []);

    		foreach($ssoProviders as $authModRequest) {
    			$response = modRequest::request($authModRequest);

    			if($response instanceof \sacore\application\Responses\ISaResponse) {
    				return $response;
    			} else {
    				continue;
    			}
    		}
		} else {
			$sessionRedirect = $_SESSION['login_redirect'];

			$_SESSION['login_redirect'] = app::get()->getRouter()->generate('dashboard_home');

			return new Redirect($sessionRedirect ? $sessionRedirect : $_SESSION['login_redirect']);
		}

        if ($_SESSION['invalidLoginAttempts']>3) {
            return $this->humanVerify();
        }

        if (!$request->request || !count($request->request->all())) {
            $data = array();
        } else {
            $data = $request->request->all();
            $notify = new notification();
            $notify->addNotification('danger', 'Error', 'Invalid username or password.');
        }

        if($customRedirectUrl != null && !empty($customRedirectUrl)) {
            $_SESSION['login_redirect'] = $customRedirectUrl;
        } else {
            $_SESSION['login_redirect'] = ($_SERVER['REQUEST_URI']=='/member/login' || $_SERVER['REQUEST_URI']=='/member/attemptLogin') ? app::get()->getRouter()->generate('dashboard_home') : $_SERVER['REQUEST_URI'];
        }

        $view = new \sacore\application\responses\View( 'login', $this->viewLocation());
        $view->data = $data;
        $view->data['enable_public_member_signup'] = app::get()->getConfiguration()->get('enable_public_member_signup')->getValue();

        return $view;
    }

    /**
     * @return Redirect
     * @throws \Exception
     */
	public function attemptLogin($request)
	{

        /** @var auth $auth */
        $auth = auth::getInstance();

	    $config = app::get()->getConfiguration();

        $autoLogoutEnabled = $config->get('member_session_timeout_enabled', false)->getValue();
        $allow_soft_logins = $config->get('allow_soft_login', false)->getValue();


        if($autoLogoutEnabled) {
            $sessionTimeoutAutoLogoutTimer = $config->get('member_session_timeout')->getValue();
            Cookie::setCookie("member_session_length", $sessionTimeoutAutoLogoutTimer);
        }

        $result = $auth->logon($request->request->get('username'), $request->request->get('password'));


        if ( $result && ($request->request->get('remember_me') || $allow_soft_logins) ) {
            $loginKey = $auth->issueMemberLoginKey();
            Cookie::setCookie('rememberme_key', $loginKey->getUuid(), time()+31536000,'/');
        }

		if ($result)
		{
			$login_redirect = (isset($_SESSION['login_redirect'])) ? $_SESSION['login_redirect'] : app::get()->getRouter()->generate('dashboard_home');
            $return =  new Redirect($login_redirect);
		}
		else
		{
            $return = $this->login($request);
		}

		return $return;
	}


	public function logoff()
	{
		//setcookie('rememberme_key', false, -1, '/');
		Cookie::setCookie('rememberme_key', false, -1, '/');
		auth::getInstance()->logoff();

        $return =  new Redirect( app::get()->getRouter()->generate('member_login') );
        return $return;
	}



   	public function signupConfirmation($request)
	{

        $view = new View('signup_confirmation', $this->viewLocation() );

		$notify = new notification();

        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy( array('password_reset_key'=>$request->get('k'), 'id'=>$request->get('i') ));

        if ( $user ) {
            $notify->addNotification('success', 'Success', 'Your account has been confirmed successfully. Please click <a href="/member/login/">here</a> to login.');
            $user->setIsActive(true);
            app::$entityManager->persist($user);
            app::$entityManager->flush();
        }

        else {
            $notify->addNotification('danger', 'Error', 'We were not able to confirm your account. Please contact ' . app::get()->getConfiguration()->get('site_name')->getValue() . '.' );
        }

        return $view;

	}

	public function getNewNotificationsCount($data)
	{
		$member = auth::getAuthMember();
		$newNotificationsCount = ioc::getRepository('saMemberNotification')->getNewNotificationsForMember($member, true);

		$data['newNotificationsCount'] = $newNotificationsCount;
		return $data;
	}

	public function getNewNotifications($data)
	{
		$member = auth::getAuthMember();
		$newNotifications = ioc::getRepository('saMemberNotification')->getNewNotificationsForMember($member);

		if($newNotifications) {
			foreach($newNotifications as $k => $v) {
				$newNotifications[$k]['date_created'] = $newNotifications[$k]['date_created']->format('M d, Y H:i:s');
			}
		}

		$data['newNotifications'] = $newNotifications;

		return $data;
	}

	public function markNotificationsAsViewed($data)
	{
		$member = auth::getAuthMember();
		if($data['notifications']) {
			foreach($data['notifications'] as $notificationId) {
				$notification = ioc::getRepository('saMemberNotification')->findOneBy(array('id' => $notificationId, 'member' => $member));
				if($notification) {
					$notification->setIsViewed(true);
				}
			}
			app::$entityManager->flush();
		}

		return $data;
	}

	public function viewNotificationHistory()
	{
		$view = new \sacore\application\responses\View('member_notification_history');
		return $view;
	}

	/**
     * @return Json
     * @throws ModRequestAuthenticationException
     */
    public function frontGetCurrentUser()
    {
        /** @var saMemberUsers $user */
        $user = modRequest::request('auth.user');

        $json = new Json();
        $json->data['success'] = true;
        $json->data['msg'] = null;

        if(!$user) {
            $json->data['success'] = false;
            $json->data['msg'] = 'Unauthorized';

            return $json;
        }

        $json->data['user'] = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName()
        ];

        return $json;
    }
}
