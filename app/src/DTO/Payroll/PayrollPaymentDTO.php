<?php

namespace App\DTO\Payroll;

use App\DTO\Member\NurseDTO;
use App\DTO\Member\ProviderDTO;
use App\DTO\Shift\ShiftDTO;
use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\Provider;
use App\Entity\Nst\Payroll\PayrollPayment;

class PayrollPaymentDTO
{
    public readonly int $id;
    public readonly string $nurseName;
    public readonly int $shiftId;
    public readonly string $resolvedBy;
    public readonly int $paymentId;
    public readonly string $shiftName;
    public readonly string $shiftTime;
    public float $clockedHours;
    public readonly string $clockTimes;
    public readonly string $clockIn;
    public readonly string $clockOut;
    public readonly string $rate;
    public readonly string $billRate;
    public readonly string $date;
    public float $amount;
    public readonly string $billAmount;
    public readonly string $type;
    public readonly string $description;
    public readonly string $requestDescription;
    public readonly string $requestClockIn;
    public readonly string $requestClockOut;
    public readonly string $status;
    public readonly string $supervisorName;
    public readonly string $supervisorCode;
    public readonly string $timeslip;
    public readonly string $clockInType;
    public readonly string $payHoliday;
    public readonly string $billHoliday;
    public readonly string $nurseRoute;
    public readonly string $actualClockIn;
    public readonly string $actualClockOut;
    public readonly ProviderDTO $provider;
    public readonly ShiftDTO $shift;

    public function __construct(PayrollPayment $payrollPayment, Provider $provider = null, Nurse $nurse = null)
    {
        $this->id = $payrollPayment->getId();
        $this->nurseName = $payrollPayment->getShift()->getNurse()->getFirstName() . ' ' . $payrollPayment->getShift()->getNurse()->getLastName() . ' (' . $payrollPayment->getShift()->getNurse()->getCredentials() . ')';
        $this->shiftId = $payrollPayment->getShift()->getId();
        $this->resolvedBy = $payrollPayment->getResolvedBy();
        $this->paymentId = $payrollPayment->getId();
        $this->shiftName = $payrollPayment->getShift()->getName();
        $this->shiftTime = $payrollPayment->getShift()->getStart()->format('g:ia') . ' - ' . $payrollPayment->getShift()->getEnd()->format('g:ia');
        $this->clockedHours = $payrollPayment->getClockedHours();
        $this->clockTimes = $payrollPayment->getShift()->getStart()->format('g:ia') . ' - ' . $payrollPayment->getShift()->getEnd()->format('g:ia');
        $this->clockIn = $payrollPayment->getShift()->getStart()->format('h:i A');
        $this->clockOut = $payrollPayment->getShift()->getEnd()->format('h:i A');
        $this->rate = number_format($payrollPayment->getPayRate(), 2, '.', '');
        $this->billRate = number_format($payrollPayment->getBillRate(), 2, '.', '');
        $this->date = $payrollPayment->getShift()->getStart()->format('Y-m-d');
        $this->amount = number_format($payrollPayment->getPayTotal(), 2, '.', '');
        $this->billAmount = number_format($payrollPayment->getBillTotal(), 2, '.', '');
        $this->type = $payrollPayment->getType();
        $this->description = $payrollPayment->getType() == 'Bonus' ? $payrollPayment->getDescription() : '';
        $this->requestDescription = $payrollPayment->getRequestDescription() ?: '';
        $this->requestClockIn = $payrollPayment->getRequestClockIn() ?: '';
        $this->requestClockOut = $payrollPayment->getRequestClockOut() ?: '';
        $this->actualClockIn = $payrollPayment->getShift()->getClockInTime()->format('h:i A') ?: '';
        $this->actualClockOut = $payrollPayment->getShift()->getClockOutTime()->format('h:i A') ?: '';
        $this->status = $payrollPayment->getStatus();
        $shift = $payrollPayment->getShift();
        $shiftOverride = $shift->getShiftOverride();
        if ($shiftOverride) {
            $this->supervisorName = $shiftOverride->getSupervisorName();
            $this->supervisorCode = $shiftOverride->getSupervisorCode();
        }

        $this->nurseRoute = "/executive/nurse/{$payrollPayment->getShift()->getNurse()->getId()}";
        if ($shift->getTimeslip()) {
            $timeslip = "/assets/files/id/{$shift->getTimeslip()->getId()}";
            $this->timeslip = $timeslip;
        }
        $this->clockInType = $payrollPayment->getShift()->getClockInType();
        $this->payHoliday = $payrollPayment->getPayHoliday();
        $this->billHoliday = $payrollPayment->getBillHoliday();
        if ($provider != null) {
            $this->provider = new ProviderDTO($provider);
        }

        if ($payrollPayment->getShift()) {
            $this->shift = ShiftDTO::fromEntity($payrollPayment->getShift());
        }
    }
}
