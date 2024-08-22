<?php

namespace App\Entity\Sax\Events;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: 'EventRecurrenceRepository')]
#[InheritanceType(value: 'SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[Table(name: 'EventRecurrence')]
class EventRecurrence
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    protected $id;

    #[Column(name: 'recurrenceUniqueId', type: 'string')]
    protected $recurrenceUniqueId;

    /**
     * @var \DateTime
     */
    #[Column(type: 'datetime')]
    protected $start;

    /**
     * @var \DateTime
     */
    #[Column(type: 'datetime')]
    protected $end;

    #[Column(type: 'string', nullable: true)]
    protected $timezone;

    /**
     * @var string
     */
    #[Column(type: 'text')]
    protected $description;

    #[Column(type: 'string', nullable: true)]
    protected $location_name;

    #[Column(type: 'string', nullable: true)]
    protected $street_one;

    #[Column(type: 'string', nullable: true)]
    protected $street_two;

    #[Column(type: 'string', nullable: true)]
    protected $city;

    #[Column(type: 'string', nullable: true)]
    protected $state;

    #[Column(type: 'string', nullable: true)]
    protected $postal_code;

    #[Column(type: 'string', nullable: true)]
    protected $contact_name;

    #[Column(type: 'string', nullable: true)]
    protected $contact_phone;

    #[Column(type: 'string', nullable: true)]
    protected $contact_email;

    #[ManyToOne(targetEntity: 'Category', inversedBy: 'events')]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    protected $category;

    /**
     * If true, the recurrence will be assumed to exist, if false it will be assumed deleted.
     *
     * @var bool
     */
    #[Column(name: 'recurrenceExists', type: 'boolean', options: ['default' => true])]
    protected $recurrenceExists;

    /**
     * @var Event
     */
    #[ManyToOne(targetEntity: 'Event', fetch: 'EAGER', inversedBy: 'event_recurrences')]
    #[JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    protected $event;

    /**
     * @OneToMany(targetEntity="Reservation", mappedBy="eventRecurrence", orphanRemoval=true)
     */
    protected $reservations;

    public function __construct()
    {
        $this->isLocked = false;
    }

    /**
     * @ORM\PrePersist
     *
     * @ORM\PrepUpdate
     */
    public function validate()
    {
        if (is_null($this->start)) {
            throw new ValidateException('Start date time must be set.');
        }

        if (is_null($this->end)) {
            throw new ValidateException('End date time must be set.');
        }

        if (is_null($this->event)) {
            throw new ValidateException('This recurrence must be assigned to an event.');
        }
    }

    public function toArray()
    {
        $timezone = new DateTimeZone($this->event->getTimezone());

        if ($this->event->isAllDay()) {
            $start = $this->start;
            $start->setTime(0, 0, 0);

            $end = $this->end;
            $end->setTime(0, 0, 0);
        } else {
            $start = new DateTime($this->getStart()->format('Y-m-d H:i'), new DateTimeZone('UTC'));
            $end = new DateTime($this->getEnd()->format('Y-m-d H:i'), new DateTimeZone('UTC'));
        }

        $start->setTimezone($timezone);
        $end->setTimezone($timezone);

        return [
            'id' => $this->id,
            'start' => $start,
            'end' => $end,
            'event' => $this->event->toArray(),
        ];
    }

    /**
     * Get id
     *
     * @return \int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getRecurrenceUniqueId()
    {
        return $this->recurrenceUniqueId;
    }

    public function setRecurrenceUniqueId($recurrenceId)
    {
        $this->recurrenceUniqueId = $recurrenceId;

        return $this;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return EventRecurrence
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
     * Set start
     *
     *
     * @return EventRecurrence
     */
    public function setStart(DateTime $start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        if (! empty($this->start)) {
            $this->start->setTimezone(new DateTimeZone($this->event->getTimezone()));
        }

        return $this->start;
    }

    /**
     * Set end
     *
     * @param  \DateTime  $end
     * @return EventRecurrence
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        if (! empty($this->end)) {
            $this->end->setTimezone(new DateTimeZone($this->event->getTimezone()));
        }

        return $this->end;
    }

    /**
     * Set timezone
     *
     * @param  string  $timezone
     * @return Event
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param  string  $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRecurrenceExists()
    {
        return $this->recurrenceExists;
    }

    /**
     * @param  bool  $exists
     * @return $this
     */
    public function setRecurrenceExists($exists)
    {
        $this->recurrenceExists = $exists;

        return $this;
    }

    /**
     * Set event
     *
     * @param  Event  $event
     * @return EventRecurrence
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Add reservation
     *
     *
     * @return EventRecurrence
     */
    public function addReservation(Reservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    /**
     * Remove reservation
     */
    public function removeReservation(Reservation $reservation)
    {
        $this->reservations->removeElement($reservation);
    }

    /**
     * Get reservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * Set link
     *
     * @param  string  $link
     * @return Event
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
     * Set locationName
     *
     * @param  string  $locationName
     * @return Event
     */
    public function setLocationName($locationName)
    {
        $this->location_name = $locationName;

        return $this;
    }

    /**
     * Get locationName
     *
     * @return string
     */
    public function getLocationName()
    {
        return $this->location_name;
    }

    /**
     * Set street
     *
     * @param  string  $street
     * @return Event
     */
    public function setStreetOne($street)
    {
        $this->street_one = $street;

        return $this;
    }

    /**
     * Get street one
     *
     * @return string
     */
    public function getStreetOne()
    {
        return $this->street_one;
    }

    /**
     * Set streetTwo
     *
     * @param  string  $streetTwo
     * @return Event
     */
    public function setStreetTwo($streetTwo)
    {
        $this->street_two = $streetTwo;

        return $this;
    }

    /**
     * Get streetTwo
     *
     * @return string
     */
    public function getStreetTwo()
    {
        return $this->street_two;
    }

    /**
     * Set state
     *
     * @param  string  $state
     * @return Event
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set city
     *
     * @param  string  $city
     * @return Event
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set postalCode
     *
     * @param  string  $postalCode
     * @return Event
     */
    public function setPostalCode($postalCode)
    {
        $this->postal_code = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Set contactName
     *
     * @param  string  $contactName
     * @return Event
     */
    public function setContactName($contactName)
    {
        $this->contact_name = $contactName;

        return $this;
    }

    /**
     * Get contactName
     *
     * @return string
     */
    public function getContactName()
    {
        return $this->contact_name;
    }

    /**
     * Set contactPhone
     *
     * @param  string  $contactPhone
     * @return Event
     */
    public function setContactPhone($contactPhone)
    {
        $this->contact_phone = $contactPhone;

        return $this;
    }

    /**
     * Get contactPhone
     *
     * @return string
     */
    public function getContactPhone()
    {
        return $this->contact_phone;
    }

    /**
     * Set contactEmail
     *
     * @param  string  $contactEmail
     * @return Event
     */
    public function setContactEmail($contactEmail)
    {
        $this->contact_email = $contactEmail;

        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contact_email;
    }
}
