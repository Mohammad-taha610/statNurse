<?php

namespace sa\api;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="apiKeyRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="sa_api_keys")
 */
class ApiKey {

    const TYPE_FULL = 'f';
    const TYPE_MEMBER = 'm';

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string", unique=true) */
    protected $api_key;

    /** @Column(type="string", unique=true) */
    protected $client_id;

    /** @Column(type="string") */
    protected $platform;

    /** @Column(type="string") */
    protected $type;

    /** @Column(type="boolean") */
    protected $is_active;

    /** @Column(type="array") */
    protected $entity_scope;

    /**
     * @return
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param mixed $api_key
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param mixed $client_id
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param mixed $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @param mixed $is_active
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
    }

    /**
     * @return mixed
     */
    public function getEntityScope()
    {
        return $this->entity_scope;
    }

    /**
     * @param array $entity_scope
     */
    public function setEntityScope($entity_scope)
    {
        $this->entity_scope = $entity_scope;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ApiKey
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
