<?php

namespace sa\events;

use Exception;
use sa\api\api;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\responses\Json;

class EventsApiController
{
    /** @var EventRecurrenceRepository */
    protected $eventRecurrenceRepository;

    /** @var EventRepository */
    protected $eventRepository;

    public function __construct()
    {
        $this->eventRecurrenceRepository = app::$entityManager->getRepository(ioc::staticGet('EventRecurrence'));
        $this->eventRepository = app::$entityManager->getRepository(ioc::staticGet('Event'));
    }

    /**
     * @param new api $api
     * @param $timestamp
     */
    public function findByDay($request)
    {
        $timestamp = $request->getRouteParams()->get('day');
        $response = new JSON();

        try {
            $date = new DateTime();
            $date->setTimestamp($timestamp);

            $eventResponse = new EventApiResponse($this->eventRecurrenceRepository->findByDay($date));
            $response->data['events'] = $eventResponse->response();
            $response->data['success'] = true;

            return $response;
        } catch(Exception $e) {
            $response->data['message'] = 'An error occurred while loading events.';
            $response->data['success'] = false;

            return $response;
        }
    }

//    public function findByMonth($request) {
    public function findByMonth($data)
    {
//    public function findByMonth($month, $category=null) {
//        $timestamp = $request->getRouteParams()->get('month');
        $month = $data['month'];
        $category = $data['category'];
        $response = new JSON();
        try {
            $date = new DateTime();
            $date->setTimestamp($month);

            $category = ioc::getRepository('Category')->findOneBy(['name' => $category]);
            $events = ioc::getRepository('Event')->findByMonth(1619827200, $category);
//            $eventResponse = new EventApiResponse($events);
            $response->data['events'] = $events;
            $response->data['success'] = true;

            return $response;
        } catch(Exception $e) {
            $response->data['success'] = false;
            $response->data['message'] = $e->getMessage();

            return $response;
        }
    }

    public function fillCalendar($data)
    {
        $start = $data['start'];
        $end = $data['end'];
        $category = $data['category'];
        $response = new JSON();
        try {
            $startDateTime = new DateTime();
            $startDateTime->setTimestamp($start);

            $endDateTime = new DateTime();
            $endDateTime->setTimestamp($end);

            $category = ioc::getRepository('Category')->findOneBy(['name' => $category]);
            $events = ioc::getRepository('Event')->fillFullCalendar($startDateTime, $endDateTime, $category);

            $response->data['events'] = $events;
            $response->data['success'] = true;

            return $response;
        } catch(Exception $e) {
            $response->data['success'] = false;
            $response->data['message'] = $e->getMessage();

            return $response;
        }
    }

    public function findByYear($request)
    {
        $timestamp = $request->getRouteParams()->get('year');
        $response = new JSON();
        try {
            $date = new DateTime();
            $date->setTimestamp($timestamp);

            $eventResponse = new EventApiResponse($this->eventRecurrenceRepository->findByYear($date));
            $response->data['events'] = $eventResponse->response();
            $response->data['success'] = true;
        } catch(Exception $e) {
            $response->data['success'] = false;
            $response->data['message'] = 'An error occurred while loading events.';
        }

        return $response;
    }

    /**
     * @param new api $api
     */
    public function findBetweenDates($timestamp, $timestamp2, api $api)
    {
        try {
            $date = new DateTime();
            $date->setTimestamp($timestamp);

            $date2 = new DateTime();
            $date2->setTimestamp($timestamp2);

            $response = $api->bldSuccessArray();
            $eventResponse = new EventApiResponse($this->eventRecurrenceRepository->findBetweenDates($date, $date2));
            $response['events'] = $eventResponse->response();
            $api->response(200, $response);
        } catch(Exception $e) {
            $api->response(500, $api->bldErrorArray('An error occurred while loading events.'));
        }
    }

    /**
     * @param new api $api
     */
    public function findUpcoming($numberOfEvents, api $api)
    {
        //@todo - Implement
    }

    /**
     * @param new api $api
     */
    public function findUpcomingAfterDate($timestamp, $numberOfEvents, api $api)
    {
        //@todo - Implement
    }
}
