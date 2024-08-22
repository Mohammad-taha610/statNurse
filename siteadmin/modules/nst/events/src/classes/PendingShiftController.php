<?php


namespace nst\events;

use DateTimeZone;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use JsonSchema\Exception\ValidationException;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\responses\View;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\app;
use sacore\application\ValidateException;
use sa\events\CategoryRepository;
use sa\events\Event;
use sacore\application\saController;
use sa\events\EventRepository;
use sacore\utilities\notification;

/** @deprecated */
class PendingShiftController extends saController
{

    /** @var EventRepository $eventRepository */
    protected $eventRepository;
    /** @var CategoryRepository $categoryRepository */
    protected $categoryRepository;

    public function __construct()
    {
        parent::__construct();
        $this->eventRepository = app::$entityManager->getRepository(ioc::staticGet('Event'));
        $this->categoryRepository = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'));
    }


//    public function listShifts($request){
//        $view = new View('table', static::viewLocation());
//
//        [$shifts, $totalPages, $totalRecords, $currentPage, $perPage] = ioc::getRepository('Shift')->getPaginatedShifts($request);
//        foreach ($shifts as $shift) {
//            /** @var Shift $shift */
//            if (strlen($shift->getDescription()) > 100)
//            {
//                $description = substr($shift->getDescription(), 0, 100);
//            }else{
//                $description = $shift->getDescription();
//            }
//            $dataSingle = ['id' => $shift->getId(), 'name' => $shift->getName(), 'date_created' => $shift->getDateCreated()->format('m/d/Y'), 'date_available' => $shift->getNextAvailableDate()->format('m/d/Y'),'description' => $description, 'customer' => ($customer)?$customer->getName():'', 'barcode' => $photo];
//            $dataArray[] = $dataSingle;
//        }
//
//        $shift_table = array(
//            /* SET THE HEADER OF THE TABLE UP */
//            'header'=>array(array('name'=>'Name', 'class'=>''), array('name'=>'Date Created', 'class'=>'', 'searchType' => 'date'), array('name'=>'Next Date Available', 'class'=>'', 'searchType' => 'date'), array('name'=>'Description', 'class'=>'width-200'), array('name'=>'Customer', 'class'=>'hidden-480'), array('name'=>'Barcode', 'class'=>'hidden-480')),
//            /* SET ACTIONS ON EVERY ROW */
//            'actions'=>array('view'=>array('name'=>'Edit', 'routeid'=>'edit_kit', 'params'=>array('id')),
//                'delete' => ['name'=>'Delete', 'routeid' => 'delete_kit', 'params'=> ['id']]),
//            'massActions'=>[
//                'edit' => ['name'=>'Edit', 'routeid' => 'mass_kit_edit_view', 'confirm' => true],
//                'delete' => ['name'=>'Delete', 'routeid' => 'mass_kit_delete', 'confirm' => true]],
//            'massActionsCheckboxValue' => 'id',
//            'tableCreateRoute' => 'create_kit',
//            /* SET THE NO DATA MESSAGE */
//            'noDataMessage'=>'No Kits Available',
//            /* SET THE DATA MAP */
//            'map'=>array('name', 'date_created','date_available', 'description', 'customer', 'barcode'),
//            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
//            'data'=>  $dataArray,
//            'searchable' => true,
//            'custom_search_fields' => array('per_page' => ['name' => 'Number of Items Per Page', 'searchType' => 'number', 'extraOptions' => 'min="1"']),
//            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
//            'totalRecords'=> $totalRecords,
//            'totalPages'=> $totalPages,
//            'currentPage'=> $currentPage,
//            'perPage'=> $perPage,
//        );
//
//        $view->data['table'][] = $shift_table;
//
//        return $view;
//    }

    /**
     * Displays the create/edit page for a Event.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function editSA($request)
    {
        $shiftId = $request->getRouteParams()->get('id');
        $view = new View('sa_edit_pending_shift_view');
        $shift = ioc::getRepository('Shift')->find($shiftId);
        $view->data['provider'] = $shift->getProvider()->getMember()->getCompany();

        if (!is_null($shift->getStartTime())) {
            $view->data['startTime'] = $shift->getStart()->format('d/m/Y') . ' ' . $shift->getStart()->format('g:i A');
            $view->data['endTime'] = $shift->getEnd()->format('d/m/Y') . ' ' . $shift->getEnd()->format('g:i A');
        } else {
            $view->data['startTime'] = $shift->getStart()->format('d/m/Y');
            $view->data['endTime'] = $shift->getEnd()->format('d/m/Y') . ' All Day';
        }

        return $view;
    }

    public function loadVue($data)
    {
        $json = new Json();
        $shift = ioc::getRepository('Shift')->find($data['shiftId']);

        if ($data['pendingShiftId'] == 0) {
            $json->data['providerApproved'] = false;
            $json->data['nurseApproved'] = false;
            $json->data['nurse'] = null;
        } else {
            $pendingShift = ioc::getRepository('PendingShift')->find($data['pendingShiftId']);
            $nurse = $pendingShift->getNurse();
            $json->data['nurseApproved'] = $pendingShift->getNurseApproved();
            $json->data['providerApproved'] = $pendingShift->getProviderApproved();
            $member = $nurse->getMember();
            $json->data['nurse'] = ['id' => $nurse->getId(), 'name' => $member->getLastName() . ', ' . $member->getFirstName()];
        }

        $itemsPerPage = 100;

        $nurses = ioc::getRepository('Nurse')->search(['nurseNumber' => ''], 'DESC', $itemsPerPage);

        $nurseOptions = [];
        foreach ($nurses as $nurse) {
            $member = $nurse->getMember();
            $singleArray = ['id' => $nurse->getId(), 'name' => $member->getLastName() . ', ' . $member->getFirstName()];
            $nurseOptions[] = $singleArray;
        }

        $json->data['nurseOptions'] = $nurseOptions;
        $json->data['success'] = true;

        return $json;
    }

    public function loadNurse($data)
    {
        $shift = ioc::getRepository('Shift')->find($data['id']);
    }

    /**
     * @throws OptimisticLockException
     * @throws IocException
     * @throws ORMException
     * @throws IocDuplicateClassException
     */
    public function save($data)
    {
        $eventId = $data['id'];

        $nurseId = $data['nurse']['id'];
        $nurseApproved = $data['nurse_approved'];

        $providerApproved = $data['provider_approved'];
        if (!is_null($data['pending_shift_id'])) {
            $pendingShiftId = $data['pending_shift_id'];
            $pendingShift = ioc::getRepository('PendingShift')->find($pendingShiftId);
        } else {
            $pendingShift = ioc::resolve('PendingShift');
        }
        $notify = new notification();

        $nurse = ioc::getRepository('Nurse')->find($nurseId);

        $pendingShift->setNurse($nurse);
        $nurse->addPendingShift($pendingShift);
        $pendingShift->setNurseApproved($nurseApproved);
        $pendingShift->setProviderApproved($providerApproved);
        $shift = ioc::getRepository('Shift')->find($eventId);
        $pendingShift->setShift($shift);
        $shift->addPendingShift($pendingShift);
        app::$entityManager->persist($shift);
        try {
            app::$entityManager->persist($pendingShift);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Pending Shift saved successfully.');
        } catch (ValidateException|ORMException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />' . $e->getMessage());
        }


        $json = new Json();
        $json->data['url'] = app::get()->getRouter()->generate('sa_events_edit', ['id' => $eventId]);
        $json->data['success'] = true;
        return $json;
    }

    public function approveNurse($request)
    {
        $id = $request->getRouteParams()->get('id');
        $pendingShiftId = $request->getRouteParams()->get('pendingShiftId');
        $pendingShift = ioc::getRepository('PendingShift')->find($pendingShiftId);
        $pendingShift->setNurseApproved(True);
        $notify = new notification();
        try {
            app::$entityManager->persist($pendingShift);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Pending Shift approved on Nurse side successfully.');
        } catch (ValidateException|ORMException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />' . $e->getMessage());
        }
        return new Redirect(app::get()->getRouter()->generate('sa_events_edit', ['id' => $id]));
    }

    public function approveProvider($request)
    {
        $id = $request->getRouteParams()->get('id');
        $pendingShiftId = $request->getRouteParams()->get('pendingShiftId');
        $pendingShift = ioc::getRepository('PendingShift')->find($pendingShiftId);
        $pendingShift->setProviderApproved(True);
        $pendingShift = ioc::getRepository('PendingShift')->find($pendingShiftId);
        $notify = new notification();
        try {
            app::$entityManager->persist($pendingShift);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Pending Shift approved on Provider side successfully.');
        } catch (ValidateException|ORMException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />' . $e->getMessage());
        }

        return new Redirect(app::get()->getRouter()->generate('sa_events_edit', ['id' => $id]));
    }

    public function delete($request)
    {
        $id = $request->getRouteParams()->get('id');
        $pendingShiftId = $request->getRouteParams()->get('pendingShiftId');
        $pendingShift = ioc::getRepository('PendingShift')->find($pendingShiftId);
        $notify = new notification();

        try {
            app::$entityManager->remove($pendingShift);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Pending Shift deleted successfully.');
        } catch (ValidateException|ORMException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />' . $e->getMessage());
        }
        return new Redirect(app::get()->getRouter()->generate('sa_events_edit', ['id' => $id]));
    }

}