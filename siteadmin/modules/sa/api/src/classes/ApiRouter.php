<?php
namespace sa\api;

use ReflectionMethod;
use \sacore\application\controller;
use sacore\application\ioc;
use sacore\application\ValidateException;
use sacore\utilities\arrayUtils;
use sacore\utilities\url;
use sacore\application\app;

class ApiRouter
{

    public function apiV1Endpoint() {

        file_put_contents(ApiRouter . phpapp::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'provider-api.log', 'start of endpoint', FILE_APPEND);
        /** @var api $api */
        $api = ioc::get('api');

        $headers = getallheaders();
        $headers = arrayUtils::array_change_key_case_recursive($headers, CASE_LOWER);
        $rawJsonString = $api->getRawJsonDataString();
        $jsonArray = $api->getJsonPostDataAsArray();
        $contentType = $headers['content-type'];
        $keySignature = $headers[strtolower(\config::api_authorization_header_name)];

        /**
         * Validate that all information in the header is
         * Present
         */
        try {
            $api->validateHeadersForProtectedApi($headers);
            $clientIdentifier = $headers['client-identifier'];
            $matchedApiKey = $api->getAPIKey($clientIdentifier);

        } catch (ApiException $e) {
            $error = $api->bldErrorArray($e->getMessage(), $e->getCode());
            $api->response($e->getCode(), $error);
            return;
        }

        /**
         * Build a string containing the data
         * used to generate an HMAC signature
         */
        $hashData =
            $matchedApiKey->getApiKey() .
            md5($rawJsonString) .
            $clientIdentifier .
            $contentType .
            url::uri();

        $hashDataAppJson =
            $matchedApiKey->getApiKey() .
            md5($rawJsonString) .
            $clientIdentifier .
            'application/json' .
            url::uri();


        /** @var string $hmac - Server-generated HMAC signature */
        $hmac = hash_hmac('sha256', $hashData, $matchedApiKey->getApiKey());
        $hmacAppJson = hash_hmac('sha256', $hashDataAppJson, $matchedApiKey->getApiKey());

        /**
         * Ensure that the server generated HMAC signature exactly matches
         * the client generated signature, otherwise return an unauthorized
         * error code
         */

        try {
            if(strcmp($hmac, $keySignature) != 0 ) {
            	if(strcmp($hmacAppJson, $keySignature) != 0) {
        		    throw new ApiAuthException('Unauthorized', 401, 'HMAC Signature Mismatch');
            	}
            }
        } catch (ApiException $e) {
            $error = $api->bldErrorArray($e->getMessage(), $e->getCode());
            $api->response($e->getCode(), $error);
            return;
        }


        $http_content_range = $headers['http_content_range'];
        $chunk_key = $headers['chunk_key'];

        if ($http_content_range && $chunk_key) {

            if ($chunk_key=='new') {
                $chunk_key = \sacore\utilities\stringUtils::generateRandomString(15, true);
            }

            if (!file_exists(\config::tempDir.'/api_chunks')) {
                mkdir(\config::tempDir.'/api_chunks', 0755, true);
            }

            if (!file_exists(\config::tempDir.'/api_chunks/'.$chunk_key)) {
                mkdir(\config::tempDir.'/api_chunks/'.$chunk_key, 0755, true);
            }

            preg_match('/bytes ([0-9]{1,})-([0-9]{1,})\/([0-9]{1,})/', $http_content_range, $filerangeinfo);

            file_put_contents( \config::tempDir.'/api_chunks/'.$chunk_key.'/'.$filerangeinfo[1].'-'.$filerangeinfo[2].'-'.$filerangeinfo[3], $rawJsonString);

            /** @var ApiChunk $chunk */
            $chunk = ioc::get('ApiChunk');
            $chunk->setChunkKey($chunk_key);
            $chunk->setBeginningOffset($filerangeinfo[1]);
            $chunk->setEndingOffset($filerangeinfo[2]);
            $chunk->setFileSize($filerangeinfo[3]);
            $chunk->setSize($filerangeinfo[2] - $filerangeinfo[1]);
            $chunk->setFileName($filerangeinfo[1].'-'.$filerangeinfo[2].'-'.$filerangeinfo[3]);
            app::$entityManager->persist($chunk);
            app::$entityManager->flush($chunk);

            $isComplete = ioc::getRepository('ApiChunk')->checkForCompleteFile($chunk_key);

            if ($isComplete) {
                $request = ioc::getRepository('ApiChunk')->reassembleFile($chunk_key);
                $rawJsonString = $request;
                $jsonArray = json_decode( trim($request), true);
            } else {
                $api->response( 206,  array('chunk_key'=>$chunk_key ) );
                return;
            }

        }

        $uriparts = url::parts();



        $url_module = $uriparts[3];
        $url_entity = null;
        $url_action = $uriparts[4];
        $url_data = $uriparts[5];

        $apiController = api::getAPIController( $url_module );

        if (!$apiController) {

            $url_module = $uriparts[3];
            $url_entity = $uriparts[4];
            $url_action = $uriparts[5];
            $url_data = $uriparts[6];

            $permittedScope = $matchedApiKey->getEntityScope();
            $permitted = false;
            $permittedEntity = null;
            foreach ($permittedScope as $entity) {
                $entityParts = explode('\\', $entity);
                if (strtolower($url_module) == strtolower($entityParts[2]) && strtolower($url_entity) == strtolower($entityParts[3])) {
                    $permitted = true;
                    $permittedEntity = $entity;
                    break;
                }
            }

            if ($permitted) {

                $repo = ioc::getRepository($permittedEntity);
                $entityname = ioc::staticResolve($permittedEntity);

                $apiController = api::getAPIEntityController($url_module . '\\' . $url_entity);

                if (!$apiController)
                    $apiController = ioc::staticGet('apiController');


                /** @var apiController $controller */
                $controller = new $apiController();
                $controller->setApiKey($matchedApiKey);
                $controller->setEntityName($entityname);
                $controller->setRepo($repo);
                $controller->setPaginatedIndexResults(\config::api_paginated_index_results);
                $controller->setEnableDefaultsApiEndpoints(\config::api_enabled_default_endpoints);
            }
            else {

                $apiController = ioc::staticGet('apiController');
                /** @var apiController $controller */
                $controller = new $apiController();
                $controller->apiNotFound();

            }

        } else {
            $permitted = true;
            $controller = new $apiController();
            $controller->setApiKey($matchedApiKey);
        }

        if ($permitted) {
            $action = $url_action;

            if (empty($action)) {
                $action = 'index';
            }

            if (!method_exists($controller, $action)) {
                $controller->apiNotFound();
            } else {

                $reflection = new ReflectionMethod($controller, $action);
                if (!$reflection->isPublic()) {
                    $controller->apiNotFound();
                }

                try {
                    $response = $controller->$action($url_data, $jsonArray);

                    $api->response(200, $response);
                } catch (ApiException $e) {
                    $error = $api->bldErrorArray($e->getMessage(), $e->getCode());
                    $code = $e->getCode();
                    if (empty($code)) {
                        $code = 500;
                    }

                    $api->response($code, $error);
                    return;
                } catch (ValidateException $e) {
                    $error = $api->bldErrorArray($e->getMessage(), $e->getCode());
                    $code = $e->getCode();
                    if (empty($code)) {
                        $code = 500;
                    }

                    $api->response($code, $error);
                    return;
                }
            }

        }

    }

}
