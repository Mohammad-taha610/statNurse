<?php
namespace nst\messages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use nst\member\NstMember;
use nst\member\NstContact;
use nst\member\Nurse;
use nst\member\NurseApplicationPartTwo;
use sa\system\SaUser;
use DateTime;

/**
 * CURRENTLY ONLY SUPPORTING NURSES
 * WILL NEED TO ADD MORE SUPPORT FOR PROVIDERS, OR OTHER CONTACTS
 * @InheritanceType("SINGLE_TABLE")
 * @Entity(repositoryClass="NstMessageRepository")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="nst_message")
 * @IOC_NAME="NstMessage"
 */
class NstMessage
{
    /** 
     * @Id @Column(type="integer") @GeneratedValue 
     */
    protected $id;

    /**
     * @Column(type="string", length=1020, nullable=true)
     */
    protected $message;

    /**
     * @var ArrayCollection $nurses
     * @ManyToMany(targetEntity="nst\member\Nurse", mappedBy="messages")
     */
    protected $nurses;

    /** 
     * @Column(type="datetime", nullable=true) 
     */
    protected $date_created;

    /** 
     * @Column(type="boolean", nullable=true) 
     */
    protected $was_sent_successfully;

    /**
     * @Column(type="boolean", options={"default": false})
     */
    protected $has_been_viewed;

    /**
     * @ManyToOne(targetEntity="sa\system\SaUser")
     * @JoinColumn(name="sa_user_id", referencedColumnName="id")
     */
    protected $sa_user;

    /**
     * @ManyToOne(targetEntity="nst\applications\ApplicationPart2")
     * @JoinColumn(name="application_2_id", referencedColumnName="id")
     */
    protected $application_2;

    /**
     * @Column(type="integer", options={"default": 0})
     */
    protected $num_media;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $message_sid;

    public function __construct()
    {
        $this->nurses = new ArrayCollection();
    }

    /**
     * @PrePersist @PreUpdate
     */
    public function validate()
    {
        if (!$this->getId()) {
            $this->setDateCreated(new DateTime());
        }
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of message
     */ 
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the value of message
     *
     * @return  self
     */ 
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return ArrayCollection
     */ 
    public function getNurses()
    {
        return $this->nurses;
    }

    /**
     * Add a nurse to this message's assigned nurses
     *
     * @param \nst\member\Nurse $messages
     * @return NstMessage
     */ 
    public function addNurse(Nurse $nurse)
    {
        $this->nurses->add($nurse);

        return $this;
    }

    /**
     * Remove a nurse from this message's assigned nurses
     *
     * @param \nst\member\Nurse $nurse
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */ 
    public function removeNurse(Nurse $nurse)
    {
        return $this->nurses->removeElement($nurse);
    }

    /**
     * Get the value of date_created
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set the value of date_created
     *
     * @return  self
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * Get the value of was_sent_successfully
     */
    public function getWasSentSuccessfully()
    {
        return $this->was_sent_successfully;
    }

    /**
     * Set the value of was_sent_successfully
     *
     * @return  self
     */
    public function setWasSentSuccessfully($was_sent_successfully)
    {
        $this->was_sent_successfully = $was_sent_successfully;

        return $this;
    }

    /**
     * Get the value of has_been_viewed
     */
    public function getHasBeenViewed()
    {
        return $this->has_been_viewed;
    }

    /**
     * Set the value of has_been_viewed
     *
     * @return  self
     */
    public function setHasBeenViewed($has_been_viewed)
    {
        $this->has_been_viewed = $has_been_viewed;

        return $this;
    }

    /**
     * Get the value of sa_user
     */ 
    public function getSaUser()
    {
        return $this->sa_user;
    }

    /**
     * Set the value of sa_user
     *
     * @return SaUser
     */ 
    public function setSaUser($sa_user)
    {
        $this->sa_user = $sa_user;

        return $this;
    }

    /**
     * Get the value of application_2
     */ 
    public function getApplication()
    {
        return $this->application_2;
    }

    /**
     * Set the value of application_2
     *
     * @return NurseApplicationPartTwo
     */ 
    public function setApplication($application_2)
    {
        $this->application_2 = $application_2;

        return $this;
    }

    /**
     * Get the number of media attachments in this message
     */
    public function getNumberOfMedia()
    {
        return $this->num_media;
    }

    /**
     * Set the number of media attachments in this message
     *
     * @return  self
     */ 
    public function setNumberOfMedia($num_media)
    {
        $this->num_media = $num_media;

        return $this;
    }

    /**
     * Get the value of twilio message sid
     */ 
    public function getSid()
    {
        return $this->message_sid;
    }

    /**
     * Set the value of twilio message sid
     *
     * @return  self
     */ 
    public function setSid($message_sid)
    {
        $this->message_sid = $message_sid;

        return $this;
    }
}
