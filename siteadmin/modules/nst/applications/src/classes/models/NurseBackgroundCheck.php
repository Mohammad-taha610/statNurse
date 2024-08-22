<?php

namespace nst\applications;

/**
 * @Entity(repositoryClass="NurseBackgroundCheckRepository")
 * @Table(name="nures_background_checks")
 */
class NurseBackgroundCheck
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @OneToOne(targetEntity="nst\member\NstMember", inversedBy="nurse_background_check") */
    protected $member;

    /** @Column(type="string", nullable=true) */
    protected $first_name;

    /** @Column(type="string", nullable=true) */
    protected $last_name;

    /** @Column(type="boolean", nullable=true) */
    protected $rights;

    /** @Column(type="boolean", nullable=true) */
    protected $compa;

    /** @Column(type="json", nullable=true) */
    protected $signature;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_first_name;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_middle_name;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_last_name;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_gender;

    /** @Column(type="datetime", nullable=true) */
    protected $personal_information_date_of_birth;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_address;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_apartment_number;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_city;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_state;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_zip_code;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_years_at_address;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_previous_address;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_previous_apartment_number;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_previous_city;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_previous_state;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_previous_zip_code;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_previous_years_at_address;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_drivers_license_number;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_drivers_license_state;

    /** @Column(type="string", nullable=true) */
    protected $personal_information_email;

    /** @Column(type="json", nullable=true) */
    protected $personal_information_signature;

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
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     *
     * @return self
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     *
     * @return self
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRights()
    {
        return $this->rights;
    }

    /**
     * @param mixed $rights
     *
     * @return self
     */
    public function setRights($rights)
    {
        $this->rights = $rights;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompa()
    {
        return $this->compa;
    }

    /**
     * @param mixed $compa
     *
     * @return self
     */
    public function setCompa($compa)
    {
        $this->compa = $compa;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param mixed $signature
     *
     * @return self
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationFirstName()
    {
        return $this->personal_information_first_name;
    }

    /**
     * @param mixed $personal_information_first_name
     *
     * @return self
     */
    public function setPersonalInformationFirstName($personal_information_first_name)
    {
        $this->personal_information_first_name = $personal_information_first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationMiddleName()
    {
        return $this->personal_information_middle_name;
    }

    /**
     * @param mixed $personal_information_middle_name
     *
     * @return self
     */
    public function setPersonalInformationMiddleName($personal_information_middle_name)
    {
        $this->personal_information_middle_name = $personal_information_middle_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationLastName()
    {
        return $this->personal_information_last_name;
    }

    /**
     * @param mixed $personal_information_last_name
     *
     * @return self
     */
    public function setPersonalInformationLastName($personal_information_last_name)
    {
        $this->personal_information_last_name = $personal_information_last_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationGender()
    {
        return $this->personal_information_gender;
    }

    /**
     * @param mixed $personal_information_gender
     *
     * @return self
     */
    public function setPersonalInformationGender($personal_information_gender)
    {
        $this->personal_information_gender = $personal_information_gender;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationDateOfBirth()
    {
        return $this->personal_information_date_of_birth;
    }

    /**
     * @param mixed $personal_information_date_of_birth
     *
     * @return self
     */
    public function setPersonalInformationDateOfBirth($personal_information_date_of_birth)
    {
        $this->personal_information_date_of_birth = $personal_information_date_of_birth;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationAddress()
    {
        return $this->personal_information_address;
    }

    /**
     * @param mixed $personal_information_address
     *
     * @return self
     */
    public function setPersonalInformationAddress($personal_information_address)
    {
        $this->personal_information_address = $personal_information_address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationApartmentNumber()
    {
        return $this->personal_information_apartment_number;
    }

    /**
     * @param mixed $personal_information_apartment_number
     *
     * @return self
     */
    public function setPersonalInformationApartmentNumber($personal_information_apartment_number)
    {
        $this->personal_information_apartment_number = $personal_information_apartment_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationCity()
    {
        return $this->personal_information_city;
    }

    /**
     * @param mixed $personal_information_city
     *
     * @return self
     */
    public function setPersonalInformationCity($personal_information_city)
    {
        $this->personal_information_city = $personal_information_city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationState()
    {
        return $this->personal_information_state;
    }

    /**
     * @param mixed $personal_information_state
     *
     * @return self
     */
    public function setPersonalInformationState($personal_information_state)
    {
        $this->personal_information_state = $personal_information_state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationZipCode()
    {
        return $this->personal_information_zip_code;
    }

    /**
     * @param mixed $personal_information_zip_code
     *
     * @return self
     */
    public function setPersonalInformationZipCode($personal_information_zip_code)
    {
        $this->personal_information_zip_code = $personal_information_zip_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationYearsAtAddress()
    {
        return $this->personal_information_years_at_address;
    }

    /**
     * @param mixed $personal_information_years_at_address
     *
     * @return self
     */
    public function setPersonalInformationYearsAtAddress($personal_information_years_at_address)
    {
        $this->personal_information_years_at_address = $personal_information_years_at_address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationPreviousAddress()
    {
        return $this->personal_information_previous_address;
    }

    /**
     * @param mixed $personal_information_previous_address
     *
     * @return self
     */
    public function setPersonalInformationPreviousAddress($personal_information_previous_address)
    {
        $this->personal_information_previous_address = $personal_information_previous_address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationPreviousApartmentNumber()
    {
        return $this->personal_information_previous_apartment_number;
    }

    /**
     * @param mixed $personal_information_previous_apartment_number
     *
     * @return self
     */
    public function setPersonalInformationPreviousApartmentNumber($personal_information_previous_apartment_number)
    {
        $this->personal_information_previous_apartment_number = $personal_information_previous_apartment_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationPreviousCity()
    {
        return $this->personal_information_previous_city;
    }

    /**
     * @param mixed $personal_information_previous_city
     *
     * @return self
     */
    public function setPersonalInformationPreviousCity($personal_information_previous_city)
    {
        $this->personal_information_previous_city = $personal_information_previous_city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationPreviousState()
    {
        return $this->personal_information_previous_state;
    }

    /**
     * @param mixed $personal_information_previous_state
     *
     * @return self
     */
    public function setPersonalInformationPreviousState($personal_information_previous_state)
    {
        $this->personal_information_previous_state = $personal_information_previous_state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationPreviousZipCode()
    {
        return $this->personal_information_previous_zip_code;
    }

    /**
     * @param mixed $personal_information_previous_zip_code
     *
     * @return self
     */
    public function setPersonalInformationPreviousZipCode($personal_information_previous_zip_code)
    {
        $this->personal_information_previous_zip_code = $personal_information_previous_zip_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationPreviousYearsAtAddress()
    {
        return $this->personal_information_previous_years_at_address;
    }

    /**
     * @param mixed $personal_information_previous_years_at_address
     *
     * @return self
     */
    public function setPersonalInformationPreviousYearsAtAddress($personal_information_previous_years_at_address)
    {
        $this->personal_information_previous_years_at_address = $personal_information_previous_years_at_address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationDriversLicenseNumber()
    {
        return $this->personal_information_drivers_license_number;
    }

    /**
     * @param mixed $personal_information_drivers_license_number
     *
     * @return self
     */
    public function setPersonalInformationDriversLicenseNumber($personal_information_drivers_license_number)
    {
        $this->personal_information_drivers_license_number = $personal_information_drivers_license_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationDriversLicenseState()
    {
        return $this->personal_information_drivers_license_state;
    }

    /**
     * @param mixed $personal_information_drivers_license_state
     *
     * @return self
     */
    public function setPersonalInformationDriversLicenseState($personal_information_drivers_license_state)
    {
        $this->personal_information_drivers_license_state = $personal_information_drivers_license_state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationEmail()
    {
        return $this->personal_information_email;
    }

    /**
     * @param mixed $personal_information_email
     *
     * @return self
     */
    public function setPersonalInformationEmail($personal_information_email)
    {
        $this->personal_information_email = $personal_information_email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersonalInformationSignature()
    {
        return $this->personal_information_signature;
    }

    /**
     * @param mixed $personal_information_signature
     *
     * @return self
     */
    public function setPersonalInformationSignature($personal_information_signature)
    {
        $this->personal_information_signature = $personal_information_signature;

        return $this;
    }
}
