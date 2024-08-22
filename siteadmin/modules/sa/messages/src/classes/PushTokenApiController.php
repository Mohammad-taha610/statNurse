<?php

namespace sa\messages;

use sa\api\apiController;
use sacore\application\ioc;

class PushTokenApiController extends apiController {
    
    public function create($id, $data)
    {
        /** @var PushToken $existingToken */
        $existingToken = ioc::getRepository('PushToken')->findOneBy(array('device_uuid' => $data['device_uuid']));
        
        if($existingToken) {
            return parent::update($existingToken->getId(), $data);
        } else {
            return parent::create($id, $data);
        }
    }

}