<?php

namespace App\DTO\Shift;

use App\Entity\Nst\Events\PresetShiftTime;

class PresetShiftTimeDTO
{
    public readonly int $id;
    public ShiftCategoryDTO $shiftCategory;
    public string $displayTime;

    public function __construct(PresetShiftTime $presetShiftTime)
    {
        $this->id = $presetShiftTime->getId();
        $this->shiftCategory = ShiftCategoryDTO::fromEntity($presetShiftTime->getCategory());
        $this->displayTime = $presetShiftTime->getHumanReadable();
    }
}
