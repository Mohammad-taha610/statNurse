<?php

namespace sa\developer;

use sacore\application\Exception;

class CodeGeneration
{
    /**
     * BuildController
     *
     * Describe your function here
     */
    public static function buildControllers($module)
    {
        $moduleName = $module['module'];
        $namespace = $module['namespace'];
        $fqcn = $namespace.'\\'.$moduleName.'Config';
        $routes = $fqcn::getRoutes();

        $count = 0;

        /** @var \sacore\application\SaRestfulRoute $route */
        foreach ($routes as $route) {
            if (get_class($route) == 'sacore\application\SaRestfulRoute' || get_class($route) == 'sacore\application\RestfulRoute') {
                $count++;
                $methods = [];

                $routeInfo = $route->getIndexRouteInfo();
                if ($routeInfo) {
                    $methods[] = ['name' => $routeInfo['method_name'], 'type' => 'index', 'route' => $route];
                }

                $routeInfo = $route->getShowRouteInfo();
                if ($routeInfo) {
                    $methods[] = ['name' => $routeInfo['method_name'], 'type' => 'show', 'route' => $route];
                }

                $routeInfo = $route->getAddRouteInfo();
                if ($routeInfo) {
                    $methods[] = ['name' => $routeInfo['method_name'], 'type' => 'show_add', 'route' => $route];
                }

                $routeInfo = $route->getAddSaveRouteInfo();
                if ($routeInfo) {
                    $methods[] = ['name' => $routeInfo['method_name'], 'type' => 'save_add', 'route' => $route];
                }

                $routeInfo = $route->getEditRouteInfo();
                if ($routeInfo) {
                    $methods[] = ['name' => $routeInfo['method_name'], 'type' => 'show_edit', 'route' => $route];
                }

                $routeInfo = $route->getEditSaveRouteInfo();
                if ($routeInfo) {
                    $methods[] = ['name' => $routeInfo['method_name'], 'type' => 'save_edit', 'route' => $route];
                }

                $routeInfo = $route->getDeleteRouteInfo();
                if ($routeInfo) {
                    $methods[] = ['name' => $routeInfo['method_name'], 'type' => 'delete', 'route' => $route];
                }

                $sa = get_class($route) == 'sacore\application\SaRestfulRoute' ? true : false;

                self::buildController($route->getController(), $namespace, $module['dir'].'/'.$module['vendor'].'/'.$module['module'].'/src/classes', $sa);
                self::addMethods($namespace.'\\'.$route->getController(), $methods, $sa, $route->getBaseEntity());
            }
        }

        return $count;
    }

    /**
     * addMethods
     *
     * Generates methods for classes
     */
    public function addMethods($fqcn, $methods, $sa = false, $baseEntity = null)
    {
        if (! class_exists($fqcn)) {
            throw new Exception('The class '.$fqcn.' does not exist.');
        }

        $reflection = new \ReflectionClass($fqcn);

        $code = file_get_contents($reflection->getFileName());

        $pos = strrpos($code, '}');
        if ($pos !== false) {
            $code = substr_replace($code, '{addhere}', $pos, 1);
        }

        foreach ($methods as $method) {
            $type = $sa ? 'sa_'.$method['type'] : $method['type'];

            if ($reflection->hasMethod($method['name'])) {
                continue;
            }

            $methodCode = self::getMethodCode($method, $type, $baseEntity);

            $code = str_replace('{addhere}', $methodCode, $code);
        }

        $lastCode = '

}';

        $code = str_replace('{addhere}', $lastCode, $code);

        file_put_contents($reflection->getFileName(), trim($code));
    }

    /**
     * buildController
     *
     * Generates Controllers
     */
    public function buildController($name, $namespace, $dir, $sa)
    {
        $path = $dir.'/'.$name.'.php';
        if (! class_exists($namespace.'\\'.$name)) {
            $code = '<?php
namespace '.$namespace.';

use \sacore\application\app;
use \sacore\application\ioc;
use \sacore\application\\'.($sa ? 'saController' : 'controller').';
use \sacore\application\view;
use \sacore\utilities\doctrineUtils;
use \sacore\utilities\fieldValidation;
use \sacore\application\ValidateException;
use \sacore\utilities\notification;
use \sacore\utilities\url;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;

class '.$name.' extends '.($sa ? 'saController' : 'controller').' {

}';

            file_put_contents($path, $code);
        }
    }

    private static function getMethodCode($method, $type, $baseEntity)
    {
        $entity = $method['route']->getBaseEntity();
        if (empty($entity)) {
            return self::getDefaultCode($method, $baseEntity);
        }

        switch($type) {
            case 'sa_index':
                return self::getSaIndexCode($method, $baseEntity);
                break;
            case 'sa_show':
                return self::getSaShowCode($method, $baseEntity);
                break;
            case 'sa_show_add':
                return self::getSaShowAddCode($method, $baseEntity);
                break;
            case 'sa_save_add':
                return self::getSaAddSaveCode($method, $baseEntity);
                break;
            case 'sa_show_edit':
                return self::getSaShowEditCode($method, $baseEntity);
                break;
            case 'sa_save_edit':
                return self::getSaEditSaveCode($method, $baseEntity);
                break;
            case 'sa_delete':
                return self::getSaDeleteCode($method, $baseEntity);
                break;
            default:
                return self::getDefaultCode($method, $baseEntity);
        }

        return self::getDefaultCode($method, $baseEntity);
    }

    private function getDefaultCode($method, $baseEntity)
    {
        $methodCode = '
    /**
    * '.$method['name'].'
    *
    * Added using SA3 Code gen Restful routes
    */
    public function '.$method['name'].'()
    {

    }

    {addhere}
';

        return $methodCode;
    }

    private function getSaShowCode($method, $baseEntity)
    {
        $methodCode = '
    /**
    * '.$method['name'].'
    *
    * Added using SA3 Code gen Restful routes
    */
    public function '.$method['name'].'($id)
    {
    }

    {addhere}
';

        return $methodCode;
    }

    private function getSaShowAddCode($method, $baseEntity)
    {
        /** @var \sacore\application\RestfulRoute $route */
        $route = $method['route'];
        $routeInfo = $route->getEditRouteInfo();

        $methodCode = '
    /**
    * '.$method['name'].'
    *
    * Added using SA3 Code gen Restful routes
    */
    public function '.$method['name'].'()
    {
        $this->'.$routeInfo['method_name'].'();
    }

    {addhere}
';

        return $methodCode;
    }

    private function getSaAddSaveCode($method, $baseEntity)
    {
        /** @var \sacore\application\RestfulRoute $route */
        $route = $method['route'];
        $routeInfo = $route->getEditSaveRouteInfo();

        $methodCode = '
    /**
    * '.$method['name'].'
    *
    * Added using SA3 Code gen Restful routes
    */
    public function '.$method['name'].'()
    {
        $this->'.$routeInfo['method_name'].'();
    }

    {addhere}
';

        return $methodCode;
    }

    private function getSaIndexCode($method, $baseEntity)
    {
        /** @var \sacore\application\RestfulRoute $route */
        $route = $method['route'];

        $create = $route->getAddRouteInfo();
        $edit = $route->getEditRouteInfo();
        $delete = $route->getDeleteRouteInfo();

        $methodCode = '/**
    * '.$method['name'].'
    *
    * Added using SA3 Code gen Restful routes
    */
    public function '.$method['name'].'()
    {
        $view = new view("master", "table", $this->viewLocation(), false);

        $perPage = isset($_REQUEST[\'limit\']) ? $_REQUEST[\'limit\'] : 20;
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


        $totalRecords = app::$entityManager->getRepository( ioc::staticResolve("'.$baseEntity.'") )->search($fieldsToSearch, false, false, false, true);
        $data = app::$entityManager->getRepository( ioc::staticResolve("'.$baseEntity.'") )->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));
        $totalPages = ceil($totalRecords / $perPage);

        $view->data["table"][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            "header"=>array(  array("name"=>"Name", "class"=>"")  ),
            /* SET ACTIONS ON EVERY ROW */
            "actions"=>array(
                "edit"=>array("name"=>"Edit", "routeid"=>"'.$edit['id'].'", "params"=>array("id")),
                "delete"=>array("name"=>"Delete", "routeid"=>"'.$delete['id'].'", "params"=>array("id"))
            ),
            /* SET THE NO DATA MESSAGE */
            "noDataMessage"=>"No records are available",
            /* SET THE DATA MAP */
            "map"=>array("name"),
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            "tableCreateRoute"=>"'.$create['id'].'",
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            "data"=> doctrineUtils::getEntityCollectionArray($data),
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            "totalRecords"=> $totalRecords,
            "totalPages"=> $totalPages,
            "currentPage"=> $currentPage,
            "perPage"=> $perPage,
            "searchable"=> true,
            "dataRowCallback"=> function($data) {
                //$data["date_created"] = $data["date_created"]->format("m/d/Y g:i a");
                return $data;
            }
        );

        $view->display();
    }

{addhere}';

        return $methodCode;
    }

    private function getSaShowEditCode($method, $baseEntity)
    {
        /** @var \sacore\application\RestfulRoute $route */
        $route = $method['route'];
        $routeInfo = $route->getEditSaveRouteInfo();

        $routeAddInfo = $route->getAddSaveRouteInfo();

        $varname = strtolower($baseEntity);

        $methodCode = '
    /**
    * '.$method['name'].'
    * Show '.$baseEntity.'
    *
    * @param '.$baseEntity.' $'.$varname.'
    * @param null $passData
    */
    public function '.$method['name'].'($'.$varname.'=null, $passData=null)
    {

        $action = "'.$routeInfo['id'].'";


        $view = new view("master", "dbform", $this->viewLocation(), false);

        $id = 0;
        if ($'.$varname.') {
            /** @var '.$baseEntity.' $'.$varname.' */
            $mData = doctrineUtils::getEntityArray( $'.$varname.' );

            if ( method_exists($'.$varname.', "getImage") && $'.$varname.'->getImage()) {
                $mData[\'image_id\'] = $'.$varname.'->getImage()->getId();
                $mData[\'image_path\'] = url::make(\'files_browser_view_file\', $'.$varname.'->getImage()->getFolder(), $'.$varname.'->getImage()->getFilename());
            }

            $view->data = array_merge($view->data, $mData);
            $id = $'.$varname.'->getId() ? $'.$varname.'->getId() : 0;
        }
        else
        {
            $action = "'.$routeAddInfo['id'].'";
        }

        if ($passData) {
            $view->data = array_merge($view->data, $passData);
        }

        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve("'.$baseEntity.'"));
        $view->data["dbform"][] = array(
            "columns"=> $metaData->fieldMappings,
            "saveRoute"=> array("routeId"=>$action, "params"=>array( $id ) ),
            "form"=>true,
            "image_upload"=>false,
            "exclude"=> array(
                "id", "date_created", "date_updated", "is_deleted"
            )
        );

        $view->display();
    }

    {addhere}
';

        return $methodCode;
    }

    private function getSaEditSaveCode($method, $baseEntity)
    {
        /** @var \sacore\application\RestfulRoute $route */
        $route = $method['route'];
        $routeInfo = $route->getIndexRouteInfo();
        $routeEditInfo = $route->getEditRouteInfo();

        $varname = strtolower($baseEntity);

        $methodCode = '
    /**
    * '.$method['name'].'
    * Save '.$baseEntity.'
    *
    * @param '.$baseEntity.' $'.$varname.'
    */
    public function '.$method['name'].'($'.$varname.'=null)
    {
        /** @var '.$baseEntity.' $group */
        if (!$'.$varname.') {
            $'.$varname.' = ioc::resolve("'.$baseEntity.'");
        }

        $'.$varname.' = doctrineUtils::setEntityData($_POST, $'.$varname.');

        try {
            $image = app::$entityManager->find(ioc::staticGet(\'saImage\'), $_POST[\'image_id\']);
            $'.$varname.'->setImage($image);
        }
        catch( \Doctrine\ORM\ORMException $e) {

        }

        $notify = new notification();

        try {
            app::$entityManager->persist($'.$varname.');
            app::$entityManager->flush();
            $notify->addNotification("success", "Success", "Record saved successfully.");
            url::redirectId("'.$routeInfo['id'].'");
        }
        catch (ValidateException $e) {

            $notify->addNotification("danger", "Error", "An error occured while saving your changes. <br />". $e->getMessage());
            $this->'.$routeEditInfo['method_name'].'($'.$varname.', $_POST);
        }
        catch (NotNullConstraintViolationException $e) {

            $notify->addNotification("danger", "Error", "An error occured while saving your changes. Please contact your web administrator.<br />");
            $this->'.$routeEditInfo['method_name'].'($'.$varname.', $_POST);
        }
    }

    {addhere}
';

        return $methodCode;
    }

    private function getSaDeleteCode($method, $baseEntity)
    {
        /** @var \sacore\application\RestfulRoute $route */
        $route = $method['route'];
        $routeInfo = $route->getIndexRouteInfo();

        $varname = strtolower($baseEntity);

        $methodCode = '
    /**
    * '.$method['name'].'
    * Delete '.$baseEntity.'
    *
    * @param '.$baseEntity.' $'.$varname.'
    */
    public function '.$method['name'].'($'.$varname.')
    {
        $notify = new notification();

        try {
            app::$entityManager->remove($'.$varname.');
            app::$entityManager->flush();
            $notify->addNotification("success", "Success", "Record deleted successfully.");
            url::redirectId("'.$routeInfo['id'].'");
        }
        catch (ValidateException $e) {

            $notify->addNotification("danger", "Error", "An error occured while saving your changes. <br />". $e->getMessage());
            url::redirectId("'.$routeInfo['id'].'");
        }
    }

    {addhere}
';

        return $methodCode;
    }
}
