<?php


namespace nst\events;

/**
 * @Entity(repositoryClass="PendingShiftRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="pending_shift")
 */
class PendingShift{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(type="boolean", nullable=false) */
    public $nurseApproved = false;

    /** @Column(type="boolean", nullable=false) */
    public $providerApproved = false;

    public function getId(){
        return $this->id;
    }

    public function setNurse($nurse){
        $this->nurse = $nurse;
        return $this;
    }

    public function getNurse(){
        return $this->nurse;
    }

    public function setNurseApproved($nurseApproved){
        $this->nurseApproved = $nurseApproved;
        return $this;
    }

    public function getNurseApproved(){
        return $this->nurseApproved;
    }

    public function setProviderApproved($providerApproved){
        $this->providerApproved = $providerApproved;
        return $this;
    }

    public function getProviderApproved(){
        return $this->providerApproved;
    }

}