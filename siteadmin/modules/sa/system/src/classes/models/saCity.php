<?php
namespace sa\system;

/**
 * @Entity(repositoryClass="saCityRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="sa_cities",indexes={@Index(name="IDX_system_city_name", columns={"name"})})
 */
class saCity  {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $name;

    /** @ManyToOne(targetEntity="saCounty", inversedBy="cities", cascade={"persist"}) */
    protected $county;

    /** @OneToMany(targetEntity="saPostalCode", mappedBy="city", cascade={"persist"}) */
    protected $postal_codes;

    /** @ManyToOne(targetEntity="saState", inversedBy="cities") */
    protected $state;

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
        $this->postal_codes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set county
     *
     * @param \sa\system\saCounty $county
     *
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
     * @param \sa\system\saPostalCode $postalCode
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
     *
     * @param \sa\system\saPostalCode $postalCode
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
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
