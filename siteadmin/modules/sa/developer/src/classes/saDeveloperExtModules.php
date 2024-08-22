<?php

namespace sa\developer;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\View;
use sacore\application\saController;

class saDeveloperExtModules extends saController
{
    public function showList()
    {
        $classes = ioc::getRegisteredClasses();
        $modulesClasses = [];
        foreach ($classes as $class) {
            if ($class['resolved']['namespace'] && (substr($class['resolved']['namespace'], 1, 2) == 'sa' || substr($class['resolved']['namespace'], 0, 2) == 'sa')) {
                // SA modules
            } elseif ($class['resolved']['namespace']) {
                // non-SA modules
                $modulesClasses[$class['resolved']['namespace']][] = ['class' => $class['resolved']['class'], 'file' => $class['resolved']['file']];
            }
        }

        $view = new View('list_ext_modules');

        $entities = ioc::getRepository('saExtModule')->findAll();
        foreach ($entities as $entity) {
            $view->data['reasons'][$entity->getModule()] = $entity->getReason();
        }

        $view->data['modules'] = $modulesClasses;

        return $view;
    }

    public function saveModuleReasons()
    {
        if ($_POST['modules']) {
            foreach ($_POST['modules'] as $module => $reason) {
                $entity = ioc::getRepository('saExtModule')->findOneBy(['module' => $module]);
                if (! $entity) {
                    $entity = ioc::get('saExtModule');
                }

                $entity->setModule($module);
                $entity->setReason($reason);
            }

            app::$entityManager->persist($entity);
            app::$entityManager->flush();
        }

        return $this->showList();
    }

    public static function getSaPerformanceAlerts($data = [])
    {
        $entities = ioc::getRepository('saExtModule')->findAll();
        foreach ($entities as $entity) {
            if (! $entity->getReason()) {
                $alert = [];
                $alert['performance_msg'] = ['type' => 'warning', 'msg' => 'There are unverified extended modules within this project.'];
                $alert['performance_header_msg'] = '';
                $alert['status'] = 'warning';
                $data[] = $alert;
                break;
            }
        }

        return $data;
    }
}
