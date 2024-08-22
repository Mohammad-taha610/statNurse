<?php

namespace App\DTO\Dashboard;

use App\Entity\Nst\Events\Shift;
use App\DTO\Dashboard\DashboardStatsDTO;


class DashboardDataDTO
{
    /**
     * @var array<Shift>
     */
    public array $shifts;

    /**
    * @var array<DashboardStatsDTO>
    */
   public array $dashboardStats;

   /**
    * @var array
    */
    public array $currentPayPeriod;

    public int $totalPages;

    public function __construct()
    {
    }

    public function setTotalPages(int $totalPages): void
    {
        $this->totalPages = $totalPages;
    }

    public function setShifts(array $shifts): void
    {
        $this->shifts = $shifts;
    }

    public function addDashboardStats(DashboardStatsDTO $dashboardStatsDTO): void
    {
        $this->dashboardStats[] = $dashboardStatsDTO;
    }

    public function setCurrentPayPeriod(array $currentPayPeriod): void
    {
        $this->currentPayPeriod = $currentPayPeriod;
    }
}
