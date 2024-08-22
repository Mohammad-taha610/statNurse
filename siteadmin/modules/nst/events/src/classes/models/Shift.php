<?php

namespace nst\events;

use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use nst\member\NstFile;
use nst\payroll\PayrollPayment;
use phpDocumentor\Reflection\Types\This;
use nst\member\Nurse;
use nst\member\ProviderService;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\integratedWidgets;
use sacore\application\ioc;
use sacore\application\ValidateException;
use sa\events\Event;
use \nst\member\Provider;
use sa\member\auth;
use sa\system\saAuth;

/**
 * @Entity(repositoryClass="ShiftRepository")
 * @HasLifecycleCallbacks
 * @IOC_NAME="Event"
 */
class Shift extends Event
{

    public const STATUS_OPEN = "Open";
    public const STATUS_PENDING = "Pending";
    public const STATUS_ASSIGNED = "Assigned";
    public const STATUS_APPROVED = "Approved";
    public const STATUS_COMPLETED = "Completed";

    /**
     * @var DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $lunch_start;

    /**
     * @var DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $lunch_end;

    /**
     * @var float
     * @Column(type="float", nullable=true)
     */
    protected $lunch_override;

    /**
     * @var Nurse $nurse
     * @ManyToOne(targetEntity="\nst\member\Nurse", inversedBy="shifts")
     * @JoinTable(name="nurse_shifts")
     */
    protected $nurse;

    /**
     * @var string $nurse_type
     * @Column(type="text", nullable=true)
     */
    protected $nurse_type;

    /**
     * @var bool $end_date_enabled
     * @Column(type="boolean", nullable=true)
     */
    protected $end_date_enabled;

    /**
     * @var Provider $provider
     * @ManyToOne(targetEntity="\nst\member\Provider", inversedBy="shifts")
     */
    protected $provider;

    /**
     * @var string $status
     * @Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var int $bonus_amount
     * @Column(type="integer", nullable=true)
     */
    protected $bonus_amount;

    /**
     * @var bool $nurse_approved
     * @Column(type="boolean", nullable=true, options={"default": false})
     */
    protected $nurse_approved = false;

    /**
     * @var bool $provider_approved
     * @Column(type="boolean", nullable=true, options={"default": false})
     */
    protected $provider_approved = false;

    /**
     * @var string $recurrence_type
     * @Column(type="string", nullable=true)
     * @var string $recurrence_type
     */
    protected $recurrence_type;

    /**
     * @var array $recurrence_options
     * @Column(type="array", nullable=true)
     */
    protected $recurrence_options;

    /**
     * @var DateTime $recurrence_end_date
     * @Column(type="datetime", nullable=true)
     */
    protected $recurrence_end_date;

    /**
     * @var PayrollPayment $payroll_payment
     * @OneToOne(targetEntity="\nst\payroll\PayrollPayment", inversedBy="shift")
     * @JoinColumn(name="payment_id", referencedColumnName="id")
     */
    protected $payroll_payment;

    /**
     * @var ArrayCollection $payroll_payments
     * @OneToMany(targetEntity="\nst\payroll\PayrollPayment", mappedBy="shift", cascade={"remove"})
     */
    protected $payroll_payments;

    /**
     * @var PayrollPayment $overtime_payment
     * @OneToOne(targetEntity="\nst\payroll\PayrollPayment", inversedBy="shift")
     * @JoinColumn(name="overtime_payment_id", referencedColumnName="id")
     */
    protected $overtime_payment;

    /**
     * @var PayrollPayment $bonus_payment
     * @OneToOne(targetEntity="\nst\payroll\PayrollPayment", inversedBy="shift")
     * @JoinColumn(name="bonus_payment_id", referencedColumnName="id")
     */
    protected $bonus_payment;

    /**
     * @var string $bonus_description
     * @Column(type="string", nullable=true)
     */
    protected $bonus_description;

    /**
     * @var DateTime $clock_in_time
     * @Column(type="datetime", nullable=true)
     */
    protected $clock_in_time;

    /**
     * @var DateTime $clock_out_time
     * @Column(type="datetime", nullable=true)
     */
    protected $clock_out_time;

    /**
     * @var int $parent_id
     * @Column(type="integer", nullable=true)
     */
    protected $parent_id;

    /**
     * @var DateTime $start
     * @Column(type="datetime", nullable=true)
     */
    protected $start;

    /**
     * @var DateTime $start
     * @Column(type="datetime", nullable=true)
     */
    protected $end;

    /**
     * @var float $hourly_rate
     * @Column(type="float", nullable=true)
     */
    protected $hourly_rate;

    /**
     * @var float $billing_rate
     * @Column(type="float", nullable=true)
     */
    protected $billing_rate;

    /**
     * @var float $hourly_overtime_rate
     * @Column(type="float", nullable=true)
     */
    protected $hourly_overtime_rate;
    /**
     * @var float $billing_overtime_rate
     * @Column(type="float", nullable=true)
     */
    protected $billing_overtime_rate;

    /**
     * @var bool $is_covid
     * @Column(type="boolean", nullable=true);
     */
    protected $is_covid;

    /**
     * @var float $incentive
     * @Column(type="float", nullable=true)
     */
    protected $incentive;

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
     * @var array $update_log
     * @Column(type="array", nullable=true)
     */
    protected $update_log;

    /**
     * @var bool $notified_by_sms
     * @Column(type="boolean", nullable=true, options={"default": "0"});
     */
    protected $notified_by_sms = false;

    /**
     * @var bool $notified_by_push_notification
     * @Column(type="boolean", nullable=true, options={"default": "0"});
     */
    protected $notified_by_push_notification = false;

    /**
     * @var string $clock_in_type
     * @Column(type="string", nullable=true, options={"default": "natural"});
     */
    protected $clock_in_type;

    /**
     * @OneToOne(targetEntity="ShiftOverride")
     * @JoinColumn(name="shift_override_id", referencedColumnName="id")
     */
    protected $shift_override;

    /**
     * @var string $clockout_comment
     * @Column(type="string", nullable=true)
     */
    protected $clockout_comment = "";

    /**
     * @OneToOne(targetEntity="nst\member\NstFile")
     * @JoinColumn(name="timeslip_file_id", referencedColumnName="id")
     */
    protected $timeslip;

    /** @Column(type="boolean", nullable=true, options={"default": "0"}); */
    protected $clocked_out_early;

    /** @Column(type="string", nullable=true); */
    protected $early_clockout_reason;

    /**
     * @var bool $isOnBreak
     * @Column(type="boolean", nullable=true, options={"default": "0"});
     */
    protected $isOnBreak;

    /**
     * @var DateTime $breakStartTime
     * @Column(type="datetime", nullable=true)
     */

    protected $breakStartTime;

    /**
     * @var bool $hasTakenBreak
     * @Column(type="boolean", nullable=true, options={"default": "0"});
     */
    protected $hasTakenBreak;

    public function __construct()
    {
        parent::__construct();
        $this->payroll_payments = new ArrayCollection();
    }

    public function validate()
    {
    }

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
     * @throws \sacore\application\ValidateException
     */
    protected function validateGeneralInformation()
    {
        if (empty($this->name)) {
            throw new ValidateException('Please enter a name for the event.');
        }

        if (!empty($this->link) && filter_var($this->link, FILTER_VALIDATE_URL) === false) {
            throw new ValidateException('The link you entered isn\'t valid.');
        }

        if (empty($this->category)) {
            throw new ValidateException('Please select a category.');
        }
    }

    /**
     * @throws \sacore\application\ValidateException
     */
    protected function validateRecurrenceRules()
    {
        if (empty($this->start_date)) {
            throw new ValidateException('Please select a start date.');
        }

        if ($this->getIsEndDateEnabled() && empty($this->end_date)) {
            throw new ValidateException('Please select an end date.');
        }

        if ($this->start_date > $this->end_date) {
            throw new ValidateException('End date can not be set before the start date.');
        }

        if (!empty($this->until) && $this->until_date < $this->end_date) {
            throw new ValidateException('Your event is set to repeat until ' . $this->until_date->format('m-d-Y') . ', 
		                                    which is before the end date of your event. Please change the "repeat until" 
		                                    field in your repeat rules to a later date.');
        }

        if (!$this->isAllDay() && empty($this->start_time)) {
            throw new ValidateException("Please enter an start time.");
        }

        if (!$this->isAllDay() && empty($this->end_time)) {
            throw new ValidateException("Please enter a end time.");
        }

        if ($this->isRecurring() && !is_numeric($this->interval) || $this->interval < 1) {
            throw new ValidateException('Recurrence interval must be a numeric value larger than 1.');
        }

        if ($this->isRecurring() && !in_array($this->frequency, static::getFrequencies())) {
            throw new ValidateException('Invalid frequency: Must be daily, weekly, monthly, or yearly.');
        }

        if (
            $this->isRecurring() && in_array($this->frequency, array(Event::FREQUENCY_DAILY, Event::FREQUENCY_WEEKLY))
            && $this->start_date->format('m/d/Y') != $this->end_date->format('m/d/Y')
        ) {
            throw new ValidateException('Events that repeat daily or weekly must start and end on the same day.');
        }

        if ($this->isRecurring() && $this->frequency == Event::FREQUENCY_WEEKLY) {

            $reoccurrencedays = $this->getRecurrenceDays();
            if (empty($reoccurrencedays)) {
                throw new ValidateException("You have set your event to repeat weekly. Please select 
                                        on which days you would like them to repeat (Mon, Tues, Wed, etc.).");
            }

            $startDateDay = $this->start_date->format('l');
            $weekDayIndex = array_search($startDateDay, $this::getWeekDays());

            if ($weekDayIndex === false || !in_array($weekDayIndex, $reoccurrencedays)) {
                throw new ValidateException("For events that repeat weekly, your start date must be on one of the days
                                         of the week you selected.");
            }
        }
    }

    /**
     * @throws \sacore\application\ValidateException
     */
    protected function validateLocationInformation()
    {
        /*
         * If one or more of the address fields are not empty, all other
         * address fields are required.
         */
        if (
            !empty($this->street_one)
            || !empty($this->street_two)
            || !empty($this->city)
            || !empty($this->state)
            || !empty($this->postal_code)
        ) {
            if (empty($this->street_one)) {
                throw new ValidateException('Please enter a street name.');
            }

            if (empty($this->city)) {
                throw new ValidateException('Please enter a city.');
            }

            if (empty($this->postal_code) || !is_numeric($this->postal_code) || strlen($this->postal_code) != 5) {
                throw new ValidateException('Please enter a valid postal code.');
            }

            if (empty($this->state)) {
                throw new ValidateException('Please select a state.');
            }
        }
    }

    /**
     * @throws \sacore\application\ValidateException
     */
    protected function validateContactInformation()
    {
        if (!empty($this->contact_email) && filter_var($this->contact_email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidateException('The email you entered is invalid.');
        }

        $phone_regex = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';
        if (!empty($this->contact_phone) && !preg_match($phone_regex, $this->contact_phone)) {
            throw new ValidateException('The phone number you entered is invalid. Must be in the format (xxx)xxx-xxx');
        }
    }

    /**
     * @return DateTime
     */
    public function getLunchStart()
    {
        return $this->lunch_start;
    }

    /**
     * @param DateTime $lunch_start
     * @return Shift
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
     * @param DateTime $lunch_end
     * @return Shift
     */
    public function setLunchEnd($lunch_end)
    {
        $this->lunch_end = $lunch_end;
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
     * @return Shift
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;
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
     * @param string $nurse_type
     * @return Shift
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
     * @param bool $end_date_enabled
     * @return Shift
     */
    public function setIsEndDateEnabled($end_date_enabled)
    {
        $this->end_date_enabled = $end_date_enabled;
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
     * @return Shift
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
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
     * @return Shift
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @param int $bonus_amount
     * @return Shift
     */
    public function setBonusAmount($bonus_amount)
    {
        $this->bonus_amount = $bonus_amount;
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
     * @param bool $nurse_approved
     * @return Shift
     */
    public function setIsNurseApproved($nurse_approved)
    {
        $this->nurse_approved = $nurse_approved;
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
     * @param bool $provider_approved
     * @return Shift
     */
    public function setIsProviderApproved($provider_approved)
    {
        $this->provider_approved = $provider_approved;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurrenceType()
    {
        return $this->recurrence_type;
    }

    /**
     * @param mixed $recurrence_type
     * @return Shift
     */
    public function setRecurrenceType($recurrence_type)
    {
        $this->recurrence_type = $recurrence_type;
        return $this;
    }

    /**
     * @return array
     */
    public function getRecurrenceOptions()
    {
        return $this->recurrence_options;
    }

    /**
     * @param array $recurrence_options
     * @return Shift
     */
    public function setRecurrenceOptions($recurrence_options)
    {
        $this->recurrence_options = $recurrence_options;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRecurrenceEndDate()
    {
        return $this->recurrence_end_date;
    }

    /**
     * @param DateTime $recurrence_end_date
     * @return Shift
     */
    public function setRecurrenceEndDate($recurrence_end_date)
    {
        $this->recurrence_end_date = $recurrence_end_date;
        return $this;
    }


    /**
     * @return int
     */
    public function getRecurrenceInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $recurrence_interval
     * @return Shift
     */
    public function setRecurrenceInterval($recurrence_interval)
    {
        $this->interval = $recurrence_interval;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param DateTime $start_time
     * @return Shift
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param DateTime $end_time
     * @return Shift
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param mixed $timezone
     * @return Shift
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return PayrollPayment
     */
    public function getPayrollPayment()
    {
        $payment = null;
        foreach ($this->payroll_payments as $p) {
            if ($p->getType() == 'Standard') $payment = $p;
        }
        // TODO - Delete this after migration
        if (!$payment) $payment = $this->payroll_payment;
        return $payment;
    }

    /**
     * @param PayrollPayment $payroll_payment
     * @return Shift
     */
    public function setPayrollPayment($payroll_payment)
    {
        $payments = $this->payroll_payments;
        $payment = null;
        foreach ($payments as $k => $p) {
            if ($p && $p->getType() == 'Standard') {
                $payment = $p;
                $p->setShift(null);
                $this->payroll_payments[$k] = $payroll_payment;
            }
        }
        if (!$payment) {
            $this->payroll_payments->add($payroll_payment);
        }
        return $this;
    }

    /**
     * @return Shift
     */
    public function clearPayrollPayment()
    {
        $this->payroll_payment = null;

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
     * @param DateTime $clock_in_time
     * @return Shift
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
     * @param DateTime $clock_out_time
     * @return Shift
     */
    public function setClockOutTime($clock_out_time)
    {
        $this->clock_out_time = $clock_out_time;
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
     * @param string $bonus_description
     * @return Shift
     */
    public function setBonusDescription($bonus_description)
    {
        $this->bonus_description = $bonus_description;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     * @return Shift
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        // This is to be consistent with Event->getStartTime()
        return $this->start;
    }

    /**
     * @param DateTime $start
     * @return Shift
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        // This is to be consistent with Event->getEndTime()
        return $this->end;
    }

    /**
     * @param DateTime $end
     * @return Shift
     */
    public function setEnd($end)
    {
        $this->end = $end;
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
     * @param float $hourly_rate
     * @return Shift
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
     * @param float $billing_rate
     * @return Shift
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
     * @param bool $is_covid
     * @return Shift
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
     * @param float $incentive
     * @return Shift
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
     * @param float $lunch_override
     * @return Shift
     */
    public function setLunchOverride($lunch_override)
    {
        $this->lunch_override = $lunch_override;
        return $this;
    }


    public function getIsRecurrence()
    {
        return false;
    }

    /**
     * @return float
     */
    public function getHourlyOvertimeRate()
    {
        return $this->hourly_overtime_rate;
    }

    /**
     * @param float $hourly_overtime_rate
     * @return Shift
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
     * @param float $billing_overtime_rate
     * @return Shift
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
            if ($p->getType() == 'Overtime') $payment = $p;
        }
        if (!$payment) $payment = $this->overtime_payment;
        return $payment;
    }

    /**
     * @param PayrollPayment $overtime_payment
     * @return Shift
     */
    public function setOvertimePayment($overtime_payment)
    {
        $payments = $this->payroll_payments;
        $payment = null;
        foreach ($payments as $k => $p) {
            if ($p->getType() == 'Overtime') {
                $payment = $p;
                $p->setShift(null);
                $this->payroll_payments[$k] = $overtime_payment;
            }
        }
        if (!$payment) {
            $this->payroll_payments->add($overtime_payment);
        }
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
     * @param DateTime $date_created
     * @return Shift
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
     * @param DateTime $date_updated
     * @return Shift
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
     * @param array $update_log
     * @return Shift
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
     * @param ArrayCollection $payroll_payments
     * @return Shift
     */
    public function setPayrollPayments($payroll_payments)
    {
        if ($payroll_payments) {
            $this->payroll_payments = $payroll_payments;
        }
        return $this;
    }

    /**
     * @param ArrayCollection $payroll_payment
     * @return Shift
     */
    public function addPayrollPayment($payroll_payment)
    {
        $this->payroll_payments->add($payroll_payment);
        return $this;
    }

    /**
     * @param ArrayCollection $payroll_payment
     * @return Shift
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
     * @param bool $notified_by_sms
     * @return Shift
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
     * @param bool $notified_by_push_notification
     * @return Shift
     */
    public function setNotifiedByPushNotification($notified_by_push_notification)
    {
        $this->notified_by_push_notification = $notified_by_push_notification;
        return $this;
    }

    public function setClockInType($clock_in_type)
    {
        $this->clock_in_type = $clock_in_type;
        return $this;
    }

    public function getClockInType()
    {
        return $this->clock_in_type;
    }

    /**
     * @return ShiftOverride
     */
    public function getShiftOverride()
    {
        return $this->shift_override;
    }

    /**
     * @param ShiftOverride $shift_override
     * @return Shift
     */
    public function setShiftOverride($shift_override)
    {
        $this->shift_override = $shift_override;
        return $this;
    }

    /**
     * @return string
     */
    public function getClockoutComment()
    {
        return $this->clockout_comment;
    }

    /**
     * @param string $clockout_comment
     * @return Shift
     */
    public function setClockoutComment($clockout_comment)
    {
        $this->clockout_comment = $clockout_comment;
        return $this;
    }

    /** @return NstFile */
    public function getTimeslip()
    {
        return $this->timeslip;
    }

    /**
     * @param NstFile $timeslip
     * @return $this
     */
    public function setTimeslip($timeslip)
    {
        $this->timeslip = $timeslip;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getBreakStartTime()
    {
        return $this->breakStartTime;
    }

    /**
     * @param DateTime $breakStartTime
     * @return Shift
     */
    public function setBreakStartTime($breakStartTime)
    {
        $this->breakStartTime = $breakStartTime;
        return $this;

    }

    /**
     * @return boolean
     */
    public function getIsOnBreak()
    {
        return $this->isOnBreak;
    }

    /**
     * @param bool $isOnBreak
     * @return Shift
     */
    public function setIsOnBreak($isOnBreak)
    {
        $this->isOnBreak = $isOnBreak;
        return $this;
    }
    /**
     * @return boolean
     */
    public function getHasTakenBreak()
    {
        return $this->hasTakenBreak;
    }

    /**
     * @param bool $hasTakenBreak
     * @return Shift
     */
    public function setHasTakenBreak($hasTakenBreak)
    {
        $this->hasTakenBreak = $hasTakenBreak;
        return $this;
    }

    /**
     * @return bool
     */
    public function getClockedOutEarly()
    {
        return $this->clocked_out_early;
    }

    /**
     * @param bool $clocked_out_early
     * @return Shift
     */
    public function setClockedOutEarly($clocked_out_early)
    {
        $this->clocked_out_early = $clocked_out_early;
        return $this;
    }


    /**
     * @return string
     */
    public function getEarlyClockoutReason()
    {
        return $this->early_clockout_reason;
    }

    /** 
     * @param string $early_clockout_reason
     * @return Shift
     */
    public function setEarlyClockoutReason($early_clockout_reason)
    {
        $this->early_clockout_reason = $early_clockout_reason;
        return $this;
    }
}
