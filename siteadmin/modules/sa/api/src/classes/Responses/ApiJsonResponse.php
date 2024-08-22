<?php

namespace sa\api\Responses;

use sacore\application\responses\Json;
use sacore\application\responses\ResponseHeader;
/**
 * Class ApiResponse
 * @package sa\api
 */
class ApiJsonResponse extends Json
{
    /**
     * ApiResponse constructor.
     * @param int $response_code
     */
    public function __construct($response_code = 200)
    {
        parent::__construct($response_code);
        
        $this->headers[] = new ResponseHeader('Access-Control-Allow-Origin', '*');
        $this->headers[] = new ResponseHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->headers[] = new ResponseHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Client-Identifier');
        $this->headers[] = new ResponseHeader('Content-Type', 'application/json; charset=utf-8');
    
        $this->data = [
            'success' => true,
            'message' => null,
            'statusCode' => $response_code,
            'response' => null,
        ];
        
    }

    /**
     * @param mixed $responseData
     */
    public function setResponseData(mixed $responseData) : void
    {
        $this->data['response'] = $responseData;
    }

    public function setMessage(?string $message)
    {
        $this->data['message'] = $message;
    }

    public function setSuccess(bool $success)
    {
        $this->data['success'] = $success;
    }


}