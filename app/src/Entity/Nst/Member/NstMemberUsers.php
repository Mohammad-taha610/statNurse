<?php

namespace App\Entity\Nst\Member;

use App\Entity\Sax\Member\saMemberUsers;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class NstMemberUsers
 *
 * @IOC_NAME="saMemberUsers"
 */
#[Entity]
#[HasLifecycleCallbacks]
class NstMemberUsers extends saMemberUsers implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ManyToOne(targetEntity: NstMember::class, fetch: 'EAGER', inversedBy: 'users')]
    protected $member;


    /**
     * @var bool $bonus_allowed
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $bonus_allowed;

    /**
     * @var bool $covid_allowed
     */
    #[Column(type: 'boolean', nullable: true)]
    protected $covid_allowed;

    /**
     * @var string $user_type
     */
    #[Column(type: 'string')]
    protected $user_type;

    /**
     * @return string
     */
    public function getUserType()
    {
        return $this->user_type;
    }

    /**
     * @param  string  $user_type
     * @return NstMemberUsers
     */
    public function setUserType($user_type)
    {
        $this->user_type = $user_type;

        return $this;
    }

    /**
     * @return bool
     */
    public function getBonusAllowed()
    {
        return $this->bonus_allowed;
    }

    /**
     * @param  bool  $bonus_allowed
     * @return NstMemberUsers
     */
    public function setBonusAllowed($bonus_allowed)
    {
        $this->bonus_allowed = $bonus_allowed;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCovidAllowed()
    {
        return $this->covid_allowed;
    }

    /**
     * @param  bool  $covid_allowed
     * @return NstMemberUsers
     */
    public function setCovidAllowed($covid_allowed)
    {
        $this->covid_allowed = $covid_allowed;

        return $this;
    }

    public function getRoles(): array
    {
        $isProvider = $this->getMember()->getMemberType() === 'Provider';
        $isNurse = $this->getMember()->getMemberType() === 'Nurse';
        $isExecutive = $this->getMember()->getMemberType() === 'Executive';

        if ($isNurse) {
            return ['ROLE_NURSE'];
        }
        if ($isProvider) {
            if ($this->getUserType() === 'Scheduler') {
                return ['ROLE_PROVIDER', 'ROLE_PROVIDER_SCHEDULER'];
            }
            else if ($this->getUserType() == 'Admin') {
                return ['ROLE_PROVIDER', 'ROLE_PROVIDER_ADMIN'];
            }
        }
        if ($isExecutive) {
            return ['ROLE_EXECUTIVE', 'ROLE_PROVIDER_ADMIN', 'ROLE_PROVIDER_SCHEDULER', 'ROLE_PROVIDER'];
        }

        return ['ROLE_USER'];
    }

    public function setRoles(array $roles): static
    {
        $this->roles = (['ROLE_USER']);

        return $this;
    }

    public function eraseCredentials()
    {

    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function getPassword(): string
    {
        return $this->password;
    }
    /**
     * @return NstMember
     */
    public function getMember(): NstMember
    {
        return $this->member;
    }

    /**
     * @param NstMember $member
     * @return NstMemberUsers
     */
    public function setMember($member): NstMemberUsers
    {
        $this->member = $member;
        return $this;
    }
}
