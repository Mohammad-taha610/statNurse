<?php

namespace App\DTO\Member;

use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\Provider;

class ProviderLocationDTO
{
    public ProviderDTO $provider;
    public int $shiftRequestCount;
    public int $unresolvedPaymentCount;
    public int $unclaimedShiftsCount;
    public string $currentPayPeriod;
    public array $previousNurses;

    public function __construct(
        Provider $provider,
        int $shiftRequestCount,
        int $unresolvedPaymentCount,
        int $unclaimedShiftsCount,
        string $currentPayPeriod,
        array $previousNurses
    )
    {
        $this->provider = new ProviderDTO($provider);
        $this->shiftRequestCount = $shiftRequestCount;
        $this->unresolvedPaymentCount = $unresolvedPaymentCount;
        $this->unclaimedShiftsCount = $unclaimedShiftsCount;
        $this->currentPayPeriod = $currentPayPeriod;
        $this->previousNurses = array_map(function ($nurse) {
            return new NurseDTO($nurse);
        }, $previousNurses);
    }
}
