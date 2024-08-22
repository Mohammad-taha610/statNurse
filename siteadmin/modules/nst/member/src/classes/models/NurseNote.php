<?php


namespace nst\member;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use sacore\application\DateTime;
use sa\files\saFile;
use sa\member\saMember;
use sa\system\saCity;
use sa\system\saState;
use nst\system\nstMember;

/**
 * @Entity(repositoryClass="NurseNoteRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @Table="NurseNote"
 */
class NurseNote {

    /**
     * @var int $id
     * @Id @Column(type="integer") @GeneratedValue
     */
    protected $id;
    
    /** @ManyToOne(targetEntity="NstMember") */
    protected $member;

    /** @Column(type="string") */
    protected $note;
    
    /** @Column(type="string") */
    protected $date;
    
    /** @Column(type="string") */
    protected $time;
    
    /** @Column(type="string") */
    protected $admin;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $member
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
     * @param string $note
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
     * @param string $date
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
     * @param string $time
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
     * @param string $admin
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

?>