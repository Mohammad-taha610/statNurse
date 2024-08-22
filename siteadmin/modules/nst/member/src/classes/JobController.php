<?php


namespace nst\member;

use sacore\application\app;
use sacore\application\saController;
use sacore\application\ioc;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\utilities\notification;

class JobController extends saController{

    public function manageJobs($request){
        $view = new View('table', static::viewLocation());

        $fieldsToSearch = array();
        foreach($request->query->all() as $field=>$value)
        {
            if($field == 'q_per_page'){
                $perPage = intval($value);
            }
            elseif (strpos($field, 'q_')===0 && !empty($value))
            {
                $fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;

        $defaultLimit = 20;

        [$jobs, $totalRecords, $totalPages] = ioc::getRepository('Job')->paginatedSearch($fieldsToSearch, $defaultLimit, $currentPage, $sort, $sortDir);
        foreach ($jobs as $job) {
            $dataSingle = ['id' => $job->getId(), 'title' => $job->getTitle()];
            $dataArray[] = $dataSingle;
        }

        $job_table = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header'=>array(array('name'=>'Title', 'class'=>'')),
            /* SET ACTIONS ON EVERY ROW */
            'actions'=>array('view'=>array('name'=>'Edit', 'routeid'=>'edit_job', 'params'=>array('id')),
                'delete' => ['name'=>'Delete', 'routeid' => 'delete_job', 'params'=> ['id']]),
            'tableCreateRoute' => 'create_job',
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No Jobs in the system',
            /* SET THE DATA MAP */
            'map'=>array('title'),
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data'=>  $dataArray,
            'searchable' => true,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords'=> $totalRecords,
            'totalPages'=> $totalPages,
            'currentPage'=> $currentPage,
            'perPage'=> $defaultLimit,
        );

        $view->data['table'][] = $job_table;

        return $view;
    }

    public function editJob($request){
        $id = $request->getRouteParams()->get('id');
        if(is_null($id)) $id=0;
        $view = new View('edit_job');
        $view->data['id'] = $id;
        if($id>0){
            /** @var Job $job */
            $job = ioc::getRepository('Job')->find($id);

            $view->data['title'] = $job->getTitle();

        }
        if(!is_null($request->request->get('title'))) {
            $view->data['title'] = $request->request->get('title');
        }

        return $view;
    }

    public function saveJob($request){
        $error = [];
        $id = $request->getRouteParams()->get('id');
        if(is_null($id)) $id = 0;
        $notify = new notification();

        if ($id>0) {
            /** @var Job $job */
            $job = ioc::getRepository('Job')->find($id);
        }
        else {
            /** @var Job $job */
            $job = ioc::resolve('Job');
        }

        if(empty($request->request->all()['title'])){
            $error[] = "Please enter job name";
        }
        if(!empty($error)){
            $notify->addNotification('error', 'Error', 'Some fields are missing.');
            return new Redirect(app::get()->getRouter()->generate( 'manage_jobs'));
        }

        $job->setTitle($request->request->get('title'));

        try {
            app::$entityManager->persist($job);
            app::$entityManager->flush();


            if ($id>0) {
                $notify->addNotification('success', 'Success', 'Job saved successfully.');
                return new Redirect(app::get()->getRouter()->generate( 'manage_jobs'));
            } else {
                $notify->addNotification('success', 'Success', 'Job created successfully.');
                return new Redirect(app::get()->getRouter()->generate( 'edit_job', ['id'=>$job->getId()]));
            }
        }
        catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage() );
            // have to return this due to editMember returning new View obj.
            return $this->editJob($request);
        }
    }

    public function deleteJob($request){
        $id = $request->getRouteParams()->get('id');
        /** @var Job $job */
        $job = ioc::getRepository('Job')->find($id);

        $notify = new notification();

        try {
            app::$entityManager->remove($job);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Job deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('manage_jobs'));
        }
        catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while deleting this nurse. <br />'. $e->getMessage());
            return $this->editJob($request);
        }
    }

}