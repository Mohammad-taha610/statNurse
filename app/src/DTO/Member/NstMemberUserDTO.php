<?php

namespace App\DTO\Member;

use App\Entity\Nst\Member\NstMemberUsers;
use App\Entity\Nst\Member\Provider;

class NstMemberUserDTO
{
    public int $id;
    public string $username;
    public string $type;
    public NstMemberDTO $member;
    public string $name;
    public array $providers;
    public array $roles = [];

    public function __construct(NstMemberUsers $user)
    {
        $this->id = $user->getId();
        $this->name = $user->getFirstName() . ' ' . $user->getLastName();
        $this->username = $user->getUsername();
        $member = $user->getMember();
        $this->member = new NstMemberDTO($user->getMember());
        $this->type = $member->getExecutive() !== null ? 'Executive' : ($member->getNurse() != null ? 'Nurse' : 'Provider');
        $this->providers = [];
        if ($this->type == 'Executive') {
            /** @var Provider $provider */
            $this->providers = array_map(function ($provider) {
                return new ProviderDTO($provider);
            }, $member->getExecutive()->getProviders()->toArray());
        } elseif ($this->type == 'Provider') {
            $provider = $member->getProvider();
            $this->providers[] = new ProviderDTO($provider);
        }
        $this->roles = $user->getRoles();
    }
}
