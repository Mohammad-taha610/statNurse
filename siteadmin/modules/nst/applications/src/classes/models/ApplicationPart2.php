<?php

namespace nst\applications;

use Doctrine\Common\Collections\ArrayCollection;
use nst\member\NstFile;
use nst\messages\NstMessage;

/**
 * @Entity(repositoryClass="Application2Repository")
 * @Table(name="ApplicationPart2")
 */
class ApplicationPart2
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @OneToOne(targetEntity="nst\member\NstMember", inversedBy="application_part2") */
    protected $member;

    /** @Column(type="string", nullable=true) */
    protected $associate_first_name;

    /** @Column(type="string", nullable=true) */
    protected $associate_last_name;

    /** @Column(type="string", nullable=true) */
    protected $associate_phone_number;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_one_first_name;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_one_last_name;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_one_relationship;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_one_phone_number;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_two_first_name;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_two_last_name;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_two_relationship;

    /** @Column(type="string", nullable=true) */
    protected $emergency_contact_two_phone_number;

    /** @Column(type="json", nullable=true) */
    protected $terms_signature;

    /** @Column(type="datetime", nullable=true) */
    protected $terms_date;

    /** @Column(type="string", nullable=true) */
    protected $terms_ip_address;

    /** @Column(type="json", nullable=true) */
    protected $medical_history;

    /** @Column(type="datetime", nullable=true) */
    protected $tb_date;

    /** @Column(type="datetime", nullable=true) */
    protected $tb_chest_date;

    /** @var string $injury_explanation
     * @Column(type="string", nullable=true)
     */
    protected $injury_explanation;

    /** @var boolean $routine_vaccinations
     * @Column(type="boolean", nullable=true)
     */
    protected $routine_vaccinations;

    /** @var boolean $hepatitis_b_vaccination
     * @Column(type="boolean", nullable=true)
     */
    protected $hepatitis_b_vaccination;

    /** @var boolean $hepatitis_a_vaccination
     * @Column(type="boolean", nullable=true)
     */
    protected $hepatitis_a_vaccination;

    /** @var boolean $covid_vaccination
     * @Column(type="boolean", nullable=true)
     */
    protected $covid_vaccination;

    /** @var boolean $covid_vaccination_exemption
     * @Column(type="boolean", nullable=true)
     */
    protected $covid_vaccination_exemption;

    /** @var boolean $positive_tb_screening
     * @Column(type="boolean", nullable=true)
     */
    protected $positive_tb_screening;

    /** @var string $positive_tb_screening_date
     * @Column(type="string", nullable=true)
     */
    protected $positive_tb_screening_date;

    /** @var boolean $chest_xray
     * @Column(type="boolean", nullable=true)
     */
    protected $chest_xray;

    /** @var string $chest_xray_date
     * @Column(type="string", nullable=true)
     */
    protected $chest_xray_date;

    /** @var string $medical_history_signature
     * @Column(type="string", nullable=true)
     */
    protected $medical_history_signature;

    /** @var string $pay_type
     * @Column(type="string", nullable=true)
    */
    protected $pay_type;

    /** @var string $account_type
     * @Column(type="string", nullable=true)
     * */
    protected $account_type;

    /** @var string $account_number
     * @Column(type="string", nullable=true)
     * */
    protected $account_number;

    /** @var string $routing_number
     * @Column(type="string", nullable=true)
     * */
    protected $routing_number;

    /** @var string $bank_name
     * @Column(type="string", nullable=true)
     */
    protected $bank_name;

    /** @var string $heard_about_us
     * @Column(type="string", nullable=true)
     */
    protected $heard_about_us;

    /** @var string $heard_about_us_other
     * @Column(type="string", nullable=true)
     */
    protected $heard_about_us_other;

    /** @var string $referrer
     * @Column(type="string", nullable=true)
     */
    protected $referrer;

    /** @var string $license1_state
     * @Column(type="string", nullable=true)
     */
    protected $license1_state;

    /** @var string $license1_number
     * @Column(type="string", nullable=true)
     */
    protected $license1_number;

    /** @var string $license1_full_name
     * @Column(type="string", nullable=true)
     */
    protected $license1_full_name;

    /** @var string $license1_expiration
     * @Column(type="datetime", nullable=true)
     */
    protected $license1_expiration;

    /** @var boolean $license1_accepted
     * @Column(type="boolean", nullable=true)
     */
    protected $license1_accepted;

    /** @var string $license2_state
     * @Column(type="string", nullable=true)
     */
    protected $license2_state;

    /** @var string $license2_number
     * @Column(type="string", nullable=true)
     */
    protected $license2_number;

    /** @var string $license2_full_name
     * @Column(type="string", nullable=true)
     */
    protected $license2_full_name;

    /** @var string $license2_expiration
     * @Column(type="datetime", nullable=true)
     */
    protected $license2_expiration;

    /** @var string $license2_accepted
     * @Column(type="boolean", nullable=true)
     */
    protected $license2_accepted;

    /** @var string $license3_state
     * @Column(type="string", nullable=true)
     */
    protected $license3_state;

    /** @var string $license3_number
     * @Column(type="string", nullable=true)
     */
    protected $license3_number;

    /** @var string $license3_full_name
     * @Column(type="string", nullable=true)
     */
    protected $license3_full_name;

    /** @var string $license3_expiration
     * @Column(type="datetime", nullable=true)
     */
    protected $license3_expiration;

    /** @var string $license3_accepted
     * @Column(type="boolean", nullable=true)
     */
    protected $license3_accepted;

    /**
     * @var ArrayCollection $application_files
     * @OneToMany(targetEntity="\nst\member\NstFile", mappedBy="nurse_application_part_2")
     */
    protected $application_files;

    /**
     * @var ArrayCollection $messages
     * @OneToMany(targetEntity="\nst\messages\NstMessage", mappedBy="application_2")
     */
    protected $messages;

    /** @OneToOne(targetEntity="\nst\applications\ApplicationStatus", inversedBy="application_part2")
     * @JoinColumn(name="application_status_id", referencedColumnName="id")
    */
    protected $application_status;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return self
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
     *
     * @return self
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAssociateFirstName()
    {
        return $this->associate_first_name;
    }

    /**
     * @param mixed $associate_first_name
     *
     * @return self
     */
    public function setAssociateFirstName($associate_first_name)
    {
        $this->associate_first_name = $associate_first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAssociateLastName()
    {
        return $this->associate_last_name;
    }

    /**
     * @param mixed $associate_last_name
     *
     * @return self
     */
    public function setAssociateLastName($associate_last_name)
    {
        $this->associate_last_name = $associate_last_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAssociatePhoneNumber()
    {
        return $this->associate_phone_number;
    }

    /**
     * @param mixed $associate_phone_number
     *
     * @return self
     */
    public function setAssociatePhoneNumber($associate_phone_number)
    {
        $this->associate_phone_number = $associate_phone_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactOneFirstName()
    {
        return $this->emergency_contact_one_first_name;
    }

    /**
     * @param mixed $emergency_contact_one_first_name
     *
     * @return self
     */
    public function setEmergencyContactOneFirstName($emergency_contact_one_first_name)
    {
        $this->emergency_contact_one_first_name = $emergency_contact_one_first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactOneLastName()
    {
        return $this->emergency_contact_one_last_name;
    }

    /**
     * @param mixed $emergency_contact_one_last_name
     *
     * @return self
     */
    public function setEmergencyContactOneLastName($emergency_contact_one_last_name)
    {
        $this->emergency_contact_one_last_name = $emergency_contact_one_last_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactOneRelationship()
    {
        return $this->emergency_contact_one_relationship;
    }

    /**
     * @param mixed $emergency_contact_one_relationship
     *
     * @return self
     */
    public function setEmergencyContactOneRelationship($emergency_contact_one_relationship)
    {
        $this->emergency_contact_one_relationship = $emergency_contact_one_relationship;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactOnePhoneNumber()
    {
        return $this->emergency_contact_one_phone_number;
    }

    /**
     * @param mixed $emergency_contact_one_phone_number
     *
     * @return self
     */
    public function setEmergencyContactOnePhoneNumber($emergency_contact_one_phone_number)
    {
        $this->emergency_contact_one_phone_number = $emergency_contact_one_phone_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactTwoFirstName()
    {
        return $this->emergency_contact_two_first_name;
    }

    /**
     * @param mixed $emergency_contact_two_first_name
     *
     * @return self
     */
    public function setEmergencyContactTwoFirstName($emergency_contact_two_first_name)
    {
        $this->emergency_contact_two_first_name = $emergency_contact_two_first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactTwoLastName()
    {
        return $this->emergency_contact_two_last_name;
    }

    /**
     * @param mixed $emergency_contact_two_last_name
     *
     * @return self
     */
    public function setEmergencyContactTwoLastName($emergency_contact_two_last_name)
    {
        $this->emergency_contact_two_last_name = $emergency_contact_two_last_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactTwoRelationship()
    {
        return $this->emergency_contact_two_relationship;
    }

    /**
     * @param mixed $emergency_contact_two_relationship
     *
     * @return self
     */
    public function setEmergencyContactTwoRelationship($emergency_contact_two_relationship)
    {
        $this->emergency_contact_two_relationship = $emergency_contact_two_relationship;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactTwoPhoneNumber()
    {
        return $this->emergency_contact_two_phone_number;
    }

    /**
     * @param mixed $emergency_contact_two_phone_number
     *
     * @return self
     */
    public function setEmergencyContactTwoPhoneNumber($emergency_contact_two_phone_number)
    {
        $this->emergency_contact_two_phone_number = $emergency_contact_two_phone_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTermsSignature()
    {
        return $this->terms_signature;
    }

    /**
     * @param mixed $terms_signature
     *
     * @return self
     */
    public function setTermsSignature($terms_signature)
    {
        $this->terms_signature = $terms_signature;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTermsDate()
    {
        return $this->terms_date;
    }

    /**
     * @param mixed $terms_date
     *
     * @return self
     */
    public function setTermsDate($terms_date)
    {
        $this->terms_date = $terms_date;

        return $this;
    }

    /**
     * @param string $terms_ip_address
     * @return NurseApplicationPartTwo
     */
    public function setTermsIpAddress($terms_ip_address)
    {
        $this->terms_ip_address = $terms_ip_address;
        return $this;
    }

    /**
     * @return string
     */
    public function getTermsIpAddress()
    {
        return $this->terms_ip_address;
    }

    /**
     * @return mixed
     */
    public function getMedicalHistory()
    {
        return $this->medical_history ?? [];
    }

    /**
     * @param mixed $medical_history
     * @return NurseApplicationPartTwo
     */
    public function setMedicalHistory($medicalHistory)
    {
        $this->medical_history = $medicalHistory ?: null;
    }


    /**
     * @return mixed
     */
    public function getTbDate()
    {
        return $this->tb_date;
    }

    /**
     * @param mixed $tb_date
     *
     * @return self
     */
    public function setTbDate($tb_date)
    {
        $this->tb_date = $tb_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTbChestDate()
    {
        return $this->tb_chest_date;
    }

    /**
     * @param mixed $tb_chest_date
     *
     * @return self
     */
    public function setTbChestDate($tb_chest_date)
    {
        $this->tb_chest_date = $tb_chest_date;

        return $this;
    }

    /**
     * @return string
     */
    public function getInjuryExplanation()
    {
        return $this->injury_explanation;
    }

    /**
     * @param string $injury_explanation
     * @return NurseApplicationPartTwo
     */
    public function setInjuryExplanation($injury_explanation)
    {
        $this->injury_explanation = $injury_explanation;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRoutineVaccinations()
    {
        return $this->routine_vaccinations;
    }

    /**
     * @param boolean $routine_vaccinations
     * @return NurseApplicationPartTwo
     */
    public function setRoutineVaccinations($routine_vaccinations)
    {
        $this->routine_vaccinations = $routine_vaccinations;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHepatitisBVaccination()
    {
        return $this->hepatitis_b_vaccination;
    }

    /**
     * @param boolean $hepatitis_b_vaccination
     * @return NurseApplicationPartTwo
     */
    public function setHepatitisBVaccination($hepatitis_b_vaccination)
    {
        $this->hepatitis_b_vaccination = $hepatitis_b_vaccination;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHepatitisAVaccination()
    {
        return $this->hepatitis_a_vaccination;
    }

    /**
     * @param boolean $hepatitis_a_vaccination
     * @return NurseApplicationPartTwo
     */
    public function setHepatitisAVaccination($hepatitis_a_vaccination)
    {
        $this->hepatitis_a_vaccination = $hepatitis_a_vaccination;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCovidVaccination()
    {
        return $this->covid_vaccination;
    }

    /**
     * @param boolean $covid_vaccination
     * @return NurseApplicationPartTwo
     */
    public function setCovidVaccination($covid_vaccination)
    {
        $this->covid_vaccination = $covid_vaccination;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCovidVaccinationExemption()
    {
        return $this->covid_vaccination_exemption;
    }

    /**
     * @param boolean $covid_vaccination_exemption
     * @return NurseApplicationPartTwo
     */
    public function setCovidVaccinationExemption($covid_vaccination_exemption)
    {
        $this->covid_vaccination_exemption = $covid_vaccination_exemption;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPreviousPositiveTBScreening()
    {
        return $this->positive_tb_screening;
    }

    /**
     * @param boolean $positive_tb_screening
     * @return NurseApplicationPartTwo
     */
    public function setPreviousPositiveTBScreening($positive_tb_screening)
    {
        $this->positive_tb_screening = $positive_tb_screening;
        return $this;
    }

    /**
     * @return string
     */
    public function getPositiveTBDate()
    {
        return $this->positive_tb_screening_date;
    }

    /**
     * @param string $positive_tb_screening_date
     * @return NurseApplicationPartTwo
     */
    public function setPositiveTBDate($positive_tb_screening_date)
    {
        $this->positive_tb_screening_date = $positive_tb_screening_date;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHadChestXRay()
    {
        return $this->chest_xray;
    }

    /**
     * @param boolean $chest_xray
     * @return NurseApplicationPartTwo
     */
    public function setHadChestXRay($chest_xray)
    {
        $this->chest_xray = $chest_xray;
        return $this;
    }

    /**
     * @return string
     */
    public function getChestXRayDate()
    {
        return $this->chest_xray_date;
    }

    /**
     * @param string $chest_xray_date
     * @return NurseApplicationPartTwo
     */
    public function setChestXRayDate($chest_xray_date)
    {
        $this->chest_xray_date = $chest_xray_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getMedicalHistorySignature()
    {
        return $this->medical_history_signature;
    }

    /**
     * @param string $medical_history_signature
     * @return NurseApplicationPartTwo
     */
    public function setMedicalHistorySignature($medical_history_signature)
    {
        $this->medical_history_signature = $medical_history_signature;
        return $this;
    }

    /**
     * @return string
     */
    public function getPayType()
    {
        return $this->pay_type;
    }

    /**
     * @param string $pay_type
     * @return NurseApplicationPartTwo
     */
    public function setPayType($pay_type)
    {
        $this->pay_type = $pay_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * @param string $account_type
     * @return NurseApplicationPartTwo
     */
    public function setAccountType($account_type)
    {
        $this->account_type = $account_type;
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
     * @param string $account_number
     * @return NurseApplicationPartTwo
     */
    public function setAccountNumber($account_number)
    {
        $this->account_number = $account_number;
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
     * @param string $routing_number
     * @return NurseApplicationPartTwo
     */
    public function setRoutingNumber($routing_number)
    {
        $this->routing_number = $routing_number;
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
     * @param string $bank_name
     * @return NurseApplicationPartTwo
     */
    public function setBankName($bank_name)
    {
        $this->bank_name = $bank_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeardAboutUs()
    {
        return $this->heard_about_us;
    }

    /**
     * @param string $heard_about_us
     * @return NurseApplicationPartTwo
     */
    public function setHeardAboutUs($heard_about_us)
    {
        $this->heard_about_us = $heard_about_us;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeardAboutUsOther()
    {
        return $this->heard_about_us_other;
    }

    /**
     * @param string $heard_about_us_other
     * @return NurseApplicationPartTwo
     */
    public function setHeardAboutUsOther($heard_about_us_other)
    {
        $this->heard_about_us_other = $heard_about_us_other;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @param string $referrer
     * @return NurseApplicationPartTwo
     */
    public function setReferrer($referrer)
    {
        $this->referrer = $referrer;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense1State()
    {
        return $this->license1_state;
    }

    /**
     * @param string $license1_state
     * @return NurseApplicationPartTwo
     */
    public function setLicense1State($license1_state)
    {
        $this->license1_state = $license1_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense1Number()
    {
        return $this->license1_number;
    }

    /**
     * @param string $license1_number
     * @return NurseApplicationPartTwo
     */
    public function setLicense1Number($license1_number)
    {
        $this->license1_number = $license1_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense1FullName()
    {
        return $this->license1_full_name;
    }

    /**
     * @param string $license1_full_name
     * @return NurseApplicationPartTwo
     */
    public function setLicense1FullName($license1_full_name)
    {
        $this->license1_full_name = $license1_full_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense1Expiration()
    {
        return $this->license1_expiration;
    }

    /**
     * @param string $license1_expiration
     * @return NurseApplicationPartTwo
     */
    public function setLicense1Expiration($license1_expiration)
    {
        $this->license1_expiration = $license1_expiration;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getLicense1Accepted()
    {
        return $this->license1_accepted;
    }

    /**
     * @param boolean $license1_accepted
     * @return NurseApplicationPartTwo
     */
    public function setLicense1Accepted($license1_accepted)
    {
        $this->license1_accepted = $license1_accepted;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense2State()
    {
        return $this->license2_state;
    }

    /**
     * @param string $license2_state
     * @return NurseApplicationPartTwo
     */
    public function setLicense2State($license2_state)
    {
        $this->license2_state = $license2_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense2Number()
    {
        return $this->license2_number;
    }

    /**
     * @param string $license2_number
     * @return NurseApplicationPartTwo
     */
    public function setLicense2Number($license2_number)
    {
        $this->license2_number = $license2_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense2FullName()
    {
        return $this->license2_full_name;
    }

    /**
     * @param string $license2_full_name
     * @return NurseApplicationPartTwo
     */
    public function setLicense2FullName($license2_full_name)
    {
        $this->license2_full_name = $license2_full_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense2Expiration()
    {
        return $this->license2_expiration;
    }

    /**
     * @param string $license2_expiration
     * @return NurseApplicationPartTwo
     */
    public function setLicense2Expiration($license2_expiration)
    {
        $this->license2_expiration = $license2_expiration;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getLicense2Accepted()
    {
        return $this->license2_accepted;
    }

    /**
     * @param boolean $license2_accepted
     * @return NurseApplicationPartTwo
     */
    public function setLicense2Accepted($license2_accepted)
    {
        $this->license2_accepted = $license2_accepted;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense3State()
    {
        return $this->license3_state;
    }

    /**
     * @param string $license3_state
     * @return NurseApplicationPartTwo
     */
    public function setLicense3State($license3_state)
    {
        $this->license3_state = $license3_state;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense3Number()
    {
        return $this->license3_number;
    }

    /**
     * @param string $license3_number
     * @return NurseApplicationPartTwo
     */
    public function setLicense3Number($license3_number)
    {
        $this->license3_number = $license3_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense3FullName()
    {
        return $this->license3_full_name;
    }

    /**
     * @param string $license3_full_name
     * @return NurseApplicationPartTwo
     */
    public function setLicense3FullName($license3_full_name)
    {
        $this->license3_full_name = $license3_full_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicense3Expiration()
    {
        return $this->license3_expiration;
    }

    /**
     * @param string $license3_expiration
     * @return NurseApplicationPartTwo
     */
    public function setLicense3Expiration($license3_expiration)
    {
        $this->license3_expiration = $license3_expiration;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getLicense3Accepted()
    {
        return $this->license3_accepted;
    }

    /**
     * @param boolean $license3_accepted
     * @return NurseApplicationPartTwo
     */
    public function setLicense3Accepted($license3_accepted)
    {
        $this->license3_accepted = $license3_accepted;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getApplicationFiles()
    {
        return $this->application_files;
    }

    /**
     * @param ArrayCollection $application_files
     * @return NurseApplicationPartTwo
     */
    public function setApplicationFiles($application_files)
    {
        $this->application_files = $application_files;
        return $this;
    }

    /**
     * @param NstFile $application_file
     * @return NurseApplicationPartTwo
     */
    public function addApplicationFile($application_file)
    {
        $this->application_files->add($application_file);
        return $this;
    }

    /**
     * @param NstFile $application_file
     * @return NurseApplicationPartTwo
     */
    public function removeApplicationFile($application_files)
    {
        $this->application_files->removeElement($application_files);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSmsMessages()
    {
        return $this->messages;
    }

    /**
     * @param NstMessage $messages
     * @return NurseApplicationPartTwo
     */
    public function addSmsMessage($message)
    {
        $this->messages->add($message);
        return $this;
    }

    /**
     * @param NstMessage $messages
     * @return NurseApplicationPartTwo
     */
    public function removeSmsMessage($message)
    {
        $this->messages->removeElement($message);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationStatus()
    {
        return $this->application_status;
    }

    /**
     * @param mixed $application_status
     * 
     * @return self
     */
    public function setApplicationStatus($application_status)
    {
        $this->application_status = $application_status;
        return $this;
    }
}
