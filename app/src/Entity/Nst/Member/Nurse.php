<?php

namespace App\Entity\Nst\Member;

use App\Entity\Nst\Events\Shift;
use App\Entity\Nst\Messages\NstMessage;
use App\Entity\Nst\Payroll\Payroll;
use App\Entity\Nst\System\NstState;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'Nurse')]
#[Entity(repositoryClass: 'NurseRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
class Nurse
{
    /**
     * @var int $id
     */
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    /**
     * @var NstMember $member
     */
    #[OneToOne(targetEntity: 'NstMember', inversedBy: 'nurse')]
    protected $member;

    /**
     * @var string $first_name
     */
    #[Column(type: 'string', nullable: true)]
    protected $first_name;

    /**
     * @var string $last_name
     */
    #[Column(type: 'string', nullable: true)]
    protected $last_name;

    /**
     * @var string $middle_name
     */
    #[Column(type: 'string', nullable: true)]
    protected $middle_name;

    /**
     * @var int $nurse_number
     */
    #[Column(type: 'integer', nullable: true)]
    protected $nurse_number;

    /**
     * @var ArrayCollection $previous_providers
     */
    #[JoinTable(name: 'providers_previous_nurses')]
    #[ManyToMany(targetEntity: 'Provider', inversedBy: 'previous_nurses')]
    protected $previous_providers;

    /**
     * @var ArrayCollection $preferred_providers
     */

    #[ManyToMany(targetEntity: 'Provider', inversedBy: 'preferred_nurses')]
    #[JoinTable(name: 'nurses_preferred_providers')]
    #[JoinColumn(name: 'nurse_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'provider_id', referencedColumnName: 'id')]
    protected $preferred_providers;

    /**
     * @var ArrayCollection $payrolls
     */
    #[OneToMany(targetEntity: Payroll::class, mappedBy: 'nurse', fetch: 'LAZY')]
    protected $payrolls;

    /**
     * @var ArrayCollection $job
     */
    #[ManyToOne(targetEntity: 'Job', inversedBy: 'nurses', fetch: 'LAZY')]
    protected $job;

    /**
     * @var ArrayCollection $blocked_providers
     */
    #[JoinTable(name: 'providers_blocked_nurses')]
    #[ManyToMany(targetEntity: 'Provider', inversedBy: 'blocked_nurses')]
    protected $blocked_providers;

    /**
     * @var ArrayCollection $shifts
     */
    #[JoinTable('nurse_shifts')]
    #[OneToMany(mappedBy: 'nurse', targetEntity: Shift::class)]
    protected $shifts;

    /**
     * @var bool $is_deleted
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $is_deleted;

    /**
     * @var float $hourly_rate
     */
    #[Column(type: 'float', nullable: true)]
    protected $hourly_rate;

    /**
     * @var string $credentials
     */
    #[Column(type: 'string', nullable: true)]
    protected $credentials;

    /**
     * @var ArrayCollection $nurse_files
     */
    #[OneToMany(targetEntity: NstFile::class, mappedBy: 'nurse')]
    protected $nurse_files;

    /**
     * @var saFile $drug_screen_file
     */
    #[OneToOne(mappedBy: 'nurse', targetEntity: NstFile::class)]
    protected $drug_screen_file;

    /**
     * @var saFile $covid_vaccine_file
     */
    #[OneToOne(mappedBy: 'nurse', targetEntity: NstFile::class, cascade: ['persist'])]
    protected $covid_vaccine_file;

    /**
     * @var bool $is_vaccinated
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $is_vaccinated;

    /**
     * @var DateTime $skin_test_expiration_date
     */
    #[Column(type: 'date', nullable: true)]
    protected $skin_test_expiration_date;

    /**
     * @var string $phone_number
     */
    #[Column(type: 'string', nullable: true)]
    protected $phone_number;

    /**
     * @var string $email_address
     */
    #[Column(type: 'string', nullable: true)]
    protected $email_address;

    /**
     * @var string $ssn
     */
    #[Column(type: 'string', nullable: true)]
    protected $ssn;

    /**
     * @var string $payment_street_address
     */
    #[Column(type: 'string', nullable: true)]
    protected $payment_street_address;

    /**
     * @var string $payment_street_address_2
     */
    #[Column(type: 'string', nullable: true)]
    protected $payment_street_address_2;

    /**
     * @var string $payment_zipcode
     */
    #[Column(type: 'string', nullable: true)]
    protected $payment_zipcode;

    /**
     * @var string $payment_city
     */
    #[Column(type: 'string', nullable: true)]
    protected $payment_city;

    /**
     * @var string $payment_state
     */
    #[Column(type: 'string', nullable: true)]
    protected $payment_state;

    /**
     * @var string $street_address
     */
    #[Column(type: 'string', nullable: true)]
    protected $street_address;

    /**
     * @var string $street_address
     */
    #[Column(type: 'string', nullable: true)]
    protected $street_address_2;

    /**
     * @var string $apt_number
     */
    #[Column(type: 'string', nullable: true)]
    protected $apt_number;

    /**
     * @var string $city
     */
    #[Column(type: 'string', nullable: true)]
    protected $city;

    /**
     * @var string $state
     */
    #[Column(type: 'string', nullable: true)]
    protected $state;

    /**
     * @var string $zipcode
     */
    #[Column(type: 'string', nullable: true)]
    protected $zipcode;

    /**
     * @var DateTime $license_expiration_date
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $license_expiration_date;

    /**
     * @var DateTime $cpr_expiration_date
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $cpr_expiration_date;

    /**
     * @var DateTime $acls_expiration_date
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $acls_expiration_date;

    /**
     * @var DateTime $date_of_hire
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $date_of_hire;

    /**
     * @var DateTime $date_of_birth
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $date_of_birth;

    /**
     * @var string $routing_number
     */
    #[Column(type: 'string', nullable: true)]
    protected $routing_number;

    /**
     * @var string $account_number
     */
    #[Column(type: 'string', nullable: true)]
    protected $account_number;

    /**
     * @var string $account_holder_name
     */
    #[Column(type: 'string', nullable: true)]
    protected $account_holder_name;

    /**
     * @var string $bank_account_type
     */
    #[Column(type: 'string', nullable: true)]
    protected $bank_account_type;

    /**
     * @var string $bank_name
     */
    #[Column(type: 'string', nullable: true)]
    protected $bank_name;

    /**
     * @var string $firebase_token
     */
    #[Column(type: 'string', nullable: true)]
    protected $firebase_token;

    /**
     * @var int $quickbooks_vendor_id
     */
    #[Column(type: 'integer', nullable: true)]
    protected $quickbooks_vendor_id;

    /**
     * @var string $payment_method
     */
    #[Column(type: 'string', nullable: true)]
    protected $payment_method;

    /**
     * @var array $pay_period_totals
     */
    #[Column(type: 'array', nullable: true)]
    protected $pay_period_totals;

    /**
     * @var bool $receives_sms
     */
    #[Column(type: 'boolean', nullable: false, options: ['default' => false])]
    protected $receives_sms;

    /**
     * @var bool $receives_push_notification
     */
    #[Column(type: 'boolean', nullable: false, options: ['default' => false])]
    protected $receives_push_notification;

    #[Column(type: 'string', nullable: true)]
    protected $app_version;

    #[Column(type: 'string', nullable: true)]
    protected $pay_card_account_number;

    /**
     * @var ArrayCollection $states_able_to_work
     */
    // join column on state should be nstState_id

    #[ManyToMany(targetEntity: NstState::class, inversedBy: 'nurse_working_states')]
    #[JoinTable(name: 'states_nurses_can_work')]
    #[JoinColumn(name: 'nurse_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'nststate_id', referencedColumnName: 'id')]
    protected $states_able_to_work;

    /**
     * @var ArrayCollection $messages
     */
    #[JoinTable(name: 'nurses_messages')]
    #[JoinColumn(name: 'nurse_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'nstmessage_id', referencedColumnName: 'id')]
    #[ManyToMany(targetEntity: NstMessage::class, inversedBy: 'nurses')]
    protected $messages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payrolls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->shifts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocked_providers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->previous_providers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->nurse_files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->states_able_to_work = new \Doctrine\Common\Collections\ArrayCollection();
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param  NstMember  $member
     * @return Nurse
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return int
     */
    public function getNurseNumber()
    {
        return $this->nurse_number;
    }

    /**
     * @param  int  $nurse_number
     * @return Nurse
     */
    public function setNurseNumber($nurse_number)
    {
        $this->nurse_number = $nurse_number;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPreviousProviders()
    {
        return $this->previous_providers;
    }

    /**
     * @param  ArrayCollection  $previous_providers
     * @return Nurse
     */
    public function setPreviousProviders($previous_providers)
    {
        $this->previous_providers = $previous_providers;

        return $this;
    }

    /**
     * @return Nurse
     */
    public function addPreviousProvider($previous_provider)
    {
        $this->previous_providers[] = $previous_provider;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayrolls()
    {
        return $this->payrolls;
    }

    /**
     * @param  ArrayCollection  $payrolls
     * @return Nurse
     */
    public function setPayrolls($payrolls)
    {
        $this->payrolls = $payrolls;

        return $this;
    }

    /**
     * Add payroll.
     *
     *
     * @return Nurse
     */
    public function addPayroll(\nst\payroll\Payroll $payroll)
    {
        $this->payrolls->add($payroll);

        return $this;
    }

    /**
     * Remove payroll.
     *
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePayroll(\nst\payroll\Payroll $payroll)
    {
        return $this->payrolls->removeElement($payroll);
    }

    /**
     * @return ArrayCollection
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param  ArrayCollection  $job
     * @return Nurse
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getBlockedProviders()
    {
        return $this->blocked_providers;
    }

    /**
     * @param  ArrayCollection  $blocked_providers
     * @return Nurse
     */
    public function setBlockedProviders($blocked_providers)
    {
        $this->blocked_providers = $blocked_providers;

        return $this;
    }

    /**
     * Add blocked_provider.
     *
     * @param  \nst\member\Provider  $blocked_provider
     * @return Nurse
     */
    public function addBlockedProvider(Provider $blocked_provider)
    {
        $this->blocked_providers->add($blocked_provider);

        return $this;
    }

    /**
     * Remove blocked_provider.
     *
     * @param  \nst\member\Provider  $blocked_provider
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeBlockedProvider(Provider $blocked_provider)
    {
        return $this->blocked_providers->removeElement($blocked_provider);
    }

    /**
     * @return ArrayCollection
     */
    public function getShifts()
    {
        return $this->shifts;
    }

    /**
     * @param  ArrayCollection  $shifts
     * @return Nurse
     */
    public function setShifts($shifts)
    {
        $this->shifts = $shifts;

        return $this;
    }

    public function addShift($shift)
    {
        $this->shifts[] = $shift;
    }

    public function removeShift($shift)
    {
        $this->shifts->removeElement($shift);
    }

    /**
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * @param  bool  $is_deleted
     * @return Nurse
     */
    public function setIsDeleted($is_deleted)
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    /**
     * @return float
     */
    public function getHourlyRate()
    {
        return $this->hourly_rate;
    }

    /**
     * @param  float  $hourly_rate
     * @return Nurse
     */
    public function setHourlyRate($hourly_rate)
    {
        $this->hourly_rate = $hourly_rate;

        return $this;
    }

    /**
     * @return string
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param  string  $credentials
     * @return Nurse
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNurseFiles()
    {
        return $this->nurse_files;
    }

    /**
     * @param  ArrayCollection  $nurse_files
     * @return Nurse
     */
    public function setNurseFiles($nurse_files)
    {
        $this->nurse_files = $nurse_files;

        return $this;
    }

    /**
     * @param  saFile  $nurse_file
     * @return Nurse
     */
    public function addNurseFile($nurse_file)
    {
        $this->nurse_files->add($nurse_file);

        return $this;
    }

    /**
     * @param  saFile  $nurse_file
     * @return Nurse
     */
    public function removeNurseFile($nurse_file)
    {
        $this->nurse_files->removeElement($nurse_file);

        return $this;
    }

    /**
     * @return saFile
     */
    public function getDrugScreenFile()
    {
        return $this->drug_screen_file;
    }

    /**
     * @param  saFile  $drug_screen_file
     * @return Nurse
     */
    public function setDrugScreenFile($drug_screen_file)
    {
        $this->drug_screen_file = $drug_screen_file;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getSkinTestExpirationDate()
    {
        return $this->skin_test_expiration_date;
    }

    /**
     * @param  DateTime  $skin_test_expiration_date
     * @return Nurse
     */
    public function setSkinTestExpirationDate($skin_test_expiration_date)
    {
        $this->skin_test_expiration_date = $skin_test_expiration_date;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * @param  string  $phone_number
     * @return Nurse
     */
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * @param  string  $email_address
     * @return Nurse
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;

        return $this;
    }

    /**
     * @return string
     */
    public function getSSN()
    {
        return $this->ssn;
    }

    /**
     * @param  string  $ssn
     * @return Nurse
     */
    public function setSSN($ssn)
    {
        $this->ssn = $ssn;

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
     * @param  string  $street_address
     * @return Nurse
     */
    public function setStreetAddress($street_address)
    {
        $this->street_address = $street_address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAptNumber()
    {
        return $this->apt_number;
    }

    /**
     * @param  string  $apt_number
     * @return Nurse
     */
    public function setAptNumber($apt_number)
    {
        $this->apt_number = $apt_number;

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
     * @param  string  $city
     * @return Nurse
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param  string  $state
     * @return Nurse
     */
    public function setState($state)
    {
        $this->state = $state;

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
     * @param  string  $zipcode
     * @return Nurse
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLicenseExpirationDate()
    {
        return $this->license_expiration_date;
    }

    /**
     * @param  DateTime  $license_expiration_date
     * @return Nurse
     */
    public function setLicenseExpirationDate($license_expiration_date)
    {
        $this->license_expiration_date = $license_expiration_date;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCprExpirationDate()
    {
        return $this->cpr_expiration_date;
    }

    /**
     * @param  DateTime  $cpr_expiration_date
     * @return Nurse
     */
    public function setCprExpirationDate($cpr_expiration_date)
    {
        $this->cpr_expiration_date = $cpr_expiration_date;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getAclsExpirationDate()
    {
        return $this->acls_expiration_date;
    }

    /**
     * @param  DateTime  $acls_expiration_date
     * @return Nurse
     */
    public function setAclsExpirationDate($acls_expiration_date)
    {
        $this->acls_expiration_date = $acls_expiration_date;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateOfHire()
    {
        return $this->date_of_hire;
    }

    /**
     * @param  DateTime  $date_of_hire
     * @return Nurse
     */
    public function setDateOfHire($date_of_hire)
    {
        $this->date_of_hire = $date_of_hire;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    /**
     * @param  DateTime  $date_of_birth
     * @return Nurse
     */
    public function setDateOfBirth($date_of_birth)
    {
        $this->date_of_birth = $date_of_birth;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param  string  $first_name
     * @return Nurse
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param  string  $last_name
     * @return Nurse
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * @param  string  $middle_name
     * @return Nurse
     */
    public function setMiddleName($middle_name)
    {
        $this->middle_name = $middle_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoutingNumber()
    {
        return $this->routing_number;
    }

    /**
     * @param  string  $routing_number
     * @return Nurse
     */
    public function setRoutingNumber($routing_number)
    {
        $this->routing_number = $routing_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->account_number;
    }

    /**
     * @param  string  $account_number
     * @return Nurse
     */
    public function setAccountNumber($account_number)
    {
        $this->account_number = $account_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountHolderName()
    {
        return $this->account_holder_name;
    }

    /**
     * @param  string  $account_number
     * @return Nurse
     */
    public function setAccountHolderName($account_holder_name)
    {
        $this->account_holder_name = $account_holder_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankAccountType()
    {
        return $this->bank_account_type;
    }

    /**
     * @param  string  $bank_account_type
     * @return Nurse
     */
    public function setBankAccountType($bank_account_type)
    {
        $this->bank_account_type = $bank_account_type;

        return $this;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bank_name;
    }

    /**
     * @param  string  $bank_name
     * @return Nurse
     */
    public function setBankName($bank_name)
    {
        $this->bank_name = $bank_name;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPreferredProviders()
    {
        return $this->preferred_providers;
    }

    /**
     * @param  ArrayCollection  $preferred_providers
     * @return Nurse
     */
    public function setPreferredProviders($preferred_providers)
    {
        $this->preferred_providers = $preferred_providers;

        return $this;
    }

    /**
     * @param  ArrayCollection  $preferred_provider
     * @return Nurse
     */
    public function addPreferredProvider($preferred_provider)
    {
        $this->preferred_providers->add($preferred_provider);

        return $this;
    }

    /**
     * @param  ArrayCollection  $preferred_provider
     * @return Nurse
     */
    public function removePreferredProvider($preferred_provider)
    {
        $this->preferred_providers->removeElement($preferred_provider);

        return $this;
    }

    /**
     * @return string
     */
    public function getFirebaseToken()
    {
        return $this->firebase_token;
    }

    /**
     * @param  string  $firebase_token
     * @return Nurse
     */
    public function setFirebaseToken($firebase_token)
    {
        $this->firebase_token = $firebase_token;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreetAddress2()
    {
        return $this->street_address_2;
    }

    /**
     * @param  string  $street_address_2
     * @return Nurse
     */
    public function setStreetAddress2($street_address_2)
    {
        $this->street_address_2 = $street_address_2;

        return $this;
    }

    /**
     * @return saFile
     */
    public function getCovidVaccineFile()
    {
        return $this->covid_vaccine_file;
    }

    /**
     * @param  saFile  $covid_vaccine_file
     * @return Nurse
     */
    public function setCovidVaccineFile($covid_vaccine_file)
    {
        $this->covid_vaccine_file = $covid_vaccine_file;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsVaccinated()
    {
        return $this->is_vaccinated;
    }

    /**
     * @param  bool  $is_vaccinated
     * @return Nurse
     */
    public function setIsVaccinated($is_vaccinated)
    {
        $this->is_vaccinated = $is_vaccinated;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuickbooksVendorId()
    {
        return $this->quickbooks_vendor_id;
    }

    /**
     * @param  int  $quickbooks_vendor_id
     * @return Nurse
     */
    public function setQuickbooksVendorId($quickbooks_vendor_id)
    {
        $this->quickbooks_vendor_id = $quickbooks_vendor_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @param  string  $payment_method
     * @return Nurse
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentStreetAddress()
    {
        return $this->payment_street_address;
    }

    /**
     * @param  string  $payment_street_address
     * @return Nurse
     */
    public function setPaymentStreetAddress($payment_street_address)
    {
        $this->payment_street_address = $payment_street_address;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentStreetAddress2()
    {
        return $this->payment_street_address_2;
    }

    /**
     * @param  string  $payment_street_address_2
     * @return Nurse
     */
    public function setPaymentStreetAddress2($payment_street_address_2)
    {
        $this->payment_street_address_2 = $payment_street_address_2;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentZipcode()
    {
        return $this->payment_zipcode;
    }

    /**
     * @param  string  $payment_zipcode
     * @return Nurse
     */
    public function setPaymentZipcode($payment_zipcode)
    {
        $this->payment_zipcode = $payment_zipcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentCity()
    {
        return $this->payment_city;
    }

    /**
     * @param  string  $payment_city
     * @return Nurse
     */
    public function setPaymentCity($payment_city)
    {
        $this->payment_city = $payment_city;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentState()
    {
        return $this->payment_state;
    }

    /**
     * @param  string  $payment_state
     * @return Nurse
     */
    public function setPaymentState($payment_state)
    {
        $this->payment_state = $payment_state;

        return $this;
    }

    /**
     * @return array
     */
    public function getPayPeriodTotals()
    {
        return $this->pay_period_totals;
    }

    /**
     * @param  array  $pay_period_totals
     * @return Nurse
     */
    public function setPayPeriodTotals($pay_period_totals)
    {
        $this->pay_period_totals = $pay_period_totals;

        return $this;
    }

    /**
     * Set receives_sms
     *
     * @param  bool  $receives_sms
     * @return Nurse
     */
    public function setReceivesSMS($receives_sms)
    {
        $this->receives_sms = $receives_sms;

        return $this;
    }

    /**
     * Get receives_sms
     *
     * @return bool
     */
    public function getReceivesSMS()
    {
        return $this->receives_sms;
    }

    /**
     * Set receives_push_notification
     *
     * @param  bool  $receives_push_notification
     * @return Nurse
     */
    public function setReceivesPushNotification($receives_push_notification)
    {
        $this->receives_push_notification = $receives_push_notification;

        return $this;
    }

    /**
     * Get receives_push_notification
     *
     * @return bool
     */
    public function getReceivesPushNotification()
    {
        return $this->receives_push_notification;
    }

    /** @return string */
    public function getAppVersion()
    {
        return $this->app_version;
    }

    /** @return Nurse */
    public function setAppVersion($app_version)
    {
        $this->app_version = $app_version;

        return $this;
    }

    /**
     * Set pay_card_account_number
     *
     * @param  string  $pay_card_account_number
     * @return Nurse
     */
    public function setPayCardAccountNumber($pay_card_account_number)
    {
        $this->pay_card_account_number = $pay_card_account_number;

        return $this;
    }

    /**
     * Get pay_card_account_number
     *
     * @return string
     */
    public function getPayCardAccountNumber()
    {
        return $this->pay_card_account_number;
    }

    public function hasVaccineCard(): int
    {
        return $this->nurse_files->filter(
            fn ($file) => $file->getTag()->getName() == 'Covid Vaccine Card'
        )->count();
    }

    /**
     * @return ArrayCollection
     */
    public function getStatesAbleToWork()
    {
        return $this->states_able_to_work;
    }

    /**
     * @return array
     */
    public function getStatesAbleToWorkAbbreviated()
    {
        // TODO rewrite
        /*return array_map(function ($state) {
            return $state['abbreviation'];
        }, doctrineUtils::getEntityCollectionArray($this->getStatesAbleToWork()));*/
    }

    /**
     * Add state_able_to_work.
     *
     *
     * @return Nurse
     */
    public function addStateAbleToWork(\nst\system\NstState $state_able_to_work)
    {
        $this->states_able_to_work->add($state_able_to_work);

        return $this;
    }

    /**
     * Remove state_able_to_work.
     *
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeStateAbleToWork(\nst\system\NstState $state_able_to_work)
    {
        return $this->states_able_to_work->removeElement($state_able_to_work);
    }

    /**
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add a message to this nurse's assigned messages
     *
     * @param  \nst\messages\NstMessage  $nurses
     * @return Nurse
     */
    public function addMessage(NstMessage $message)
    {
        $this->messages->add($message);

        return $this;
    }

    /**
     * Remove a message from this nurse's assigned messages
     *
     * @param  \nst\messages\NstMessages  $message
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeMessage(NstMessage $message)
    {
        return $this->messages->removeElement($message);
    }
}
