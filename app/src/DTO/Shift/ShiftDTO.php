<?php

namespace App\DTO\Shift;

use App\DTO\Member\NurseDTO;
use App\DTO\Member\ProviderDTO;
use App\Entity\Nst\Events\Shift;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class ShiftDTO
{
    public int $id;
    public string $start;
    public string $end;
    public string $startTime;
    public string $endTime;
    public string $date;
    public string $status;
    public int $providerId;
    public string $nurseType;
    public string $shiftRoute;
    public string $nurseName;
    public string $nurseRoute;
    public float $bonus;
    public string $bonusDescription;
    public string $description;

    public float $incentive;
    public bool $isCovid;
    public ShiftCategoryDTO $category;
    public NurseDTO $nurse;
    public ProviderDTO $provider;


    public static function fromEntity(Shift $shift, string $tz = 'America/New_York')
    {
        $shiftDTO = new self();
        $shiftDTO->id = $shift->getId();
        $shiftStart = CarbonImmutable::parse($shift->getStart())
            ->shiftTimezone('UTC')
            ->setTimezone($tz);
        $shiftEnd = CarbonImmutable::parse($shift->getEnd())
            ->shiftTimezone('UTC')
            ->setTimezone($tz);

        $startDate = $shiftStart;
        $endDate = $shiftEnd;

        $shiftDTO->date = $startDate->format('Y-m-d');
        $shiftDTO->start = $startDate->toISOString();
        $shiftDTO->end = $endDate->toISOString();
        $shiftDTO->startTime = $startDate->format('g:i A');
        $shiftDTO->endTime = $endDate->format('g:i A');
        $shiftDTO->status = $shift->getStatus();
        $shiftDTO->provider = new ProviderDTO($shift->getProvider());
        $shiftDTO->providerId = $shift->getProvider()->getId();
        $shiftDTO->nurseType = $shift->getNurseType();
        if ($shift->getNurse() !== null) {
            $shiftDTO->nurseRoute = "/executive/nurse/" . $shift->getNurse()->getId();
        }
        $shiftDTO->bonusDescription = $shift->getBonusDescription();
        $shiftDTO->description = $shift->getDescription();
        if ($shift->getCategory()) {
            $shiftDTO->category = ShiftCategoryDTO::fromEntity($shift->getCategory());
        }
        $shiftDTO->shiftRoute = "/executive/shifts/" . $shift->getId() . "/edit";

        $nurse = $shift->getNurse();
        if ($nurse !== null) {
            $shiftDTO->nurse = new NurseDTO($shift->getNurse());
            $shiftDTO->nurseName = $nurse->getFirstName() . " " . $nurse->getLastName();
        } else {
            $shiftDTO->nurseName = "Unassigned";
        }

        $shiftDTO->bonus = $shift->getBonusAmount() ?? 0;
        $shiftDTO->incentive = $shift->getIncentive() ?? 0;
        $shiftDTO->isCovid = $shift->getIsCovid() ?? false;
        return $shiftDTO;
    }
}
