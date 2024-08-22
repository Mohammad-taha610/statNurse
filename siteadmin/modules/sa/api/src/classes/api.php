<?php

namespace sa\api;


use sacore\application\app;
use sacore\application\ioc;
use sacore\application\jsonView;
use sacore\application\responses\Json;
use sacore\utilities\arrayUtils;
use sacore\utilities\url;

class api
{

    public static $registredApis = array(
        'controller'=>array('mobile'=>'apiMobileController', 'ws'=>'wsController'),
        'entities'=>array()
    );

    public static function registerAPIEntityController($identification, $controller, $route = null) {
        if(is_array($identification)) {
            foreach ($identification as $value) {
                static::$registredApis['entities'][strtolower($value)] = $controller;
            }
        } else if(empty($identification) && $route) {
            static::$registredApis['controller'][$route] = $controller;
        } else {
            static::$registredApis['entities'][ strtolower($identification) ] = $controller;
        }
        
    }

    public static function registerAPIController($identification, $controller) {
        static::$registredApis['controller'][ strtolower($identification) ] = $controller;
    }


    public static function modRequestRegisterEntityAPIController($data)
    {
        static::registerAPIEntityController($data['entity'], $data['controller'], $data['route']);
    }

    public static function getAPIEntityController($identification) {
        $identification = strtolower($identification);
        $controller = null;
        if (isset(static::$registredApis['entities'][$identification])) {
            $controller = ioc::staticGet(static::$registredApis['entities'][$identification]);
        }

        return $controller;

    }

    public static function getAPIController($identification) {
        $identification = strtolower($identification);
        $controller = null;
        if (isset(static::$registredApis['controller'][$identification])) {
            $controller = ioc::staticGet(static::$registredApis['controller'][$identification]);
        }
        
        return $controller;

    }

    public function response($code=200, $data=null)
    {
        $view = new jsonView($code);
        
        $view->data['route'] = url::uri();
        $view->data['response_code'] = $code;
        $view->data['response'] = $data;
        $view->data["completed_time"] = time();
        $view->display();

    }


    public static function getJsonPostDataAsArray ()
    {
        if (isset ($_REQUEST['json_str']))
        {
            $jsonStr = $_REQUEST['json_str'];
        }
        else
        {
            $jsonStr = file_get_contents('php://input');
        }

        if (empty($jsonStr))
            return null;

        $json = json_decode( trim($jsonStr), true);
        return $json;
    }

    public static function getRawJsonDataString() {
        if(isset($_REQUEST['json_str'])) {
            $jsonStr = $_REQUEST['json_str'];
        } else {
            $jsonStr = file_get_contents('php://input');
        }

        if(empty($jsonStr)) {
            return null;
        }

        return $jsonStr;
    }


    public function bldSuccessArray()
    {
        return (array ('success' => '1'));
    }

    public function bldErrorArray ($errMsg, $errCode = 0)
    {
        return (array ('success' => '0', 'errorMsg' => $errMsg, 'errorCode' => $errCode));
    }

    /**
     * Validate headers for all required information
     *
     * @throws HeaderValidationException
     */
    public static function validateHeadersForProtectedApi($headers) {

        $headers = arrayUtils::array_change_key_case_recursive($headers, CASE_LOWER);
        
        /**
         * Ensure request headers contain
         * all required information
         * for successful validation
         */
        if(empty($headers[strtolower(\config::api_authorization_header_name)]) ||  empty($headers['content-type']) ||  empty($headers['client-identifier']) /*|| empty($headers['Request-Uri']) || ($headers['Request-Uri'] !== $_SERVER['REQUEST_URI'])*/ )
        {
            throw new HeaderValidationException('Unauthorized - Invalid Headers', 401);
        }

        return true;
    }

    public static function getAPIKey($clientIdentifier) {
        

        /** @var ApiKey $matchedApiKey - Ensure the api key provided by the client is valid and active */
        $matchedApiKey = ioc::getRepository('ApiKey')->findOneBy(
            array('client_id' => $clientIdentifier, 'is_active' => true)
        );

        /**
         * Api Key has been revoked or
         * no longer exists
         */
        if(!$matchedApiKey) {
            throw new ApiKeyException('Unauthorized', 401, 'Unable to find valid matching API Key');
        }

        return $matchedApiKey;
    }
}