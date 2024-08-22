<?php


namespace nst\member;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use nst\events\ShiftRecurrence;
use sacore\application\app;
use sa\member\saMember;

/**
 * @Entity(repositoryClass="ProviderRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table="Provider"
 */
class Provider {

    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @var NstMember $member
     * @OneToOne(targetEntity="NstMember", inversedBy="provider")
     */
    protected $member;


    /**
     * @var string $name
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var ArrayCollection $previous_nurses
     * @ManyToMany(targetEntity="Nurse", mappedBy="previous_providers")
     * @JoinTable(name="providers_previous_nurses")
     */
    protected $previous_nurses;

    /**
     * @var ArrayCollection $blocked_nurses
     * @ManyToMany(targetEntity="Nurse", mappedBy="blocked_providers")
     * @JoinTable(name="providers_blocked_nurses")
     */
    protected $blocked_nurses;

    /**
     * @var ArrayCollection $shifts
     * @OneToMany(targetEntity="\nst\events\Shift", mappedBy="provider")
     */
    protected $shifts;

    /**
     * @var ArrayCollection $shift_recurrences
     * @OneToMany(targetEntity="\nst\events\ShiftRecurrence", mappedBy="provider")
     */
    protected $shift_recurrences;

    /**
     * @var bool $is_deleted
     * @Column(type="boolean", nullable=true)
     */
    protected $is_deleted;

    /**
     * @var ArrayCollection $invoices
     * @OneToMany(targetEntity="\nst\payroll\NstInvoice", mappedBy="provider")
     */
    protected $invoices;

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
     * @var ArrayCollection $provider_files
     * @OneToMany(targetEntity="\nst\member\NstFile", mappedBy="provider")
     */
    protected $provider_files;

    /**
     * @var ArrayCollection $contacts
     * @OneToMany(targetEntity="\nst\member\NstContact", mappedBy="provider")
     */
    protected $contacts;

    /**
     * @var array $pay_rates
     * @Column(type="array", nullable=true)
     */
    protected $pay_rates;

    /**
     * @var float $covid_pay_amount
     * @Column(type="float", nullable=true)
     */
    protected $covid_pay_amount;

    /**
     * @var float $covid_bill_amount
     * @Column(type="float", nullable=true)
     */
    protected $covid_bill_amount;

    /**
     * @var boolean $uses_travel_pay
     * @Column(type="boolean", nullable=true)
     */
    protected $uses_travel_pay;

    /**
     * @var boolean $has_covid_pay
     * @Column(type="boolean", nullable=true)
     */
    protected $has_covid_pay;

    /**
     * @var boolean $has_ot_pay
     * @Column(type="boolean", options={"default" : true})
     */
    protected $has_ot_pay;

    /**
     * @var array $travel_pay
     * @Column(type="array", nullable=true)
     */
    protected $travel_pay;

    /**
     * @var string $travel
     * @Column(type="string", nullable=true)
     */
    protected $travel;

    /**
     * @var string $stipend
     * @Column(type="string", nullable=true)
     */
    protected $stipend;

    /**
     * @var float $covid_amount
     * @Column(type="float", nullable=true)
     */
    protected $covid_amount;

    /**
     * @var boolean $requires_covid_vaccine
     * @Column(type="boolean", nullable=true)
     */
    protected $requires_covid_vaccine;

    /**
     * @var ArrayCollection $file_tags
     * @ManyToMany(targetEntity="NstFileTag")
     * @JoinTable(name="provider_shown_file_tags",
     *      joinColumns={@JoinColumn(name="provider_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="nstfiletag_id", referencedColumnName="id")}
     *      )
     */
    protected $file_tags;

    /**
     * @var ArrayCollection $custom_tags
     * @OneToMany(targetEntity="\nst\member\CustomNstFileTag", mappedBy="provider")
     */
    protected $custom_tags;

    /**
     * Many Providers have Many NurseCredentials.
     * @ManyToMany(targetEntity="NurseCredential", inversedBy="providers")
     * @JoinTable(name="nurseCredential_providers")
     */
    private $nurseCredentials;


    /**
     * One Provider has Many PresetShiftTimes. This is the inverse side.
     * @OneToMany(targetEntity="nst\events\PresetShiftTime", mappedBy="provider")
     */
    private $presetShiftTimes;

    /**
     * @var int $breakLengthInMinutes
     * @Column(type="integer", nullable=true, options={"default" : 30})
     */
    private $breakLengthInMinutes;


    public function __construct() {
        $this->shifts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->previous_nurses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocked_nurses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->file_tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->shift_recurrences = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->custom_tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->provider_files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->invoices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pay_rates = [
            'CNA' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
            'CMT' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
            'LPN' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
            'RN' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
        ];
        $this->nurseCredentials = new \Doctrine\Common\Collections\ArrayCollection();
        $this->presetShiftTimes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->has_ot_pay = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return NstMember
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param NstMember $member
     * @return Provider
     */
    public function setMember($member)
    {
        $this->member = $member;
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
     * @return Provider
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPreviousNurses()
    {
        return $this->previous_nurses;
    }

    /**
     * @param ArrayCollection $previous_nurses
     * @return Provider
     */
    public function setPreviousNurses($previous_nurses)
    {
        $this->previous_nurses = $previous_nurses;
        return $this;
    }

    /**
     * @param Nurse $previous_nurse
     * @return Provider
     */
    public function addPreviousNurse($previous_nurse) {
        $this->previous_nurses[] = $previous_nurse;
        return $this;
    }

    /**
     * @param Nurse $previous_nurse
     * @return Provider
     */
    public function removePreviousNurse($previous_nurse) {
        $this->previous_nurses->removeElement($previous_nurse);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getBlockedNurses()
    {
        return $this->blocked_nurses;
    }

    /**
     * @param ArrayCollection $blocked_nurses
     * @return Provider
     */
    public function setBlockedNurses($blocked_nurses)
    {
        $this->blocked_nurses = $blocked_nurses;
        return $this;
    }

    /**
     * @param Nurse $blocked_nurse
     * @return Provider
     */
    public function addBlockedNurse($blocked_nurse) {
        $this->blocked_nurses[] = $blocked_nurse;
        return $this;
    }

    /**
     * @param Nurse $blocked_nurse
     * @return Provider
     */
    public function removeBlockedNurse($blocked_nurse) {
        $this->blocked_nurses->removeElement($blocked_nurse);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getShifts()
    {
        return $this->shifts;
    }

    /**
     * @param ArrayCollection $shifts
     * @return Provider
     */
    public function setShifts($shifts)
    {
        $this->shifts = $shifts;
        return $this;
    }

    /**
     * @param ArrayCollection $shifts
     * @return Provider
     */
    public function addShift($shifts)
    {
        $this->shifts = $shifts;
        return $this;
    }

    /**
     * @param ArrayCollection $shifts
     * @return Provider
     */
    public function removeShift($shifts)
    {
        $this->shifts->removeElement($shifts);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getShiftRecurrences()
    {
        return $this->shift_recurrences;
    }

    /**
     * @param ArrayCollection $shift_recurrences
     * @return Provider
     */
    public function setShiftRecurrences($shift_recurrences)
    {
        $this->shift_recurrences = $shift_recurrences;
        return $this;
    }

    /**
     * @param ArrayCollection $shift_recurrences
     * @return Provider
     */
    public function addShiftRecurrence($shift_recurrences)
    {
        $this->shift_recurrences = $shift_recurrences;
        return $this;
    }

    /**
     * @param ArrayCollection $shift_recurrences
     * @return Provider
     */
    public function removeShiftRecurrence($shift_recurrences)
    {
        $this->shift_recurrences->removeElement($shift_recurrences);
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * @param bool $is_deleted
     * @return Provider
     */
    public function setIsDeleted($is_deleted)
    {
        $this->is_deleted = $is_deleted;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * @param ArrayCollection $invoices
     * @return Provider
     */
    public function setInvoices($invoices)
    {
        $this->invoices = $invoices;
        return $this;
    }

    /**
     * @param ArrayCollection $invoices
     * @return Provider
     */
    public function addInvoice($invoices)
    {
        $this->invoices = $invoices;
        return $this;
    }

    /**
     * @param ArrayCollection $invoices
     * @return Provider
     */
    public function removeInvoice($invoices)
    {
        $this->invoices->removeElement($invoices);
        return $this;
    }


    /**
     * @return string
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @param string $administrator
     * @return Provider
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirectorOfNursing()
    {
        return $this->director_of_nursing;
    }

    /**
     * @param string $director_of_nursing
     * @return Provider
     */
    public function setDirectorOfNursing($director_of_nursing)
    {
        $this->director_of_nursing = $director_of_nursing;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchedulerName()
    {
        return $this->scheduler_name;
    }

    /**
     * @param string $scheduler_name
     * @return Provider
     */
    public function setSchedulerName($scheduler_name)
    {
        $this->scheduler_name = $scheduler_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFacilityPhoneNumber()
    {
        return $this->facility_phone_number;
    }

    /**
     * @param string $facility_phone_number
     * @return Provider
     */
    public function setFacilityPhoneNumber($facility_phone_number)
    {
        $this->facility_phone_number = $facility_phone_number;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param ArrayCollection $contacts
     * @return Provider
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
        return $this;
    }

    /**
     * @param NstContact $contact
     * @return Provider
     */
    public function addContact($contact)
    {
        $this->contacts->add($contact);
        return $this;
    }

    /**
     * @param NstContact $contact
     * @return Provider
     */
    public function removeContact($contact)
    {
        $this->contacts->removeElement($contact);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getProviderFiles()
    {
        return $this->provider_files;
    }

    /**
     * @param ArrayCollection $provider_files
     * @return Provider
     */
    public function setProviderFiles($provider_files)
    {
        $this->provider_files = $provider_files;
        return $this;
    }

    /**
     * @param NstFile $provider_file
     * @return Provider
     */
    public function addProviderFile($provider_file)
    {
        $this->provider_files->add($provider_file);
        return $this;
    }

    /**
     * @param NstFile $provider_file
     * @return Provider
     */
    public function removeProviderFile($provider_file)
    {
        $this->provider_files->removeElement($provider_file);
        return $this;
    }

    /**
     * @return array
     */
    public function getPayRates()
    {
        return $this->pay_rates;
    }

    /**
     * @param array $pay_rates
     * @return Provider
     */
    public function setPayRates($pay_rates)
    {
        $this->pay_rates = $pay_rates;
        return $this;
    }

    /**
     * @return string
     */
    public function getTravel()
    {
        return $this->travel;
    }

    /**
     * @param string $travel
     * @return Provider
     */
    public function setTravel($travel)
    {
        $this->travel = $travel;
        return $this;
    }

    /**
     * @return array
     */
    public function getTravelPay()
    {
        return $this->travel_pay;
    }

    /**
     * @param array $travel_pay
     * @return Provider
     */
    public function setTravelPay($travel_pay)
    {
        $this->travel_pay = $travel_pay;
        return $this;
    }

    /**
     * @return string
     */
    public function getStipend()
    {
        return $this->stipend;
    }

    /**
     * @param string $stipend
     * @return Provider
     */
    public function setStipend($stipend)
    {
        $this->stipend = $stipend;
        return $this;
    }

    /**
     * @return float
     */
    public function getCovidAmount()
    {
        return $this->covid_amount;
    }

    /**
     * @param float $covid_amount
     * @return Provider
     */
    public function setCovidAmount($covid_amount)
    {
        $this->covid_amount = $covid_amount;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRequiresCovidVaccine()
    {
        return $this->requires_covid_vaccine;
    }

    /**
     * @param bool $requires_covid_vaccine
     * @return Provider
     */
    public function setRequiresCovidVaccine($requires_covid_vaccine)
    {
        $this->requires_covid_vaccine = $requires_covid_vaccine;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryEmailAddress()
    {
        return $this->primary_email_address;
    }

    /**
     * @param string $primary_email_address
     * @return Provider
     */
    public function setPrimaryEmailAddress($primary_email_address)
    {
        $this->primary_email_address = $primary_email_address;
        return $this;
    }

    /**
     * @return integer
     */
    public function getQuickbooksCustomerId()
    {
        return $this->quickbooks_customer_id;
    }

    /**
     * @param integer $quickbooks_customer_id
     * @return Provider
     */
    public function setQuickbooksCustomerId($quickbooks_customer_id)
    {
        $this->quickbooks_customer_id = $quickbooks_customer_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreetAddress()
    {
        return $this->street_address;
    }

    /**
     * @param string $street_address
     * @return Provider
     */
    public function setStreetAddress($street_address)
    {
        $this->street_address = $street_address;
        return $this;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     * @return Provider
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Provider
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getStateAbbreviation()
    {
        return $this->state_abbreviation;
    }

    /**
     * @param string $state_abbreviation
     * @return Provider
     */
    public function setStateAbbreviation($state_abbreviation)
    {
        $this->state_abbreviation = $state_abbreviation;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUsesTravelPay()
    {
        return $this->uses_travel_pay;
    }

    /**
     * @param bool $uses_travel_pay
     * @return Provider
     */
    public function setUsesTravelPay($uses_travel_pay)
    {
        $this->uses_travel_pay = $uses_travel_pay;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasCovidPay()
    {
        return $this->has_covid_pay;
    }

    /**
     * @param bool $has_covid_pay
     * @return Provider
     */
    public function setHasCovidPay($has_covid_pay)
    {
        $this->has_covid_pay = $has_covid_pay;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasOtPay()
    {
        return $this->has_ot_pay;
    }

    /**
     * @param bool $has_ot_pay
     * @return Provider
     */
    public function setHasOtPay($has_ot_pay)
    {
        $this->has_ot_pay = $has_ot_pay;
        return $this;
    }

    /**
     * @return float
     */
    public function getCovidPayAmount()
    {
        return $this->covid_pay_amount;
    }

    /**
     * @param float $covid_pay_amount
     * @return Provider
     */
    public function setCovidPayAmount($covid_pay_amount)
    {
        $this->covid_pay_amount = $covid_pay_amount;
        return $this;
    }

    /**
     * @return float
     */
    public function getCovidBillAmount()
    {
        return $this->covid_bill_amount;
    }

    /**
     * @param float $covid_bill_amount
     * @return Provider
     */
    public function setCovidBillAmount($covid_bill_amount)
    {
        $this->covid_bill_amount = $covid_bill_amount;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFileTags()
    {
        return $this->file_tags;
    }

    /**
     * @param ArrayCollection $file_tags
     * @return Provider
     */
    public function setFileTags($file_tags)
    {
        $this->file_tags = $file_tags;
        return $this;
    }

    /**
     * @param NstFileTag $file_tag
     * @return Provider
     */
    public function addFileTag($file_tag) {
        $this->file_tags[] = $file_tag;
        return $this;
    }

    /**
     * @param NstFileTag $file_tag
     * @return Provider
     */
    public function removeFileTag($file_tag) {
        $this->file_tags->removeElement($file_tag);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCustomTags()
    {
        return $this->custom_tags;
    }

    /**
     * @param ArrayCollection $custom_tags
     * @return Provider
     */
    public function setCustomTags($custom_tags)
    {
        $this->custom_tags = $custom_tags;
        return $this;
    }

    /**
     * @param CustomNstFileTag $custom_tag
     * @return Provider
     */
    public function addCustomTag($custom_tag)
    {
        $this->custom_tags->add($custom_tag);
        return $this;
    }

    /**
     * @param CustomNstFileTag $custom_tag
     * @return Provider
     */
    public function removeCustomTag($custom_tag)
    {
        $this->custom_tags->removeElement($custom_tag);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNurseCredentials()
    {
        return $this->nurseCredentials;
    }

    /**
     * @param NurseCredential $nurseCredential
     * @return Provider
     */
    public function addNurseCredential($nurseCredential)
    {
        $this->nurseCredentials->add($nurseCredential);
        return $this;
    }

    /**
     * @param NurseCredential $nurseCredential
     * @return Provider
     */
    public function removeNurseCredential($nurseCredential)
    {
        $this->nurseCredentials->removeElement($nurseCredential);
        return $this;
    }

    /**
     * @return Provider
     */
    public function clearNurseCredentials()
    {
        $this->nurseCredentials->clear();
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPresetShiftTimes()
    {
        return $this->presetShiftTimes;
    }

    /**
     * @param PresetShiftTime $presetShiftTime
     * @return Provider
     */
    public function addPresetShiftTime($presetShiftTime)
    {
        $this->presetShiftTimes->add($presetShiftTime);
        return $this;
    }

    /**
     * @param PresetShiftTime $presetShiftTime
     * @return Provider
     */
    public function removePresetShiftTime($presetShiftTime)
    {
        $this->presetShiftTimes->removeElement($presetShiftTime);
        return $this;
    }

    /**
     * @return Provider
     */
    public function clearPresetShiftTimes()
    {
        $this->presetShiftTimes->clear();
        return $this;
    }

    public function getCompanyAndAddress()
    {
        return $this->getMember()->getCompany() . ' - ' . $this->getStreetAddress() . ', ' . $this->getCity() . ' ' . $this->getStateAbbreviation() . ' ' . $this->getZipcode();
    }

    /**
     * @return int
     */
    public function getBreakLengthInMinutes()
    {
        return $this->breakLengthInMinutes;
    }

    /**
     * @param int $breakLengthInMinutes
     * @return Provider
     */
    public function setBreakLengthInMinutes($breakLengthInMinutes)
    {
        $this->breakLengthInMinutes = $breakLengthInMinutes;
        return $this;
    }
}
