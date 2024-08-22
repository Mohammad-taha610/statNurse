<?php

namespace sa\system;
use sacore\application\DateTime;

/**
 * @Entity(repositoryClass="saUserPushTokenRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @HasLifecycleCallbacks
 * @Table(name="sa_user_push_tokens")
 */
class saUserPushToken
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="string", nullable=true) */
    protected $service;
    /** @Column(type="string", nullable=true) */
    protected $token;
    /** @Column(type="datetime", nullable=true) */
    protected $date_updated;
    /** @Column(type="datetime", nullable=true) */
    protected $date_created;
    /** @ManyToOne(targetEntity="saUser", inversedBy="pushTokens") */
    protected $user;

    const sa_USER_TOKEN_GOOGLE = 'google';
    const sa_USER_TOKEN_APPLE = 'apple';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param mixed $date_updated
     */
    public function setDateUpdated($date_updated)
    {
        $this->date_updated = $date_updated;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param mixed $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /** @PrePersist */
    public function prePersist() {
        $this->setDateCreated(new DateTime());
    }

    /** @PreUpdate */
    public function preUpdate() {
        $this->setDateUpdated(new DateTime());
    }
}