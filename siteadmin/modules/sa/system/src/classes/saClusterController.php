<?php

namespace sa\system;


use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sa\sa3ApiClient\Sa3ApiClient;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use sacore\utilities\url;

class saClusterController extends saController
{

    /*
    * apiKeyIndex
    *
    * Added using SA3 Code gen Restful routes
    */
    public function index()
    {
        $view = new View("table");

        $perPage = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
        $fieldsToSearch=array();

        foreach($_GET as $field=>$value) {
            if (strpos($field, "q_")===0 && !empty($value)) {
                $fieldsToSearch[ str_replace("q_", "", $field) ] = $value;
            }
        }

        $currentPage = !empty($_REQUEST["page"]) ? $_REQUEST["page"] : 1;
        $sort = !empty($_REQUEST["sort"]) ? $_REQUEST["sort"] : false;
        $sortDir = !empty($_REQUEST["sortDir"]) ? $_REQUEST["sortDir"] : false;
        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }


        $totalRecords = ioc::getRepository("saClusterNode")->search($fieldsToSearch, false, false, false, true);
        $data =ioc::getRepository("saClusterNode")->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));
        $totalPages = ceil($totalRecords / $perPage);

        $view->data["table"][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            "header"=>array(
                array("name" => "Name", "class" => ""),
                array("name" => "Environment", "class" => ""),
                array("name" => "URL", "class" => "")
            ),
            /* SET ACTIONS ON EVERY ROW */
            "actions"=>array(
                "edit"=>array("name"=>"Edit", "routeid"=>"sa_system_cluster_node_edit", "params"=>array("id")),
                "delete"=>array("name"=>"Delete", "routeid"=>"sa_system_cluster_node_delete", "params"=>array("id"))
            ),
            /* SET THE NO DATA MESSAGE */
            "noDataMessage"=>"There are no cluster nodes set up for this site",
            /* SET THE DATA MAP */
            "map"=>array("name", "environment", "sa_api_url"),
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            "tableCreateRoute"=>"sa_system_cluster_node_add",
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            "data"=> doctrineUtils::getEntityCollectionArray($data),
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            "totalRecords"=> $totalRecords,
            "totalPages"=> $totalPages,
            "currentPage"=> $currentPage,
            "perPage"=> $perPage,
            "searchable"=> false,
            "dataRowCallback"=> function($data) {
                //$data["date_created"] = $data["date_created"]->format("m/d/Y g:i a");
                return $data;
            }
        );

        return $view;
    }


    /*
    * apiKeyShow
    *
    * Added using SA3 Code gen Restful routes
    */
    public function show($id)
    {
    }


    /*
    * apiKeyShowAdd
    *
    * Added using SA3 Code gen Restful routes
    */
    public function showAdd()
    {
        return $this->showEdit(0);
    }


    /*
    * apiKeySaveAdd
    *
    * Added using SA3 Code gen Restful routes
    */
    public function saveAdd()
    {
        return $this->saveEdit(null);
    }


    /**
     * @param saClusterNode $cluster
     * @param null $passData
     * @return View
     */
    public function showEdit($cluster=null, $passData=null)
    {
        $view = new View("dbform");

        $id = 0;
        $save_route = null;
        if ($cluster) {
            /** @var $cluster $cluster */
            $mData = doctrineUtils::getEntityArray( $cluster );

            $view->data = array_merge($view->data, $mData);
            $id = $cluster->getId() ? $cluster->getId() : 0;

            $save_route = 'sa_system_cluster_node_edit_save';

        } else {

            $cluster = ioc::resolve('saClusterNode');
            $save_route = 'sa_system_cluster_node_add_save';

        }

        if ($passData) {
            $view->data = array_merge($view->data, $passData);
        }


        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve('saClusterNode'));
        $view->data['dbform'][] = array(

            /* SET THE HEADER OF THE TABLE UP */
            'columns'=> $metaData->fieldMappings,
            'form'=> true,
            'useInputFields'=> true,
            //'typeOverrides' => array('body'=>'iframe', 'response'=>'pre'),
            'saveRouteId'=> $save_route,
            'exclude'=> array('id')
        );

        return $view;
    }

    /**
     * @param saClusterNode $cluster
     * @return Redirect|View
     */
    public function saveEdit($cluster=null)
    {
        $new = false;
        if (!$cluster) {
            $new = true;
            $cluster = ioc::get('saClusterNode');
        }

        $notify = new notification();

        /** @var saClusterNode $cluster */
        $cluster = doctrineUtils::setEntityData($_POST, $cluster);

        $client = new Sa3ApiClient($cluster->getSaApiUrl(), $cluster->getClientId(), $cluster->getApiKey());

        if (!$client->isConnected()) {
            $notify->addNotification("danger", "Error", "Unable to connect to the cluster node.  Please check the node and try again. ");
            return $this->showEdit( $new ? null : $cluster, $_POST);
        }


        try {


            app::$entityManager->persist($cluster);
            app::$entityManager->flush();

            $notify->addNotification("success", "Success", "Cluster Node saved successfully.");


            return new Redirect( url::make('sa_system_cluster') );

        }
        catch (ValidateException $e) {

            $notify->addNotification("danger", "Error", "An error occurred while saving your changes. <br />". $e->getMessage());
            return $this->showEdit($new ? null : $cluster, $_POST);

        }
        catch (NotNullConstraintViolationException $e) {

            $notify->addNotification("danger", "Error", "An error occurred while saving your changes. Please contact your web administrator.<br />");
            return $this->showEdit($new ? null : $cluster, $_POST);

        }


    }



    /**
     * @param saClusterNode $cluster
     * @return null|Redirect
     */
    public function delete($cluster)
    {
        $notify = new notification();

        $redirect = null;

        try {
            app::$entityManager->remove($cluster);
            app::$entityManager->flush();
            $notify->addNotification("success", "Success", "Record deleted successfully.");
            $redirect = new Redirect( app::get()->getRouter()->generate('sa_system_cluster') );
        }
        catch (ValidateException $e) {

            $notify->addNotification("danger", "Error", "An error occurred while saving your changes. <br />". $e->getMessage());
            $redirect = new Redirect( app::get()->getRouter()->generate('sa_system_cluster'));
        }

        return $redirect;
    }

}