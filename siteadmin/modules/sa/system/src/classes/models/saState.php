<?php
namespace sa\system;

/**
 * @Entity(repositoryClass="saStateRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="sa_states",indexes={@Index(name="IDX_system_state_abbreviation", columns={"abbreviation"})})
 */
class saState  {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $abbreviation;

    /** @Column(type="string") */
    protected $name;

    /** @ManyToOne(targetEntity="saCountry", inversedBy="states", cascade={"persist"}) */
    protected $country;

    /** @OneToMany(targetEntity="saCounty", mappedBy="state", cascade={"persist"}) */
    protected $counties;

    /** @OneToMany(targetEntity="saPostalCode", mappedBy="city", cascade={"persist"}) */
    protected $postal_codes;

    /** @OneToMany(targetEntity="saCity", mappedBy="state") */
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
     * Set abbreviation
     *
     * @param string $abbreviation
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
        $this->counties = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set country
     *
     * @param \sa\system\saCountry $country
     *
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
     * @param \sa\system\saCounty $county
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
     *
     * @param \sa\system\saCounty $county
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
     * @param mixed $cities
     */
    public function setCities($cities)
    {
        $this->cities = $cities;
    }

    /**
     * Add city
     *
     * @param saCity $city
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
     * @param saCity $city
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
     * @param mixed $postal_codes
     */
    public function setPostalCodes($postal_codes)
    {
        $this->postal_codes = $postal_codes;
    }

    /**
     * Add Postal Code
     *
     * @param saPostalCode $postalCode
     * @return saState
     *
     */
    public function addPostalCode(\sa\system\saPostalCode $postalCode)
    {
        $this->postal_codes[] = $postalCode;

        return $this;
    }

    /**
     * Remove PostalCode
     *
     * @param saPostalCode $postalCode
     */
    public function removePostalCode(\sa\system\saPostalCode $postalCode)
    {
        $this->counties->removeElement($postalCode);
    }
}
