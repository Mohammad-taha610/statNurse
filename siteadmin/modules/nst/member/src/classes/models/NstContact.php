<?php

namespace nst\member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="NstContactRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table="Contacts"
 */
class NstContact
{

    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;
    /**
     * @var string $email_address
     * @Column(type="string", nullable=true)
     */
    protected $email_address;


    /**
     * @var string $phone_number
     * @Column(type="string", nullable=true)
     */
    protected $phone_number;

    /**
     * @var string $first_name
     * @Column(type="string", nullable=true)
     */
    protected $first_name;

    /**
     * @var string $last_name
     * @Column(type="string", nullable=true)
     */
    protected $last_name;

    /**
     * @var Provider $provider
     * @ManyToOne(targetEntity="\nst\member\Provider", inversedBy="contacts")
     */
    protected $provider;

    /**
     * @var boolean $receives_invoices
     * @Column(type="boolean", nullable=true)
     */
    protected $receives_invoices;

    /**
     * @var boolean $receives_sms
     * @Column(type="boolean", nullable=true)
     */
    protected $receives_sms;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return NstContact
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * @param string $email_address
     * @return NstContact
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;
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
     * @param string $phone_number
     * @return NstContact
     */
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     * @return NstContact
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     * @return NstContact
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
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
     * @return NstContact
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return bool
     */
    public function getReceivesInvoices()
    {
        return $this->receives_invoices;
    }

    /**
     * @param bool $receives_invoices
     * @return NstContact
     */
    public function setReceivesInvoices($receives_invoices)
    {
        $this->receives_invoices = $receives_invoices;
        return $this;
    }
 /**
     * @return bool
     */
    public function getReceivesSMS()
    {
        return $this->receives_sms;
    }

    /**
     * @param bool $receives_sms
     * @return NstContact
     */
    public function setReceivesSMS(bool $receives_sms): NstContact
    {
        $this->receives_sms = $receives_sms;
        return $this;
    }

}
