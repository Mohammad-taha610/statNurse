<?php

namespace App\DTO\File;

class NstFileTagDTO
{
    public int $id;
    public string $name;

    public function __construct($tag)
    {
        $this->id = $tag->getId();
        $this->name = $tag->getName();
    }
}
