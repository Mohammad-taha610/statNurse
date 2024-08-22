<?php

namespace nst\events;

use sacore\application\DateTime;

/** This is a convenience class to easily manage shift times in service classes
 *  It is meant to be instantiated and passed a data array with times to generate values
 */
class ShiftTimes
{
    /**
     * @var DateTime $start
     */
    protected $start;

    /**
     * @var DateTime $end
     */
    protected $end;

    /**
     * @var DateTime
     */
    protected $start_date;

    /**
     * @var DateTime
     */
    protected $end_date;

    /**
     * @var DateTime
     */
    protected $start_time;

    /**
     * @var DateTime
     */
    protected $end_time;

    /**
     * 
     */
    public function __construct($data = null)
    {
        if (!is_array($data)) {
            return;
        }
        $this->generateByArray($data);
    }

    /**
     * 
     */
    public function generateByArray($data)
    {
        $this->setStartTime(new DateTime($data['start_time']))
        ->setStartDate(new DateTime($data['start_time']))
        ->setEndTime(new DateTime($data['end_time']))
        ->setEndDate(new DateTime($data['start_date']));
        if ($this->end_time < $this->start_time) {
            $this->setEndDate($this->getEndDate()->modify('+24 hours'));
        }
        $this->setStart(new DateTime($data['start_date'] . ' ' . $data['start_time']))
        ->setEnd(new DateTime($this->getEndDate()->format('Y-m-d') . ' ' . $data['end_time']));
    }

    /**
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * @param DateTime $start_time
     * @return ShiftTimes
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * @param DateTime $end_time
     * @return ShiftTimes
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
        return $this;
    }
    
    /**
     * @return DateTime
     */
    public function getStart()
    {
        // This is to be consistent with Event->getStartTime()
        return $this->start;
    }

    /**
     * @param DateTime $start
     * @return ShiftTimes
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        // This is to be consistent with Event->getEndTime()
        return $this->end;
    }

    /**
     * @param DateTime $end
     * @return ShiftTimes
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }
    
    /**
     * @param \DateTime $startDate
     *
     * @return ShiftTimes
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }
    /**
     * @param \DateTime $endDate
     *
     * @return ShiftTimes
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }
}
