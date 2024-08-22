<?php

namespace sa\events;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use sacore\application\Exception;
use sacore\application\ValidateException;

/**
 * Class Reservation
 *
 * @Entity(repositoryClass="ReservationRepository")
 *
 * @Table(name="sa_events_reservations")
 *
 * @InheritanceType("SINGLE_TABLE")
 *
 * @HasLifecycleCallbacks
 *
 * @IOC_Name="EventReservation"
 */
class Reservation
{
    /**
     * @Id
     *
     * @Column(type="integer")
     *
     * @GeneratedValue
     */
    protected $id;

    /** @Column(type="string", nullable=false) */
    protected $email;

    /**
     * @var Event
     *
     * @ManyToOne(targetEntity="Event", inversedBy="reservations")
     */
    protected $event;

    /**
     * @ManyToOne(targetEntity="EventRecurrence", inversedBy="reservations")
     */
    protected $eventRecurrence;

    /**
     * @PrePersist
     *
     * @PreUpdate
     */
    public function validate()
    {
        if (empty($this->email)) {
            throw new ValidateException('Please enter an email.');
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidateException('The email you entered isn\'t valid.');
        }

        if (empty($this->event)) {
            throw new Exception('Reservation must have an event.');
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
     * Set email
     *
     * @param  string  $email
     * @return Reservation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set event
     *
     * @param  \sa\events\Event  $event
     * @return Reservation
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \sa\events\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set event
     *
     * @param  \sa\events\EventRecurrence  $eventRecurrence
     * @return Reservation
     */
    public function setRecurrence(EventRecurrence $eventRecurrence = null)
    {
        $this->eventRecurrence = $eventRecurrence;

        return $this;
    }

    /**
     * Get event recurrence
     *
     * @return \sa\events\EventRecurrence
     */
    public function getRecurrence()
    {
        return $this->eventRecurrence;
    }
}
