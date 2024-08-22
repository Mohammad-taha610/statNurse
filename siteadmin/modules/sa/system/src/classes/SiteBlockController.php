<?php
namespace sa\system;

use \sacore\application\app;
use \sacore\application\controller;
use \sacore\application\Event;
use sacore\application\Request;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\utilities\notification;
use sacore\utilities\url;

class SiteBlockController extends controller
{
    public static function isSiteBlocked(Event $event) {

        $routeInfo = $event->getData('routeInfo');

        $allowed = $event->getData('allowed');

        if (!isset($_SESSION['site_access_allowed']))
            $_SESSION['site_access_allowed'] = false;
        
        if (!$_SESSION['site_access_allowed'] && app::get()->getConfiguration()->get('site_block')->getValue() && !$routeInfo->excludeFromSiteBlock && $routeInfo->id!='site_block_login' && $routeInfo->id!='sa_ping' && $routeInfo->id!='system_thread_route' && $routeInfo->id != 'api_v1_endpoint' && strpos($_SERVER['REQUEST_URI'], '/api/v1') === false) {
            $routeInfo = app::get()->findRouteById( 'site_block' );
            $allowed = false;

            $event->setData('routeInfo', $routeInfo);
            $event->setData('allowed', $allowed);

        }

    }

    /**
     * @param Request $request
     * @return View
     */
    public function site_blocked($request) {
        $view = new View('site_block');

        if (method_exists($view, 'setTemporaryDisableHTMLOptimization')) {
            $view->setTemporaryDisableHTMLOptimization(true);
        }

        return $view;
    }

    public function site_blocked_login($request)
    {
        $siteBlockPass = app::get()->getConfiguration()->get('site_block_password')->getValue();

        $data = $request->request;
        if ($data->get('password') == $siteBlockPass) {
            $_SESSION['site_access_allowed'] = true;

            if(!empty($siteBlockPass)) {
                return new Redirect($data->get('return_uri'));
            }

            return new Redirect( '/' );
        }

        $notify = new notification();
        $notify->addNotification('danger', 'Error', 'The password did not match');

        return $this->site_blocked($data->get('return_uri'));
    }
}