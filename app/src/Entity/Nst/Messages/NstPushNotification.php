<?php

namespace App\Entity\Nst\Messages;

use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\Provider;
use App\Entity\Sax\Messages\PushNotification;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @IOC_NAME="PushNotification"
 */
#[Entity(repositoryClass: 'NstPushNotificationRepository')]
class NstPushNotification extends PushNotification
{
    /**
     * @var bool $is_read
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $is_read;

    /**
     * @var Nurse $nurse
     */
    #[ManyToOne(targetEntity: Nurse::class, inversedBy: 'notifications')]
    protected $nurse;

    /**
     * @var Provider $provider
     */
    #[ManyToOne(targetEntity: Provider::class, inversedBy: 'notifications')]
    protected $provider;

    /**
     * @return bool
     */
    public function getIsRead()
    {
        return $this->is_read;
    }

    /**
     * @param  bool  $is_read
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
     * @param  Nurse  $nurse
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
     * @param  Provider  $provider
     * @return NstPushNotification
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }
}
