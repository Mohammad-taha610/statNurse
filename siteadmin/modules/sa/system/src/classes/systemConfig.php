<?php
namespace sa\system;
use sacore\application\app;
use sacore\application\CollectionConfigurator;
use sacore\application\ioc;
use sacore\application\MiddlewareManager;
use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\navItem;

class systemConfig extends moduleConfig
{
    const safe_mode_compatible = true;

    /**
     * @param CollectionConfigurator $routes
     */
    static function initRoutes($routes)
    {

        /** API ROUTES */
        $routes->addWithOptionsAndName( 'API User Login', 'api_sa_user_login', '/siteadmin/api/login')->controller('SaUserApiController@login');

        /** WEB ROUTES */


        $routes->addWithOptionsAndName('Site Block', 'site_block', '/site_blocked')->controller('SiteBlockController@site_blocked');
        $routes->addWithOptionsAndName('Site Block Login', 'site_block_login', '/site_blocked')->controller('SiteBlockController@site_blocked_login')->methods(['POST']);


        $routes->addWithOptions('testing_unit_testing_route', '/siteadmin/loginLeaveMeInvalidImJustATester')->controller('sa\system\saSystemController@login');
        $routes->addWithOptions('sa_ping', '/siteadmin/system/ping2')->controller('systemController@ping')->methods(["POST"]);  //, 'excludeFromAuth'=>true)),

        $routes->addWithOptions('robots_txt', '/robots.txt')->controller('systemController@robots');

        $routes->addWithOptions('sitemap_xml', '/sitemap.xml')->controller('systemController@sitemapXML');
        $routes->addWithOptions('sitemap_json', '/sitemap.json')->controller('systemController@sitemapJSON');
        $routes->addWithOptions('sitemap_html', '/sitemap')->controller('systemController@sitemapHTML');

        $routes->addWithOptionsAndName('SiteAdmin Login', 'sa_login', '/siteadmin/login')->controller('SaAuthController@login');
        $routes->addWithOptionsAndName('SiteAdmin Logoff', 'sa_logoff', '/siteadmin/logoff')->controller('SaAuthController@logoff');
        $routes->addWithOptions('sa_loginattempt', '/siteadmin/login')->controller('SaAuthController@attemptLogin')->methods(['POST']);

        $routes->addWithOptions('sa_import_location_data', '/siteadmin/system/import-location-data')->controller('systemController@importLocationData');//, 'excludeFromAuth'=>true)),

        $routes->addWithOptions('sa_permission_denied', '/siteadmin/denied')->controller('SaAuthController@showPermissionDenied');//, 'excludeFromAuth'=>true, 'Permission Denied')),
        $routes->addWithOptions('sa_humanverify', '/siteadmin/humanverify')->controller('SaAuthController@humanVerify');//, 'protected'=>false )),
        $routes->addWithOptions('sa_humanverifypost', '/siteadmin/humanverify')->controller('SaAuthController@humanVerifyAttempt')->methods(['POST']);//, 'protected'=>false )),
        $routes->addWithOptions('sa_machineverify', '/siteadmin/machineverify')->controller('SaAuthController@machineVerify')->middleware('SaAuthMiddleware');//, 'protected'=>true )),
        $routes->addWithOptions('sa_location_blocked', '/siteadmin/location-restricted')->controller('SaAuthController@loginLocationRestricted')->middleware('SaAuthMiddleware');//, 'protected'=>true )),
        $routes->addWithOptions('sa_two_factor_verify', '/siteadmin/two-factor-verify')->controller('SaAuthController@twoFactorVerify')->middleware('SaAuthMiddleware');//);//, 'protected'=>true )),


//        new resourceRoute( array('route'=>'^/build/combined/css/[a-zA_Z0-9-_\.]{1,}.css$')->controller('systemController@getCSSBuild') ),
//        new resourceRoute( array('route'=>'^/build/combined/js/[a-zA_Z0-9-_\.]{1,}.js$')->controller('systemController@getJSBuild') ),
//        new resourceRoute( array('route'=>'/build/combined/sprite.png')->controller('systemController@getSpriteBuildImage') ),
//        new resourceRoute( array('route'=>'/build/combined/sprite.css')->controller('systemController@getSpriteBuildCSS') ),

        // MODULE RESOURCES
        $routes->addWithOptions('system_images', '/siteadmin/system/images/{file}')->controller('sa\system\saSystemController@images');
        $routes->addWithOptions('system_js', '/siteadmin/system/js/{file}')->controller('saSystemController@js');
        $routes->addWithOptions('system_css', '/siteadmin/system/css/{file}')->controller('saSystemController@css');

        // THEME RESOURCES
        $routes->addWithOptions('theme_resources', '/themes/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@themeResource');
        $routes->addWithOptions('theme_resources_build', '/build/themes/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@themeResource');

        //COMPONENT RESOURCE ROUTES
        $routes->addWithOptions('component_resources', '/components/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@componentResource');
        $routes->addWithOptions('component_resources_build', '/build/components/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@componentResource');

        //SYSTEM RESOURCE ROUTES
        $routes->addWithOptions('system_resources', '/system/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@systemResource');
        $routes->addWithOptions('system_resources_build', '/build/system/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@systemResource');

        //VENDOR ROUTES
        $routes->addWithOptions('vendor_resources', '/vendor/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@vendorResource');
        $routes->addWithOptions('vendor_resources_build', '/build/vendor/{path}')->requirements(['path'=>'([a-zA-Z0-9\.\-_]{1,}/){0,}[a-zA-Z0-9\.\-_]{1,}(.js|.css|.jpg|.png|.gif|.otf|.eot|.svg|.ttf|.woff|.woff2)$'])->controller('sa\system\systemController@vendorResource');

        //new route(array('route'=>'/syncresources')->controller('sa\system\systemController@syncResources')),

        // SETTINGS ROUTES
        $routes->addWithOptions('sa_settings', '/siteadmin/settings')->controller('saSettingsController@viewSettings');//, 'permissions'=>'system_view_settings')),
        $routes->addWithOptions('sa_settings_post',  '/siteadmin/settings')->controller('saSettingsController@saveSettings')->methods(['POST']);;//, 'method' => "POST", 'permissions'=>'system_edit_settings',)),

        $routes->addWithOptions('sa_settings_modal', '/siteadmin/settings/modal')->controller('saSettingsController@viewSettings');//, 'permissions'=>'system_view_settings')),
        $routes->addWithOptions('sa_settings_modal_post', '/siteadmin/settings/modal')->controller('saSettingsController@saveSettings')->methods(['POST']);//, 'method' => "POST", 'permissions'=>'system_edit_settings')),


//        $routes->addWithOptions('sa_frontend_edit', '/siteadmin/editor')->controller('saFrontEditorController@siteFrontEditor');//, 'protected'=>true )),
//        $routes->addWithOptions('sa_frontend_edit_sitemap', '/siteadmin/editor/sitemap')->controller('saFrontEditorController@siteFrontEditorSitemap');//, 'protected'=>true )),
        $routes->addWithOptionsAndName('SA User Groups', 'sa_sausergroups', '/siteadmin/sausergroups')->defaults(['route_permissions' => []])->controller('saUserGroupController@manageSAUserGroups')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Create SA User Group', 'sa_sausergroup_create', '/siteadmin/sausergroup/create')->defaults(['route_permissions' => []])->controller('saUserGroupController@createSaUserGroup')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Edit SA User Group', 'sa_sausergroup_edit', '/siteadmin/sausergroup/{id}/edit')->defaults(['route_permissions' => []])->controller('saUserGroupController@editSaUserGroup')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Delete SA User Group', 'sa_sausergroup_delete', '/siteadmin/sausergroup/{id}/delete')->defaults(['route_permissions' => []])->controller('saUserGroupController@deleteSaUserGroup')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');

        $routes->addWithOptionsAndName('SA User Group Permissions', 'sa_sausergroup_permissions', '/siteadmin/sausergroup/permissions')->defaults(['route_permissions' => []])->controller('saUserGroupPermissionController@manageSAUserGroupPermissions')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Create SA User Group Permissions', 'sa_sausergroup_permission_create', '/siteadmin/sausergroup/permission/create')->defaults(['route_permissions' => []])->controller('saUserGroupPermissionController@createSaUserGroupPermission')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Edit SA User Group Permission', 'sa_sausergroup_permission_edit', '/siteadmin/sausergroup/permission/{id}/edit')->defaults(['route_permissions' => []])->controller('saUserGroupPermissionController@editSaUserGroupPermission')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Delete SA User Group Permission', 'sa_sausergroup_permission_delete', '/siteadmin/sausergroup/permission/{id}/delete')->defaults(['route_permissions' => []])->controller('saUserGroupPermissionController@deleteSaUserGroupPermission')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');

        $routes->addWithOptionsAndName('SA Users', 'sa_sausers', '/siteadmin/sausers')->defaults(['route_permissions' => ['system_list_users']])->controller('saSystemController@manageSAUsers')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_list_users')),
        $routes->addWithOptionsAndName('Create User','sa_sausers_create', '/siteadmin/sausers/create')->defaults(['route_permissions' => ['system_add_user']])->controller('saSystemController@editSAUsers')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_add_user')),
        $routes->addWithOptionsAndName('Edit User','sa_sausers_edit', '/siteadmin/sausers/{id}/edit')->defaults(['route_permissions' => ['system_view_user']])->controller('saSystemController@editSAUsers')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_view_user')),
        $routes->addWithOptionsAndName('Save User','sa_sausers_save', '/siteadmin/sausers/{id}/edit')->defaults(['route_permissions' => ['system_save_user']])->controller('saSystemController@saveSAUsers')->methods(['POST'])->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_save_user', 'method'=>'POST')),
        $routes->addWithOptionsAndName('Delete User','sa_sausers_delete', '/siteadmin/sausers/{id}/delete')->defaults(['route_permissions' => ['system_delete_user']])->controller('saSystemController@deleteSAUsers')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_delete_user')),
        $routes->addWithOptions('sa_sausers_deactivate_device', '/siteadmin/sausers/{userId}/deactivate/{deviceId}')->defaults(['route_permissions' => ['system_view_user']])->controller('saSystemController@deactivateSAUserDevice')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_view_user')),

        $routes->addWithOptions('sa_default_data', '/siteadmin/default-data')->defaults(['route_permissions' => ['system_default_data']])->controller('saDefaultDataController@defaultDataIndex')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_default_data')),
        $routes->addWithOptions('sa_default_data_import', '/siteadmin/default-data/import')->defaults(['route_permissions' => ['system_default_data_import']])->controller('saDefaultDataController@defaultDataImport')->methods(["POST"])->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_default_data_import')),
        $routes->addWithOptions('sa_delete_install_script', '/siteadmin/remove-script')->controller('saSystemController@deleteInstallScript')->middleware('SaAuthMiddleware');

        $routes->addWithOptions('system_safemode', '/siteadmin/safe-mode')->controller('saSystemController@safeMode')->middleware('SaAuthMiddleware');
        $routes->addWithOptions('system_safemode_disable', '/siteadmin/safe-mode/disable')->controller('saSystemController@safeModeDisable')->middleware('SaAuthMiddleware');

        $routes->addWithOptions('sa_system_generate_sprite', '/siteadmin/generate-sprite')->defaults(['route_permissions' => ['system_sprite_generation']])->controller('saSystemController@generateSpriteResources')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions' => 'system_sprite_generation')),


        $routes->addWithOptions('sa_flush_build', '/siteadmin/assets/flush')->defaults(['route_permissions' => ['system_manage_cache']])->controller('saAssetManagerController@flush')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_manage_cache')),
        $routes->addWithOptions('sa_flush_cache', '/siteadmin/flush-cache')->defaults(['route_permissions' => ['system_manage_cache']])->controller('saCacheController@flushCache')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_manage_cache')),
        $routes->addWithOptions('sa_build_asset_cache_combine', '/siteadmin/assets/build-cache')->defaults(['route_permissions' => ['system_manage_cache']])->controller('saAssetManagerController@build')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_manage_cache')),
        $routes->addWithOptions('sa_build_asset_cache_combine_now', '/siteadmin/assets/build-cache/now')->defaults(['route_permissions' => ['system_manage_cache']])->controller('saAssetManagerController@buildNow')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_manage_cache')),

        $routes->addWithOptions('sa_asset_build_log',  '/siteadmin/assets/build-cache/log')->defaults(['route_permissions' => ['system_manage_cache']])->controller('saAssetManagerController@buildLog')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_manage_cache')),


        $routes->addWithOptions('test_geo_coding_ip',  '/siteadmin/system/repair-ip-geocode')->controller('systemController@repairOnlineUsersGeo')->middleware('SaAuthMiddleware');


        $routes->addWithOptions('sa_system_cluster', '/siteadmin/clusters')->defaults(['route_permissions' => ['system_clusters']])->controller('saClusterController@index')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_clusters');
        $routes->addWithOptions('sa_system_cluster_node_delete', '^/siteadmin/clusters/nodes/[0-9]{1,}/delete$')->defaults(['route_permissions' => ['system_clusters']])->controller('saClusterController@delete')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_clusters')),
        $routes->addWithOptions('sa_system_cluster_node_add', '/siteadmin/clusters/nodes/add')->defaults(['route_permissions' => ['system_clusters']])->controller('saClusterController@showAdd')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_clusters')),
        $routes->addWithOptions('sa_system_cluster_node_edit', '/siteadmin/clusters/nodes/{id}/edit')->defaults(['route_permissions' => ['system_clusters']])->controller('saClusterController@showEdit')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_clusters')),

        $routes->addWithOptions('sa_system_cluster_node_add_save', '/siteadmin/clusters/nodes/add')->defaults(['route_permissions' => ['system_clusters']])->controller('saClusterController@saveAdd')->methods(['POST'])->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_clusters', 'method'=>'POST')),
        $routes->addWithOptions('sa_system_cluster_node_edit_save', '/siteadmin/clusters/nodes/{id}')->defaults(['route_permissions' => ['system_clusters']])->controller('saClusterController@saveEdit')->methods(['POST'])->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_clusters', 'method'=>'POST')),


        $routes->addWithOptionsAndName('System Log', 'sa_system_log', '/siteadmin/log')->defaults(['route_permissions' => ['system_log']])->controller('SaLogViewerController@index')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware');//, 'permissions'=>'system_log'))
    }

    /**
     * @return array
     */
    public static function getPermissions()
    {

        $permissions = array();
        $permissions['system_list_users'] = 'List Users';
        $permissions['system_add_user'] = 'Add User';
        $permissions['system_view_user'] = 'View User';
        $permissions['system_save_user'] = 'Save User';
        $permissions['system_delete_user'] =' Delete User';
        $permissions['system_view_settings'] = 'View Settings';
        $permissions['system_edit_settings'] = 'Edit Settings';
        $permissions['system_default_data'] = 'Default Data Import';
        $permissions['system_clusters'] = 'Edit Clusters';
        $permissions['system_manage_cache'] = 'Manage System Cache';
        $permissions['system_log'] = 'View System Log';
        $permissions['system_sprite_generation'] = 'Generate Sprite Image';
        $permissions['system_manage_permissions'] = 'Manage User Permissions';

        return $permissions;
    }




    static function init()
    {
//        Event::listen('app.pre.routeVerify', 'SiteBlockController@isSiteBlocked', 26, 'site_block');
//
//        Event::listen('app.pre.routeVerify', 'saAuth@isAllowedToRunRoute', 25, 'saAuth');
//
//        Event::listen('app.pre.run', 'systemController@verifyLicense');
//        Event::listen('app.pre.routeVerify', 'systemController@checkBruteForceIP');

        MiddlewareManager::register('SaAuthMiddleware', 'SaAuthMiddleware', MiddlewareManager::MIDDLEWARE_PRIORITY_HIGH);
        MiddlewareManager::register('AppRedirectsMiddleware', 'AppRedirectsMiddleware', MiddlewareManager::MIDDLEWARE_PRIORITY_HIGH);
        MiddlewareManager::registerGlobal('AppRedirectsMiddleware');

        modRequest::listen('system.testRevisionUpdates', 'systemController@testRevisionUpdates', 1, false, true, false);

//        Event::listen('app.redirects', 'systemController@checkSSLDomainRedirects', 50);
        
        modRequest::listen('system.registerRevisionUpdates', 'systemController@registerRevisionUpdates');
        modRequest::listen('system.cancelRevisionUpdates', 'systemController@cancelRevisionUpdates', 1);
        modRequest::listen('sa.header', 'saSystemController@headerWidget', 0);


        modRequest::request('sa.dashboard.add_widget', null, array('id'=>'sa.dashboard.users_online', 'name'=>'Users Online', 'action'=>'saSystemController@getUsersOnlineWidget'));

        /**
         * SA User Group MRs
         */
        modRequest::listen('sa.user.group.create', 'saUserGroupController@create', 1, null, true, false);
        modRequest::listen('sa.user.group.get', 'saUserGroupController@get', 1, null, true, false);
        modRequest::listen('sa.user.group.save', 'saUserGroupController@save', 1, null, true, false);

        /**
         * SA User Group Permission MRs
         */
        modRequest::listen('sa.user.group.permission.create', 'saUserGroupPermissionController@create', 1, null, true, false);
        modRequest::listen('sa.user.group.permission.get', 'saUserGroupPermissionController@get', 1, null, true, false);
        modRequest::listen('sa.user.group.permission.save', 'saUserGroupPermissionController@save', 1, null, true, false);
        modRequest::listen('sa.user.group.permission.get.groupings', 'saUserGroupPermissionController@getGroupings', 1, null, true, false);

        modRequest::listen('sa.user', 'saAuth@getAuthUser');
        modRequest::listen('sa.verify.send_code', 'SaAuthController@ajaxSendVerifyCode', 1, null, true, true);
        modRequest::listen('sa.verify.two_factor_send_code', 'SaAuthController@ajaxSendTwoFactorVerifyCode', 1, null, true, true);
        modRequest::listen('sa.verify.check_code_status', 'SaAuthController@ajaxCheckVerifyCodeStatus', 1, null, true, true);
        modRequest::listen('sa.verify.verify_code', 'SaAuthController@ajaxVerifyCode', 1, null, true, true);
        modRequest::listen('sa.verify.two_factor_verify_code', 'SaAuthController@twoFactorVerifyCode', 1, null, true, true);

        modRequest::listen('sa.verify.issue_ga_code', 'SaAuthController@ajaxIssueGAUserSecretCode', 1, null, true, true);


        modRequest::listen('sa.session.extend', 'SaAuthController@extendSession', 1, null, true, true);
        modRequest::listen('sa.session.logoff', 'SaAuthController@logoffSession', 1, null, true, true);

        modRequest::listen('site.default_data.list', 'saDefaultDataController@getModuleList',1,null,true,true);

        modRequest::listen('system.zipinfo', 'systemController@getZipInfo', 1, null, true, false);

        modRequest::listen('system.location.states', 'systemController@getStatesInfo', 1, null, true, false);
        modRequest::listen('system.location.countries', 'systemController@getCountryInfo', 1, null, true, false);
        modRequest::listen('system.getCountryByCode', 'systemController@getCountryDataByCode', 1, null, true, false);

        modRequest::listen('system.generateURL', 'systemController@generateURL', 1, null, true, false);

        modRequest::listen('assets.rebuild', 'saAssetManagerController@modRequestCacheRebuild', 1, null, true, false);

        modRequest::listen('system.log', 'SaLogViewerController@getLogData', 1, null, true, true);

        /**
         * Flushes System Cache
         * 
         * @param array - An array of cache namespaces to flush
         * @return null
         */
        modRequest::listen('system.cache.flush', 'saCacheController@modRequestFlushSystemCache', 1, null, false, false);

        /**
         * Checks if route may be accessed.
         *
         * $data = [
         *      'route_id' => '{route ID}',
         *      'can_access' => true|false
         * ]
         */
        modRequest::listen('app.route.can_access', 'systemController@canAccessRoute', 1, null, true, false);
    }



    static function getNavigation()
    {
        return array(

            //new navItem(array( 'id'=>'system', 'name'=>'System', 'icon'=>'fa fa-dashboard', 'parent'=>'root',  )),
            new navItem(array( 'id'=>'sa_logoff', 'name'=>'Log Out', 'routeid'=>'sa_logoff', 'icon'=>'fa fa-lock', 'parent'=>'siteadmin_root', 'priority'=>navItem::PRIORITY_LOW  )),

            new navItem(array('id'=>'sa_sausers', 'name'=>'SA Users', 'icon'=>'fa fa-user', 'parent'=>'sa_settings')),
            new navItem(array('name'=>'Manage SA Users',  'routeid'=>'sa_sausers', 'icon'=>'fa fa-user', 'parent'=>'sa_sausers')),
            new navItem(array('name'=>'Create SA Users', 'routeid'=>'sa_sausers_create', 'icon'=>'fa fa-user-plus', 'parent'=>'sa_sausers')),
            new navItem(array('name'=>'Manage SA User Groups',  'routeid'=>'sa_sausergroups', 'icon'=>'fa fa-users', 'parent'=>'sa_sausers')),
            new navItem(array('name'=>'Manage SA User Group Permissions',  'routeid'=>'sa_sausergroup_permissions', 'icon'=>'fa fa-users', 'parent'=>'sa_sausers')),
            new navItem(array('id'=>'sa_settings', 'name'=>'Settings', 'icon'=>'fa fa-gear', 'parent'=>'siteadmin_root')),

            new navItem(array('id'=>'sa_manage_settings', 'name'=>'General Settings', 'routeid'=>'sa_settings', 'icon'=>'fa fa-gear', 'parent'=>'sa_settings', 'priority'=>navItem::PRIORITY_ABOVENORMAL)),

            new navItem(array('id'=>'sa_manage_settings_cache', 'name'=>'Caching', 'icon'=>'fa fa-gear', 'parent'=>'sa_settings', 'priority'=>navItem::PRIORITY_ABOVENORMAL)),
            new navItem(array('id'=>'sa_default_data_import', 'subpattern'=>'/siteadmin/default_data', 'name'=>'Default Data Import', 'routeid'=>'sa_default_data', 'icon'=>'fa fa-gear', 'parent'=>'sa_settings', 'priority'=>navItem::PRIORITY_ABOVENORMAL)),

            new navItem(array('id'=>'sa_generate_sprite', 'name'=>'Generate Sprite Image', 'routeid'=>'sa_system_generate_sprite', 'icon'=>'fa fa-image', 'parent'=>'sa_manage_settings_cache', 'priority'=>navItem::PRIORITY_ABOVENORMAL )),
            new navItem(array('id'=>'sa_build_asset_cache', 'name'=>'Build Asset Cache', 'routeid'=>'sa_build_asset_cache_combine', 'icon'=>'fa fa-wrench', 'parent'=>'sa_manage_settings_cache')),

            new navItem(array('id'=>'sa_flush_cache', 'name'=>'Flush Cache', 'routeid'=>'sa_flush_cache', 'icon'=>'fa fa-sync', 'parent'=>'sa_manage_settings_cache')),
            new navItem(array('id'=>'sa_flush_build', 'name'=>'Flush Build Directory', 'routeid'=>'sa_flush_build', 'icon'=>'fa fa-folder', 'parent'=>'sa_manage_settings_cache')),


            new navItem(array('id'=>'sa_cluster', 'name'=>'SA Cluster Nodes', 'routeid'=>'sa_system_cluster', 'icon'=>'fa fa-server', 'parent'=>'sa_settings', 'priority'=>navItem::PRIORITY_ABOVENORMAL)),
            new navItem(array('id'=>'sa_system_log', 'name'=>'System Log', 'routeid'=>'sa_system_log', 'icon'=>'fa fa-align-left', 'parent'=>'sa_settings')),

        );

    }

    static function getCLICommands()
    {
        return array(
            ioc::staticGet('ClearCacheCommand'),
            ioc::staticGet('FlushAssetBuildDirectoryCommand'),
            ioc::staticGet('BuildAssetsCommand'),
            ioc::staticGet('ShowConfigCommand'),
            ioc::staticGet('ChangeConfigCommand'),
            ioc::staticGet('SetupAdminSaUserGroupPermissionsCommand')

        );
    }


    public static function getSettings()
    {
        $module_settings = array(
            'api_sa_user_login_enabled' => array('type' => 'boolean', 'default' => false),
            'require_ssl' => array('type' => 'boolean', 'default' => false),
            'force_main_domain_redirect' => array('type' => 'boolean', 'default' => true),
            'timezone_default' => array('type' => 'string', 'default' => 'America/New_York'),
            'public_directory' => array(),
            'theme' => array('tab'=>'theme'),
            'site_url' => array('type' => 'string', 'default' => 'http://'.app::get()->getActiveRequest()->getHost()),
            'secure_site_url' => array('type' => 'string', 'default' => 'https://'.app::get()->getActiveRequest()->getHost()),
            'session_domain' => array(),
            'thread_domain' => array('type' => 'string', 'default' => app::get()->getActiveRequest()->getHost()),
            'thread_cookie' => array('type' => 'string', 'default' => ''),
            'force_www_redirect' => array('type' => 'boolean', 'default' => false),
            'site_robot_indexable' => array('type' => 'boolean', 'default' => true),
            'site_name' => array('tab'=>'theme'),
            'site_logo' => array('tab'=>'theme', 'type'=>'image_uploader'),
            'site_logo_alternative' => array('tab'=>'theme', 'type'=>'image_uploader'),
            'site_address' => array('tab'=>'theme'),
            'site_address_2' => array('tab'=>'theme'),
            'site_city' => array('tab'=>'theme'),
            'site_state' => array('tab'=>'theme'),
            'site_zip' => array('tab'=>'theme'),
            'site_latitude' => array('tab'=>'theme'),
            'site_longitude' => array('tab'=>'theme'),
            'site_phone' => array('tab'=>'theme'),
            'site_phone_2' => array('tab'=>'theme'),
            'social_facebook_url' => array('tab'=>'theme'),
            'social_twitter_url' => array('tab'=>'theme'),
            'social_instagram_url' => array('tab'=>'theme'),
            'social_tumbler_url' => array('tab'=>'theme'),
            'social_google_url' => array('tab'=>'theme'),
            'social_linkedin_url' => array('tab'=>'theme'),
            'site_email' => array(),
            'version' => array(),
            'recaptcha_public' => array(),
            'recaptcha_private' => array(),
            'sa_session_timeout' => array('type' => 'int', 'default' => 1200),
            'sa_device_verify'  => array('type' => 'boolean', 'default' => false),
            'sa_device_verify_method'  => array('type' => 'select', 'options'=>array('Google Authenticator', 'SMS'), 'default' => 'Google Authenticator'),
            'sa_login_two_factor'  => array('type' => 'boolean', 'default' => false),
            'sa_login_two_factor_method'  => array('type' => 'select', 'options'=>array('Google Authenticator', 'SMS'), 'default' => 'Google Authenticator'),
            'twilio_sid'  => array('type' => 'text', 'default' => ''),
            'twilio_token'  => array('type' => 'text', 'default' => ''),
            'twilio_phonenumber' => array('type' => 'text', 'default' => ''),
            'siteadmin_image_id' => array('type'=>'image_uploader'),
            'siteadmin_header_bg' => array('type' => 'text', 'default' => '#438eb9'),
            'siteadmin_login_bg' => array('type'=>'image_uploader'),
            'siteadmin_login_image_id' => array('type'=>'image_uploader'),
            'combine_resources' => array('type' => 'boolean', 'default' => false),
            'safe_mode' => array('type' => 'boolean', 'default' => false),

            'http2' => array('type' => 'boolean', 'default' => false),
            'cache_assets' => array('type' => 'boolean', 'default' => true),
            'cache_assets_using_hard_link' => array('type' => 'boolean', 'default' => false),

            'cache_assets_module_request' => array('type' => 'boolean', 'default' => true),

            'cache_driver' => array('type' => 'select', 'options'=>array('File System', 'Redis', 'Memcached', 'Apcu'), 'default' => 'File System'),
            'cache_driver_host' => array('type' => 'text', 'default' => '127.0.0.1'),

            'move_css_to_end' => array('type' => 'boolean', 'default' => false),
            'move_js_to_end' => array('type' => 'boolean', 'default' => true),
            'minify_html' => array('type' => 'boolean', 'default' => false),


            'use_second_level_cache' => array('type' => 'boolean', 'default' => false),

            'site_block' => array('type' => 'boolean', 'default' => false),
            'site_block_password' => array('type' => 'password', 'default' => 'elink'),
            
            'allow_doctrine_discriminator' => array('type' => 'boolean', 'default' => true),
            'sprite_images' => array('type' => 'boolean', 'default' => false),
            'development_banner_position' => array('default' => 'top'),
            'show_development_banners' => array('type' => 'boolean', 'default' => true),

            'db_driver' => array('type' => 'text', 'default' => ''),
            'db_name' => array('type' => 'text', 'default' => ''),
            'db_password' => array('type' => 'text', 'default' => ''),
            'db_path' => array('type' => 'text', 'default' => ''),
            'db_server' => array('type' => 'text', 'default' => ''),
            'db_username' => array('type' => 'text', 'default' => ''),
            'db_port' => array('type' => 'text', 'default' => '3306'),

            'db_driver_secondary' => array('type' => 'text', 'default' => ''),
            'db_path_secondary' => array('type' => 'text', 'default' => ''),
            'db_username_secondary' => array('type' => 'text', 'default' => ''),
            'db_password_secondary' => array('type' => 'text', 'default' => ''),
            'db_name_secondary' => array('type' => 'text', 'default' => ''),
            'db_server_secondary' => array('type' => 'text', 'default' => ''),

            'geo_ip_database_path' => array('type' => 'text', 'default' => '../siteadmin/modules/sa/system/src/other/GeoLite2-City.mmdb'),

            'log_doctrine_enable' => array('type' => 'boolean', 'default' => false),
            'log_enable' => array('type' => 'boolean', 'default' => true),
            'log_level' => array('type' => 'select', 'options'=>array('ERROR', 'INFO', 'NOTICE', 'WARNING', 'DEBUG' ), 'default' => 'ERROR'),
            'log_email' => array('type' => 'text', 'default' => ''),

            'settings_no_sync' => array('type' => 'text', 'default' => ''),
            'disable_settings' => array('type' => 'boolean', 'default' => false),

            'remote_assistance' => array('type' => 'boolean', 'default' => true),
            'brute_force_check_enabled' => array('type' => 'boolean', 'default' => true),
            'brute_force_lockout_attempts' => array('type' => 'integer', 'default' => 10),

            'permissions_owner_group' => array('type' => 'text', 'default' => get_current_user()),

            'sa_central_enabled' => array('type' => 'boolean', 'default' => true),
            'sa_central_host' => array('type' => 'text', 'default' => 'http://central.siteadministrator.com'),
            'sa_central_instance' => array('type' => 'text', 'default' => 'A'),

            'sa_central_client_id' => array('type' => 'text', 'default' => 'sa-central'),
            'sa_central_client_key' => array('type' => 'text', 'default' => '$2y$10$IsgfJ1PkRni0zSmq0ndfBesV0a0wg/W3Xog2iw7jQldqAyvFMTpEq'),
            'sa_central_monitoring_interval' => array('type' => 'integer', 'default' => 7200),

            'sa_central_email_monitoring_to' => array('type' => 'text', 'default' => 'sa32test@gmail.com'),
            'middleware' => array('type' => 'array', 'default' => []),
            'hidden_sa_modules' => array('type' => 'text', 'default' => '')
        );

        return $module_settings;
    }
}
