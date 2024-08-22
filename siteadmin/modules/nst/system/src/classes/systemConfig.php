<?php

namespace nst\system;

use sacore\application\ioc;
use sacore\application\moduleConfig;
use sacore\application\modRequest;

class systemConfig extends moduleConfig
{
    static function initRoutes($routes)
    {
        // RESOURCES
        $routes->addWithOptionsAndName('css', 'system_css', '/siteadmin/system/css/{file}')->controller('NstSystemController@css');// 'permissions' => 'developer'
        $routes->addWithOptionsAndName('js', 'system_js', '/siteadmin/system/js/{file}')->controller('NstSystemController@js');// 'permissions' => 'developer'

    }

    static function init() {
        modRequest::listen('sa.system.get.all.states', 'NstSystemController@getAllStates', 1, null, true, false);

    }

    static function getNavigation()
    {

    }

    static function getCLICommands()
    {
        return array(
            ioc::staticGet('GenerateUserGroupsAndPermissionsCommand')
        );
    }
}