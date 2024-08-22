<?php

namespace App\Entity\Nst\Member;

use App\Entity\Sax\Member\saMember;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use sacore\application\app;
use sacore\application\DateTime;

/**
 * @IOC_NAME="saMember"
 */
#[Entity(repositoryClass: 'NstMemberRepository')]
#[HasLifecycleCallbacks]
class NstMember extends saMember
{
    #[OneToOne(mappedBy: 'member', targetEntity: NurseApplication::class)]
    protected $nurse_application;

    #[OneToOne(mappedBy: 'member', targetEntity: NurseApplicationPartTwo::class)]
    protected $nurse_application_part_two;

    #[OneToOne(mappedBy: 'member', targetEntity: Nurse::class)]
    protected $nurse;

    #[OneToOne(mappedBy: 'member', targetEntity: Provider::class)]
    protected $provider;

    #[OneToOne(mappedBy: 'member', targetEntity: Executive::class)]
    protected $executive;



    #[Column(type: 'string', nullable: true)]
    protected $member_type;

    #[OneToMany(mappedBy: 'member', targetEntity: NurseNote::class, cascade: ['all'])]
    protected $notes;

    #[PrePersist]
    public function validate()
    {
        if (empty($this->old_id)) {
            $this->old_id = null;
        }

        if (! $this->getId()) {
            $this->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
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
     * @param  mixed  $nurse_application
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
     * @param  mixed  $nurse_application_part_two
     * @return self
     */
    public function setNurseApplicationPartTwo($nurse_application_part_two)
    {
        $this->nurse_application_part_two = $nurse_application_part_two;

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
     * @param  mixed  $nurse
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
     * @param  mixed  $provider
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
     * @param  mixed  $member_type
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
    public function addNote(NurseNote $notes)
    {
        $this->notes[] = $notes;

        return $this;
    }

    /**
     * Remove notes
     *
     * @param  \nst\member\NurseNote  $notes
     */
    public function removeNote(NurseNote $notes)
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
     * @return Executive
     */
    public function getExecutive()
    {
        return $this->executive;
    }

    /**
     * @param Executive $executive
     * @return NstMember
     */
    public function setExecutive($executive)
    {
        $this->executive = $executive;
        return $this;
    }
}
