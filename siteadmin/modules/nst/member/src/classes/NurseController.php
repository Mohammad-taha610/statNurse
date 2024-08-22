<?php


namespace nst\member;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use nst\events\ShiftService;
use sacore\application\app;
use sacore\application\controller;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\ValidateException;
use sa\member\auth;
use sacore\utilities\notification;

class NurseController extends controller
{
    public function manageNurses($request): View
    {
        $view = new View('table', static::viewLocation());

        $fieldsToSearch = array();
        foreach ($request->query->all() as $field => $value) {
            if ($field == 'q_per_page') {
                $perPage = intval($value);
            } elseif (str_starts_with($field, 'q_') && !empty($value)) {
                $fieldsToSearch[str_replace('q_', '', $field)] = $value;
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;

        $defaultLimit = 20;

        [$nurses, $totalRecords, $totalPages] = ioc::getRepository('Nurse')->paginatedSearch($fieldsToSearch, $defaultLimit, $currentPage, $sort, $sortDir);
        foreach ($nurses as $nurse) {
            /** @var Nurse $nurse */
            $firstName = $nurse->getMember()->getFirstName();
            $lastName = $nurse->getMember()->getLastName();
            $dataSingle = ['id' => $nurse->getId(), 'first_name' => $firstName, 'last_name' => $lastName, 'date_created' => $nurse->getDateCreated()->format('m/d/Y')];
            $dataArray[] = $dataSingle;
        }

        $nurse_table = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header' => array(array('name' => 'First Name', 'class' => ''), array('name' => 'Last Name', 'class' => ''), array('name' => 'Date Created', 'class' => '', 'searchType' => 'date')),
            /* SET ACTIONS ON EVERY ROW */
            'actions' => array('view' => array('name' => 'Edit', 'routeid' => 'edit_nurse', 'params' => array('id')),
                'delete' => ['name' => 'Delete', 'routeid' => 'delete_nurse', 'params' => ['id']]),
            'tableCreateRoute' => 'create_nurse',
            /* SET THE NO DATA MESSAGE */
            'noDataMessage' => 'No Nurses in the system',
            /* SET THE DATA MAP */
            'map' => array('first_name', 'last_name', 'date_created'),
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => $dataArray,
            'searchable' => true,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $defaultLimit,
        );

        $view->data['table'][] = $nurse_table;

        return $view;
    }

    public function editNurse($request): View
    {
        $id = $request->getRouteParams()->get('id');
        if (is_null($id)) $id = 0;
        $view = new View('edit_nurse');
        $view->data['id'] = $id;
        if ($id > 0) {
            /** @var Nurse $nurse */
            $nurse = ioc::getRepository('Nurse')->find($id);

            $view->data['first_name'] = $nurse->getMember()->getFirstName();
            $view->data['last_name'] = $nurse->getMember()->getLastName();
            $view->data['is_active'] = $nurse->getIsActive();

        }
        if (!is_null($request->request->get('first_name'))) {
            $view->data['first_name'] = $request->request->get('first_name');
        }
        if (!is_null($request->request->get('last_name'))) {
            $view->data['last_name'] = $request->request->get('last_name');
        }
        if (!is_null($request->request->get('is_active'))) {
            $view->data['is_active'] = $request->request->get('is_active');
        }

        return $view;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws IocException
     * @throws IocDuplicateClassException
     */
    public function saveNurse($request): Redirect|View
    {
        $error = [];
        $id = $request->getRouteParams()->get('id');
        $job = ioc::getRepository('Job')->find(3);
        if (is_null($id)) $id = 0;
        $notify = new notification();

        if ($id > 0) {
            /** @var Nurse $nurse */
            $nurse = ioc::getRepository('Nurse')->find($id);
        } else {
            /** @var Nurse $nurse */
            $nurse = ioc::resolve('Nurse');
            $member = ioc::getRepository('saMember')->find(1);
            $nurse->setNurseNumber('1252');
            $member->setNurse($nurse);
        }

        if (!empty($error)) {
            $notify->addNotification('error', 'Error', 'Some fields are missing.');
            return new Redirect(app::get()->getRouter()->generate('manage_nurses'));
        }

        $nurse->setJob($job);

        try {
            app::$entityManager->persist($nurse);
            app::$entityManager->flush();


            if ($id > 0) {
                $notify->addNotification('success', 'Success', 'Nurse saved successfully.');
                return new Redirect(app::get()->getRouter()->generate('manage_nurses'));
            } else {
                $notify->addNotification('success', 'Success', 'Nurse created successfully.');
                return new Redirect(app::get()->getRouter()->generate('edit_nurse', ['id' => $nurse->getId()]));
            }
        } catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />' . $e->getMessage());
            // have to return this due to editMember returning new View obj.
            return $this->editNurse($request);
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function deleteNurse($request): Redirect|View
    {
        $id = $request->getRouteParams()->get('id');
        /** @var Nurse $nurse */
        $nurse = ioc::getRepository('kit')->find($id);

        $notify = new notification();

        try {
            app::$entityManager->remove($nurse);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Nurse deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('manage_nurses'));
        } catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while deleting this nurse. <br />' . $e->getMessage());
            return $this->editNurse($request);
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws ModRequestAuthenticationException
     */
    public function listNurses($request): View
    {
        $view = new View('nurse_search');

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $view->data['provider_id'] = $provider->getId();
        $view->data['search_term'] = $_GET['search_term'];
        return $view;
    }

    public static function getNursesForNurseList($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->getNursesForNurseList($data);
    }

    public function nurseProfile($request): View
    {
        $view = new View('nurse_profile');

        $view->data['id'] = $request->getRouteParams()->get('id');
        $view->data['providerNurseLoadFilesRoute'] = "provider.load_nurse_files";

        return $view;
    }

    public static function loadNurseProfileData($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->loadNurseProfileData($data);
    }

    public static function loadUpcomingNurseShifts($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->loadUpcomingNurseShifts($data);
    }

    public static function loadNursePastShifts($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->loadNursePastShifts($data);
    }

    /**
     * @throws OptimisticLockException
     * @throws IocException
     * @throws ORMException
     * @throws IocDuplicateClassException
     */
    public static function requestShift($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->requestShift($data);
    }

    public static function acceptShift($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->acceptShift($data);
    }

    public static function declineShift($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->declineShift($data);
    }

    public static function importNurses($data)
    {
        $nurseService = new NurseService();

        return $nurseService->importNurses($data);
    }

    public static function mergeDuplicateNurse($data)
    {
        $nurseService = new NurseService();

        return $nurseService->mergeDuplicateNurse($data);
    }

    public static function search($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->search($data);
    }

    public static function getMetaData($data): array
    {
        $nurseService = new NurseService();

        return $nurseService->getMetaData($data);
    }

    public static function mergeShifts($data){
        $shiftService = new ShiftService();

        $response = $shiftService->mergeShifts($data);

        return $response;
    }

    public static function mergeNurseData($data){
        $nurseService = new NurseService();

        $response = $nurseService->mergeNurseData($data);

        return $response;
    }

    public static function nurseDeactivate($nurseId){
        $nurseService = new NurseService();

        $response = $nurseService->nurseDeactivate($nurseId);

        return $response;
    }

}
