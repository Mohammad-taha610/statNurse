<?php

namespace App\Entity\Nst\Messages;

use App\Entity\Nst\Member\Nurse;
use App\Entity\Sax\System\saUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;

/**
 * CURRENTLY ONLY SUPPORTING NURSES
 * WILL NEED TO ADD MORE SUPPORT FOR PROVIDERS, OR OTHER CONTACTS
 *
 * @IOC_NAME="NstMessage"
 */
#[Table(name: 'nst_message')]
#[InheritanceType('SINGLE_TABLE')]
#[Entity(repositoryClass: 'NstMessageRepository')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
class NstMessage
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string', nullable: true)]
    protected $message;

    /**
     * @var ArrayCollection $nurses
     */
    #[ManyToMany(targetEntity: Nurse::class, mappedBy: 'messages')]
    protected $nurses;

    #[Column(type: 'datetime', nullable: true)]
    protected $date_created;

    #[Column(type: 'boolean', nullable: true)]
    protected $was_sent_successfully;

    #[Column(type: 'boolean', options: ['default' => false])]
    protected $has_been_viewed;

    #[ManyToOne(targetEntity: saUser::class)]
    #[JoinColumn(name: 'sa_user_id', referencedColumnName: 'id')]
    protected $sa_user;

    #[Column(type: 'integer', options: ['default' => 0])]
    protected $num_media;

    #[Column(type: 'string', nullable: true)]
    protected $message_sid;

    public function __construct()
    {
        $this->nurses = new ArrayCollection();
    }

    #[PrePersist]
    public function validate()
    {
        if (! $this->getId()) {
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
     * @param  \nst\member\Nurse  $messages
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
     * @param  \nst\member\Nurse  $nurse
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
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
