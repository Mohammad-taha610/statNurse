<?php

namespace sa\dashboard;

use sacore\application\controller;
use sacore\application\view;
use sacore\utilities\url;

class dashboardController extends controller
{
    public function testDefaultRoute()
    {
        if (url::route() == '/') {
            return true;
        } else {
            return false;
        }
    }

    public function welcome()
    {
        $view = new view('master', 'dashboard', $this->viewLocation(), false);
        $view->display();
    }
}
