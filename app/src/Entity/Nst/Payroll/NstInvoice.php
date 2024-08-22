<?php

namespace App\Entity\Nst\Payroll;

use App\Entity\Nst\Member\Provider;
use App\Entity\Nst\Quickbooks\QuickbooksInvoice;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class NstInvoice
 */
#[Table]
#[Entity(repositoryClass: 'InvoiceRepository')]
#[InheritanceType('SINGLE_TABLE')]
class NstInvoice extends QuickbooksInvoice
{
    /**
     * @var int $id
     */
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    /**
     * @var Provider $provider
     */
    #[ManyToOne(targetEntity: Provider::class, inversedBy: 'invoices')]
    protected $provider;

    /**
     * @var string $pay_period
     */
    #[Column(type: 'string', nullable: true)]
    protected $pay_period;

    /**
     * @var string $invoice_number
     */
    #[Column(type: 'string', nullable: true)]
    protected $invoice_number;

    /**
     * @var float $amount
     */
    #[Column(type: 'float', nullable: true)]
    protected $amount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return NstInvoice
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayPeriod()
    {
        return $this->pay_period;
    }

    /**
     * @param  string  $pay_period
     * @return NstInvoice
     */
    public function setPayPeriod($pay_period)
    {
        $this->pay_period = $pay_period;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * @param  string  $invoice_number
     * @return NstInvoice
     */
    public function setInvoiceNumber($invoice_number)
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param  float  $amount
     * @return NstInvoice
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
