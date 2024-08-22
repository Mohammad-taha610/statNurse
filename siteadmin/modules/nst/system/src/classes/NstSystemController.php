<?php

namespace nst\system;

use sacore\application\ioc;
use sa\system\systemController;
use sacore\utilities\doctrineUtils;

class NstSystemController extends systemController
{
    public static function getAllStates($data) {
        $response = ['success' => false];

        try {
            $response['states'] = doctrineUtils::getEntityCollectionArray(ioc::getRepository('NstState')->getAllStates());
            $response['success'] = true;
        } catch (\Throwable $th) {
            $response['message'] = $th->getMessage();
            $response['trace'] = $th->getTraceAsString();
            $response['code'] = $th->getCode();
            $response['file'] = $th->getFile();
            $response['line'] = $th->getLine();
            $response['previous'] = $th->getPrevious();
        }

        return $response;
    }

}