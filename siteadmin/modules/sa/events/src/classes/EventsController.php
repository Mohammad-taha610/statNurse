<?php

namespace sa\events;

use DateTime;
use sacore\application\app;
use sacore\application\controller;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\View;
use sacore\utilities\doctrineUtils;

/**
 * Class EventsController
 */
class EventsController extends controller
{
    /**
     * Displays the calendar with all events.

     *

     * @throws \Exception
     */
    public function index()
    {
        $view = new view('full_calendar_index_view');
//        $months = (new EventsApiController())->findByMonth(1619827200);
//        $view->data['eventData'] = $months;
        $view->data['category1'] = 'Test Category 1';
        $view->data['category2'] = 'Test Category 2';
//        $view = new view('events_index_view');

        return $view;
    }

    /**
     * Displays a single event.

     *

     * @throws \Exception
     */
    public function single($request)
    {
        /** @var EventRecurrenceRepository $recurrenceRepo */
        $id = $request->getRouteParams()->get('id');
        $event = ioc::getRepository('Event')->find($id);
        $recurrenceRepo = app::$entityManager->getRepository(ioc::staticGet('EventRecurrence'));

        $startDate = $event->getStartDate();
        $endDate = $event->getEndDate();
        $startTime = $event->getStartTime();
        $endTime = $event->getStartTime();
        if (! $event->isAllDay()) {
            $start = DateTime::createFromFormat('Y-m-d g:ia', $startDate->format('Y-m-d').' '.$startTime->format('g:ia'));
            $end = DateTime::createFromFormat('Y-m-d g:ia', $endDate->format('Y-m-d').' '.$endTime->format('g:ia'));
        } else {
            $start = DateTime::createFromFormat('Y-m-d', $startDate->format('Y-m-d'));
            $end = DateTime::createFromFormat('Y-m-d', $endDate->format('Y-m-d'));
        }

        $view = new view('events_single_view');

        $view->setXSSSanitation(false);

        $view->data = [

            'event' => doctrineUtils::getEntityArray($event),

            'is_all_day' => $event->isAllDay(),

            'is_recurring' => $event->isRecurring(),

            'location_address' => $event->getFullAddress(),

            'recurrence' => null,

            'start' => $start,

            'end' => $end,

            'reservationForm' => modRequest::request('events.get_reservation_form', $event)->getHtml(),

            'upcoming_recurrences' => doctrineUtils::getEntityCollectionArray(

                $recurrenceRepo->findUpcomingEventRecurrences($event)

            ),

        ];

        return $view;
    }

    public function singleRecurrence($request)
    {
        $id = $request->getRouteParams()->get('id');
        $event = ioc::getRepository('Event')->find($id);
        $recurrenceId = $request->getRouteParams()->get('recurrenceId');
        $recurrenceUniqueId = $request->getRouteParams()->get('recurrenceUniqueId');
        if ($recurrenceId == 0) {
            $recurrenceUniqueId = $request->getRouteParams()->get('recurrenceUniqueId');
            $startDate = DateTime::createFromFormat('mdY', explode('-', $recurrenceUniqueId)[0]);
            $endDate = DateTime::createFromFormat('mdY', explode('-', $recurrenceUniqueId)[1]);

            $startTime = $event->getStartTime();
            $endTime = $event->getStartTime();
            if (! $event->isAllDay()) {
                $start = DateTime::createFromFormat('Y-m-d g:ia', $startDate->format('Y-m-d').' '.$startTime->format('g:ia'));
                $end = DateTime::createFromFormat('Y-m-d g:ia', $endDate->format('Y-m-d').' '.$endTime->format('g:ia'));
            } else {
                $start = DateTime::createFromFormat('Y-m-d', $startDate->format('Y-m-d'));
                $end = DateTime::createFromFormat('Y-m-d', $endDate->format('Y-m-d'));
            }
        } else {
            $recurrence = ioc::getRepository('EventRecurrence')->find($recurrenceId);
            if ($event->getId() != $recurrence->getEvent()->getId()) {
                $this->error404();
            }
            $start = $recurrence->getStart();
            $end = $recurrence->getEnd();
        }

        /** @var EventRecurrenceRepository $recurrenceRepo */
        $recurrenceRepo = app::$entityManager->getRepository(ioc::staticGet('EventRecurrence'));

        $view = new view('events_single_view');

        $view->setXSSSanitation(false);

        $reservationFormData = [];
        $reservationFormData['event'] = $event;
        $reservationFormData['recurrenceId'] = $recurrenceId;
        $reservationFormData['recurrenceUniqueId'] = $recurrenceUniqueId;

        $view->data = [

            'event' => doctrineUtils::getEntityArray($event),

            'is_all_day' => $event->isAllDay(),

            'is_recurring' => $event->isRecurring(),

            'location_address' => $event->getFullAddress(),

            'recurrence' => doctrineUtils::getEntityArray($recurrence),

            'start' => $start,

            'end' => $end,

            'reservationForm' => modRequest::request('events.get_reservation_form', $reservationFormData)->getHtml(),

            'upcoming_recurrences' => doctrineUtils::getEntityCollectionArray(

                $recurrenceRepo->findUpcomingEventRecurrences($event)

            ),

        ];

        return $view;
    }

    /**
     * @param  Event  $event

     * @return string
     */
    public function getReservationForm($data)
    {
        $view = new view('_reservation_form');

        if (is_array($data)) {
            $event = $data['event'];
            $recurrence = $data['recurrence'];
            $recurrenceId = (is_null($recurrence)) ? 0 : $recurrence->getId();
            $recurrenceUniqueId = $data['recurrenceUniqueId'];

            $view->data = [

                'event' => doctrineUtils::getEntityArray($event),

                'addReservationUrl' => app::get()->getRouter()->generate('events_api_create_reservation_for_recurrence', ['id' => $event->getId(), 'recurrenceId' => $recurrenceId, 'recurrenceUniqueId' => $recurrenceUniqueId]),

                'cancelReservationUrl' => app::get()->getRouter()->generate('events_api_cancel_reservation_for_recurrence', ['id' => $event->getId(), 'recurrenceId' => $recurrenceId]),

            ];
        } else {
            $event = $data;
            $view->data = [

                'event' => doctrineUtils::getEntityArray($event),

                'addReservationUrl' => app::get()->getRouter()->generate('events_api_create_reservation', ['id' => $event->getId()]),

                'cancelReservationUrl' => app::get()->getRouter()->generate('events_api_cancel_reservation', ['id' => $event->getId()]),
            ];
        }

        return $view;
    }

    public function viewCategoryById($id)
    {
        $view = new View('event_category');
        $category = ioc::getRepository('\sa\events\Category')->find($id);
        if ($category) {
            $view->data['category'] = doctrineUtils::getEntityArray($category);
            $events = $category->getEvents();
            $view->data['events'] = doctrineUtils::getEntityCollectionArray($events);
            $view->addXssSanitationExclude('description');
        } else {
            return $this->error404();
        }

        return $view;
    }
}
