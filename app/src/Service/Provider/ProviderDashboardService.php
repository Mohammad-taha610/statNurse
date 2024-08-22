<?php

namespace App\Service\Provider;

use App\DTO\Dashboard\DashboardDataDTO;
use App\DTO\Dashboard\DashboardStatsDTO;
use App\DTO\Member\ProviderDTO;
use App\DTO\Shift\ShiftDTO;
use App\Entity\Nst\Events\Shift;
use App\Entity\Nst\Member\NstMemberUsers;
use App\Entity\Nst\Member\Provider;
use App\Repository\Nst\Member\ProviderRepository;
use App\Repository\Nst\Shift\ShiftRepository;
use App\Service\Provider\ProviderService;
use App\Enum\MemberType;
use Carbon\Carbon;
use DateTime;

class ProviderDashboardService
{

    private ProviderRepository $providerRepository;

    private ProviderService $providerService;
    private ShiftRepository $shiftRepository;

    public function __construct(
        ProviderRepository $providerRepository,
        ProviderService    $providerService,
        ShiftRepository    $shiftRepository
    )
    {
        $this->providerRepository = $providerRepository;
        $this->providerService = $providerService;
        $this->shiftRepository = $shiftRepository;
    }

    public function loadDashboardDataForUser(NstMemberUsers $user): DashboardDataDTO
    {
        $memberType = $user->getMember()->getMemberType();
        if ($memberType == MemberType::Executive->name) {
            return $this->loadExecutiveDashboardData($user);
        } elseif ($memberType == MemberType::Provider->name) {
            return $this->loadProviderDashboardData($user);
        } else {
            throw new \Exception("Invalid member type");
        }
    }

    private function loadProviderDashboardData(NstMemberUsers $user): DashboardDataDTO
    {
        $providers = [$user->getMember()->getProvider()];
        return $this->loadDashboardData($providers);
    }

    private function loadExecutiveDashboardData(NstMemberUsers $user): DashboardDataDTO
    {
        $executive = $user->getMember()->getExecutive();
        $providers = $executive->getProviders()->toArray();
        return $this->loadDashboardData($providers);
    }


    public function getUpcomingShifts(
        $providers,
        $page,
        $offset,
        $now,
        $tz
    )
    {
        $paginatedShifts = $this->shiftRepository->getShifts(
            $providers,
            $page,
            $offset,
            $now
        );
        $shifts = $paginatedShifts['shifts'];
        $totalPages = $paginatedShifts['totalPages'];

        $upcomingShifts = [];
        if ($shifts) {
            /** @var Shift $shift */
            foreach ($shifts as $shift) {
                if ($shift->isRecurring()) {
                    $recurrence_start = $shift->getStartDate();
                    $recurrence_end = $shift->getUntilDate();
                    $recurrence_interval = $shift->getRecurrenceInterval();
                    $recurrence_type = $shift->getRecurrenceType();
                    $recurrence_rules = $shift->getRecurrenceRules();
                    $response['recurrence'] = [
                        'start' => $recurrence_start,
                        'end' => $recurrence_end,
                        'interval' => $recurrence_interval,
                        'type' => $recurrence_type,
                        'rules' => $recurrence_rules
                    ];
                }

                $startDate = Carbon::parse($shift->getStart(), \DateTimeZone::UTC)->tz($tz);
                $currentDateTime = Carbon::now($tz);

                if ($startDate < $currentDateTime) {
                    continue;
                }

                $endDate = $shift->getEnd();
                $startTime = Carbon::parse($shift->getStart(), \DateTimeZone::UTC)->tz($tz);
                $endTime = Carbon::parse($shift->getEnd(), \DateTimeZone::UTC)->tz($tz);
                //$isInArray = in_array($shift->getId(), array_column($tz, 'ID')); ??
                $upcomingShifts[] =
                    ShiftDTO::fromEntity($shift);
            }
        }
        return [
            'shifts' => $upcomingShifts,
            'totalPages' => $totalPages
        ];
    }

    /**
     * @param array<Provider> $providers
     * @return DashboardDataDTO
     */
    private function loadDashboardData(
        array $providers,
        int   $page = 1,
        int   $offset = 10
    ): DashboardDataDTO
    {
        $dashboardData = new DashboardDataDTO();
        $toTimeZone = 'America/New_York';
        $now = Carbon::now();
        $paginatedShiftData = $this->getUpcomingShifts(
            $providers,
            $page,
            $offset,
            $now,
            $toTimeZone
        );

        $upcomingShifts = $paginatedShiftData['shifts'];
        $totalPages = $paginatedShiftData['totalPages'];

        $dashboardData->setTotalPages($totalPages);

        foreach ($providers as $provider) {
            // $shifts = $provider->getShifts($page, $offset)
            if ($provider) {
                $providerId = $provider->getId();
                $unclaimedShiftCount = $this->providerRepository->getUnclaimedShiftsCount($providerId);
                $shiftRequestCount = $this->providerRepository->getShiftRequestsCount($providerId);
                $unresolvedPaymentCount = $this->providerRepository->getUnresolvedPaymentsCount($providerId);
                $currentPayPeriod = $this->providerService->calculatePayPeriodFromDate(new DateTime('now'));;

                $dashboardStatsDTO = new DashboardStatsDTO(
                    $provider,
                    $unclaimedShiftCount,
                    $shiftRequestCount,
                    $unresolvedPaymentCount
                );
                $dashboardData->setCurrentPayPeriod($currentPayPeriod);
                $dashboardData->addDashboardStats($dashboardStatsDTO);
            }
        }


        $sortedShifts = self::sortShiftsByDateTime($upcomingShifts);
        $dashboardData->setShifts($sortedShifts);
        return $dashboardData;
    }

    /**
     * @param array<ShiftDTO> $shifts
     * @return array
     */
    public function sortShiftsByDateTime(array $shifts): array
    {
        /**
         * @param ShiftDTO $a
         * @param ShiftDTO $b
         */
        usort($shifts, function ($a, $b) {
            $dateA = Carbon::parse($a->startTime);
            $dateB = Carbon::parse($b->startTime);
            return $dateA >= $dateB;
        });

        return $shifts;
    }
}
