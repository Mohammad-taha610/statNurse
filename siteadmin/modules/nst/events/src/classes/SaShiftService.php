<?php


namespace nst\events;


use DateTimeZone;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use nst\member\NstMember;
use nst\member\Nurse;
use nst\member\NurseRepository;
use nst\member\NurseService;
use nst\member\Provider;
use nst\messages\SmsService;
use nst\payroll\PayrollService;
use nst\events\ShiftTimes;
use oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\StartDate;
use Recurr\Rule;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\responses\Json;
use sacore\application\ValidateException;
use sa\events\Category;
use sa\events\Event;
use sa\member\auth;
use sa\system\saUser;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;
use When\When;

class SaShiftService
{
    /** @var ShiftRepository $shiftRepository */
    protected $shiftRepository;

    /** @var \DateTimeZone $timezone */
    protected $timezone;

    /** @var SmsService $smsService */
    protected $smsService;

    /** @var NurseService $nurseService */
    protected $nurseService;
    
    /** @var ShiftService $shiftService */
    protected $shiftService;

    /** @var SaUser $currentSaUser */
    protected $currentSaUser;

    public function __construct()
    {
        $this->shiftRepository = ioc::getRepository('Shift');
        $this->timezone = app::getInstance()->getTimeZone();
        $this->smsService = new SmsService();
        $this->nurseService = new NurseService();
        $this->shiftService = new ShiftService();
        $this->currentSaUser = modRequest::request('sa.user');
    }

    public function loadShiftCalendarData($data) {
        ini_set('memory_limit','512M');

        $response = [];
        $response['events'] = [];
        $data['backend'] = true;
        $shiftService = new ShiftService();
        $shiftRecurrences = $shiftService->parseShiftDataForCalendar($data);
        

        if($data['calendar_type'] == 'month') {
            $response['success'] = true;
            $response['calendar_type'] = 'month';
            foreach($shiftRecurrences as $date => $counts) {
                $response['shifts'][] = [
                    'date' => $date,
                    'counts' => $counts
                ];
            }
            return $response;
        }

        /** @var Shift $shift */
        foreach($shiftRecurrences as $shiftRecurrence) {

            if($shiftRecurrence['id']) {
                if (is_array($shiftRecurrence["nurse_type"])) {
                    $nurse_type_string = implode(', ', $shiftRecurrence["nurse_type"]);
                } else {
                    $nurse_type_string = $shiftRecurrence["nurse_type"];
                }
                if(!$shiftRecurrence['status']) continue;

                $bonusDisplay = $shiftRecurrence['bonus_amount'] ? '$' . number_format($shiftRecurrence['bonus_amount'], 2) : 'None';
                $covidDisplay = $shiftRecurrence['is_covid'] ? 'Yes' : 'No';
                $incentiveDisplay = 'None';
                switch ($shiftRecurrence['incentive']) {
                    case 1.0:
                        $incentiveDisplay = 'None';
                        break;
                    case 2:
                        $incentiveDisplay = 'Double';
                        break;
                    default:
                        $incentiveDisplay = $shiftRecurrence['incentive'] . 'x';
                        break;
                }

                $shiftData = [
                    'id' => $shiftRecurrence['id'],
                    'recurrence_id' => $shiftRecurrence['id'],
                    'route' => $shiftRecurrence['url'],
                    'shift_route' => $shiftRecurrence['url'],
                    'copy_route' => $shiftRecurrence['copy_route'],
                    'unique_id' => $shiftRecurrence['unique_id'],
                    'name' => ($shiftRecurrence['nurse_type'] ? '[' . $shiftRecurrence['nurse_type'] . '] ' : '') . ($shiftRecurrence['nurse_name'] ?: ''),
                    'status' => $shiftRecurrence['status'],
                    'start_time_formatted' => $shiftRecurrence['start_time_formatted'],
                    'end_time_formatted' => $shiftRecurrence['end_time_formatted'],
                    'start_date' => $shiftRecurrence['start_date'],
                    'start_time' => $shiftRecurrence['start_time'],
                    'end_date' => $shiftRecurrence['start_date'],
                    'end_time' => $shiftRecurrence['end_time'],
                    'nurse_name' => $shiftRecurrence['nurse_name'],
                    'nurse_route' => $shiftRecurrence['nurse_id'] ? app::get()->getRouter()->generate('edit_nurse', ['id' => $shiftRecurrence['member_id']]) : '',
                    'shift_type' => 'Recurring Shift',
                    'nurse_type' => $shiftRecurrence['nurse_type'],
                    'nurse_type_string' => $nurse_type_string,
                    'provider_id' => $shiftRecurrence['provider_id'],
                    'provider_name' => $shiftRecurrence['provider_name'],
                    'parent_id' => $shiftRecurrence['parent_id'],
                    'parent_unique_id' => $shiftRecurrence['parent_unique_id'],
                    'is_covid' => $shiftRecurrence['is_covid'],
                    'incentive' => $shiftRecurrence['incentive'],
                    'is_recurrence' => $shiftRecurrence['is_recurrence'],
                    'bonus_display' => $bonusDisplay,
                    'covid_display' => $covidDisplay,
                    'incentive_display' => $incentiveDisplay
                ];
            } else {
                $shift = ioc::get('Shift', ['id' => $shiftRecurrence['event_id']]);
                $startTime = $shift->getStart();
                $endTime = $shift->getEnd();

                if(!$shift->getStatus()) continue;

                $shiftType = 'Standard Shift';
                if ($shift->getRecurrenceType() && $shift->getRecurrenceType() != 'None') {
                    $shiftType = 'Recurring Shift';
                    $route = $shiftRecurrence['url'];
                } else {
                    $route = app::get()->getRouter()->generate('sa_shift_edit', ['id' => $shiftRecurrence['event_id']]);
                }

                $bonusDisplay = $shift->getBonusAmount() ? '$' . number_format($shift->getBonusAmount(), 2) : 'None';
                $covidDisplay = $shift->getIsCovid() ? 'Yes' : 'No';
                $incentiveDisplay = 'None';
                switch ($shift->getIncentive()) {
                    case 1.0:
                        $incentiveDisplay = 'None';
                        break;
                    case 2:
                        $incentiveDisplay = 'Double';
                        break;
                    default:
                        $incentiveDisplay = $shift->getIncentive().'x';
                        break;
                }

                if (is_array($shift->getNurseType())) {
                    $nurse_type_string = implode(', ', $shift->getNurseType());
                } else {
                    $nurse_type_string = $shift->getNurseType();
                }
                $shiftData = [
                    'id' => $shiftRecurrence['event_id'],
                    'recurrence_id' => 0,
                    'route' => $route,
                    'shift_route' => app::get()->getRouter()->generate('sa_shift_edit', ['id' => $shiftRecurrence['event_id']]),
                    'copy_route' => app::get()->getRouter()->generate('sa_shift_copy', ['id' => $shiftRecurrence['event_id']]),
                    'unique_id' => $shiftRecurrence['unique_id'],
                    'is_recurrence' => $shiftRecurrence['is_recurrence'],
                    'parent_unique_id' => $shiftRecurrence['parent_unique_id'],
                    'name' => ($shift->getNurseType() ? '[' . $shift->getNurseType() . '] ' : '') . ($shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() : ''),
                    'status' => $shift->getStatus(),
                    'start_time_formatted' => $startTime->format('g:i a'),
                    'end_time_formatted' => $endTime->format('g:i a'),
                    'start_date' => explode('T', $shiftRecurrence['start'])[0],
                    'start_time' => $startTime->format('H:i:s'),
                    'end_date' => $shift->getIsEndDateEnabled() ? explode('T', $shiftRecurrence['end'])[0] : explode('T', $shiftRecurrence['start'])[0],
                    'end_time' => $endTime->format('H:i:s'),
                    'nurse_name' => $shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() : '',
                    'nurse_route' => $shift->getNurse() ? app::get()->getRouter()->generate('edit_nurse', ['id' => $shift->getNurse()->getMember()->getId()]) : '',
                    'shift_type' => $shiftType,
                    'nurse_type' => $shift->getNurseType(),
                    'nurse_type_string' => $nurse_type_string,
                    'provider_id' => $shift->getProvider()->getId(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'is_covid' => $shift->getIsCovid(),
                    'incentive' => $shift->getIncentive(),
                    'parent_id' => $shift->getParentId(),
                    'bonus_display' => $bonusDisplay,
                    'covid_display' => $covidDisplay,
                    'incentive_display' => $incentiveDisplay
                ];
            }

            $response['shifts'][] = $shiftData;

            $response['success'] = true;
        }

        return $response;
    }

    /**
     * @param $data
     * @return array
     * @throws Exception
     * @throws ValidateException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Recurr\Exception\InvalidArgument
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\IocException
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    /**
     * Saves changes to a shift, or creation of a new shift
     * @throws ValidateException
     * @throws \Exception
     */
    public function saveShift($data)
    {
        $response = ['success' => false];
        $isNewShift = $data['id'];

        /** @var Shift $shift */
        $shift = $data['id'] ? ioc::get('Shift', ['id' => $data['id']]) : null;
        $shift = $shift ?: ioc::resolve('Shift');
        $shiftTimes = new ShiftTimes($data);
        
        $this->validateSaveShift($data, $shift, $shiftTimes);

        $this->initializeShiftData($data, $shift, $shiftTimes);

        if (!$isNewShift) {
            app::$entityManager->persist($shift);
        }

        if ($data['recurrence_type'] != 'None' && $data['recurrence_type'] != 'Custom') {
            $this->handleRecurrenceGeneration($data, $shift);
        } else {
            $this->handleShiftGeneration($data, $shift);
        }

        if (!$isNewShift) {
            $this->handleRecurrenceCustomDateShiftGeneration($data, $shift, $shiftTimes);
        }

        app::$entityManager->flush();
        $response['shift_id'] = $shift->getId();
        $response['success'] = true;
        return $response;
    }

    /**
     * Utility method for saveShift
     * @param array $data
     * @param Shift $shift
     * @param ShiftTimes $shiftTimes
     * @throws ValidateException
     */
    public function validateSaveShift($data, $shift, $shiftTimes)
    {
        if($data['nurse_id'] && !filter_var($data['override_expiring_docs'], FILTER_VALIDATE_BOOLEAN)) {
            $nurse =  ioc::get('Nurse', ['id' => $data['nurse_id']]);
            $nurseCreds = $nurse->getCredentials();
            
            $licenseExpirationDate = $nurse->getLicenseExpirationDate();
            $cprExpirationDate = $nurse->getCprExpirationDate();
            // $aclsExpirationDate = $nurse->getAclsExpirationDate();
            $now = new DateTime();

            if($licenseExpirationDate < $now) {
                throw new ValidateException('License Expired or will be expired on ' . $now->format('Y-m-d') . '.', 1);
            }
            
            if($cprExpirationDate < $now  && $nurseCreds != 'CNA') {
                throw new ValidateException('CPR Expired or will be expired on ' . $now->format('Y-m-d') . '.', 2);
            }

            // if($aclsExpirationDate < $now) {
            //     throw new ValidateException('ACLS Expired or will be expired on ' . $now->format('Y-m-d') . '.', 3);
            // }

            if ($data['start_date']) {
                $startDate = new Datetime($data['start_date']);
                if(!$this->canNurseWorkDay($startDate, $licenseExpirationDate)) {
                    throw new ValidateException('License Expired or will be expired on ' . $data['start_date'] . '.', 1);
                }
                
                if(!$this->canNurseWorkDay($startDate, $cprExpirationDate)  && $nurseCreds != 'CNA') {
                    throw new ValidateException('CPR Expired or will be expired on ' . $data['start_date'] . '.', 2);
                }

                // if(!$this->canNurseWorkDay($startDate, $aclsExpirationDate)) {
                //     throw new ValidateException('ACLS Expired or will be expired on ' . $data['start_date'] . '.', 3);
                // }

                if ($data['recurrence_end_date']) {
                    $recurringStartDate = clone($startDate);
                    $recurringEndDate = new Datetime($data['recurrence_end_date']);
                    while ($recurringStartDate <= $recurringEndDate) {
                        
                        if ($data['recurrence_options']) {
                            if (in_array(strtoupper(substr($recurringStartDate->format('D'), 0, 2)), $data['recurrence_options'])) {
                                $recurringStartDate->modify('+1 days');
                                continue;
                            }
                        }

                        if(!$this->canNurseWorkDay($recurringStartDate, $licenseExpirationDate)) {
                            throw new ValidateException('License Expired or will be expired on ' . $recurringStartDate->format('Y-m-d') . '.', 1);
                        }
                        
                        if(!$this->canNurseWorkDay($recurringStartDate, $cprExpirationDate) && $nurseCreds != 'CNA') {
                            throw new ValidateException('CPR Expired or will be expired on ' . $recurringStartDate->format('Y-m-d') . '.', 2);
                        }
        
                        // if(!$this->canNurseWorkDay($recurringStartDate, $aclsExpirationDate)) {
                        //     throw new ValidateException('ACLS Expired or will be expired on ' . $recurringStartDate->format('Y-m-d') . '.', 3);
                        // }

                        $recurringStartDate->modify('+1 days');
                    }
                }
                
                if ($data['recurrence_custom_dates']) {
                    foreach ($data['recurrence_custom_dates'] as $dateString) {
                        $customDate = new DateTime($dateString);
                        if(!$this->canNurseWorkDay($customDate, $licenseExpirationDate)) {
                            throw new ValidateException('License Expired or will be expired on ' . $customDate->format('Y-m-d') . '.', 1);
                        }
                        
                        if(!$this->canNurseWorkDay($customDate, $cprExpirationDate) && $nurseCreds != 'CNA') {
                            throw new ValidateException('CPR Expired or will be expired on ' . $customDate->format('Y-m-d') . '.', 2);
                        }
        
                        // if(!$this->canNurseWorkDay($customDate, $aclsExpirationDate)) {
                            // throw new ValidateException('ACLS Expired or will be expired on ' . $customDate->format('Y-m-d') . '.', 3);
                        // }
                    }
                }
            }
        }

        $response = ['success' => true, 'message' => ''];
        if ($shift->getStatus() == 'Completed') {
            throw new ValidateException('This shift has already been completed');
        }
        
        // make sure shift does not cover more than 16 hours -now passed to validateSaveShift
        if (ceil((strtotime($shiftTimes->getEnd()) - strtotime($shiftTimes->getStart()))/3600) > 16) {
            throw new ValidateException('Cannot create a shift spanning more than 16 hours');
        }
        
        if (!ioc::get('Category', ['id' => $data['category_id']])) {
            throw new ValidateException('Unable to find category');
        }

        if(!$data['provider_id']) {
            throw new ValidateException('No Provider ID');
        }

        if(!ioc::get('Provider', ['id' => $data['provider_id']])) {
            throw new ValidateException('Unable to find provider with id: ' . $data['provider_id']);
        }

        if (!$data['id']) {
            if ($this->hasConflictingShifts($data, $shiftTimes)) {
                throw new ValidateException('An existing shift already exists for this Nurse at the given time and date');
            }
        }

        return $response;
    }
    
    /**
     *  @return bool
     */
    public function canNurseWorkDay(DateTime $day, $documentExpDate): bool
    {
        if($documentExpDate < $day) {
            return false;
        }

        return true;
    }

    /**
     * Utility method for saveShift
     * @param array $data
     * @param ShiftTimes $shiftTimes
     */
    public function hasConflictingShifts($data, $shiftTimes)
    {
        $nurse = ioc::get('Nurse', ['id' => $data['nurse_id']]);
        if ($nurse) {
            $sameDayShifts = $this->shiftRepository->getShiftsForNurse($nurse, $shiftTimes->getStart())['all'];
            /** @var Shift $sdShift */
            foreach ($sameDayShifts as $sdShift) {
                if ($this->shiftService->isConflicting($shiftTimes->getStart(), $shiftTimes->getEnd(), $sdShift->getStart(), $sdShift->getEnd())) {
                    return true;
                }
            }
        }

        return false;
    }
    
    /**
     * Utility method for saveShift
     */
    public function handleRecurrenceGeneration($data, &$shift)
    {
        if ($data['recurrence_type'] != 'Daily') {
            $shift->setRecurrenceOptions($data['recurrence_options']);
        } else {
            $shift->setRecurrenceOptions(null);
        }

        $recurrenceEndDate = $data['recurrence_end_date'] ? new DateTime($data['recurrence_end_date'] . ' ' . $data['end_time'], app::getInstance()->getTimeZone()) : null;
        if (!$recurrenceEndDate) {
            $newEndDate = new DateTime($data['start_date'] . ' ' . $data['start_time'], app::getInstance()->getTimeZone());
            $newEndDate->modify('+ 30 days');
            $recurrenceEndDate = $newEndDate;
        }

        $shift->setRecurrenceEndDate($recurrenceEndDate);
        $shift->setRecurrenceInterval($data['recurrence_interval']);
        $shift->setUntilDate($recurrenceEndDate);

        $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();
        $utcstartdate = new \DateTime($shift->getStart()->format('Ymd G:i:s', true), $timezone);
        $utcenddate = new \DateTime($shift->getEnd()->format('Ymd G:i:s', true), $timezone);

        $rrule = new Rule(null, $utcstartdate, $utcenddate, $timezone);

        $frequency = 'DAILY';
        if (!$data['id']) {
            switch ($data['recurrence_type']) {
                case 'Weekly':
                    $frequency = 'WEEKLY';
                    $rrule->setByDay($data['recurrence_options']);
                    break;
                case 'Monthly':
                    // This is intentional
                    $frequency = 'YEARLY';
                    $rrule->setByMonth($data['recurrence_options']);
                    break;
            }
            $rrule->setFreq($frequency)
            ->setInterval($data['recurrence_interval'])
            ->setWeekStart('SU');
            
            if ($recurrenceEndDate) {
                $rrule->setUntil($recurrenceEndDate);
            } else {
                $shift->setUntilDate(null);
                $rrule->setCount(Event::LARGE_NUMBER);
            }
            
            $shift->setRecurrenceRules($rrule->getString());
        }

        $isCreation = $data['action_type'] === 'create';
        switch($data['status']) {
            case 'Open':
                self::assignNurseToShift($shift, null, false);
                break;
            case 'Pending':
                $resp = $this->nurseService->requestShift(['shift' => $shift, 'nurse_id' => $data['nurse_id'], 'user_type' => 'NurseStat', 'is_creation' => $isCreation]);
                if(!$resp['success']) {
                    throw new ValidateException('Could not request shift successfully.');
                }
                break;
            case 'Assigned':
                self::assignNurseToShift($shift, $data['nurse_id'], $isCreation);
                break;
            case 'Approved':
                self::adminAssignNurseToShift($shift, $data['nurse_id'], false, $isCreation);
                break;
        }
        $shift->setStatus($data['status']);

        // create shifts based on recurrence rules
        $_start = new DateTime($data['start_date'] . ' ' . $data['start_time'], app::getInstance()->getTimeZone());
        $_end = new DateTime($data['recurrence_end_date'] . ' ' . $data['end_time'], app::getInstance()->getTimeZone());
        $_interval = $data['recurrence_interval'];
        $_type = $data['recurrence_type']; // Daily, Weekly, Monthly, Custom
        $_diff = $_start->diff($_end);
        //$_rrule = $shift->getRecurrenceRules();

        if ($_type === 'Custom') {
            app::$entityManager->persist($shift); // persist the main shift that we copied
        }

        // daily recurrences
        if ($_type !== 'Weekly' && $_type !== 'Monthly') {
            // normal for loop for daily recurrences
            if (!$data['id']) {
                for ($r = 0; $r <= $_diff->format('%a'); $r += $_interval) {
                    // create a shift like above, only with the modified startDate based on the loop day
                    $occurrence_dt = new DateTime($data['start_date']);
                    $occurrence = $occurrence_dt->modify('+' . $r . ' day');
                    
                    $this->createNewShiftRecurrence($data, $occurrence, $_end, $rrule);
                }
                app::$entityManager->remove($shift); // done with initial shift, lets delete it
            }
        }
        // weekly recurrences & monthly recurrences
        if ($_type === 'Weekly' || $_type === 'Monthly') {
            if (!$data['id']) {
                // add 1 day to $_rrule
                $w = new When();
                $w->RFC5545_COMPLIANT = When::IGNORE;

                $until = $rrule->getUntil()->modify('+1 day');

                switch ($data['recurrence_type']) {
                    case 'Weekly':
                        $w->startDate(new DateTime())
                            ->byday($rrule->getByDay())
                            ->freq($rrule->getFreqAsText())
                            ->interval($rrule->getInterval())
                            ->until($until)
                            ->generateOccurrences();
                        break;
                    case 'Monthly':
                        $w->startDate(new DateTime())
                            ->bymonth($rrule->getByMonth())
                            ->freq($rrule->getFreqAsText())
                            ->interval($rrule->getInterval())
                            ->until($until)
                            ->generateOccurrences();
                        break;
                }
                foreach ($w->occurrences as $occurrence) {
                    // each $occurrence has a date, timezone_type, timezone
                    $recurrenceStart = new DateTime($occurrence, app::getInstance()->getTimeZone());
                    if ($_start !== $recurrenceStart) {
                        $this->createNewShiftRecurrence($data, $occurrence, $_end, $rrule);
                    }
                }
                app::$entityManager->remove($shift); // done with initial shift, lets delete it
            }
        }
    }
    
    /**
     * Utility method for saveShift
     */
    public function handleShiftGeneration($data, &$shift)
    {
        $shift->setRecurrenceOptions(null)
        ->setRecurrenceEndDate(null)
        ->setRecurrenceInterval(1);

        if ($shift->getStatus() == 'Pending') {
            if ($data['approve_nurse']) {
                self::approveShiftRequest([ 'id' => $data['id'], 'is_recurrence' => false ]);
            } else if ($data['deny_nurse']) {
                self::denyShiftRequest([ 'id' => $data['id'], 'is_recurrence' => false ]);
            }
        }

        $isCreation = $data['action_type'] === 'create';
        switch($data['status']) {
            case 'Open':
                self::assignNurseToShift($shift, null, false);
                break;
            case 'Pending':
                $resp = $this->nurseService->requestShift(['shift' => $shift, 'nurse_id' => $data['nurse_id'], 'user_type' => 'NurseStat', 'is_creation' => $isCreation]);
                if(!$resp['success']) {
                    throw new ValidateException('Could not request shift successfully.');
                }
                break;
            case 'Assigned':
                self::assignNurseToShift($shift, $data['nurse_id'], $isCreation);
                break;
            case 'Approved':
                self::adminAssignNurseToShift($shift, $data['nurse_id'], false, $isCreation);
                break;
        }
        $shift->setStatus($data['status']);
    }

    /**
     * Utility method for saveShift
     */
    public function handleRecurrenceCustomDateShiftGeneration($data, &$shift, $shiftTimes)
    {
        app::$entityManager->flush($shift);
        if ($data['recurrence_type'] && $data['recurrence_custom_dates']) {
            foreach ($data['recurrence_custom_dates'] as $recurrence_date) {
                if ($recurrence_date == $data['start_date']) {
                    continue;
                }

                $customShift = clone $shift;
                $customStartDate = new DateTime($recurrence_date, app::getInstance()->getTimeZone());
                $customStart = new DateTime($recurrence_date . ' ' . $data['start_time'], app::getInstance()->getTimeZone());
                $customEnd = new DateTime(($recurrence_date) . ' ' . $data['end_time'], app::getInstance()->getTimeZone());
                if ($customEnd < $customStart) {
                    $customEnd->modify('+1 days');
                }
                $customShift->setStart($customStart);
                $customShift->setStartTime($shiftTimes->getStartTime());
                $customShift->setStartDate($customStartDate);
                $customShift->setEnd($customEnd);
                $customShift->setEndTime($shiftTimes->getEnd());
                $customShift->setEndDate($customStartDate);
                app::$entityManager->persist($customShift);

                if ($data['nurse_id']) {
                    $isCreation = $data['action_type'] === 'create';
                    switch($data['status']) {
                        case 'Open':
                            self::assignNurseToShift($customShift, null, false);
                            break;
                        case 'Pending':
                            $resp = $this->nurseService->requestShift(['shift' => $customShift, 'nurse_id' => $data['nurse_id'], 'user_type' => 'NurseStat', 'is_creation' => $isCreation]);
                            if(!$resp['success']) {
                                throw new ValidateException('Could not request shift successfully.');
                            }
                            break;
                        case 'Assigned':
                            self::assignNurseToShift($customShift, $data['nurse_id'], $isCreation);
                            break;
                        case 'Approved':
                            self::adminAssignNurseToShift($customShift, $data['nurse_id'], false, $isCreation);
                            break;
                    }
                    $shift->setStatus($data['status']);
                } else {
                    app::$entityManager->flush();
                    for ($i = 1; $i < $data['number_of_copies']; $i++) {
                        $copiedShift = clone $customShift;
                        self::assignNurseToShift($copiedShift, null, false);
                        $copiedShift->setParentId($customShift->getId());
                        app::$entityManager->persist($copiedShift);
                    }
                }
                app::$entityManager->flush();
            }
        }
        for ($i = 1; $i < $data['number_of_copies']; $i++) {
            $copiedShift = clone $shift;
            self::assignNurseToShift($copiedShift, null, $data['action_type']);
            $copiedShift->setParentId($shift->getId());
            app::$entityManager->persist($copiedShift);
        }
    }

    /**
     * Utility method for saveShift
     */
    public function initializeShiftData($data, &$shift, $shiftTimes)
    {
        $shift->setBonusAmount($data['bonus_amount'])
        ->setBonusDescription($data['bonus_description'])
        ->setIsEndDateEnabled(false)
        ->setNurseType($data['nurse_type'])
        ->setRecurrenceType($data['recurrence_type'])
        ->setDescription($data['description'])
        ->setName($data['name'])
        ->setStartTime($shiftTimes->getStartTime())
        ->setStartDate($shiftTimes->getStartDate())
        ->setEndTime($shiftTimes->getEndTime())
        ->setEndDate($shiftTimes->getEndDate())
        ->setStart($shiftTimes->getStart())
        ->setEnd($shiftTimes->getEnd())
        ->setIsCovid($data['is_covid'] == 'Yes')
        ->setIncentive($data['incentive'])
        ->setCategory(ioc::get('Category', ['id' => $data['category_id']]))
        ->setProvider(ioc::get('Provider', ['id' => $data['provider_id']]));
    }

    public function saveShiftRecurrence($data) {
        $shiftId = $data['id'];
        $recurrenceId = $data['recurrence_id'];
        $recurrenceUniqueId = $data['recurrence_unique_id'];

        $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();
        $start = new DateTime($data['start_date'] . ' ' . $data['start_time'], $timezone);
        $end = new DateTime(($data['end_date_enabled'] == "true" ? $data['end_date'] : $data['start_date']) . ' ' . $data['end_time'], $timezone);

        $start_date_unique_id = $start->format('mdY');
        $end_date_unique_id = $end->format('mdY');
        $uniqueId = $shiftId . '-' . $start_date_unique_id;

        $shift = ioc::get('Shift', ['id' => $shiftId]);
        /** @var ShiftRecurrence $recurrence */
        $recurrence = null;
        if($recurrenceId) {
            $recurrence = ioc::get('ShiftRecurrence', ['id' => $recurrenceId]);
        } else {
            $recurrence = ioc::get('ShiftRecurrence', ['recurrenceUniqueId' => $uniqueId, 'event' => $shift]);
            if(!$recurrence) {
                $recurrence = ioc::resolve('ShiftRecurrence');
                app::$entityManager->persist($recurrence);
            }
        }

        // Set initial shift data
        $recurrence->setName($data['name'])
            ->setBonusAmount($data['bonus_amount'])
            ->setBonusDescription($data['bonus_description'])
            ->setRecurrenceUniqueId($uniqueId)
            ->setStatus($recurrenceId ? $recurrence->getStatus() : 'Open')
            ->setDescription($data['description'])
            ->setIsEndDateEnabled(false)
            ->setNurseType($data['nurse_type'])
            ->setRecurrenceExists(true)
            ->setEvent($shift)
            ->setStart($start)
            ->setEnd($end)
            ->setIsCovid($data['is_covid'] == 'Yes')
            ->setIncentive($data['incentive']);

        // Save Category
        $category = ioc::get('Category', ['id' => $data['category_id']]);
        if(!$category) {
            throw new ValidateException('Unable to find category');
        }
        $recurrence->setCategory($category);

        //Set Provider as owner of the shift
        $provider = ioc::get('Provider', ['id' => $data['provider_id']]);
        if(!$provider) {
            throw new ValidateException('Could not find provider');
        }
        $recurrence->setProvider($provider);

        if($recurrence->getStatus() === 'Pending') {
            if (filter_var($data['approve_nurse'], FILTER_VALIDATE_BOOLEAN )) {
                self::approveShiftRequest([
                    'id' => $recurrenceId,
                    'is_recurrence' => "true"
                ]);
            } else if (filter_var($data['deny_nurse'], FILTER_VALIDATE_BOOLEAN )) {
                    self::denyShiftRequest([
                        'id' => $recurrenceId,
                        'is_recurrence' => "true"
                    ]);
            }
        }
        // Remove nurse from recurrence if changing
        if($recurrence->getNurse() && $recurrence->getNurse()->getId() != $data['nurse_id']) {
            switch($recurrence->getStatus()) {
                case 'Pending':
                    self::denyShiftRequest([
                        'id' => $recurrenceId,
                        'is_recurrence' => true
                    ]);
                    break;
                default:
                    $service = new NurseService();
                    $service->removeFromShift(['shift' => $recurrence]);
                    break;
            }
        } elseif($recurrence->getNurse() && $recurrence->getStatus() === 'Pending') {
            if (filter_var($data['approve_nurse'], FILTER_VALIDATE_BOOLEAN ) || $data['status'] === 'Approved') {
                self::approveShiftRequest([
                    'id' => $recurrenceId,
                    'is_recurrence' => true
                ]);
            } else if (filter_var($data['deny_nurse'], FILTER_VALIDATE_BOOLEAN ) || $data['status'] !== 'Pending') {
                self::denyShiftRequest([
                    'id' => $recurrenceId,
                    'is_recurrence' => true
                ]);
            }
        }

        $isCreation = $data['action_type'] === 'create';
        switch($data['status']) {
            case 'Pending':
                $service = new NurseService();
                $service->requestShift(['shift' => $recurrence, 'nurse_id' => $data['nurse_id'], 'user_type' => 'NurseStat']);
                break;
            case 'Assigned':
                self::assignNurseToRecurrence($recurrence, $data['nurse_id'], $isCreation);
                break;
            case 'Approved':
                self::adminAssignNurseToShift($recurrence, $data['nurse_id'], true, $isCreation);
                break;
        }

        if(!$recurrenceId) {
            app::$entityManager->persist($recurrence);
        }

        app::$entityManager->flush();
        $response['shift_id'] = $recurrence->getId();
        $response['success'] = true;
        return $response;
    }

    /**
     * @param Shift $shift
     * @param integer $nurseId
     * @throws \Exception
     */
    public function adminAssignNurseToShift($shift, $nurseId, $isRecurrence, $isCreation) {
        $response = ['success' => false];
        $currentUser = modRequest::request('sa.user');
        $saShiftLogger = new SaShiftLogger();
        $previousNurse = $shift->getNurse();
        if($previousNurse && $previousNurse->getId() != $nurseId) {
            $notificationData = [
                'nurse' => $previousNurse,
                'type' => $shift->getStatus() == 'Assigned' || $shift->getStatus() == 'Approved' ? 'Removed' : 'Denied',
                'is_recurrence' => $isRecurrence,
                'shift' => $shift
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);
            
            $previousNurse->removeShift($shift);

            $this->smsService->handleSendSms($shift, ['message_type' => 'remove_shift', 'by' => 'siteadmin', 'nurse' => $previousNurse]);
        }

        if(!$nurseId) {
            $shift->setNurse(null);
            $shift->setStatus('Open');
            $shift->setIsProviderApproved(false);
            $shift->setIsNurseApproved(false);

            $response['success'] = true;
        } else {
            $nurse = ioc::get('Nurse', ['id' => $nurseId]);
            
            $shiftService = new ShiftService();
            $rfcsResponse = $shiftService->removeFromConflictingShifts($nurse, $shift, 'NurseStat');

            // If success is false, likely can't remove the nurse from a shift starting in the next 2 hours
            if(!$rfcsResponse['success']){
                throw new ValidateException('An existing shift already exists for this Nurse at the given time and date');
            }
                        
            $previousNurse = $shift->getNurse();
            if($previousNurse && $previousNurse->getId() != $nurseId) {
                $notificationData = [
                    'nurse' => $previousNurse,
                    'type' => $shift->getStatus() == 'Assigned' || $shift->getStatus() == 'Approved' ? 'Removed' : 'Denied',
                    'is_recurrence' => $isRecurrence,
                    'shift' => $shift
                ];
                modRequest::request('nst.messages.sendShiftNotification', $notificationData);
                $previousNurse->removeShift($shift);

                $this->smsService->handleSendSms($shift, ['message_type' => 'remove_shift', 'by' => 'siteadmin', 'nurse' => $previousNurse]);
            }

            $nurse->addShift($shift);
            $shift->setNurse($nurse);

            // Log Siteadmin assignment action
            $nurse = $shift->getNurse();
            if ($nurse && !$isCreation) {
                $nurseCreds = $nurse->getCredentials();
                $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');
                $shiftService = new ShiftService();

                /** @var saUser $currentUser */
                $currentUser = modRequest::request('sa.user');
                $saShiftLogger = new SaShiftLogger();
                $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been Approved by NurseStat';
                $logMessage = $logBody . ' - User: ' . $currentUser?->getFirstName() . ' ' . $currentUser?->getLastName();
                $saShiftLogger->log($logMessage, ['action' => 'APPROVED']);
            }

            if($shift->getStatus() == 'Assigned') {
                $notificationData = [
                    'provider' => $shift->getProvider(),
                    'type' => 'Accepted',
                    'is_recurrence' => $isRecurrence,
                    'shift' => $shift
                ];
            }

            $shift->setStatus('Approved');
            $shift->setIsProviderApproved(true);
            $shift->setIsNurseApproved(true);
            $notificationData = [
                'nurse' => $nurse,
                'type' => 'Admin Assigned',
                'is_recurrence' => $isRecurrence,
                'shift' => $shift
            ];

            // Send siteadmin shift created sms
            if ($nurse && $isCreation) {
                $this->smsService->handleSendSms($shift, ['message_type' => 'create_shift', 'by' => 'siteadmin', 'nurse' => $nurse]);
            }
            
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);

            if ($isCreation) {
                $nurseCreds = $nurse->getCredentials();
                $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');
                $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been Approved by NurseStat';
                /** @var saUser $currentUser */
                $currentUser = modRequest::request('sa.user');
                $saShiftLogger = new SaShiftLogger();
                $logMessage = $currentUser ? $logBody . ' - User: ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName() : $logBody;
                $saShiftLogger->log($logMessage, ['action' => 'CREATED']);
            }
            
            /** @var Provider $provider */
            $provider = $shift->getProvider();
            
            if(!$nurse->getPreviousProviders()->contains($provider)) {
                $nurse->addPreviousProvider($provider);
            }
            if(!$provider->getPreviousNurses()->contains($nurse)) {
                $provider->addPreviousNurse($nurse);
            }

            app::$entityManager->flush();
            $service = new PayrollService();
            $service->initializeShiftRates($shift);

            $response['success'] = true;
        }

        return $response;
    }

    /**
     * @param Shift $shift
     * @param integer $nurseId
     */
    public function assignNurseToShift($shift, $nurseId, $isCreation) {
        if($nurseId && $shift->getNurse() && $shift->getNurse()->getId() === $nurseId) {
            return;
        }
        if($shift->getStatus() == SHIFT::STATUS_COMPLETED) {
            throw new ValidateException('This shift is not eligible for a nurse assignment');
        }

        if(!$nurseId) {
            $shift->setNurse(null);
            $shift->setStatus('Open');
            $shift->setIsProviderApproved(false);
            $shift->setIsNurseApproved(false);
        } else {
            $nurse = ioc::get('Nurse', ['id' => $nurseId]);
            $nurse->addShift($shift);

            if (!$isCreation) {
                $this->smsService->handleSendSms($shift, ['message_type' => 'assign_shift', 'by' => 'siteadmin', 'nurse' => $nurse]);
                $nurseCreds = $nurse->getCredentials();
                $status = empty($shift->getStatus()) ? 'New' : $shift->getStatus();
                $intro = ($status === 'Pending' || $status === 'New' ? 'A ' : 'An ') . ucfirst($status);
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');

                /** @var saUser $currentUser */
                $currentUser = modRequest::request('sa.user');
                $saShiftLogger = new SaShiftLogger();
                $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been Assigned by NurseStat';
                $logMessage = $logBody . ' - User: ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                $saShiftLogger->log($logMessage, ['action' => 'ASSIGNED']);
            }

            /** @var Provider $provider */
            $provider = $shift->getProvider();
            
            if(!$nurse->getPreviousProviders()->contains($provider)) {
                $nurse->addPreviousProvider($provider);
            }
            if(!$provider->getPreviousNurses()->contains($nurse)) {
                $provider->addPreviousNurse($nurse);
            }

            $shift->setNurse($nurse);
            $shift->setStatus('Assigned');
            $shift->setIsProviderApproved(true);
            $shift->setIsNurseApproved(false);
            $notificationData = [
                'nurse' => $shift->getNurse(),
                'type' => 'Assigned',
                'is_recurrence' => false,
                'shift' => $shift
            ];

            if ($isCreation) {
                $this->smsService->handleSendSms($shift, ['message_type' => 'create_shift', 'by' => 'siteadmin', 'nurse' => $nurse]);
            }

            /** @var saUser $currentUser */
            $nurseCreds = $nurse->getCredentials();
            $status = empty($shift->getStatus()) ? 'New' : $shift->getStatus();
            $intro = ($status === 'Pending' || $status === 'New' ? 'A ' : 'An ') . ucfirst($status);
            $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $date = $shift->getStart()->format('m/d/Y');
            $time = $shift->getStart()->format('H:i');
            $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been Assigned by NurseStat';
            $currentUser = modRequest::request('sa.user');
            $saShiftLogger = new SaShiftLogger();
            $logMessage = $logBody . ' - User: ' . $currentUser?->getFirstName() . ' ' . $currentUser?->getLastName();
            $saShiftLogger->log($logMessage, ['action' => 'CREATED']);

            modRequest::request('nst.messages.sendShiftNotification', $notificationData);
        }
    }

    /**
     * @param Shift $shift
     * @param integer $nurseId
     */
    public function assignNurseToRecurrence($recurrence, $nurseId, $isCreation) {

        if($nurseId && $recurrence->getNurse() && $recurrence->getNurse()->getId() == $nurseId) {
            return;
        }
        if($recurrence->getStatus() == SHIFT::STATUS_COMPLETED) {
            throw new ValidateException('This shift is not eligible for a nurse assignment');
        }

        if($previousNurse = $recurrence->getNurse()) {
            $notificationData = [
                'nurse' => $recurrence->getNurse(),
                'type' => $recurrence->getStatus() == 'Assigned' || $recurrence->getStatus() == 'Approved' ? 'Removed' : 'Denied',
                'is_recurrence' => true,
                'shift' => $recurrence
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);
            $previousNurse->removeShiftRecurrence($recurrence);
        }

        if(!$nurseId) {
            $recurrence->setNurse(null);
            $recurrence->setStatus('Open');
            $recurrence->setIsProviderApproved(false);
            $recurrence->setIsNurseApproved(false);
        } else {
            $nurse = ioc::get('Nurse', ['id' => $nurseId]);
            $nurse->addShiftRecurrence($recurrence);

            // send twilio sms
            if ($nurse && !$isCreation) {
                $nurseCreds = $nurse->getCredentials();
                $status = empty($recurrence->getStatus()) ? 'New' : $recurrence->getStatus();
                $intro = ($status === 'Pending' || $status === 'New' ? 'A ' : 'An ') . ucfirst($status);
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $date = $recurrence->getStart()->format('m/d/Y');
                $time = $recurrence->getStart()->format('H:i');

                /** @var saUser $currentUser */
                $currentUser = modRequest::request('sa.user');
                $saShiftLogger = new SaShiftLogger();
                $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been Assigned by NurseStat';
                $logMessage = $logBody . ' - User: ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                $saShiftLogger->log($logMessage, ['action' => 'ASSIGNED']);
            }

            $recurrence->setNurse($nurse);
            $recurrence->setStatus('Assigned');
            $recurrence->setIsProviderApproved(true);
            $recurrence->setIsNurseApproved(false);
            $notificationData = [
                'nurse' => $recurrence->getNurse(),
                'type' => 'Assigned',
                'is_recurrence' => true,
                'shift' => $recurrence
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);
        }
    }

    public function loadShiftData($data) {
        $id = $data['id'];
        $response = ['success' => false];

        if($id) {
            /** @var Shift $shift */
            $shift = ioc::get('Shift', ['id' => $id]);

            $startTime = $shift->getStart();
            $endTime = $shift->getEnd();
            if ($shift) {
                $response['data'] = [
                    'name' => $shift->getName(),
                    'category' => $shift->getCategory()->getId(),
                    'start_time' => $shift->getStart()->format('G:i'),
                    'end_time' => $shift->getEnd()->format('G:i'),
                    'start_date' => $shift->getStart()->format('Y-m-d'),
                    'end_date' => $shift->getEnd() ? $shift->getEnd()->format('Y-m-d') : null,
                    'end_date_enabled' => $shift->getIsEndDateEnabled(),
                    'nurse_id' => $shift->getNurse() ? $shift->getNurse()->getId() : null,
                    'nurse_name' => $shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() : null,
                    'nurse_type' => $shift->getNurseType(),
                    'bonus_amount' => $shift->getBonusAmount(),
                    'bonus_description' => $shift->getBonusDescription(),
                    'description' => $shift->getDescription(),
                    'recurrence_type' => $shift->getRecurrenceType(),
                    'recurrence_options' => $shift->getRecurrenceOptions(),
                    'recurrence_end_date' => $shift->getRecurrenceEndDate() ? $shift->getRecurrenceEndDate()->format('Y-m-d'): '',
                    'recurrence_interval' => $shift->getRecurrenceInterval(),
                    'provider_id' => $shift->getProvider()->getId(),
                    'is_covid' => $shift->getIsCovid(),
                    'incentive' => $shift->getIncentive(),
                    'status' => $shift->getStatus()
                ];

                $response['success'] = true;
            }
        }

        return $response;
    }
    /** @deprecated */
    public static function loadRecurrenceData($data) {
        $shiftId = $data['id'];
        $recurrenceId = $data['recurrence_id'];
        $recurrenceUniqueId = $data['recurrence_unique_id'];
        $response = ['success' => false];


        $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();
        if($recurrenceId) {
            /** @var ShiftRecurrence $shiftRecurrence */
            $shiftRecurrence = ioc::get('Shift', ['id' => $recurrenceId]);
            $startTime = $shiftRecurrence->getStart();
            $endTime = $shiftRecurrence->getEnd();

            if($shiftRecurrence) {
                $response['data'] = [
                    'name' => $shiftRecurrence->getName(),
                    'category' => $shiftRecurrence->getCategory()->getId(),
                    'start_time' => $startTime->format('G:i'),
                    'end_time' => $endTime->format('G:i'),
                    'start_date' => $shiftRecurrence->getStart()->format('Y-m-d'),
                    'end_date' => $shiftRecurrence->getIsEndDateEnabled() ? $shiftRecurrence->getEnd()->format('Y-m-d') : $shiftRecurrence->getStart()->format('Y-m-d'),
                    'end_date_enabled' => $shiftRecurrence->getIsEndDateEnabled(),
                    'bonus_amount' => $shiftRecurrence->getBonusAmount(),
                    'bonus_description' => $shiftRecurrence->getBonusDescription(),
                    'description' => $shiftRecurrence->getDescription(),
                    'nurse_id' => $shiftRecurrence->getNurse() ? $shiftRecurrence->getNurse()->getId() : null,
                    'nurse_name' => $shiftRecurrence->getNurse() ? $shiftRecurrence->getNurse()->getMember()->getFirstName() . ' ' . $shiftRecurrence->getNurse()->getMember()->getLastName() : null,
                    'nurse_type' => $shiftRecurrence->getNurseType(),
                    'is_covid' => $shiftRecurrence->getIsCovid(),
                    'incentive' => $shiftRecurrence->getIncentive(),
                ];

                $response['success'] = true;
            }
        } else {
            /** @var Shift $shift */
            $shift = ioc::get('Shift', ['id' => $shiftId]);

            $start = new DateTime($data['start_date'], $timezone);
            $end = new DateTime($data['end_date'], $timezone);

            $startTime = $shift->getStart();
            $endTime = $shift->getEnd();

            if ($shift) {
                $response['data'] = [
                    'name' => $shift->getName(),
                    'category' => $shift->getCategory()->getId(),
                    'start_time' => $startTime->format('G:i'),
                    'end_time' => $endTime->format('G:i'),
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $shift->getIsEndDateEnabled() ? $end->format('Y-m-d') : null,
                    'end_date_enabled' => $shift->getIsEndDateEnabled(),
                    'nurse_id' => $shift->getNurse() ? $shift->getNurse()->getId() : null,
                    'nurse_name' => $shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() : null,
                    'nurse_type' => $shift->getNurseType(),
                    'bonus_description' => $shift->getBonusDescription(),
                    'bonus_amount' => $shift->getBonusAmount(),
                    'description' => $shift->getDescription(),
                    'recurrence_type' => $shift->getRecurrenceType(),
                    'recurrence_options' => $shift->getRecurrenceOptions(),
                    'recurrence_end_date' => $shift->getRecurrenceEndDate() ? $shift->getRecurrenceEndDate()->format('Y-m-d'): '',
                    'recurrence_interval' => $shift->getRecurrenceInterval(),
                    'status' => $shift->getStatus(),
                    'is_covid' => $shift->getIsCovid(),
                    'incentive' => $shift->getIncentive(),
                ];

                $response['success'] = true;
            }
        }

        return $response;
    }

    public function loadProviders() {
        $response = ['success' => false];

        $providers = ioc::getRepository('Provider')->findAll();

        if($providers) {
            /** @var Provider $provider */
            foreach($providers as $provider) {
                if(!$provider->getMember() || $provider->getMember()->getIsDeleted()) {
                    continue;
                }
                $response['providers'][] = [
                    'id' => $provider->getId(),
                    'name' => $provider->getMember()->getCompany(),
                    'pay_rates' => $provider->getPayRates(),
                    'uses_travel_pay' => $provider->getUsesTravelPay()
                ];
            }

            $response['success'] = true;
        }

        return $response;
    }

    public function loadNurses() {
        $response = ['success' => false];

        $nurses = ioc::getRepository('Nurse')->findAll();

        if($nurses) {
            /** @var Nurse $nurse */
            foreach($nurses as $nurse) {
                if(!$nurse->getMember() || $nurse->getMember()->getIsDeleted()) {
                    continue;
                }
                $response['nurses'][] = [
                    'id' => $nurse->getId(),
                    'name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
                    'payment_method' => $nurse->getPaymentMethod(),
                    'credentials' => $nurse->getCredentials()
                ];
            }

            $response['success'] = true;
        }

        return $response;
    }

    public function loadCalendarFilters() {
        $response = ['success' => false];

        $providers = ioc::getRepository('Provider')->findAll();
        $nurses = ioc::getRepository('Nurse')->findAll();
        $categories = ioc::getRepository(ioc::staticResolve('\sa\events\Category'))->findAll();

        if($providers || $nurses || $categories) {
            /** @var Provider $provider */
            foreach($providers as $provider) {
                if(!$provider->getMember() || $provider->getMember()->getIsDeleted()) {
                    continue;
                }
                $response['providers'][] = [
                    'id' => $provider->getId(),
                    'name' => $provider->getMember()->getCompany()
                ];
            }
            /** @var Nurse $nurse */
            foreach($nurses as $nurse) {
                if(!$nurse->getMember() || $nurse->getMember()->getIsDeleted()) {
                    continue;
                }
                $response['nurses'][] = [
                    'id' => $nurse->getId(),
                    'name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName()
                ];
            }
            /** @var Category $category */
            foreach($categories as $category) {
                $response['categories'][] = [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ];
            }

            $response['success'] = true;
        }

        return $response;
    }

    public function deleteShift($data) {
        $id = $data['id'];
        $response = ['success' => false];

        $shift = ioc::get('Shift', ['id' => $id]);
        if($shift) {
            try {
                /** @var NstUser $currentUser */
                $currentUser = modRequest::request('sa.user');

                // send twilio sms
                $nurse = $shift->getNurse();
                if ($nurse) {
                    $this->smsService->handleSendSms($shift, ['message_type' => 'deleted_shift', 'by' => 'siteadmin']);
                    
                    $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
                    $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                    $date = $shift->getStart()->format('m/d/Y');
                    $time = $shift->getStart()->format('H:i');
                    $smsBody = 'DELETED SHIFT - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been DELETED by ' . $currentUser->getLastName() . ', ' . $currentUser->getFirstName();
                
                    // log
                    app::get()->getLogger()->addError('sa.deleteShift: ' . $smsBody);
                }
                /** @var saUser $currentUser */
                $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');
                $logBody = $intro . ' Shift on ' . $date . ' at ' . $time . ' was Deleted by '. $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                $saShiftLogger = new SaShiftLogger();
                $saShiftLogger->log($logBody, ['action' => 'DELETED']);

                $recurrences = ioc::getRepository('ShiftRecurrence')->findBy(['event' => $shift]);
                
                foreach($recurrences as $recurrence){                
                    app::$entityManager->remove($recurrence);
                    app::$entityManager->flush();
                }
                
                app::$entityManager->remove($shift);
                app::$entityManager->flush();
                $response['success'] = true;
            } catch (\Throwable $t) {
                $response['error'] = $t->getMessage();
            }
        }

        return $response;
    }

    public function deleteShiftRecurrence($data) {
        //$id = $data['id'];
        $recurrence_id = $data['id'];
        $response = ['success' => false];

        /** @var ShiftRecurrence $recurrence */
        $recurrence = ioc::get('Shift', ['id' => $recurrence_id]);
        if($recurrence) {
            //$recurrence->setRecurrenceExists(false);
            /** @var Nurse $nurse */
            $nurse = $recurrence->getNurse();
            $saShiftLogger = new SaShiftLogger();
            // send twilio sms
            if ($nurse) {
                $nurseCreds = $nurse->getCredentials();
                $intro = ($recurrence->getStatus() === 'pending' ? 'A ' : 'An ') . strtoupper($recurrence->getStatus());
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $date = $recurrence->getStart()->format('m/d/Y');
                $time = $recurrence->getStart()->format('H:i');

                /** @var saUser $currentUser */
                $currentUser = modRequest::request('sa.user');
                $logBody = $intro . ' Recurring Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' was Deleted by NurseStat';
                $logMessage = $logBody . ' - User: ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                $saShiftLogger->log($logMessage, ['action' => 'DELETED']);

                $recurrence->setNurse(null);
            } else {
                $intro = ($recurrence->getStatus() === 'pending' ? 'A ' : 'An ') . strtoupper($recurrence->getStatus());
                $date = $recurrence->getStart()->format('m/d/Y');
                $time = $recurrence->getStart()->format('H:i');

                /** @var saUser $currentUser */
                $currentUser = modRequest::request('sa.user');
                $logBody = $intro . ' Recurring Shift on ' . $date . ' at ' . $time . ' was Deleted by NurseStat';
                $logMessage = $logBody . ' - User: ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                $saShiftLogger->log($logMessage, ['action' => 'DELETED']);
            }

            app::$entityManager->remove($recurrence);
            app::$entityManager->flush();
            $response['success'] = true;
        }

        return $response;
    }


    /** Load all nurses that have worked with the provider but are not on the DO NOT RETURN list */
    public function loadAssignableNurses($data)
    {
        $id = $data['provider_id'];
        $response = ['success' => false];
        $shiftId = $data['id'];
        $recurrenceId = $data['recurrence_id'];
        $types = strlen($data['nurse_type']) ? explode('/', $data['nurse_type']) : [];

        $start = new DateTime($data['start_date'] . ' ' . $data['start_time'], app::getInstance()->getTimeZone());
        $end = new DateTime($data['start_date'] . ' ' . $data['end_time'], app::getInstance()->getTimeZone());

        if($end < $start) {
            $end->modify('+1 day');
        }

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);

        // NurseStat Admins should be able to assign any nurse to a shift
//        $previousNurses = $provider->getPreviousNurses();
        /** @var NurseRepository $nurseRepo */
        $nurseRepo = ioc::getRepository('Nurse');
        if (in_array('CNA', $types) || $types == 'CNA') {
            if (!is_array($types)) {
                $types = ['CMT', 'CNA'];
            } else {
                if (!in_array('CNA', $types)) {
                    $types[] = 'CMT';
                }
            }
        }
        $allNurses = $nurseRepo->findNursesOfTypes($types);
        $blockedNurses = $provider ? $provider->getBlockedNurses() : null;

        if($recurrenceId) {
            $shift = ioc::get('ShiftRecurrence', ['id' => $recurrenceId]);
        } else {
            $shift = ioc::get('Shift', ['id' => $shiftId]);
        }

        /** @var NurseRepository $nurseRepo */
        $nurseRepo = ioc::getRepository('Nurse');
        /** @var Nurse $nurse */
        foreach($allNurses as $nurse) {
            if(!$blockedNurses || !$blockedNurses->contains($nurse)) {
                // Nurse is disabled if they have a shift in the time period.
                $isAvailable = $nurseRepo->getNurseAvailability($start, $end, $nurse);

                if ($shift && $shift->getNurse() && $shift->getNurse()->getId() == $nurse->getId()) {
                    $isAvailable = true;
                }

                $response['nurses'][] = [
                    'id' => $nurse->getId(),
                    'name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
                    'disabled' => !$isAvailable
                ];

                $response['success'] = true;
            }
        }

        return $response;
    }


    public function loadCategories(){
        $json = new Json();
        $categories = ioc::getRepository('EventsCategory')->findAll();
        $returnArray = [];
        foreach ($categories as $category){
            $returnArray[] = ['value' => $category->getId(), 'text' => $category->getName()];
        }
        $json->data['categories'] = $returnArray;
        $json->data['success'] = true;
        return $json;
    }

    public function loadShiftRequests($data){
        $response = ['success' => false];
        // $shifts = ioc::getRepository('Shift')->findBy(['nurse_approved' => true, 'status' => 'Pending']);
        // $shiftRecurrences = ioc::getRepository('ShiftRecurrence')->findBy(['nurse_approved' => true, 'status' => 'Pending']);

        // ----------
        // TODO move these DQL queries into repository method when removing recurrences from project
        $today = new DateTime();
        $qb = app::$entityManager->createQuery('SELECT s from nst\\events\\Shift s WHERE s.nurse_approved = :nurse_approved AND s.status = :status AND s.start >= :today');
        $qb->setParameters(array(
            'nurse_approved' => true,
            'status' => 'Pending',
            'today' => $today
        ));
        $shifts = $qb->getResult();

        $qb2 = app::$entityManager->createQuery('SELECT sr from nst\\events\\ShiftRecurrence sr WHERE sr.nurse_approved = :nurse_approved AND sr.status = :status AND sr.start >= :today');
        $qb2->setParameters(array(
            'nurse_approved' => true,
            'status' => 'Pending',
            'today' => $today
        ));
        $shiftRecurrences = $qb2->getResult();
        // ----------

        if($shifts || $shiftRecurrences) {
            /** @var Shift $shift */
            foreach($shifts as $shift) {
                if($shift?->getNurse()?->getId()){
                    $response['shifts'][] = [
                        'id' => $shift?->getId(),
                        'is_recurrence' => false,
                        'nurse_id' => $shift?->getNurse()?->getId(),
                        'nurse_name' => $shift?->getNurse()?->getMember()?->getFirstName() . ' ' . $shift->getNurse()?->getMember()?->getLastName(),
                        'nurse_profile' => app::get()?->getRouter()?->generate('edit_nurse', ['id' => $shift?->getNurse()?->getId()]),
                        'provider_id' => $shift?->getProvider()?->getId(),
                        'provider_name' => $shift?->getProvider()?->getMember()?->getFirstName() . ' ' . $shift?->getProvider()?->getMember()?->getLastName(),
                        'provider_profile' => app::get()?->getRouter()?->generate('edit_provider', ['id' => $shift?->getProvider()?->getId()]),
                        'start_time' => $shift?->getStart()?->format('g:i A'),
                        'end_time' => $shift?->getEnd()?->format('g:i A'),
                        'date' => $shift?->getIsEndDateEnabled() ?
                            $shift?->getStart()?->format('m/d/Y') . ' - ' . $shift?->getEnd()?->format('m/d/Y') :
                            $shift?->getStart()?->format('m/d/Y'),
                        'shift_route' => app::get()?->getRouter()?->generate('sa_shift_edit', ['id' => $shift?->getId()]),
                        'shift_name' => $shift->getName(),
                        'sorting_date' => $shift?->getStart()?->format('Y-m-d') . ' ' . $shift?->getStart()?->format('h:i:s'),
                        'is_covid' => $shift?->getIsCovid(),
                        'incentive' => $shift?->getIncentive(),
                    ];
                } else {
                    $response['no_nurse_shifts'][] = $shift?->getId();
                }
            }

            /** @var ShiftRecurrence $shift */
            foreach($shiftRecurrences as $shift) {
                if($shift?->getNurse()?->getId()){
                    $response['shifts'][] = [
                        'id' => $shift->getId(),
                        'is_recurrence' => true,
                        'nurse_id' => $shift?->getNurse()?->getId(),
                        'nurse_name' => $shift?->getNurse()?->getMember()?->getFirstName() . ' ' . $shift?->getNurse()?->getMember()?->getLastName(),
                        'nurse_profile' => app::get()?->getRouter()?->generate('edit_nurse', ['id' => $shift?->getNurse()?->getId()]),
                        'provider_id' => $shift?->getProvider()?->getId(),
                        'provider_name' => $shift?->getProvider()?->getMember()?->getFirstName() . ' ' . $shift?->getProvider()?->getMember()?->getLastName(),
                        'provider_profile' => app::get()?->getRouter()?->generate('edit_provider', ['id' => $shift?->getProvider()?->getId()]),
                        'start_time' => $shift?->getStart()?->format('g:i A'),
                        'end_time' => $shift?->getEnd()?->format('g:i A'),
                        'date' => $shift?->getIsEndDateEnabled() ?
                            $shift?->getStart()?->format('m/d/Y') . ' - ' . $shift?->getEnd()?->format('m/d/Y') :
                            $shift?->getStart()?->format('m/d/Y'),
                        'shift_route' => app::get()?->getRouter()?->generate('edit_recurrence', ['recurrenceId' => $shift?->getId()]),
                        'shift_name' => $shift?->getName(),
                        'sorting_date' => $shift?->getStart()?->format('Y-m-d h:i:s'),
                        'is_covid' => $shift?->getIsCovid(),
                        'incentive' => $shift?->getIncentive(),
                    ];
                } else {
                    $response['no_nurse_recurring_shifts'][] = $shift?->getId();
                }
            }

            usort($response['shifts'], function($a, $b) {
                $aDate = new DateTime($a['sorting_date']);
                $bDate = new DateTime($b['sorting_date']);
                if ($aDate >= $bDate) {
                    return 1;
                } else {
                    return -1;
                }
            });

            $response['success'] = true;
        }

        return $response;
    }


    public function approveShiftRequest($data)
    {
        $id = $data['id'];
        $is_recurrence = $data['is_recurrence'] === "true" || $data['is_recurrence'] === true;
        $response = ['success' => false];

        try {
            $shift = null;
            if($is_recurrence) {
                $shift = ioc::get('ShiftRecurrence', ['id' => $id]);
            } else {
                $shift = ioc::get('Shift', ['id' => $id]);
            }

            if ($shift) {
                $shiftService = new ShiftService();
                $rfcsResponse = $shiftService->removeFromConflictingShifts($shift->getNurse(), $shift, 'NurseStat');

                // If success is false, likely can't remove the nurse from a shift starting in the next 2 hours
                if(!$rfcsResponse['success']){
                    return $response;
                }
            }

            $notificationData = [
                'nurse' => $shift->getNurse(),
                'type' => 'Approved',
                'is_recurrence' => $is_recurrence,
                'shift' => $shift
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);

            // send twilio sms
            $nurse = $shift->getNurse();
            if ($nurse) {
                $this->smsService->handleSendSms($shift, ['message_type' => 'approve_request', 'by' => 'siteadmin']);

                $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');
                $smsBody = 'APPROVED SHIFT - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been APPROVED by NurseStat';

                // log
                app::get()->getLogger()->addError('sa.approveShiftRequest: ' . $smsBody);
            }

            /** @var Provider $provider */
            $provider = $shift->getProvider();
            
            if(!$nurse->getPreviousProviders()->contains($provider)) {
                $nurse->addPreviousProvider($provider);
            }
            if(!$provider->getPreviousNurses()->contains($nurse)) {
                $provider->addPreviousNurse($nurse);
            }

            $shift->setIsProviderApproved(true);
            $shift->setStatus('Approved');
            app::$entityManager->flush();

            $service = new PayrollService();
            $service->initializeShiftRates($shift);
            $response['success'] = true;
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function denyShiftRequest($data)
    {
        $id = $data['id'];
        $is_recurrence = $data['is_recurrence'] == "true" || $data['is_recurrence'] === true;
        $response = ['success' => false];

        try {
            $shift = null;
            if($is_recurrence) {
                $shift = ioc::get('ShiftRecurrence', ['id' => $id]);
            } else {
                $shift = ioc::get('Shift', ['id' => $id]);
            }

            $nurse = $shift->getNurse();

            // send twilio sms
            if ($nurse) {
                $this->smsService->handleSendSms($shift, ['message_type' => 'deny_request', 'by' => 'siteadmin']);

                $nurseCreds = $nurse->getCredentials();
                $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');
                $smsBody = 'DENIED SHIFT - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been DENIED by NurseStat';
                // log
                app::get()->getLogger()->addError('sa.denyShiftRequest: ' . $smsBody);

                /** @var saUser $currentUser */
                $currentUser = modRequest::request('sa.user');
                $saShiftLogger = new SaShiftLogger();
                $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' was Denied by NurseStat';
                $logMessage = $logBody . ' - User: ' . $currentUser->getFirstName() . ' ' . $currentUser->getLastName();
                $saShiftLogger->log($logMessage, ['action' => 'DENIED']);
            }

            $shift->setIsProviderApproved(false);
            $shift->setIsNurseApproved(false);
            $shift->setNurse(null);
            $shift->setStatus('Open');

            $notificationData = [
                'nurse' => $nurse,
                'type' => 'Denied',
                'is_recurrence' => $is_recurrence,
                'shift' => $shift
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);

            app::$entityManager->flush();
            $response['success'] = true;
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;

    }

    public function massDeleteShifts($data) {
        $response = ['success' => false];
        $events = $data['events'];
        $successful = false;

        foreach($events as $event) {
            if($event['is_recurrence'] == 'true' || $event['is_recurrence'] === true) {
                $d = [
                    'id' => $event['id'],
                    'recurrence_id' => $event['id'],
                    'unique_id' => $event['unique_id'],
                ];

                $successful = self::deleteShiftRecurrence($d);
            } else {
                $d = [
                    'id' => $event['id']
                ];

                $successful = self::deleteShift($d);
            }

            if (!$successful) {
                return $response;
            }
        }
        if ($successful) {
            $response = ['success' => true];
        }

        return $response;
    }

    /**
     * NOT A REAL RECURRENCE OBJ, GENERATES A NORMAL SHIFT
     * @throws ORMException
     * @throws IocDuplicateClassException
     * @throws ModRequestAuthenticationException
     * @throws OptimisticLockException
     * @throws IocException
     * @throws ValidateException
     * @throws TransactionRequiredException
     * @throws \Exception
     */
    public function createNewShiftRecurrence($data, mixed $occurrence, $recurrenceEndDate, $rrule): void
    {
        app::get()->getLogger()->addError('createNewShiftRecurrence - $occurrence: ' . json_encode($occurrence));
        $ns_startTime = new DateTime($data['start_time']);
        $ns_startDate = new DateTime($occurrence);
        $ns_endTime = new DateTime($data['end_time']);
        $ns_endDate = new DateTime($occurrence);
        $ns_start = new DateTime($ns_startDate->format('Y-m-d') . ' ' . $data['start_time']);

        if ($ns_endTime < $ns_startTime) {
            $ns_endDate->modify('+1 day');
        }
        $ns_end = new DateTime($ns_endDate->format('Y-m-d') . ' ' . $data['end_time']);

        $newShift = ioc::resolve('Shift');
        $newShift->setBonusAmount($data['bonus_amount'])
            ->setBonusDescription($data['bonus_description'])
            ->setIsEndDateEnabled(false)
            ->setNurseType($data['nurse_type'])
            ->setRecurrenceType($data['recurrence_type'])
            ->setDescription($data['description'])
            ->setName($data['name'])
            ->setStartTime($ns_startTime)
            ->setStartDate($ns_startDate)
            ->setEndTime($ns_endTime)
            ->setEndDate($ns_endDate)
            ->setStart($ns_start)
            ->setEnd($ns_end)
            ->setIsCovid($data['is_covid'] == 'Yes')
            ->setIncentive($data['incentive']);

        // Save Category
        $category = ioc::get('Category', ['id' => $data['category_id']]);
        if (!$category) {
            throw new ValidateException('Unable to find category');
        }
        $newShift->setCategory($category);

        //Set Provider as owner of the shift
        $newShift->setProvider( ioc::get('Provider', ['id' => $data['provider_id']]) );
        $newShift->setUntilDate($recurrenceEndDate);
        $newShift->setRecurrenceRules($rrule->getString());

        app::$entityManager->persist($newShift);
        app::$entityManager->flush($newShift);

        self::assignNurseToShift($newShift, $data['nurse_id'], true);
    }

    public function callInShift($data): array
    {
        $response = ['success' => false];
        $shiftService = new ShiftService();
        $shift = ioc::get('Shift', ['id' => $data['id']]);
        if (!$shift) {
            return $response;
        }
        $data['provider_id'] = $shift->getProvider()->getId();
        $data['nurse_type'] = $shift->getNurseType();
        $data['start_date'] = $shift->getStart()->format('Y-m-d');
        $data['start_time'] = $shift->getStart()->format('H:i:s');
        $data['end_date'] = $shift->getStart()->format('Y-m-d');
        $data['end_time'] = $shift->getEnd()->format('H:i:s');


        $nurse = $shift->getNurse();
        if (!$nurse) {
            return $response;
        }
        
        $this->smsService->handleSendSms($shift, ['message_type' => 'call_in', 'by' => 'siteadmin']);

        /** @var saUser $currentUser */
        $currentUser = modRequest::request('sa.user');
        $saShiftLogger = new SaShiftLogger();
        $nurse_name = $nurse->getFirstName() . $nurse->getLastName();
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $user_name = $currentUser->getFirstName() . $currentUser->getLastName();
        $logBody = "CALL IN: Shift for $nurse_name on $date at $time was CALLED IN and has been reposted";
        $logMessage = "$logBody - SA User: $user_name";
        $saShiftLogger->log($logMessage, ['action' => 'CALL-IN']);

        $shift->setIsProviderApproved(false);
        $shift->setIsNurseApproved(false);
        $shift->setNurse(null);
        $shift->setStatus('Open');

        app::$entityManager->flush($shift);

        $shiftService->NotifyPreferredNurses($data);
        
        $response['success'] = true;
        return $response;
    }
}
