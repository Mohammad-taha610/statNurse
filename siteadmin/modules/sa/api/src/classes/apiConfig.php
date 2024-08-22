<?php

namespace sa\api;

use sacore\application\CollectionConfigurator;
use sacore\application\MiddlewareManager;
use sacore\application\modRequest;
use sacore\application\ModRequestException;
use sacore\application\moduleConfig;
use sacore\application\navItem;

/**
 * Class apiConfig
 * @package sa\api
 */
abstract class apiConfig extends moduleConfig
{
    /**
     * @param CollectionConfigurator $routes
     */
    public static function initRoutes($routes)
    {
        /** CMS ROUTES **/

        $routes->addWithOptionsAndName('API V1 Key Management - Index', 'api_v1_key_mgmt_index', '/siteadmin/api-key')
            ->controller('SaManageApiKeysController@apiKeyIndex')
            ->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('API V1 Key Management - Add', 'api_v1_key_mgmt_add', '/siteadmin/api-key/add')
            ->controller('SaManageApiKeysController@apiKeyShowAdd')
            ->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('API V1 Key Management - Edit', 'api_v1_key_mgmt_edit', '/siteadmin/api-key/{id}/edit')
            ->controller('SaManageApiKeysController@apiKeyShowEdit')
            ->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('API V1 Key Management - Save', 'api_v1_key_mgmt_save', '/siteadmin/api-key/{id}/save')
            ->controller('SaManageApiKeysController@apiKeySaveEdit')
            ->methods(['POST'])
            ->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('API V1 Key Management - Delete Key', 'api_v1_key_mgmt_delete', '/siteadmin/api-key/{id}/delete')
            ->controller('SaManageApiKeysController@apiKeyDelete')
            ->middleware('SaAuthMiddleware');

        /** API ROUTING **/

        // default route with no 'route' param - old api controllers function as normal
        $routes->addWithOptionsAndName('API V1 Routes - No Entity ID', 'api_v1_route', '/api/v1/{module}/{entity}/{action}')
            ->controller('ApiRouteController@apiV1Endpoint')
            ->middleware('ApiRouteMiddleware')
            ->methods(['POST', 'GET']);

        // new route with 'route' param to allow access to multiple controllers
        $routes->addWithOptionsAndName('API V1 Routes - No Entity ID', 'api_v1_route_with_route_param', '/api/v1/{route}/{module}/{entity}/{action}')
            ->controller('ApiRouteController@apiV1Endpoint')
            ->middleware('ApiRouteMiddleware')
            ->methods(['POST', 'GET']);
    }

    /**
     * @return array
     */
    public static function getSettings() : array
    {
        return [
            'api_enabled_default_endpoints' => array('module' => 'API', 'type' => 'boolean', 'default' => true),
            'api_paginated_index_results' => array('module' => 'API', 'type' => 'boolean', 'default' => false),
            'api_authorization_header_name' => array('module' => 'API', 'type' => 'text', 'default' => 'authorization')
        ];
    }

    /**
     * @throws ModRequestException
     */
    static function init() : void
    {
        MiddlewareManager::register('ApiRouteMiddleware', 'ApiRouteMiddleware', MiddlewareManager::MIDDLEWARE_PRIORITY_HIGH);

        modRequest::listen('api.registerEntityAPI', 'APIEndpointController@modRequestRegisterEntityAPIController');
    }

    /**
     * @return navItem[]
     */
    static function getNavigation() : array
    {
        return [
            new navItem([
                'id' => 'sa_api_keys',
                'name' => 'Api Keys',
                'icon' => 'fa fa-mobile',
                'parent' => 'sa_settings',
                'routeid' => 'api_v1_key_mgmt_index'
            ])
        ];
    }
}
