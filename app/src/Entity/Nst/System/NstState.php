<?php

namespace App\Entity\Nst\System;

use App\Entity\Nst\Member\Nurse;
use App\Entity\Sax\System\saState;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @Entity(repositoryClass="NstStateRepository")
 */
#[Entity]
class NstState extends saState
{
    /**
     * @var ArrayCollection $nurse_working_states
     *
     * @ManyToMany(targetEntity="nst\member\Nurse", mappedBy="states_able_to_work")
     */
    #[ManyToMany(targetEntity: Nurse::class, mappedBy: 'states_able_to_work')]
    protected $nurse_working_states;

    public function __construct()
    {
        parent::__construct();
        $this->nurse_working_states = new \Doctrine\Common\Collections\ArrayCollection();
    }
}
