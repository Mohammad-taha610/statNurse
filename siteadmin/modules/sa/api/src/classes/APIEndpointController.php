<?php

namespace sa\api;

use sa\api\Service\ApiRouterService;
use sacore\application\controller;
use sacore\application\ioc;

/**
 * Class APIEndpointController
 * @package sa\api
 */
class APIEndpointController extends controller
{
    /**
     * @param array $data
     */
    public static function modRequestRegisterEntityAPIController(array $data) : void
    {
        /** @var ApiRouterService $routeServiceRef */
        $routeServiceRef = ioc::staticResolve('ApiRouterService');
        $routeServiceRef::registerEntityAPI($data['entity'], $data['controller'], $data['route']);
    }
}
