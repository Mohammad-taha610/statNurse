<?php

namespace App\DTO\Shift;

use App\Entity\Nst\Events\NstCategory;

class ShiftCategoryDTO
{
    public int $id;
    public string $name;

    /**
     * @param NstCategory|null $category
     * @return self
     */
    public static function fromEntity(NstCategory|null $category): self
    {
        $dto = new self();
        if ($category) {
            $dto->id = $category->getId();
            $dto->name = $category->getName();
        }

        return $dto;
    }
}
