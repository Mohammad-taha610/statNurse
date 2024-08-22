<?php

namespace sa\events;

use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;

/**
 * Class SaEventRecurrenceController
 */
class SaEventRecurrencesController extends saController
{
    /**
     * @param  Event  $event
     */
    public function index($request)
    {
        $id = $request->getRouteParams()->get('id');
        /** @var Event $event */
        $event = ioc::getRepository('Event')->find($id);
        $perPage = 20;
        $currentPage = ! empty($request->get('page')) ? $request->get('page') : 1;

        $view = new View('sa_event_recurrence_index_view', $this->viewLocation());

        $view->data['event'] = doctrineUtils::getEntityArray($event);
        $view->data['eventId'] = $id;

        $rruleString = $event->getRecurrenceRules();
        $startDate = $event->getStartDate();
        $endDate = $event->getEndDate();
        $until_date = $event->getUntilDate();

        $rrule = new Rule($rruleString, $startDate, $endDate);
        if (! is_null($until_date)) {
            $rrule->setUntil($until_date);
        }
        $transformer = new ArrayTransformer();
        $times = $transformer->transform($rrule);

        $tableData = ioc::getRepository('Event')->getEventRecurrences($id, $perPage * $currentPage, $perPage * ($currentPage - 1));

        $totalRecords = count($times);
        $totalPages = ceil($totalRecords / $perPage);
        $view->data['table'][] = [
            'header' => [
                ['name' => 'Start Date', 'class' => '', 'sort' => 'start_date'],
                ['name' => 'End Date', 'class' => '', 'sort' => 'end_date'],
                ['name' => 'Start Time', 'class' => '', 'sort' => 'start_time'],
                ['name' => 'End Time', 'class' => '', 'sort' => 'end_time'],
            ],
            'actions' => [
                'edit' => [
                    'name' => 'Edit',
                    'routeid' => 'sa_event_recurrence_edit',
                    'params' => ['eventId', 'recurrenceId', 'recurrenceUniqueId'],
                ],
                //Removing the delete button because in order to remove it from the calendar we have to make a recurrence and then set it to inactive
                //                'delete' => array(
                //                    'name'    => 'Delete',
                //                    'routeid' => 'sa_event_recurrence_delete',
                //                    'params'  => array('eventId', 'recurrenceId')
                //                )
            ],
            'noDataMessage' => 'No Events Available',
            'map' => ['start_date', 'end_date', 'start_time', 'end_time'],
            'data' => $tableData,
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ];

        return $view;
    }

    public function edit($request)
    {
        $eventId = $request->getRouteParams()->get('eventId');
        $recurrenceId = $request->getRouteParams()->get('recurrenceId');
        $recurrenceUniqueId = $request->getRouteParams()->get('recurrenceUniqueId');
        $event = ioc::getRepository('Event')->find($eventId);
        $view = new view('sa_event_recurrence_edit_view');
        $view->data = EventRecurrenceService::editEventRecurrenceViewData($event, $recurrenceId, $recurrenceUniqueId);

        return $view;
    }

    public function save($request)
    {
        $eventId = $request->getRouteParams()->get('eventId');
        $recurrenceId = $request->getRouteParams()->get('recurrenceId');
        $recurrenceUniqueId = $request->getRouteParams()->get('recurrenceUniqueId');

        $event = ioc::getRepository('Event')->find($eventId);
        $return = EventRecurrenceService::saveEventRecurrence($event, $recurrenceId, $recurrenceUniqueId, $request->request->all());
        if (is_string($return)) {
            return new Redirect($return);
        } else {
            $view = new view('sa_event_recurrence_edit_view');
            $view->data = $return;

            return $view;
        }
    }

    public function delete($request)
    {
        $eventId = $request->getRouteParams()->get('eventId');
        $recurrenceId = $request->getRouteParams()->get('recurrenceId');
        $notification = new notification();

        if ($recurrenceId != 0) {
            $recurrence = ioc::getRepository('EventRecurrence')->find($recurrenceId);
        }

        try {
            if (is_null($recurrence)) {
                throw new ValidateException('Oops! That recurrence has already been deleted.');
            }

            app::$entityManager->remove($recurrence);
            app::$entityManager->flush($recurrence);
            $notification->addNotification('success', 'Recurrence deleted!');
        } catch (ValidateException $e) {
            $notification->addNotification('error', $e->getMessage());
        } catch (\Exception $e) {
            $notification->addNotification('error', 'Failed to delete recurrence.');
        }

        return new Redirect(app::get()->getRouter()->generate('sa_event_recurrences', ['id' => $eventId]));
    }
}
