<?php

namespace nst\system;

use sa\system\saState;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="NstStateRepository")
 * @IOC_NAME="saState"
 */
class NstState extends \sa\system\saState {

    /**
     * @var ArrayCollection $nurse_working_states
     * @ManyToMany(targetEntity="nst\member\Nurse", mappedBy="states_able_to_work")
     */
    protected $nurse_working_states;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->nurse_working_states = new \Doctrine\Common\Collections\ArrayCollection();
    }

}