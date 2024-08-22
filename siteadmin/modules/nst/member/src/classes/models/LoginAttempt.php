<?php

namespace nst\member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use sacore\application\DateTime;

/**
 * @Entity(repositoryClass="LoginAttemptsRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @Table("LoginAttempt")
 */
class LoginAttempt
{
    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @var string $ip
     * @Column(type="string", nullable=true)
     */
    protected $ip;


    /**
     * @var DateTime $attempt_time
     * @Column(type="datetime", nullable=true)
     */
    protected $attempt_time;

    /**
     * @var string $username
     * @Column(type="string", nullable=true)
     */
    protected $username;

    /**
     * @var string $device_id
     * @Column(type="string", nullable=true)
     */
    protected $device_id;

    /**
     * @param string $ip
     * @return LoginAttempt
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param DateTime $attempt_time
     * @return LoginAttempt
     */
    public function setAttemptTime(DateTime $attempt_time)
    {
        $this->attempt_time = $attempt_time;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getAttemptTime()
    {
        return $this->attempt_time;
    }

    /**
     * @param string $username
     * @return LoginAttempt
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $device_id
     * @return LoginAttempt
     */
    public function setDeviceId(string $device_id)
    {
        $this->device_id = $device_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return $this->device_id;
    }
}
