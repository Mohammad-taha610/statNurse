<?php

namespace sa\messages;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\Json;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\utilities\doctrineUtils;

class saPushNotificationController extends saController {
    
    public function index($request) {
        $view = new View( 'table', $this->viewLocation());

        $perPage = 20;
        $fieldsToSearch=array();

        foreach($request->query->all() as $field=>$value) {
            if (strpos($field, 'q_')===0 && !empty($value)) {
                $fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;

        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }
        
        /** @var pushNotificationRepository $pushNotificationRepo */
        $pushNotificationRepo = ioc::getRepository('PushNotification');

        $totalRecords = $pushNotificationRepo->search($fieldsToSearch, null, null, null, true);
        $data = $pushNotificationRepo->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));
        $totalPages = ceil($totalRecords / $perPage);

        $view->data['table'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header' => array(
                array('name' => 'Title', 'class' => ''),
                array('name' => 'Date Created', 'class' => ''), 
                array('name' => 'Attempted Send', 'class' => '', 'type' => 'boolean'), 
                array('name' => 'Date Attempted Send', 'class' => ''), 
                array('name' => 'Success', 'class' => '', 'type' => 'boolean'),
            ),
            /* SET ACTIONS ON EVERY ROW */
            'actions' => array(
                'view' => array(
                    'name' => 'View',
                    'routeid' => 'sa_notification_view',
                    'params' => array('id'),
                    'icon' => 'fa fa-refresh'
                )
            ),
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No Forms Available',
            /* SET THE DATA MAP */
            'map'=>array('title', 'date_created', 'attempted_send', 'date_attempted_send', 'success'),
            /* IS THE TABLE SEARCHABLE? */
            'searchable'=> false,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => doctrineUtils::getEntityCollectionArray($data),
            "dataRowCallback"=> function($data) {
                $data['date_created'] = $data['date_created'] ? $data['date_created']->format('m/d/Y g:i a') : 'N/A';
                $data['date_attempted_send'] = $data['date_attempted_send'] ? $data['date_attempted_send']->format('m/d/Y g:i a') : 'N/A';
                return $data;
            }
        );
        
        return $view;
    }
    
    public function show($request) {
        $id = $request->getRouteParams()->get('id');
        $view = new View( 'dbform', $this->viewLocation());
        $view->data['id'] = $id;

        if ($id) {
            $mData = doctrineUtils::getEntityArray(
                ioc::getRepository('PushNotification')->findOneBy(array('id' => $id))
            );
            
            $view->data = array_merge($view->data, $mData);
            $view->addXssSanitationExclude('response');
        }

        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve('PushNotification'));
        $view->data['dbform'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'columns'=> $metaData->fieldMappings,
            'form'=> true,
            'useInputFields'=> false,
            'typeOverrides' => array('body'=>'iframe', 'response'=>'pre'),
            'saveRouteId'=> 'sa_emails_save',
            'exclude'=> array(),
            // key and value are swapped here, quite unconventionally, to work with the core view dbform.php
            'saveRouteParams' => array( $id => 'id')
        );

        return $view;
    }
    
    public function sendNotificationView() {
        $view = new View('push_notification_send', static::viewLocation());
        $view->data['hasBeenConfigured'] = $this->hasBeenConfigured();
        
        return $view;
    }
    
    public static function queueNotification($data) {
        $title = $data['title'];
        $message = $data['message'];
        
        $response = new Json();
        $response->data['success'] = true;

        if(empty($title) || empty($message)) {
            $response->data['success'] = false;
            return $response;
        }
        
        try {
            modRequest::request('messages.startPushNotificationBatch');

            $batchSize = 100;
            $i = 0;
            $tokens = ioc::getRepository('PushToken')->createQueryBuilder('token')->getQuery()->iterate();

            foreach($tokens as $row) {
                /** @var PushToken $token */
                $token = $row[0];
                
                modRequest::request('messages.sendPushNotification', array(
                    'title' => $title,
                    'message' => $message,
                    'token' => $token->getToken()
                ));
                
                if(($i % $batchSize) === 0) {
                    app::$entityManager->clear();
                }
                
                ++$i;
            }
            
            app::$entityManager->flush();

            modRequest::request('messages.commitPushNotificationBatch');
        } catch(\Exception $e) {
            $response->data['success'] = false;
        }
        
        return $response;
    }
    
    private function hasBeenConfigured() {
        $serverKey = app::get()->getConfiguration()->get('fcm_server_key')->getValue();
        
        return $serverKey ? true : false;
    }
    
}
