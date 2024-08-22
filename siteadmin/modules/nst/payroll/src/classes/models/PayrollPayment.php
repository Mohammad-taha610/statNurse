<?php


namespace nst\payroll;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use nst\events\Shift;
use nst\events\ShiftRecurrence;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sa\member\auth;
use sa\system\saAuth;
use sa\system\saUser;

/**
 * @Entity(repositoryClass="PayrollPaymentRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="payroll_payments")
 */
class PayrollPayment
{

    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer", nullable=false)`
     */
    protected $id;
    /**
     * @var Shift $shift
     * @ManyToOne(targetEntity="nst\events\Shift", inversedBy="payroll_payments")
     */
    protected $shift;
    /**
     * @var ShiftRecurrence $shift_recurrence
     * @ManyToOne(targetEntity="nst\events\ShiftRecurrence", inversedBy="payroll_payments")
     */
    protected $shift_recurrence;
    /**
     * @var float $pay_rate
     * @Column(type="float", nullable=false)
     */
    protected $pay_rate;
    /**
     * @var float $bill_rate
     * @Column(type="float", nullable=false)
     */
    protected $bill_rate;
    /**
     * @var float $pay_bonus
     * @Column(type="float", nullable=true)
     */
    protected $pay_bonus;
    /**
     * @var float $bill_bonus
     * @Column(type="float", nullable=true)
     */
    protected $bill_bonus;
    /**
     * @var float $pay_travel
     * @Column(type="float", nullable=true)
     */
    protected $pay_travel;
    /**
     * @var float $bill_travel
     * @Column(type="float", nullable=true)
     */
    protected $bill_travel;
    /**
     * @var float $pay_total
     * @Column(type="float", nullable=false)
     */
    protected $pay_total;
    /**
     * @var float $bill_total
     * @Column(type="float", nullable=false)
     */
    protected $bill_total;
    /**
     * @var string $payment_method
     * @Column(type="string", nullable=false)
     */
    protected $payment_method;
    /**
     * @var string $payment_status
     * @Column(type="string", nullable=false)
     */
    protected $payment_status;
    /**
     * @var string $status
     * @Column(type="string", nullable=false)
     */
    protected $status;
    /**
     * @var string $resolved_by
     * @Column(type="string", nullable=true)
     */
    protected $resolved_by;
    /**
     * @var string $type
     * @Column(type="string", nullable=false)
     */
    protected $type;
    /**
     * @var string $description
     * @Column(type="string", nullable=true)
     */
    protected $description;
    /**
     * @var string $invoice_description
     * @Column(type="string", nullable=true)
     */
    protected $invoice_description;
    /**
     * @var string $request_description
     * @Column(type="text", nullable=true)
     */
    protected $request_description;
    /**
     * @var string $request_clock_in
     * @Column(type="string", nullable=true)
     */
    protected $request_clock_in;
    /**
     * @var string $request_clock_out
     * @Column(type="string", nullable=true)
     */
    protected $request_clock_out;
    /**
     * @var float $clocked_hours
     * @Column(type="float", nullable=true)
     */
    protected $clocked_hours;

    /**
     * @var integer $quickbooks_bill_id
     * @Column(type="integer", nullable=true)
     */
    protected $quickbooks_bill_id;

    /**
     * @var integer $quickbooks_bill_payment_id
     * @Column(type="integer", nullable=true)
     */
    protected $quickbooks_bill_payment_id;
    
    /**
     * @var integer $quickbooks_purchase_id
     * @Column(type="integer", nullable=true)
     */
    protected $quickbooks_purchase_id;

    /**
     * @var DateTime $date_created
     * @Column(type="datetime", nullable=true) 
     */
    protected $date_created;

    /**
     * @var DateTime $date_updated
     * @Column(type="datetime", nullable=true) 
     */
    protected $date_updated;

    /**
     * @var boolean $is_deleted
     * @Column(type="boolean", nullable=true) 
     */
    protected $is_deleted;

    /**
     * @var DateTime $date_deleted
     * @Column(type="datetime", nullable=true) 
     */
    protected $date_deleted;

    /**
     * @var array $update_log
     * @Column(type="array", nullable=true)
     */
    protected $update_log;

    /**
     * @var string $corrected_comment
     * @Column(type="text", nullable=true)
     */
    protected $corrected_comment;
    
    /**
     * @var float $pay_holiday
     * @Column(type="float", options={"default": 0})
     */
    protected $pay_holiday = 0.0;
    
    /**
     * @var float $bill_holiday
     * @Column(type="float", options={"default": 0})
     */
    protected $bill_holiday = 0.0;

    /**
     * @PrePersist
     * @PreUpdate
     */
    public function persistCallback()
    {
        if (!$this->id) {
            $this->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
        }
        $dateUpdated = new DateTime('now', app::getInstance()->getTimeZone());
        $this->setDateUpdated($dateUpdated);

        $user = saAuth::getAuthUser();
        if (!$user) {
            $user = auth::getAuthUser();
        }
        try {
            $this->update_log[] = [
                'date' => $dateUpdated->format('Y-m-d H:i:s'),
                'user' => $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getUsername() . ')'
            ];
        } catch (\Throwable $e) {
            $this->update_log[] = [
                'date' => $dateUpdated->format('Y-m-d H:i:s'),
                'user' => 'Unknown'
            ];
        }
    }

    /**
     * @return float
     */
    public function getCalculatedPayTotal()
    {
        $payTotal =
            ($this->clocked_hours * $this->pay_rate)
            + $this->pay_bonus
            + $this->pay_holiday
            + $this->pay_travel;
        return $payTotal;
    }

    public function setResolvedBy($resolved_by)
    {
        $resolvedBy = $resolved_by;

        $this->resolved_by = $resolvedBy;
        return $this;
    }
    /**
     * @return string
     */
    public function getResolvedBy()
    {
        return $this->resolved_by;
    }

    /**
     * @return float
     */
    public function getCalculatedBillTotal()
    {
        $billTotal =
            ($this->clocked_hours * $this->bill_rate)
            + $this->bill_bonus
            + $this->bill_holiday
            + $this->bill_travel;
        return $billTotal;
    }

    /**
     * @return float
     */
    public function getPayTotal()
    {
        return $this->pay_total;
    }

    /**
     * @param float $pay_total
     * @return PayrollPayment
     */
    public function setPayTotal($pay_total)
    {
        $this->pay_total = $pay_total;
        return $this;
    }

    /**
     * @return float
     */
    public function getBillTotal()
    {
        return $this->bill_total;
    }

    /**
     * @param float $bill_total
     * @return PayrollPayment
     */
    public function setBillTotal($bill_total)
    {
        $this->bill_total = $bill_total;
        return $this;
    }

    /**
     * @return float
     */
    public function getPayRate()
    {
        return $this->pay_rate;
    }

    /**
     * @param float $pay_rate
     * @return PayrollPayment
     */
    public function setPayRate($pay_rate)
    {
        $this->pay_rate = $pay_rate;
        return $this;
    }

    /**
     * @return float
     */
    public function getBillRate()
    {
        return $this->bill_rate;
    }

    /**
     * @param float $bill_rate
     * @return PayrollPayment
     */
    public function setBillRate($bill_rate)
    {
        $this->bill_rate = $bill_rate;
        return $this;
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
     * @return PayrollPayment
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Shift
     */
    public function getShift()
    {
        if (!$this->shift) {
            $shift = ioc::get('Shift', ['payroll_payment' => $this]);
            if (!$shift) {
                $shift = ioc::get('Shift', ['overtime_payment' => $this]);
            }
            if (!$shift) {
                $shift = $this->getShiftRecurrence();
            }
            return $shift;
        }

        return $this->shift;
    }

    /**
     * @param Shift $shift
     * @return PayrollPayment
     */
    public function setShift($shift)
    {
        $this->shift = $shift;
        return $this;
    }

    /**
     * @return ShiftRecurrence
     */
    public function getShiftRecurrence()
    {
        if (!$this->shift_recurrence) {
            $shift = ioc::get('ShiftRecurrence', ['payroll_payment' => $this]);
            if (!$shift) {
                $shift = ioc::get('ShiftRecurrence', ['overtime_payment' => $this]);
            }
            return $shift;
        }
        return $this->shift_recurrence;
    }

    /**
     * @param ShiftRecurrence $shift_recurrence
     * @return PayrollPayment
     */
    public function setShiftRecurrence($shift_recurrence)
    {
        $this->shift_recurrence = $shift_recurrence;
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
     * @param string $status
     * @return PayrollPayment
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PayrollPayment
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return PayrollPayment
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestDescription()
    {
        return $this->request_description;
    }

    /**
     * @param string $request_description
     * @return PayrollPayment
     */
    public function setRequestDescription($request_description)
    {
        $this->request_description = $request_description;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestClockIn()
    {
        return $this->request_clock_in;
    }

    /**
     * @param string $request_clock_in
     * @return PayrollPayment
     */
    public function setRequestClockIn($request_clock_in)
    {
        $this->request_clock_in = $request_clock_in;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestClockOut()
    {
        return $this->request_clock_out;
    }

    /**
     * @param string $request_clock_out
     * @return PayrollPayment
     */
    public function setRequestClockOut($request_clock_out)
    {
        $this->request_clock_out = $request_clock_out;
        return $this;
    }

    /**
     * @return float
     */
    public function getClockedHours()
    {
        return $this->clocked_hours;
    }

    /**
     * @param float $clocked_hours
     * @return PayrollPayment
     */
    public function setClockedHours($clocked_hours)
    {
        $this->clocked_hours = $clocked_hours;
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceDescription()
    {
        return $this->invoice_description;
    }

    /**
     * @param string $invoice_description
     * @return PayrollPayment
     */
    public function setInvoiceDescription($invoice_description)
    {
        $this->invoice_description = $invoice_description;
        return $this;
    }


    /**
     * @return float
     */
    public function getPayBonus()
    {
        return $this->pay_bonus;
    }

    /**
     * @param float $pay_bonus
     * @return PayrollPayment
     */
    public function setPayBonus($pay_bonus)
    {
        $this->pay_bonus = $pay_bonus;
        return $this;
    }

    /**
     * @return float
     */
    public function getBillBonus()
    {
        return $this->bill_bonus;
    }

    /**
     * @param float $bill_bonus
     * @return PayrollPayment
     */
    public function setBillBonus($bill_bonus)
    {
        $this->bill_bonus = $bill_bonus;
        return $this;
    }

    /**
     * @return float
     */
    public function getPayTravel()
    {
        return $this->pay_travel;
    }

    /**
     * @param float $pay_travel
     * @return PayrollPayment
     */
    public function setPayTravel($pay_travel)
    {
        $this->pay_travel = $pay_travel;
        return $this;
    }

    /**
     * @return float
     */
    public function getBillTravel()
    {
        return $this->bill_travel;
    }

    /**
     * @param float $bill_travel
     * @return PayrollPayment
     */
    public function setBillTravel($bill_travel)
    {
        $this->bill_travel = $bill_travel;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @param string $payment_method
     * @return PayrollPayment
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
        return $this;
    }


    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->payment_status;
    }

    /**
     * @param string $payment_status
     * @return PayrollPayment
     */
    public function setPaymentStatus($payment_status)
    {
        $this->payment_status = $payment_status;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuickbooksBillId()
    {
        return $this->quickbooks_bill_id;
    }

    /**
     * @param int $quickbooks_bill_id
     * @return PayrollPayment
     */
    public function setQuickbooksBillId($quickbooks_bill_id)
    {
        $this->quickbooks_bill_id = $quickbooks_bill_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuickbooksBillPaymentId()
    {
        return $this->quickbooks_bill_payment_id;
    }

    /**
     * @param int $quickbooks_bill_payment_id
     * @return PayrollPayment
     */
    public function setQuickbooksBillPaymentId($quickbooks_bill_payment_id)
    {
        $this->quickbooks_bill_payment_id = $quickbooks_bill_payment_id;
        return $this;
    }

    
    /**
     * @return int
     */
    public function getQuickbooksPurchaseId()
    {
        return $this->quickbooks_purchase_id;
    }

    /**
     * @param int $quickbooks_purchase_id
     * @return PayrollPayment
     */
    public function setQuickbooksPurchaseId($quickbooks_purchase_id)
    {
        $this->quickbooks_purchase_id = $quickbooks_purchase_id;
        return $this;
    }

    /**
     * Set date_created
     *
     * @param DateTime $dateCreated
     * @return PayrollPayment
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Get date_created
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set date_updated
     *
     * @param \sacore\application\DateTime $dateUpdated
     * @return PayrollPayment
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->date_updated = $dateUpdated;

        return $this;
    }

    /**
     * Get date_updated
     *
     * @return \sacore\application\DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return PayrollPayment
     */
    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * Set date_deleted
     *
     * @param \sacore\application\DateTime $dateDeleted
     * @return PayrollPayment
     */
    public function setDateDeleted($dateDeleted)
    {
        $this->date_deleted = $dateDeleted;

        return $this;
    }

    /**
     * Get date_deleted
     *
     * @return \sacore\application\DateTime
     */
    public function getDateDeleted()
    {
        return $this->date_deleted;
    }

    /**
     * @return array
     */
    public function getUpdateLog()
    {
        return $this->update_log;
    }

    /**
     * @param array $update_log
     * @return PayrollPayment
     */
    public function setUpdateLog($update_log)
    {
        $this->update_log = $update_log;
        return $this;
    }

    /**
     * @return string
     */
    public function getCorrectedComment()
    {
        return $this->corrected_comment;
    }

    /**
     * @param string $corrected_comment
     * @return PayrollPayment
     */
    public function setCorrectedComment($corrected_comment)
    {
        $this->corrected_comment = $corrected_comment;
        return $this;
    }

    /**
     * @return Nurse
     */
    public function getNurseFromShift()
    {
        return $this->shift->getNurse();
    }

    /**
     * @return float
     */
    public function getPayHoliday()
    {
        return $this->pay_holiday;
    }

    /**
     * @param float $pay_holiday
     * @return PayrollPayment
     */
    public function setPayHoliday($pay_holiday)
    {
        $this->pay_holiday = $pay_holiday;
        return $this;
    }

    /**
     * @return float
     */
    public function getBillHoliday()
    {
        return $this->bill_holiday;
    }

    /**
     * @param float $bill_holiday
     * @return PayrollPayment
     */
    public function setBillHoliday($bill_holiday)
    {
        $this->bill_holiday = $bill_holiday;
        return $this;
    }
}
