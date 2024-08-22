<?php

namespace sa\developer;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\View;
use sacore\application\saController;
use sa\developer\EntityGenerator\EntityGenerator;
use sa\developer\EntityGenerator\EntityGeneratorProperty;
use sacore\utilities\doctrineUtils;
use sacore\utilities\fieldValidation;
use sacore\utilities\notification;
use sacore\utilities\url;

class saDeveloperController extends saController
{
    public function showDoctrine()
    {
        $view = new View('doctrine', $this->viewLocation(), false);

        return $view;
    }

    public function doctrineCacheStats()
    {
        $view = new View('showDoctrineCache', $this->viewLocation(), false);

        $stats = [
            app::$entityManager->getConfiguration()->getMetadataCacheImpl()->getStats(),
            app::$entityManager->getConfiguration()->getQueryCacheImpl()->getStats(),
            app::$entityManager->getConfiguration()->getResultCacheImpl()->getStats(),
        ];

        $view->data['doctrine_data'] = print_r($stats, true);
        $view->data['apc_data'] = 'N\\A';
        if (function_exists('apc_cache_info')) {
            $view->data['apc_data'] = print_r(apc_cache_info(), true);
        }

        return $view;
    }

    public static function clearDoctrineCache()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
            $_SESSION['apc_dev_cache'] = time();
        }

        try {
            /** @var \Doctrine\DBAL\Configuration $config */
            $config = app::$entityManager->getConnection()->getConfiguration();
            $config->getMetadataCacheImpl()->deleteAll();
            $config->getQueryCacheImpl()->deleteAll();
            $config->getResultCacheImpl()->deleteAll();

            app::get()->getCacheManager()->getCache('doctrine')->deleteAll();
        } catch(\Exception $e) {
        }
    }

    public function doctrineEntities()
    {
        $entities = [];
        try {
            $meta = app::$entityManager->getMetadataFactory()->getAllMetadata();
            foreach ($meta as $m) {
                $entities[] = $m->getName();
            }
        } catch(\Exception $e) {
            $log = 'An error occured preventing us from gathering all the available entities. '.$e->getMessage();
        }

        $view = new View('doctrine_entities', $this->viewLocation(), false);
        $view->setXSSSanitation(false);
        $view->data['entities'] = $entities;
        $view->data['output'] = $log;

        return $view;
    }

    public function executeDoctrine()
    {
        $app = app::getInstance();
        $subcommand = '';
        switch ($_REQUEST['c']) {
            case 'orm_entities':
                static::clearDoctrineCache();
                $log = doctrineUtils::updateEntity($_REQUEST['entity']);
                break;
            case 'orm_repositories':
                static::clearDoctrineCache();
                $log = doctrineUtils::generateRepositories();
                break;
            case 'orm_proxies':
                static::clearDoctrineCache();
                $log = doctrineUtils::generateProxies();
                break;
            case 'orm_schema_update':
                ini_set('memory_limit', '128M');
                static::clearDoctrineCache();
                $log = doctrineUtils::updateSchema();
                break;
            case 'migration_status':
                $log = 'Unavailable at this time';
                break;
            case 'migration_migrate':
                //$subcommand = 'migrations:migrate --no-interaction';
                $log = 'Unavailable at this time';
                break;
            case 'migration_migrate_dry':
                //$subcommand = 'migrations:migrate --no-interaction --dry-run';
                $log = 'Unavailable at this time';
                break;
            case 'migration_migrate_generate':
                //$subcommand = 'migrations:generate';
                $log = 'Unavailable at this time';
                break;
            default:
        }

        $view = new view('doctrine', $this->viewLocation(), false);
        $view->setXSSSanitation(false);
        $view->data['output'] = $log;
        // echo "<pre>" . \Doctrine\Common\Util\Debug::dump($view->data, 3) . "</pre>"; exit;
        return $view;
    }

    public function createModule($request)
    {
        $passeddata = $request->data;
        $view = new View('createModule', $this->viewLocation(), false);

        $types = \Doctrine\DBAL\Types\Type::getTypesMap();

        if ($passeddata) {
            $view->data = $passeddata;
        }

        $view->data['types'] = array_keys($types);

        return $view;
    }

    public function saveModule()
    {
        $fv = new fieldValidation();

        $fv->isNotEmpty($_POST['name'], 'Please enter a module name.');
        $fv->isNotEmpty($_POST['namespace'], 'Please enter a name space.');
        $fv->isNotEmpty($_POST['entity_name'], 'Please enter an entity name.');
        $fv->isNotEmpty($_POST['table_name'], 'Please enter a table name.');

        $notify = new notification();

        if (! $fv->hasErrors()) {
            $moduleCreator = new ModuleGenerator($_POST['name'], $_POST['namespace']);

            /** @var EntityGenerator $entityGenerator */
            $entityGenerator = ioc::get('EntityGenerator');
            $entityGenerator->setName($_POST['entity_name']);
            $entityGenerator->setNamespace($_POST['namespace']);
            $entityGenerator->setTableName($_POST['table_name']);
            $entityGenerator->setPath($moduleCreator->getModuleDirectory());
            foreach ($_POST['entity_property'] as $property) {
                /** @var EntityGeneratorProperty $pObject */
                $pObject = ioc::get('EntityGeneratorProperty');
                $pObject->setName($property['name']);
                $pObject->setType($property['type']);
                $entityGenerator->addProperty($pObject);
            }
            $moduleCreator->addEntity($entityGenerator);

            $moduleCreator->create();

            $notify->addNotification('success', 'Success', 'The module has been created successfully.');

            unset($_SESSION['moduleCache']);

            $this->createModule();
        } else {
            $notify->addNotification('danger', 'Error', $fv->getHtml());

            $this->createModule($_POST);
        }
    }

//
//	public static function testRoutes($odata) {
//
////	    $oldURI = url::uri();
////	    url::setURI($odata);
//
//
//	    $data = array();
//        $data['route_info'] = app::get()->findRoute($odata['route'], $odata['method'], true);
//
////        url::setURI($oldURI);
//
//	    return $data;
//    }

    public static function testRoutes($data)
    {
        $return = [];
        if ($data['method'] == 'ANY') {
            $router = app::get()->getRouter();
            $context = $router->getContext();
            $originalMethod = $context->getMethod();
            $context->setMethod('GET');
            try {
                $return['route_info'] = app::get()->findRoute($data['route']);
            } catch (MethodNotAllowedException $e) {
            }
            $context->setMethod('POST');
            try {
                $return['route_info'] = app::get()->findRoute($data['route']);
            } catch (MethodNotAllowedException $e) {
            }
            $context->setMethod($originalMethod);
        } else {
            $return['route_info'] = app::get()->findRoute($data['route']);
        }

        return $return;
    }

    public function showIOC()
    {
        $view = new view('ioc_info', $this->viewLocation(), false);

        $view->data['last_refreshed'] = ioc::getLastRefreshed();

        $classes = ioc::getRegisteredClasses();
//        echo '<pre>'.print_r($classes, true).'</pre>'; ob_flush(); exit;
        $dataForTable = [];

        foreach ($classes as $key => $class) {
            if (empty($key)) {
                continue;
            }

            $resolved = 'Not Resolved';
            $discovered = '';
            if (isset($class['resolved']['full_class'])) {
                $resolved = $class['resolved']['full_class'];
                $discovered = $class['resolved']['discovered_by'];
            }

            $dataForTable[] = [$key, $resolved, $discovered, count($class['classes'])];
        }

        usort($dataForTable, function ($a, $b) {
            if ($a > $b) {
                return 1;
            } elseif ($a < $b) {
                return -1;
            } else {
                return 0;
            }
        });

        $view->data['table'][] = [

            /* SET THE HEADER OF THE TABLE UP */
            'header' => [
                ['name' => 'IOC Request Name', 'class' => ''],
                ['name' => 'Resolved Class', 'class' => ''],
                ['name' => 'Discovered By', 'class' => ''],
                ['name' => '# Classes', 'class' => ''],
            ],
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => $dataForTable,
            'dataRowCallback' => function ($data) {
                if ($data[1] == 'Not Resolved') {
                    $data[1] = '<span class="label label-danger arrowed arrowed-right" style="font-weight: bold">'.$data[1].'</span>';
                }

                return $data;
            },
        ];

        //throw new \Exception("Error Processing Request");
        return $view;
    }

    public function showRoutes()
    {
        $view = new View('route_test', $this->viewLocation(), false);

        //		Todo: Changed SiteAdmin code in vendor, not sure if that will be updated when all these files are copied
        $routes = app::getInstance()->getRoutes();
        $dataForTable = [];

        foreach ($routes as $id => $route) {
            $defaults = $route->getDefaults();
            $methods = implode(', ', $route->getMethods());
            $dataForTable[] = [$id, $defaults['name'], $route->getPath(), $methods, $defaults['_controller'], $defaults['_middleware']];
        }

        $view->data['table'][] = [

            /* SET THE HEADER OF THE TABLE UP */
            'header' => [['name' => 'Id', 'class' => ''], ['name' => 'Name', 'class' => ''],	['name' => 'Route'], ['name' => 'Method'],	['name' => 'Action'], ['name' => 'Type']],
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => $dataForTable,
        ];

        //throw new \Exception("Error Processing Request");
        return $view;
    }

    public function showSession()
    {
        $view = new View('showSession', $this->viewLocation());

        $view->data['environment'] = print_r(app::getInstance()->getEnvironment(), true);
        $view->data['id'] = session_id();
        $view->data['session'] = print_r($_SESSION, true);

        //throw new \Exception("Error Processing Request");
        return $view;
    }

    public function showEventsListeners()
    {
        $view = new view('events_listeners', $this->viewLocation(), false);

        $view->data['events'] = print_r(app::getInstance()->eventSubscriptions, true);
        $view->data['listeners'] = print_r(app::getInstance()->requestSubscriptions, true);

        return $view;
    }

    public function showInstalledPkgs()
    {
        $packages = json_decode(file_get_contents(app::getAppPath().'/vendor/composer/installed.json'), true);

        usort($packages, function ($a, $b) {
            if ($a['name'] == $b['name']) {
                return 0;
            }

            return ($a['name'] < $b['name']) ? -1 : 1;
        });

        $view = new view('installed_packages', $this->viewLocation(), false);

        $view->data['pkgs'] = $packages;

        //throw new \Exception("Error Processing Request");
        return $view;
    }

    public function showPHPInfo()
    {
        ob_start();
        phpinfo();
        $phpInfo = ob_get_clean();

        //echo $phpInfo; exit;

        //$doc = phpQuery::newDocumentHTML($phpInfo);

        $doc = $phpInfo;

        $view = new View('data', $this->viewLocation(), false);
        $view->addCSSResources([app::get()->getRouter()->generate('sa_developer_css', ['file' => 'phpinfo.css'])]);
        $view->setXSSSanitation(false);

        //		$view->data['data'] = '<div class="phpinfo">'.$doc->selectOne('.center')->html().'</div>';
        $view->data['data'] = '<div class="phpinfo">'.$doc.'</div>';

        return $view;
    }

    public function showCodeGeneration()
    {
        $modules = app::getInstance()->getModules();
        $view = new View('code_generation', $this->viewLocation(), false);
        $view->data['modules'] = $modules;

        return $view;
    }

    public function executeCodeGeneration()
    {
        $modules = app::getInstance()->getModules();
        $selectedModule = '';
        foreach ($modules as $module) {
            if ($module['module'] == $_REQUEST['module']) {
                $selectedModule = $module;
                break;
            }
        }

        $modules = app::getInstance()->getModules();
        $changed = CodeGeneration::buildControllers($selectedModule);
        $view = new View('code_generation', $this->viewLocation());
        $view->data['modules'] = $modules;
        $view->data['output'] = $changed.' controllers where modified.';

        return $view;
    }

    public function updateNewEntities($args)
    {
        static::clearCache();
        doctrineUtils::updateEntity($args['name']);
        doctrineUtils::generateRepositories();
        doctrineUtils::generateProxies();
        doctrineUtils::updateSchema();
    }

    public static function deleteDir($dirPath)
    {
        if (! is_dir($dirPath)) {
            //throw new InvalidArgumentException("$dirPath must be a directory");
            return;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath.'*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}
