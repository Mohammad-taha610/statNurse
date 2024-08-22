<?php
namespace sa\system;

/**
 * @Entity(repositoryClass="saEntityUuidRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @Table(name="sa_entity_uuid",indexes={@Index(name="IDX_system_entity_name", columns={"name"})})
 */
class saEntityUuid  {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $name;

    /** @Column(type="string") */
    protected $uuid;

    /** @Column(type="string") */
    protected $entity_id;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
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
     * @param string $uuid
     *
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
     * @param string $entityId
     *
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
