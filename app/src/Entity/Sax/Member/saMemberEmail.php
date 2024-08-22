<?php

namespace App\Entity\Sax\Member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use sacore\application\ValidateException;
use sacore\utilities\fieldValidation;

#[Table(name: 'sa_member_email')]
#[Index(name: 'IDX_member_email_member', columns: ['member_id'])]
#[Entity(repositoryClass: 'saMemberEmailRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[HasLifecycleCallbacks]
class saMemberEmail
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string')]
    protected $email;

    #[Column(type: 'string')]
    protected $type;

    #[Column(type: 'string')]
    protected $is_primary;

    #[Column(type: 'string')]
    protected $is_active;

    #[ManyToOne(targetEntity: 'saMember', inversedBy: 'emails')]
    protected $member;

    #[PrePersist]
    public function validate()
    {
        $fv = new fieldValidation();
        $fv->isNotEmpty($this->email, 'Please enter an email address.');
        $fv->isNotEmpty($this->type, 'Please enter an email type.');

        if ($this->email) {
            $fv->isEmail($this->email, 'Please enter a valid email address.');
        }

        if ($fv->hasErrors()) {
            throw new ValidateException(implode('<br />', $fv->getErrors()));
        }
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param  string  $email
     * @return saMemberEmail
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set type
     *
     * @param  string  $type
     * @return saMemberEmail
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
     * @param  string  $isPrimary
     * @return saMemberEmail
     */
    public function setIsPrimary($isPrimary)
    {
        $this->is_primary = $isPrimary;

        return $this;
    }

    /**
     * Get is_primary
     *
     * @return string
     */
    public function getIsPrimary()
    {
        return $this->is_primary;
    }

    /**
     * Set is_active
     *
     * @param  string  $isActive
     * @return saMemberEmail
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return string
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set member
     *
     * @param  \sa\member\saMember  $member
     * @return saMemberEmail
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

    public function toArray()
    {

        return get_object_vars($this);
    }
}
