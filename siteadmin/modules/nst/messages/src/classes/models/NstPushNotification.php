<?php

namespace nst\messages;

use nst\member\Nurse;
use nst\member\Provider;
use sa\messages\PushNotification;


/**
 * @Entity(repositoryClass="NstPushNotificationRepository")
 * @IOC_NAME="PushNotification"
 */
class NstPushNotification extends PushNotification {

    /**
     * @var boolean $is_read
     * @Column(type="boolean", nullable=true)
     */
    protected $is_read;

    /**
     * @var Nurse $nurse
     * @ManyToOne(targetEntity="\nst\member\Nurse", inversedBy="notifications")
     */
    protected $nurse;

    /**
     * @var Provider $provider
     * @ManyToOne(targetEntity="\nst\member\Provider", inversedBy="notifications")
     */
    protected $provider;

    /**
     * @return bool
     */
    public function getIsRead()
    {
        return $this->is_read;
    }

    /**
     * @param bool $is_read
     * @return NstPushNotification
     */
    public function setIsRead($is_read)
    {
        $this->is_read = $is_read;
        return $this;
    }

    /**
     * @return Nurse
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * @param Nurse $nurse
     * @return NstPushNotification
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;
        return $this;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param Provider $provider
     * @return NstPushNotification
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }


}
