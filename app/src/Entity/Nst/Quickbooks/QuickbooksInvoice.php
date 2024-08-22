<?php

namespace App\Entity\Nst\Quickbooks;

use App\Entity\Sax\Files\saFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'QuickbooksInvoice')]
#[Entity(repositoryClass: 'QuickbooksRepository')]
#[InheritanceType('SINGLE_TABLE')]
class QuickbooksInvoice
{
    public const STATUS_UNPAID = 'Unpaid';

    public const STATUS_PAID = 'Paid';

    public const STATUS_REVIEW = 'Review';

    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'integer', nullable: true)]
    protected $quickbooks_id;

    #[Column(type: 'integer', nullable: true)]
    protected $total;

    #[OneToMany(mappedBy: 'invoice', targetEntity: QuickbooksLine::class)]
    protected $lines;

    #[OneToOne(targetEntity: saFile::class)]
    protected $invoice_file;

    /**
     * @var string $invoice_number
     */
    #[Column(type: 'string', nullable: true)]
    protected $invoice_number;

    /**
     * @var DateTime $date_created
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $date_created;

    /**
     * @var array $emails
     */
    #[Column(type: 'array', nullable: true)]
    protected $emails;

    /**
     * @var string $status
     */
    #[Column(type: 'string', nullable: true)]
    protected $status;

    public function __construct()
    {
        $this->lines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->date_created = new DateTime('now', app::getInstance()->getTimeZone());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int  $id
     * @return QuickbooksInvoice
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @param  ArrayCollection  $lines
     * @return QuickbooksInvoice
     */
    public function setLines($lines)
    {
        $this->lines = $lines;

        return $this;
    }

    /**
     * @param  QuickbooksLine  $line
     * @return QuickbooksInvoice
     */
    public function addLine($line)
    {
        $this->lines[] = $line;

        return $this;
    }

    /**
     * @param  QuickbooksLine  $line
     * @return QuickbooksInvoice
     */
    public function removeLine($line)
    {
        $this->lines->remove($line);

        return $this;
    }

    /**
     * @return int
     */
    public function getQuickbooksId()
    {
        return $this->quickbooks_id;
    }

    /**
     * @param  int  $quickbooks_id
     * @return QuickbooksInvoice
     */
    public function setQuickbooksId($quickbooks_id)
    {
        $this->quickbooks_id = $quickbooks_id;

        return $this;
    }

    /**
     * @return saFile
     */
    public function getInvoiceFile()
    {
        return $this->invoice_file;
    }

    /**
     * @param  saFile  $invoice_file
     * @return QuickbooksInvoice
     */
    public function setInvoiceFile($invoice_file)
    {
        $this->invoice_file = $invoice_file;

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
     * @return QuickbooksInvoice
     */
    public function setInvoiceNumber($invoice_number)
    {
        $this->invoice_number = $invoice_number;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param  float  $total
     * @return QuickbooksInvoice
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param  DateTime  $date_created
     * @return QuickbooksInvoice
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * @return array
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param  array  $emails
     * @return QuickbooksInvoice
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param  string  $status
     * @return QuickbooksInvoice
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
