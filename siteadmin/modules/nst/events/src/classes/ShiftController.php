<?php


namespace nst\events;

use DateTimeZone;
use Matrix\Exception;
use nst\member\NstMember;
use nst\member\Provider;
use nst\member\ProviderRepository;
use nst\payroll\PayrollService;
use sacore\application\controller;
use sacore\application\Request;
use sacore\application\responses\View;
use sacore\application\responses\Json;
use sacore\application\ValidateException;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\app;
use sa\events\Event;
use sacore\application\saController;
use sacore\application\responses\Redirect;
use sa\events\EventRepository;
use sa\member\auth;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use sacore\application\DateTime;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;

class ShiftController extends controller
{
    /** @var  EventRepository $eventRepository */
    protected $eventRepository;

    /** @var  CategoryRepository $categoryRepository */
    protected $categoryRepository;
    
    /** @var  ProviderRepository $providerRepository */
    protected $providerRepository;

    public function __construct()
    {
        parent::__construct();
        $this->eventRepository = app::$entityManager->getRepository(ioc::staticGet('Event'));
        $this->categoryRepository = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'));
        $this->providerRepository = app::$entityManager->getRepository(ioc::staticGet('Provider'));
    }

    public function providerCreateShift(): View
    {
        $view = new View('edit_shift');
        $view->data['id'] = 0;

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $view->data['source_id'] = 0;
        $view->data['provider_id'] = $member->getProvider()->getId();
        $view->data['recurrence_id'] = 0;
        $view->data['recurrence_unique_id'] = 0;
        $view->data['recurrence_source_id'] = 0;
        $view->data['is_recurrence'] = 0;
        $view->data['is_copy'] = 0;
        $view->data['start_date'] = 0;
        $view->data['end_date'] = 0;

        $view->data['submit_url'] = app::get()->getRouter()->generate('save_shift', ['id' => 0]);
        return $view;
    }

    public function providerReviewShift(): View
    {
        $view = new View('review_shift');

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $view->data['provider_id'] = $member->getProvider()->getId();

        return $view;
    }

    /** @param Request $request */
    public function providerEditShift($request): View
    {
        $id = $request->getRouteParams()->get('id');

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        if ($id > 0) {
            $provider = $member?->getProvider();
            $shift = ioc::get('Shift', ['id' => $id]);

            if ($shift && $provider && $shift->getProvider()->getId() != $provider->getId()) {
                $view = new View('dashboard');
                return $view;
            }
        }

        $view = new View('edit_shift');
        $view->data['id'] = $id;

        $nurse = null;
        if ($id) {
            $shift = ioc::get('Shift', ['id' => $id]);
            $nurse = $shift->getNurse();
        }
        $view->data['source_id'] = 0;
        $view->data['provider_id'] = $member->getProvider()->getId();
        $view->data['nurse_id'] = $nurse ? $nurse->getId() : 0;
        $view->data['recurrence_id'] = 0;
        $view->data['recurrence_unique_id'] = 0;
        $view->data['recurrence_source_id'] = 0;
        $view->data['is_recurrence'] = 0;
        $view->data['is_copy'] = 0;
        $view->data['start_date'] = 0;
        $view->data['end_date'] = 0;

        $view->data['submit_url'] = app::get()->getRouter()->generate('save_shift', ['id' => $id]);
        return $view;
    }

    public function providerCopyShift($request): View
    {
        $id = $request->getRouteParams()->get('id');
        $view = new View('edit_shift');
        /** @var NstMember $member */
        $member = auth::getAuthMember();

        $view->data['id'] = 0;
        $view->data['source_id'] = $id;
        $view->data['provider_id'] = $member->getProvider()->getId();
        $view->data['recurrence_id'] = 0;
        $view->data['nurse_id'] = 0;
        $view->data['recurrence_unique_id'] = 0;
        $view->data['recurrence_source_id'] = 0;
        $view->data['is_recurrence'] = 0;
        $view->data['is_copy'] = true;
        $view->data['start_date'] = 0;
        $view->data['end_date'] = 0;

        $view->data['submit_url'] = app::get()->getRouter()->generate('save_shift', ['id' => $id]);
        return $view;
    }

    public function providerShiftRequests(): View
    {
        return new View('shift_requests');
    }

    public function providerPendingShifts(): View
    {
        return new View('pending_shifts_view');
    }


    //Here so we can change it later without much fuss, identical to events as of right now
    public function index($request): View
    {
        $view = new View('table', $this->viewLocation(), false);
        $perPage = 20;
        $fieldsToSearch = array();

        foreach ($request->query->all() as $field => $value) {
            if (strpos($field, 'q_') === 0 && !empty($value)) {
                $fieldsToSearch[str_replace('q_', '', $field)] = $value;
            }
        }

        $currentPage = !empty($request->request->get('page')) ? $request->request->get('page') : 1;
        $sort = !empty($request->request->get('sort')) ? $request->request->get('sort') : false;
        $sortDir = !empty($request->request->get('sortDir')) ? $request->request->get('sortDir') : false;

        /** @var EventRepository $repo */
        $repo = app::$entityManager->getRepository(ioc::staticResolve('Event'));
        $orderBy = ($sort) ? array($sort => $sortDir) : null;
        $data = $repo->search($fieldsToSearch, $orderBy, $perPage, (($currentPage - 1) * $perPage));
        $totalRecords = count($repo->findAll());
        $totalPages = ceil($totalRecords / $perPage);
        $view->data['table'][] = array(
            'header' => array(
                array('name' => 'Name', 'class' => '', 'sort' => 'name'),
                array('name' => 'Start Date', 'class' => '', 'sort' => 'start_date'),
                array('name' => 'Location', 'class' => '', 'sort' => 'location_name'),
                array('name' => 'Contact', 'class' => '', 'sort' => 'contact_name')
            ),
            'actions' => array(
                'edit' => array(
                    'name' => 'Edit',
                    'routeid' => 'sa_events_edit',
                    'params' => array('id')
                ),
                'delete' => array(
                    'name' => 'Delete',
                    'routeid' => 'sa_events_delete',
                    'params' => array('id')
                )
            ),
            'noDataMessage' => 'No Events Available',
            'map' => array('name', 'start_date', 'location_name', 'contact_name'),
            'tableCreateRoute' => 'sa_events_create',
            'data' => doctrineUtils::getEntityCollectionArray($data),
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
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
    public function edit($request): View
    {
        $id = $request->getRouteParams()->get('id');
        $event = $request->getRouteParams()->get('event');
        if (empty($event)) {
            if ($id == 0) {
                $event = ioc::resolve('Shift');
            } else {
                $event = app::$entityManager->find(ioc::staticResolve('Shift'), $id);
            }
        }

        /** @var saStateRepository $statesRepository */
        $statesRepository = app::$entityManager->getRepository(ioc::staticGet('saState'));

        $view = new View('sa_event_edit_view');

        $view->data['event'] = $event;
        $view->data['categories'] = $this->categoryRepository->findAll();
        $view->data['providers'] = $this->providerRepository->findAll();
        $view->data['timezones'] = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA);
        $view->data['states'] = $statesRepository->findAll();
        $view->data['months'] = Event::getMonths();
        $view->data['week_days'] = Event::getWeekDays();
        $view->data['defaultTimezone'] = (!empty($event->getTimezone()))
            ? $event->getTimezone()
            : app::getInstance()->getTimeZone()->getName();

        $pendingShifts = $event->getPendingShifts();
        $dataArray = [];
        foreach ($pendingShifts as $pendingShift) {
            $nurse = $pendingShift->getNurse();
            $member = $nurse->getMember();
            $name = $member->getLastName() . ", " . $member->getFirstName();
            $nurseApproved = ($pendingShift->getNurseApproved()) ? "Approved by Nurse" : "Not Approved by Nurse";
            $providerApproved = ($pendingShift->getProviderApproved()) ? "Approved by Provider" : "Not Approved by Provider";
            $singleArray = ['id' => $event->getId(), 'pendingShiftId' => $pendingShift->getId(), 'name' => $name, 'nurse_approved' => $nurseApproved, 'provider_approved' => $providerApproved];
            $dataArray[] = $singleArray;
        }


        if ($id != 0) {
            $view->data['table'][] = array(
                'tab-id' => 'pending-shifts-pane',
                /* SET THE HEADER OF THE TABLE UP */
                'header' => array(
                    array('name' => 'Nurse Name', 'class' => '', 'map' => 'name'),
                    array('name' => 'Nurse Approved', 'class' => '', 'map' => 'nurse_approved'),
                    array('name' => 'Provider Approved', 'class' => '', 'map' => 'provider_approved')
                ),
                /* SET ACTIONS ON EVERY ROW */
                'actions' => array(
                    'nurse_approve' => array('name' => 'Nurse Approve', 'routeid' => 'nurse_approve_pending_shift', 'params' => array('id', 'pendingShiftId')),
                    'provider_approve' => array('name' => 'Provider Approve', 'routeid' => 'provider_approve_pending_shift', 'params' => array('id', 'pendingShiftId')),
                    'delete' => array('name' => 'Delete', 'routeid' => 'delete_pending_shift', 'params' => array('id', 'pendingShiftId')),
                ),
                /* SET THE NO DATA MESSAGE */
                'noDataMessage' => 'No Pending Shifts',
                /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
                'tableCreateRoute' => ['routeId' => 'sa_create_pending_shift_for_shift', 'params' => ['type' => 'shift', 'id' => $event->getId()]],
                /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
                'data' => $dataArray
            );
        }

        return $view;
    }

    /**
     * Saves new shift object.
     *
     *
     * @throws \Exception
     */
    public static function save($data): Redirect|array
    {
        $data = $data['params'];
        if (empty($data)) {
            //Todo: this needs to be changed
            return new Redirect(app::get()->getRouter()->generate('sa_events_index'));
        }

        $notify = new notification();

        $shiftService = new ShiftService();

        $response = [];
        try {
            $shiftArray = $shiftService->saveShift($data);
            if (!$shiftArray['success']) {
                $response['success'] = false;
                $notify->addNotification('danger', 'Error', $shiftArray['message']);
                return $response;
            }
            $notify->addNotification('success', 'Success', 'Shift saved successfully.');
            // initialize pay rates
            $service = new PayrollService();
            $shift = ioc::getRepository('Event')->find($shiftArray['shift_id']) ;
            $service->initializeShiftRates($shift);
            $response['success'] = true;
            $response['url'] = app::get()->getRouter()->generate('events_index');
        } catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', $e->getMessage());
            $response['success'] = false;
            $response['error'] = $e->getMessage();
        }
        return $response;
    }

    /** @deprecated */
    public static function saveRecurrence($data): Redirect|array
    {
        $data = $data['params'];
        if (empty($data)) {
            //Todo: this needs to be changed
            return new Redirect(app::get()->getRouter()->generate('sa_events_index'));
        }

        $notify = new notification();

        $response = [];
        try {
            $shiftService = new ShiftService();
            $shiftArray = $shiftService->saveShiftRecurrence($data);

            $notify->addNotification('success', 'Success', 'Shift saved successfully.');
            $response['success'] = true;
            $response['url'] = app::get()->getRouter()->generate('events_index');
        } catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', $e->getMessage());
            $response['success'] = false;
            $response['error'] = $e->getMessage();
        }

        return $response;
    }

    public static function cancelShift($data): Redirect|array
    {
        $response = [];
        $notify = new notification();
        $data = $data['params'];

        if (empty($data)) {
            $errorMsg = "The required shift data was not provided.";
            $notify->addNotification('danger', 'Error', $errorMsg);
            $response['success'] = false;
            $response['error'] = $errorMsg;
            return $response;
        }

        $shiftService = new ShiftService();
        $result = $shiftService->cancelShift($data);

        if ($result['success']) {
            $notify->addNotification('success', 'Success', 'Shift cancelled successfully.');
            $response['success'] = true;
        } else {
            $notify->addNotification('danger', 'Failed', 'Shift was not cancelled.');
            $response['success'] = false;
        }
        $response['url'] = app::get()->getRouter()->generate('events_index');

        return $response;
    }

    public static function loadCategories($data): Json
    {
        $json = new Json();
        $categories = ioc::getRepository('EventsCategory')->findAll();
        $returnArray = [];
        foreach ($categories as $category) {
            $returnArray[] = ['value' => $category->getId(), 'text' => $category->getName()];
        }
        $json->data['categories'] = $returnArray;
        $json->data['success'] = true;
        return $json;
    }

    /**
     * @param $data
     * @return array
     * Returns a json response with event data to load onto calendar
     */
    public static function loadCalendar($data): array
    {
        $shiftService = new ShiftService();
        try {
            $response = $shiftService->loadCalendarShifts($data);
            $response['success'] = true;
        } catch (ValidateException $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public static function loadShiftData($data): array
    {
        $shiftService = new ShiftService();

        return $shiftService->loadShiftData($data);
    }

    public static function loadRecurrenceData($data): array
    {
        $shiftService = new ShiftService();

        return $shiftService->loadRecurrenceData($data);
    }

    public static function deleteShift($data): array
    {
        $shiftService = new ShiftService();

        return $shiftService->deleteShift($data);
    }

    public static function approveShiftRequest($data): array
    {
        $shiftService = new ShiftService();

        return $shiftService->approveShiftRequest($data);
    }

    public static function denyShiftRequest($data): array
    {
        $shiftService = new ShiftService();

        return $shiftService->denyShiftRequest($data);
    }

    public static function loadCalendarFilters($data): array
    {
        $shiftService = new ShiftService();

        return $shiftService->loadCalendarFilters($data);
    }

    public static function massDeleteShifts($data): array
    {
        $shiftService = new ShiftService();

        return $shiftService->massDeleteShifts($data);
    }
}
