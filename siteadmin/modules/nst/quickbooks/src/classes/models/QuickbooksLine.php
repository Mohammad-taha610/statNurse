<?php

namespace nst\quickbooks;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * Class QuickbooksLine
 * @Entity(repositoryClass="QuickbooksRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @Table="QuickbooksLines")
 */
class QuickbooksLine
{
    const DETAIL_TYPE_SALES_ITEM_LINE_DETAIL = 'SalesItemLineDetail';
    const DETAIL_TYPE_DESCRIPTION_ONLY_LINE_DETAIL = 'DescriptionOnlyLineDetail';

    /**
     * @var integer $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;
    /**
     * @var string $detail_type
     * @Column(type="string", nullable=true)
     */
    protected $detail_type;

    /**
     * @var float $amount
     * @Column(type="float", nullable=true)
     */
    protected $amount;

    /**
     * @var array $line_detail
     * @Column(type="array", nullable=true)
     */
    protected $line_detail;

    /**
     * @var QuickbooksInvoice $invoice
     * @ManyToOne(targetEntity="\nst\quickbooks\QuickbooksInvoice", inversedBy="lines")
     * @JoinColumn(name="invoice_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $invoice;

    /**
     * @var float $rate
     * @Column(type="float", nullable=true)
     */
    protected $rate;

    /**
     * @var float $quantity
     * @Column(type="float", nullable=true)
     */
    protected $quantity;

    /**
     * @var string $description
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @return string
     */
    public function getDetailType(): string
    {
        return $this->detail_type;
    }

    /**
     * @param string $detail_type
     */
    public function setDetailType(string $detail_type): void
    {
        $this->detail_type = $detail_type;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return array
     */
    public function getLineDetail(): array
    {
        return $this->line_detail;
    }

    /**
     * @param array $line_detail
     */
    public function setLineDetail(array $line_detail): void
    {
        $this->line_detail = $line_detail;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return QuickbooksLine
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return QuickbooksInvoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param QuickbooksInvoice $invoice
     * @return QuickbooksLine
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     * @return QuickbooksLine
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     * @return QuickbooksLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return QuickbooksLine
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }



}