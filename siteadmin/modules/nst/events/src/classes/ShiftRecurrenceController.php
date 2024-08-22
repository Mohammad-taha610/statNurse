<?php


namespace nst\events;


use DateTimeZone;
use nst\member\Provider;
use nst\member\ProviderRepository;
use sacore\application\responses\View;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\app;
use sa\events\Event;
use sacore\application\saController;
use sacore\application\responses\Redirect;
use sa\events\EventRepository;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use sacore\application\DateTime;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;

class ShiftRecurrenceController extends saController{

    /** @var  EventRepository */
    protected $eventRepository;
    /** @var  CategoryRepository */
    protected $categoryRepository;

    public function __construct()
    {
        parent::__construct();
        $this->eventRecurrenceRepository = app::$entityManager->getRepository(ioc::staticGet('EventRecurrence'));
        $this->categoryRepository = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'));
        $this->providerRepository = app::$entityManager->getRepository(ioc::staticGet('Provider'));
    }

    //Here in case we have to modify it
    public function index($request){
        $id = $request->getRouteParams()->get('id');
        /** @var Event $event */
        $event = ioc::getRepository('Event')->find($id);
        $perPage = 20;
        $currentPage = ! empty($request->get('page')) ? $request->get('page') : 1;

        $view = new View('sa_event_recurrence_index_view', $this->viewLocation());

        $view->data['event'] = doctrineUtils::getEntityArray($event);
        $view->data['eventId'] = $id;

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

        $tableData = ioc::getRepository('Event')->getEventRecurrences($id, $perPage * $currentPage, $perPage * ($currentPage - 1));

        $totalRecords = count($times);
        $totalPages   = ceil($totalRecords / $perPage);
        $view->data['table'][] = array(
            'header'           => array(
                array('name' => 'Start Date', 'class' => '', 'sort' => 'start_date'),
                array('name' => 'End Date', 'class' => '', 'sort' => 'end_date'),
                array('name' => 'Start Time', 'class' => '', 'sort' => 'start_time'),
                array('name' => 'End Time', 'class' => '', 'sort' => 'end_time')
            ),
            'actions'          => array(
                'edit'   => array(
                    'name'    => 'Edit',
                    'routeid' => 'sa_event_recurrence_edit',
                    'params'  => array('eventId', 'recurrenceId', 'recurrenceUniqueId')
                ),
                //Removing the delete button because in order to remove it from the calendar we have to make a recurrence and then set it to inactive
//                'delete' => array(
//                    'name'    => 'Delete',
//                    'routeid' => 'sa_event_recurrence_delete',
//                    'params'  => array('eventId', 'recurrenceId')
//                )
            ),
            'noDataMessage'    => 'No Events Available',
            'map'              => array('start_date', 'end_date', 'start_time', 'end_time'),
            'data'             => $tableData,
            'totalRecords'     => $totalRecords,
            'totalPages'       => $totalPages,
            'currentPage'      => $currentPage,
            'perPage'          => $perPage
        );

        return $view;
    }

    /**
     * Displays the create/edit page for a Event.
     *
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function edit($request)
    {
        $eventId = $request->getRouteParams()->get('eventId');
        $recurrenceId = $request->getRouteParams()->get('recurrenceId');
        $recurrenceUniqueId = $request->getRouteParams()->get('recurrenceUniqueId');
        $event = ioc::getRepository('Event')->find($eventId);
        $view = new view('sa_event_recurrence_edit_view');
        $view->data = ShiftRecurrenceService::editEventRecurrenceViewData($event, $recurrenceId, $recurrenceUniqueId);

        return $view;
    }

    /**
     * Saves new shift object.
     *
     *
     * @throws \Exception
     */
    public function save($request)
    {
        if(empty($request->request->all())) {
            return new Redirect(app::get()->getRouter()->generate('sa_events_index'));
        }

        /** @var Event $Event */
        $event = null;
        $notify = new notification();

        try {
            if(!empty($request->request->get('id'))) {
                /** @var Event $event */
                $event = app::$entityManager->getRepository(ioc::staticGet('Event'))
                    ->findOneBy(array('id' => $request->request->get('id')));
            }

            if(empty($event)) {
                /** @var Event $event */
                $event = ioc::resolve('Event');
            }

            $event->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'))
                ->setLink($request->request->get('link'))
                ->setLocationName($request->request->get('location_name'))
                ->setStreetOne($request->request->get('street_one'))
                ->setStreetTwo($request->request->get('street_two'))
                ->setCity($request->request->get('city'))
                ->setState($request->request->get('state'))
                ->setPostalCode($request->request->get('postal_code'))
                ->setContactName($request->request->get('contact_name'))
                ->setContactPhone($request->request->get('contact_phone'))
                ->setContactEmail($request->request->get('contact_email'));

            $timezone = !(empty($request->request->get('timezone'))) ? new \DateTimeZone($request->request->get('timezone')) : new \DateTimeZone(app::getInstance()->getTimeZone());

            if(!empty($request->request->get('start_date'))) {
                $start = new DateTime($request->request->get('start_date'), $timezone);
                $event->setStartDate($start);
            }

            if(!empty($request->request->get('end_date'))) {
                $end = new DateTime($request->request->get('end_date'), $timezone);
                $event->setEndDate($end);
            }

            if(!$request->request->get('is-all-day-event')) {

                if(empty($request->request->get('start_time')) || empty($request->request->get('end_time'))) {
                    throw new ValidateException("Please enter a start time and end time.");
                }

                $startTime = new DateTime($request->request->get('start_time'), $timezone);

                $endTime = new DateTime($request->request->get('end_time'), $timezone);

                $event->setStartTime($startTime);
                $event->setEndTime($endTime);
            } else {
                $event->setStartTime(null);
                $event->setEndTime(null);
            }

            if($request->request->get('is-recurring') == 1) {
                if($request->request->get('repeat-until-date') == 1) {
                    if(!empty($request->request->get('until_date'))) {
                        $event->setUntilDate(new DateTime($request->request->get('until_date'), $timezone));
                    } else {
                        throw new ValidateException("You selected \"repeat until date\", but did not select a date.");
                    }
                } else {
                    $event->setUntilDate(null);
                }
            } else {
                //Removing leftover recurrences if switching from
                //previously recurring event to a non-recurring event

                if ($request->request->get('id')) {
//                    $this->deleteRecurrences($request);
                }
                $event->setUntilDate(null);
            }

            if(!empty($request->request->get('frequency'))) {
                $event->setFrequency($request->request->get('frequency'));
            } else {
                $event->setFrequency(null);
            }

            if(!empty($request->request->get('interval'))) {
                $event->setInterval($request->request->get('interval'));
            } else {
                $event->setInterval(null);
            }

            if(!empty($request->request->get('week_days'))) {
                $event->setRecurrenceDays($request->request->get('week_days'));
            } else {
                $event->setRecurrenceDays(array());
            }

            if(!empty($request->request->get('months'))) {
                if(is_array($request->request->get('months'))) {
                    $offsetMonths = array();

                    // INFO : This is a fix for the way JS handles months,
                    // It sees them as beginning with 0 = January.
                    foreach($request->request->get('months') as $month) {
                        $offsetMonths[] = $month + 1;
                    }

                    $event->setRecurrenceMonths($offsetMonths);
                } else {
                    $event->setRecurrenceMonths($request->request->get('months'));
                }
            } else {
                $event->setRecurrenceMonths(array());
            }

            if(!empty($request->request->get('category_id'))) {
                /** @var CategoryRepository $categoryRepo */
                $categoryRepo = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'));
                /** @var Category $category */
                $category = $categoryRepo->findOneBy(array('id' => $request->request->get('category_id')));

                if(!is_null($category)) {
                    $event->setCategory($category);
                }
            }

            if(!empty($request->request->get('provider_id'))) {
                /** @var ProviderRepository $categoryRepo */
                $providerRepo = app::$entityManager->getRepository(ioc::staticGet('Provider'));
                /** @var Provider $category */
                $provider = $providerRepo->find($request->request->get('provider_id'));

                if(!is_null($provider)) {
                    $event->setProvider($provider);
                }
            }

            /**
             * @throws \sacore\application\ValidateException
             */
            $event->validate();

            $utcstartdate = new \DateTime( $event->getStart()->format('Ymd G:i:s', true), new DateTimeZone('UTC')  );
            $utcenddate = new \DateTime( $event->getEnd()->format('Ymd G:i:s', true), new DateTimeZone('UTC')  );

            $rrule  = new Rule(null, $utcstartdate, $utcenddate, 'UTC');


            $rrule->setFreq($event->getFrequency())
                ->setInterval($event->getInterval())
                ->setWeekStart('SU');

            if($request->request->get('is-recurring') == 1) {
                if(!empty($event->getRecurrenceDays())) {
                    $rrule->setByDay($event->getRecurrenceDays());
                }

                if(!empty($event->getRecurrenceMonths())) {
                    $rrule->setByMonth($event->getRecurrenceMonths());
                }


                if(!empty($event->getUntilDate())) {
                    $rrule->setUntil($event->getUntilDate());
                } else {
                    $event->setUntilDate(null);
                }
            } else {
                $event->setUntilDate(null);
            }

            $event->setRecurrenceRules($rrule->getString());

            $transformer = new ArrayTransformer();

            /**
             * If until date is not set, limit the number of recurrences to
             * be generated.
             */
            if($request->request->get('is-recurring') == 1) {
                if(empty($event->getUntilDate())) {
                    //Setting it to a very large number
                    $rrule->setCount(Event::LARGE_NUMBER);
                }
            }
            else {
                $rrule->setCount(1);
            }

            $event->setMaxRecurrences($rrule->getCount());
            $times = $transformer->transform($rrule);

            if(count($times) > 0) {

                //If updating, remove existing occurrences of this event.
                if($event->getId() != null) {
                    /** @var EventRecurrenceRepository $repo */
                    $recurrenceRepo = app::$entityManager->getRepository(ioc::staticGet('EventRecurrence'));
                    /** @var EventRecurrence[] $recurrences */
                    $recurrences = $recurrenceRepo->findBy(array('event' => $event));

                    $today = date("Y-m-d 00:00:00");
                    foreach($recurrences as $recurrence) {
                        /**
                         * Only remove recurrences that have not occurred yet. Lock recurrences will not be
                         * removed.
                         */
                        if($recurrence->getEnd() >= $today && !$recurrence->isLocked()) {
                            app::$entityManager->remove($recurrence);
                        }
                    }
                    app::$entityManager->flush();

                }

                //Commented out while restructuring so I don't have to look through the git log
//                /** @var \Recurr\Recurrence $time */
//                foreach($times as $time) {
//                    $original_start = $time->getStart();
//                    $original_start->setTimezone(new DateTimeZone('UTC'));
//                    $original_end = $time->getEnd();
//                    $original_end->setTimezone(new DateTimeZone('UTC'));
//
//
//                    // These may be the culprit behind recurring events being off on all day events
//                    $start = new DateTime($original_start->format('m/d/Y') . " " . ( !$event->isAllDay() ? $event->getStart()->format('h:i A') : '00:00:00'));
//                    $end = new DateTime($original_end->format('m/d/Y') . " " . ( !$event->isAllDay() ? $event->getEnd()->format('h:i A') : '00:00:00'));
//
//                    $start->setTimeZone($timezone);
//                    $end->setTimeZone($timezone);
//
//
//                    /** @var EventRecurrence $eventRecurrence */
//                    $eventRecurrence = ioc::resolve('EventRecurrence');
//                    $eventRecurrence->setStart($start)
//                        ->setEnd($end)
//                        ->setEvent($event)
//                        ->setLocked(false);
//
//                    app::$entityManager->persist($eventRecurrence);
//                }
            } elseif (!$request->request->get('is-recurring') && $event->getId() >= 1){
                //remove all recurrences if they exist;
                $recurrences = app::$entityManager->getRepository(ioc::staticResolve('EventRecurrence'))->findByEvent($event);
                foreach ($recurrences as $rec) {
                    app::$entityManager->remove($rec);
                }

            } else {
                throw new ValidateException('Oops! The repeat rules you set will never occur within the start date
                                                and end date. Either extend your start/end date range or adjust your
                                                repeat rules to fit within the date range.');
            }

            app::$entityManager->persist($event);
            app::$entityManager->flush();

            $notify->addNotification('success', 'Success', 'Event saved successfully.');
            return new Redirect(app::get()->getRouter()->generate('sa_events_index'));
        } catch(ValidateException $e) {
            $notify->addNotification('danger', 'Error', $e->getMessage());
            return $this->edit($request);
        }
    }
}