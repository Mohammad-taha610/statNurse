<?php

namespace sa\events;

use DateTimeZone;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\ValidateException;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;

class EventRecurrenceService
{
    public static function editEventRecurrenceViewData($event, $recurrenceId, $recurrenceUniqueId)
    {
        $data = [];

        if ($recurrenceId != 0) {
            $recurrence = ioc::getRepository('EventRecurrence')->find($recurrenceId);

            $recurrence = ioc::getRepository('EventRecurrence')->getRecurrenceEntityArray($recurrence);
//            $recurrence = doctrineUtils::getEntityArray($recurrence);
            $data['event'] = $event;
            $data['recurrence'] = $recurrence;
            $data['recurrenceId'] = $recurrenceId;
            $data['recurrenceUniqueId'] = $recurrenceUniqueId;
        } else {
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
            foreach ($times as $time) {
                $start_date_unique_id = $time->getStart()->format('mdY');
                $end_date_unique_id = $time->getEnd()->format('mdY');
                $uniqueId = $start_date_unique_id.'-'.$end_date_unique_id;
                if ($uniqueId == $recurrenceUniqueId) {
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

        $defaultTimezone = ((! is_null($recurrence['timezone'])) ? $recurrence['timezone'] : null);
        if (empty($defaultTimezone)) {
            $defaultTimezone = (! empty($event->getTimezone()) ? $event->getTimezone() : app::getInstance()->getTimeZone()->getName());
        }
        $data['defaultTimezone'] = $defaultTimezone;
        /** @var saStateRepository $statesRepository */
        $statesRepository = app::$entityManager->getRepository(ioc::staticGet('saState'));
        $data['states'] = $statesRepository->findAll();

        return $data;
    }

    public static function saveEventRecurrence($event, $recurrenceId, $recurrenceUniqueId, $postArray)
    {
        if ($recurrenceId != 0) {
            $recurrence = ioc::getRepository('EventRecurrence')->find($recurrenceId);
        } else {
            $recurrence = ioc::resolve('EventRecurrence');
            $event->addEventRecurrence($recurrence);
            $recurrence->setEvent($event);
            $recurrence->setRecurrenceUniqueId($recurrenceUniqueId);
        }
        $notification = new notification();

        $recurrence->setName($postArray['name'])
            ->setDescription($postArray['description'])
            ->setLink($postArray['link'])
            ->setLocationName($postArray['location_name'])
            ->setStreetOne($postArray['street_one'])
            ->setStreetTwo($postArray['street_two'])
            ->setCity($postArray['city'])
            ->setState($postArray['state'])
            ->setPostalCode($postArray['postal_code'])
            ->setContactName($postArray['contact_name'])
            ->setContactPhone($postArray['contact_phone'])
            ->setContactEmail($postArray['contact_email'])
            ->setTimezone($postArray['timezone']);

        try {
            if (empty($postArray['start_date']) || empty($postArray['start_time'])) {
                throw new ValidateException('Please enter a start date and start time.');
            }

            if (! $event->isAllDay() && (empty($postArray['end_date']) || empty($postArray['start_time']))) {
                throw new ValidateException('Please enter an end date and end time.');
            }

            if (! is_null($postArray['recurrence-exists']) && $postArray['recurrence-exists'] == 1) {
                $recurrence->setRecurrenceExists(true);
            } else {
                $recurrence->setRecurrenceExists(false);
            }

            $startTimestamp = sprintf('%s %s', $postArray['start_date'], $postArray['start_time']);
            $recurrence->setStart(new DateTime($startTimestamp, new DateTimeZone($event->getTimezone())));

            $endTimestamp = sprintf('%s %s', $postArray['end_date'], $postArray['end_time']);
            $recurrence->setEnd(new DateTime($endTimestamp, new DateTimeZone($event->getTimezone())));

            $recurrence->setDescription($postArray['description']);

            if (! empty($postArray['category_id'])) {
                /** @var CategoryRepository $categoryRepo */
                $categoryRepo = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'));
                /** @var Category $category */
                $category = $categoryRepo->findOneBy(['id' => $postArray['category_id']]);

                if (! is_null($category)) {
                    $event->setCategory($category);
                }
            }

            $notification->addNotification('success', 'Recurrence updated!');
            app::$entityManager->persist($recurrence);
            app::$entityManager->flush($recurrence);

            return app::get()->getRouter()->generate('sa_event_recurrences', ['id' => $event->getId()]);
        } catch (ValidateException $e) {
            $notification->addNotification('error', $e->getMessage());
        } catch(\Exception $e) {
            $notification->addNotification('error', 'Failed to save recurrence. Please make sure date and time 
                                            fields contain valid values.');
        }

        return self::editEventRecurrenceViewData($event, $recurrenceId, $recurrenceUniqueId);
    }
}
