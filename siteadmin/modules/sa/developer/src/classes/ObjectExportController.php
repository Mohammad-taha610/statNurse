<?php

namespace sa\developer;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\utilities\doctrineUtils;

class ObjectExportController extends saController
{
    public function displayEntities()
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

        $view = new View('object_export_entities', $this->viewLocation(), false);
        $view->setXSSSanitation(false);
        $view->data['entities'] = $entities;
        $view->data['output'] = $log;

        return $view;
    }

    public function entitiesExport()
    {
        //Doesn't break anymore but zip file is completely empty
        $zip = new \ZipArchive;

        $zip->open(app::get()->getConfiguration()->get('tempDir')->getValue().'/objects_export.zip', \ZipArchive::CREATE);

        foreach ($_POST['entity'] as $entity) {
            $objects = ioc::getRepository('\\'.$entity)->findBy([], [], 100, 0);

            $table = strtolower(preg_replace('/[^0-1A-Z]/i', '_', $entity));

            foreach ($objects as $object) {
                $objectSerialized = doctrineUtils::getEntityArray($object, true, true);
                $objectSerialized['entity_name'] = $entity;

                if (method_exists($object, 'objectExport')) {
                    $objectSerialized = $object::objectExport($objectSerialized);
                }

                if ($object instanceof \sa\files\saFile) {
                    $objectSerialized['binary'] = base64_encode($object->get());
                }

                $zip->addFromString($table.'/'.$object->getId().'.saobj', serialize($objectSerialized));
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=objects_export.zip');
        header('Content-Length: '.filesize(app::get()->getConfiguration()->get('tempDir')->getValue().'/objects_export.zip'));
        readfile(app::get()->getConfiguration()->get('tempDir')->getValue().'/objects_export.zip');

        unlink(app::get()->getConfiguration()->get('tempDir')->getValue().'/objects_export.zip');
    }
}
