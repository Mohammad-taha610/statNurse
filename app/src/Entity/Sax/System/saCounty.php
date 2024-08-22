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

#[Entity(repositoryClass: 'saCountyRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[Table(name: 'sa_counties')]
#[Index(columns: ['name'], name: 'IDX_system_county_name')]
class saCounty
{
    #[Id]
    #[GeneratedValue(strategy: 'AUTO')]
    #[Column(type: 'integer')]
    protected int $id;

    #[Column(type: 'string')]
    protected string $name;

    #[ManyToOne(targetEntity: saState::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'counties')]
    protected $state;

    // OneToMany(targetEntity="saCity", mappedBy="county", cascade={"persist"})
    #[OneToMany(mappedBy: 'county', targetEntity: saCity::class, cascade: ['persist'])]
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
        $this->cities = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set state
     *
     * @param  \sa\system\saState  $state
     * @return saCounty
     */
    public function setState(\sa\system\saState $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \sa\system\saState
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Add city
     *
     *
     * @return saCounty
     */
    public function addCity(\sa\system\saCity $city)
    {
        $this->cities[] = $city;

        return $this;
    }

    /**
     * Remove city
     */
    public function removeCity(\sa\system\saCity $city)
    {
        $this->cities->removeElement($city);
    }

    /**
     * Get cities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCities()
    {
        return $this->cities;
    }
}
