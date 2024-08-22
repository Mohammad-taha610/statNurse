<?php

namespace App\DTO\Shift;

use App\DTO\Member\NurseDTO;
use App\DTO\Member\ProviderDTO;

class ShiftPayrollPaymentAggregateDTO
{
    public array $clockedHours;
    public array $billRate;
    public array $billTotal;
    public float $travelPay;
    public float $holidayPay;
    public float $bonus;
    public string $date;
    public NurseDTO $nurse;
    public ProviderDTO $provider;

    public function __construct()
    {
        $this->clockedHours = [];
        $this->billRate = [];
        $this->billTotal = [];
        $this->travelPay = 0;
        $this->holidayPay = 0;
        $this->bonus = 0;
    }
}
