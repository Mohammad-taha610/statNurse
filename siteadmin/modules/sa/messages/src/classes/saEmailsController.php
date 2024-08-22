<?php
namespace sa\messages;

use \sacore\application\app;
use sacore\application\ioc;
use \sacore\application\modelResult;
use sacore\application\modRequest;
use \sacore\application\navItem;
use \sacore\application\saController;
use \sacore\application\saRoute;
use sacore\application\ValidateException;
use \sacore\application\responses\View;
use sa\system\saUser;
use \sacore\utilities\notification;
use \sacore\utilities\url;

class saEmailsController extends saController 
{
    public function manageEmails() 
    {
        $saEmail = ioc::staticResolve('saEmail');
        $repo = app::$entityManager->getRepository($saEmail);

        $view = new \sacore\application\Responses\View('master', 'table', static::viewLocation());

        $perPage = 20;
        $fieldsToSearch=array();

        foreach ($_GET as $field=>$value) {
            if (strpos($field, 'q_')===0 && !empty($value)) {
                $fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
            }
        }

        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : false;
        $sortDir = !empty($_REQUEST['sortDir']) ? $_REQUEST['sortDir'] : false;

        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort=>$sortDir);
        }

        $data = $repo->findBy($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));
        $totalRecords = count($repo->findBy($fieldsToSearch));
        $totalPages = ceil($totalRecords / $perPage);

        $view->data['table'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header' => array(
                array('name' => 'To', 'class'=>''),
                array('name' => 'Attempted Send', 'class' => '', 'type' => 'boolean', 'searchable' => false),
                array('name' => 'Was Sent', 'class' => '', 'type' => 'boolean', 'searchable' => false),
                array('name' => 'Sent time', 'class' => '', 'searchable' => false)),
            /* SET ACTIONS ON EVERY ROW */
            'actions' => array(
                'edit' => array('name' => 'Edit', 'routeid' => 'sa_emails_edit', 'params' => array('id') ),
                'delete'=>array('name' => 'Delete', 'routeid' => 'sa_emails_delete', 'params' => array('id'))
            ),
            /* SET THE NO DATA MESSAGE */
            'noDataMessage' => 'No Emails Available',
            /* SET THE DATA MAP */
            'map' => array('to_address', 'attempted_send', 'success', 'date_attempted_send'),
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            //'tableCreateRoute'=>'sa_emails_create',
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => $data,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'searchable' => true,
            'headerActions'=> $headerActions
        );

        $view->display();
    }

    public function editEmails($id=0, $passData=false) 
    {
        $view = new View('dbform', $this->viewLocation(), false);
        $view->data['id'] = $id;

        $saEmail = ioc::staticResolve('saEmail');
        $email = app::$entityManager->find($saEmail, $id)->toArray();
        $view->data = array_merge($view->data, $email);
        
        $metaData = app::$entityManager->getClassMetadata($saEmail);

        $view->data['dbform'][] = array( 
            /* SET THE HEADER OF THE TABLE UP */
            'columns'=> $metaData->fieldMappings,
            'form'=> true,
            'useInputFields'=> false,
            'typeOverrides' => array('body'=>'iframe', 'response'=>'pre'),
            'saveRouteId'=> 'sa_emails_save',
            'exclude'=> array()
        );

        $view->setXSSSanitation(false);
//        $view->display();
        return $view;
    }


    public function deleteEmails($id=0) 
    {

        $saEmail = ioc::staticResolve('saEmail');
        $email = app::$entityManager->find($saEmail, $id);

        $notify = new notification();

        try {
            app::$entityManager->remove($email);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Member deleted successfully.');
            url::redirect(url::make('sa_emails'));
        }
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occured while to deleteing that email. <br />'. $e->getMessage());
            url::redirect(url::make('sa_emails'));
        }
    }
}