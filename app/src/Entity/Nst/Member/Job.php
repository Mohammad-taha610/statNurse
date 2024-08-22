<?php

namespace App\Entity\Nst\Member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'job')]
#[Entity(repositoryClass: 'JobRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class Job
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string')]
    protected $title;

    #[Column(type: 'string')]
    protected $form_needed;

    #[OneToMany(mappedBy: 'job', targetEntity: Nurse::class, fetch: 'LAZY')]
    protected $nurses;

    #[OneToMany(mappedBy: 'job', targetEntity: NurseApplication::class, fetch: 'LAZY')]
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
     * @param  string  $title
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
     * @param  \nst\member\Nurse  $nurse
     * @return Job
     */
    public function addNurse(Nurse $nurse)
    {
        $this->nurses[] = $nurse;

        return $this;
    }

    /**
     * Remove nurse.
     *
     * @param  \nst\member\Nurse  $nurse
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNurse(Nurse $nurse)
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
     * @param  \nst\member\NurseApplication  $nurseApplicant
     * @return Job
     */
    public function addNurseApplicant(NurseApplication $nurseApplicant)
    {
        $this->nurseApplicants[] = $nurseApplicant;

        return $this;
    }

    /**
     * Remove nurseApplicant.
     *
     * @param  \nst\member\NurseApplication  $nurseApplicant
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNurseApplicant(NurseApplication $nurseApplicant)
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
