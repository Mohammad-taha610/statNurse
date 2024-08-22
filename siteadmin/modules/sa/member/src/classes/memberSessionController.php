<?php

namespace sa\member;

use sacore\application\controller;

class memberSessionController extends controller {

    public static function extendSession() {
        $auth = auth::getInstance();
        $auth->extendSession();
    }

    public static function logoffSession() {
        $auth = auth::getInstance();
        $auth->logoff();
    }
}