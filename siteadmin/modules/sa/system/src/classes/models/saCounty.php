<?php
namespace sa\system;

/**
 * @Entity
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="sa_counties",indexes={@Index(name="IDX_system_county_name", columns={"name"})})
 */
class saCounty  {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $name;

    /** @ManyToOne(targetEntity="saState", inversedBy="counties", fetch="EAGER", cascade={"persist"}) */
    protected $state;

    /** @OneToMany(targetEntity="saCity", mappedBy="county", cascade={"persist"}) */
    protected $cities;


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
     * @param \sa\system\saState $state
     *
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
     * @param \sa\system\saCity $city
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
     *
     * @param \sa\system\saCity $city
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
