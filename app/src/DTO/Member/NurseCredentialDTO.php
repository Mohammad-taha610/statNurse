<?php

namespace App\DTO\Member;

use App\Entity\Nst\Member\NurseCredential;

class NurseCredentialDTO
{
    public readonly string $id;
    public readonly string $name;
    public function __construct(NurseCredential $nurseCredential)
    {
       $this->id = $nurseCredential->getId();
         $this->name = $nurseCredential->getName();
    }
}
