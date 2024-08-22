<?php

namespace nst\events;

use Doctrine\ORM\QueryBuilder;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\events\Event;
use sa\events\EventRecurrenceRepository;
use sa\member\auth;
use sacore\utilities\doctrineUtils;

/**
 * Class ShiftRepository
 * @package nst\events
 */
class ShiftRepository extends \sa\events\EventRepository
{

    /**
     * @param $request
     * @return array
     */
    public function getPaginatedShifts($request)
    {
        $perPage = 20;

        $fieldsToSearch = array();
        foreach($request->query->all() as $field=>$value)
        {
            if($field == 'q_per_page'){
                $perPage = intval($value);
            }
            elseif (strpos($field, 'q_')===0 && !empty($value))
            {
                $fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
            }
        }

        $currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
        $sort = !empty($request->get('sort')) ? $request->get('sort') : false;
        $sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;
        $totalRecords = $this->search($fieldsToSearch, null, null, null, true);
        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalPages = ceil($totalRecords / $perPage);

        $shifts = $this->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));

        return [$shifts, $totalPages, $totalRecords, $currentPage, $perPage];
    }

    /**
     * Get all shifts between a start date and end date (inclusive)
     * @param array $start
     * @param array $end
     * @return array
     */
    public function getShiftsBetweenInclusive($start, $end, $provider = null, $all = false)
    {
        $member = auth::getAuthMember();
        if (!$provider) {
            $provider = $member->getProvider();
        }

        $q = $this->createQueryBuilder('e')
            ->leftJoin('e.nurse', 'n')
            ->leftJoin('n.member', 'm')
            ->select('e')
            ->addSelect('n')
            ->addSelect('m')
            ->where('e.provider = :provider');

        if(!$all) {
            $q->andWhere('e.end_date >= :start')
                ->andWhere('e.start_date <= :end')
                ->setParameter(':start', $start['date'])
                ->setParameter(':end', $end['date']);
        }

        $q->setParameter(':provider', $provider);


        return $q->getQuery()->getArrayResult();
    }


    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\ModRequestAuthenticationException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \sacore\application\IocException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Exception
     */
    public function getEventsAndRecurrencesBetweenDates(DateTime $dateTime1, DateTime $dateTime2, $category = null, $backend = false, $providerId = null, $nurseId = null, $nurseType = null, $mobile = false, $calendar_type = null){
        $qb = $this->createQueryBuilder('e');

        $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();
        $provider = null;
        if(!$backend && !$mobile) {
            $member = auth::getAuthMember();
            $provider = $member->getProvider();
        } elseif($providerId) {
            $provider = ioc::get('Provider', ['id' => $providerId]);
        }
        // Find all shifts in the given range, or find recurring shifts that have started before the current date
        $qb->select('e')
            ->where($qb
                    ->expr()->andX(
                        $qb->expr()->gte('e.start_date', ':firstDate'),
                        $qb->expr()->lte('e.start_date', ':endDate')
                    )
                . ' OR ' .
                $qb->expr()->andX(
                    $qb->expr()->lte('e.start_date', ':firstDate'),
                    $qb->expr()->gte('e.until_date', ':firstDate')
                )
                . ' OR ' .
                $qb->expr()->andX(
                    $qb->expr()->andX(
                        $qb->expr()->lte('e.start_date', ':firstDate'),
                        $qb->expr()->isNull('e.until_date')
                    ),
                    $qb->expr()->orX(
                        $qb->expr()->eq('e.recurrence_type', "'Daily'"),
                        $qb->expr()->eq('e.recurrence_type', "'Weekly'")
                    )
                )
            );
        if($provider) {
            $qb->andWhere('e.provider = :provider')
                ->setParameter(':provider', $provider);
        }
        $qb
            ->setParameter('firstDate', $dateTime1->format('Ymd'))
            ->setParameter('endDate', $dateTime2->format('Ymd'))
            ->orderBy('e.start_date', 'ASC');

        if($category) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $category);
        }

        if($nurseType) {
            $qb->andWhere('e.nurse_type = :nurse_type')
                ->setParameter('nurse_type', $nurseType);
        }


        $results = $qb->getQuery()->getResult();
        $auth = modRequest::request('auth.object');

        $member = null;
        $user = null;

        if ($auth) {
            $member = $auth->getAuthMember();
            $user = $auth->getAuthUser();
        }


        /** @var EventRecurrenceRepository $recurrenceRepository */
        $recurrenceRepository = app::$entityManager->getRepository(ioc::staticResolve('EventRecurrence'));

        $monthEvents = [];
        $events = [];
        /** @var Shift $event */
        foreach ($results as $event){

            //The offset is zero because these set recurrences should all be included first
            $recurrences = $recurrenceRepository->findByEvent($event);

            $recurrenceData = [];
            $createdRecurrences = [];
            foreach($recurrences as $recurrence) {

                $eventRecurrence = [];
                if($calendar_type == 'month') {
                    $eventRecurrence['nurse_id'] = $recurrence['nurse'] ? $recurrence['nurse']['id'] : '';
                    $eventRecurrence['unique_id'] = $recurrence['recurrenceUniqueId'];
                }
                $start_date_unique_id = $recurrence['start']->format('mdY');
                $end_date_unique_id = $recurrence['end']->format('mdY');
                $uniqueId = $event->getId() . '-' . $start_date_unique_id;
                $parentUniqueId = $event->getParentId() ? $event->getParentId() . '-' . $start_date_unique_id : '';
                $eventRecurrence['start'] = $recurrence['start']->format('Y-m-d\TH:i:s');
                $eventRecurrence['url'] = app::get()->getRouter()->generate($backend ? 'sa_shift_edit' : 'edit_shift',
                    [
                        'id'=> $event->getId(),
                        'recurrenceId' => $recurrence['id'],
                        'recurrenceUniqueId' => $uniqueId,
                        'start_date' => $recurrence['start']->format('Ymd'),
                        'end_date' => $recurrence['end']->format('Ymd')
                    ]);
                $eventRecurrence['copy_route'] = app::get()->getRouter()->generate($backend ? 'sa_shift_copy' : 'copy_shift',
                    [
                        'id'=> $event->getId(),
                        'recurrenceId' => $recurrence['id'],
                        'recurrenceUniqueId' => $uniqueId,
                        'start_date' => $recurrence['start']->format('Ymd'),
                        'end_date' => $recurrence['end']->format('Ymd')
                    ]);
                $eventRecurrence['end'] = $recurrence['end']->format('Y-m-d\TH:i:s');
                $eventRecurrence['recurrenceUniqueId'] = $uniqueId;
                $eventRecurrence['groupId'] = $event->getId();
                $eventRecurrence['parent_id'] = $event->getParentId();
                $eventRecurrence['parent_unique_id'] = $parentUniqueId;
                $eventRecurrence['is_recurrence'] = true;
//                $singleArray['recurrenceId'] = $recurrence['id'];

                $startTime = new DateTime($recurrence['start'], $timezone);
                $endTime = new DateTime($recurrence['end'], $timezone);
                $eventRecurrence['exists'] = $recurrence['recurrenceExists'];
                $eventRecurrence['unique_id'] = $recurrence['recurrenceUniqueId'];
                $eventRecurrence['id'] = $recurrence['id'];
                $eventRecurrence['name'] = $recurrence['name'];
                $eventRecurrence['status'] = $recurrence['status'];
                $eventRecurrence['nurse_type'] = $recurrence['nurse_type'];
                $eventRecurrence['start_time_formatted'] = $startTime->format('g:i a');
                $eventRecurrence['end_time_formatted'] = $endTime->format('g:i a');
                $eventRecurrence['start_date'] = $startTime->format('Y-m-d');
                $eventRecurrence['start_time'] = $startTime->format('H:i:s');
                $eventRecurrence['end_date'] = $recurrence['end_date_enabled'] ? $endTime->format('Y-m-d') : $startTime->format('Y-m-d');
                $eventRecurrence['end_time'] = $endTime->format('H:i:s');
                $eventRecurrence['nurse_name'] = $recurrence['nurse'] ? $recurrence['nurse']['member']['first_name'] . ' ' . $recurrence['nurse']['member']['last_name'] : '';
                $eventRecurrence['nurse_route'] = $recurrence['nurse'] ? app::get()->getRouter()->generate('nurse_profile', ['id' => $recurrence['nurse']['id']]) : '';
                $eventRecurrence['nurse_type'] = $recurrence['nurse_type'];
                $eventRecurrence['nurse_id'] = $recurrence['nurse'] ? $recurrence['nurse']['id'] : '';
                $eventRecurrence['member_id'] = $recurrence['nurse'] ? $recurrence['nurse']['member']['id'] : '';
                $eventRecurrence['provider_id'] = $event->getProvider()->getId();
                $eventRecurrence['provider_name'] = $event->getProvider()->getMember()->getCompany();
                $eventRecurrence['is_covid'] = $recurrence['is_covid'];
                $eventRecurrence['incentive'] = $recurrence['incentive'];
                $eventRecurrence['bonus_amount'] = $recurrence['bonus_amount'];
                $eventRecurrence['category_id'] = $event->getCategory()->getId();
                $eventRecurrence['category_name'] = $event->getCategory()->getName();
                //Only add to this array so that we can use Recur/Rule to order the recurrences
                $createdRecurrences[$recurrence['recurrenceUniqueId']] = $eventRecurrence;
            }


            if((!$event->getCategory()) || (!$event->getCategory()->hasPermissionToViewEvent($member, $user))) {
                continue;
            }
            $rruleString = $event->getRecurrenceRules();
            $startDate = $event->getStartDate();
            $endDate = $event->getEndDate();
            $untilDate = $event->getUntilDate();
            $maxRecurrences = $event->getMaxRecurrences();

            if(!is_null($untilDate)) {
                $setUntilDate = ($untilDate < $dateTime2) ? $untilDate : $dateTime2;
            }
            else{
                $setUntilDate = $dateTime2;
            }

            if(!is_null($maxRecurrences)  && $maxRecurrences != 0){
                $rrule = new Rule($rruleString, $startDate, $endDate);
                $transformer = new ArrayTransformer();
            }
            else {
                //Currently as the date range increases this, and recurrences that have an extremely long until date will become slow to generate
                //If we need to fix that at some point, the issue is in this class and we would need to clone it and change it or scrap it to fix it
                $rrule = new Rule($rruleString, $startDate, $endDate);
                //May want to move this up to the normal one at some point but idk what the reasonable limit would be for it
                $config = new ArrayTransformerConfig();
                $config->setVirtualLimit(Event::LARGE_NUMBER);
                $transformer = new ArrayTransformer($config);
            }
            //Only show times that are before the later date
            $rrule->setUntil($setUntilDate);
            $times = $transformer->transform($rrule);
            $count = 0;
            foreach($times as $time){

                //This might cause missing out on the real recurrences, but I kind of doubt it. Should look at this first if that is the case though
                if(!is_null($maxRecurrences)  && $maxRecurrences != 0) {
                    if ($count >= $maxRecurrences && $maxRecurrences != 0) {
                        break;
                    }
                    $count++;
                }
                if($time->getEnd() < $dateTime1 && $time->getEnd()->format('$d') != $dateTime1->format('$d')){
                    continue;
                }

                $start_date_unique_id = $time->getStart()->format('mdY');
                $uniqueId = $event->getId() . '-' . $start_date_unique_id;
                $parentUniqueId = $event->getParentId() ? $event->getParentId() . '-' . $start_date_unique_id : '';

                $monthDay = $time->getStart()->format('Y-m-d');

                if($calendar_type == 'month') {
                    $monthEvents[$monthDay][$event->getStatus()] = $this->getShiftsCountForToday($time->getStart(), $event->getStatus(), $event->getProvider());
                    continue;
                }

                if($nurseId && (!$event->getNurse() || $event->getNurse()->getId() != $nurseId)) {
                    continue;
                }

                $eventRecurrence = [];
                $eventRecurrence['start'] = $time->getStart()->format('Y-m-d') . 'T' . $event->getStart()->format('H:i:s');
                $eventRecurrence['url'] = app::get()->getRouter()->generate($backend ? 'sa_shift_edit' : 'edit_shift',
                    [
                        'id'=> $event->getId(),
                        'recurrenceId' => 0,
                        'recurrenceUniqueId' => $uniqueId,
                        'start_date' => $time->getStart()->format('Ymd'),
                        'end_date' => $time->getEnd()->format('Ymd')
                    ]);
                $eventRecurrence['copy_route'] = app::get()->getRouter()->generate($backend ? 'sa_shift_copy' : 'copy_shift',
                    [
                        'id'=> $event->getId(),
                        'recurrenceId' => 0,
                        'recurrenceUniqueId' => $uniqueId,
                        'start_date' => $time->getStart()->format('Ymd'),
                        'end_date' => $time->getEnd()->format('Ymd')
                    ]);
                $eventRecurrence['end'] = $time->getEnd()->format('Y-m-d') . 'T' . $event->getEnd()->format('H:i:s');
                $eventRecurrence['recurrenceUniqueId'] = $uniqueId;
                $eventRecurrence['parent_unique_id'] = $parentUniqueId;
//                $events[] = ['title' => $event->getName(), 'group_id' => $event->getId(), 'url' => $eventRecurrence['url'],
//                    'start' => $eventRecurrence['start'], 'end' => $eventRecurrence['end']];
                //On Review not sure we want group id
                $events[] = [
                    'title' => $event->getName(),
                    'event_id' => $event->getId(),
                    'parent_id' => $event->getParentId(),
                    'is_recurrence' => $event->getRecurrenceType() != 'None',
                    'nurse_type' => $event->getNurseType(),
                    'url' => $eventRecurrence['url'],
                    'start' => $eventRecurrence['start'],
                    'end' => $eventRecurrence['end'],
                    'unique_id' => $eventRecurrence['recurrenceUniqueId'],
                    'parent_unique_id' => $eventRecurrence['parent_unique_id'],
                    'copy_route' => $eventRecurrence['copy_route'],
                    'category_id' => $event->getCategory()->getId(),
                ];
            }
        }
        if($calendar_type == 'month') {
            return $monthEvents;
        }

        return $events;
    }

    /**
     * @throws \Exception
     */
    public function getShiftsCountForToday($date, $status, $provider = null, $nurse = null, $category = null, $nurseType = null, $all = null): int
    {
        $start = new DateTime($date->format('Y-m-d') . ' 00:00:00', app::getInstance()->getTimeZone());
        $end = new DateTime($date->format('Y-m-d') . ' 23:59:59', app::getInstance()->getTimeZone());

        $q1 = $this->createQueryBuilder('s')
            ->select('count(s)')
            ->leftJoin('s.provider', 'p')
            ->andWhere('s.start BETWEEN :start AND :end')
            ->andWhere('s.status = :status')
            ->setParameter(':start', $start)
            ->setParameter(':end', $end)
            ->setParameter(':status', $status);

            //When viewing the calendar in siteadmin, need to add this block
            if (!$all) {
                if($provider){
                    $q1->andWhere('p.id = :providerId')
                    ->setParameter(':providerId', $provider->getId());
                }
            }

            if($nurse){
                $q1->andWhere('s.nurse = :nurse')
                    ->setParameter(':nurse', $nurse);
            }
            if($category){
                $q1->andWhere('s.category = :category')
                    ->setParameter(':category', $category);
            }
            if($nurseType){
                $q1->andWhere('s.nurse_type = :nurseType')
                    ->setParameter(':nurseType', $nurseType);
            }

        return $q1->getQuery()->getSingleScalarResult();
    }
    
    public function providerShiftsInTimeFrame($startDate, $endDate, $provider)
    {
        $start = new DateTime($startDate . ' 00:00:00', app::getInstance()->getTimeZone());
        $end = new DateTime($endDate . ' 23:59:59', app::getInstance()->getTimeZone());
        // $start and $end expect "Y-m-d"
        $q1 = $this->createQueryBuilder('s')
        ->select('s.id')
        ->leftJoin('s.provider', 'n')
        ->andWhere('s.start BETWEEN :start AND :end')
        ->andWhere('n.id = :id')
        ->setParameter(':start', $start)
        ->setParameter(':id', $provider->getId())
        ->setParameter(':end', $end);

        $shiftData = [];
        $shiftData = $q1->getQuery()->getResult();

        return $shiftData;
    }

    public function getShiftDataAsArray($id) {
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $q = $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->andWhere('s.provider = :provider')
            ->leftJoin('s.category', 'c')
            ->setParameter(':id', $id)
            ->setParameter(':provider', $provider);

        return $q->getQuery()->getArrayResult();
    }

    public function getShiftsForNurse($nurse, $date) {
        $start = new DateTime($date->format('Y-m-d') . ' 00:00:00', app::getInstance()->getTimeZone());
        $end = new DateTime($date->format('Y-m-d') . ' 23:59:59', app::getInstance()->getTimeZone());

        $q1 = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.nurse', 'n')
            ->andWhere('s.start BETWEEN :start AND :end')
            ->andWhere('n.id = :id')
            ->andWhere("s.status != 'Open'")
            ->setParameter(':start', $start)
            ->setParameter(':id', $nurse->getId())
            ->setParameter(':end', $end);

        $shiftData = [];
        $shiftData['shifts'] = $q1->getQuery()->getResult();
        $shiftData['all'] = $q1->getQuery()->getResult();

        return $shiftData;
    }

    public function findCurrentShift($nurse) {
        $timezone = app::getInstance()->getTimeZone();

        // Try to find a shift that has started but not finished
        $time = new DateTime('now', $timezone);
        /** @var Shift $shift */
        $shift = static::findStartedShifts($nurse, $time);
        if($shift && !$shift->getClockOutTime()) {
            return $shift;
        }

        // Find a shift that they have not clocked o\

        // If we get here, try looking for a shift within 3 hours of now
        $shift = static::findShiftsComingUpSoon($nurse, $time);
        if ($shift) {
            return $shift;
        }

        // If one still hasn't been found, look for the closest shift within the last 24 hours
//        $last24Hours = clone $time;
//        $last24Hours->modify('-24 hours');
//        $shift = static::findStartedShifts($nurse, $last24Hours, true);

        // If we get here, it is what it is
        return $shift;
    }

    public function findShiftsComingUpSoon($nurse, $time) {
        $now = clone $time;
        $timePlusX = clone $time;
        $hours = app::get()->getConfiguration()->get('nurse_clockin_window')->getValue();
        if(is_numeric($hours) && (int)$hours > 0){
            $timePlusX->modify("+$hours hours");
        } else {
            // Default to 1 hour, just in case we have bad data
            $timePlusX->modify("+1 hours");            
        }

        $q1 = $this->createQueryBuilder('s')
            ->leftJoin('s.nurse', 'n')
            ->select('s')
            ->where('s.start BETWEEN :now AND :inXHours')
            ->andWhere('s.status = :approved')
            ->andWhere('s.nurse = :nurse')
            ->setParameter('approved', 'Approved')
            ->setParameter('nurse', $nurse)
            ->setParameter('inXHours', $timePlusX)
            ->setParameter('now', $now);

        $shifts = $q1->getQuery()->getResult();

        // If there is a shift or a recurrence it'll be in the first position
        if($shifts) {
            return $shifts[0];
        }

        return null;
    }

    public function findStartedShifts($nurse, $time) {
        $sixHoursAgo = clone $time;
        $sixHoursAgo->modify('-6 hours');
        $q1 = $this->createQueryBuilder('s')
            ->leftJoin('s.nurse', 'n')
            ->select('s')
            ->where('s.start < :now')
            ->andWhere('s.nurse = :nurse')
            ->andWhere('s.status = :approved')
            ->setParameter('approved', 'Approved')
            ->setParameter('nurse', $nurse)
            ->andWhere('s.end > :sixHoursAgo')
            ->setParameter('now', $time)
            ->setParameter('sixHoursAgo', $sixHoursAgo);

        $shifts = $q1->getQuery()->getResult();

        // Catch errors
        /*if($shifts) {
            throw new Exception('There is both a shift and recurrence started right now');
        }
        if(count($shifts) > 1) {
            throw new Exception('There is more than one shift started right now');
        }*/

        // If there is a shift or a recurrence it'll be in the first position
        if($shifts) {
            return $shifts[0];
        }

        return null;
    }

    public function getAutomaticClockOutShifts() {
        $timezone = app::getInstance()->getTimeZone();
        $time = new DateTime('now', $timezone);

        $sixHoursAgo = clone $time;
        $sixHoursAgo->modify('-6 hours');

        $start = clone $time;
        $start->modify('-36 hours');
        $q1 = $this->createQueryBuilder('s')
            ->select('s')
            ->where('s.status = :approved')
            ->andWhere('s.end BETWEEN :start AND :sixHoursAgo')
            ->andWhere('s.clock_out_time IS NULL')
            ->andWhere('s.clock_in_time IS NOT NULL')
            ->setParameter('approved', 'Approved')
            ->setParameter('sixHoursAgo', $sixHoursAgo)
            ->setParameter('start', $start);

        $shiftData = [];
        $shiftData['shifts'] = $q1->getQuery()->getResult();
        $shiftData['all'] = $q1->getQuery()->getResult();

        // Added to correct clocking out nightshifts incorrectly
        $startTimestamp = $start->getTimestamp();
        $sixHoursAgoTimestamp = $sixHoursAgo->getTimestamp();
        file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', 'startTimeStamp: ' .  $start->format('Y-m-d H:i:s') . PHP_EOL .
            'sixHoursAgoTimestamp: ' . $sixHoursAgo->format('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);

        foreach($shiftData['all'] as $key => $shift){
            $shiftEnd = new DateTime($shift->getEnd());
            $shiftStart = new Datetime($shift->getStart());
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', 'Shift ID: ' . $shift->getId() . ' is recurrence?: ' . ($shift->getIsRecurrence() ? 1 : 0) . PHP_EOL . '   Shift Start: ' .  $shiftStart->format('Y-m-d H:i:s') . PHP_EOL .
                '   Shift End: ' . $shiftEnd->format('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);

            if ($shiftEnd->getTimestamp() <= $shiftStart->getTimestamp()){
                $shiftEnd->modify('+1 day');
                // Welcome to Marvel's 'What if?'
                if($shiftEnd->getTimestamp() <= $shiftStart->getTimestamp()){
                    // What if a shift end time was 2 days before the start time?
                    $shiftEnd->modify('+1 day');
                }
                $shift->setEnd($shiftEnd);
                file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', '   Shift end before start, adding one day. New shift end time: ' . $shiftEnd->format('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
            } else {
                continue;
            }

            if($shiftEnd->getTimestamp() >= $startTimestamp && $shiftEnd->getTimestamp() <= $sixHoursAgoTimestamp){
                continue;
            } else {
                file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', '   Incorrectly flagged, unsetting shift: ' . $shift->getId() . PHP_EOL, FILE_APPEND);
                unset($shiftData['all'][$key]);
            }
        }

        return $shiftData;
    }

    public function migrateShiftsToPrimaryNurse($data){
        $q = $this->createQueryBuilder('s')
            ->select('s')
            ->where('s.nurse_id = :duplicateNurseId')
            ->setParameter('duplicateNurseId', $data['duplicateNurseId']);

        return $q->getQuery()->getResult();
    }

    public function getAllShiftsForNurse($nurse) {
        $q1 = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.nurse', 'n')
            ->andWhere('n.id = :id')
            ->setParameter(':id', $nurse->getId());

        $recurrenceRepo = ioc::getRepository('ShiftRecurrence');
        $q2 = $recurrenceRepo->createQueryBuilder('sr')
            ->select('sr')
            ->leftJoin('sr.nurse', 'n')
            ->andWhere('n.id = :id')
            ->setParameter(':id', $nurse->getId());

        $shiftData = [];
        $shiftData['shifts'] = $q1->getQuery()->getResult();
        $shiftData['all'] = $q1->getQuery()->getResult();
        $shiftData['recurrences'] = $q2->getQuery()->getResult();
        $shiftData['all'] = array_merge($shiftData['all'], $shiftData['recurrences']);

        return $shiftData;
    }

    public function getShiftsByRecurrenceType($recurrence_types, $count = false)
    {
        if (is_array($recurrence_types)) {
            $fields = $recurrence_types;
        } else {
            $fields[] = $recurrence_types;
        }

        $query = $this->createQueryBuilder('s')
            ->select('s')
            ->where('s.recurrence_type IN (:fields)')
            ->setParameter('fields', $fields);

        if ($count){
            $query->select('count(s.id)');
            return $query->getQuery()->getSingleScalarResult();
        } else {
            return $query->getQuery()->getResult();
        }
    }

    /**
     * Get all shifts between dates
     */
    public function getShiftsBetweenDates(
        $start,
        $end,
        $category = null,
        $backend = false,
        $providerId = null,
				$memberId = null,
        $nurseId = null,
        $nurseType = null,
        $mobile = false,
        $calendar_type = null,
        $provider = null,
        $all = false,
        $asArray = false,
        $orderBys = false,
        $statesFilter = false
    )
    {
        $start = new DateTime($start->format('Y-m-d') . ' 00:00:00', app::getInstance()->getTimeZone());
        $end = new DateTime($end->format('Y-m-d') . ' 23:59:59', app::getInstance()->getTimeZone());
        if(!$backend && !$mobile) {
            $member = auth::getAuthMember();
            $provider = $member->getProvider();
        } elseif($providerId) {
            $provider = ioc::get('Provider', ['id' => $providerId]);
        }

        $q = $this->createQueryBuilder('e')
            ->select('e');

        if ($provider) {
            $q->where('e.provider = :provider');
            $q->setParameter(':provider', $provider);
        }

        if($category) {
            $q->andWhere('e.category = :category')
                ->setParameter('category', $category);
        }

        if($nurseType) {
            $q->andWhere('e.nurse_type = :nurse_type')
                ->setParameter(':nurse_type', $nurseType);
        }

        if($nurseId) {
            $nurse = ioc::get('Nurse', ['id' => $nurseId]);
            $q->andWhere('e.nurse = :nurse')
                ->setParameter('nurse', $nurse);
        }
        
        if(!$all) {
            $q->andWhere('e.start BETWEEN :start AND :end')
                ->setParameter(':start', $start)
                ->setParameter(':end', $end);
        }

        if($orderBys){
            foreach ($orderBys as $orderBy){
                $q->addOrderBy('e.'.$orderBy['column'], $orderBy['dir']);
            }
        }
        
                
        if(app::get()->getConfiguration()->get('nurse_states_filter_enabled')->getValue()) {
            if ($statesFilter) {

                $nurse = ioc::get('Nurse', ['member' => $memberId]);
                $statesFilter = $nurse->getStatesAbleToWorkAbbreviated();

								$q->leftJoin('e.provider', 'p');
								$q->andWhere('p.state_abbreviation IN (:states)');
								$q->setParameter(':states', $statesFilter);
            }
        }

        if ($asArray) {
            return $q->getQuery()->getArrayResult();
        }

        return $q->getQuery()->getResult();
    }

    public function getShiftsOnOrAfterToday($count = false)
    {
        $today = new DateTime('now');
        $start = new DateTime($today->format('Y-m-d') . ' 00:00:00');

        $q = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.start > :start')
            ->setParameter(':start', $start);

        if ($count){
            $q->select('count(e.id)');
            return $q->getQuery()->getSingleScalarResult();
        } else {
            return $q->getQuery()->getResult();
        }
    }  

    public function getShiftRequestsForDashboardByProvider($providerId) {
        $now = new Datetime('now');

        $q = $this->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->select('s')
            ->where('p.id = :id')
            ->andWhere('s.status = :pending')
            ->andWhere('s.provider_approved = 0')
            ->andWhere('s.start > :now')
            ->setParameter(':pending', 'Pending')
            ->setParameter(':id', $providerId)
            ->setParameter(':now', $now);

        return $q->getQuery()->getResult();
    }

    public function getShiftStatusesForNurseInMonth($data)
    {
        $q = $this->createQueryBuilder('s');

        $q->leftJoin('s.nurse', 'n')
            ->select('s');

        // Easiest way to do fuzzy matching for both CNA and CMT dynamically was was this if() statement
        // I'm sure there is a better solution, but right now THIS IS IT
        if ($data['nurse_type'] == 'CMT') {
            $q->where(
                $q->expr()->andX(
                    $q->expr()->orX(
                        $q->expr()->andX(
                            $q->expr()->eq('n.id', ':nurse_id'),
                            $q->expr()->in('s.status', ':statuses'),
                        ),
                        $q->expr()->eq('s.status', "'Open'")
                    ),
                    $q->expr()->gte('month(s.start)', 'month(:start)'),
                    $q->expr()->lte('month(s.start)', 'month(:end)'),
                    $q->expr()->gte('day(s.start)', 'day(:start)'),
                    $q->expr()->lte('day(s.start)', 'day(:end)'),
                    $q->expr()->gte('year(s.start)', 'year(:start)'),
                    $q->expr()->lte('year(s.start)', 'year(:end)'),
                    $q->expr()->orX(
                        $q->expr()->like('s.nurse_type', '\'%CNA%\''),
                        $q->expr()->like('s.nurse_type', '\'%CMT%\'')
                    ),
                ),
            );

            $q->setParameters([
                'nurse_id' => $data['nurse_id'],
                'statuses' => $data['statuses'],
                'start' => $data['start'],
                'end' => $data['end']
            ]);
        } else {
            $q->where(
                $q->expr()->andX(
                    $q->expr()->orX(
                        $q->expr()->andX(
                            $q->expr()->eq('n.id', ':nurse_id'),
                            $q->expr()->in('s.status', ':statuses'),
                        ),
                        $q->expr()->eq('s.status', "'Open'")
                    ),
                    $q->expr()->gte('month(s.start)', 'month(:start)'),
                    $q->expr()->lte('month(s.start)', 'month(:end)'),
                    $q->expr()->gte('day(s.start)', 'day(:start)'),
                    $q->expr()->lte('day(s.start)', 'day(:end)'),
                    $q->expr()->gte('year(s.start)', 'year(:start)'),
                    $q->expr()->lte('year(s.start)', 'year(:end)'),
                    $q->expr()->like('s.nurse_type', ':type')
                ),
            );
    
            $q->setParameters([
                'nurse_id' => $data['nurse_id'],
                'statuses' => $data['statuses'],
                'start' => $data['start'],
                'end' => $data['end'],
                'type' => '%'.$data['nurse_type'].'%' //fuzzy
            ]);
        }

        if ($data['nurseStates']) {
            $q->leftJoin('s.provider', 'p');
            $q->andWhere('p.state_abbreviation IN (:states)');
            $q->setParameter(':states', $data['nurseStates']);
        }

        return $q->getQuery()->getResult();
    }

    public function getShiftsForProviderRateUpdates($provider) {
        $now = new Datetime('now');
        $now->modify("+2 hours");
        $statuses = ['Approved', 'Pending', 'Assigned'];

        $q = $this->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->select('s')
            ->where('p = :provider')
            ->andWhere('s.status in (:statuses)')
            ->andWhere('s.start > :now')
            ->andWhere('s.nurse IS NOT NULL')
            ->setParameter(':statuses', $statuses)
            ->setParameter(':provider', $provider)
            ->setParameter(':now', $now);

        return $q->getQuery()->getResult();
    }    

    public function nurseHadShiftInYear( /*str*/$year, $id ) {

        $q = $this->createQueryBuilder( 'n' )
                  ->select( 'n' )
                  ->innerJoin('n.nurse', 's')
                  ->where('s.id = :id')
                  ->andWhere( 'n.start_date LIKE :year' )
                  ->setParameter( ':id', $id )
                  ->setParameter( ':year', $year );

        if ( $q->getQuery()->getArrayResult() != null ) {
            return true;
        } else return false;
    }
    
    public function getShiftsNurseWorkedInQuarter($dateRange, $nurse)
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $q = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.nurse', 'n')
            ->innerJoin('s.payroll_payments', 'pp')
            ->where('s.start BETWEEN :start AND :end')
            ->andWhere('n = :nurse')
            ->andWhere('s.status = :completed')
            ->setParameter(':start', $start)
            ->setParameter(':end', $end)
            ->setParameter(':nurse', $nurse)
            ->setParameter(':completed', 'Completed');

        return $q->getQuery()->getResult();
    }

    public function getNurseMostRecentShift($id)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.nurse', 'n')
            ->where('n.id = :id')
            ->andWhere('s.start < :now')
            ->orderBy('s.start', 'DESC')
            ->setParameter(':id', $id)
            ->setParameter(':now', new DateTime('now', app::getInstance()->getTimeZone()))
            ->setMaxResults(1);
    
        return $q->getQuery()->getOneOrNullResult();
    }

    public function getProviderMostRecentShift($id)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.provider', 'p')
            ->where('p.id = :id')
            ->andWhere('s.start < :now')
            ->orderBy('s.start', 'DESC')
            ->setParameter(':id', $id)
            ->setParameter(':now', new DateTime('now', app::getInstance()->getTimeZone()))
            ->setMaxResults(1);
    
        return $q->getQuery()->getOneOrNullResult();
    }

    public function getBackendShiftsBetweenTwoDates($dateRange, $count = false, $offset = null, $limit = null)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s.start','s.end','s.id AS shift_id', 's.bonus_amount', 's.incentive', 'n.first_name', 'n.last_name', 'n.id AS nurse_id', 'p.state_abbreviation', 'p.city', 'n.credentials', 'm.company')
            ->leftJoin('s.nurse', 'n')
            ->leftJoin('s.provider', 'p')
            ->leftJoin('p.member', 'm')
            ->innerJoin('s.payroll_payments', 'pp')
            ->where('s.start BETWEEN :start AND :end')
            ->andWhere('s.status = :completed')
            ->groupBy('s.id')
            ->setParameter(':start', $dateRange['start'])
            ->setParameter(':end', $dateRange['end'])
            ->setParameter(':completed', 'Completed');
    
            if ($limit) {
                $q->setMaxResults($limit);
            }
        
            if ($offset) {
                $q->setFirstResult($offset);
            }
        
            if ($count) {
                $q->select('COUNT(DISTINCT n.id)');
        
                return $q->getQuery()->getSingleScalarResult();
            } else {
                return $q->getQuery()->getArrayResult();
            }
    
    }

    public function getBackendShiftsBetweenTwoDatesForNurse($dateRange, $count = false, $offset = null, $limit = null)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s.start','s.end','s.id AS shift_id', 's.bonus_amount', 's.incentive', 'n.first_name', 'n.last_name', 'n.id AS nurse_id', 'p.state_abbreviation', 'p.city', 'n.credentials', 'm.company')
            ->leftJoin('s.nurse', 'n')
            ->leftJoin('s.provider', 'p')
            ->leftJoin('p.member', 'm')
            ->innerJoin('s.payroll_payments', 'pp')
            ->where('s.start BETWEEN :start AND :end')
            ->andWhere('s.status = :completed')
            ->andWhere('n.id = :nurse')
            ->groupBy('s.id')
            ->setParameter(':start', $dateRange['start'])
            ->setParameter(':end', $dateRange['end'])
            ->setParameter(':completed', 'Completed')
            ->setParameter(':nurse', $dateRange['nurse']);
    
            if ($limit) {
                $q->setMaxResults($limit);
            }
        
            if ($offset) {
                $q->setFirstResult($offset);
            }
        
            if ($count) {
                $q->select('COUNT(DISTINCT n.id)');
        
                return $q->getQuery()->getSingleScalarResult();
            } else {
                return $q->getQuery()->getArrayResult();
            }
    
    }

    public function getBackendScheduleBetweenTwoDates($dateRange, $count = false, $offset = null, $limit = null)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s.start','s.end', 's.status' ,'s.id AS shift_id', 's.bonus_amount', 's.incentive', 'n.first_name', 'n.last_name', 'n.id AS nurse_id', 'n.credentials', 'm.company')
            ->leftJoin('s.nurse', 'n')
            ->leftJoin('s.provider', 'p')
            ->leftJoin('p.member', 'm')
            ->where('s.start BETWEEN :start AND :end')
            ->groupBy('s.id')
            ->setParameter(':start', $dateRange['startDate'])
            ->setParameter(':end', $dateRange['endDate']);
    
            if ($limit) {
                $q->setMaxResults($limit);
            }
        
            if ($offset) {
                $q->setFirstResult($offset);
            }
        
            if ($count) {
                $q->select('COUNT(DISTINCT n.id)');
        
                return $q->getQuery()->getSingleScalarResult();
            } else {
                return $q->getQuery()->getArrayResult();
            }
    
    }

    public function getBackendScheduleBetweenTwoDatesForNurse($dateRange, $count = false, $offset = null, $limit = null)
    {
        $q = $this->createQueryBuilder('s')
            ->select('s.start','s.end', 's.status' , 's.id AS shift_id', 's.bonus_amount', 's.incentive', 'n.first_name', 'n.last_name', 'n.id AS nurse_id', 'n.credentials', 'm.company')
            ->leftJoin('s.nurse', 'n')
            ->leftJoin('s.provider', 'p')
            ->leftJoin('p.member', 'm')
            ->where('s.start BETWEEN :start AND :end')
            ->andWhere('n.id = :nurse')
            ->groupBy('s.id')
            ->setParameter(':start', $dateRange['startDate'])
            ->setParameter(':end', $dateRange['endDate'])
            ->setParameter(':nurse', $dateRange['nurse']);
    
            if ($limit) {
                $q->setMaxResults($limit);
            }
        
            if ($offset) {
                $q->setFirstResult($offset);
            }
        
            if ($count) {
                $q->select('COUNT(DISTINCT n.id)');
        
                return $q->getQuery()->getSingleScalarResult();
            } else {
                return $q->getQuery()->getArrayResult();
            }
    
    }

}
