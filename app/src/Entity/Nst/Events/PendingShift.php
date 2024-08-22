<?php

namespace App\Entity\Nst\Events;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'pending_shift')]
#[Entity(repositoryClass: 'PendingShiftRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class PendingShift
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    protected $id;

    #[Column(name: 'nurseApproved', type: 'boolean', nullable: false)]
    public $nurseApproved = false;

    #[Column(name: 'providerApproved', type: 'boolean', nullable: false)]
    public $providerApproved = false;

    public function getId()
    {
        return $this->id;
    }

    public function setNurse($nurse)
    {
        $this->nurse = $nurse;

        return $this;
    }

    public function getNurse()
    {
        return $this->nurse;
    }

    public function setNurseApproved($nurseApproved)
    {
        $this->nurseApproved = $nurseApproved;

        return $this;
    }

    public function getNurseApproved()
    {
        return $this->nurseApproved;
    }

    public function setProviderApproved($providerApproved)
    {
        $this->providerApproved = $providerApproved;

        return $this;
    }

    public function getProviderApproved()
    {
        return $this->providerApproved;
    }
}
