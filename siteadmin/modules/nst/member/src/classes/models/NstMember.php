<?php


namespace nst\member;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ValidateException;
use sa\member\saMember;
use sacore\utilities\fieldValidation;
use nst\member\NurseApplicationPartTwo;
use nst\applications\ApplicationPart1;
use nst\applications\ApplicationPart2;


/**
 * @Entity(repositoryClass="NstMemberRepository")
 * @HasLifecycleCallbacks
 * @IOC_NAME="saMember"
 */
class NstMember extends saMember
{
	/** @OneToOne(targetEntity="NurseApplication", mappedBy="member") */
	protected $nurse_application;

	/** @OneToOne(targetEntity="NurseApplicationPartTwo", mappedBy="member") */
	protected $nurse_application_part_two;

    /** @OneToOne(targetEntity="nst\applications\ApplicationPart1", mappedBy="member") */
    protected $application_part1;

    /** @OneToOne(targetEntity="nst\applications\ApplicationPart2", mappedBy="member") */
    protected $application_part2;

    /** @OneToOne(targetEntity="Nurse", mappedBy="member") */
    protected $nurse;

    /** @OneToOne(targetEntity="Provider", mappedBy="member") */
    protected $provider;

    /** @OneToOne(targetEntity="Executive", mappedBy="member") */
    protected $executive;

    /** @Column(type="string", nullable=true) */
    protected $member_type;

    /** @OneToMany(targetEntity="NurseNote", mappedBy="member", cascade={"all"}) */
    protected $notes;

    /**
     * @PrePersist @PreUpdate
     */
    public function validate()
    {
        if(empty($this->old_id)) {
            $this->old_id = null;
        }

        if (!$this->getId()) {
            $this->setDateCreated( new DateTime('now', app::getInstance()->getTimeZone()) );
        }
    }

    /**
     * @return mixed
     */
    public function getNurseApplication()
    {
        return $this->nurse_application;
    }

    /**
     * @param mixed $nurse_application
     *
     * @return self
     */
    public function setNurseApplication($nurse_application)
    {
        $this->nurse_application = $nurse_application;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNurseApplicationPartTwo()
    {
        return $this->nurse_application_part_two;
    }

    /**
     * @param mixed $nurse_application_part_two
     *
     * @return self
     */
    public function setNurseApplicationPartTwo($nurse_application_part_two)
    {
        $this->nurse_application_part_two = $nurse_application_part_two;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationPart1()
    {
        return $this->application_part1;
    }

    /**
     * @param mixed $application_part1
     *
     * @return self
     */
    public function setApplicationPart1($application_part1)
    {
        $this->application_part1 = $application_part1;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationPart2()
    {
        return $this->application_part2;
    }

    /**
     * @param mixed $application_part2
     *
     * @return self
     */
    public function setApplicationPart2($application_part2)
    {
        $this->application_part2 = $application_part2;

        return $this;
    }

    /**
     * @return Nurse
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * @param mixed $nurse
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return mixed
     */
    public function getMemberType()
    {
        return $this->member_type;
    }

    /**
     * @param mixed $member_type
     */
    public function setMemberType($member_type)
    {
        $this->member_type = $member_type;
    }

    /**
     * Add notes
     *
     * @param \nst\member\ $notes
     * @return saMember
     */
    public function addNote(\nst\member\NurseNote $notes)
    {
        $this->notes[] = $notes;

        return $this;
    }

    /**
     * Remove notes
     *
     * @param \nst\member\NurseNote $notes
     */
    public function removeNote(\nst\member\NurseNote $notes)
    {
        $this->notes->removeElement($notes);
    }

    /**
     * Get Notes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Get the value of executive
     */ 
    public function getExecutive()
    {
        return $this->executive;
    }

    /**
     * Set the value of executive
     *
     * @return  self
     */ 
    public function setExecutive($executive)
    {
        $this->executive = $executive;

        return $this;
    }
}
