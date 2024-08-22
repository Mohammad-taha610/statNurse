<?php

namespace sa\messages;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\TransactionRequiredException;
use \sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Request;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use \sacore\application\saController;
use sa\system\saUser;
use sacore\utilities\doctrineUtils;
use \sacore\utilities\url;
use \sacore\utilities\notification;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class saMessagesController
 * @package sa\messages
 */
class saMessagesController extends saController {

	public function manageEmails($request) {
		$view = new View('table', $this->viewLocation());

		$perPage = 100;
		$fieldsToSearch = [];

		foreach($request->query->all() as $field=>$value) {
			if (strpos($field, 'q_') === 0 && !empty($value)) {
				$fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
			}
		}
		$currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
		$sort = !empty($request->get('sort')) ? $request->get('sort') : 'date_created';
		$sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : 'DESC';

        $offset = (($currentPage-1)*$perPage);

        $headerActions = [];

        /** @var saUser $saUser */
        $saUser = modRequest::request('sa.user');

        if($saUser->getUserType() == saUser::TYPE_DEVELOPER) {
            $headerActions[] = [
                'icon' => ' fas fa-check',
                'name' => 'Acknowledge All',
                'routeid' => 'messages_central_acknowledge_all',
                'showText' => true
            ];
        }

        $totalRecords = ioc::getRepository('saEmail')->getEmails(true,$perPage,$offset,$sort,$sortDir,$fieldsToSearch);
        $data = ioc::getRepository('saEmail')->getEmails(false,$perPage,$offset,$sort,$sortDir,$fieldsToSearch);

		$totalPages = ceil($totalRecords / $perPage);
		$view->data['table'][] = array(
			/* SET THE HEADER OF THE TABLE UP */
			'header'=>array( array('name'=>'To', 'class'=>''), array('name'=>'Subject', 'class'=>''),array('name'=>'Attempted Send', 'class'=>'', 'type'=>'boolean', 'searchable'=>false),array('name'=>'Was Sent', 'class'=>'', 'type'=>'boolean', 'searchable'=>false), array('name'=>'Sent time', 'class'=>'', 'searchable'=>false) ),
			/* SET ACTIONS ON EVERY ROW */
			'actions'=>array( 'resend'=>array( 'name'=>'Resend', 'showText' => true, 'routeid'=>'sa_emails_resend', 'params'=>array('id'), 'icon' => 'refresh' ), 'edit'=>array( 'name'=>'Edit', 'routeid'=>'sa_emails_edit', 'params'=>array('id') ), 'delete'=>array( 'name'=>'Delete', 'routeid'=>'sa_emails_delete', 'params'=>array('id') ), ),
			/* SET THE NO DATA MESSAGE */
			'noDataMessage'=>'No Emails Available',
			/* SET THE DATA MAP */
			'map'=>array( 'to_address', 'subject', 'attempted_send','success','date_attempted_send', ),
			/* SET THE ACTION FOR THE HEADER CREATE BUTTON */
			/* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
			'data'=> $data,
			/* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
			'totalRecords'=> $totalRecords,
			'totalPages'=> $totalPages,
			'currentPage'=> $currentPage,
			'perPage'=> $perPage,
			'searchable'=> true,
            'headerActions' => $headerActions
		);

		return $view;
	}

    public function deleteEmails($request) {
	    $id = $request->getRouteParams()->get('id');
        $obj = app::$entityManager->find( ioc::staticResolve('saEmail'), $id);

        app::$entityManager->remove( $obj );
        app::$entityManager->flush();

        $notify = new notification();
        $notify->addNotification('success', 'Success', 'Deleted Successfully.' );

        return new Redirect(app::get()->getRouter()->generate('sa_emails'));
    }

    public function createEmails($request) {
	    $request = new Request();
	    $request->setRouteParams(new ParameterBag(['id'=>0]));
	    return $this->editEmails($request);
    }

	public function editEmails($request) {
	    $id = $request->getRouteParams()->get('id');
		$view = new View('dbform', static::viewLocation());
		$view->data['id'] = $id;

		if ($id>0) {
            $mData = doctrineUtils::getEntityArray( app::$entityManager->find( ioc::staticResolve('saEmail'), $id ) );
			$view->data = array_merge($view->data, $mData);
			$view->addXssSanitationExclude('body');
			$view->addXssSanitationExclude('response');
		}

		if ($request->request)
			$view->data = array_merge($view->data, $request->query->all());

        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve('saEmail'));
		$view->data['dbform'][] = array( 

			/* SET THE HEADER OF THE TABLE UP */
			'columns'=> $metaData->fieldMappings,
			'form'=> true,
			'useInputFields'=> false,
			'typeOverrides' => array('body'=>'iframe', 'response'=>'pre'),
			'saveRouteId'=> 'sa_emails_save',
            'saveRouteParams' => ['id'],
			'exclude'=> array('id')
		);

		return $view;
	}

	public function saveEmails($request){
	    $id = $request->getRouteParams()->get('id');

        $notify = new notification();

	    if($id>0){
            $email = app::$entityManager->getRepository( ioc::staticResolve('saEmail') )->find($id);
        }
	    else{
	        $email = ioc::resolve('saEmail');
        }

	    $email = doctrineUtils::setEntityData($request->request->all(), $email);
        app::$entityManager->persist($email);
        app::$entityManager->flush();

        $notify->addNotification('success', 'Success', 'File saved successfully.');
        $redirect = new Redirect(app::get()->getRouter()->generate('sa_emails',['id'=>$email->getId()]));
        return $redirect;
    }

    /**
     * @throws Exception
     */
	public function manageSMS($request)
    {
		$view = new View('table', static::viewLocation());

		$perPage = 20;
		$fieldsToSearch = [];

		foreach($request->query->all() as $field=>$value)  {
			if (strpos($field, 'q_')===0 && !empty($value)) {
				$fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
			}
		}

		$currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
		$sort = !empty($request->get('sort')) ? $request->get('sort') : 'date_created';
		$sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : 'DESC';

		if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalRecords = app::$entityManager->getRepository( ioc::staticResolve( 'saSMS' ) )->search($fieldsToSearch, null, null, null, true);
        $data = app::$entityManager->getRepository( ioc::staticResolve( 'saSMS' ) )->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));

        $totalPages = ceil($totalRecords / $perPage);

		$view->data['table'][] = array(
			/* SET THE HEADER OF THE TABLE UP */
			'header'=>array(
			    array('name' => 'To', 'class' => ''),
                array('name'=>'Attempted Send', 'class'=>'', 'type'=>'boolean', 'searchable'=>false),array('name'=>'Status', 'class'=>'', 'searchable'=>false), array('name'=>'Sent time', 'class'=>'', 'searchable'=>false) ),
			/* SET ACTIONS ON EVERY ROW */
			'actions'=>array( 'edit'=>array( 'name'=>'Edit', 'routeid'=>'sa_sms_edit', 'params'=>array('id') ), 'delete'=>array( 'name'=>'Delete', 'routeid'=>'sa_sms_delete', 'params'=>array('id') ), ),
			/* SET THE NO DATA MESSAGE */
			'noDataMessage'=>'No SMS Messages Available',
			/* SET THE DATA MAP */
			'map' => array( 'to_address','attempted_send','status','date_attempted_send', ),
			/* SET THE ACTION FOR THE HEADER CREATE BUTTON */
			/* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
			'data' => doctrineUtils::convertEntityToArray($data),
			/* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
			'totalRecords' => $totalRecords,
			'totalPages' => $totalPages,
			'currentPage' => $currentPage,
			'perPage' => $perPage,
			'searchable' => true,
		);

		return $view;
	}

    public function createSms($request)
    {
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => 0]));
        return $this->editSms($request);
    }

	public function editSms($request,  $passData = false) {
//	    var_export($request);
	    //Should probably check id
	    $id = ($request->getRouteParams()->get('id'))?$request->getRouteParams()->get('id'):0;


		$view = new View('dbform', static::viewLocation());
		$view->data['id'] = $id;
		if ($id>0) {
            $mData = doctrineUtils::convertEntityToArray( app::$entityManager->find( ioc::staticResolve('saSMS'), $id ) );
			$view->data = array_merge($view->data, $mData);
		}

		if ($request->query) {
            $view->data = array_merge($view->data, $request->query->all());
        }

        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve('saSMS'));
        $view->data['dbform'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'columns'=> $metaData->fieldMappings,
            'form'=> true,
            'useInputFields'=> false,
            'typeOverrides' => array('body'=>'iframe', 'response'=>'pre'),
            'saveRouteId'=> 'sa_sms_save',
            'exclude'=> array(),
            // key and value are swapped here, quite unconventionally, to work with the core-view 'dbform.php' 
            'saveRouteParams' => array( $id => 'id')
        );

		return $view;
	}

    public function deleteSms($request)
    {
        $id = $request->getRouteParams()->get('id');
        $obj = app::$entityManager->find( ioc::staticResolve('saSMS'), $id);

        app::$entityManager->remove( $obj );
        app::$entityManager->flush();

        $notify = new notification();
        $notify->addNotification('success', 'Success', 'Deleted Successfully.' );

        return new Redirect(app::get()->getRouter()->generate('sa_sms'));
    }

    public function saveSms($request){
        $id = $request->getRouteParams()->get('id');

        $notify = new notification();

        if($id>0){
            $sms = app::$entityManager->getRepository( ioc::staticResolve('saSMS') )->find($id);
        }
        else{
            $sms = ioc::resolve('saSMS');
        }

        $sms = doctrineUtils::setEntityData($request->request->all(), $sms);
        app::$entityManager->persist($sms);
        app::$entityManager->flush();

        $notify->addNotification('success', 'Success', 'File saved successfully.');
        $redirect = new Redirect(app::get()->getRouter()->generate('sa_sms',['id'=>$sms->getId()]));
        return $redirect;
    }


	public function manageVoice($request)
    {
		$view = new View('table', static::viewLocation());

		$perPage = 20;
		$fieldsToSearch=array();

		foreach($request->query->all() as $field=>$value) {
			if (strpos($field, 'q_')===0 && !empty($value)) {
				$fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
			}
		}

		$currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
		$sort = !empty($request->get('sort')) ? $request->get('sort') : 'date_created';
		$sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : 'DESC';

		if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalRecords = count(app::$entityManager->getRepository( ioc::staticResolve( 'saVoice' ) )->search($fieldsToSearch));
        $data = app::$entityManager->getRepository( ioc::staticResolve( 'saVoice' ) )->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));

        $totalPages = ceil($totalRecords / $perPage);

		$view->data['table'][] = array( 

			/* SET THE HEADER OF THE TABLE UP */
			'header'=>array( array('name'=>'To', 'class'=>''),array('name'=>'Attempted Send', 'class'=>'', 'type'=>'boolean', 'searchable'=>false),array('name'=>'Status', 'class'=>'', 'searchable'=>false), array('name'=>'Sent time', 'class'=>'', 'searchable'=>false) ),
			/* SET ACTIONS ON EVERY ROW */
			'actions'=>array( 'edit'=>array( 'name'=>'Edit', 'routeid'=>'sa_voice_edit', 'params'=>array('id') ), 'delete'=>array( 'name'=>'Delete', 'routeid'=>'sa_voice_delete', 'params'=>array('id') ), ),
			/* SET THE NO DATA MESSAGE */
			'noDataMessage'=>'No Voice Messages Available',
			/* SET THE DATA MAP */
			'map'=>array( 'to_address','attempted_send','status','date_attempted_send', ),
			/* SET THE ACTION FOR THE HEADER CREATE BUTTON */

			/* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
			'data'=> doctrineUtils::convertEntityToArray($data),
			/* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
			'totalRecords'=> $totalRecords,
			'totalPages'=> $totalPages,
			'currentPage'=> $currentPage,
			'perPage'=> $perPage,
			'searchable'=> true,
		);

        return $view;
	}

    public function deleteVoice($request)
    {
        $id = $request->getRouteParams()->get('id');
        $obj = app::$entityManager->find( ioc::staticResolve('saVoice'), $id);

        app::$entityManager->remove( $obj );
        app::$entityManager->flush();

        $notify = new notification();
        $notify->addNotification('success', 'Success', 'Deleted Successfully.' );

        return new Redirect(app::get()->getRouter()->generate('sa_voice'));
    }

    public function createVoice($request){
	    $request = new Request();
	    $request->setRouteParams(new ParameterBag(['id'=>0]));
	    return $this->editVoice($request);
    }

	public function editVoice($request)
    {
        $id=$request->getRouteParams()->get('id');
        $view = new View('dbform', static::viewLocation());
		$view->data['id'] = $id;

		if ($id) {
            $mData = doctrineUtils::convertEntityToArray( app::$entityManager->find( ioc::staticResolve('saVoice'), $id ) );
			$view->data = array_merge($view->data, $mData);
		}

		if ($request->request->all()) {
            $view->data = array_merge($view->data, $request->request->all());
        }

        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve('saVoice'));
        $view->data['dbform'][] = array(

            /* SET THE HEADER OF THE TABLE UP */
            'columns'=> $metaData->fieldMappings,
            'form'=> true,
            'useInputFields'=> false,
            'typeOverrides' => array('body'=>'iframe', 'response'=>'pre'),
            'saveRouteId'=> 'sa_voice_save',
            'exclude'=> array()
        );

		return $view;
	}

    public function saveVoice($request){
        $id = $request->getRouteParams()->get('id');

        $notify = new notification();

        if($id>0){
            $voice = app::$entityManager->getRepository( ioc::staticResolve('saVoice') )->find($id);
        }
        else{
            $voice = ioc::resolve('saVoice');
        }

        $voice = doctrineUtils::setEntityData($request->request->all(), $voice);
        app::$entityManager->persist($voice);
        app::$entityManager->flush();

        $notify->addNotification('success', 'Success', 'File saved successfully.');
        $redirect = new Redirect(app::get()->getRouter()->generate('sa_voice',['id'=>$voice->getId()]));
        return $redirect;
    }


    /**
     * @param saEmail $email
     * @param $email
     * @return Redirect
     * @throws \Exception
     */
    public function resendEmail($request)
    {

        $email = ioc::getRepository('saEmail')->find($request->getRouteParams()->get('id'));
	    app::$entityManager->detach($email);
	    
	    $email->setAttemptedSend(false);
	    $email->setDateAttemptedSend(null);
	    $email->setResponse(null);
	    $email->setDateCreated(new DateTime());
	    $email->setBatchId(null);
        $email->send();
        
        $notify = new notification();
        $notify->addNotification('success', 'Success', 'Successfully queued email to resend.');

        return new Redirect(app::get()->getRouter()->generate('sa_emails'));
    }

    public function acknowledgeAll()
    {
        $q = app::$entityManager->createQueryBuilder()
            ->update(ioc::staticGet('saEmail'), 'email')
            ->set('email.central_acknowledged', true);

        $q->getQuery()->execute();

        $notify = new notification();
        $notify->addNotification('success', 'Success', 'All emails were successfully acknowledged');

        return new Redirect(app::get()->getRouter()->generate('sa_emails'));
    }
}
