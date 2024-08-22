<?php

namespace nst\member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;

/**
 * @Entity(repositoryClass="NstFileTagRepository")
 * @InheritanceType("SINGLE_TABLE")
 */
class NstFileTag
{
    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;
    /**
     * @var string $name
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var string $description
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @var string $type
     * @Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * @var boolean $show_in_provider_portal
     * @Column(type="boolean", nullable=true)
     */
    protected $show_in_provider_portal;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return NstFileTag
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return NstFileTag
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return NstFileTag
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return NstFileTag
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function getShowInProviderPortal()
    {
        return $this->show_in_provider_portal;
    }

    /**
     * @param bool $show_in_provider_portal
     * @return NstFileTag
     */
    public function setShowInProviderPortal($to_show_in_provider_portal)
    {
        $this->show_in_provider_portal = $to_show_in_provider_portal;
        return $this;
    }
}