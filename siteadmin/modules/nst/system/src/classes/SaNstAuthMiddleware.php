<?php
namespace nst\system;


use sacore\application\app;
use sacore\application\responses\Redirect;
use sa\member\auth;

class SaNstAuthMiddleware extends \sa\system\SaAuthMiddleware
{
    public function beforeRoute($request) {

        $redirect = parent::beforeRoute($request);

        $member = auth::getAuthMember();
        if($member->getMemberType() != 'Provider') {
            return new Redirect(app::get()->getRouter()->generate('sa_login'));
        }
        if($redirect) {
            return $redirect;
        }
    }
}