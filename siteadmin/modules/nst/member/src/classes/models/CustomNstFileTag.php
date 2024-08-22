<?php

namespace nst\member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;

/**
 * @Entity(repositoryClass="CustomNstFileTagRepository")
 * @InheritanceType("SINGLE_TABLE")
 */
class CustomNstFileTag
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
     * @var Provider $provider
     * @ManyToOne(targetEntity="\nst\member\Provider", inversedBy="custom_tags")
     */
    protected $provider;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CustomNstFileTag
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
     * @return CustomNstFileTag
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
     * @return CustomNstFileTag
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
     * @return CustomNstFileTag
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }
}