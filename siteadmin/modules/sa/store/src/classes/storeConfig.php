<?php

namespace sa\store;

use sacore\application\CollectionConfigurator;
use sacore\application\controller;
use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\navItem;
use sacore\application\route;
use sacore\application\saRoute;

abstract class storeConfig extends moduleConfig
{
    const safe_mode_compatible = true;

    public static function getRoutes()
    {
        return [
            new saRoute(['id' => 'sa_store',
                'permissions' => 'store_update_install',
                'name' => 'SA Store',
                'route' => '/siteadmin/store',
                'controller' => 'sa\store\saStoreController@store']),

            new saRoute(['id' => 'sa_store_check_for_updates',
                'permissions' => 'store_update_install',
                'name' => 'Check For Updates',
                'route' => '/siteadmin/store/checkforupdates',
                'controller' => 'sa\store\saStoreController@sa_store_check_for_updates']),

            new saRoute(['id' => 'sa_module_details',
                'permissions' => 'store_update_install',
                'name' => 'Module Details',
                'route' => '/siteadmin/store/details',
                'controller' => 'sa\store\saStoreController@details']),

            new saRoute(['id' => 'sa_module_details_picture',
                'name' => 'Module Details Picture',
                'route' => '^/siteadmin/store/details/pictures/[a-zA_Z0-9-_\.]{1,}$',
                'controller' => 'sa\store\saStoreController@detailPicture']),

            new saRoute(['id' => 'sa_module_install',
                'permissions' => 'store_update_install',
                'name' => 'Module Install',
                'route' => '/siteadmin/store/install',
                'controller' => 'sa\store\saStoreController@install']),

            new saRoute(['id' => 'sa_module_update',
                'permissions' => 'store_update_install',
                'name' => 'Module Update',
                'route' => '/siteadmin/store/update',
                'controller' => 'sa\store\saStoreController@update']),

            new saRoute(['id' => 'sa_module_uninstall',
                'permissions' => 'store_update_install',
                'name' => 'Module Uninstall',
                'route' => '/siteadmin/store/uninstall',
                'controller' => 'sa\store\saStoreController@uninstall']),

            new saRoute(['id' => 'sa_module_updateAll',
                'permissions' => 'store_update_install',
                'name' => 'All Module Update',
                'route' => '/siteadmin/store/update/all',
                'controller' => 'sa\store\saStoreController@updateAll']),

            new saRoute(['id' => 'sa_module_composer_log',
                'name' => 'Composer Log',
                'route' => '/siteadmin/store/log',
                'controller' => 'sa\store\saStoreController@log']),

            new saRoute(['id' => 'sa_module_composer',
                'name' => 'Composer',
                'route' => '/siteadmin/store/composer',
                'controller' => 'sa\store\saStoreController@runComposer']),

            //new saRoute(array('id'=>'sa_module_installSuccess', 'name'=>'Module Install Successful', 'route'=>'/siteadmin/store/success', 'controller'=>'sa\store\saStoreController@installSuccess')),
            //new saRoute(array('id'=>'sa_module_updateSuccess', 'name'=>'Module Update Successful', 'route'=>'/siteadmin/store/success', 'controller'=>'sa\store\saStoreController@installSuccess')),
            //new saRoute(array('id'=>'sa_module_uninstallSuccess', 'name'=>'Module Uninstall Successful', 'route'=>'/siteadmin/store/uninstall_success', 'controller'=>'sa\store\saStoreController@uninstallSuccess')),

            new saRoute(['id' => 'sa_module_buy', 'name' => 'Buy Module', 'permissions' => 'store_update_install', 'route' => '/siteadmin/store/buy', 'controller' => 'sa\store\saStoreController@buy']),

            new saRoute(['id' => 'run_composer_post_tasks', 'name' => 'Run Post Composer Tasks', 'route' => '/siteadmin/store/composer-post-run', 'controller' => 'saStoreController@composerPostRunTasks']),

        ];
    }

    /**
     * @param  CollectionConfigurator  $routes
     */
    public static function initRoutes($routes)
    {
        $routes
            ->addWithOptionsAndName('Store - Browse All', 'sa_store', '/siteadmin/store')
            ->controller('saStoreController@store')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Store - CSS', 'sa_store_css', '/siteadmin/store/css/{file}')
            ->controller('saStoreController@css')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Module Details Photo', 'sa_module_details_photo', '/siteadmin/store/details/photos/{file}')
            ->controller('saStoreController@detailPicture')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Module - Buy', 'sa_module_buy', '/siteadmin/store/buy')
            ->controller('saStoreController@buy')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Module Details', 'sa_module_details', '/siteadmin/store/details')
            ->controller('saStoreController@details')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Module Uninstall', 'sa_module_uninstall', '/siteadmin/store/uninstall')
            ->controller('saStoreController@uninstall')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Check For Updates', 'sa_store_check_for_updates', '/siteadmin/store/checkforupdates')
            ->controller('saStoreController@saStoreCheckForUpdates')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('All module Update', 'sa_module_updateAll', '/siteadmin/store/update/all')
            ->controller('saStoreController@updateAll')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Module Install', 'sa_module_install', '/siteadmin/store/install')
            ->controller('saStoreController@install')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Composer Log', 'sa_module_composer_log', '/siteadmin/store/log')
            ->controller('saStoreController@log')
            ->middleware('SaAuthMiddleware');
        $routes
            ->addWithOptionsAndName('Module Update', 'sa_module_update', '/siteadmin/store/update')
            ->controller('saStoreController@update')
            ->middleware('SaAuthMiddleware');
    }

    public static function getPermissions()
    {
        $permissions = [];
        $permissions['store_update_install'] = 'Install/Update Modules';

        return $permissions;
    }

    public static function getSettings()
    {
        $module_settings = [
            'enable_store_update_widget' => ['modules' => 'Store', 'type' => 'boolean', 'default' => true],
            'enable_store' => ['modules' => 'Store', 'type' => 'boolean', 'default' => true],
            'store_only_show_installed' => ['modules' => 'Store', 'type' => 'boolean', 'default' => false],
            'store_repositories' => ['modules' => 'Store', 'type' => 'array', 'default' => ['https://pkg.elinkstaging.com']],
            'allow_cli_composer' => ['modules' => 'Store', 'type' => 'boolean', 'default' => false],
        ];

        return $module_settings;
    }

    public static function getNavigation()
    {
        return [
            new navItem(['id' => 'sa_store', 'name' => 'SA Store', 'routeid' => 'sa_store', 'icon' => 'fa fa-shopping-cart', 'parent' => 'siteadmin_root']),
            new navItem(['id' => 'sa_store_all', 'name' => 'Browse All', 'routeid' => 'sa_store', 'subpattern' => '/siteadmin/store[0-9]{1,}', 'icon' => 'fa fa-th', 'parent' => 'sa_store']),
            new navItem(['id' => 'sa_store_check_for_updates', 'name' => 'Check For Updates', 'routeid' => 'sa_store_check_for_updates', 'subpattern' => '/siteadmin/store/checkforupdates', 'icon' => 'fa fa-arrow-circle-up', 'parent' => 'sa_store']),
        ];
    }

    public static function postInit()
    {
        modRequest::request('sa.dashboard.add_widget', null, ['id' => 'sa.dashboard.updates', 'name' => 'System Updates', 'action' => 'saStoreController@getUpdatesWidget']);
    }

    public static function init()
    {
        modRequest::listen('sa.store.get_information', 'saStoreController@ajaxGetInformation', 1, null, true, true);

        modRequest::listen('store.post.tasks', 'saStoreController@registerPostRunTasks');
    }
}
