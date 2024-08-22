<?php
namespace sa\system;

use sacore\application\app;
use sacore\application\modRequest;
use \sacore\application\saController;
use sacore\application\responses\View;


class saDefaultDataController extends saController
{


    public function defaultDataIndex() {

        $modules = modRequest::request('site.default_data.list');
        $view = new View('default_data', $this->viewLocation());
        $view->data['modules'] = $modules;
        return $view;

    }

    public function getModuleList(){
        return app::get()->getModules();
    }


    public function defaultDataImport($request) {
        $modules = modRequest::request('site.default_data.list');

        $data = $request->request;
        foreach($data->get('module') as $module) {
            $install = modRequest::request($module);
            foreach($modules as $k=>$mod) {
                if ($mod['module']==$module) {
                    $modules[$k]['status']['install'] = $install;
                    break;
                }
            }
        }

        foreach($data->get('module') as $module) {
            $relations = modRequest::request($module.'.link');
            foreach($modules as $k=>$mod) {
                if ($mod['request']==$module) {
                    $modules[$k]['status']['relations'] = $relations;
                    break;
                }
            }
        }

        foreach($modules as $k=>$mod) {
            if (!isset($modules[$k]['status'])) {
                unset($modules[$k]);
            }
        }

        $view = new View( 'table', $this->viewLocation());
        $view->data['modules'] = $modules;

        $view->data['table'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header'=>array(
                array('name'=>'Module', 'class'=>'', 'map'=>'name'),
                array('name'=>'Rows Affected', 'class'=>'', 'map'=>'status.install')),
            /* SET ACTIONS ON EVERY ROW */
//            'actions'=>array(
//                'edit'=>array('name'=>'Edit', 'routeid'=>'sa_sausers_edit', 'params'=>array('id') ),
//                'delete'=>array('name'=>'Delete', 'routeid'=>'sa_sausers_delete', 'params'=>array('id')),
//            ),
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No default data was imported',
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            //'tableCreateRoute'=>'sa_sausers_create',
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data'=> $modules,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords'=> count($modules),
            /*'totalPages'=> $totalPages,
            'currentPage'=> $currentPage,
            'perPage'=> $perPage,*/
            'title'=>'The following modules setup default data:',
            'dataRowCallback'=> function($data) {
                /*if ($data["last_login"])
                    $data["last_login"] = $data["last_login"]->format("m/d/Y g:i a");*/

                return $data;
            }
        );

        $view->data['table'][] = array(
            /* SET THE HEADER OF THE TABLE UP */
            'header'=>array(
                array('name'=>'Module', 'class'=>'', 'map'=>'name'),
                array('name'=>'Rows Affected', 'class'=>'', 'map'=>'status.relations')),
            /* SET ACTIONS ON EVERY ROW */
//            'actions'=>array(
//                'edit'=>array('name'=>'Edit', 'routeid'=>'sa_sausers_edit', 'params'=>array('id') ),
//                'delete'=>array('name'=>'Delete', 'routeid'=>'sa_sausers_delete', 'params'=>array('id')),
//            ),
            /* SET THE NO DATA MESSAGE */
            'noDataMessage'=>'No default data was imported',
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            //'tableCreateRoute'=>'sa_sausers_create',
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data'=> $modules,
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords'=> count($modules),
            /*'totalPages'=> $totalPages,
            'currentPage'=> $currentPage,
            'perPage'=> $perPage,*/
            'title'=>'The following modules setup data relationships:',
            'dataRowCallback'=> function($data) {
                /*if ($data["last_login"])
                    $data["last_login"] = $data["last_login"]->format("m/d/Y g:i a");*/

                return $data;
            }
        );

        return $view;
    }


    public static function index($data) {
        $data[] = array('request'=>'system.install_default_data', 'name'=>'System');
        return $data;
    }

    public static function installData() {


        return true;
    }


}