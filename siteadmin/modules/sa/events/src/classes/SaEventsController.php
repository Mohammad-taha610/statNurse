<?php

namespace sa\events;

use DateTimeZone;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sa\system\saStateRepository;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;

class SaEventsController extends saController
{
    /** @var EventRepository */
    protected $eventRepository;

    /** @var CategoryRepository */
    protected $categoryRepository;

    public function __construct()
    {
        parent::__construct();
        $this->eventRepository = app::$entityManager->getRepository(ioc::staticGet('Event'));
        $this->categoryRepository = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'));
    }

    /**
     * @throws \sacore\application\Exception
     */
    public function index($request)
    {
        $view = new View('table', $this->viewLocation(), false);
        $perPage = 20;
        $fieldsToSearch = [];

        foreach ($request->query->all() as $field => $value) {
            if (strpos($field, 'q_') === 0 && ! empty($value)) {
                $fieldsToSearch[str_replace('q_', '', $field)] = $value;
            }
        }

        $currentPage = ! empty($request->request->get('page')) ? $request->request->get('page') : 1;
        $sort = ! empty($request->request->get('sort')) ? $request->request->get('sort') : false;
        $sortDir = ! empty($request->request->get('sortDir')) ? $request->request->get('sortDir') : false;

        /** @var EventRepository $repo */
        $repo = app::$entityManager->getRepository(ioc::staticResolve('Event'));
        $orderBy = ($sort) ? [$sort => $sortDir] : null;
        $data = $repo->search($fieldsToSearch, $orderBy, $perPage, (($currentPage - 1) * $perPage));
        $totalRecords = count($repo->findAll());
        $totalPages = ceil($totalRecords / $perPage);
        $view->data['table'][] = [
            'header' => [
                ['name' => 'Name', 'class' => '', 'sort' => 'name'],
                ['name' => 'Start Date', 'class' => '', 'sort' => 'start_date'],
                ['name' => 'Location', 'class' => '', 'sort' => 'location_name'],
                ['name' => 'Contact', 'class' => '', 'sort' => 'contact_name'],
            ],
            'actions' => [
                'edit' => [
                    'name' => 'Edit',
                    'routeid' => 'sa_events_edit',
                    'params' => ['id'],
                ],
                'recurrences' => [
                    'name' => 'Recurrences',
                    'routeid' => 'sa_event_recurrences',
                    'params' => ['id'],
                ],
                'delete' => [
                    'name' => 'Delete',
                    'routeid' => 'sa_events_delete',
                    'params' => ['id'],
                ],
            ],
            'noDataMessage' => 'No Events Available',
            'map' => ['name', 'start_date', 'location_name', 'contact_name'],
            'tableCreateRoute' => 'sa_events_create',
            'data' => doctrineUtils::getEntityCollectionArray($data),
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ];

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
        $id = $request->getRouteParams()->get('id');
        $event = $request->getRouteParams()->get('event');
        if (empty($event)) {
            if ($id == 0) {
                $event = ioc::resolve('Event');
            } else {
                $event = app::$entityManager->find(ioc::staticResolve('Event'), $id);
            }
        }

        /** @var saStateRepository $statesRepository */
        $statesRepository = app::$entityManager->getRepository(ioc::staticGet('saState'));

        $view = new view('sa_event_edit_view', $this->viewLocation(), false);

        //Moved to view as asset call
//        $view->addCSSResources(app::get()->getRouter()->generate('events_css', ['file' => 'bootstrap-datetimepicker.min.css']));
//        $view->addCSSResources(app::get()->getRouter()->generate('events_css', ['file' => 'slider.css']));
//        $view->addCSSResources(app::get()->getRouter()->generate('events_css', ['file' => 'sa_style.css']));
//
//        $view->addJSResources(app::get()->getRouter()->generate('events_js', ['file' => 'moment.js']));
//        $view->addJSResources(url::make('events_js', 'bootstrap-datetimepicker.min.js'), '11');
//        $view->addJSResources(app::get()->getRouter()->generate('events_js', ['file' => 'bootstrap-slider.js']));
//        $view->addJSResources(app::get()->getRouter()->generate('events_js', ['file' => 'rrule.js']));
//        $view->addJSResources(app::get()->getRouter()->generate('events_js', ['file' => 'nlp.js']));
//        $view->addJSResources(app::get()->getRouter()->generate('events_js', ['file' => 'sa_script.js']));

        $view->data['event'] = $event;
        $view->data['categories'] = $this->categoryRepository->findAll();
        $view->data['timezones'] = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA);
        $view->data['states'] = $statesRepository->findAll();
        $view->data['months'] = Event::getMonths();
        $view->data['week_days'] = Event::getWeekDays();
        $view->data['defaultTimezone'] = (! empty($event->getTimezone()))
            ? $event->getTimezone()
            : app::getInstance()->getTimeZone()->getName();

        return $view;
    }

    /**
     * Saves new Event object.
     *
     * @todo - Move object creation logic to factory.
     *
     * @throws \Exception
     */
    public function save($request)
    {
        if (empty($request->request->all())) {
            return new Redirect(app::get()->getRouter()->generate('sa_events_index'));
        }

        /** @var Event $Event */
        $event = null;
        $notify = new notification();

        try {
            if (! empty($request->request->get('id'))) {
                /** @var Event $event */
                $event = app::$entityManager->getRepository(ioc::staticGet('Event'))
                    ->findOneBy(['id' => $request->request->get('id')]);
            }

            if (empty($event)) {
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
                ->setContactEmail($request->request->get('contact_email'))
                ->setTimezone($request->request->get('timezone'));

            $timezone = ! (empty($request->request->get('timezone'))) ? new \DateTimeZone($request->request->get('timezone')) : new \DateTimeZone(app::getInstance()->getTimeZone());

            if (! empty($request->request->get('start_date'))) {
                $start = new DateTime($request->request->get('start_date'), $timezone);
                $start->setTimeZone(new DateTimeZone('UTC'));
                $event->setStartDate($start);
            }

            if (! empty($request->request->get('end_date'))) {
                $end = new DateTime($request->request->get('end_date'), $timezone);
                $end->setTimeZone(new DateTimeZone('UTC'));
                $event->setEndDate($end);
            }

            if (! $request->request->get('is-all-day-event')) {
                if (empty($request->request->get('start_time')) || empty($request->request->get('end_time'))) {
                    throw new ValidateException('Please enter a start time and end time.');
                }

                $startTime = new DateTime($request->request->get('start_time'), $timezone);
                $startTime->setTimeZone(new DateTimeZone('UTC'));

                $endTime = new DateTime($request->request->get('end_time'), $timezone);
                $endTime->setTimeZone(new DateTimeZone('UTC'));

                $event->setStartTime($startTime);
                $event->setEndTime($endTime);
            } else {
                $event->setStartTime(null);
                $event->setEndTime(null);
            }

            if ($request->request->get('is-recurring') == 1) {
                if ($request->request->get('repeat-until-date') == 1) {
                    if (! empty($request->request->get('until_date'))) {
                        $event->setUntilDate(new DateTime($request->request->get('until_date'), $timezone));
                    } else {
                        throw new ValidateException('You selected "repeat until date", but did not select a date.');
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

            if (! empty($request->request->get('frequency'))) {
                $event->setFrequency($request->request->get('frequency'));
            } else {
                $event->setFrequency(null);
            }

            if (! empty($request->request->get('interval'))) {
                $event->setInterval($request->request->get('interval'));
            } else {
                $event->setInterval(null);
            }

            if (! empty($request->request->get('week_days'))) {
                $event->setRecurrenceDays($request->request->get('week_days'));
            } else {
                $event->setRecurrenceDays([]);
            }

            if (! empty($request->request->get('months'))) {
                if (is_array($request->request->get('months'))) {
                    $offsetMonths = [];

                    // INFO : This is a fix for the way JS handles months,
                    // It sees them as beginning with 0 = January.
                    foreach ($request->request->get('months') as $month) {
                        $offsetMonths[] = $month + 1;
                    }

                    $event->setRecurrenceMonths($offsetMonths);
                } else {
                    $event->setRecurrenceMonths($request->request->get('months'));
                }
            } else {
                $event->setRecurrenceMonths([]);
            }

            if (! empty($request->request->get('category_id'))) {
                /** @var CategoryRepository $categoryRepo */
                $categoryRepo = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'));
                /** @var Category $category */
                $category = $categoryRepo->findOneBy(['id' => $request->request->get('category_id')]);

                if (! is_null($category)) {
                    $event->setCategory($category);
                }
            }

            /**
             * @throws \sacore\application\ValidateException
             */
            $event->validate();

            $utcstartdate = new \DateTime($event->getStartDate()->format('Ymd G:i:s', true), new DateTimeZone('UTC'));
            $utcenddate = new \DateTime($event->getEndDate()->format('Ymd G:i:s', true), new DateTimeZone('UTC'));

            $rrule = new Rule(null, $utcstartdate, $utcenddate, 'UTC');

            $rrule->setFreq($event->getFrequency())
                ->setInterval($event->getInterval())
                ->setWeekStart('SU');

            if ($request->request->get('is-recurring') == 1) {
                if (! empty($event->getRecurrenceDays())) {
                    $rrule->setByDay($event->getRecurrenceDays());
                }

                if (! empty($event->getRecurrenceMonths())) {
                    $rrule->setByMonth($event->getRecurrenceMonths());
                }

                if (! empty($event->getUntilDate())) {
                    $event->getUntilDate()->setTimezone(new DateTimeZone('UTC'));
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
            if ($request->request->get('is-recurring') == 1) {
                if (empty($event->getUntilDate())) {
                    //Setting it to a very large number
                    $rrule->setCount(Event::LARGE_NUMBER);
                }
            } else {
                $rrule->setCount(1);
            }

            $event->setMaxRecurrences($rrule->getCount());
            $times = $transformer->transform($rrule);

            if (count($times) > 0) {
                //If updating, remove existing occurrences of this event.
                if ($event->getId() != null) {
                    /** @var EventRecurrenceRepository $repo */
                    $recurrenceRepo = app::$entityManager->getRepository(ioc::staticGet('EventRecurrence'));
                    /** @var EventRecurrence[] $recurrences */
                    $recurrences = $recurrenceRepo->findBy(['event' => $event]);

                    $today = date('Y-m-d 00:00:00');
                    foreach ($recurrences as $recurrence) {
                        /**
                         * Only remove recurrences that have not occurred yet. Lock recurrences will not be
                         * removed.
                         */
                        if ($recurrence->getEnd() >= $today && ! $recurrence->isLocked()) {
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
//                    $start = new DateTime($original_start->format('m/d/Y') . " " . ( !$event->isAllDay() ? $event->getStartTime()->format('h:i A') : '00:00:00'));
//                    $end = new DateTime($original_end->format('m/d/Y') . " " . ( !$event->isAllDay() ? $event->getEndTime()->format('h:i A') : '00:00:00'));
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
            } elseif (! $request->request->get('is-recurring') && $event->getId() >= 1) {
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

    /**
     * Delete all recurrences for a recurring event.
     *
     * @param    $id
     * @return null
     */
    public function deleteRecurrences($request)
    {
        $id = $request->request->get('id');
        $eventRepository = app::$entityManager->getRepository(ioc::staticGet('Event'));

        $event = $eventRepository->findOneBy(['id' => $id]);

        $recurrenceRepository = app::$entityManager->getRepository(ioc::staticGet('EventRecurrence'));

        $today = new DateTime();

        $q = $recurrenceRepository->createQueryBuilder('r')
            ->join('r.event', 'event')
            ->where('r.start > :today')
            ->andWhere('event.id = :eventId')
            ->setParameter(':eventId', $id)
            ->setParameter(':today', $today);

        $recurrences = $q->getQuery()->getResult();

        foreach ($recurrences as $recurrence) {
            app::$entityManager->remove($recurrence);
        }

        app::$entityManager->flush();
    }

    /**
     * Deletes an Event object.
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public function delete($request)
    {
        $id = $request->getRouteParams()->get('id');
        $event = app::$entityManager->find(ioc::staticResolve('Event'), $id);
        $notify = new notification();

        try {
            app::$entityManager->remove($event);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Event deleted successfully.');

            return new Redirect(app::get()->getRouter()->generate('sa_events_index'));
        } catch(ValidateException $e) {
            $notify->addNotification('danger', 'Error', $e->getMessage());

            return new Redirect(app::get()->getRouter()->generate('member_sa_group'));
        }
    }
}
