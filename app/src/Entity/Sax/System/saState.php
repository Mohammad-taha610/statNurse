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

#[Table(name: 'sa_states')]
#[Index(name: 'IDX_system_state_abbreviation', columns: ['abbreviation'])]
#[Entity(repositoryClass: 'saStateRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
class saState
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string')]
    protected $abbreviation;

    #[Column(type: 'string')]
    protected $name;

    #[ManyToOne(targetEntity: saCountry::class, cascade: ['persist'], inversedBy: 'states')]
    protected $country;

    #[OneToMany(mappedBy: 'state', targetEntity: saCounty::class, cascade: ['persist'])]
    protected $counties;

    #[OneToMany(mappedBy: 'state', targetEntity: saPostalCode::class, cascade: ['persist'])]
    protected $postal_codes;

    #[OneToMany(mappedBy: 'state', targetEntity: saCity::class, cascade: ['persist'])]
    protected $cities;

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
     * Set abbreviation
     *
     * @param  string  $abbreviation
     * @return saStates
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    /**
     * Get abbreviation
     *
     * @return string
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
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
        $this->counties = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set country
     *
     * @param  \sa\system\saCountry  $country
     * @return saState
     */
    public function setCountry(\sa\system\saCountry $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \sa\system\saCountry
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Add county
     *
     *
     * @return saState
     */
    public function addCounty(\sa\system\saCounty $county)
    {
        $this->counties[] = $county;

        return $this;
    }

    /**
     * Remove county
     */
    public function removeCounty(\sa\system\saCounty $county)
    {
        $this->counties->removeElement($county);
    }

    /**
     * Get counties
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCounties()
    {
        return $this->counties;
    }

    /**
     * @return mixed
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @param  mixed  $cities
     */
    public function setCities($cities)
    {
        $this->cities = $cities;
    }

    /**
     * Add city
     *
     * @param  saCity  $city
     * @return saState
     */
    public function addCity(\sa\system\saCity $city)
    {
        $this->cities[] = $city;

        return $this;
    }

    /**
     * Remove city
     *
     * @param  saCity  $city
     */
    public function removeCity(\sa\system\saCity $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * @return mixed
     */
    public function getPostalCodes()
    {
        return $this->postal_codes;
    }

    /**
     * @param  mixed  $postal_codes
     */
    public function setPostalCodes($postal_codes)
    {
        $this->postal_codes = $postal_codes;
    }

    /**
     * Add Postal Code
     *
     * @param  saPostalCode  $postalCode
     * @return saState
     */
    public function addPostalCode(\sa\system\saPostalCode $postalCode)
    {
        $this->postal_codes[] = $postalCode;

        return $this;
    }

    /**
     * Remove PostalCode
     *
     * @param  saPostalCode  $postalCode
     */
    public function removePostalCode(\sa\system\saPostalCode $postalCode)
    {
        $this->counties->removeElement($postalCode);
    }
}
