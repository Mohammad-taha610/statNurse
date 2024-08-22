<?php

namespace sa\system;

use Doctrine\Common\EventArgs;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;

class DoctrineRevisionManager {
    
    /** @var TrackedRevisionEntity[] */
    protected $trackedEntities;
    /** @var EntityManager */
    protected $entityManager = null;
    /** @var Connection */
    protected $connection = null;
    /** @var UnitOfWork */
    protected $unitOfWork = null;
    /** @var AbstractPlatform */
    protected $platform = null;
    /** @var AbstractSchemaManager */
    protected $schemaManager = null;
    /** @var array $revisions */
    protected $revisions = array();

    const CREATE_TAG = 'CREATE';
    const UPDATE_TAG = 'UPDATE';
    const REMOVE_TAG = 'DELETE';

    private static $postFlushFired = false;

    /**
     * @param TrackedRevisionEntity[] $trackedEntities
     */
    public function __construct($trackedEntities = array())
    {
        $this->trackedEntities = $trackedEntities;
    }

    public function onFlush(OnFlushEventArgs $e)
    {
        $this->init($e);
    }

    public function postPersist(LifecycleEventArgs $e)
    {
        $entity = $e->getEntity();
        $tracked = $this->isEntityTrackedForRevisions($entity);
        
        if(!$tracked) {
            return;
        }

        $classData = $this->entityManager->getClassMetadata(get_class($entity));
        $id = reset($classData->getIdentifierValues($entity));
        
        $this->generateEntityRevision($id, $classData->name, array(), self::CREATE_TAG);
    }

    public function postUpdate(LifecycleEventArgs $e)
    {
        $entity = $e->getEntity();
        $tracked = $this->isEntityTrackedForRevisions($entity);
        
        if(!$tracked) {
            return;
        }

        $class = $this->entityManager->getClassMetadata(get_class($entity));
        $entityChangeSet = $this->unitOfWork->getEntityChangeSet($entity);
        $id = reset($class->getIdentifierValues($entity));

        // The entity hasn't changed
        if(sizeof($entityChangeSet) == 0) {
            return;
        }

        $ignoredColumns = $this->getIgnoredColumnsForEntity($entity);
        
        // Remove any excluded columns from the revision data if it is set to be ignored
        foreach($ignoredColumns as $columnName) {
            if (isset($entityChangeSet[$columnName])) {
                unset($entityChangeSet[$columnName]);
            }
        }

        if(!count($entityChangeSet)) {
            return;
        }

        // Save revision information to the database
        $this->generateEntityRevision($id, $class->name, $entityChangeSet, self::UPDATE_TAG);
    }

    public function preRemove(LifecycleEventArgs $e)
    {
        $this->init($e);
        $entity = $e->getEntity();
        $tracked = $this->isEntityTrackedForRevisions($entity);
        
        if(!$tracked) {
            return;
        }

        $class = $this->entityManager->getClassMetadata(get_class($entity));
        $entityChangeSet = $this->unitOfWork->getEntityChangeSet($entity);
        $id = reset($class->getIdentifierValues($entity));

        // Save revision information to the database
        $this->generateEntityRevision($id, $class->name, $entityChangeSet, self::REMOVE_TAG);
    }

    /**
     * @param $id
     * @param $name
     * @param $changeSet
     * @param string $revType
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\IocException
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    public function generateEntityRevision($id, $name, $changeSet, $revType) {
        self::$postFlushFired = false;

        /** @var Revision $revision */
        $revision = ioc::resolve('Revision');

        $revision->setEntityId($id);

        // ioc::staticGet() will prepend a '\' to the class namespace. That character must prefix
        // the entityName for it to be queried with findBy(ioc::staticGet(...)).
        $revision->setEntityName('\\' . $name);
        $revision->setChangeSet($changeSet);
        $revision->setRevisionType($revType);
        $revision->setRevisionDate(new DateTime('now'));

        if (!app::getInstance()->isCommandLineStarted()) {
            // If user id is available for change set,
            // add it to the revision
            $userId = modRequest::request('auth.user_id');

            if($userId) {
                $revision->setUserId($userId);
            }
        }

        /** @var saRevisionRepository $revisionRepo */
        $revisionRepo = app::$entityManager->getRepository( ioc::staticResolve('Revision') );
        $lastRevision = $revisionRepo->getLastRevisionNumber($name, $id);

        if($lastRevision['revisionNumber']) {
            $revision->setRevisionNumber($lastRevision['revisionNumber'] + 1);
        } else {
            $revision->setRevisionNumber(1);
        }

        $this->revisions[] = $revision;
    }

    protected function init(EventArgs $e) {
        $this->entityManager = app::$entityManager;
        $this->connection = $this->entityManager->getConnection();
        $this->unitOfWork = $this->entityManager->getUnitOfWork();
        $this->platform = $this->connection->getDatabasePlatform();
        $this->schemaManager = $this->connection->getSchemaManager();
    }

    public function postFlush(PostFlushEventArgs $e) {
        if(self::$postFlushFired) {
            return;
        }

        if(sizeof($this->revisions) > 0) {
            foreach($this->revisions as &$revision) {
                $this->entityManager->persist($revision);
            }
        }

        self::$postFlushFired = true;
        $this->entityManager->flush();
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isEntityTrackedForRevisions($entity) {
        $tracked = false;
        
        if($entity instanceof Revision) {
            return $tracked;
        }
        
        foreach($this->trackedEntities as $trackedEntity) {
            if(is_a($entity, $trackedEntity->getClassName(), true)) {
                $tracked = true;
            }
        }
        
        return $tracked;
    }

    /**
     * @param object $entity
     * @return array
     */
    protected function getIgnoredColumnsForEntity($entity) {
        $ignoredColumns = array();
        
        /** @var TrackedRevisionEntity $trackedEntity */
        foreach($this->trackedEntities as $trackedEntity) {
            if(is_a($entity, $trackedEntity->getClassName(), true)) {
                $ignoredColumns = $trackedEntity->getIgnoredFields();
            }
        }
        
        return $ignoredColumns;
    }
}