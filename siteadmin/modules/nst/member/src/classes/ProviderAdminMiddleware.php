<?php


namespace nst\member;


use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Middleware;
use sacore\application\responses\Redirect;
use sa\member\auth;
use sacore\utilities\notification;

class ProviderAdminMiddleware extends Middleware
{
    public function beforeRoute($request)
    {
        /** @var NstMember $member */
        $member = auth::getAuthMember();
        /** @var NstMemberUsers $user */
        $user = auth::getAuthUser();

        if($member->getProvider() && $user->getUserType() != 'Admin') {
            $notify = new notification();
            $notify->addNotification('danger', 'Permission Denied', 'The route you were trying to access is restricted to Admin users only.');
            return new Redirect(app::get()->getRouter()->generate('dashboard_home'));
        }
    }

}