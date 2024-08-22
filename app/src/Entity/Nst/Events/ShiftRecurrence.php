<?php

/** @noinspection PhpHierarchyChecksInspection */

namespace App\Entity\Nst\Events;

use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\Provider;
use App\Entity\Nst\Payroll\PayrollPayment;
use App\Entity\Sax\Events\EventRecurrence;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @IOC_NAME="EventRecurrence"
 */
#[Entity(repositoryClass: 'ShiftRecurrenceRepository')]
#[HasLifecycleCallbacks]
class ShiftRecurrence extends EventRecurrence
{
    /**
     * @var DateTime
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $lunch_start;

    /**
     * @var DateTime
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $lunch_end;

    /**
     * @var float $lunch_override
     */
    #[Column(type: 'float', nullable: true)]
    protected $lunch_override;

    #[ManyToOne(targetEntity: Nurse::class, inversedBy: 'shiftRecurrences')]
    protected $nurse;

    #[ManyToOne(targetEntity: Provider::class, inversedBy: 'shift_recurrences')]
    protected $provider;

    /**
     * @var int $bonus_amount
     */
    #[Column(type: 'integer', nullable: true)]
    protected $bonus_amount;

    /**
     * @var string $bonus_description
     */
    #[Column(type: 'string', nullable: true)]
    protected $bonus_description;

    /**
     * @var string $nurse_type
     */
    #[Column(type: 'text', nullable: true)]
    protected $nurse_type;

    /**
     * @var bool $end_date_enabled
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $end_date_enabled;

    /**
     * @var string $name
     */
    #[Column(type: 'string', nullable: true)]
    protected $name;

    /**
     * @var string $status
     */
    #[Column(type: 'string', nullable: true)]
    protected $status;

    /**
     * @var bool $provider_approved
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $provider_approved;

    /**
     * @var bool $nurse_approved
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $nurse_approved;

    /**
     * @var PayrollPayment $payroll_payment
     */
    #[OneToOne(targetEntity: PayrollPayment::class, inversedBy: 'shift_recurrence')]
    #[JoinColumn(name: 'payment_id', referencedColumnName: 'id')]
    protected $payroll_payment;

    /**
     * @var ArrayCollection $payroll_payments
     *
     * @OneToMany(targetEntity="\nst\payroll\PayrollPayment", mappedBy="shift_recurrence", cascade={"remove"})
     */
    #[OneToMany(targetEntity: PayrollPayment::class, mappedBy: 'shift_recurrence', cascade: ['remove'])]
    protected $payroll_payments;

    /**
     * @var DateTime
     */
    #[Column(type: 'datetime')]
    protected $start;

    /**
     * @var DateTime
     */
    #[Column(type: 'datetime')]
    protected $end;

    /**
     * @var PayrollPayment $bonus_payment
     */
    #[OneToOne(targetEntity: PayrollPayment::class, inversedBy: 'shift_recurrence')]
    #[JoinColumn(name: 'bonus_payment_id', referencedColumnName: 'id')]
    protected $bonus_payment;

    /**
     * @var PayrollPayment $travel_payment
     */
    #[OneToOne(targetEntity: PayrollPayment::class, inversedBy: 'shift_recurrence')]
    #[JoinColumn(name: 'travel_payment_id', referencedColumnName: 'id')]
    protected $travel_payment;

    /**
     * @var PayrollPayment $overtime_payment
     */
    #[OneToOne(targetEntity: PayrollPayment::class, inversedBy: 'shift_recurrence')]
    #[JoinColumn(name: 'overtime_payment_id', referencedColumnName: 'id')]
    protected $overtime_payment;

    /**
     * @var DateTime $clock_in_time
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $clock_in_time;

    /**
     * @var DateTime $clock_out_time
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $clock_out_time;

    /**
     * @var float $hourly_rate
     */
    #[Column(type: 'float', nullable: true)]
    protected $hourly_rate;

    /**
     * @var float $billing_rate
     */
    #[Column(type: 'float', nullable: true)]
    protected $billing_rate;

    /**
     * @var float $hourly_overtime_rate
     */
    #[Column(type: 'float', nullable: true)]
    protected $hourly_overtime_rate;

    /**
     * @var float $billing_overtime_rate
     */
    #[Column(type: 'float', nullable: true)]
    protected $billing_overtime_rate;

    /**
     * @var bool $is_covid
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $is_covid;

    /**
     * @var float $incentive
     */
    #[Column(type: 'float', nullable: true)]
    protected $incentive;

    /**
     * @var DateTime $date_created
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $date_created;

    /**
     * @var DateTime $date_updated
     */
    #[Column(type: 'datetime', nullable: true)]
    protected $date_updated;

    /**
     * @var array $update_log
     */
    #[Column(type: 'array', nullable: true)]
    protected $update_log;

    /**
     * @var bool $has_been_notified_by_sms
     */
    #[Column(type: 'boolean', nullable: true, options: ['default' => '0'])]
    protected $notified_by_sms = false;

    /**
     * @var bool $has_been_notified_by_push_notification
     */
    #[Column(type: 'boolean', nullable: true, options: ['default' => '0'])]
    protected $notified_by_push_notification = false;

    public function __construct()
    {
        parent::__construct();
        $this->payroll_payments = new ArrayCollection();
    }

    /**
     * @PrePersist
     *
     * @PreUpdate
     */
    public function persistCallback()
    {
        if (! $this->id) {
            $this->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
        }
        $dateUpdated = new DateTime('now', app::getInstance()->getTimeZone());
        $this->setDateUpdated($dateUpdated);

        $user = saAuth::getAuthUser();
        if (! $user) {
            $user = auth::getAuthUser();
        }
        try {
            $this->update_log[] = [
                'date' => $dateUpdated->format('Y-m-d H:i:s'),
                'user' => $user->getFirstName().' '.$user->getLastName().' ('.$user->getUsername().')',
            ];
        } catch (\Throwable $e) {
            $this->update_log[] = [
                'date' => $dateUpdated->format('Y-m-d H:i:s'),
                'user' => 'Unknown',
            ];
        }
    }

    /**
     * @ORM\PrePersist
     *
     * @ORM\PrepUpdate
     */
    public function validate()
    {

    }

    public function toArray()
    {

        return [
            'id' => $this->id,
            'start' => $this->start,
            'end' => $this->end,
            'event' => $this->event->toArray(),
        ];
    }

    /**
     * @return DateTime
     */
    public function getLunchStart()
    {
        return $this->lunch_start;
    }

    /**
     * @param  DateTime  $lunch_start
     * @return ShiftRecurrence
     */
    public function setLunchStart($lunch_start)
    {
        $this->lunch_start = $lunch_start;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLunchEnd()
    {
        return $this->lunch_end;
    }

    /**
     * @param  DateTime  $lunch_end
     * @return ShiftRecurrence
     */
    public function setLunchEnd($lunch_end)
    {
        $this->lunch_end = $lunch_end;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * @param  mixed  $nurse
     * @return ShiftRecurrence
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param  mixed  $provider
     * @return ShiftRecurrence
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return int
     */
    public function getBonusAmount()
    {
        return $this->bonus_amount;
    }

    /**
     * @param  int  $bonus_amount
     * @return ShiftRecurrence
     */
    public function setBonusAmount($bonus_amount)
    {
        $this->bonus_amount = $bonus_amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getNurseType()
    {
        return $this->nurse_type;
    }

    /**
     * @param  string  $nurse_type
     * @return ShiftRecurrence
     */
    public function setNurseType($nurse_type)
    {
        $this->nurse_type = $nurse_type;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEndDateEnabled()
    {
        return $this->end_date_enabled;
    }

    /**
     * @param  bool  $end_date_enabled
     * @return ShiftRecurrence
     */
    public function setIsEndDateEnabled($end_date_enabled)
    {
        $this->end_date_enabled = $end_date_enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     * @return ShiftRecurrence
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return ShiftRecurrence
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsProviderApproved()
    {
        return $this->provider_approved;
    }

    /**
     * @param  bool  $provider_approved
     * @return ShiftRecurrence
     */
    public function setIsProviderApproved($provider_approved)
    {
        $this->provider_approved = $provider_approved;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsNurseApproved()
    {
        return $this->nurse_approved;
    }

    /**
     * @param  bool  $nurse_approved
     * @return ShiftRecurrence
     */
    public function setIsNurseApproved($nurse_approved)
    {
        $this->nurse_approved = $nurse_approved;

        return $this;
    }

    /**
     * @return PayrollPayment
     */
    public function getPayrollPayment()
    {
        $payment = null;
        foreach ($this->payroll_payments as $p) {
            if ($p->getType() == 'Standard') {
                $payment = $p;
            }
        }
        // TODO - Delete this after migration
        if (! $payment) {
            $payment = $this->payroll_payment;
        }

        return $payment;
    }

    /**
     * @param  PayrollPayment  $payroll_payment
     * @return ShiftRecurrence
     */
    public function setPayrollPayment($payroll_payment)
    {
        $payments = $this->payroll_payments;
        $payment = null;
        foreach ($payments as $k => $p) {
            if ($p->getType() == 'Standard') {
                $payment = $p;
                $p->setShiftRecurrence(null);
                $this->payroll_payments[$k] = $payroll_payment;
            }
        }
        if (! $payment) {
            $this->payroll_payments->add($payroll_payment);
        }

        return $this;
    }

    /**
     * @return PayrollPayment
     */
    public function getBonusPayment()
    {
        return $this->bonus_payment;
    }

    /**
     * @param  PayrollPayment  $bonus_payment
     * @return ShiftRecurrence
     */
    public function setBonusPayment($bonus_payment)
    {
        $this->bonus_payment = $bonus_payment;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getClockInTime()
    {
        return $this->clock_in_time;
    }

    /**
     * @param  DateTime  $clock_in_time
     * @return ShiftRecurrence
     */
    public function setClockInTime($clock_in_time)
    {
        $this->clock_in_time = $clock_in_time;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getClockOutTime()
    {
        return $this->clock_out_time;
    }

    /**
     * @param  DateTime  $clock_out_time
     * @return ShiftRecurrence
     */
    public function setClockOutTime($clock_out_time)
    {
        $this->clock_out_time = $clock_out_time;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param  mixed  $category
     * @return ShiftRecurrence
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Shift
     */
    public function getShift()
    {
        return $this->event;
    }

    /**
     * @param  Shift  $event
     * @return ShiftRecurrence
     */
    public function setShift($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return string
     */
    public function getBonusDescription()
    {
        return $this->bonus_description;
    }

    /**
     * @param  string  $bonus_description
     * @return ShiftRecurrence
     */
    public function setBonusDescription($bonus_description)
    {
        $this->bonus_description = $bonus_description;

        return $this;
    }

    /**
     * @return float
     */
    public function getHourlyRate()
    {
        return $this->hourly_rate;
    }

    /**
     * @param  float  $hourly_rate
     * @return ShiftRecurrence
     */
    public function setHourlyRate($hourly_rate)
    {
        $this->hourly_rate = $hourly_rate;

        return $this;
    }

    /**
     * @return float
     */
    public function getBillingRate()
    {
        return $this->billing_rate;
    }

    /**
     * @param  float  $billing_rate
     * @return ShiftRecurrence
     */
    public function setBillingRate($billing_rate)
    {
        $this->billing_rate = $billing_rate;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCovid()
    {
        return $this->is_covid;
    }

    /**
     * @param  bool  $is_covid
     * @return ShiftRecurrence
     */
    public function setIsCovid($is_covid)
    {
        $this->is_covid = $is_covid;

        return $this;
    }

    /**
     * @return float
     */
    public function getIncentive()
    {
        return $this->incentive;
    }

    /**
     * @param  float  $incentive
     * @return ShiftRecurrence
     */
    public function setIncentive($incentive)
    {
        $this->incentive = $incentive;

        return $this;
    }

    /**
     * @return float
     */
    public function getLunchOverride()
    {
        return $this->lunch_override;
    }

    /**
     * @param  float  $lunch_override
     * @return ShiftRecurrence
     */
    public function setLunchOverride($lunch_override)
    {
        $this->lunch_override = $lunch_override;

        return $this;
    }

    public function getIsRecurrence()
    {
        return true;
    }

    /**
     * @return float
     */
    public function getHourlyOvertimeRate()
    {
        return $this->hourly_overtime_rate;
    }

    /**
     * @param  float  $hourly_overtime_rate
     * @return ShiftRecurrence
     */
    public function setHourlyOvertimeRate($hourly_overtime_rate)
    {
        $this->hourly_overtime_rate = $hourly_overtime_rate;

        return $this;
    }

    /**
     * @return float
     */
    public function getBillingOvertimeRate()
    {
        return $this->billing_overtime_rate;
    }

    /**
     * @param  float  $billing_overtime_rate
     * @return ShiftRecurrence
     */
    public function setBillingOvertimeRate($billing_overtime_rate)
    {
        $this->billing_overtime_rate = $billing_overtime_rate;

        return $this;
    }

    /**
     * @return PayrollPayment
     */
    public function getOvertimePayment()
    {
        $payment = null;
        foreach ($this->payroll_payments as $p) {
            if ($p->getType() == 'Overtime') {
                $payment = $p;
            }
        }
        if (! $payment) {
            $payment = $this->overtime_payment;
        }

        return $payment;
    }

    /**
     * @param  PayrollPayment  $overtime_payment
     * @return ShiftRecurrence
     */
    public function setOvertimePayment($overtime_payment)
    {
        $payments = $this->payroll_payments;
        $payment = null;
        foreach ($payments as $k => $p) {
            if ($p->getType() == 'Overtime') {
                $payment = $p;
                $p->setShiftRecurrence(null);
                $this->payroll_payments[$k] = $overtime_payment;
            }
        }
        if (! $payment) {
            $this->payroll_payments->add($overtime_payment);
        }

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param  DateTime  $start
     * @return ShiftRecurrence
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param  DateTime  $end
     * @return ShiftRecurrence
     */
    public function setEnd($end)
    {
        $this->end = $end;

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
     * @return ShiftRecurrence
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param  DateTime  $date_updated
     * @return ShiftRecurrence
     */
    public function setDateUpdated($date_updated)
    {
        $this->date_updated = $date_updated;

        return $this;
    }

    /**
     * @return array
     */
    public function getUpdateLog()
    {
        return $this->update_log;
    }

    /**
     * @param  array  $update_log
     * @return ShiftRecurrence
     */
    public function setUpdateLog($update_log)
    {
        $this->update_log = $update_log;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayrollPayments()
    {
        return $this->payroll_payments;
    }

    /**
     * @param  ArrayCollection  $payroll_payments
     * @return ShiftRecurrence
     */
    public function setPayrollPayments($payroll_payments)
    {
        $this->payroll_payments = $payroll_payments;

        return $this;
    }

    /**
     * @param  ArrayCollection  $payroll_payment
     * @return ShiftRecurrence
     */
    public function addPayrollPayment($payroll_payment)
    {
        $this->payroll_payments->add($payroll_payment);

        return $this;
    }

    /**
     * @param  ArrayCollection  $payroll_payment
     * @return ShiftRecurrence
     */
    public function removePayrollPayment($payroll_payment)
    {
        $this->payroll_payments->remove($payroll_payment);

        return $this;
    }

    /**
     * @return bool
     */
    public function getNotifiedBySMS()
    {
        return $this->notified_by_sms;
    }

    /**
     * @param  bool  $notified_by_sms
     * @return ShiftRecurrence
     */
    public function setNotifiedBySMS($notified_by_sms)
    {
        $this->notified_by_sms = $notified_by_sms;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNotifiedByPushNotification()
    {
        return $this->notified_by_push_notification;
    }

    /**
     * @param  bool  $notified_by_push_notification
     * @return ShiftRecurrence
     */
    public function setNotifiedByPushNotification($notified_by_push_notification)
    {
        $this->notified_by_push_notification = $notified_by_push_notification;

        return $this;
    }
}
