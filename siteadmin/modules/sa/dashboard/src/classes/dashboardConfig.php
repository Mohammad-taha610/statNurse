<?php

namespace sa\dashboard;

use sacore\application\CollectionConfigurator;
use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\navItem;
use sacore\application\route;
use sacore\application\saRoute;
use sacore\application\staticResourceRoute;

abstract class dashboardConfig extends moduleConfig
{
    const safe_mode_compatible = true;

//    static function getRoutes()
//    {
//        return array(
    ////            new route( array('id'=>'default', 'name'=>'Welcome', 'route'=>'*', 'test_action'=>'dashboardController@testDefaultRoute', 'controller'=>'sa\dashboard\dashboardController@welcome', 'priority'=>route::PRIORITY_LOW )),
    ////            new saRoute(array('id'=>'siteadmin', 'name'=>'Site Admin', 'route'=>'/siteadmin', 'forward_to_route'=>'/siteadmin/dashboard')),
    ////            new saRoute(array('id'=>'sa_dashboard', 'name'=>'Dashboard', 'route'=>'/siteadmin/dashboard', 'controller'=>'sa\dashboard\saDashboardController@dashboard')),
    ////            new saRoute(array('id'=>'sa_dashboard_get_widgets_ajax', 'name'=>'Dashboard Get Widgets AJAX', 'route'=>'/siteadmin/dashboard/widgets/ajax', 'controller'=>'sa\dashboard\saDashboardController@getWidgetsHtmlAjax')),
    ////
    ////            new staticResourceRoute(array( 'id'=>'sa_dashboard_css', 'name'=>'images', 'route'=>'^/siteadmin/dashboard/css/[a-zA-Z0-9-_\.]{1,}$', 'controller'=>'saDashboardController@css' )),
    ////            new staticResourceRoute(array( 'id'=>'sa_dashboard_js', 'name'=>'js', 'route'=>'^/siteadmin/dashboard/js/[a-zA-Z0-9-_\.]{1,}$', 'controller'=>'saDashboardController@js' )),
    ////            new staticResourceRoute(array( 'id'=>'sa_dashboard_img', 'name'=>'img', 'route'=>'^/siteadmin/dashboard/img/[a-zA-Z0-9-_\.]{1,}$', 'controller'=>'saDashboardController@img' )),
//
//
//        );
//    }

    /**
     * @param  CollectionConfigurator  $routes
     */
    public static function initRoutes($routes)
    {
        /** API ROUTES */
        $routes->addWithOptionsAndName('Site Administrator-Redirect1', 'siteadmin-reditect1', '/siteadmin')->forward('/siteadmin/dashboard');
        $routes->addWithOptionsAndName('Site Administrator-Redirect2', 'siteadmin-reditect2', '/siteadmin/')->forward('/siteadmin/dashboard');
        $routes->addWithOptionsAndName('Dashboard', 'sa_dashboard', '/siteadmin/dashboard')->controller('saDashboardController@dashboard')->middleware('SaAuthMiddleware');

        $routes->addWithOptions('sa_dashboard_css', '/siteadmin/dashboard/css/{file}')->controller('saDashboardController@css')->middleware('SaAuthMiddleware');
        $routes->addWithOptions('sa_dashboard_img', '/siteadmin/dashboard/img/{file}')->controller('saDashboardController@img')->middleware('SaAuthMiddleware');
        $routes->addWithOptions('sa_dashboard_js', '/siteadmin/dashboard/js/{file}')->controller('saDashboardController@js')->middleware('SaAuthMiddleware');
        $routes->addWithOptions('sa_dashboard_get_widgets_ajax', '/siteadmin/dashboard/widgets/ajax')->controller('saDashboardController@getWidgetsHtmlAjax')->middleware('SaAuthMiddleware');
    }

    public static function getNavigation()
    {
        return [
            new navItem(['id' => 'sa_dashboard', 'name' => 'Dashboard', 'routeid' => 'sa_dashboard', 'icon' => 'fa fa-dashboard', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH]),
        ];
    }

    public static function init()
    {
        modRequest::listen('sa.dashboard.add_widget', 'saDashboardController@addDashboardWidget');
        modRequest::listen('sa.dashboard.save_settings', 'saDashboardController@saveSettings', 1, null, true, true);
    }
}
