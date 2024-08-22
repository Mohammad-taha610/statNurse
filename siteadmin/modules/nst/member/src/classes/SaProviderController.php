<?php


namespace nst\member;


use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sacore\utilities\notification;

class SaProviderController extends saController
{
    public function manageProviders($request){
        $view = new View('table');

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

        [$providers, $totalRecords, $totalPages] = ioc::getRepository('Provider')->paginatedSearch($fieldsToSearch, $defaultLimit, $currentPage, $sort, $sortDir);
        foreach ($providers as $provider) {
            /** @var Provider $provider */
            $name = $provider->getFirstName();
            $dataSingle = ['id' => $provider->getId(), 'name' => $name, 'date_created' => $provider->getDateCreated()->format('m/d/Y') ];
            $dataArray[] = $dataSingle;
        }

        $provider_table = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header'=>array(array('name'=>'Name', 'class'=>''), array('name'=>'Date Created', 'class'=>'', 'searchType' => 'date')),
            /* SET ACTIONS ON EVERY ROW */
            'actions'=>array('view'=>array('name'=>'Edit', 'routeid'=>'edit_provider', 'params'=>array('id')),
                'delete' => ['name'=>'Delete', 'routeid' => 'delete_provider', 'params'=> ['id']]),
            'tableCreateRoute' => 'create_provider',
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No provider in the system',
            /* SET THE DATA MAP */
            'map'=>array('name','date_created'),
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data'=>  $dataArray,
            'searchable' => true,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords'=> $totalRecords,
            'totalPages'=> $totalPages,
            'currentPage'=> $currentPage,
            'perPage'=> $defaultLimit,
        );

        $view->data['table'][] = $provider_table;

        return $view;
    }

    public function editProvider($request){
        $id = $request->getRouteParams()->get('id');
        if(is_null($id)) $id=0;
        $view = new View('edit_provider');
        $view->data['id'] = $id;
        if($id>0){
            /** @var Provider $provider */
            $provider = ioc::getRepository('Provider')->find($id);

            $view->data['name'] = $provider->getFirstName();
            $view->data['is_active'] = $provider->getIsActive();

        }
        if(!is_null($request->request->get('name'))) {
            $view->data['name'] = $request->request->get('name');
        }
        if(!is_null($request->request->get('is_active'))) {
            $view->data['is_active'] = $request->request->get('is_active');
        }

        return $view;
    }

    public function saveProvider($request){
        $error = [];
        $id = $request->getRouteParams()->get('id');
        if(is_null($id)) $id = 0;
        $notify = new notification();

        if ($id>0) {
            /** @var Provider $provider */
            $provider = ioc::getRepository('Provider')->find($id);
        }
        else {
            /** @var Provider $provider */
            $provider = ioc::resolve('Provider');
            $provider->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
        }

        $provider->setFirstName($request->request->all()['first_name']);
        $provider->setLastName($request->request->all()['last_name']);

        if(empty($request->request->all()['is_active']))
        {
            $is_active = 0;
        }else{
            $is_active = 1;
        }

        $provider->setIsActive($is_active);

        try {
            app::$entityManager->persist($provider);
            app::$entityManager->flush();


            if ($id>0) {
                $notify->addNotification('success', 'Success', 'Provider saved successfully.');
                return new Redirect(app::get()->getRouter()->generate( 'manage_providers'));
            } else {
                $notify->addNotification('success', 'Success', 'Provider created successfully.');
                return new Redirect(app::get()->getRouter()->generate( 'edit_provider', ['id'=>$provider->getId()]));
            }
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage() );
            // have to return this due to editMember returning new View obj.
            return $this->manageProviders($request);
        }
    }

    public function deleteProvider($request){
        $id = $request->getRouteParams()->get('id');
        /** @var Nurse $nurse */
        $nurse = ioc::getRepository('danger')->find($id);

        $notify = new notification();


        try {
            app::$entityManager->remove($nurse);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Nurse deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('manage_nurses'));
        }
        catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while deleting this nurse. <br />'. $e->getMessage());
            return $this->editNurse($request);
        }
    }


}