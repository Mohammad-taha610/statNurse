<?php
namespace sa\system;

/**
 * @Entity(repositoryClass="saPostalCodeRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="sa_postal_codes",indexes={@Index(name="IDX_system_postal_code", columns={"code"})})
 */
class saPostalCode  {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $code;

    /** @Column(type="float", nullable=true) */
    protected $latitude;

    /** @Column(type="float", nullable=true) */
    protected $longitude;

    /** @ManyToOne(targetEntity="saCity", inversedBy="postal_codes") */
    protected $city;

    /** @ManyToOne(targetEntity="saState", inversedBy="postal_codes") */
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
     * Set latitude
     *
     * @param string $latitude
     *
     * @return saPostalCode
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return saPostalCode
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set city
     *
     * @param \sa\system\saCity $city
     *
     * @return saPostalCode
     */
    public function setCity(\sa\system\saCity $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \sa\system\saCity
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return saPostalCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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

    /**
     * Uses the haversine formula
     * https://en.wikipedia.org/wiki/Haversine_formula
     *
     * Returns the distance between two postal codes in metres
     *
     * @param saPostalCode $to  The postal code to calculate the distance to from this postal code
     * @return float    The distance between the two postal codes in metres
     */
    public function getDistance(saPostalCode $to) {
        $from = $this;

        $R = 6371000; // Earths radius in metres

        // angle radians
        $φ1 = deg2rad($from->latitude);
        $φ2 = deg2rad($to->latitude);

        // deltas
        $Δφ = deg2rad($to->latitude-$from->latitude);
        $Δλ = deg2rad($to->longitude-$from->longitude);

        $a = sin($Δφ/2) * sin($Δφ/2) +
            cos($φ1)   * cos($φ2) *
            sin($Δλ/2) * sin($Δλ/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        $d = $R * $c;

        return $d;
    }
}
