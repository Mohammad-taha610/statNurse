<?php
namespace sa\system;

use Doctrine\ORM\Query;
use sacore\application\app;
use sacore\application\ioc;
use sacore\utilities\doctrineUtils;
use sacore\utilities\stringUtils;

class DefaultDataInstaller
{


    public static function install($dataLocation) {

        $data_files = static::rsearch($dataLocation, '/(.+)\.saobj/');

        $count = 0;

        /**
         * Loop through each data file
         */
        foreach($data_files as $file) {

            $content = $content_relationship =  unserialize(file_get_contents($file));
            $entityName = '\\'.$content['entity_name'];

            $metaData = app::$entityManager->getClassMetadata($entityName);
            $entityId = reset($metaData->getIdentifier());

            $idProxy = $content[ $entityId ];

            $uuid = ioc::getRepository('saEntityUuid')->getUuid( $entityName, null, $idProxy->getUuid(), false );
            if (!$uuid) {

                /**
                 * Unset ModelIdProxies.  Will be used later
                 */
                foreach ($content as $field => $value) {
                    if ($content[$field] instanceof \sa\system\saEntityUuid) {
                        unset($content[$field]);
                    }
                }


                /**
                 * Setup the entity and persist/flush it
                 */
                $entity = ioc::get($entityName);
                doctrineUtils::setEntityData($content, $entity, true);
                
                if ( method_exists($entity, 'objectImport') ){
                    $entity->objectImport($content);
                }

                app::$entityManager->persist($entity);
                app::$entityManager->flush($entity);

                if ($entity instanceof \sa\files\saFile) {
                    /** @var \sa\files\saFile $entity */
                    file_put_contents( app::get()->getConfiguration()->get('uploadsDir')->getValue().'/'.$entity->getDiskFileName() , base64_decode( $content['binary'] ) );
                }

                /**
                 * map the uuid
                 */
                ioc::getRepository('saEntityUuid')->getUuid($entityName, $entity->getId(), $idProxy->getUuid());
                $count++;
            }

        }

        return $count;
    }


    public static function link($dataLocation) {

        $data_files = static::rsearch($dataLocation, '/(.+)\.saobj/');

        $count = 0;

        /**
         * Loop through each data file
         */
        foreach($data_files as $file) {

            $content = $content_relationship =  unserialize(file_get_contents($file));
            $entityName = '\\'.$content['entity_name'];

            $metaData = app::$entityManager->getClassMetadata($entityName);
            $entityId = reset($metaData->getIdentifier());

            $idProxy = $content[ $entityId ];

            $uuid = ioc::getRepository('saEntityUuid')->getUuid( $entityName, null, $idProxy->getUuid(), false );
            $entity = ioc::get($entityName, array( 'id'=>$uuid->getEntityId() ) );


            foreach($content_relationship as $field=>$value) {
                if ($content_relationship[$field] instanceof \sa\system\saEntityUuid && $entityId!=$field ) {


                    $relation_uuid = ioc::getRepository('saEntityUuid')->getUuid( '\\'.$value->getName(), null, $value->getUuid(), false );
                    if (!$relation_uuid)
                        continue;

                    $relation_entity = ioc::get('\\'.$value->getName(), array( 'id'=>$relation_uuid->getEntityId() ) );
                    if (!$relation_entity)
                        continue;

                    $setMethodName = stringUtils::camelCasing('set_'.$field);
                    if ( method_exists($entity, $setMethodName) ) {
                        $entity->$setMethodName($relation_entity);
                        $count++;
                    }

                }
            }
            
            if ( method_exists($entity, 'objectImportLink') ){
                $entity->objectImportLink($content);
            }

        }

        app::$entityManager->flush();

        return $count;

    }

    public static function rsearch($folder, $pattern) {
        
        if (!file_exists($folder)) {
            return array();
        }
        
        $dir = new \RecursiveDirectoryIterator($folder);
        $ite = new \RecursiveIteratorIterator($dir);
        $files = new \RegexIterator($ite, $pattern, \RegexIterator::GET_MATCH);
        $fileList = array();
        foreach($files as $file) {
            $fileList[] = $file[0];
        }
        return $fileList;
    }

}
