<?php

namespace nst\applications;

use nst\applications\NurseBackgroundCheck;
use sacore\application\app;
use sacore\application\controller;
use sacore\application\modRequest;
use sacore\application\responses\View;
use sacore\utilities\doctrineUtils;

class NurseBackgroundCheckController extends controller
{
    public function index()
    {
        $member = modRequest::request('auth.member');

        $view = new View('nurse_background_check_form');

        $view->data['member'] = null;

        if ($member) {
            $view->data['member'] = doctrineUtils::getEntityArray($member);
        }

        return $view;
    }

    public function store($data)
    {
        $data['member'] = modRequest::request('auth.member');
        $data['signature'] = json_encode($data['signature']);
        $data['personal_information_signature'] = json_encode($data['personal_information']['signature']);

        $nurseBackgroundCheck = doctrineUtils::setEntityData(self::flatten($data), new NurseBackgroundCheck);

        app::$entityManager->persist($nurseBackgroundCheck);
        app::$entityManager->flush();
    }

    public static function flatten($array, $prefix = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + self::flatten($value, $prefix . $key . '_');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }
}
