<?php

namespace nst\member;

/**
 * @Entity(repositoryClass="NurseApplicationPartTwoRepository")
 * @Table(name="nurse_application_part_two")
 */
class NurseApplicationPartTwo
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @OneToOne(targetEntity="NstMember", inversedBy="nurse_application_part_two") */
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

    /** @Column(type="json", nullable=true) */
    protected $medical_history;

    /** @Column(type="datetime", nullable=true) */
    protected $tb_date;

    /** @Column(type="datetime", nullable=true) */
    protected $tb_chest_date;

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
     * @return mixed
     */
    public function getMedicalHistory()
    {
        return $this->medical_history;
    }

    /**
     * @param mixed $medical_history
     *
     * @return self
     */
    public function setMedicalHistory($medical_history)
    {
        $this->medical_history = $medical_history;

        return $this;
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
}
