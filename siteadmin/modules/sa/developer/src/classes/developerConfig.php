<?php

namespace sa\developer;

use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\navItem;
use sacore\application\saRoute;
use sacore\application\staticResourceRoute;

abstract class developerConfig extends moduleConfig
{
    const safe_mode_compatible = true;

    public static function init()
    {
        modRequest::listen('developer.test_route', 'saDeveloperController@testRoutes', 1, null, true, true);
        modRequest::listen('developer.execute.unittesting', 'saUnitTestingController@executeUnitTesting', 1, null, true, true);

        modRequest::listen('developer.timing', 'saTimingProfileController@showTimingAjax', 1, null, true, true);
        modRequest::listen('developer.ajax_port_test', 'saPortTesterController@ajaxTestPort', 1, null, true, true);

        modRequest::listen('sa.performance_alerts', 'saDeveloperExtModules@getSaPerformanceAlerts', 1, null, true, true);
    }

    public static function getCLICommands()
    {
        return [
            ioc::staticGet('ShowRoutesCommand'),
            ioc::staticGet('ShowIOCCommand'),
            ioc::staticGet('ShowInstalledPackagesCommand'),
            ioc::staticGet('RunUnitTestsCommand'),

        ];
    }

//    static function getRoutes()
//    {
//        return array(
//            new saRoute(array('id'=>'sa_developer', 'permissions'=>'developer', 'name'=>'Developer', 'route'=>'/siteadmin/developer', 'controller'=>'saDeveloperController@dashboard')),
//            new saRoute(array('id'=>'sa_developer_create_module', 'permissions'=>'developer', 'name'=>'Create Module', 'route'=>'/siteadmin/developer/module/create', 'controller'=>'saDeveloperController@createModule')),
//            new saRoute(array('id'=>'sa_developer_create_module_post', 'permissions'=>'developer', 'name'=>'Create Module', 'method'=>'POST', 'route'=>'/siteadmin/developer/module/create', 'controller'=>'saDeveloperController@saveModule')),
//            new saRoute(array('id'=>'sa_developer_show_routes', 'permissions'=>'developer', 'name'=>'View Routes', 'route'=>'/siteadmin/developer/routes', 'controller'=>'saDeveloperController@showRoutes')),
//            new saRoute(array('id'=>'sa_developer_show_ioc', 'permissions'=>'developer', 'name'=>'View IOC', 'route'=>'/siteadmin/developer/ioc', 'controller'=>'saDeveloperController@showIOC')),
//
//            new saRoute(array('id'=>'sa_developer_show_session', 'permissions'=>'developer', 'name'=>'View Session', 'route'=>'/siteadmin/developer/session', 'controller'=>'saDeveloperController@showSession')),
//            new saRoute(array('id'=>'sa_developer_unit_testing', 'permissions'=>'developer', 'name'=>'Unit Testing', 'route'=>'/siteadmin/developer/unittesting', 'controller'=>'saUnitTestingController@unitTesting')),
//            new saRoute(array('id'=>'sa_developer_php_info', 'permissions'=>'developer', 'name'=>'PHP Info', 'route'=>'/siteadmin/developer/phpinfo', 'controller'=>'saDeveloperController@showPHPInfo')),
//            new saRoute(array('id'=>'sa_developer_doctrine', 'permissions'=>'developer', 'name'=>'Doctrine', 'route'=>'/siteadmin/developer/doctrine', 'controller'=>'saDeveloperController@showDoctrine')),
//            new saRoute(array('id'=>'sa_developer_doctrine_execute', 'permissions'=>'developer', 'name'=>'Doctrine', 'route'=>'/siteadmin/developer/doctrine/execute', 'controller'=>'saDeveloperController@executeDoctrine')),
//            new saRoute(array('id'=>'sa_developer_show_installed_pkgs', 'permissions'=>'developer', 'name'=>'Installed Packages', 'route'=>'/siteadmin/developer/composer/installed_pkgs', 'controller'=>'saDeveloperController@showInstalledPkgs')),
//            new saRoute(array('id'=>'sa_developer_show_registered_events_requests', 'permissions'=>'developer', 'name'=>'Show registered events/listeners', 'route'=>'/siteadmin/developer/events_listeners', 'controller'=>'saDeveloperController@showEventsListeners')),
//            new saRoute(array('id'=>'sa_developer_code_generation', 'permissions'=>'developer', 'name'=>'Code Generation', 'route'=>'/siteadmin/developer/code_generation', 'controller'=>'saDeveloperController@showCodeGeneration')),
//            new saRoute(array('id'=>'sa_developer_code_generation_exec', 'permissions'=>'developer', 'name'=>'Code Generation', 'route'=>'/siteadmin/developer/code_generation/exec', 'controller'=>'saDeveloperController@executeCodeGeneration')),
//            new saRoute(array('id'=>'sa_developer_doctrine_entities', 'permissions'=>'developer', 'name'=>'Doctrine Entities', 'route'=>'/siteadmin/developer/doctrine/entities', 'controller'=>'saDeveloperController@doctrineEntities')),
//            new saRoute(array('id'=>'sa_developer_doctrine_cache_stats', 'permissions'=>'developer', 'name'=>'Doctrine Cache Stats', 'route'=>'/siteadmin/developer/cache_stats_doctrine', 'controller'=>'saDeveloperController@doctrineCacheStats')),
//
//            new saRoute(array('id'=>'sa_developer_object_export', 'permissions'=>'developer', 'name'=>'Object Export', 'route'=>'/siteadmin/developer/object_export', 'controller'=>'ObjectExportController@displayEntities')),
//            new saRoute(array('id'=>'sa_developer_object_export_zip', 'permissions'=>'developer', 'name'=>'Object Export', 'method'=>'POST', 'route'=>'/siteadmin/developer/object_export', 'controller'=>'ObjectExportController@entitiesExport')),
//
//            new saRoute(array('id'=>'sa_developer_timing_profile', 'permissions'=>'developer', 'name'=>'Timing Profile', 'route'=>'/siteadmin/developer/timing-profile', 'controller'=>'saTimingProfileController@showTiming')),
//
//            new saRoute(array('id'=>'sa_developer_port_tester', 'permissions'=>'developer', 'name'=>'Outbound Port Tester', 'route'=>'/siteadmin/developer/port-tester', 'controller'=>'saPortTesterController@portTester')),
//            new saRoute(array('id'=>'sa_developer_extended_modules', 'permissions'=>'developer', 'name'=>'Extended Modules', 'route'=>'/siteadmin/developer/extended-modules', 'controller'=>'saDeveloperExtModules@showList')),
//            new saRoute(array('id'=>'sa_developer_extended_modules_save', 'permissions'=>'developer', 'name'=>'Save Extended Modules', 'route'=>'/siteadmin/developer/extended-modules', 'controller'=>'saDeveloperExtModules@saveModuleReasons', 'method' => 'POST')),
//
//            new staticResourceRoute(array('id'=>'sa_developer_css', 'name'=>'css', 'route'=>'^/siteadmin/developer/css/[a-zA_Z0-9-_\.]{1,}$', 'controller'=>'saDeveloperController@css')),
//            new staticResourceRoute(array('id'=>'sa_developer_js', 'name'=>'js', 'route'=>'^/siteadmin/developer/js/[a-zA_Z0-9-_\.]{1,}$', 'controller'=>'saDeveloperController@js')),
//
//        );
//    }

    public static function initRoutes($routes)
    {
        /** API ROUTES */
        $routes->addWithOptionsAndName('Developer', 'sa_developer', '/siteadmin/developer')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@dashboard')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'        $routes->addWithOptionsAndName( 'Create Module', 'sa_developer_create_module', '/siteadmin/developer/module/create')->controller('saDeveloperController@createModule'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Create Module', 'sa_developer_create_module', '/siteadmin/developer/module/create')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@createModule')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Create Module', 'sa_developer_create_module_post', '/siteadmin/developer/module/create')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@saveModule')->methods(['POST'])->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('View Routes', 'sa_developer_show_routes', '/siteadmin/developer/module/routes')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showRoutes')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('View IOC', 'sa_developer_show_ioc', '/siteadmin/developer/ioc')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showIOC')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'

        $routes->addWithOptionsAndName('View Session', 'sa_developer_show_session', '/siteadmin/developer/ioc')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showIOC')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Unit Testing', 'sa_developer_unit_testing', '/siteadmin/developer/unittesting')->defaults(['route_permissions' => ['developer']])->controller('saUnitTestingController@unitTesting')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('PHP Info', 'sa_developer_php_info', '/siteadmin/developer/phpinfo')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showPHPInfo')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Doctrine', 'sa_developer_doctrine', '/siteadmin/developer/doctrine')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showDoctrine')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Doctrine', 'sa_developer_doctrine_execute', '/siteadmin/developer/doctrine/execute')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@executeDoctrine')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'

        $routes->addWithOptionsAndName('Installed Packages', 'sa_developer_show_installed_pkgs', '/siteadmin/developer/composer/installed-pkgs')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showInstalledPkgs')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'

        $routes->addWithOptionsAndName('Show registered events/listeners', 'sa_developer_show_registered_events_requests', '/siteadmin/developer/event-listeners')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showEventsListeners')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Code Generation', 'sa_developer_code_generation', '/siteadmin/developer/code-generation')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@showCodeGeneration')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Code Generation', 'sa_developer_code_generation_exec', '/siteadmin/developer/code-generation/exec')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@executeCodeGeneration')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Doctrine Entities', 'sa_developer_doctrine_entities', '/siteadmin/developer/doctrine/entities')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@doctrineEntities')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Doctrine Cache', 'sa_developer_doctrine_cache_stats', '/siteadmin/developer/cache-stats-doctrine')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@doctrineCacheStats')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'

        $routes->addWithOptionsAndName('Object Export', 'sa_developer_object_export', '/siteadmin/developer/object-export')->defaults(['route_permissions' => ['developer']])->controller('ObjectExportController@displayEntities')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Object Export', 'sa_developer_object_export_zip', '/siteadmin/developer/object-export')->defaults(['route_permissions' => ['developer']])->controller('ObjectExportController@entitiesExport')->methods(['POST'])->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'

        $routes->addWithOptionsAndName('Timing Profile', 'sa_developer_timing_profile', '/siteadmin/developer/timing-profile')->defaults(['route_permissions' => ['developer']])->controller('saTimingProfileController@showTiming')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'

        $routes->addWithOptionsAndName('Outbound Port Tester', 'sa_developer_port_tester', '/siteadmin/developer/port-tester')->defaults(['route_permissions' => ['developer']])->controller('saPortTesterController@portTester')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('Extended Modules', 'sa_developer_extended_modules', '/siteadmin/developer/extended-modules')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperExtModules@showList')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
//        $routes->addWithOptionsAndName('Save Extended Modules', 'sa_developer_extended_modules', '/siteadmin/developer/extended-modules')->controller('saDeveloperExtModules@saveModulesReasons')->methods(["POST"]);// 'permissions' => 'developer'

        $routes->addWithOptionsAndName('css', 'sa_developer_css', '/siteadmin/developer/css/{file}')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@css')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
        $routes->addWithOptionsAndName('js', 'sa_developer_js', '/siteadmin/developer/js/{file}')->defaults(['route_permissions' => ['developer']])->controller('saDeveloperController@js')->middleware('SaPermissionMiddleware'); // 'permissions' => 'developer'
    }

    public static function getNavigation()
    {
        return [
            new navItem(['id' => 'sa_developer', 'name' => 'Developer', 'icon' => 'fa fa-cubes', 'parent' => 'siteadmin_root']),
            new navItem(['id' => 'sa_codegeneration', 'name' => 'Code Generation', 'routeid' => 'sa_developer_code_generation', 'icon' => 'fa fa-terminal', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_phpinfo', 'name' => 'PHP Info', 'routeid' => 'sa_developer_php_info', 'icon' => 'fa fa-code', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_unittesting', 'name' => 'Unit Testing', 'routeid' => 'sa_developer_unit_testing', 'icon' => 'fa fa-stethoscope', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_viewroutes', 'name' => 'Show Routes', 'routeid' => 'sa_developer_show_routes', 'icon' => 'fa fa-globe', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_viewsession', 'name' => 'Show Session', 'routeid' => 'sa_developer_show_session', 'icon' => 'fa fa-table', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_doctrine', 'subpattern' => '^/siteadmin/developer/doctrine', 'name' => 'Doctrine Commands', 'routeid' => 'sa_developer_doctrine', 'icon' => 'fa fa-database', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_doctrine_cache', 'name' => 'Doctrine Cache Stats', 'routeid' => 'sa_developer_doctrine_cache_stats', 'icon' => 'fa fa-database', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_composer_installed_pkgs', 'name' => 'Installed Packages', 'routeid' => 'sa_developer_show_installed_pkgs', 'icon' => 'fab fa-dropbox', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_developer_show_registered_events_requests', 'name' => 'Registered events/listeners', 'routeid' => 'sa_developer_show_registered_events_requests', 'icon' => 'fa fa-bars', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_create_module', 'name' => 'Create Module', 'routeid' => 'sa_developer_create_module', 'icon' => 'fa fa-plus', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_export_objects', 'name' => 'Export Objects', 'routeid' => 'sa_developer_object_export', 'icon' => 'fa fa-save', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_extended_modules', 'name' => 'Extended Modules', 'routeid' => 'sa_developer_extended_modules', 'icon' => 'fa fa-building', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_viewioc', 'name' => 'Show IOC', 'routeid' => 'sa_developer_show_ioc', 'icon' => 'fa fa-th-list', 'parent' => 'sa_developer']),

            new navItem(['id' => 'sa_viewtiming', 'name' => 'Timing Profile', 'routeid' => 'sa_developer_timing_profile', 'icon' => 'fa fa-th-list', 'parent' => 'sa_developer']),
            new navItem(['id' => 'sa_port_tester', 'name' => 'Outbound Port Tester', 'routeid' => 'sa_developer_port_tester', 'icon' => 'fa fa-th-list', 'parent' => 'sa_developer']),

        ];
    }
}
