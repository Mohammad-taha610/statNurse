<?php

namespace nst\member;

/**
 * @Entity(repositoryClass="JobRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="job")
 */
class Job{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="string")*/
    protected $title;
    /** @Column(type="string")*/
    protected $form_needed;


    /** @OneToMany(targetEntity="Nurse", mappedBy="job", fetch="LAZY") */
    protected $nurses;

    /** @OneToMany(targetEntity="NurseApplication", mappedBy="job", fetch="LAZY") */
    protected $nurseApplicants;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->nurses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->nurseApplicants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Job
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add nurse.
     *
     * @param \nst\member\Nurse $nurse
     *
     * @return Job
     */
    public function addNurse(\nst\member\Nurse $nurse)
    {
        $this->nurses[] = $nurse;

        return $this;
    }

    /**
     * Remove nurse.
     *
     * @param \nst\member\Nurse $nurse
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNurse(\nst\member\Nurse $nurse)
    {
        return $this->nurses->removeElement($nurse);
    }

    /**
     * Get nurses.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNurses()
    {
        return $this->nurses;
    }

    /**
     * Add nurseApplicant.
     *
     * @param \nst\member\NurseApplication $nurseApplicant
     *
     * @return Job
     */
    public function addNurseApplicant(\nst\member\NurseApplication $nurseApplicant)
    {
        $this->nurseApplicants[] = $nurseApplicant;

        return $this;
    }

    /**
     * Remove nurseApplicant.
     *
     * @param \nst\member\NurseApplication $nurseApplicant
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNurseApplicant(\nst\member\NurseApplication $nurseApplicant)
    {
        return $this->nurseApplicants->removeElement($nurseApplicant);
    }

    /**
     * Get nurseApplicants.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNurseApplicants()
    {
        return $this->nurseApplicants;
    }
}