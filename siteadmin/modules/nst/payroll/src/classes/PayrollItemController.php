<?php


namespace nst\payroll;

use nst\member\Nurse;
use nst\member\Provider;
use sacore\application\app;
use sacore\application\saController;
use sacore\application\responses\View;
use sacore\application\ioc;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;

class PayrollItemController extends saController{

    public function managePayrollItems($request){
        $view = new View('table');

        $fieldsToSearch = array();
        foreach($request->query->all() as $field=>$value)
        {
            if($field == 'q_per_page'){
                $perPage = intval($value);
            }
            elseif (strpos($field, 'q_')===0 && !empty($value)) {
                $fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;

        $defaultLimit = 20;

        [$payrollItems, $totalRecords, $totalPages] = ioc::getRepository('PayrollItem')->paginatedSearch($fieldsToSearch, $defaultLimit, $currentPage, $sort, $sortDir);
        foreach ($payrollItems as $payrollItem) {
            /** @var PayrollItem $payrollItem */
            $dataSingle = ['id' => $payrollItem->getId(), 'type' => $payrollItem->getType(),
                'description' => $payrollItem->getDescription(), 'amount' => $payrollItem->getAmount(),
                'status' => $payrollItem->getStatus(), 'bonus' => $payrollItem->getBonus(), 'approved' => $payrollItem->getApproved()];
            $dataArray[] = $dataSingle;
        }

        $provider_table = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header'=>array(array('name'=>'Type', 'class'=>''),array('name'=>'Description', 'class'=>''),
                array('name'=>'Amount', 'class'=>''), array('name'=>'Status', 'class'=>''), array('name'=>'Approved', 'class'=>''),
                array('name'=>'Bonus', 'class'=>'')),
            /* SET ACTIONS ON EVERY ROW */
            'actions'=>array('view'=>array('name'=>'Edit', 'routeid'=>'edit_payroll_item', 'params'=>array('id')),
                'delete' => ['name'=>'Delete', 'routeid' => 'delete_payroll_item', 'params'=> ['id']]),
            'tableCreateRoute' => 'create_payroll_item',
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No payrolls in the system',
            /* SET THE DATA MAP */
            'map'=>array('type','description','amount', 'status', 'approved', 'bonus'),
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

    public function editPayrollItem($request){
        $id = $request->getRouteParams()->get('id');
        if(is_null($id)) $id=0;
        $view = new View('edit_payroll_item');
        $view->data['id'] = $id;
        return $view;
    }

    public function loadVue($data){
        $payrollItemId = $data['payrollItemId'];
        $payrollId = $data['payrollId'];
        $json = new Json();

        $payrollFieldsToSearch = [];
//        $scheduledShiftFieldsToSearch = ['name' => ""];

        $itemsPerPage = 100;

        $payrolls = ioc::getRepository('Payroll')->search($payrollFieldsToSearch, 'DESC', $itemsPerPage);
//        $kits = ioc::getRepository('Shift')->search($kitFieldsToSearch, 'DESC', $itemsPerPage);

        $payrollArray = [];
        foreach ($payrolls as $payroll){
            /** @var Payroll $payroll*/
            $payrollArray[] = ['id' => $payroll->getId(), 'name' => $payroll->getUniqueDescriptor()];
        }


        $json->data['payrollOptions'] = $payrollArray;
//        $json->data['shiftOptions'] = $shiftArray;
        if($payrollId>0){
            $payroll = ioc::getRepository('Payroll')->find($payrollId);
            $name = $payroll->getUniqueDescriptor();
            $json->data['payroll'] = ['id' => $payroll->getId(), 'name' => $name];
        }

        //Shift info potentially
//        if($materialId>0){
//            $payroll = ioc::getRepository('material')->find($payroll);
//            $json->data['material'] = ['id' => $payroll->getId(), 'name' => $payroll->getDescription()];
//        }
        if($payrollItemId>0) {
            /** @var PayrollItem $payrollItem */
            $payrollItem = ioc::getRepository('PayrollItem')->find($payrollItemId);
            $json->data['type'] = $payrollItem->getType();
            $json->data['description'] = $payrollItem->getDescription();
            $json->data['approved'] = $payrollItem->getApproved();
            $json->data['bonus'] = $payrollItem->getBonus();

            $payroll = $payrollItem->getPayroll();
            if($payroll) {
                $json->data['payroll'] = ['id' => $payroll->getId(), 'name' => $payroll->getUniqueDescriptor()];
            }
            //Shift information goes here
//            $payroll = $payrollItem->getMaterial();
//            $json->data['material'] = ['id' => $payroll->getId(), 'name' => $payroll->getName()];
        }

        $json->data['success'] = true;
        return $json;
    }

    public function loadVuePayroll($data){
        //Not entirely sure yet what this search will be on
    }

    public function loadVueScheduledShift($data){
        //Not entirely sure yet what this search will be on
    }

    public function savePayrollItem($request){
        $error = [];
        $id = $request->getRouteParams()->get('id');
        if(is_null($id)) $id = 0;
        $notify = new notification();

        if ($id>0) {
            /** @var Provider $payrollItem */
            $payrollItem = ioc::getRepository('PayrollItem')->find($id);
        }
        else {
            /** @var Provider $provider */
            $payrollItem = ioc::resolve('PayrollItem');
            $payrollItem->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
        }

        if(empty($request->request->all()['name'])){
            $error[] = "Please enter Payroll Item name";
        }
        if(!empty($error)){
            $notify->addNotification('error', 'Error', 'Some fields are missing.');
            return new Redirect(app::get()->getRouter()->generate( 'manage_payroll_items'));
        }

        if(empty($request->request->all()['is_active'])) {
            $is_active = 0;
        }else{
            $is_active = 1;
        }

        $payrollItem->setIsActive($is_active);
        $payrollItem->getMember()->setFirstName($request->request->all()['name']);

        try {
            app::$entityManager->persist($payrollItem);
            app::$entityManager->flush();


            if ($id>0) {
                $notify->addNotification('success', 'Success', 'Payroll Item saved successfully.');
                return new Redirect(app::get()->getRouter()->generate( 'manage_payroll_items'));
            } else {
                $notify->addNotification('success', 'Success', 'Payroll Item created successfully.');
                return new Redirect(app::get()->getRouter()->generate( 'edit_payroll_item', ['id'=>$payrollItem->getId()]));
            }
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage() );
            // have to return this due to editMember returning new View obj.
            return $this->editPayrollItem($request);
        }
    }

    public function deletePayrollItem($request){
        $id = $request->getRouteParams()->get('id');
        /** @var Nurse $nurse */
        $nurse = ioc::getRepository('danger')->find($id);

        $notify = new notification();


        try {
            app::$entityManager->remove($nurse);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Payroll Item deleted successfully.');
            return new Redirect(app::get()->getRouter()->generate('manage_payroll_item'));
        }
        catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while deleting this nurse. <br />'. $e->getMessage());
            return $this->editPayroll($request);
        }
    }

}