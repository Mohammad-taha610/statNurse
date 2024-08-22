<?php
namespace sa\api;

use Doctrine\ORM\QueryBuilder;
use sacore\application\app;
use \sacore\application\controller;
use sacore\application\DefaultRepository;
use sacore\application\Event;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\member\saMember;
use sa\system\saAuth;
use sacore\utilities\arrayUtils;
use sacore\utilities\doctrineUtils;
use sacore\utilities\stringUtils;
use sacore\utilities\url;

class wsController extends ApiController
{
    public function description($urldata, $json)
    {
        $key = $this->getApiKey();
        $scope = $key->getEntityScope();

        $description = array();

        foreach(api::$registredApis['controller'] as $route=>$c) {

            $apiController = ioc::staticGet($c);

            if (!$apiController)
                continue;

            $class = new \ReflectionClass( $apiController );
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach($methods as $m) {
                $docblock = $m->getDocComment();
                if (strpos($docblock, '@api')!==false)
                    $description['api/v1/'.$route.'/'.$m->getName()] = array('module'=>'custom', 'class'=>$route, 'method'=>$m->getName());

            }

        }

        foreach($scope as $entity) {

            $parts = explode('\\', $entity);

            $url_module = $parts[2];
            $url_entity = $parts[3];

            $apiController = api::getAPIController( $url_module );

            if (!$apiController) {

                $apiController = api::getAPIEntityController($url_module . '\\' . $url_entity);

                if (!$apiController)
                    $apiController = ioc::staticGet('apiController');

                if (!$apiController)
                    continue;

                $class = new \ReflectionClass( $apiController );
                $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

                foreach($methods as $m) {
                    $docblock = $m->getDocComment();
                    if (strpos($docblock, '@api')!==false)
                        $description['api/v1/'.$url_module.'/'.$url_entity.'/'.$m->getName()] = array('module'=>$url_module, 'class'=>$url_entity, 'method'=>$m->getName());

                }
            }
            else
            {


                $class = new \ReflectionClass( $apiController );
                $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

                foreach($methods as $m) {
                    $docblock = $m->getDocComment();
                    if (strpos($docblock, '@api')!==false)
                        $description['api/v1/'.$url_module.'/'.$url_entity.'/'.$m->getName()] = array('module'=>$url_module, 'class'=>$url_entity, 'method'=>$m->getName());
                }

            }


        }

        $this->api->response(200, $description);
    }

    public function advance_query($urldata, $json)
    {
        $this->apiNotImplemented();
    }

    public function advance_query_sql($urldata, $json)
    {
        $this->apiNotImplemented();
    }


    public function query($urldata, $json)
    {
        $this->apiNotImplemented();
    }

    public function index($urldata, $json)
    {
        $this->apiNotImplemented();
    }

    public function count()
    {
        $this->apiNotImplemented();
    }

    public function get($id, $json)
    {
        $this->apiNotImplemented();
    }

    public function delete($id)
    {
        $this->apiNotImplemented();
    }

    public function update($id, $data)
    {
        $this->apiNotImplemented();
    }

    public function create($id, $data)
    {
        $this->apiNotImplemented();
    }
}