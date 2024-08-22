<?php
namespace nst\events;

use sa\events\EventsController;

use DateTime;
use sacore\application\app;
use sacore\application\controller;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\View;
use sacore\utilities\doctrineUtils;
use sacore\utilities\url;

/**
 * @deprecated
 * @IOC_NAME="EventsController"
 */
class NstEventsController extends EventsController
{
    public function createShiftView($request)
    {
        $view = new View('create_shift');

        // json encode entity array
        $view->data['shift'] = 5;

        return $view;
    }

    public function shiftRequestView($request)
    {
        $view = new View('shift_requests');

        // json encode entity array

        return $view;
    }

    public function getShiftRequests($request) {
        
    }
}