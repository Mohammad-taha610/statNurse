<?php

namespace nst\events;

use DateTimeZone;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\ValidateException;
use sacore\application\responses\View;
use sacore\application\responses\Redirect;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use sacore\utilities\url;

class ShiftRecurrenceService extends \sa\events\EventRecurrenceService{

    public static function editEventRecurrenceViewData($event, $recurrenceId, $recurrenceUniqueId){
        $data = [];

        if($recurrenceId != 0) {
            $recurrence = ioc::getRepository('EventRecurrence')->find($recurrenceId);

            $recurrenceArray = ioc::getRepository('EventRecurrence')->getRecurrenceEntityArray($recurrence);
//            $recurrence = doctrineUtils::getEntityArray($recurrence);
            $data['event'] = $event;
            $data['recurrence'] = $recurrenceArray;
            $data['recurrenceId'] = $recurrenceId;
            $data['recurrenceUniqueId'] = $recurrenceUniqueId;
        }
        else {
            $rruleString = $event->getRecurrenceRules();
            $startDate = $event->getStart();
            $endDate = $event->getEnd();
            $until_date = $event->getUntilDate();
            $rrule = new Rule($rruleString, $startDate, $endDate);
            if(!is_null($until_date)) {
                $rrule->setUntil($until_date);
            }
            $transformer = new ArrayTransformer();
            $times = $transformer->transform($rrule);
            foreach ($times as $time){
                $start_date_unique_id = $time->getStart()->format('mdY');
                $end_date_unique_id = $time->getEnd()->format('mdY');
                $uniqueId = $event->getId() . '-' . $start_date_unique_id;
                if($uniqueId == $recurrenceUniqueId){
                    $singleArray = ['description' => '', 'recurrenceExists' => true];
                    $singleArray['start'] = $time->getStart();
                    $singleArray['end'] = $time->getEnd();
                    break;
                }
            }

            $data['event'] = $event;
            $data['eventId'] = $event->getId();
            $data['recurrenceId'] = $recurrenceId;
            $data['recurrenceUniqueId'] = $recurrenceUniqueId;
            $data['recurrence'] = $singleArray;
            $data['isRecurring'] = true;
        }

        $data['timezones'] = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA);

        $defaultTimezone = ((!is_null($recurrenceArray['timezone'])) ? $recurrenceArray['timezone'] : null);
        if(empty($defaultTimezone)) {
            $defaultTimezone = (!empty($event->getTimezone()) ? $event->getTimezone() : app::getInstance()->getTimeZone()->getName());
        }
        $data['defaultTimezone'] = $defaultTimezone;
        /** @var saStateRepository $statesRepository */
        $statesRepository = app::$entityManager->getRepository(ioc::staticGet('saState'));
        $data['states'] = $statesRepository->findAll();

        $pendingShifts = $recurrence->getPendingShifts();
        $dataArray = [];
        foreach ($pendingShifts as $pendingShift){
            $nurse = $pendingShift->getNurse();
            $member = $nurse->getMember();
            $name = $member->getLastName() . ", " . $member->getFirstName();
            $nurseApproved = ($pendingShift->getNurseApproved())?"Approved by Nurse": "Not Approved by Nurse";
            $providerApproved = ($pendingShift->getProviderApproved())?"Approved by Provider": "Not Approved by Provider";
            $singleArray = ['id' => $recurrence->getId(), 'pendingShiftId' => $pendingShift->getId(), 'name' => $name, 'nurse_approved' => $nurseApproved, 'provider_approved' => $providerApproved];
            $dataArray[] = $singleArray;
        }


        if($recurrenceId != 0 ) {
            $data['table'][] = array(
                'tab-id' => 'pending-shifts-pane',
                /* SET THE HEADER OF THE TABLE UP */
                'header' => array(
                    array('name' => 'Nurse Name', 'class' => '', 'map' => 'name'),
                    array('name' => 'Nurse Approved', 'class' => '', 'map' => 'nurse_approved'),
                    array('name' => 'Provider Approved', 'class' => '', 'map' => 'provider_approved')),
                /* SET ACTIONS ON EVERY ROW */
                'actions' => array(
                    'nurse_approve' => array('name' => 'Nurse Approve', 'routeid' => 'nurse_approve_pending_shift', 'params' => array('id', 'pendingShiftId')),
                    'provider_approve' => array('name' => 'Provider Approve', 'routeid' => 'provider_approve_pending_shift', 'params' => array('id', 'pendingShiftId')),
                    'delete' => array('name' => 'Delete', 'routeid' => 'delete_pending_shift', 'params' => array('id', 'pendingShiftId')),
                ),
                /* SET THE NO DATA MESSAGE */
                'noDataMessage' => 'No Pending Shifts',
                /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
                'tableCreateRoute' => ['routeId' => 'sa_create_pending_shift_for_shift_recurrence', 'params' => ['id' => $event->getId(), 'recurrenceId' => $recurrence->getId(), 'recurrenceUniqueId' => $recurrenceUniqueId]],
                /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
                'data' => $dataArray
            );
        }
        return $data;
    }

}