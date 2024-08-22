<?php

namespace App\Entity\Nst\Member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'NurseNote')]
#[Entity(repositoryClass: 'NurseNoteRepository')]
#[InheritanceType('SINGLE_TABLE')]
class NurseNote
{
    /**
     * @var int $id
     */
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[ManyToOne(targetEntity: 'NstMember', inversedBy: 'notes')]
    protected $member;

    #[Column(type: 'string')]
    protected $note;

    #[Column(type: 'string')]
    protected $date;

    #[Column(type: 'string')]
    protected $time;

    #[Column(type: 'string')]
    protected $admin;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  mixed  $member
     * @return NurseNote
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return NstMember
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set note
     *
     * @param  string  $note
     * @return NurseNote
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set date
     *
     * @param  string  $date
     * @return NurseNote
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set time
     *
     * @param  string  $time
     * @return NurseNote
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set admin
     *
     * @param  string  $admin
     * @return NurseNote
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return string
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}
