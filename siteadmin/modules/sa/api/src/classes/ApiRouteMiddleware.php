<?php

namespace sa\api;

use sa\api\Responses\ApiJsonResponse;
use sa\api\Service\ApiAuthenticationService;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\Middleware;
use sacore\application\Request;
use sacore\application\responses\ISaResponse;
use sacore\application\responses\Redirect;

/**
 * Class ApiRouteMiddleware
 * @package sa\api
 */
class ApiRouteMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @return ?ISaResponse
     * @throws IocDuplicateClassException
     * @throws IocException
     */
    public function BeforeRoute($request)
    {
        $authServiceRef = ioc::staticResolve('ApiAuthenticationService');
        /** @var ApiAuthenticationService $authService */
        $authService = new $authServiceRef($request);

        try {
            $authService->authenticate();
        } catch(ApiAuthException $e) {
            /** @var ApiController $apiController */
            $apiController = ioc::resolve('ApiController');

            /** @var ApiJsonResponse $error401 */
            $error401 = $apiController->error401();
            $error401->data['message'] = $e->getMessage();

            return $error401;
        }

    }
}