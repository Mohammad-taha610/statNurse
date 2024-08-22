<?php

namespace App\Entity\Sax\System;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: 'saEntityUuidRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[Table(name: 'sa_entity_uuid')]
#[Index(columns: ['name'], name: 'IDX_system_entity_name')]
class saEntityUuid
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[Column(type: 'string')]
    protected $name;

    #[Column(type: 'string')]
    protected $uuid;

    #[Column(type: 'string')]
    protected $entity_id;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return saEntityUuid
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set uuid
     *
     * @param  string  $uuid
     * @return saEntityUuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set entityId
     *
     * @param  string  $entityId
     * @return saEntityUuid
     */
    public function setEntityId($entityId)
    {
        $this->entity_id = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entity_id;
    }
}
