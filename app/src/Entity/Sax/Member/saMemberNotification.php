<?php

namespace App\Entity\Sax\Member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use sacore\application\DateTime;

#[Table(name: 'sa_member_notifications')]
#[Index(name: 'IDX_member_address_member', columns: ['member_id'])]
#[Entity(repositoryClass: 'sa\member\saMemberNotificationRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class saMemberNotification
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'text', nullable: true)]
    protected $message;

    #[Column(type: 'datetime', nullable: true)]
    protected $date_created;

    #[ManyToOne(targetEntity: 'saMember', inversedBy: 'notifications')]
    protected $member;

    #[Column(type: 'boolean', nullable: true)]
    protected $is_viewed;

    #[Column(type: 'text', nullable: true)]
    protected $link;

    #[Column(type: 'text', nullable: true)]
    protected $image_url;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date_created = new DateTime();
        $this->is_viewed = false;
    }

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
     * Set message
     *
     * @param  string  $message
     * @return saMemberNotification
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set dateCreated
     *
     * @param  \DateTime  $dateCreated
     * @return saMemberNotification
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set isViewed
     *
     * @param  bool  $isViewed
     * @return saMemberNotification
     */
    public function setIsViewed($isViewed)
    {
        $this->is_viewed = $isViewed;

        return $this;
    }

    /**
     * Get isViewed
     *
     * @return bool
     */
    public function getIsViewed()
    {
        return $this->is_viewed;
    }

    /**
     * Set member
     *
     * @param  \sa\member\saMember  $member
     * @return saMemberNotification
     */
    public function setMember(\sa\member\saMember $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \sa\member\saMember
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set link
     *
     * @param  string  $link
     * @return saMemberNotification
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set imageUrl
     *
     * @param  string  $imageUrl
     * @return saMemberNotification
     */
    public function setImageUrl($imageUrl)
    {
        $this->image_url = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }
}
