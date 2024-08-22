<?php

namespace sa\events;

use DateTimeZone;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use sacore\application\DateTime;
use sacore\application\ValidateException;

/**
 * Class Event
 *
 * @Entity(repositoryClass="EventRepository")
 *
 * @InheritanceType("SINGLE_TABLE")
 *
 * @HasLifecycleCallbacks
 */
class Event
{
    const FREQUENCY_DAILY = 'DAILY';

    const FREQUENCY_WEEKLY = 'WEEKLY';

    const FREQUENCY_MONTHLY = 'MONTHLY';

    const FREQUENCY_YEARLY = 'YEARLY';

    const WEEKDAY_SUN = 'SU';

    const WEEKDAY_MON = 'MO';

    const WEEKDAY_TUES = 'TU';

    const WEEKDAY_WED = 'WE';

    const WEEKDAY_THURS = 'TH';

    const WEEKDAY_FRI = 'FR';

    const WEEKDAY_SAT = 'SA';

    const MONTH_JAN = 0;

    const MONTH_FEB = 1;

    const MONTH_MAR = 2;

    const MONTH_APR = 3;

    const MONTH_MAY = 4;

    const MONTH_JUN = 5;

    const MONTH_JUL = 6;

    const MONTH_AUG = 7;

    const MONTH_SEP = 8;

    const MONTH_OCT = 9;

    const MONTH_NOV = 10;

    const MONTH_DEC = 11;

    const LARGE_NUMBER = 1000000000;

    /**
     * @Id
     *
     * @Column(type="integer")
     *
     * @GeneratedValue
     */
    protected $id;

    /** @Column(type="string", nullable=false) */
    protected $name;

    /** @Column(type="text", nullable=true) */
    protected $description;

    /** @Column(type="string", nullable=true) */
    protected $link;

    /**
     * @var DateTime
     *
     * @Column(type="time", nullable=true)
     */
    protected $start_time;

    /**
     * @var DateTime
     *
     * @Column(type="time", nullable=true)
     */
    protected $end_time;

    /** @Column(type="string", nullable=true) */
    protected $timezone;

    /**
     * @var DateTime
     *
     * @Column(type="date", nullable=false)
     */
    protected $start_date;

    /**
     * @var DateTime
     *
     * @Column(type="date", nullable=true)
     */
    protected $end_date;

    /**
     * @var DateTime
     *
     * @Column(type="date", nullable=true)
     */
    protected $until_date;

    /** @Column(type="string", nullable=true) */
    protected $recurrence_rules;

    /** @Column(type="string", nullable=true) */
    protected $exclude_rules;

    /** @Column(type="string", nullable=true) */
    protected $frequency;

    /** @Column(type="string", name="recurrence_interval", nullable=true) */
    protected $interval;

    /**
     * @var int
     *
     * @Column(type="integer", nullable=true)
     */
    protected $max_recurrences;

    /** @Column(type="string", nullable=true) */
    protected $recurrence_months;

    /** @Column(type="string", nullable=true) */
    protected $recurrence_days;

    /** @Column(type="string", nullable=true) */
    protected $location_name;

    /** @Column(type="string", nullable=true) */
    protected $street_one;

    /** @Column(type="string", nullable=true) */
    protected $street_two;

    /** @Column(type="string", nullable=true) */
    protected $city;

    /** @Column(type="string", nullable=true) */
    protected $state;

    /** @Column(type="string", nullable=true) */
    protected $postal_code;

    /** @Column(type="string", nullable=true) */
    protected $contact_name;

    /** @Column(type="string", nullable=true) */
    protected $contact_phone;

    /** @Column(type="string", nullable=true) */
    protected $contact_email;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="events")
     *
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @var array
     *
     * @OneToMany(targetEntity="Reservation", mappedBy="event", orphanRemoval=true)
     */
    protected $reservations;

    /**
     * @OneToMany(targetEntity="EventRecurrence", mappedBy="event", cascade={"remove"})
     */
    protected $event_recurrences;

    /**
     * Event constructor.
     */
    public function __construct()
    {
        $this->frequency = static::FREQUENCY_DAILY;
        $this->interval = 1;
    }

    /**
     * @return array
     */
    public static function getFrequencies()
    {
        return [
            static::FREQUENCY_DAILY,
            static::FREQUENCY_WEEKLY,
            static::FREQUENCY_MONTHLY,
            static::FREQUENCY_YEARLY,
        ];
    }

    /**
     * @return array
     */
    public static function getMonths()
    {
        return [
            static::MONTH_JAN => 'Jan',
            static::MONTH_FEB => 'Feb',
            static::MONTH_MAR => 'Mar',
            static::MONTH_APR => 'Apr',
            static::MONTH_MAY => 'May',
            static::MONTH_JUN => 'Jun',
            static::MONTH_JUL => 'Jul',
            static::MONTH_AUG => 'Aug',
            static::MONTH_SEP => 'Sep',
            static::MONTH_OCT => 'Oct',
            static::MONTH_NOV => 'Nov',
            static::MONTH_DEC => 'Dec',
        ];
    }

    /**
     * @return array
     */
    public static function getWeekDays()
    {
        return [
            static::WEEKDAY_SUN => 'Sunday',
            static::WEEKDAY_MON => 'Monday',
            static::WEEKDAY_TUES => 'Tuesday',
            static::WEEKDAY_WED => 'Wednesday',
            static::WEEKDAY_THURS => 'Thursday',
            static::WEEKDAY_FRI => 'Friday',
            static::WEEKDAY_SAT => 'Saturday',
        ];
    }

    /**
     * @return string
     */
    public function getFullAddress()
    {
        return "{$this->street_one} {$this->street_two}, {$this->city}, {$this->state} {$this->postal_code}";
    }

    /**
     * Determines if the event is recurring.
     *
     * @return bool
     */
    public function isRecurring()
    {
        return $this->max_recurrences > 1;
    }

    /**
     * @return mixed
     *
     * @throws ValidateException
     */
    public static function getWeekDayFromInteger($integer)
    {
        if ($integer < 0 || $integer > 6) {
            throw new ValidateException('Integer must be in range of 0 to 6 inclusive.');
        }

        $days = static::getWeekDays();

        return  $days[$integer];
    }

    /**
     * Determines if the event is an occurs all day.
     *
     * @return bool
     */
    public function isAllDay()
    {
        return is_null($this->start_time) && is_null($this->end_time);
    }

    /**
     * @PrePersist
     *
     * @PreUpdate
     *
     * @throws \sacore\application\ValidateException
     */
    public function persistCallback()
    {
        $this->validate();

        $this->start_date->setTimeZone(new DateTimeZone('UTC'));
        $this->end_date->setTimeZone(new DateTimeZone('UTC'));

        if (! empty($this->start_time)) {
            $this->start_time->setTimeZone(new DateTimeZone('UTC'));
        }

        if (! empty($this->end_time)) {
            $this->end_time->setTimeZone(new DateTimeZone('UTC'));
        }
    }

    public function validate()
    {
        $this->validateGeneralInformation();
        $this->validateRecurrenceRules();
        $this->validateLocationInformation();
        $this->validateContactInformation();
    }

    /**
     * @throws \sacore\application\ValidateException
     */
    protected function validateGeneralInformation()
    {
        if (empty($this->name)) {
            throw new ValidateException('Please enter a name for the event.');
        }

        if (! empty($this->link) && filter_var($this->link, FILTER_VALIDATE_URL) === false) {
            throw new ValidateException('The link you entered isn\'t valid.');
        }

        if (empty($this->category)) {
            throw new ValidateException('Please select a category.');
        }
    }

    /**
     * @throws \sacore\application\ValidateException
     */
    protected function validateRecurrenceRules()
    {
        if (empty($this->start_date)) {
            throw new ValidateException('Please select a start date.');
        }

        if (empty($this->end_date)) {
            throw new ValidateException('Please select an end date.');
        }

        if ($this->start_date > $this->end_date) {
            throw new ValidateException('End date can not be set before the start date.');
        }

        if (! empty($this->until) && $this->until_date < $this->end_date) {
            throw new ValidateException('Your event is set to repeat until '.$this->until_date->format('m-d-Y').', 
		                                    which is before the end date of your event. Please change the "repeat until" 
		                                    field in your repeat rules to a later date.');
        }

        if (! $this->isAllDay() && empty($this->start_time)) {
            throw new ValidateException('Please enter an start time.');
        }

        if (! $this->isAllDay() && empty($this->end_time)) {
            throw new ValidateException('Please enter a end time.');
        }

        if ($this->isRecurring() && ! is_numeric($this->interval) || $this->interval < 1) {
            throw new ValidateException('Recurrence interval must be a numeric value larger than 1.');
        }

        if ($this->isRecurring() && ! in_array($this->frequency, static::getFrequencies())) {
            throw new ValidateException('Invalid frequency: Must be daily, weekly, monthly, or yearly.');
        }

        if ($this->isRecurring() && in_array($this->frequency, [Event::FREQUENCY_DAILY, Event::FREQUENCY_WEEKLY])
            && $this->start_date->format('m/d/Y') != $this->end_date->format('m/d/Y')) {
            throw new ValidateException('Events that repeat daily or weekly must start and end on the same day.');
        }

        if ($this->isRecurring() && $this->frequency == Event::FREQUENCY_WEEKLY) {
            $reoccurrencedays = $this->getRecurrenceDays();
            if (empty($reoccurrencedays)) {
                throw new ValidateException('You have set your event to repeat weekly. Please select 
                                        on which days you would like them to repeat (Mon, Tues, Wed, etc.).');
            }

            $startDateDay = $this->start_date->format('l');
            $weekDayIndex = array_search($startDateDay, $this::getWeekDays());

            if ($weekDayIndex === false || ! in_array($weekDayIndex, $reoccurrencedays)) {
                throw new ValidateException('For events that repeat weekly, your start date must be on one of the days
                                         of the week you selected.');
            }
        }
    }

    /**
     * @throws \sacore\application\ValidateException
     */
    protected function validateLocationInformation()
    {
        /*
         * If one or more of the address fields are not empty, all other
         * address fields are required.
         */
        if (! empty($this->street_one)
            || ! empty($this->street_two)
            || ! empty($this->city)
            || ! empty($this->state)
            || ! empty($this->postal_code)) {
            if (empty($this->street_one)) {
                throw new ValidateException('Please enter a street name.');
            }

            if (empty($this->city)) {
                throw new ValidateException('Please enter a city.');
            }

            if (empty($this->postal_code) || ! is_numeric($this->postal_code) || strlen($this->postal_code) != 5) {
                throw new ValidateException('Please enter a valid postal code.');
            }

            if (empty($this->state)) {
                throw new ValidateException('Please select a state.');
            }
        }
    }

    /**
     * @throws \sacore\application\ValidateException
     */
    protected function validateContactInformation()
    {
        if (! empty($this->contact_email) && filter_var($this->contact_email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidateException('The email you entered is invalid.');
        }

        $phone_regex = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';
        if (! empty($this->contact_phone) && ! preg_match($phone_regex, $this->contact_phone)) {
            throw new ValidateException('The phone number you entered is invalid. Must be in the format (xxx)xxx-xxx');
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
     * @return Event
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
     * Set startTime
     *
     * @param  DateTime  $startTime
     * @return Event
     */
    public function setStartTime($startTime)
    {
        $this->start_time = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        if ($this->start_time) {
//            $start = new DateTime($this->start_time->format('h:i A'), new DateTimeZone('UTC'));
            if ($this->timezone) {
                $this->start_time->setTimeZone($this->timezone);
            }

            return $this->start_time;
        }

        return null;
    }

    /**
     * Set endTime
     *
     * @param  DateTime  $endTime
     * @return Event
     */
    public function setEndTime($endTime)
    {
        $this->end_time = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        if ($this->end_time) {
//            $end = new DateTime($this->end_time->format('h:i A'), new DateTimeZone('UTC'));

            $this->end_time->setTimeZone($this->timezone);

            return $this->end_time;
        }

        return null;
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
     * Set startDate
     *
     * @param  \DateTime  $startDate
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
//        if(!empty($this->start_date) && !empty($this->getTimezone())) {
//            $this->start_date->setTimeZone(new DateTimeZone($this->getTimezone()));
//        }

        return $this->start_date;
    }

    /**
     * Set endDate
     *
     * @param  \DateTime  $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
//        if(!empty($this->end_date) && !empty($this->getTimezone())) {
//            $this->end_date->setTimeZone(new DateTimeZone($this->getTimezone()));
//        }

        return $this->end_date;
    }

    /**
     * Set category
     *
     * @param  Category  $category
     * @return Event
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set recurrenceRules
     *
     * @param  string  $recurrenceRules
     * @return Event
     */
    public function setRecurrenceRules($recurrenceRules)
    {
        $this->recurrence_rules = $recurrenceRules;

        return $this;
    }

    /**
     * Get recurrenceRules
     *
     * @return string
     */
    public function getRecurrenceRules()
    {
        return $this->recurrence_rules;
    }

    /**
     * Set excludeRules
     *
     * @param  string  $excludeRules
     * @return Event
     */
    public function setExcludeRules($excludeRules)
    {
        $this->exclude_rules = $excludeRules;

        return $this;
    }

    /**
     * Get excludeRules
     *
     * @return string
     */
    public function getExcludeRules()
    {
        return $this->exclude_rules;
    }

    /**
     * Set frequency
     *
     * @param  string  $frequency
     * @return Event
     */
    public function setFrequency($frequency)
    {
        $this->frequency = strtoupper($frequency);

        return $this;
    }

    /**
     * Get frequency
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * Set interval
     *
     * @param  string  $interval
     * @return Event
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Get interval
     *
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set description
     *
     * @param  string  $description
     * @return Event
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

    /**
     * Returns Event object's properties in an array.
     *
     * @return array
     */
    public function toArray()
    {
        $timezone = new DateTimeZone($this->getTimezone());
        $startDate = new \DateTime($this->getStartDate()->format('Y-m-d'), $timezone);
        $endDate = new \DateTime($this->getEndDate()->format('Y-m-d'), $timezone);

        if ($this->start_time != null && $this->end_time != null) {
            $startTime = new \DateTime($this->getStartTime()->format('Y-m-d H:i'), new DateTimeZone('UTC'));
            $endTime = new \DateTime($this->getEndTime()->format('Y-m-d H:i'), new DateTimeZone('UTC'));

            $startTime->setTimezone($timezone);
            $endTime->setTimezone($timezone);
        } else {
            $startTime = null;
            $endTime = null;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,

            'start_date' => $startDate,
            'start_time' => $startTime,
            'end_date' => $endDate,
            'end_time' => $endTime,
            'timezone' => $this->timezone,
            'frequency' => $this->frequency,
            'interval' => $this->interval,
            'exclude_rules' => $this->exclude_rules,
            'recurrence_rules' => $this->recurrence_rules,

            'location_name' => $this->location_name,
            'street_one' => $this->street_one,
            'street_two' => $this->street_two,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,

            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
        ];
    }

    /**
     * Set recurrenceMonths
     *
     *
     * @return Event
     */
    public function setRecurrenceMonths(array $recurrenceMonths = [])
    {
        $this->recurrence_months = (is_array($recurrenceMonths)) ? implode(',', $recurrenceMonths) : '';

        return $this;
    }

    /**
     * Get recurrenceMonths
     *
     * @return string
     */
    public function getRecurrenceMonths()
    {
        return (! empty($this->recurrence_months)) ? explode(',', $this->recurrence_months) : [];
    }

    /**
     * Set recurrenceDays
     *
     *
     * @return Event
     */
    public function setRecurrenceDays(array $recurrenceDays = [])
    {
        $this->recurrence_days = (is_array($recurrenceDays)) ? implode(',', $recurrenceDays) : '';

        return $this;
    }

    /**
     * Get recurrenceDays
     *
     * @return string
     */
    public function getRecurrenceDays()
    {
        return (! empty($this->recurrence_days)) ? explode(',', $this->recurrence_days) : [];
    }

    /**
     * Set untilDate
     *
     * @param  DateTime  $untilDate
     * @return Event
     */
    public function setUntilDate($untilDate)
    {
        $this->until_date = $untilDate;

        return $this;
    }

    /**
     * Get untilDate
     *
     * @return \DateTime
     */
    public function getUntilDate()
    {
        return $this->until_date;
    }

    /**
     * @return int
     */
    public function getMaxRecurrences()
    {
        return $this->max_recurrences;
    }

    /**
     * @param  int  $max_recurrences
     * @return $this
     */
    public function setMaxRecurrences($max_recurrences)
    {
        $this->max_recurrences = $max_recurrences;

        return $this;
    }

    /**
     * Add reservation
     *
     *
     * @return Event
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

    public function getEventRecurrences()
    {
        return $this->event_recurrences;
    }

    public function addEventRecurrence($event_recurrence)
    {
        $this->event_recurrences[] = $event_recurrence;

        return $this;
    }

    public function removeEventRecurrence($event_recurrence)
    {
        $this->event_recurrences->removeElement($event_recurrence);
    }
}
