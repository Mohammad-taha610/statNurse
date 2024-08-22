<?php

namespace sa\system;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="saOnlineUsersRepository")
 * @HasLifecycleCallbacks
 * @Table(name="sa_system_online_users", indexes={
 *     @Index(name="IDX_system_user_machine_id", columns={"machineId"}),
 *     @Index(name="IDX_system_user_last_visit_date", columns={"last_visit_date"}),
 *     @Index(name="IDX_system_user_last_last_page", columns={"last_page"}),
 *     @Index(name="IDX_system_user_last_view_count", columns={"view_count"}),
 *     @Index(name="IDX_system_user_last_ip_address", columns={"ip_address"}),
 *     @Index(name="IDX_system_user_last_ip_state", columns={"ip_state"}),
 *     @Index(name="IDX_system_user_last_ip_city", columns={"ip_city"}),
 *     @Index(name="IDX_system_user_last_ip_country", columns={"ip_country"}),
 *     @Index(name="IDX_system_user_last_ip_code", columns={"ip_code"}),
 *     @Index(name="IDX_system_user_last_user_agent", columns={"user_agent"})
 * })
 */

class OnlineUser {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="string", nullable=true) */
    protected $machineId;
    /** @Column(type="string", nullable=true)) */
    protected $first_page;
    /** @Column(type="string", nullable=true)) */
    protected $last_page;
    /** @Column(type="integer", nullable=true) */
    protected $view_count;
    /** @Column(type="string", nullable=true) */
    protected $ip_address;
    /** @Column(type="string", nullable=true) */
    protected $user_agent;
    /** @Column(type="boolean", nullable=true) */
    protected $was_idle;
    /** @Column(type="boolean", nullable=true) */
    protected $was_page_load;
    /** @Column(type="datetime", nullable=true) */
    protected $last_visit_date;

    /** @Column(type="string", nullable=true) */
    protected $ip_city;
    /** @Column(type="string", nullable=true) */
    protected $ip_state;
    /** @Column(type="string", nullable=true) */
    protected $ip_country;
    /** @Column(type="string", nullable=true) */
    protected $ip_code;
    /** @Column(type="string", nullable=true) */
    protected $ip_latitude;
    /** @Column(type="string", nullable=true) */
    protected $ip_longitude;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set machineId
     *
     * @param string $machineId
     *
     * @return OnlineUser
     */
    public function setMachineId($machineId)
    {
        $this->machineId = $machineId;

        return $this;
    }

    /**
     * Get machineId
     *
     * @return string
     */
    public function getMachineId()
    {
        return $this->machineId;
    }

    /**
     * Set lastVisitDate
     *
     * @param \DateTime $lastVisitDate
     *
     * @return OnlineUser
     */
    public function setLastVisitDate($lastVisitDate)
    {
        $this->last_visit_date = $lastVisitDate;

        return $this;
    }

    /**
     * Get lastVisitDate
     *
     * @return \DateTime
     */
    public function getLastVisitDate()
    {
        return $this->last_visit_date;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     *
     * @return OnlineUser
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
     * Set userAgent
     *
     * @param string $userAgent
     *
     * @return OnlineUser
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
     * Set wasIdle
     *
     * @param boolean $wasIdle
     *
     * @return OnlineUser
     */
    public function setWasIdle($wasIdle)
    {
        $this->was_idle = $wasIdle;

        return $this;
    }

    /**
     * Get wasIdle
     *
     * @return boolean
     */
    public function getWasIdle()
    {
        return $this->was_idle;
    }

    /**
     * Set wasPageLoad
     *
     * @param boolean $wasPageLoad
     *
     * @return OnlineUser
     */
    public function setWasPageLoad($wasPageLoad)
    {
        $this->was_page_load = $wasPageLoad;

        return $this;
    }

    /**
     * Get wasPageLoad
     *
     * @return boolean
     */
    public function getWasPageLoad()
    {
        return $this->was_page_load;
    }
    

    /**
     * Set viewCount
     *
     * @param string $viewCount
     *
     * @return OnlineUser
     */
    public function setViewCount($viewCount)
    {
        $this->view_count = $viewCount;

        return $this;
    }

    /**
     * Get viewCount
     *
     * @return integer
     */
    public function getViewCount()
    {
        return $this->view_count;
    }

    /**
     * Set first page
     *
     * @param string $firstPage
     *
     * @return OnlineUser
     */
    public function setFirstPage($firstPage)
    {
        $this->first_page = $firstPage;

        return $this;
    }

    /**
     * Get first Page
     *
     * @return string
     */
    public function getFirstPage()
    {
        return $this->first_page;
    }

    /**
     * Set lastPage
     *
     * @param string $lastPage
     *
     * @return OnlineUser
     */
    public function setLastPage($lastPage)
    {
        $this->last_page = $lastPage;

        return $this;
    }

    /**
     * Get lastPage
     *
     * @return string
     */
    public function getLastPage()
    {
        return $this->last_page;
    }

    /**
     * Set ipCity
     *
     * @param string $ipCity
     *
     * @return OnlineUser
     */
    public function setIpCity($ipCity)
    {
        $this->ip_city = $ipCity;

        return $this;
    }

    /**
     * Get ipCity
     *
     * @return string
     */
    public function getIpCity()
    {
        return $this->ip_city;
    }

    /**
     * Set ipCountry
     *
     * @param string $ipCountry
     *
     * @return OnlineUser
     */
    public function setIpCountry($ipCountry)
    {
        $this->ip_country = $ipCountry;

        return $this;
    }

    /**
     * Get ipCountry
     *
     * @return string
     */
    public function getIpCountry()
    {
        return $this->ip_country;
    }

    /**
     * Set ipCode
     *
     * @param string $ipCode
     *
     * @return OnlineUser
     */
    public function setIpCode($ipCode)
    {
        $this->ip_code = $ipCode;

        return $this;
    }

    /**
     * Get ipCode
     *
     * @return string
     */
    public function getIpCode()
    {
        return $this->ip_code;
    }

    /**
     * Set ipLatitude
     *
     * @param string $ipLatitude
     *
     * @return OnlineUser
     */
    public function setIpLatitude($ipLatitude)
    {
        $this->ip_latitude = $ipLatitude;

        return $this;
    }

    /**
     * Get ipLatitude
     *
     * @return string
     */
    public function getIpLatitude()
    {
        return $this->ip_latitude;
    }

    /**
     * Set ipLongitude
     *
     * @param string $ipLongitude
     *
     * @return OnlineUser
     */
    public function setIpLongitude($ipLongitude)
    {
        $this->ip_longitude = $ipLongitude;

        return $this;
    }

    /**
     * Get ipLongitude
     *
     * @return string
     */
    public function getIpLongitude()
    {
        return $this->ip_longitude;
    }

    /**
     * Set ipState
     *
     * @param string $ipState
     *
     * @return OnlineUser
     */
    public function setIpState($ipState)
    {
        $this->ip_state = $ipState;

        return $this;
    }

    /**
     * Get ipState
     *
     * @return string
     */
    public function getIpState()
    {
        return $this->ip_state;
    }
}
