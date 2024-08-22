<?php
namespace sa\api;

use sacore\application\app;
use \sacore\application\controller;
use sacore\application\Event;
use sacore\application\Exception;
use sacore\application\ioc;
use sa\system\Revision;
use sa\system\saAuth;
use sa\system\saRevisionRepository;
use sacore\utilities\doctrineUtils;
use sacore\utilities\stringUtils;
use sacore\utilities\url;

class apiMobileController extends apiController
{

    public function __construct()
    {
        parent::__construct();
        $this->enable_defaults_api_endpoints = false;
    }


    public function sync() {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 1800);

        $jsonArray = $this->api->getJsonPostDataAsArray();

        /**
         * The POST data is empty or missing required top-level key
         * there are some basic fields
         * required for the sync process
         */
        if(sizeof($jsonArray) == 0 ||
            !isset($jsonArray['change_set']) ||
            !isset($jsonArray['scope'])
        ) {
            throw new ApiAuthException('Unauthorized - Missing Scope and Change Set', 401);
        }


        /** @var array $entityScope - Requested  */
        $entityScope = $jsonArray['scope'];
        /** @var array $permittedScope - Permitted */
        $permittedScope = $this->api_key->getEntityScope();

        /**
         * Client has informed us that there are no entities
         * that it wishes to sync, so there's nothing to do.
         * This problems stems from an issue on the client
         */
        if(sizeof($entityScope) == 0) {
            throw new ApiException('Not Modified - No Scope Provided', 304);
        } else {
            foreach($entityScope as $entity) {
                if(!in_array($entity, $permittedScope)) {
                    throw new ApiAuthException('Unauthorized - Modification of $entity not allowed', 401);
                }
            }
        }

        if(!empty($jsonArray['last_sync'])) {
            // Get Changes since last sync date

            /** @var  $changesForClient */
            $changesForClient = self::buildRevisionData($entityScope, $jsonArray['last_sync']);
        } else {
            // Get All Entity Data for scope in current state
            $changesForClient = self::buildRevisionData($entityScope, null);
        }

        /**
         * The client has sent changes to the server
         * to be persisted, handle that here
         */
        if(sizeof($jsonArray['change_set']) > 0) {
            $entityMap = array();

            foreach ($jsonArray['change_set'] as $entityName => $entityChangeSet) {
                if (!in_array($entityName, $permittedScope)) { continue; }
                if (empty($entityChangeSet)) { continue; }

                foreach ($entityChangeSet as $entityArray) {
                    $trackingUuid = $entityArray["tracking_uuid"];
                    $id = null;

                    /**
                     * Determine if entity has previously
                     * been persisted, and if it has, return that ID
                     * to be sent back to the client. This can happen
                     * if the client never receives a response due to
                     * connection issues, etc.
                     */
                    if (!empty($trackingUuid)) {
                        /** @var ApiUuid $apiUuidEntry */
                        $apiUuidEntry = app::$entityManager->getRepository(ioc::staticGet('ApiUuid'))->findOneBy(array('entityClass' => $entityName, 'uuid' => $trackingUuid));

                        if ($apiUuidEntry) {
                            $id = $apiUuidEntry->getEntityId();
                        } else {
                            $id = $entityArray['id'] > 0 ? $entityArray['id'] : null;
                        }
                    } else {
                        $id = $entityArray['id'] > 0 ? $entityArray['id'] : null;
                    }

                    /**
                     * Returns the entity object for setting data.
                     * if the entity already exists, it is returned,
                     * otherwise a new instance of the entity is returned.
                     */
                    if ($id != null && $id != 0) {
                        $entityObj = app::$entityManager->getRepository( ioc::staticGet($entityName) )->findOneBy(array('id' => $id));
                    } else {
                        $entityObj = ioc::resolve($entityName);
                    }

                    $entityObj = static::setEntityDataForApi($entityArray, $entityObj, true);
                    $entityIdentifier = $entityObj->getId() ? $entityObj->getId() : $trackingUuid;
                    $entityMap[$entityIdentifier] = $entityObj;
                }
            }

            foreach($jsonArray['change_set'] as $entityName => $entityChangeSet) {
                foreach ($entityChangeSet as $entityArray) {
                    if (!in_array($entityName, $permittedScope)) {
                        continue;
                    }
                    if (empty($entityChangeSet)) {
                        continue;
                    }

                    $entityIdentifier = !empty($entityArray['id']) && $entityArray['id'] > 0 ? $entityArray['id'] : $entityArray['tracking_uuid'];

                    $entityObj = $entityMap[$entityIdentifier];

                    $classPathParts = explode("\\", get_class($entityObj));
                    $className = $classPathParts[count($classPathParts) - 1];
                    $entityMetaData = app::$entityManager->getClassMetadata(ioc::staticResolve($className));
                    $entityAssociationMap = $entityMetaData->getAssociationMappings();

                    foreach ($entityAssociationMap as $property => $association) {
                        if (!array_key_exists($property, $entityArray)) { continue; }

                        $propertyData = $entityArray[$property];
                        $targetEntities = array();

                        if(is_array($propertyData) && !array_key_exists('target', $propertyData) && !array_key_exists('value', $propertyData)) {
                            foreach($propertyData as $index => $singleAssoc) {
                                $classPathParts = explode('\\', $association['targetEntity']);
                                $className = $classPathParts[count($classPathParts) - 1];

                                if($singleAssoc['type'] == 'uuid') {
                                    $singleTarget = $entityMap[$singleAssoc['value']];
                                } else {
                                    $singleTarget = app::$entityManager->getRepository( ioc::staticGet($className) )->findOneBy( array('id' => $singleAssoc['value']) );
                                }

                                if($singleTarget) {
                                    $targetEntities[] = $singleTarget;
                                }
                            }
                        } else {
                            $classPathParts = explode("\\", $association['targetEntity']);
                            $className = $classPathParts[count($classPathParts) - 1];

                            if($propertyData['type'] == 'uuid') {
                                $singleTarget = $entityMap[$propertyData['value']];
                            } else {
                                $singleTarget = app::$entityManager->getRepository( ioc::staticGet($className) )->findOneBy(array('id' => $propertyData['value']));
                            }

                            if($singleTarget) {
                                $targetEntities[] = $singleTarget;
                            }
                        }

                        // Get setter & add method names
                        $setMethodName = stringUtils::camelCasing('set_' . $property);
                        $addMethodName = stringUtils::camelCasing('add_' . $property);

                        if(count($targetEntities) > 0) {
                            foreach($targetEntities as $entity) {
                                if(method_exists($entityObj, $setMethodName)) {
                                    $entityObj->$setMethodName($entity);
                                } else if(method_exists($entityObj, $addMethodName)) {
                                    $entityObj->$addMethodName($entity);
                                }
                            }
                        }
                    }
                }
            }

            if(count($entityMap) > 0) {
                foreach($entityMap as $identifier => $entityObject) {
                    app::$entityManager->persist($entityObject);
                }
                app::$entityManager->flush();

                foreach($entityMap as $identifier => $entityObject) {
                    $classPathParts = explode("\\", get_class($entityObject));
                    $className = $classPathParts[count($classPathParts) - 1];

                    if(!is_numeric($identifier)) {
                        /** @var ApiUuid $apiUuidEntry */
                        $apiUuidEntry = ioc::resolve('ApiUuid');

                        $apiUuidEntry->setUuid($identifier);
                        $apiUuidEntry->setEntityId($entityObject->getId());
                        $apiUuidEntry->setEntityClass($className);
                        app::$entityManager->persist($apiUuidEntry);
                    }

                    $entityArray = doctrineUtils::getEntityArray($entityObject, true);

                    if(!is_numeric($identifier)) {
                        $entityArray['tracking_uuid'] = $identifier;
                    }

                    $changesForClient[$className][] = $entityArray;
                }

                app::$entityManager->flush();
            }
        }

        $returnArray = $this->api->bldSuccessArray();
        $returnArray['change_set'] = $changesForClient;

        if(count($returnArray['change_set']) > 0) {
            Event::fire('api.sync.notify', $this->api_key);
        }

        $returnArray['completed_time'] = time();
        return $returnArray;
    }

    /**
     * Build array representation of all changes for an entity scope since
     * time specified in SERVER epoch time
     *
     * @param array $scope - Entity Scope to check against for new revisions
     * @param integer $epochTime - Unix Epoch time since last sync date
     *
     * @return array - an array of entities with their associated revision history
     */
    private function buildRevisionData(array $scope, $epochTime = null) {
        $entityChangeSet = array();

        /** @var saRevisionRepository $revisionRepo */
        $revisionRepo = app::$entityManager->getRepository( ioc::staticGet('Revision') );

        foreach($scope as $entityName) {
            $class = ioc::staticResolve($entityName);
            $refClass = new \ReflectionClass($class);
            $name = $refClass->getName();

            if($epochTime != null) {
                // We'll need a current state of all entities that have changed since date
                $revisionHistory = $revisionRepo->getRevisionsSinceEpochDate($epochTime, $name);
                if(!$revisionHistory) { continue; }

                $changeSet = array();

                /** @var Revision $revision */
                foreach($revisionHistory as $revision) {
                    $currentEntityState = app::$entityManager->getRepository( ioc::staticResolve($refClass->getShortName()) )->findOneBy(array('id' => $revision->getEntityId()));
                    if($currentEntityState) {
                        $changeSet[] = $currentEntityState;
                    }
                }

            } else {
                $changeQuery = ioc::getRepository($entityName)->createQueryBuilder('q');
                $iterator = $changeQuery->getQuery()->iterate();

                $batchSize = 500;
                $i = 0;
                
                foreach($iterator as $row) {
                    $iteratedEntity = $row[0];
                    
                    $entityChangeSet[$entityName][] = doctrineUtils::getEntityArray($iteratedEntity);
                   
                    if(($i % $batchSize) == 0) {
                        app::$entityManager->clear();
                    }
                
                    ++$i;
                }
                
                app::$entityManager->flush();
            }
        }

        return $entityChangeSet;
    }

    /**
     * Build an array consisting of revision history for
     * given entity
     *
     * @param $entityCollection
     * @return array - Array consisting of revisions for given entity name
     * @throws Exception
     */
    private function buildChangeSetArray($entityCollection) {
        $returnArray = array();

        foreach($entityCollection as $entity) {
            if(!is_array($entity)) {
                $entityAsArray = doctrineUtils::getEntityArray($entity, true);
                $returnArray[] = $entityAsArray;
            }
        }

        return $returnArray;
    }



    private function setEntityDataForApi($propertyMap, $entity, $useBlankFields = false)
    {
        // get class name to get class meta data
        $classPathParts = explode("\\", get_class($entity));
        $className = $classPathParts[count($classPathParts) - 1];
        $classMetaData = app::$entityManager->getClassMetadata(ioc::staticResolve('\\' . get_class($entity)));

        foreach ($propertyMap as $property => $value) {
            // create the set method name
            $methodName = stringUtils::camelCasing('set_' . $property);
            $getMethodName = stringUtils::camelCasing('get_' . $property);

            if (method_exists($entity, $methodName) && (!stringUtils::isBlank($value) || $useBlankFields) && empty($classMetaData->getAssociationMappings()[$property]['targetEntity'])) {
                // Check for properties we may need to do some conversion on before calling the set method
                if (array_key_exists($property, $classMetaData->fieldMappings)) {
                    $fieldMap = $classMetaData->getFieldMapping($property);

                    if ($fieldMap['type'] == "date" || $fieldMap['type'] == "datetime") {

                        $time = strtotime($value);
                        $datetime = new \sacore\application\DateTime();

                        $datetime->setTimestamp($time);
                        $value = $datetime;
                    }
                }
                // finally call the setter
                $entity->$methodName($value);
            } else {
                $associationMap = $classMetaData->getAssociationMappings();
                /**
                 * Look at entity Associations to determine if value
                 * in property map matches an association rather
                 * than a field
                 */
                foreach ($associationMap as $propertyName => $data) {
                    $targetEntityReflection = new \ReflectionClass($data['targetEntity']);
                    $lowerShortName = strtolower($targetEntityReflection->getShortName());

                    if (strtolower($property) != $lowerShortName) {
                        continue;
                    }

                    try {
                        if(is_array($value)) { continue; }

                        $targetEntityFromId = app::$entityManager->getRepository(ioc::staticResolve($targetEntityReflection->getShortName()))->find($value);

                        // Target entity not found
                        if (!$targetEntityFromId) {
                            continue;
                        }

                        // Get setter & add method names
                        $setMethodName = stringUtils::camelCasing('set_' . $property);
                        $addMethodName = stringUtils::camelCasing('add_' . $property);

                        /**
                         * Invoke setter/add method for entity to add the desired
                         * matched entity to the source entity
                         *
                         * Entity should only contain either a setter for the property, or an add function,
                         * depending on relationship between associated entities.
                         */
                        if (method_exists($entity, $addMethodName) && (!stringUtils::isBlank($value) || $useBlankFields)) {
                            $entity->$addMethodName($targetEntityFromId);
                        } else if (method_exists($entity, $setMethodName) && (!stringUtils::isBlank($value) || $useBlankFields)) {
                            $entity->$setMethodName($targetEntityFromId);
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $entity;
    }
}