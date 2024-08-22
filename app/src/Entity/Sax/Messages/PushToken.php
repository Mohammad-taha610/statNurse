<?php

namespace App\Entity\Sax\Messages;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'sa_push_tokens')]
#[Entity(repositoryClass: 'pushTokenRepository')]
class PushToken
{
    public const DEVICE_TYPE_IOS = 'ios';

    public const DEVICE_TYPE_ANDROID = 'android';

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string')]
    protected $device_uuid;

    #[Column(type: 'string')]
    protected $platform;

    #[Column(type: 'string')]
    protected $token;

    #[Column(type: 'integer', nullable: true)]
    protected $user_id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  mixed  $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDeviceUuid()
    {
        return $this->device_uuid;
    }

    /**
     * @param  mixed  $device_uuid
     */
    public function setDeviceUuid($device_uuid)
    {
        $this->device_uuid = $device_uuid;
    }

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param  mixed  $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param  mixed  $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param  mixed  $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
}
