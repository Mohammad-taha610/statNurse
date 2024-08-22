<?php

namespace App\Service\Shift;

use App\DTO\Shift\ShiftDTO;
use App\Entity\Nst\Events\NstCategory;
use App\Entity\Nst\Events\Shift;
use App\Entity\Nst\Member\NstMemberUsers;
use App\Entity\Nst\Member\Nurse;
use App\Entity\Nst\Member\Provider;
use App\Enum\MemberType;
use App\Repository\Nst\Shift\ShiftCategoryRepository;
use App\Repository\Nst\Shift\ShiftRepository;
use App\Service\Provider\ProviderService;
use App\Service\SaCommandService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;

class ShiftService
{
    private ShiftRepository $shiftRepository;
    private ShiftCategoryRepository $shiftCategoryRepository;
    private ProviderService $providerService;
    private SaCommandService $saCommandService;

    public function __construct(
        ShiftRepository         $shiftRepository,
        ShiftCategoryRepository $shiftCategoryRepository,
        SaCommandService        $saCommandService,
        ProviderService         $providerService
    )
    {
        $this->shiftRepository = $shiftRepository;
        $this->shiftCategoryRepository = $shiftCategoryRepository;
        $this->saCommandService = $saCommandService;
        $this->providerService = $providerService;
    }

    /**
     * @throws Exception
     */
    public function loadShiftCalendarMonthView(
        NstMemberUsers $user,
        \DateTime      $start,
        \DateTime      $end,
        int            $nurseFilterId = null,
        int            $providerFilterId = null,
        int            $categoryFilterId = null,
        string         $credentialFilter = null,
        string         $tz = null,
        string         $calendarMode = null
    ): array
    {
        $providers = $this->providerService->getProvidersForMember($user);
        $shifts = [];

        $shiftsByDay = [];
        // length of providers
        foreach ($providers as $provider) {
            if ($providerFilterId && $provider != null && $provider->getId() !== $providerFilterId) {
                continue;
            }
            $shiftsForProvider = $this->shiftRepository->findShiftsByProviderAndDateRange($provider, $start, $end, $calendarMode);
            /** @var Shift $shift */
            foreach ($shiftsForProvider as $shift) {
                $nurse = $shift->getNurse();
                if ($nurseFilterId && ($nurse == null || ($nurse->getId() !== $nurseFilterId))) {
                    continue;
                }

                if ($credentialFilter != null && $shift->getNurseType() != $credentialFilter) {
                    continue;
                }

                if ($categoryFilterId && $shift->getCategory() != null && $shift->getCategory()->getId() !== $categoryFilterId) {
                    continue;
                }

                $shiftDTO = ShiftDTO::fromEntity($shift);

                $shiftStart = CarbonImmutable::parse($shiftDTO->start);
                $shiftEnd = CarbonImmutable::parse($shiftDTO->end);

                $shiftDTO->startTime = $shiftStart->timezone($tz)->format('H:i');
                $shiftDTO->endTime = $shiftEnd->timezone($tz)->format('H:i');

                $key = $shiftStart->timezone($tz)->format('Y-m-d');

                if ($calendarMode !== 'month') {
                    $shiftsByDay[$key]['shifts'][] = $shiftDTO;
                }

                if (!isset($shiftsByDay[$key]['count'][$shift->getStatus()])) {
                    $shiftsByDay[$key]['count'][$shift->getStatus()] = 0;
                }
                $shiftsByDay[$key]['count'][$shift->getStatus()]++;
            }
        }
        return $shiftsByDay;
    }

    /**
     * @return array<NstCategory>
     */
    public function loadAllCategories(): array
    {
        return $this->shiftCategoryRepository->findAll();
    }

    public function saveShift($data)
    {
        return $this->saCommandService->executeSaCommandWithJson('shifts:create_shift', $data);
    }

    public function loadShiftById(int $id): ShiftDTO
    {
        $shift = $this->shiftRepository->find($id);
        return ShiftDTO::fromEntity($shift);
    }

    public function deleteShift($id): string
    {
        // get shift, make sure its > 2 hours away from now
        $shift = $this->shiftRepository->find($id);
        $start = Carbon::parse($shift->getStart());
        $now = Carbon::now();
        $diff = $now->diffInHours($start);

        if ($diff < 2) {
            return 'Shift cannot be deleted within 2 hours of start time';
        }

        $data = [
            'id' => $id,
        ];

        return $this->saCommandService->executeSaCommandWithJson('shift:delete_shift', json_encode($data));
    }

    public function bulkDeleteShifts($member, $shiftIds): string
    {
        $providerIds = array_map(function ($provider) {
            return $provider->getId();
        },
            $this->providerService->getProvidersForMember($member));
        foreach ($shiftIds as $shiftId) {
            // get shift, make sure its > 2 hours away from now and that the provder is in our list
            $shift = $this->shiftRepository->find($shiftId);
            $start = Carbon::parse($shift->getStart());
            $now = Carbon::now();
            $diff = $now->diffInHours($start);
            if ($diff < 2) {
                continue;
            }

            if (!in_array($shift->getProvider()->getId(), $providerIds)) {
                continue;
            }
            $this->deleteShift($shiftId);
        }
        return 'success';
    }
}
