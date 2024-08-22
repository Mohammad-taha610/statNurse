<?php

namespace App\DTO\Dashboard;
use App\DTO\Member\ProviderDTO;
use App\Entity\Nst\Member\Provider;



class DashboardStatsDTO
{
    public ProviderDTO $provider;

    public int $unclaimedShifts;

    public int $shiftRequests;

    public int $unresolvedPayments;

    public string $currentPayPeriod;

    public function __construct(
        Provider $provider, 
        int $unclaimedShifts, 
        int $shiftRequests, 
        int $unresolvedPayments
    ) {
            $this->provider = new ProviderDTO($provider);
            $this->unclaimedShifts = $unclaimedShifts;
            $this->shiftRequests = $shiftRequests;
            $this->unresolvedPayments = $unresolvedPayments;
    }
}
