<?php


namespace sa\system;


use sacore\application\app;
use sacore\application\Middleware;
use sacore\application\responses\Redirect;

/**
 * Class SaAuthMiddleware
 * @package sa\system
 */
class SaAuthMiddleware extends Middleware
{
    /**
     * @param \sacore\application\Request $request
     * @return mixed
     */
    public function BeforeRoute($request)
    {

        $auth = saAuth::getInstance();

        if (!$auth->isAuthenticated()) {
            $_SESSION['sa_login_redirect'] = $request->getPathInfo();
            return new Redirect(app::get()->getRouter()->generate('sa_login'));
        }


    }


}