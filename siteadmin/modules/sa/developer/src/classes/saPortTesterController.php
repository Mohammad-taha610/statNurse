<?php

namespace sa\developer;

use sacore\application\responses\View;
use sacore\application\saController;

class saPortTesterController extends saController
{
    public function portTester()
    {
        $view = new View('port_tester');

        return $view;
    }

    public function ajaxTestPort($data)
    {
        $connection = @fsockopen($data['host'], $data['port'], $errno, $errstr, 1);
        if (is_resource($connection)) {
            $data['status'] = 1;
            fclose($connection);
        } else {
            $data['status'] = 2;
        }

        return $data;
    }
}
