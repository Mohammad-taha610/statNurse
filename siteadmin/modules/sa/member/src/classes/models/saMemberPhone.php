<?php
namespace sa\member;
use sacore\application\ValidateException;
use sacore\utilities\fieldValidation;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @InheritanceType("SINGLE_TABLE")
 * @Table(name="sa_member_phone", indexes={
 * @Index(name="IDX_member_phone_member", columns={"member_id"})
 * })
 */
class saMemberPhone
{
    /** @Id @Column(type="integer") @GeneratedValue */
    public $id;
    /** @Column(type="string", nullable=true) */
    public $phone;
    /** @Column(type="string", nullable=true) */
    public $type;
    /** @Column(type="boolean", nullable=true) */
    public $is_active;
    /** @Column(type="boolean", nullable=true) */
    public $is_primary;
    /** @ManyToOne(targetEntity="saMember", inversedBy="phones") */
    protected $member;

    /** @ManyToOne(targetEntity="saMemberUsers", inversedBy="phones") */
    protected $user;

    /**
     * @PrePersist @PreUpdate
     */
    public function validate()
    {
        $fv = new fieldValidation();

        if(strlen($this->phone) > 20) {
            $fv->adderror("Invalid phone number.");
        } else {
            $this->phone = preg_replace('/[^\d]/', '', $this->phone);
            $fv->isNotEmpty($this->phone, 'Please enter a phone number.');
        }

        if ($fv->hasErrors()) {
            throw new ValidateException(implode('<br />', $fv->getErrors()));
        }
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
     * Set phone
     *
     * @param string $phone
     * @return saMemberPhone
     */
    public function setPhone($phone)
    {
        $strippedPhone = preg_replace('/[^0-9]/', '', $phone);
        $this->phone = $strippedPhone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return saMemberPhone
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
     * Set is_active
     *
     * @param boolean $isActive
     * @return saMemberPhone
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
     * Set is_primary
     *
     * @param boolean $isPrimary
     * @return saMemberPhone
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
        return $this->is_primary;
    }

    /**
     * Set member
     *
     * @param \sa\member\saMember $member
     * @return saMemberPhone
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
     * Set user
     *
     * @param \sa\member\saMemberUsers $user
     *
     * @return saMemberPhone
     */
    public function setUser(\sa\member\saMemberUsers $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \sa\member\saMemberUsers
     */
    public function getUser()
    {
        return $this->user;
    }
}
