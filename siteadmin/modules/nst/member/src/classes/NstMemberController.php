<?php

namespace nst\member;

use sacore\application\ioc;
use sa\member\auth;
use sa\member\memberController;
use \sacore\application\app;
use sacore\application\modRequest;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sa\member\saMember;
use sa\member\saMemberLoginKey;
use sa\member\saMemberUsers;
use sacore\utilities\Cookie;
use sacore\utilities\notification;
use Symfony\Component\HttpClient\HttpClient;


/**
 * @IOC_NAME="memberController"
 */
class NstMemberController extends memberController
{

    public function attemptLogin($request)
    {
        $loginKey = null;

        /** @var auth $auth */
        $auth = auth::getInstance();
        $result = $auth->logon($request->request->get('username'), $request->request->get('password'));

        if ($result) {
            /** @var saMember $member */
            $member = $auth->getAuthMember();

            if ($member) {
                if ($member->getMemberType() !== 'Provider') {
                    // redirect non-provider uses to log in screen again
                    Cookie::setCookie('rememberme_key', false, -1, '/');
                    $auth->logoff();
                    $notify = new notification();
                    $notify->addNotification('danger', 'Error', 'Invalid account type: Non-Provider.');

                    return new Redirect(app::get()->getRouter()->generate('member_login'));
                } else {
                    return parent::attemptLogin($request);
                }
            }
        } else {
            $notify = new notification();
            $notify->addNotification('danger', 'Error', 'Invalid email or username and password combination.');

            return new Redirect(app::get()->getRouter()->generate('member_login'));
        }

        return new Redirect(app::get()->getRouter()->generate('member_login'));
    }

    public function register($request)
	{
	    $publicRegistrationEnabled = app::get()->getConfiguration()->get('enable_public_member_signup')->getValue();

	    if(!$publicRegistrationEnabled) {
	        return $this->error404(true);
        }

		$view = new \sacore\application\responses\View('register', $this->viewLocation());

		if ($request->request) {
            $view->data = $request->request->all();
        }

        return $view;
	}

	public function attemptRegister($request)
	{
	    $saMember = ioc::staticResolve('saMember');
	    $publicRegistrationEnabled = app::get()->getConfiguration()->get('enable_public_member_signup')->getValue();

	    if(!$publicRegistrationEnabled) {
	        return $this->error404(true);
        }

		$notify = new notification();
		try {
            $reg_email = $request->request->get('email');
            $reg_password = $request->request->get('password');
            $reg_password2 = $request->request->get('password2');

            // copy $reg_email value to $request->request
            $request->request->set('email2', $reg_email);

            if ($reg_password != $reg_password2) {
                $notify->addNotification('danger', 'Error', 'Your passwords need to match!');
			    return $this->register($request->request->all());
            }

            $saMember::memberSignUp($request->request->all());

            $auth = \sa\member\auth::getInstance();
            $auth->logon($reg_email, $reg_password);

            return new Redirect(app::get()->getRouter()->generate('dashboard_home'));
		} catch( ValidateException $e) {
			$notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage() );
			return $this->register($request->request->all());
		}
	}

    public static function getDashboardItems($data)
    {
        $data[] = array(
            'module' => 'member',
            'icon' => 'las la-exclamation-triangle',
            'color' => 'bgl-primary text-primary',
            'name' => 'Unclaimed Shifts',
            'value' => '24'
        );
        $data[] = array(
            'module' => 'member',
            'icon' => 'las la-bell',
            'color' => 'bgl-warning text-warning',
            'name' => 'Shift Requests',
            'value' => '19'
        );
        $data[] = array(
            'module' => 'member',
            'icon' => 'las la-list-alt',
            'color' => 'bgl-danger text-danger',
            'name' => 'Shifts to Review',
            'value' => '10'
        );
        $data[] = array(
            'module' => 'member',
            'icon' => 'las la-dollar-sign',
            'color' => 'bgl-success text-success',
            'name' => 'Current Pay Period',
            'value' => '3/31/2021'
        );
        $data[] = array(
            'table' => true
        );
        return $data;
    }

    public function defaultDashboard()
    {
        return new Redirect(app::get()->getRouter()->generate('dashboard_home'));
    }

    public function resetPassword()
    {
        $view = new View('passwordreset', $this->viewLocation());
        return $view;
    }

    public function dashboard() {
        /** @var NstMember $member */
        $member = auth::getAuthMember();

        if($member?->getMemberType() != 'Provider') {
            return new Redirect(app::get()->getRouter()->generate('application_form'));
        }
        return parent::dashboard();
    }
    public static function memberLoginAsync($data) {
        $loginResult = parent::memberLoginAsync($data);
        return $loginResult;
    }

    public static function getMemberType($data) {
        $username = $data['username'];
        $nstMemberService = new NstMemberService();
        $memberType = $nstMemberService->getNstMemberUsersType($username);
        return [
            'memberType' => $memberType
        ];
    }
}
