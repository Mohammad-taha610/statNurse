<?php

namespace App\DTO\Member;

use App\Entity\Nst\Member\NstMember;

class NstMemberDTO
{
    public $company;

    public function __construct(NstMember $member)
    {
        $this->company = $member->getCompany();
    }
}
