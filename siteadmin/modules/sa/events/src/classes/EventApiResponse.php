<?php

namespace sa\events;

use sacore\application\app;

class EventApiResponse
{
    /** @var array */
    protected $eventsRecurrences;

    public function __construct(array $eventRecurrences = [])
    {
        $this->eventsRecurrences = $eventRecurrences;
    }

    /**
     * Returns event recurrences in an array.
     *
     * @return array
     */
    public function response()
    {
        $events = [];

        if (count($this->eventsRecurrences) > 0) {
            /** @var EventRecurrence $recurrence */
            foreach ($this->eventsRecurrences as $recurrence) {
                $eventId = $recurrence->getEvent()->getId();

                if (! array_key_exists($eventId, $events)) {
                    $events[$eventId] = $recurrence->getEvent()->toArray();
                    $events[$eventId]['url'] = app::get()->getRouter()->getGenerator('events_single', ['id' => $eventId]);
                }

                $recurrenceArray = $recurrence->toArray();
                $recurrenceArray['url'] = app::get()->getRouter()->getGenerator('event_single_recurrence', ['id' => $eventId, 'recurrenceId' => $recurrenceArray['id']]);

                unset($recurrenceArray['event']);
                $events[$recurrence->getEvent()->getId()]['recurrences'][] = $recurrenceArray;
            }
        }

        return $events;
    }

    /**
     * @return array
     */
    public function getEventsRecurrences()
    {
        return $this->eventsRecurrences;
    }

    /**
     * @param  array  $eventsRecurrences
     */
    public function setEventsRecurrences($eventsRecurrences)
    {
        $this->eventsRecurrences = $eventsRecurrences;
    }

    public function addEventRecurrence(EventRecurrence $recurrence)
    {
        $this->eventsRecurrences[] = $recurrence;
    }
}
