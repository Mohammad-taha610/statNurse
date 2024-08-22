<?php

namespace sa\api;

use ReflectionException;
use sa\api\Service\ApiRouterService;
use sacore\application\ioc;
use sacore\application\Request;
use sacore\application\responses\ISaResponse;

/**
 * Class ApiRouteController
 * @package sa\api
 */
class ApiRouteController extends ApiController
{
    /**
     * ApiRouteController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->enableDefaultApiEndpoints = false;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function apiV1Endpoint(Request $request)
    {
        $routerServiceRef = ioc::staticResolve('ApiRouterService');
        /** @var ApiRouterService $routerService */
        $routerService = new $routerServiceRef($request);
        try {
            return $routerService->executeApiRoute();
        } catch(\Exception $e) {
            $apiControllerRef = ioc::staticResolve('ApiController');
            /** @var ApiController $apiController */
            $apiController = new $apiControllerRef();

            return $apiController->error500($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return ISaResponse
     */
    public function apiV1EndpointWithIdentifier(Request $request)
    {
        $routerServiceRef = ioc::staticResolve('ApiRouterService');

        /** @var ApiRouterService $routerService */
        $routerService = new $routerServiceRef($request);

        try {
            return $routerService->executeApiRoute();
        } catch(\Exception $e) {
            $apiControllerRef = ioc::staticResolve('ApiController');
            /** @var ApiController $apiController */
            $apiController = new $apiControllerRef();

            return $apiController->error500($e->getMessage());
        }
    }
}