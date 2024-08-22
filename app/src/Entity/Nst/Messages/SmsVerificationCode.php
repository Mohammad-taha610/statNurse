<?php

namespace App\Entity\Nst\Messages;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @IOC_NAME="SmsVerificationCode"
 */
#[Entity(repositoryClass: 'SmsVerificationCodeRepository')]
#[Table(name: 'SmsVerificationCode')]
class SmsVerificationCode
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    protected $id;

    /**
     * @var string $code
     */
    #[Column(type: 'string', nullable: true)]
    protected $code;

    /**
     * @var string $phone_number
     */
    #[Column(type: 'string', nullable: true)]
    protected $phone_number;

    /**
     * @var \DateTime $time_sent
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $time_sent;

    /**
     * @param  string  $code
     * @return SmsVerificationCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param  string  $phone_number
     * @return SmsVerificationCode
     */
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * @param  \DateTime  $time_sent
     * @return SmsVerificationCode
     */
    public function setTimeSent($time_sent)
    {
        $this->time_sent = $time_sent;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTimeSent()
    {
        return $this->time_sent;
    }
}
