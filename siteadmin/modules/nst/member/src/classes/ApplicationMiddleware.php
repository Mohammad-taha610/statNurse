<?php


namespace nst\member;


use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Middleware;
use sacore\application\responses\Redirect;
use sa\member\auth;
use sacore\utilities\notification;

class ApplicationMiddleware extends Middleware
{
    public function beforeRoute($request)
    {
        //if ($request->getServerBag())
        //print_r($request->server->get('HTTP_HOST')); exit;
        // print_r($request->server->get('REQUEST_URI')); exit;

        if ($request->server->get('HTTP_HOST')=='app.nursestatky.com' && $request->server->get('REQUEST_URI')=='/') {

            return new Redirect(app::get()->getRouter()->generate('application_form'));
        }

        /*if($member->getProvider() && $user->getUserType() != 'Admin') {
            $notify = new notification();
            $notify->addNotification('danger', 'Permission Denied', 'The route you were trying to access is restricted to Admin users only.');
            return new Redirect(app::get()->getRouter()->generate('dashboard_home'));
        }*/
    }

}