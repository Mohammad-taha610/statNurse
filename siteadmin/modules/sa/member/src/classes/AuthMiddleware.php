<?php
namespace sa\member;


use sacore\application\app;
use sacore\application\Middleware;
use sacore\application\responses\Redirect;
use sa\member\auth;

/**
 * Class AuthMiddleware
 * @package sa\member
 */
class AuthMiddleware extends Middleware
{
    /**
     * @param \sacore\application\Request $request
     * @return mixed
     */
    public function BeforeRoute($request)
    {        
        $auth = auth::getInstance();

        if (!$auth->isAuthenticated()) {
            $_SESSION['login_redirect'] = $request->getPathInfo();
            return new Redirect(app::get()->getRouter()->generate('member_login'));
        }

    }
}