<?php

namespace App\Entity\Sax\System;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'sa_cities')]
#[Index(name: 'IDX_system_city_name', columns: ['name'])]
#[Entity(repositoryClass: 'saCityRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
class saCity
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string')]
    protected $name;

    #[ManyToOne(targetEntity: saCounty::class, cascade: ['persist'], inversedBy: 'cities')]
    protected $county;

    #[OneToMany(mappedBy: 'city', targetEntity: 'saPostalCode', cascade: ['persist'])]
    protected $postal_codes;

    #[ManyToOne(targetEntity: 'saState', inversedBy: 'cities')]
    protected $state;

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
     * @return saStates
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
     * Constructor
     */
    public function __construct()
    {
        $this->postal_codes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set county
     *
     * @param  \sa\system\saCounty  $county
     * @return saCity
     */
    public function setCounty(\sa\system\saCounty $county = null)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return \sa\system\saCounty
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Add postalCode
     *
     *
     * @return saCity
     */
    public function addPostalCode(\sa\system\saPostalCode $postalCode)
    {
        $this->postal_codes[] = $postalCode;

        return $this;
    }

    /**
     * Remove postalCode
     */
    public function removePostalCode(\sa\system\saPostalCode $postalCode)
    {
        $this->postal_codes->removeElement($postalCode);
    }

    /**
     * Get postalCodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPostalCodes()
    {
        return $this->postal_codes;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param  mixed  $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
