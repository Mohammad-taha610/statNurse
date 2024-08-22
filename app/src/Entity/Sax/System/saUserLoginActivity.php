<?php

namespace App\Entity\Sax\System;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'sa_user_login_activity')]
#[Entity(repositoryClass: 'saUserLoginActivityRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class saUserLoginActivity
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'datetime', nullable: true)]
    protected $date;

    #[Column(type: 'boolean', nullable: true)]
    protected $was_success;

    #[Column(type: 'string', nullable: true)]
    protected $user_agent;

    #[Column(type: 'string', nullable: true)]
    protected $ip_address;

    #[Column(type: 'string', nullable: true)]
    protected $machine_uuid;

    #[ManyToOne(targetEntity: 'saUser', inversedBy: 'login_activity')]
    protected $user;

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
     * Set date
     *
     * @param  \DateTime  $date
     * @return saUserLoginActivity
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set wasSuccess
     *
     * @param  bool  $wasSuccess
     * @return saUserLoginActivity
     */
    public function setWasSuccess($wasSuccess)
    {
        $this->was_success = $wasSuccess;

        return $this;
    }

    /**
     * Get wasSuccess
     *
     * @return bool
     */
    public function getWasSuccess()
    {
        return $this->was_success;
    }

    /**
     * Set userAgent
     *
     * @param  string  $userAgent
     * @return saUserLoginActivity
     */
    public function setUserAgent($userAgent)
    {
        $this->user_agent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     * Set ipAddress
     *
     * @param  string  $ipAddress
     * @return saUserLoginActivity
     */
    public function setIpAddress($ipAddress)
    {
        $this->ip_address = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Set user
     *
     * @param  \sa\system\saUser  $user
     * @return saUserLoginActivity
     */
    public function setUser(\sa\system\saUser $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \sa\system\saUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set machineUuid
     *
     * @param  string  $machineUuid
     * @return saUserLoginActivity
     */
    public function setMachineUuid($machineUuid)
    {
        $this->machine_uuid = $machineUuid;

        return $this;
    }

    /**
     * Get machineUuid
     *
     * @return string
     */
    public function getMachineUuid()
    {
        return $this->machine_uuid;
    }
}
