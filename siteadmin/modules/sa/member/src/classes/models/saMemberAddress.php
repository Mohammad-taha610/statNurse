<?php
namespace sa\member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use sacore\application\ioc;
use sacore\application\ValidateException;
use sa\system\saState;
use sacore\utilities\fieldValidation;

/**
 * @Entity(repositoryClass="sa\member\saMemberAddressRepo")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="sa_member_address")
 */
class saMemberAddress
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="string", nullable=true) */
    protected $name;
    /** @Column(type="string", nullable=true) */
    protected $street_one;
    /** @Column(type="string", nullable=true) */
    protected $street_two;
    /** @Column(type="string", nullable=true) */
    protected $city;

    /** @Column(type="string", nullable=true) */
    protected $state;
    /** @Column(type="string", nullable=true) */
    protected $country;

    /** @OneToOne(targetEntity="\sa\system\saState") */
    protected $state_object;
    /** @OneToOne(targetEntity="\sa\system\saCountry") */
    protected $country_object;

    /** @Column(type="string", nullable=true) */
    protected $postal_code;
    /** @Column(type="string", nullable=true) */
    protected $type;
    /** @Column(type="boolean", nullable=true) */
    protected $is_primary;
    /** @Column(type="boolean", nullable=true) */
    protected $is_active;
    /** @Column(type="string", nullable=true) */
    protected $normalized_name;
    /** @ManyToOne(targetEntity="saMember", inversedBy="addresses") */
    protected $member;

    /** @Column(type="float", nullable=true) */
    protected $latitude;
    /** @Column(type="float", nullable=true) */
    protected $longitude;

    /**
     * @PrePersist @PreUpdate
     */
    public function validate()
    {
        $fv = new fieldValidation();
        //$fv->isNotEmpty($this->name, 'Please enter the name.');
        $fv->isNotEmpty($this->street_one, 'Please enter a street.');
        $fv->isNotEmpty($this->city, 'Please enter a city.');
        $fv->isNotEmpty($this->state, 'Please enter a valid state.');
        $fv->isNotEmpty($this->postal_code, 'Please enter a postal code.');

        if ($fv->hasErrors()) {
            throw new ValidateException(implode('<br />', $fv->getErrors()));
        }

        $this->normalized_name = strtolower(preg_replace('/[^\da-z]/i', '', $this->street_one . $this->street_two . $this->city . $this->state . $this->postal_code));
    }


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
     * Set street_one
     *
     * @param string $streetOne
     * @return saMemberAddress
     */
    public function setStreetOne($streetOne)
    {
        $this->street_one = $streetOne;

        return $this;
    }

    /**
     * Get street_one
     *
     * @return string 
     */
    public function getStreetOne()
    {
        return $this->street_one;
    }

    /**
     * Set street_two
     *
     * @param string $streetTwo
     * @return saMemberAddress
     */
    public function setStreetTwo($streetTwo)
    {
        $this->street_two = $streetTwo;

        return $this;
    }

    /**
     * Get street_two
     *
     * @return string 
     */
    public function getStreetTwo()
    {
        return $this->street_two;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return saMemberAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set postal_code
     *
     * @param string $postalCode
     * @return saMemberAddress
     */
    public function setPostalCode($postalCode)
    {
        $this->postal_code = $postalCode;

        return $this;
    }

    /**
     * Get postal_code
     *
     * @return string 
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return saMemberAddress
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set is_primary
     *
     * @param boolean $isPrimary
     * @return saMemberAddress
     */
    public function setIsPrimary($isPrimary)
    {
        $this->is_primary = $isPrimary;

        return $this;
    }

    /**
     * Get is_primary
     *
     * @return boolean 
     */
    public function getIsPrimary()
    {
        if($this->is_primary == null) {
            return false;
        } else {
            return $this->is_primary;
        }
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return saMemberAddress
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set member
     *
     * @param \sa\member\saMember $member
     * @return saMemberAddress
     */
    public function setMember(\sa\member\saMember $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \sa\member\saMember 
     */
    public function getMember()
    {
        return $this->member;
    }

    public function toArray() {

        return get_object_vars($this);
    }

    /**
     * Set normalizedName
     *
     * @param string $normalizedName
     *
     * @return saMemberAddress
     */
    public function setNormalizedName($normalizedName)
    {
        $this->normalized_name = $normalizedName;

        return $this;
    }

    /**
     * Get normalizedName
     *
     * @return string
     */
    public function getNormalizedName()
    {
        $this->normalized_name = strtolower(preg_replace('/[^\da-z]/i', '', $this->street_one . $this->street_two . $this->city . $this->state . $this->postal_code));

        return $this->normalized_name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return saMemberAddress
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
     * Set stateObject
     *
     * @param \sa\system\saState $stateObject
     *
     * @return saMemberAddress
     */
    public function setStateObject(\sa\system\saState $stateObject = null)
    {
        $this->state_object = $stateObject;
        $this->state = $stateObject->getName();

        return $this;
    }

    /**
     * Get stateObject
     *
     * @return \sa\system\saState
     */
    public function getStateObject()
    {
        if (!$this->state_object) {

            /** @var saState $stateObj */
            $stateObj = ioc::getRepository('saState')->findStateByNameIdAbbr($this->state);
            if ($stateObj) {
                $this->state_object = $stateObj;
            }

        }

        return $this->state_object;
    }

    /**
     * Set countryObject
     *
     * @param \sa\system\saCountry $countryObject
     *
     * @return saMemberAddress
     */
    public function setCountryObject(\sa\system\saCountry $countryObject = null)
    {
        $this->country_object = $countryObject;
        $this->country = $countryObject->getName();

        return $this;
    }

    /**
     * Get countryObject
     *
     * @return \sa\system\saCountry
     */
    public function getCountryObject()
    {

        if (!$this->country_object) {
            /** @var saState $stateObj */
            $countryObj = ioc::getRepository('saCountry')->findCountryByNameIdAbbr($this->country);
            if ($countryObj) {
                $this->country_object = $countryObj;
            }

        }


        return $this->country_object;
    }

    /**
     * Set state
     *
     * @param $state
     * @return saMemberAddress
     */
    public function setState($state)
    {
        /** @var saState $stateObj */
        $stateObj = ioc::getRepository('saState')->findStateByNameIdAbbr($state);
        if ($stateObj) {
            $this->state = $stateObj->getName();
            $this->state_object = $stateObj;
        }
        else
        {
            $this->state = '';
        }

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        $state = $this->state;
        if(!$state)
            return null;

        /** @var saState $stateObj */
        $stateObj = ioc::getRepository('saState')->findStateByNameIdAbbr($this->state);
        if ($stateObj) {
            $state = $stateObj->getName();
        }

        return $state;
    }

    /**
     * Set country
     *
     * @param $country
     * @return saMemberAddress
     */
    public function setCountry($country)
    {
        $this->country = $country;

        /** @var saState $stateObj */
        $countryObj = ioc::getRepository('saCountry')->findCountryByNameIdAbbr($country);
        if ($countryObj) {
            $this->country = $countryObj->getName();
            $this->country_object = $countryObj;
        }
        else
        {
            $this->country = '';
        }

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        $country = $this->country;

        /** @var saState $stateObj */
        $countryObj = ioc::getRepository('saCountry')->findCountryByNameIdAbbr($this->country);
        if ($countryObj) {
            $country = $countryObj->getName();
        }


        return $country;
    }

    public static function generateNormalizedName($streetOne, $streetTwo, $city, $state, $postalCode) {
        return strtolower(preg_replace('/[^\da-z]/i', '', $streetOne . $streetTwo . $city . $state . $postalCode));
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     *
     * @return saMemberAddress
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return saMemberAddress
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}
