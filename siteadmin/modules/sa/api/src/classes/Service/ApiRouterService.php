<?php

namespace sa\api\Service;

use ReflectionException;
use ReflectionMethod;
use sa\api\ApiController;
use sa\api\ApiException;
use sa\api\ApiKey;
use sa\api\apiKeyRepository;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Request;
use sacore\application\responses\ISaResponse;
use sahtmldocument\htmldocument\Parser;
use sa\member\MemberApiV1Controller;

/**
 * Class ApiRouterService
 * @package sa\api
 */
class ApiRouterService
{
    private Request $request;

    private static array $registeredApis = [
        'controller' => ['mobile' => 'apiMobileController', 'ws'=>'wsController'],
        'entities' => []
    ];

    /**
     * ApiRouterService constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $entityName
     * @param $controller
     * @param null $route
     */
    public static function registerEntityAPI($entityName, $controller, $route = null) : void
    {
        $route = strtolower($route);
        if (!isset(static::$registeredApis['entities'][strtolower($entityName)])) {
            static::$registeredApis['entities'][strtolower($entityName)] = [];
        }
        static::$registeredApis['entities'][strtolower($entityName)][$route] = $controller;
    }

    /**
     * @return ISaResponse
     * @throws ApiException
     * @throws ReflectionException
     */
    public function executeApiRoute()
    {
        $reqAction = $this->request->getRouteParams()->get('action');
        /** @var ApiController $apiController */
        $apiController = $this->resolveApiController();

        if(!method_exists($apiController, $reqAction)) {
            $apiController->error404();
        }

        $ref = new ReflectionMethod($apiController, $reqAction);

        if(!$ref->isPublic()) {
            throw new ApiException('500 - Internal Server Error');
        }

        return $apiController->$reqAction($this->request);
    }

    /**
     * @return ?ApiController
     * @throws ApiException
     */
    private function resolveApiController()
    {
        $reqModule = $this->request->getRouteParams()->get('module');
        $reqEntity = strtolower($this->request->getRouteParams()->get('entity'));
        $reqRoute = strtolower($this->request->getRouteParams()->get('route'));
        $controllerName = ioc::staticget('ApiController');
        if(isset(static::$registeredApis['entities'][$reqEntity]) && isset(static::$registeredApis['entities'][$reqEntity][$reqRoute])) {
            $controllerName = ioc::staticGet(static::$registeredApis['entities'][$reqEntity][$reqRoute]);
        }


        $clientIdHeader = $this->request->headers->get('Client-Identifier');

        /** @var ApiKey $apiKey */
        $apiKey = ioc::getRepository('ApiKey')->findOneBy(['client_id' => $clientIdHeader]);

        if(!$apiKey) {
            throw new ApiException('Internal Server Error');
        }

        $entityForRepo = null;

        foreach($apiKey->getEntityScope() as $entity) {
            $entityParts = explode('\\', $entity);

            if (strtolower($reqModule) == strtolower($entityParts[2]) && strtolower($reqEntity) == strtolower($entityParts[3])) {
                $entityForRepo = $entity;
                break;
            }
        }

        if(!$entityForRepo) {
            throw new ApiException('Internal Server Error');
        }

        $repo = ioc::getRepository($entityForRepo);

        /** @var ApiController $controller */
        $controller = new $controllerName();
        $controller->setApiKey($apiKey);
        $controller->setEntityName($reqEntity);
        $controller->setRepo($repo);
        $controller->setPaginatedIndexResults(
            app::get()->getConfiguration()->get('api_paginated_index_results')->getValue()
        );
        $controller->setEnableDefaultApiEndpoints(
            app::get()->getConfiguration()->get('api_enabled_default_endpoints')->getValue()
        );

        return $controller;
    }
}
