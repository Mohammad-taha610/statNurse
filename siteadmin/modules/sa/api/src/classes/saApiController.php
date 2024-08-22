<?php
namespace sa\api;

use ReflectionClass;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\route;
use \sacore\application\saController;
use sacore\application\view;

class saApiController extends saController
{

//    public function apiHelp() {
//
//        $routes = app::getInstance()->getRoutes();
//
//        $apiRoutes = array();
//        /** @var \sacore\application\route $route */
//        foreach($routes as $route) {
//            if (strpos(strtolower($route->id), 'api')!==false && $route->id!='api_catch_all' && $route->id!='api_help' && $route->id!='api_not_allowed') {
//
//                $function = $route->function;
//                $controller = ioc::staticResolve( $route->controller );
//
//                $rc = new ReflectionClass( $controller );
//                if ($rc->hasMethod($function)) {
//                    $method = $rc->getMethod($function);
//                    $description = $method->getDocComment();
//
//                    $routeURL = trim($route->route, '^$');
//                    $routeURL = preg_replace_callback('#\[.*?\](\{.*?\}){0,}#s', function ($matches) {
//                        return '{param}';
//                    }, $routeURL);
//                    $routeName = trim($route->name, 'API ');
//                    $apiRoutes[] = array('id' => $route->id, 'route' => $routeURL, 'name' => $routeName, 'action' => $route->getAction(), 'description' => $description);
//                }
//
//            }
//        }
//
//        usort($apiRoutes, function($a, $b) {
//
//            if ($a['name'] == $b['name']) {
//                return 0;
//            }
//            return ($a['name'] < $b['name']) ? -1 : 1;
//
//        });
//
//
//
//        $view = new view('master_nonav', 'help', $this->viewLocation(), false);
//        $view->data['routes'] = $apiRoutes;
//        $view->display();
//
//    }

}