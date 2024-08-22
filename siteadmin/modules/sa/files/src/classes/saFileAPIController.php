<?php

namespace sa\files;

use sa\api\apiController;
use sacore\application\app;
use sacore\application\ioc;

/**
 * Class saFileAPIController
 * @package sa\files
 */
class saFileAPIController extends apiController
{
    /**
     * @param $urldata
     * @param $json
     * @return int
     * @api
     *
     * This function takes a string of image data that is base64 encoded, uploads it, and puts it in the database
     * The corresponding database id is returned.
     */
    public function uploadImage($urldata, $json)
    {
        if(!$json || !$json['image'] || !$json['name'])
        {
            $this->api->response(422, array('error'=>'You are missing the image and/or image name.'));
            return;
        }

        /** @var saImage $image */
        $image = ioc::resolve('saImage');

        $image = $image->uploadFromString(base64_decode($json['image']), $json['name']);

        $image->createMultipleSizes(false);

        app::$entityManager->persist($image);
        app::$entityManager->flush();

        return $image->getId();
    }
}
