<?php

namespace sa\api;

use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;

/**
 * Class ApiException
 * @package sa\api
 */
class ApiException extends Exception
{
    /**
     * ApiException constructor.
     * @param $message
     * @param int $code
     * @param string $logMessage
     * @param Exception|null $previous
     */
    public function __construct($message, $code = 0, $logMessage = '', Exception $previous = null)
    {
        $tempDir = app::get()->getConfiguration()->get('tempDir')->getValue();

        file_put_contents($tempDir . '/api-log.txt', new DateTime() . ' - ' . $message. PHP_EOL, FILE_APPEND);
        file_put_contents($tempDir . '/api-log.txt', $code . ': ' . $logMessage. PHP_EOL, FILE_APPEND);
        file_put_contents($tempDir . '/api-log.txt', $this->getTraceAsString() . PHP_EOL, FILE_APPEND);
        
        parent::__construct($message, $code, $previous);
    }
}