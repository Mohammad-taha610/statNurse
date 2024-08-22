<?php

namespace App\Entity\Sax\Events;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use sacore\application\ValidateException;

#[Table(name: 'events_categories')]
#[Entity(repositoryClass: 'CategoryRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[HasLifecycleCallbacks]
class Category
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    protected $id;

    #[Column(type: 'string', nullable: false)]
    protected $name;

    #[Column(type: 'text', nullable: true)]
    protected $description;

    #[OneToMany(targetEntity: 'Event', mappedBy: 'category')]
    protected $events;

    #[Column(type: 'array', nullable: true)]
    protected $access_groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    #[PrePersist]
    #[PreUpdate]
    public function validate()
    {
        if (empty($this->name)) {
            throw new ValidateException('Name cannot be empty.');
        }
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
     * Set name
     *
     * @param  string  $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param  string  $description
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add event
     *
     * @param  \sa\events\Event  $event
     * @return Category
     */
    public function addEvent(Event $event)
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event
     *
     * @param  \sa\events\Event  $event
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return mixed
     */
    public function getAccessGroups()
    {
        return $this->access_groups;
    }

    /**
     * @param  mixed  $access_groups
     */
    public function setAccessGroups($access_groups)
    {
        $this->access_groups = $access_groups;

        return $this;
    }

    /**
     * @param  \sa\member\saMember  $member
     * @param  \sa\member\saMemberUsers  $user
     * @return bool
     */
    public function hasPermissionToViewEvent($member = null, $user = null)
    {
        $access_groups = $this->getAccessGroups();

        // Backwards compatibility for existing events
        if (count($access_groups) == 0) {
            return true;
        }

        if (empty($access_groups)) {
            return true;
        }

        if (count($access_groups) > 0) {
            if (in_array('E', $access_groups)) {
                return true;
            }

            if (in_array('M', $access_groups) && $member) {
                return true;
            }

            if (in_array('G', $access_groups) && ! $member) {
                return true;
            }

            if ($member && app::get()->getConfiguration()->get('member_groups') == 'member') {
                $groups = $member->getGroups();
                foreach ($groups as $group) {
                    if (in_array($group->getId(), $access_groups)) {
                        return true;
                    }
                }
            } elseif ($member && $user && app::get()->getConfiguration()->get('member_groups') == 'user') {
                $groups = $user->getGroups();
                foreach ($groups as $group) {
                    if (in_array($group->getId(), $access_groups)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
