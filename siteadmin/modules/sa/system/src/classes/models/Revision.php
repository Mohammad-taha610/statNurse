<?php

namespace sa\system;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="saRevisionRepository")
 * @HasLifecycleCallbacks
 * @Table(name="sa_revisions", indexes={
 *      @Index(name="IDX_entity_name", columns={"entityName"}),
 *      @Index(name="IDX_entity_id", columns={"entityId"}),
 *      @Index(name="IDX_revision_number", columns={"revisionNumber"})
 * })
 */

class Revision {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="string") */
    protected $entityName;
    /** @Column(type="integer") */
    protected $entityId;
    /** @Column(type="datetime") */
    protected $revisionDate;
    /** @Column(type="array") */
    protected $changeSet;
    /** @Column(type="string") */
    protected $revisionType;
    /** @Column(type="integer") */
    protected $revisionNumber;
    /** @Column(type="integer", nullable=true) */
    protected $user_id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getRevisionDate()
    {
        return $this->revisionDate;
    }

    /**
     * @param mixed $revisionDate
     */
    public function setRevisionDate($revisionDate)
    {
        $this->revisionDate = $revisionDate;
    }

    /**
     * @return mixed
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @param mixed $changeSet
     */
    public function setChangeSet($changeSet)
    {
        $this->changeSet = $changeSet;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return mixed
     */
    public function getRevisionType()
    {
        return $this->revisionType;
    }

    /**
     * @param mixed $revisionType
     */
    public function setRevisionType($revisionType)
    {
        $this->revisionType = $revisionType;
    }

    /**
     * @return mixed
     */
    public function getRevisionNumber()
    {
        return $this->revisionNumber;
    }

    /**
     * @param mixed $revisionNumber
     */
    public function setRevisionNumber($revisionNumber)
    {
        $this->revisionNumber = $revisionNumber;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
}