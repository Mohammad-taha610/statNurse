<?php

namespace nst\member;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Mapping as ORM;
use Doctrine\Mapping\DiscriminatorColumn;
use Doctrine\Mapping\Entity;
use Doctrine\Mapping\InheritanceType;
use Doctrine\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @Entity(repositoryClass="nst\member\ExecutiveRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table="Executive"
 */
class Executive
{
    /**
     * @Id()
     * @GeneratedValue()
     * @Column(type="integer")
     */
    private $id;

    /**
     * @var NstMember $member
     * @OneToOne(targetEntity="NstMember", inversedBy="executive")
     */
    protected $member;

    /**
     * @var bool $is_deleted
     * @Column(type="boolean", nullable=true)
     */
    protected $is_deleted;

        /**
     * @var string $administrator
     * @Column(type="string", nullable=true)
     */
    protected $administrator;

    /**
     * @var string $director_of_nursing
     * @Column(type="string", nullable=true)
     */
    protected $director_of_nursing;

    /**
     * @var string $scheduler_name
     * @Column(type="string", nullable=true)
     */
    protected $scheduler_name;

    /**
     * @var string $facility_phone_number
     * @Column(type="string", nullable=true)
     */
    protected $facility_phone_number;

    /**
     * @var string $primary_email_address
     * @Column(type="string", nullable=true)
     */
    protected $primary_email_address;

    /**
     * @var string $street_address
     * @Column(type="string", nullable=true)
     */
    protected $street_address;

    /**
     * @var string $zipcode
     * @Column(type="string", nullable=true)
     */
    protected $zipcode;

        /**
     * @var string $city
     * @Column(type="string", nullable=true)
     */
    protected $city;

        /**
     * @var string $state_abbreviation
     * @Column(type="string", nullable=true)
     */
    protected $state_abbreviation;
    
    /**
     * @var integer $quickbooks_customer_id
     * @Column(type="integer", nullable=true)
     */
    protected $quickbooks_customer_id;


    /**
     * @Column(length=255, nullable=true)
     */
    protected ?string $name = null;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Executive
     */
    public function setName(?string $name): Executive
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @ManyToMany(targetEntity="nst\member\Provider")
     * @JoinTable(name="executives_providers")
     */
    private $providers;

    public function __construct()
    {
        $this->providers = new ArrayCollection();
    }

    public function addProvider(Provider $provider): self
    {
        if (!$this->providers->contains($provider)) {
            $this->providers[] = $provider;
        }

        return $this;
    }

    public function removeProvider(Provider $provider): self
    {
        $this->providers->removeElement($provider);
        return $this;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get $member
     *
     * @return  NstMember
     */ 
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set $member
     *
     * @param  NstMember  $member  $member
     *
     * @return  self
     */ 
    public function setMember(NstMember $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get $is_deleted
     *
     * @return  bool
     */ 
    public function getIs_deleted()
    {
        return $this->is_deleted;
    }

    /**
     * Set $is_deleted
     *
     * @param  bool  $is_deleted  $is_deleted
     *
     * @return  self
     */ 
    public function setIs_deleted(bool $is_deleted)
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    /**
     * Get $administrator
     *
     * @return  string
     */ 
    public function getAdministrator()
    {
        return $this->administrator;
    }

    /**
     * Set $administrator
     *
     * @param  string  $administrator  $administrator
     *
     * @return  self
     */ 
    public function setAdministrator(string $administrator)
    {
        $this->administrator = $administrator;

        return $this;
    }

    /**
     * Get $director_of_nursing
     *
     * @return  string
     */ 
    public function getDirectorOfNursing()
    {
        return $this->director_of_nursing;
    }

    /**
     * Set $director_of_nursing
     *
     * @param  string  $director_of_nursing  $director_of_nursing
     *
     * @return  self
     */ 
    public function setDirectorOfNursing(string $director_of_nursing)
    {
        $this->director_of_nursing = $director_of_nursing;

        return $this;
    }

    /**
     * Get $scheduler_name
     *
     * @return  string
     */ 
    public function getSchedulerName()
    {
        return $this->scheduler_name;
    }

    /**
     * Set $scheduler_name
     *
     * @param  string  $scheduler_name  $scheduler_name
     *
     * @return  self
     */ 
    public function setSchedulerName(string $scheduler_name)
    {
        $this->scheduler_name = $scheduler_name;

        return $this;
    }

    /**
     * Get $facility_phone_number
     *
     * @return  string
     */ 
    public function getFacilityPhoneNumber()
    {
        return $this->facility_phone_number;
    }

    /**
     * Set $facility_phone_number
     *
     * @param  string  $facility_phone_number  $facility_phone_number
     *
     * @return  self
     */ 
    public function setFacilityPhoneNumber(string $facility_phone_number)
    {
        $this->facility_phone_number = $facility_phone_number;

        return $this;
    }

    /**
     * Get $primary_email_address
     *
     * @return  string
     */ 
    public function getPrimaryEmailAddress()
    {
        return $this->primary_email_address;
    }

    /**
     * Set $primary_email_address
     *
     * @param  string  $primary_email_address  $primary_email_address
     *
     * @return  self
     */ 
    public function setPrimaryEmailAddress(string $primary_email_address)
    {
        $this->primary_email_address = $primary_email_address;

        return $this;
    }



    /**
     * Get $street_address
     *
     * @return  string
     */ 
    public function getStreetAddress()
    {
        return $this->street_address;
    }

    /**
     * Set $street_address
     *
     * @param  string  $street_address  $street_address
     *
     * @return  self
     */ 
    public function setStreetAddress(string $street_address)
    {
        $this->street_address = $street_address;

        return $this;
    }

    /**
     * Get $zipcode
     *
     * @return  string
     */ 
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set $zipcode
     *
     * @param  string  $zipcode  $zipcode
     *
     * @return  self
     */ 
    public function setZipcode(string $zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get $city
     *
     * @return  string
     */ 
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set $city
     *
     * @param  string  $city  $city
     *
     * @return  self
     */ 
    public function setCity(string $city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get $state_abbreviation
     *
     * @return  string
     */ 
    public function getStateAbbreviation()
    {
        return $this->state_abbreviation;
    }

    /**
     * Set $state_abbreviation
     *
     * @param  string  $state_abbreviation  $state_abbreviation
     *
     * @return  self
     */ 
    public function setStateAbbreviation(string $state_abbreviation)
    {
        $this->state_abbreviation = $state_abbreviation;

        return $this;
    }

    /**
     * Get $quickbooks_customer_id
     *
     * @return  integer
     */ 
    public function getQuickbooks_customer_id()
    {
        return $this->quickbooks_customer_id;
    }

    /**
     * Set $quickbooks_customer_id
     *
     * @param  integer  $quickbooks_customer_id  $quickbooks_customer_id
     *
     * @return  self
     */ 
    public function setQuickbooks_customer_id($quickbooks_customer_id)
    {
        $this->quickbooks_customer_id = $quickbooks_customer_id;

        return $this;
    }
}

