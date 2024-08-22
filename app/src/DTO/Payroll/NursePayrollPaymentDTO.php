<?php

namespace App\DTO\Payroll;
use App\DTO\Member\NurseDTO;
use App\DTO\Member\ProviderDTO;
use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\Provider;
use App\Entity\Nst\Payroll\PayrollPayment;


class NursePayrollPaymentDTO
{
    public string $nurseRoute;
    public ProviderDTO $provider;
    public float $clockedHours;
    public float $payRate;
    public float $billRate;
    public float $payBonus;
    public float $billBonus;
    public float $payTravel;
    public float $billTravel;
    public float $payHoliday;
    public float $billHoliday;
    public float $payTotal;
    public float $billTotal;
    public string $hasUnresolvedPayments;
    public NurseDTO $nurse;

    public function __construct(PayrollPayment $payrollPayment, Provider $provider, Nurse $nurse, bool $hasUnresolvedPayments)
    {
        $this->provider = new ProviderDTO($provider);
        $this->nurse = new NurseDTO($nurse);
        $this->nurseRoute = "/executive/nurse/{$nurse->getId()}";
        $this->clockedHours = $payrollPayment->getClockedHours();
        $this->payRate = $payrollPayment->getPayRate() ?? 0;
        $this->billRate = $payrollPayment->getBillRate() ?? 0;
        $this->payBonus = $payrollPayment->getPayBonus() ?? 0;
        $this->billBonus = $payrollPayment->getBillBonus() ?? 0;
        $this->payTravel = $payrollPayment->getPayTravel() ?? 0;
        $this->billTravel = $payrollPayment->getBillTravel() ?? 0;
        $this->payHoliday = $payrollPayment->getPayHoliday() ?? 0;
        $this->billHoliday = $payrollPayment->getBillHoliday() ?? 0;
        $this->payTotal = $payrollPayment->getPayTotal() ?? 0;
        $this->billTotal = $payrollPayment->getBillTotal() ?? 0;
        $this->hasUnresolvedPayments = $hasUnresolvedPayments ? 'Yes' : 'No';
    }
}
