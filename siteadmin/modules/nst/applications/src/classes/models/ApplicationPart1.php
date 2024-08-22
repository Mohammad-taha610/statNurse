<?php

namespace nst\applications;

/**
 * @Entity(repositoryClass="Application1Repository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="ApplicationPart1")
 */
class ApplicationPart1
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @OneToOne(targetEntity="nst\member\NstMember", inversedBy="application_part1") */
    protected $member;

    /** @Column(type="datetime", nullable=true) */
    protected $submitted_at;

    /** @Column(type="datetime", nullable=true) */
    protected $approved_at;

    /** @Column(type="datetime", nullable=true) */
    protected $declined_at;

    /** @Column(type="string", nullable=true) */
    protected $dob;

    /** @Column(type="string", nullable=true) */
    protected $position;

    /** @Column(type="string", nullable=true) */
    protected $explanation;

    /** @Column(type="boolean", nullable=true) */
    protected $citizen_of_usa;

    /** @Column(type="boolean", nullable=true) */
    protected $allowed_to_work;

    /** @Column(type="string", nullable=true) */
    protected $social_security_number;

    /**
     * @var string $phone_number
     * @Column(type="string", nullable=true)
     */
    protected $phone_number;

    /**
     * @var string $street_address
     * @Column(type="string", nullable=true)
     */
    protected $street_address;

    /**
     * @var string $street_address
     * @Column(type="string", nullable=true)
     */
    protected $street_address_2;

    /**
     * @var string $city
     * @Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @var string $state
     * @Column(type="string", nullable=true)
     */
    protected $state;
    
    /**
     * @var string $zipcode
     * @Column(type="string", nullable=true)
     */
    protected $zipcode;

    /** @Column(type="json", nullable=true) */
    protected $nurse;

    /** @Column(type="json", nullable=true) */
    protected $employment_details_one;

    /** @Column(type="json", nullable=true) */
    protected $employment_details_two;

    /** @Column(type="json", nullable=true) */
    protected $employment_details_three;

    /** @var boolean $one_year_ltc_experience
     * @Column(type="boolean", nullable=true)
     */
    protected $one_year_ltc_experience;

    /** @var string $one_year_explanation
     * @Column(type="string", nullable=true)
     */
    protected $one_year_explanation;

    /** @var boolean $currently_employed
     * @Column(type="boolean", nullable=true)
     */
    protected $currently_employed;

    /** @var string $company1_company_name
     * @Column(type="string", nullable=true)
     */
    protected $company1_company_name;

    /** @var string $company1_supervisor_name
     * @Column(type="string", nullable=true)
     */
    protected $company1_supervisor_name;

    /** @var string $company1_company_address
     * @Column(type="string", nullable=true)
     */
    protected $company1_company_address;

    /** @var string $company1_company_city
     * @Column(type="string", nullable=true)
     */
    protected $company1_company_city;

    /** @var string $company1_company_state
     * @Column(type="string", nullable=true)
     */
    protected $company1_company_state;

    /** @var string $company1_company_zip
     * @Column(type="string", nullable=true)
     */
    protected $company1_company_zip;

    /** @var string $company1_company_phone
     * @Column(type="string", nullable=true)
     */
    protected $company1_company_phone;

    /** @ var string $company1_company_email
     * @Column(type="string", nullable=true)
     */
    protected $company1_company_email;

    /** @var string $company1_job_title
     * @Column(type="string", nullable=true)
     */
    protected $company1_job_title;

    /** @var string $company1_start_date
     * @Column(type="string", nullable=true)
     */
    protected $company1_start_date;

    /** @var string $company1_end_date
     * @Column(type="string", nullable=true)
     */
    protected $company1_end_date;

    /** @var string $company1_responsibilites
     * @Column(type="string", nullable=true)
     */
    protected $company1_responsibilites;

    /** @var string $company1_reason_for_leaving
     * @Column(type="string", nullable=true)
     */
    protected $company1_reason_for_leaving;

    /** @var boolean $company1_may_we_contact_employer
     * @Column(type="boolean", nullable=true)
     */
    protected $company1_may_we_contact_employer;

    /** @var string $company2_company_name
     * @Column(type="string", nullable=true)
     */
    protected $company2_company_name;

    /** @var string $company2_supervisor_name
     * @Column(type="string", nullable=true)
     */
    protected $company2_supervisor_name;

    /** @var string $company2_company_address
     * @Column(type="string", nullable=true)
     */
    protected $company2_company_address;

    /** @var string $company2_company_city
     * @Column(type="string", nullable=true)
     */
    protected $company2_company_city;

    /** @var string $company2_company_state
     * @Column(type="string", nullable=true)
     */
    protected $company2_company_state;

    /** @var string $company2_company_zip
     * @Column(type="string", nullable=true)
     */
    protected $company2_company_zip;

    /** @var string $company2_company_phone
     * @Column(type="string", nullable=true)
     */
    protected $company2_company_phone;

    /** @ var string $company2_company_email
     * @Column(type="string", nullable=true)
     */
    protected $company2_company_email;

    /** @var string $company2_job_title
     * @Column(type="string", nullable=true)
     */
    protected $company2_job_title;

    /** @var string $company2_start_date
     * @Column(type="string", nullable=true)
     */
    protected $company2_start_date;

    /** @var string $company2_end_date
     * @Column(type="string", nullable=true)
     */
    protected $company2_end_date;

    /** @var string $company2_responsibilites
     * @Column(type="string", nullable=true)
     */
    protected $company2_responsibilites;

    /** @var string $company2_reason_for_leaving
     * @Column(type="string", nullable=true)
     */
    protected $company2_reason_for_leaving;

    /** @var boolean $company2_may_we_contact_employer
     * @Column(type="boolean", nullable=true)
     */
    protected $company2_may_we_contact_employer;

    /** @var string $company3_company_name
     * @Column(type="string", nullable=true)
     */
    protected $company3_company_name;

    /** @var string $company3_supervisor_name
     * @Column(type="string", nullable=true)
     */
    protected $company3_supervisor_name;

    /** @var string $company3_company_address
     * @Column(type="string", nullable=true)
     */
    protected $company3_company_address;

    /** @var string $company3_company_city
     * @Column(type="string", nullable=true)
     */
    protected $company3_company_city;

    /** @var string $company3_company_state
     * @Column(type="string", nullable=true)
     */
    protected $company3_company_state;

    /** @var string $company3_company_zip
     * @Column(type="string", nullable=true)
     */
    protected $company3_company_zip;

    /** @var string $company3_company_phone
     * @Column(type="string", nullable=true)
     */
    protected $company3_company_phone;

    /** @ var string $company3_company_email
     * @Column(type="string", nullable=true)
     */
    protected $company3_company_email;

    /** @var string $company3_job_title
     * @Column(type="string", nullable=true)
     */
    protected $company3_job_title;

    /** @var string $company3_start_date
     * @Column(type="string", nullable=true)
     */
    protected $company3_start_date;

    /** @var string $company3_end_date
     * @Column(type="string", nullable=true)
     */
    protected $company3_end_date;

    /** @var string $company3_responsibilites
     * @Column(type="string", nullable=true)
     */
    protected $company3_responsibilites;

    /** @var string $company3_reason_for_leaving
     * @Column(type="string", nullable=true)
     */
    protected $company3_reason_for_leaving;

    /** @var boolean $company3_may_we_contact_employer
     * @Column(type="boolean", nullable=true)
     */
    protected $company3_may_we_contact_employer;

    /** @Column(type="json", nullable=true) */
    protected $highschool;

    /** @Column(type="json", nullable=true) */
    protected $college;

    /** @Column(type="json", nullable=true) */
    protected $other_education;

    /** @var string $hs_or_ged
     * @Column(type="string", nullable=true)
     */
    protected $hs_or_ged;

    /** @ var string $college_name
     * @Column(type="string", nullable=true)
     */
    protected $college_name;

    /** @var string $college_city
     * @Column(type="string", nullable=true)
     */
    protected $college_city;

    /** @var string $college_state
     * @Column(type="string", nullable=true)
     */ 
    protected $college_state;

    /** @var string $college_subjects_major_degree
     * @Column(type="string", nullable=true)
     */
    protected $college_subjects_major_degree;

    /** @var string $college_year_graduated
     * @Column(type="string", nullable=true)
     */
    protected $college_year_graduated;

    /** @var string $ged_name
     * @Column(type="string", nullable=true)
     */
    protected $ged_name;

    /** @var string $ged_city
     * @Column(type="string", nullable=true)
     */
    protected $ged_city;

    /** @var string $ged_state
     * @Column(type="string", nullable=true)
     */
    protected $ged_state;

    /** @var string $ged_year_graduated
     * @Column(type="string", nullable=true)
     */
    protected $ged_year_graduated;

    /** @var string $hs_name
     * @Column(type="string", nullable=true)
     */
    protected $hs_name;

    /** @var string $hs_city
     * @Column(type="string", nullable=true)
     */
    protected $hs_city;

    /** @var string $hs_state
     * @Column(type="string", nullable=true)
     */
    protected $hs_state;

    /** @var string $hs_year_graduated
     * @Column(type="string", nullable=true)
     */
    protected $hs_year_graduated;

    /** @var string $other_education_name
     * @Column(type="string", nullable=true)
     */
    protected $other_education_name;

    /** @var string $other_education_city
     * @Column(type="string", nullable=true)
     */
    protected $other_education_city;

    /** @var string $other_education_state
     * @Column(type="string", nullable=true)
     */
    protected $other_education_state;

    /** @var string $other_education_year_graduated
     * @Column(type="string", nullable=true)
     */
    protected $other_education_year_graduated;

    /** @var string $other_education_subjects_major_degree
     * @Column(type="string", nullable=true)
     */
    protected $other_education_subjects_major_degree;

    /** @Column(type="json", nullable=true) */
    protected $professional_reference_one;

    /** @Column(type="json", nullable=true) */
    protected $professional_reference_two;

    /** @var string $professional_reference_one_name
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_one_name;

    /** @var string $professional_reference_one_relationship
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_one_relationship;

    /** @var string $professional_reference_one_company
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_one_company;

    /** @var string $professional_reference_one_phone
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_one_phone;

    /** @var string $professional_reference_two_name
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_two_name;

    /** @var string $professional_reference_two_relationship
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_two_relationship;

    /** @var string $professional_reference_two_company
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_two_company;

    /** @var string $professional_reference_two_phone
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_two_phone;

    /** @var string $professional_reference_three_name
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_three_name;

    /** @var string $professional_reference_three_relationship
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_three_relationship;

    /** @var string $professional_reference_three_company
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_three_company;

    /** @var string $professional_reference_three_phone
     * @Column(type="string", nullable=true)
     */
    protected $professional_reference_three_phone;

    /** @Column(type="json", nullable=true) */
    protected $employment;

    /** @Column(type="json", nullable=true) */
    protected $criminal_record;

    /** @Column(type="json", nullable=true) */
    protected $nurse_stat_info;

    /** @Column(type="json", nullable=true) */
    protected $direct_deposit;

    /** @Column(type="json", nullable=true) */
    protected $license_and_certifications;

    /** @Column(type="json", nullable=true) */
    protected $emergency_contact_one;

    /** @Column(type="json", nullable=true) */
    protected $emergency_contact_two;

    /** @Column(type="json", nullable=true) */
    protected $terms;

    /** @Column(type="json", nullable=true) */
    protected $medical_history;

    /** @Column(type="json", nullable=true) */
    protected $tb;

    /** @Column(type="json", nullable=true) */
    protected $files;

    /** @var string $agreement_signature
     * @Column(type="string", nullable=true)
     */
    protected $agreement_signature;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return NurseApplication
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param mixed $member
     * @return NurseApplication
     */
    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubmittedAt()
    {
        return $this->submitted_at;
    }

    /**
     * @param mixed $submitted_at
     * @return NurseApplication
     */
    public function setSubmittedAt($submitted_at)
    {
        $this->submitted_at = $submitted_at;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApprovedAt()
    {
        return $this->approved_at;
    }

    /**
     * @param mixed $approved_at
     * @return NurseApplication
     */
    public function setApprovedAt($approved_at)
    {
        $this->approved_at = $approved_at;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeclinedAt()
    {
        return $this->declined_at;
    }

    /**
     * @param mixed $declined_at
     * @return NurseApplication
     */
    public function setDeclinedAt($declined_at)
    {
        $this->declined_at = $declined_at;
        return $this;
    }

    /**
     * Set date of birth
     *
     * @param string $dob
     * @return NurseApplication
     */
    public function setDOB($dob)
    {
        $this->dob = $dob;

        return $this;
    }

    /**
     * Get dob
     *
     * @return string
     */
    public function getDOB()
    {
        return $this->dob;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return NurseApplication
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set explanation
     *
     * @param string $explanation
     * @return NurseApplication
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

        return $this;
    }

    /**
     * Get explanation
     *
     * @return string
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Set citizen_of_usa
     *
     * @param boolean $citizen_of_usa
     * @return NurseApplication
     */
    public function setIsCitizen($citizen_of_usa)
    {
        $this->citizen_of_usa = $citizen_of_usa;

        return $this;
    }

    /**
     * Get citizen_of_usa
     *
     * @return boolean 
     */
    public function getIsCitizen()
    {
        return $this->citizen_of_usa;
    }

    /**
     * Set allowed_to_work
     *
     * @param boolean $allowed_to_work
     * @return NurseApplication
     */
    public function setIsAllowedToWork($allowed_to_work)
    {
        $this->allowed_to_work = $allowed_to_work;

        return $this;
    }

    /**
     * Get allowed_to_work
     *
     * @return boolean 
     */
    public function getIsAllowedToWork()
    {
        return $this->allowed_to_work;
    }

    /**
     * Set social security number
     *
     * @param string $social_security_number
     * @return NurseApplication
     */
    public function setSocSec($social_security_number)
    {
        $this->social_security_number = $social_security_number;

        return $this;
    }

    /**
     * Get social_security_number
     *
     * @return string
     */
    public function getSocSec()
    {
        return $this->social_security_number;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * @param string $phone_number
     * @return NurseApplication
     */
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
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
     * @return NurseApplication
     */
    public function setStreetAddress($street_address)
    {
        $this->street_address = $street_address;
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
     * @param string $street_address_2
     * @return NurseApplication
     */
    public function setStreetAddress2($street_address_2)
    {
        $this->street_address_2 = $street_address_2;
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
     * @return NurseApplication
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
     * @param string $state
     * @return NurseApplication
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
     * @param string $zipcode
     * @return NurseApplication
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * @param mixed $nurse
     * @return NurseApplication
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmploymentDetailsOne()
    {
        return $this->employment_details_one;
    }

    /**
     * @param mixed $employment_details_one
     * @return NurseApplication
     */
    public function setEmploymentDetailsOne($employment_details_one)
    {
        $this->employment_details_one = $employment_details_one;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmploymentDetailsTwo()
    {
        return $this->employment_details_two;
    }

    /**
     * @param mixed $employment_details_two
     * @return NurseApplication
     */
    public function setEmploymentDetailsTwo($employment_details_two)
    {
        $this->employment_details_two = $employment_details_two;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmploymentDetailsThree()
    {
        return $this->employment_details_three;
    }

    /**
     * @param mixed $employment_details_three
     * @return NurseApplication
     */
    public function setEmploymentDetailsThree($employment_details_three)
    {
        $this->employment_details_three = $employment_details_three;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getOneYearLTCExperience()
    {
        return $this->one_year_ltc_experience;
    }

    /**
     * @param boolean $one_year_ltc_experience
     * @return NurseApplication
     */
    public function setOneYearLTCExperience($one_year_ltc_experience)
    {
        $this->one_year_ltc_experience = $one_year_ltc_experience;
        return $this;
    }

    /**
     * @return string
     */
    public function getOneYearExplanation()
    {
        return $this->one_year_explanation;
    }

    /**
     * @param string $one_year_explanation
     * @return NurseApplication
     */
    public function setOneYearExplanation($one_year_explanation)
    {
        $this->one_year_explanation = $one_year_explanation;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCurrentlyEmployed()
    {
        return $this->currently_employed;
    }

    /**
     * @param boolean $currently_employed
     * @return NurseApplication
     */
    public function setCurrentlyEmployed($currently_employed)
    {
        $this->currently_employed = $currently_employed;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1CompanyName()
    {
        return $this->company1_company_name;
    }

    /**
     * @param string $company1_company_name
     * @return NurseApplication
     */
    public function setCompany1CompanyName($company1_company_name)
    {
        $this->company1_company_name = $company1_company_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1SupervisorName()
    {
        return $this->company1_supervisor_name;
    }
    
    /**
     * @param string $company1_supervisor_name
     * @return NurseApplication
     */
    public function setCompany1SupervisorName($company1_supervisor_name)
    {
        $this->company1_supervisor_name = $company1_supervisor_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1CompanyAddress()
    {
        return $this->company1_company_address;
    }

    /**
     * @param string $company1_company_address
     * @return NurseApplication
     */
    public function setCompany1CompanyAddress($company1_company_address)
    {
        $this->company1_company_address = $company1_company_address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1CompanyCity()
    {
        return $this->company1_company_city;
    }

    /**
     * @param string $company1_company_city
     * @return NurseApplication
     */
    public function setCompany1CompanyCity($company1_company_city)
    {
        $this->company1_company_city = $company1_company_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1CompanyState()
    {
        return $this->company1_company_state;
    }

    /**
     * @param string $company1_company_state
     * @return NurseApplication
     */
    public function setCompany1CompanyState($company1_company_state)
    {
        $this->company1_company_state = $company1_company_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1CompanyZip()
    {
        return $this->company1_company_zip;
    }

    /**
     * @param string $company1_company_zip
     * @return NurseApplication
     */
    public function setCompany1CompanyZip($company1_company_zip)
    {
        $this->company1_company_zip = $company1_company_zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1CompanyPhone()
    {
        return $this->company1_company_phone;
    }

    /**
     * @param string $company1_company_phone
     * @return NurseApplication
     */
    public function setCompany1CompanyPhone($company1_company_phone)
    {
        $this->company1_company_phone = $company1_company_phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1CompanyEmail()
    {
        return $this->company1_company_email;
    }

    /**
     * @param string $company1_company_email
     * @return NurseApplication
     */
    public function setCompany1CompanyEmail($company1_company_email)
    {
        $this->company1_company_email = $company1_company_email;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1JobTitle()
    {
        return $this->company1_job_title;
    }

    /**
     * @param string $company1_job_title
     * @return NurseApplication
     */
    public function setCompany1JobTitle($company1_job_title)
    {
        $this->company1_job_title = $company1_job_title;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1StartDate()
    {
        return $this->company1_start_date;
    }

    /**
     * @param string $company1_start_date
     * @return NurseApplication
     */
    public function setCompany1StartDate($company1_start_date)
    {
        $this->company1_start_date = $company1_start_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1EndDate()
    {
        return $this->company1_end_date;
    }

    /**
     * @param string $company1_end_date
     * @return NurseApplication
     */
    public function setCompany1EndDate($company1_end_date)
    {
        $this->company1_end_date = $company1_end_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1Responsibilites()
    {
        return $this->company1_responsibilites;
    }

    /**
     * @param string $company1_responsibilites
     * @return NurseApplication
     */
    public function setCompany1Responsibilites($company1_responsibilites)
    {
        $this->company1_responsibilites = $company1_responsibilites;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany1ReasonForLeaving()
    {
        return $this->company1_reason_for_leaving;
    }

    /**
     * @param string $company1_reason_for_leaving
     * @return NurseApplication
     */
    public function setCompany1ReasonForLeaving($company1_reason_for_leaving)
    {
        $this->company1_reason_for_leaving = $company1_reason_for_leaving;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCompany1MayWeContactEmployer()
    {
        return $this->company1_may_we_contact_employer;
    }

    /**
     * @param boolean $company1_may_we_contact_employer
     * @return NurseApplication
     */
    public function setCompany1MayWeContactEmployer($company1_may_we_contact_employer)
    {
        $this->company1_may_we_contact_employer = $company1_may_we_contact_employer;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2CompanyName()
    {
        return $this->company2_company_name;
    }

    /**
     * @param string $company2_company_name
     * @return NurseApplication
     */
    public function setCompany2CompanyName($company2_company_name)
    {
        $this->company2_company_name = $company2_company_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2SupervisorName()
    {
        return $this->company2_supervisor_name;
    }

    /**
     * @param string $company2_supervisor_name
     * @return NurseApplication
     */
    public function setCompany2SupervisorName($company2_supervisor_name)
    {
        $this->company2_supervisor_name = $company2_supervisor_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2CompanyAddress()
    {
        return $this->company2_company_address;
    }

    /**
     * @param string $company2_company_address
     * @return NurseApplication
     */
    public function setCompany2CompanyAddress($company2_company_address)
    {
        $this->company2_company_address = $company2_company_address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2CompanyCity()
    {
        return $this->company2_company_city;
    }

    /**
     * @param string $company2_company_city
     * @return NurseApplication
     */
    public function setCompany2CompanyCity($company2_company_city)
    {
        $this->company2_company_city = $company2_company_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2CompanyState()
    {
        return $this->company2_company_state;
    }

    /**
     * @param string $company2_company_state
     * @return NurseApplication
     */
    public function setCompany2CompanyState($company2_company_state)
    {
        $this->company2_company_state = $company2_company_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2CompanyZip()
    {
        return $this->company2_company_zip;
    }

    /**
     * @param string $company2_company_zip
     * @return NurseApplication
     */
    public function setCompany2CompanyZip($company2_company_zip)
    {
        $this->company2_company_zip = $company2_company_zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2CompanyPhone()
    {
        return $this->company2_company_phone;
    }

    /**
     * @param string $company2_company_phone
     * @return NurseApplication
     */
    public function setCompany2CompanyPhone($company2_company_phone)
    {
        $this->company2_company_phone = $company2_company_phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2CompanyEmail()
    {
        return $this->company2_company_email;
    }

    /**
     * @param string $company2_company_email
     * @return NurseApplication
     */
    public function setCompany2CompanyEmail($company2_company_email)
    {
        $this->company2_company_email = $company2_company_email;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2JobTitle()
    {
        return $this->company2_job_title;
    }

    /**
     * @param string $company2_job_title
     * @return NurseApplication
     */
    public function setCompany2JobTitle($company2_job_title)
    {
        $this->company2_job_title = $company2_job_title;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2StartDate()
    {
        return $this->company2_start_date;
    }

    /**
     * @param string $company2_start_date
     * @return NurseApplication
     */
    public function setCompany2StartDate($company2_start_date)
    {
        $this->company2_start_date = $company2_start_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2EndDate()
    {
        return $this->company2_end_date;
    }

    /**
     * @param string $company2_end_date
     * @return NurseApplication
     */
    public function setCompany2EndDate($company2_end_date)
    {
        $this->company2_end_date = $company2_end_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2Responsibilites()
    {
        return $this->company2_responsibilites;
    }

    /**
     * @param string $company2_responsibilites
     * @return NurseApplication
     */
    public function setCompany2Responsibilites($company2_responsibilites)
    {
        $this->company2_responsibilites = $company2_responsibilites;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany2ReasonForLeaving()
    {
        return $this->company2_reason_for_leaving;
    }

    /**
     * @param string $company2_reason_for_leaving
     * @return NurseApplication
     */
    public function setCompany2ReasonForLeaving($company2_reason_for_leaving)
    {
        $this->company2_reason_for_leaving = $company2_reason_for_leaving;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCompany2MayWeContactEmployer()
    {
        return $this->company2_may_we_contact_employer;
    }

    /**
     * @param boolean $company2_may_we_contact_employer
     * @return NurseApplication
     */
    public function setCompany2MayWeContactEmployer($company2_may_we_contact_employer)
    {
        $this->company2_may_we_contact_employer = $company2_may_we_contact_employer;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3CompanyName()
    {
        return $this->company3_company_name;
    }

    /**
     * @param string $company3_company_name
     * @return NurseApplication
     */
    public function setCompany3CompanyName($company3_company_name)
    {
        $this->company3_company_name = $company3_company_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3SupervisorName()
    {
        return $this->company3_supervisor_name;
    }

    /**
     * @param string $company3_supervisor_name
     * @return NurseApplication
     */
    public function setCompany3SupervisorName($company3_supervisor_name)
    {
        $this->company3_supervisor_name = $company3_supervisor_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3CompanyAddress()
    {
        return $this->company3_company_address;
    }

    /**
     * @param string $company3_company_address
     * @return NurseApplication
     */
    public function setCompany3CompanyAddress($company3_company_address)
    {
        $this->company3_company_address = $company3_company_address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3CompanyCity()
    {
        return $this->company3_company_city;
    }

    /**
     * @param string $company3_company_city
     * @return NurseApplication
     */
    public function setCompany3CompanyCity($company3_company_city)
    {
        $this->company3_company_city = $company3_company_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3CompanyState()
    {
        return $this->company3_company_state;
    }

    /**
     * @param string $company3_company_state
     * @return NurseApplication
     */
    public function setCompany3CompanyState($company3_company_state)
    {
        $this->company3_company_state = $company3_company_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3CompanyZip()
    {
        return $this->company3_company_zip;
    }

    /**
     * @param string $company3_company_zip
     * @return NurseApplication
     */
    public function setCompany3CompanyZip($company3_company_zip)
    {
        $this->company3_company_zip = $company3_company_zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3CompanyPhone()
    {
        return $this->company3_company_phone;
    }

    /**
     * @param string $company3_company_phone
     * @return NurseApplication
     */
    public function setCompany3CompanyPhone($company3_company_phone)
    {
        $this->company3_company_phone = $company3_company_phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3CompanyEmail()
    {
        return $this->company3_company_email;
    }

    /**
     * @param string $company3_company_email
     * @return NurseApplication
     */
    public function setCompany3CompanyEmail($company3_company_email)
    {
        $this->company3_company_email = $company3_company_email;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3JobTitle()
    {
        return $this->company3_job_title;
    }

    /**
     * @param string $company3_job_title
     * @return NurseApplication
     */
    public function setCompany3JobTitle($company3_job_title)
    {
        $this->company3_job_title = $company3_job_title;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3StartDate()
    {
        return $this->company3_start_date;
    }

    /**
     * @param string $company3_start_date
     * @return NurseApplication
     */
    public function setCompany3StartDate($company3_start_date)
    {
        $this->company3_start_date = $company3_start_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3EndDate()
    {
        return $this->company3_end_date;
    }

    /**
     * @param string $company3_end_date
     * @return NurseApplication
     */
    public function setCompany3EndDate($company3_end_date)
    {
        $this->company3_end_date = $company3_end_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3Responsibilites()
    {
        return $this->company3_responsibilites;
    }

    /**
     * @param string $company3_responsibilites
     * @return NurseApplication
     */
    public function setCompany3Responsibilites($company3_responsibilites)
    {
        $this->company3_responsibilites = $company3_responsibilites;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany3ReasonForLeaving()
    {
        return $this->company3_reason_for_leaving;
    }

    /**
     * @param string $company3_reason_for_leaving
     * @return NurseApplication
     */
    public function setCompany3ReasonForLeaving($company3_reason_for_leaving)
    {
        $this->company3_reason_for_leaving = $company3_reason_for_leaving;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCompany3MayWeContactEmployer()
    {
        return $this->company3_may_we_contact_employer;
    }

    /**
     * @param boolean $company3_may_we_contact_employer
     * @return NurseApplication
     */
    public function setCompany3MayWeContactEmployer($company3_may_we_contact_employer)
    {
        $this->company3_may_we_contact_employer = $company3_may_we_contact_employer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHighschool()
    {
        return $this->highschool;
    }

    /**
     * @param mixed $highschool
     * @return NurseApplication
     */
    public function setHighschool($highschool)
    {
        $this->highschool = $highschool;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @param mixed $college
     * @return NurseApplication
     */
    public function setCollege($college)
    {
        $this->college = $college;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOtherEducation()
    {
        return $this->other_education;
    }

    /**
     * @param mixed $other_education
     * @return NurseApplication
     */
    public function setOtherEducation($other_education)
    {
        $this->other_education = $other_education;
        return $this;
    }

    /**
     * @return string
     */
    public function getHSorGED()
    {
        return $this->hs_or_ged;
    }

    /**
     * @param string $hs_or_ged
     * @return NurseApplication
     */
    public function setHSorGED($hs_or_ged)
    {
        $this->hs_or_ged = $hs_or_ged;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollegeName()
    {
        return $this->college_name;
    }

    /**
     * @param string $college_name
     * @return NurseApplication
     */
    public function setCollegeName($college_name)
    {
        $this->college_name = $college_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollegeCity()
    {
        return $this->college_city;
    }

    /**
     * @param string $college_city
     * @return NurseApplication
     */
    public function setCollegeCity($college_city)
    {
        $this->college_city = $college_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollegeState()
    {
        return $this->college_state;
    }

    /**
     * @param string $college_state
     * @return NurseApplication
     */
    public function setCollegeState($college_state)
    {
        $this->college_state = $college_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollegeSubjects()
    {
        return $this->college_subjects_major_degree;
    }

    /**
     * @param string $college_subjects_major_degree
     * @return NurseApplication
     */
    public function setCollegeSubjects($college_subjects_major_degree)
    {
        $this->college_subjects_major_degree = $college_subjects_major_degree;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollegeGraduated()
    {
        return $this->college_year_graduated;
    }

    /**
     * @param string $college_year_graduated
     * @return NurseApplication
     */
    public function setCollegeGraduated($college_year_graduated)
    {
        $this->college_year_graduated = $college_year_graduated;
        return $this;
    }

    /**
     * @return string
     */
    public function getGEDName()
    {
        return $this->ged_name;
    }

    /**
     * @param string $ged_name
     * @return NurseApplication
     */
    public function setGEDName($ged_name)
    {
        $this->ged_name = $ged_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getGEDCity()
    {
        return $this->ged_city;
    }

    /**
     * @param string $ged_city
     * @return NurseApplication
     */
    public function setGEDCity($ged_city)
    {
        $this->ged_city = $ged_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getGEDState()
    {
        return $this->ged_state;
    }

    /**
     * @param string $ged_state
     * @return NurseApplication
     */
    public function setGEDState($ged_state)
    {
        $this->ged_state = $ged_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getGEDYearGraduated()
    {
        return $this->ged_year_graduated;
    }

    /**
     * @param string $ged_year_graduated
     * @return NurseApplication
     */
    public function setGEDYearGraduated($ged_year_graduated)
    {
        $this->ged_year_graduated = $ged_year_graduated;
        return $this;
    }

    /**
     * @return string
     */
    public function getHSName()
    {
        return $this->hs_name;
    }

    /**
     * @param string $hs_name
     * @return NurseApplication
     */
    public function setHSName($hs_name)
    {
        $this->hs_name = $hs_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getHSCity()
    {
        return $this->hs_city;
    }

    /**
     * @param string $hs_city
     * @return NurseApplication
     */
    public function setHSCity($hs_city)
    {
        $this->hs_city = $hs_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getHSState()
    {
        return $this->hs_state;
    }

    /**
     * @param string $hs_state
     * @return NurseApplication
     */
    public function setHSState($hs_state)
    {
        $this->hs_state = $hs_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getHSYearGraduated()
    {
        return $this->hs_year_graduated;
    }

    /**
     * @param string $hs_year_graduated
     * @return NurseApplication
     */
    public function setHSYearGraduated($hs_year_graduated)
    {
        $this->hs_year_graduated = $hs_year_graduated;
        return $this;
    }

    /**
     * @return string
     */
    public function getOtherEducationName()
    {
        return $this->other_education_name;
    }

    /**
     * @param string $other_education_name
     * @return NurseApplication
     */
    public function setOtherEducationName($other_education_name)
    {
        $this->other_education_name = $other_education_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOtherEducationCity()
    {
        return $this->other_education_city;
    }

    /**
     * @param string $other_education_city
     * @return NurseApplication
     */
    public function setOtherEducationCity($other_education_city)
    {
        $this->other_education_city = $other_education_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getOtherEducationState()
    {
        return $this->other_education_state;
    }

    /**
     * @param string $other_education_state
     * @return NurseApplication
     */
    public function setOtherEducationState($other_education_state)
    {
        $this->other_education_state = $other_education_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getOtherEducationYearGraduated()
    {
        return $this->other_education_year_graduated;
    }

    /**
     * @param string $other_education_year_graduated
     * @return NurseApplication
     */
    public function setOtherEducationYearGraduated($other_education_year_graduated)
    {
        $this->other_education_year_graduated = $other_education_year_graduated;
        return $this;
    }

    /**
     * @return string
     */
    public function getOtherEducationSubjects()
    {
        return $this->other_education_subjects_major_degree;
    }

    /**
     * @param string $other_education_subjects_major_degree
     * @return NurseApplication
     */
    public function setOtherEducationSubjects($other_education_subjects_major_degree)
    {
        $this->other_education_subjects_major_degree = $other_education_subjects_major_degree;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfessionalReferenceOne()
    {
        return $this->professional_reference_one;
    }

    /**
     * @param mixed $professional_reference_one
     * @return NurseApplication
     */
    public function setProfessionalReferenceOne($professional_reference_one)
    {
        $this->professional_reference_one = $professional_reference_one;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfessionalReferenceTwo()
    {
        return $this->professional_reference_two;
    }

    /**
     * @param mixed $professional_reference_two
     * @return NurseApplication
     */
    public function setProfessionalReferenceTwo($professional_reference_two)
    {
        $this->professional_reference_two = $professional_reference_two;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceOneName()
    {
        return $this->professional_reference_one_name;
    }

    /**
     * @param string $professional_reference_one_name
     * @return NurseApplication
     */
    public function setProfessionalReferenceOneName($professional_reference_one_name)
    {
        $this->professional_reference_one_name = $professional_reference_one_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceOneRelationship()
    {
        return $this->professional_reference_one_relationship;
    }

    /**
     * @param string $professional_reference_one_relationship
     * @return NurseApplication
     */
    public function setProfessionalReferenceOneRelationship($professional_reference_one_relationship)
    {
        $this->professional_reference_one_relationship = $professional_reference_one_relationship;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceOneCompany()
    {
        return $this->professional_reference_one_company;
    }

    /**
     * @param string $professional_reference_one_company
     * @return NurseApplication
     */
    public function setProfessionalReferenceOneCompany($professional_reference_one_company)
    {
        $this->professional_reference_one_company = $professional_reference_one_company;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceOnePhone()
    {
        return $this->professional_reference_one_phone;
    }

    /**
     * @param string $professional_reference_one_phone
     * @return NurseApplication
     */
    public function setProfessionalReferenceOnePhone($professional_reference_one_phone)
    {
        $this->professional_reference_one_phone = $professional_reference_one_phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceTwoName()
    {
        return $this->professional_reference_two_name;
    }

    /**
     * @param string $professional_reference_two_name
     * @return NurseApplication
     */
    public function setProfessionalReferenceTwoName($professional_reference_two_name)
    {
        $this->professional_reference_two_name = $professional_reference_two_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceTwoRelationship()
    {
        return $this->professional_reference_two_relationship;
    }

    /**
     * @param string $professional_reference_two_relationship
     * @return NurseApplication
     */
    public function setProfessionalReferenceTwoRelationship($professional_reference_two_relationship)
    {
        $this->professional_reference_two_relationship = $professional_reference_two_relationship;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceTwoCompany()
    {
        return $this->professional_reference_two_company;
    }

    /**
     * @param string $professional_reference_two_company
     * @return NurseApplication
     */
    public function setProfessionalReferenceTwoCompany($professional_reference_two_company)
    {
        $this->professional_reference_two_company = $professional_reference_two_company;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceTwoPhone()
    {
        return $this->professional_reference_two_phone;
    }

    /**
     * @param string $professional_reference_two_phone
     * @return NurseApplication
     */
    public function setProfessionalReferenceTwoPhone($professional_reference_two_phone)
    {
        $this->professional_reference_two_phone = $professional_reference_two_phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceThreeName()
    {
        return $this->professional_reference_three_name;
    }

    /**
     * @param string $professional_reference_three_name
     * @return NurseApplication
     */
    public function setProfessionalReferenceThreeName($professional_reference_three_name)
    {
        $this->professional_reference_three_name = $professional_reference_three_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceThreeRelationship()
    {
        return $this->professional_reference_three_relationship;
    }

    /**
     * @param string $professional_reference_three_relationship
     * @return NurseApplication
     */
    public function setProfessionalReferenceThreeRelationship($professional_reference_three_relationship)
    {
        $this->professional_reference_three_relationship = $professional_reference_three_relationship;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceThreeCompany()
    {
        return $this->professional_reference_three_company;
    }

    /**
     * @param string $professional_reference_three_company
     * @return NurseApplication
     */
    public function setProfessionalReferenceThreeCompany($professional_reference_three_company)
    {
        $this->professional_reference_three_company = $professional_reference_three_company;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfessionalReferenceThreePhone()
    {
        return $this->professional_reference_three_phone;
    }

    /**
     * @param string $professional_reference_three_phone
     * @return NurseApplication
     */
    public function setProfessionalReferenceThreePhone($professional_reference_three_phone)
    {
        $this->professional_reference_three_phone = $professional_reference_three_phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmployment()
    {
        return $this->employment;
    }

    /**
     * @param mixed $employment
     * @return NurseApplication
     */
    public function setEmployment($employment)
    {
        $this->employment = $employment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriminalRecord()
    {
        return $this->criminal_record;
    }

    /**
     * @param mixed $criminal_record
     * @return NurseApplication
     */
    public function setCriminalRecord($criminal_record)
    {
        $this->criminal_record = $criminal_record;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNurseStatInfo()
    {
        return $this->nurse_stat_info;
    }

    /**
     * @param mixed $nurse_stat_info
     * @return NurseApplication
     */
    public function setNurseStatInfo($nurse_stat_info)
    {
        $this->nurse_stat_info = $nurse_stat_info;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDirectDeposit()
    {
        return $this->direct_deposit;
    }

    /**
     * @param mixed $direct_deposit
     * @return NurseApplication
     */
    public function setDirectDeposit($direct_deposit)
    {
        $this->direct_deposit = $direct_deposit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenseAndCertifications()
    {
        return $this->license_and_certifications ?? [];
    }

    /**
     * @param mixed $license_and_certifications
     * @return NurseApplication
     */
    public function setLicenseAndCertifications($licenses_and_certifications)
    {
        $this->license_and_certifications = $licenses_and_certifications ?: null;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactOne()
    {
        return $this->emergency_contact_one;
    }

    /**
     * @param mixed $emergency_contact_one
     * @return NurseApplication
     */
    public function setEmergencyContactOne($emergency_contact_one)
    {
        $this->emergency_contact_one = $emergency_contact_one;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactTwo()
    {
        return $this->emergency_contact_two;
    }

    /**
     * @param mixed $emergency_contact_two
     * @return NurseApplication
     */
    public function setEmergencyContactTwo($emergency_contact_two)
    {
        $this->emergency_contact_two = $emergency_contact_two;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @param mixed $terms
     * @return NurseApplication
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTb()
    {
        return $this->tb;
    }

    /**
     * @param mixed $tb
     * @return NurseApplication
     */
    public function setTb($tb)
    {
        $this->tb = $tb;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param mixed $files
     * @return NurseApplication
     */
    public function setFiles($files)
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @return string
     */
    public function getAgreementSignature()
    {
        return $this->agreement_signature;
    }

    /**
     * @param string $agreement_signature
     * @return NurseApplication
     */
    public function setAgreementSignature($agreement_signature)
    {
        $this->agreement_signature = $agreement_signature;
        return $this;
    }
}
