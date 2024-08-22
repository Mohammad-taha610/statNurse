<?php


namespace nst\events;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use nst\member\NstFile;
use nst\member\NstMember;
use nst\member\NstMemberUsers;
use nst\member\Nurse;
use nst\member\Provider;
use nst\member\ProviderPayRate;
use nst\member\ProviderService;
use nst\messages\SmsService;
use nst\payroll\PayrollPayment;
use nst\payroll\PayrollService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Recurr\Rule;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Request;
use sacore\application\ValidateException;
use sacore\utilities\doctrineUtils;
use sa\events\Category;
use sa\events\Event;
use sa\files\saFile;
use sa\member\auth;
use sa\system\saUser;
use When\When;
use nst\messages\NstPushNotificationService;


/**
 * Class ShiftService
 * @package nst\events
 */
class ShiftService
{
    /** @var ShiftRepository $shiftRepository */
    protected $shiftRepository;

    /** @var \DateTimeZone $timezone */
    protected $timezone;

    /** @var \SaShiftLogger $shiftLogger */
    protected $shiftLogger;

    /** @var \SmsService $smsService */
    protected $smsService;

    public function __construct()
    {
        $this->shiftLogger = new SaShiftLogger();
        $this->shiftRepository = ioc::getRepository('Shift');
        $this->timezone = app::getInstance()->getTimeZone();
        $this->smsService = new SmsService();
    }

    public function logShiftAction($message, $action)
    {
        $member = auth::getAuthMember();
        if (!$member) {
            return;
        }
        $memberType = $member->getMemberType();
        $memberName = '';
        if ($memberType === 'Provider') {
            $memberName = $member->getProvider()->getName();
        } else if ($memberType === 'Nurse') {
            $memberName = $member->getNurse()->getFirstName() . ' ' . $member->getNurse()->getLastName();
        }
        $this->shiftLogger->log($message . ' By ' . $memberName . '(' . $memberType . ')', ['action' => $action]);
    }

    public function loadShiftData($data)
    {
        $id = $data['id'];
        $response = ['success' => false];

        /** @var NstMemberUsers $user */
        $user = auth::getAuthUser();
        if ($id) {
            /** @var Shift $shift */
            $shift = ioc::get('Shift', ['id' => $id]);
            $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();

            if ($shift) {
                $startTime = $shift->getStart();
                $endTime = $shift->getEnd();
                $now = new DateTime('now', app::getInstance()->getTimeZone());
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
                    'recurrence_end_date' => $shift->getRecurrenceEndDate() ? $shift->getRecurrenceEndDate()->format('Y-m-d') : '',
                    'recurrence_interval' => $shift->getRecurrenceInterval(),
                    'is_covid' => $shift->getIsCovid(),
                    'incentive' => $shift->getIncentive(),
                    'status' => $shift->getStatus(),
                    'allow_editing' => $shift->getStart() > $now
                ];

                $response['success'] = true;
            }
        }

        // Get last allowed date to create shifts
        $today = new DateTime('now', app::getInstance()->getTimeZone());
        $today->modify('+5 weeks');
        $payrollService = new PayrollService();
        $period = $payrollService->calculatePayPeriodFromDate($today);
        $response['max_start_date'] = $period['end']->format('Y-m-d');
        $response['bonus_allowed'] = $user->getUserType() == 'Admin' || $user->getBonusAllowed();
        $response['covid_allowed'] = $user->getUserType() == 'Admin' || $user->getCovidAllowed();

        return $response;
    }


    public function NotifyPreferredNurses($data) {
        // create provider service to retrieve Assignable Nurses
        $providerService = new ProviderService();

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id'=>$data['provider_id']]);
        // Assignable Nurses data for provider_id , nurse_type, etc
        $assignableNurses = $providerService->loadAssignableNurses($data);
        $availableNurses = $assignableNurses['nurses'];
        $pushNotificationService = new NstPushNotificationService();

        try{
            // iterate through each available Nurse in Provider Available Nurses
            foreach($availableNurses as $nurse){
                // instantiate Nurse with available Nurse ID
                $nurseData = ioc::get('Nurse', ['id' => $nurse['id']]);

                // Check if Nurse contains this Provider as a Preferred provider, and if Nurse recieves Push Notifications.
                if($nurseData->getPreferredProviders()->contains($provider) && $nurseData->getReceivesPushNotification()){
                    $nurseSmsBody = "Your preferred Provider " . $provider->getMember()->getCompany() . " Has created a shift at " . $data['start_time'] . " on " . $data['start_date'];
                    $data['title'] = "New Shift";
                    $data['message'] = $nurseSmsBody;
                    $data['nurse_id'] = $nurse['id'];

                    $pushNotificationService->sendNotificationToNurse($data);
                    app::get()->getLogger()->addError($nurseSmsBody);
                }

            }
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/nstmessages.txt', 'all is well' . PHP_EOL, FILE_APPEND);
        }catch(Exception $error){
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/nstmessages.txt', (new DateTime())->format('Y-m-d h:i-s') . ' ' . $error->getMessage() . PHP_EOL, FILE_APPEND);

            app::get()->getLogger()->addError($error->getMessage());
        }
    }

    /**
     * Saves changes to a shift, or creation of a new shift
     * @throws ValidateException
     * @throws \Exception
     */
    public function saveShift($data, $providerId = null, $isCommand = false)
    {
        app::get()->getLogger()->addError('Provider - save shift, data: ' . json_encode($data));
        $id = $data['id'];
        $response = ['success' => false];
        $previous_status = $data['shift_status'];

        /** @var Shift $shift */
        $shift = null;
        if ($id) {
            $shift = ioc::get('Shift', ['id' => $id]);
        }
        if (!$shift) {
            $shift = ioc::resolve('Shift');
        }

        if ($shift->getStatus() == 'Completed') {
            throw new ValidateException('This shift has already been completed');
        }

        $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();

        /* KNewton 8/2/2021
         * Changed shifts to use Start and End rather than StartDate, StartTime, EndDate, EndTime
         * I kept the old way in, so I hopefully don't break anything
         */
        $startTime = new DateTime($data['start_time']);
        $startDate = new DateTime($data['start_date']);
        $endTime = new DateTime($data['end_time']);
        $endDate = new DateTime($data['start_date']);
        if ($endTime < $startTime) {
            $endDate->modify('+1 day');
        }
        $start = new DateTime($data['start_date'] . ' ' . $data['start_time']);
        $end = new DateTime($endDate->format('Y-m-d') . ' ' . $data['end_time']);

        // make sure shift does not cover more than 16 hours
        $hourdiff = ceil((strtotime($end) - strtotime($start)) / 3600);
        if ($hourdiff > 16) {
            throw new ValidateException('Cannot create a shift spanning more than 16 hours');
        }

        /**
         * Check if shift time overlaps times from today, if so deny saveShift
         */
        $today = $shift->getStart() ? $shift->getStart() : new DateTime('today');
        $nurse = ioc::get('Nurse', ['id' => $data['nurse_id']]);

        if ($nurse) {
            $sameDayShifts = $this->shiftRepository->getShiftsForNurse($nurse, $today)['all'];
            /** @var Shift $sdShift */
            foreach ($sameDayShifts as $sdShift) {
                if (!$sdShift->getIsRecurrence()) {
                    if ($sdShift->getStartTime() === $startTime || $sdShift->getEndTime() === $startTime) {
                        // drop $shift?
                        $response['message'] = "An existing shift already exists for this Nurse at the given time and date";
                        return $response; // which is success => false at this point
                    }
                }
                if ($this->isConflicting($start, $end, $sdShift->getStart(), $sdShift->getEnd())) {
                    $response['message'] = "An existing shift already exists for this Nurse at the given time and date";
                    return $response; // which is success => false at this point
                }
            }
        }

        // Set initial shift data
        $shift->setBonusAmount($data['bonus_amount'])
            ->setBonusDescription($data['bonus_description'])
            ->setIsEndDateEnabled(false)
            ->setNurseType($data['nurse_type'])
            ->setRecurrenceType($data['recurrence_type'])
            ->setDescription($data['description'])
            ->setName($data['name'])
            ->setStartTime($startTime)
            ->setStartDate($startDate)
            ->setEndTime($endTime)
            ->setEndDate($endDate)
            ->setStart($start)
            ->setEnd($end)
            ->setIsCovid($data['is_covid'] == 'Yes')
            ->setIncentive($data['incentive']);

        // Save Category
        $category = ioc::get('Category', ['id' => $data['category_id']]);
        if (!$category) {
            throw new ValidateException('Unable to find category');
        }
        $shift->setCategory($category);

        if ($providerId != null) {
            $provider = ioc::get('Provider', ['id' => $providerId]);
            if (!$provider) {
                throw new ValidateException('Unable to find provider');
            }
            $shift->setProvider($provider);
        }
        else {
            //Set Provider as owner of the shift
            /** @var NstMember $member */
            $member = auth::getAuthMember();
            if (!$member || !$member->getProvider() || $member->getMemberType() != 'Provider') {
                throw new ValidateException('Unable to authenticate user');
            }
            $shift->setProvider($member->getProvider());
        }

        if (!$id) {
            app::$entityManager->persist($shift);
        }

        // Recurrence
        if ($data['recurrence_type'] != 'None' && $data['recurrence_type'] != 'Custom') {

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

            $utcstartdate = new \DateTime($shift->getStart()->format('Ymd G:i:s', true));
            $utcenddate = new \DateTime($shift->getEnd()->format('Ymd G:i:s', true));

            $rrule = new Rule(null, $utcstartdate, $utcenddate, $timezone);

            $frequency = 'DAILY';
            if (!$id) {
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

            self::assignNurseToShift($shift, $data['nurse_id'], $data['action_type']);

            if (!$id) {
                // create shifts based on recurrence rules
                $_start = new DateTime($data['start_date'] . ' ' . $data['start_time'], app::getInstance()->getTimeZone());
                $_end = new DateTime($data['recurrence_end_date'] . ' ' . $data['end_time'], app::getInstance()->getTimeZone());
                $_interval = $data['recurrence_interval'];
                $_type = $data['recurrence_type']; // Daily, Weekly, Monthly, Custom
                $_diff = $_start->diff($_end);
                // $startTime -- referred above
                // $endTime -- referred above

                if ($_type === 'Custom') {
                    app::$entityManager->persist($shift); // persist the main shift that we copied
                }

                // daily recurrences
                if ($_type !== 'Weekly' && $_type !== 'Monthly') {
                    // normal for loop for daily recurrences
                    for ($r = 0; $r <= $_diff->format('%a'); $r += $_interval) {
                        //app::get()->getLogger()->addError('daily recurrence - day: ' . $r . ', interval: ' . $_interval . ', type: ' . $_type . ', diff: ' . $_diff->format('%a days'));
                        // create a shift like above, only with the modified startDate based on the loop day
                        $occurrence_dt = new DateTime($data['start_date']);
                        $occurrence = $occurrence_dt->modify('+' . $r . ' day');

                        //app::get()->getLogger()->addError('daily recurrence - $occurrence: ' . json_encode($occurrence));
                        $this->createNewShiftRecurrence($data, $occurrence, $_end, $rrule, $isCommand);
                    }
                    app::$entityManager->remove($shift); // done with initial shift, lets delete it
                }
                // weekly recurrences & monthly recurrences
                if ($_type === 'Weekly' || $_type === 'Monthly') {
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
                }

                //app::get()->getLogger()->addError('weekly recurrence - interval: ' . $_interval . ', type: ' . $_type . ', diff: ' . $_diff->format('%a days') . ', recurrence rules: ' . $_rrule);
                //app::get()->getLogger()->addError('weekly recurrence - occurrences: ' . json_encode($w->occurrences));
                // for loop based on Weekly recurrences using $_options
                foreach ($w->occurrences as $occurrence) {
                    // each $occurrence has a date, timezone_type, timezone
                    $recurrenceStart = new DateTime($occurrence, app::getInstance()->getTimeZone());
                    //app::get()->getLogger()->addError('weekly recurrence - recurrence start: ' . $recurrenceStart);
                    if ($_start !== $recurrenceStart) {
                        $this->createNewShiftRecurrence($data, $occurrence, $_end, $rrule, $isCommand);
                    }
                }
                //$shift->setMaxRecurrences(count($w->occurrences));
                app::$entityManager->remove($shift); // done with initial shift, lets delete it
            }
            // end: recurrence rules issues
        } else {
            $shift->setRecurrenceOptions(null);
            $shift->setRecurrenceEndDate(null);
            $shift->setRecurrenceInterval(1);

            // Assign a nurse (only if not recurring shift)
            // They will have to individually assign nurses to each recurring shift
            if ($shift->getStatus() == 'Pending') {
                if ($data['approve_nurse']) {
                    self::approveShiftRequest([
                        'id' => $id,
                        'is_recurrence' => false
                    ]);
                } else if ($data['deny_nurse']) {
                    self::denyShiftRequest([
                        'id' => $id,
                        'is_recurrence' => false
                    ]);
                }
            }
            self::assignNurseToShift($shift, $data['nurse_id'], $data['action_type']);
        }

        if (!$id) {
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
                    $customShift->setStartTime($startTime);
                    $customShift->setStartDate($customStartDate);
                    $customShift->setEnd($customEnd);
                    $customShift->setEndTime($endTime);
                    $customShift->setEndDate($customStartDate);
                    app::$entityManager->persist($customShift);

                    if ($data['nurse_id']) {
                        self::assignNurseToShift($customShift, $data['nurse_id'], false);
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
        $data['provider_id'] = $shift->getProvider()->getId();
        $inFiveDays = (new DateTime())->getTimestamp() + 432000;
        if ($shift->getStart()->getTimeStamp() < $inFiveDays && $shift->getStatus() == 'Open') {
            $this->NotifyPreferredNurses($data);
        }
        app::$entityManager->flush();
        $response['shift_id'] = $shift->getId();
        $response['success'] = true;
        $logMessage = 'Shift for ' . $startDate->format('m/d/y') . ' at ' . $startDate->format('H:i') . ' has been saved ';
        if (!$isCommand) {
            self::logShiftAction($logMessage, 'SAVED');
        }

        return $response;
    }

    /**
     * @param Shift $shift
     * @param integer $nurseId
     */
    public function assignNurseToShift($shift, $nurseId, $isCreation)
    {
        if ($nurseId && $shift->getNurse() && $shift->getNurse()->getId() == $nurseId) {
            return;
        }
        if ($shift->getStatus() == SHIFT::STATUS_COMPLETED) {
            throw new ValidateException('This shift is not eligible for a nurse assignment');
        }

        if (!$nurseId) {
            $shift->setNurse(null);
            $shift->setStatus(Shift::STATUS_OPEN);
            $shift->setIsProviderApproved(false);
            $shift->setIsNurseApproved(false);
        } else {
            $nurse = ioc::get('Nurse', ['id' => $nurseId]);

            $smsService = new SmsService();
            if ($nurse && !$isCreation) {
                $nurseCreds = $nurse->getCredentials();
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');
                $status = $shift->getStatus() ? $shift->getStatus() : '';
                $intro = ($status === 'Pending' ? 'A ' : 'An ') . ucfirst($status);
                $intro = $intro ?: 'A';
                $by = 'the provider';
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $providerSmsBody = 'ASSIGNED SHIFT - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been ASSIGNED by ' . $by;
                $provider = $shift->getProvider();

                if ($provider) {
                    // Nurse SMS
                    $nurse_provider = $provider->getName() ?: 'your provider';
                    $nurseSmsBody = 'ASSIGNED SHIFT - ' . $intro . ' Shift for ' . $nurse_provider . ' on ' . $date . ' at ' . $time . ' was ASSIGNED to you.';

                    if ($nurse->getPhoneNumber() && $nurse->getReceivesSMS() && ($shift->getId() && $this->checkIfShiftShouldSendSMS($shift->getId()))) {
                        modRequest::request('messages.startSMSBatch');
                        modRequest::request('messages.sendSMS', array('phone' => $nurse->getPhoneNumber(), 'body' => $nurseSmsBody));
                        modRequest::request('messages.commitSMSBatch');
                    }
                }

                $_provider = $provider->getName() ?: 'a provider';
                $logBody = $intro . ' Shift for ' . $_provider . ' for the nurse '. $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' was Assigned.';
                $logMessage = $logBody . ' - User: ' . $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $this->shiftLogger->log($logMessage, ['action' => 'ASSIGNED']);
            } elseif ($nurse && $isCreation) {
                $smsService->handleSendSms($shift, ['message_type' => 'assign_shift', 'by' => 'provider', 'nurse' => $nurse]);
            }

            $nurse->addShift($shift);
            $shift->setNurse($nurse);
            $shift->setStatus('Assigned');
            $shift->setIsProviderApproved(true);
            $shift->setIsNurseApproved(false);
            $notificationData = [
                'nurse' => $nurse,
                'type' => 'Assigned',
                'is_recurrence' => false,
                'shift' => $shift
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);

            $nurseCreds = $nurse->getCredentials();
            $status = empty($shift->getStatus()) ? 'New' : $shift->getStatus();
            $intro = ($status === 'Pending' || $status === 'New' ? 'A ' : 'An ') . ucfirst($status);
            $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $date = $shift->getStart()->format('m/d/Y');
            $time = $shift->getStart()->format('H:i');
            $_provider = $shift->getProvider()->getName() ?: 'a provider';
            $logBody = $intro . ' Shift for ' . $_provider . ' for the nurse '. $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been Assigned during creation';
            $logMessage = $logBody . ' - User: ' . $nurse_name;
            $this->shiftLogger->log($logMessage, ['action' => 'CREATED']);
            $providerName = $shift->getProvider()->getName() ?: 'a provider';
            self::logShiftAction('Nurse assigned to a shift from ' . $providerName . ' provider on '  . $date . ' at ' . $time . ' has been assigned ', 'ASSIGNED');

            $service = new PayrollService();
            $service->initializeShiftRates($shift);
        }
    }

    /**
     * Gets all shifts within a given range
     */
    public function loadCalendarShifts($data)
    {
        $data['backend'] = false;
        $shiftRecurrences = $this->parseShiftDataForCalendar($data);
        $response = [];
        $response['events'] = [];
        $memberId = $data['member_id'];
        /** @var NstMember $member */
        $member = ioc::get('NstMember', ['id' => $memberId]);
        /** @var Nurse $nurse */
        $nurse = null;
        if ($member && $member->getMemberType() == 'Nurse') {
            $nurse = $member->getNurse();
        }

        if ($data['calendar_type'] == 'month') {
            $response['success'] = true;
            $response['calendar_type'] = 'month';
            foreach ($shiftRecurrences as $date => $counts) {
                $response['shifts'][] = [
                    'date' => $date,
                    'counts' => $counts
                ];
            }
            return $response;
        }

        $providerService = new ProviderService();
        /** @var Shift $shift */
        foreach ($shiftRecurrences as $shiftRecurrence) {

            if ($shiftRecurrence['id']) {
                if (is_array($shiftRecurrence['nurse_type'])) {
                    $nurse_type_string = implode(', ', $shiftRecurrence['nurse_type']);
                } else {
                    $nurse_type_string = $shiftRecurrence['nurse_type'];
                }

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
                $startTime = $shiftRecurrence->getStart();
                $endTime = $shiftRecurrence->getEnd();

                $totalHours = date_diff($startTime, $endTime)->h;

                $hourlyRate = 0;

                $provider = $shift->getProvider();
                if ($nurse != null) {
                    $hourlyRate = $providerService->calculatePayRate([
                        'shift' => $shift,
                        'provider' => $provider,
                        'nurse_type' => $nurse?->getCredentials(),
                        'rate_type' => 'Standard',
                        'is_covid' => $shift->getIsCovid(),
                        'incentive' => $shift->getIncentive(),
                        'pay_or_bill' => 'Pay'
                    ]);
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
                    'start' => $shiftRecurrence['start'],
                    'start_date' => $shiftRecurrence['start_date'],
                    'start_time' => $shiftRecurrence['start_time'],
                    'shift_type' => 'Recurring Shift',
                    'end' => $shiftRecurrence['end'],
                    'end_date' => $shiftRecurrence['end_date'],
                    'end_time' => $shiftRecurrence['end_time'],
                    'nurse_name' => $shiftRecurrence['nurse_name'],
                    'nurse_id' => $shiftRecurrence['nurse_id'],
                    'nurse_route' => $shiftRecurrence['nurse_route'],
                    'nurse_type' => $shiftRecurrence['nurse_type'],
                    'nurse_type_string' => $nurse_type_string,
                    'parent_id' => $shiftRecurrence['parent_id'],
                    'parent_unique_id' => $shiftRecurrence['parent_unique_id'],
                    'is_covid' => $shiftRecurrence['is_covid'],
                    'incentive' => $shiftRecurrence['incentive'],
                    'is_recurrence' => $shiftRecurrence['is_recurrence'],
                    'bonus_display' => $bonusDisplay,
                    'bonus' => $shiftRecurrence['bonus_amount'],
                    'covid_display' => $covidDisplay,
                    'covid_pay' => $shiftRecurrence['is_covid'],
                    'incentive_display' => $incentiveDisplay,
                    'provider_id' => $shiftRecurrence['provider_id'],
                    'provider_name' => $shiftRecurrence['provider_name'],
                    'category_id' => $shiftRecurrence['category_id'],
                    'category_name' => $shiftRecurrence['category_name'],
                    'hourly_rate' => $hourlyRate,
                    'total_pay' => $hourlyRate * $totalHours
                ];
            } else {
                $shift = ioc::get('Shift', ['id' => $shiftRecurrence['event_id']]);

                $shiftType = 'Standard Shift';
                if ($shift->getRecurrenceType() && $shift->getRecurrenceType() != 'None') {
                    $shiftType = 'Recurring Shift';
                    $route = $shiftRecurrence['url'];
                } else {
                    $route = app::get()->getRouter()->generate('edit_shift', ['id' => $shiftRecurrence['event_id']]);
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

                $startTime = $shift->getStart();
                $endTime = $shift->getEnd();
                if (is_array($shift->getNurseType())) {
                    $nurse_type_string = implode(', ', $shift->getNurseType());
                } else {
                    $nurse_type_string = $shift->getNurseType();
                }
                $hourlyRate = 0;

                $provider = $shift->getProvider();
                if ($nurse != null) {
                    $hourlyRate = $providerService->calculatePayRate([
                        'shift' => $shift,
                        'provider' => $provider,
                        'nurse_type' => $nurse?->getCredentials(),
                        'rate_type' => 'Standard',
                        'is_covid' => $shift->getIsCovid(),
                        'incentive' => $shift->getIncentive(),
                        'pay_or_bill' => 'Pay'
                    ]);
                }
                $totalHours = date_diff($startTime, $endTime)->h;
                $shiftData = [
                    'id' => $shiftRecurrence['event_id'],
                    'recurrence_id' => 0,
                    'route' => $route,
                    'shift_route' => app::get()->getRouter()->generate('edit_shift', ['id' => $shiftRecurrence['event_id']]),
                    'copy_route' => $shiftRecurrence['copy_route'] ?: app::get()->getRouter()->generate('copy_shift', ['id' => $shiftRecurrence['event_id']]),
                    'unique_id' => $shiftRecurrence['unique_id'],
                    'is_recurrence' => $shiftRecurrence['is_recurrence'],
                    'parent_unique_id' => $shiftRecurrence['parent_unique_id'],
                    'name' => ($shift->getNurseType() ? '[' . $shift->getNurseType() . '] ' : '') . ($shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() : ''),
                    'status' => $shift->getStatus(),
                    'start_time_formatted' => $startTime->format('g:i a'),
                    'end_time_formatted' => $endTime->format('g:i a'),
                    'start' => $shiftRecurrence['start'],
                    'start_date' => explode('T', $shiftRecurrence['start'])[0],
                    'start_time' => $startTime->format('H:i:s'),
                    'end' => $shiftRecurrence['end'],
                    'end_date' => $shift->getIsEndDateEnabled() ? explode('T', $shiftRecurrence['end'])[0] : explode('T', $shiftRecurrence['start'])[0],
                    'end_time' => $endTime->format('H:i:s'),
                    'nurse_name' => $shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() : '',
                    'nurse_id' => $shift->getNurse() ? $shift->getNurse()->getId() : null,
                    'nurse_route' => $shift->getNurse() ? app::get()->getRouter()->generate('nurse_profile', ['id' => $shift->getNurse()->getId()]) : '',
                    'shift_type' => $shiftType,
                    'nurse_type' => $shift->getNurseType(),
                    'nurse_type_string' => $nurse_type_string,
                    'is_covid' => $shift->getIsCovid(),
                    'incentive' => $shift->getIncentive(),
                    'parent_id' => $shift->getParentId(),
                    'bonus_display' => $bonusDisplay,
                    'covid_display' => $covidDisplay,
                    'incentive_display' => $incentiveDisplay,
                    'provider_id' => $shift->getProvider()->getId(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'category_id' => $shift?->getCategory()?->getId(),
                    'category_name' => $shift?->getCategory()?->getName(),
                    'hourly_rate' => $hourlyRate,
                    'estimated_pay' => $hourlyRate * $totalHours
                ];
            }

            $response['shifts'][] = $shiftData;
        }

        return $response;
    }

    public function deleteShift($data)
    {
        $id = $data['id'];
        $response = ['success' => false];

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);
        if ($shift) {

            $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            $date = $shift->getStart()->format('m/d/Y');
            $time = $shift->getStart()->format('H:i');
            $logBody = $intro . ' Shift on ' . $date . ' at ' . $time . ' has been Deleted by NurseStat';
            $nurse = $shift->getNurse();
            $logMessage = $logBody;
            $this->shiftLogger->log($logMessage, ['action' => 'DELETED']);

            // $logBody = $intro . ' Shift on ' . $date . ' at ' . $time . ' has been Deleted by ' . $currentUser->getLastName(). ', '.$currentUser->getFirstName();
            // $logMessage = $logBody;
            // self::logShiftAction($logMessage, 'DELETED');

            if ($shift->getStatus() == 'Pending') {
                $denyResponse = static::denyShiftRequest([
                    'id' => $id,
                    'is_recurrence' => false
                ]);
            } else {
                $SmsService = new SmsService();
                $SmsService->handleSendSms($shift, ['message_type' => 'deleted_shift', 'by' => 'provider']);
            }

            $recurrences = ioc::getRepository('ShiftRecurrence')->findBy(['event' => $shift]);

            foreach ($recurrences as $recurrence) {
                app::$entityManager->remove($recurrence);
                app::$entityManager->flush();
            }

            app::$entityManager->remove($shift);
            app::$entityManager->flush();
            $response['success'] = true;
        }
        return $response;
    }

    public function approveShiftRequest($data)
    {
        $id = $data['id'];
        $is_recurrence = $data['is_recurrence'] == "true";
        $response = ['success' => false];

        try {
            $shift = ioc::get('Shift', ['id' => $id]);
            $SmsService = new SmsService();
            $SmsService->handleSendSms($shift, ['message_type' => 'approve_request', 'by' => 'provider']);

            /** @var NstMemberUsers $user */
            $currentUser = auth::getAuthUser();

            $shift->setIsProviderApproved(true);
            $shift->setStatus('Approved');
            $service = new PayrollService();
            $service->initializeShiftRates($shift);
            
            $provider = $shift?->getProvider();
            if ($provider) {

                $providerName = $shift->getProvider()->getName();
                if ($providerName == "") {
                    $providerName = $shift->getProvider()->getMember()->getCompany();
                }
                $providerMessage = ' at facility: ' . $providerName;
            } else {
                $providerMessage = ' which has no assigned provider';
            }

            $nurse = $shift?->getNurse();
            if ($nurse) {
                $nurseMessage = ' for nurse: ' . $nurse->getFirstName().' '. $nurse->getLastName();
            } else {
                $nurseMessage = ' which has no assigned nurse';
            }

            $logMessage = 'Shift ' . $shift->getStartDate()->format('m/d/y') . ' at ' . $shift->getStartDate()->format('H:i') . $providerMessage . $nurseMessage . ' has been Approved';
            self::logShiftAction($logMessage, 'APPROVED');
            $log_msg = 'Shift ' . $shift->getStartTime() . ' Approved by ' . $currentUser?->getLastName() . ', ' . $currentUser?->getFirstName();
            $this->shiftLogger->log($log_msg, ['action'=>'APPROVED']);
            app::$entityManager->flush();
            $response['success'] = true;
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function denyShiftRequest($data)
    {
        $id = $data['id'];
        $is_recurrence = $data['is_recurrence'] == "true";
        $response = ['success' => false];

        try {
            $shift = ioc::get('Shift', ['id' => $id]);
            $SmsService = new SmsService();
            $SmsService->handleSendSms($shift, ['message_type' => 'deny_request', 'by' => 'provider']);

            $shift->setIsProviderApproved(false);
            $shift->setIsNurseApproved(false);
            $shift->setNurse(null);
            $shift->setStatus(Shift::STATUS_OPEN);
            $logMessage = 'Shift ' . $shift->getStartDate()->format('m/d/y') . ' at ' . $shift->getStartDate()->format('H:i') . ' has been Denied ';
            self::logShiftAction($logMessage, 'DENIED');
            app::$entityManager->flush();
            $response['success'] = true;
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * @param Shift $shift
     */
    public function getShiftClockedHours($shift)
    {

        $clockInTime = $shift->getClockInTime();
        $clockOutTime = $shift->getClockOutTime();

        $seconds = $clockOutTime->getTimestamp() - $clockInTime->getTimestamp();
        $hours = $seconds / 3600;

        $lunchHours = 0;
        if ($lunchOverride = $shift->getLunchOverride()) {
            $lunchHours = $lunchOverride / 60;
        } elseif ($shift->getLunchStart() && $shift->getLunchEnd()) {
            $lunchStartTime = $shift->getLunchStart();
            $lunchEndTime = $shift->getLunchEnd();
            $lunchSeconds = $lunchEndTime->getTimestamp() - $lunchStartTime->getTimestamp();
            $lunchHours = $lunchSeconds / 3600;
        }

        return $hours - $lunchHours;
    }

    /**
     * @param Shift $shift
     */
    public function getShiftPaymentAmount($shift)
    {
        if (!$shift || !$shift->getNurse() || !$shift->getClockInTime() || !$shift->getClockOutTime()) {
            return null;
        }

        $hours = static::getShiftClockedHours($shift);

        return $shift->getHourlyRate() * $hours;
    }

    /**
     * @param Shift $shift
     */
    public function getShiftBillingAmount($shift)
    {
        if (!$shift || !$shift->getNurse() || !$shift->getClockInTime() || !$shift->getClockOutTime()) {
            return null;
        }

        $hours = static::getShiftClockedHours($shift);

        $rate = $shift->getBillingRate() ?: ($shift->getHourlyRate() + 8);
        return $rate * $hours;
    }

    public static function loadCalendarFilters($data)
    {
        $response = ['success' => false];

        $nurses = ioc::getRepository('Nurse')->findAll();
        $categories = ioc::getRepository(ioc::staticResolve('\sa\events\Category'))->findAll();

        if ($nurses || $categories) {
            /** @var Nurse $nurse */
            foreach ($nurses as $nurse) {
                if (!$nurse->getMember() || $nurse->getMember()->getIsDeleted()) {
                    continue;
                }
                $response['nurses'][] = [
                    'id' => $nurse->getId(),
                    'name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
                    'nurse_type' => $nurse->getCredentials()
                ];
            }
            /** @var Category $category */
            foreach ($categories as $category) {
                $response['categories'][] = [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ];
            }

            $response['success'] = true;
        }

        return $response;
    }

    public function massDeleteShifts($data)
    {
        $response = ['success' => true];
        $events = $data['events'];

        foreach ($events as $event) {
            $d = [
                'id' => $event['id']
            ];

            $response['success'] = self::deleteShift($d) && $response['success'];
        }

        return $response;
    }


    public function getShiftData($data, $member = null)
    {
        $isRecurrence = $data['is_recurrence'];
        $shiftId = $data['shift_id'];
        $date = new DateTime($data['date_string'], app::getInstance()->getTimeZone());

        $uniqueId = '';
        $shiftData = [];
        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);
        $uniqueId = '';

        // All of the methods are the same between recurrence and shift so they can be used interchangeably
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

        if ($member) {
            $nurse = $member->getNurse();

            /** @var ProviderService $providerService */
            $providerService = new ProviderService();
            $hourlyRate = $providerService->calculatePayRate([
                'shift' => $shift,
                'provider' => $shift->getProvider(),
                'nurse_type' => $nurse->getCredentials(),
                'rate_type' => 'Standard',
                'is_covid' => $shift->getIsCovid(),
                'incentive' => $shift->getIncentive(),
                'pay_or_bill' => 'Pay'
            ]);
        } else {
            $hourlyRate = $shift->getHourlyRate();
        }

        $returnData = [
            'id' => $shiftId,
            'provider_name' => $shift->getProvider()->getMember()->getCompany(),
            'unique_id' => $uniqueId,
            'start' => $shift->getStart()->format('Y-m-d\TH:i:s'),
            'end' => $shift->getEnd()->format('Y-m-d\TH:i:s'),
            'status' => $shift->getStatus(),
            'description' => $shift->getDescription(),
            'is_recurrence' => $isRecurrence,
            'bonus_description' => $shift->getBonusDescription(),
            'bonus_display' => $bonusDisplay,
            'covid_display' => $covidDisplay,
            'incentive_display' => $incentiveDisplay,
            'category_id' => $shift->getCategory()->getId(),
            'provider_id' => $shift->getProvider()->getId(),
            'hours' => round((strtotime($shift->getEnd()->format('Y-m-d\TH:i:s')) - strtotime($shift->getStart()->format('Y-m-d\TH:i:s'))) / 3600, 1),
            'hourly_rate' => $hourlyRate,
            'nurse_id' => $shift->getNurse() ? $shift->getNurse()->getId() : ''
        ];

        return $returnData;
    }

    public function requestShift($data)
    {
        $response = ['success' => false];
        $shiftId = $data['shift_id'];
        $nurseId = $data['nurse_id'];
        $providerService = new ProviderService();
        $user_type = $data['user_type'] ?: '';

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurseId]);
        if (!$nurse) {
            $nurse = $shift->getNurse();
        }

        $rfcsResponse = $this->removeFromConflictingShifts($nurse, $shift, $user_type);

        // If success is false, likely can't remove the nurse from a shift starting in the next 2 hours
        if(!$rfcsResponse['success']){
            return $response;
        }

        $shift->setNurse($nurse);
        $shift->setStatus(Shift::STATUS_PENDING);
        $shift->setIsNurseApproved(true);

        $provider = $shift->getProvider();
        if (!$provider->getPayRates()) {
            $providerService->initializePayRates($provider);
        }

        if (!$nurse->getPreviousProviders()->contains($provider)) {
            $nurse->addPreviousProvider($provider);
        }
        if (!$provider->getPreviousNurses()->contains($nurse)) {
            $provider->addPreviousNurse($nurse);
        }

        $nurse->addShift($shift);
        app::$entityManager->flush();

        $service = new PayrollService();
        $service->initializeShiftRates($shift);

        // Send sms to nurse and/or provider
        // This also logs to the SaShiftLog
        $this->smsService->handleSendSms($shift, ['message_type' => 'request_shift', 'by' => $user_type, 'nurse' => $nurse]);

        $response['id'] = $shift->getId();
        $response['success'] = true;
        return $response;
    }

    public function cancelShift($data)
    {
        $response = ['success' => false];
        $shiftId = $data['shift_id'];
        // This is poor design, providers should not be using the same logic as nurses - This will be stripped out into a provider cancel shift method
        $user_type = $data['user_type'] ?: 'provider';

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);

        // Do not allow cancelling within 2 hours of shift start time
        // UPDATED: Do not allow cancelling of approved shifts AT ALL
        if ($shift->getStatus() == 'Approved') {
            return $response;
        }

        // send twilio sms
        $nurse = $shift->getNurse();
        if ($nurse) {
            $nurseCreds = $nurse->getCredentials();
            $date = $shift->getStart()->format('m/d/Y');
            $time = $shift->getStart()->format('H:i');

            $providerBy = $user_type === 'NurseStat' ? 'NurseStat' : 'the ' . $user_type;
            $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $provider = $shift->getProvider();

            $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            if ($user_type === 'provider') {
                $providerBy = $provider->getName();
            }
            $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been Cancelled by ' . $providerBy;
            $this->shiftLogger->log($logBody, ['action' => 'CANCELLED']);

            // Nurse SMS
            $nurseIntro = 'Your ' . ucfirst($shift->getStatus());
            $nurseBy = $user_type === 'nurse' ? 'you.' : 'the ' . $user_type;
            $nurseSmsBody = 'CANCELLED SHIFT - ' . $nurseIntro . ' Shift for ' . $provider->getName() . ' on ' . $date . ' at ' . $time . ' has been CANCELLED by ' . $nurseBy;
            if ($nurse->getPhoneNumber() && $nurse->getReceivesSMS()) {
                modRequest::request('messages.startSMSBatch');
                modRequest::request('messages.sendSMS', array('phone' => $nurse->getPhoneNumber(), 'body' => $nurseSmsBody));
                modRequest::request('messages.commitSMSBatch');
                app::get()->getLogger()->addError('cancelShift (nurse): ' . $nurseSmsBody);
            }
        }

        static::assignNurseToShift($shift, null, false);
        $logMessage = $intro . ' Shift for ' . $nurse_name . ' (' . $nurseCreds . ') on ' . $date . ' at ' . $time . ' has been Cancelled ';
        self::logShiftAction($logMessage, 'CANCELLED');
        app::$entityManager->flush();

        $response['status'] = 'Open';
        $response['success'] = true;
        return $response;
    }

    public function declineShift($data)
    {
        $response = ['success' => false];
        $shiftId = $data['shift_id'];
        $isRecurrence = $data['is_recurrence'];
        $uniqueId = $data['unique_id'];
        $nurseId = $data['nurse_id'];
        $user_type = $data['user_type'] ?: 'provider';

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);

        // send twilio sms
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurseId]);
        if ($shift && $nurse) {
            $nurseCreds = $nurse->getCredentials();
            $by = $user_type === 'NurseStat' ? 'NurseStat' : 'the ' . $user_type;
            $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $date = $shift->getStart()->format('m/d/Y');
            $time = $shift->getStart()->format('H:i');
            $provider = $shift->getProvider();

            // Nurse SMS
            if ($nurse->getPhoneNumber() && $nurse->getReceivesSMS()) {
                $providerName = $provider->getName() ? $provider->getName() : 'a provider';
                $nurseSmsBody = 'DECLINED SHIFT - ' . $intro . ' Shift for ' . $providerName . ' on ' . $date . ' at ' . $time . ' has been DECLINED by ' . $by;
                modRequest::request('messages.startSMSBatch');
                modRequest::request('messages.sendSMS', array('phone' => $nurse->getPhoneNumber(), 'body' => $nurseSmsBody));
                modRequest::request('messages.commitSMSBatch');
                app::get()->getLogger()->addError('declineShift (nurse): ' . $nurseSmsBody);
            }
        }

        static::assignNurseToShift($shift, null, false);

        $logMessage = 'Shift for ' . $nurse_name . ' (' . $nurseCreds . ') on ' . $date . ' at ' . $time . ' has been Declined ';
        self::logShiftAction($logMessage, 'DECLINED');
        app::$entityManager->flush();

        $response['status'] = 'Open';
        $response['success'] = true;
        return $response;
    }

    public function acceptShift($data)
    {
        $response = ['success' => false];
        $id = $data['shift_id'];
        $nurse_id = $data['nurse_id'];
        $user_type = $data['user_type'] ?: 'provider';

        if ($data['shift']) {
            $shift = $data['shift'];
        } else {
            $shift = ioc::get('Shift', ['id' => $id]);
        }

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurse_id]);

        if ($shift && $nurse) {
            if (!$shift->getProvider() || !$shift->getIsProviderApproved()) {
                return $response;
            }
            $rfcsResponse = $this->removeFromConflictingShifts($nurse, $shift, $user_type);

            // If success is false, likely can't remove the nurse from a shift starting in the next 2 hours
            if(!$rfcsResponse['success']){
                return $response;
            }

            $nurseCreds = $nurse->getCredentials();

            // send twilio sms
            $date = $shift->getStart()->format('m/d/Y');
            $time = $shift->getStart()->format('H:i');

            $by = $user_type === 'NurseStat' ? 'NurseStat' : 'the ' . $user_type;
            $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $providerSmsBody = 'ACCEPTED SHIFT - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been ACCEPTED by ' . $by;
            $provider = $shift->getProvider();
            if ($provider) {

                // Nurse SMS
                $nurseIntro = ($shift->getStatus() === 'Pending' ? 'a ' : 'an ') . ucfirst($shift->getStatus());
                $nurseSmsBody = 'ACCEPTED SHIFT - You have accepted ' . $nurseIntro . ' Shift for ' . $provider->getName() . ' on ' . $date . ' at ' . $time;
                if ($nurse->getPhoneNumber() && $nurse->getReceivesSMS()) {
                    modRequest::request('messages.startSMSBatch');
                    modRequest::request('messages.sendSMS', array('phone' => $nurse->getPhoneNumber(), 'body' => $nurseSmsBody));
                    modRequest::request('messages.commitSMSBatch');
                    app::get()->getLogger()->addError('acceptShift (nurse): ' . $providerSmsBody);
                }
            }

            if ($user_type === 'provider') {
                $by = $provider->getName();
            }
            $logMessage = 'Shift for ' . $nurse_name . ' (' . $nurseCreds . ') on ' . $date . ' at ' . $time . ' has been Accepted by ';
            self::logShiftAction($logMessage, 'ACCEPTED');

            $shift->setNurse($nurse);
            $shift->setStatus(Shift::STATUS_APPROVED);
            $shift->setIsNurseApproved(true);

            app::$entityManager->flush();

            $service = new PayrollService();
            $service->initializeShiftRates($shift);

            $response['status'] = 'Approved';
            $response['success'] = true;
        }

        return $response;
    }

    public function findCurrentShiftForNurse($nurse)
    {
        try {
            return ioc::getRepository('Shift')->findCurrentShift($nurse);
        } catch (Exception $e) {
            echo "Exception in ShiftService->findCurrentShiftForNurse\n";
            echo "<pre>" . \Doctrine\Common\Util\Debug::dump($e->getMessage(), 3) . "</pre>";
            exit;
        }
    }

    public static function clockIn($data)
    {
        file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/willtest.txt.log', print_r($data, true) . PHP_EOL, FILE_APPEND);
        $response = ['success' => false];
        $id = $data['shift_id'];

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);

        //validate and set clock in type on shift
        $clock_in_type = $data['clock_in_type'];
        if ($clock_in_type == "natural" || $clock_in_type == "manual") {
            $shift->setClockInType($clock_in_type);
        }

        // Don't override clock in time if they click the button a second time
        if (!$shift->getClockInTime()) {
            $now = new DateTime('now', app::getInstance()->getTimeZone());
            $shift->setClockInTime($now);
            app::$entityManager->flush($shift);
        }

        // log
        $nurse = $shift->getNurse();
        if ($nurse) {
            $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
            $now = new DateTime('now', app::getInstance()->getTimeZone());
            $provider = $shift->getProvider();
            if ($provider) {
                $log_msg = sprintf('%s has clocked in at %s for the %s provider', $nurse_name, $now, $provider->getName());
                app::getInstance()->getLogger()->info('clockIn: ' . $log_msg);
            }
        }

        $response['success'] = true;
        return $response;
    }

    public function clockOut($data)
    {
        $response = ['success' => false];
        $id = $data['shift_id'];

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);

        if ($this->isOnBreak($shift)) {
            $response['message'] = 'You cannot clock out while on break.';
            $response['success'] = false;
            return $response;
        }

        if ($data['lunch_override']) {
            $shift->setLunchOverride($data['lunch_override']);
        }

        $end = $shift->getEnd();
        if ($shift->getEnd() < $shift->getStart()) {
            $end->modify('+1 day');
        }

        $now = new DateTime('now', app::getInstance()->getTimeZone());
        $shift->setClockOutTime($data['automatic_clock_out'] ? $end : $now);
        $shift->setStatus(Shift::STATUS_COMPLETED);
        $shift->setClockoutComment($data['clockout_comment']);

        if ($data['clocked_out_early']) {
            $shift->setClockedOutEarly(true);
            $shift->setEarlyClockOutReason($data['early_clock_out_reason']);
        }

        try {
            // store time slip photo
            $saf = new saFile();
            /** @var NstFile $currentFile */
            $timeslipFile = $saf->saveStringToFile($id . 'timeslip.png', base64_decode($data['timeslip']));
            app::$entityManager->persist($timeslipFile);
            $shift->setTimeslip($timeslipFile);
        }
        catch (\Exception $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/timeslip.log',   $e->getMessage() . PHP_EOL, FILE_APPEND);
        }

        app::$entityManager->flush();

        $payrollService = new PayrollService();
        $payments = $payrollService->createShiftPayment($shift);
        if (!$payments) return $response;

        // Check for clock in/out over 30 minutes before/after start/end
        $clockInDiff = $shift->getClockInTime()->diff($shift->getStart());
        // $clockInMinutes = $clockInDiff->days * 24 * 60;
        $clockInMinutes = $clockInDiff->h * 60;
        $clockInMinutes += $clockInDiff->i;

        $clockOutDiff = $shift->getClockOutTime()->diff($shift->getEnd());
        // $clockOutMinutes = $clockOutDiff->days * 24 * 60;
        $clockOutMinutes = $clockOutDiff->h * 60;
        $clockOutMinutes += $clockOutDiff->i;

        if ($clockInMinutes > 50 || $clockOutMinutes > 50) {
            foreach ($payments as $payment) {
                $payment->setStatus('Unresolved');
                $payment->setRequestDescription('Over 50 minutes early or late.');
            }
            app::$entityManager->flush();
        }

        if ($data['confirmation_message']) {
            foreach ($payments as $payment) {
                $payment->setRequestDescription($data['confirmation_message']);
                $payment->setStatus('Unresolved');
            }
            app::$entityManager->flush();
        }

        if ($data['automatic_clock_out']) {
            foreach ($payments as $payment) {
                $payment->setStatus('Unresolved');
                $payment->setRequestDescription('Automatic Clock Out');
            }
            app::$entityManager->flush();
        }

        if ($payments) {
            $response['success'] = true;

            // log
            $nurse = $shift->getNurse();
            if ($nurse) {
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $now = new DateTime('now', app::getInstance()->getTimeZone());
                $provider = $shift->getProvider() ? $shift->getProvider()->getMember()->getCompany() : '';
                if ($provider) {
                    $log_msg = sprintf('%s has clocked out at %s for the %s provider', $nurse_name, $now, $provider);
                    app::getInstance()->getLogger()->info('clockOut: ' . $log_msg);
                }
            }
        }

        return $response;
    }

    public static function startLunch($data)
    {
        $response = ['success' => false];
        $id = $data['shift_id'];

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);
        $now = new DateTime('now', app::getInstance()->getTimeZone());
        $shift->setLunchStart($now);
        app::$entityManager->flush($shift);

        $response['success'] = true;
        return $response;
    }

    public static function endLunch($data)
    {
        $response = ['success' => false];
        $id = $data['shift_id'];

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);
        $now = new DateTime('now', app::getInstance()->getTimeZone());
        $shift->setLunchEnd($now);
        app::$entityManager->flush($shift);

        $response['success'] = true;
        return $response;
    }

    /**
     * Determines if a shift is conflicting with an approved shift
     * @param $s1
     * @param $e1
     * @param $s2
     * @param $e2
     * @returns bool
     */
    public function isConflicting($s1, $e1, $s2, $e2)
    {
        $start1 = new DateTime($s1, $this->timezone);
        $end1 = new DateTime($e1, $this->timezone);
        $start2 = new DateTime($s2, $this->timezone);
        $end2 = new DateTime($e2, $this->timezone);
        if ($start1 >= $start2 && $start1 <= $end2)
            return true;
        if ($start1 <= $start2 && $end1 >= $start2)
            return true;
        return false;
    }

    public function getDashboardData($data)
    {
        $response = ['success' => false];
        $nurse = ioc::get('Nurse', ['id' => $data['nurse_id']]);

        /** @var Shift $shift */
        $shift = static::findCurrentShiftForNurse($nurse);

        if ($shift) {
            $start = $shift->getStart();
            $end = $shift->getEnd();
            $clockIn = $shift->getClockInTime();
            $response['current_shift'] = [
                'id' => $shift->getId(),
                'is_recurrence' => $shift->getIsRecurrence(),
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                'provider_id' => $shift->getProvider()->getId(),
                'status' => $shift->getStatus(),
                'clock_in_display' => $shift->getClockInTime() ? $clockIn->format('g:i a') : '',
                'has_clocked_in' => (bool)$shift->getClockInTime(),
                'has_clocked_out' => (bool)$shift->getClockOutTime(),
                'has_started_lunch' => (bool)$shift->getLunchStart(),
                'has_finished_lunch' => (bool)$shift->getLunchEnd(),
                'clock_in_type' => (string)$shift->getClockInType(),
                'is_on_break' => $shift->getIsOnBreak() ?? false,
                'break_start_time' => $shift->getBreakStartTime() ? $shift->getBreakStartTime()->format('g:i a') : '',
                'has_taken_break' => $shift->getHasTakenBreak() ?? false,
            ];
        }

        $response['success'] = true;
        return $response;
    }

    /**
     * @param int $id
     * @param Nurse $nurse
     */
    public function isValidForNurse($id, $nurse)
    {
        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);

        if (!$nurse->getSkinTestExpirationDate() || $nurse->getSkinTestExpirationDate() < $shift->getStart()) {
            return false;
        }

        if (!$nurse->getLicenseExpirationDate() || $nurse->getLicenseExpirationDate() < $shift->getStart()) {
            return false;
        }

        // LPN/RN cannot work without a valid CPR license
        if ($nurse->getCredentials() == 'LPN' || $nurse->getCredentials() == 'RN') {
            if (!$nurse->getCprExpirationDate() || $nurse->getCprExpirationDate() < $shift->getStart()) {
                return false;
            }
        }

        // Credentials Check
        $nurseTypes = explode('/', trim($shift->getNurseType()));
        if (!in_array($nurse->getCredentials(), $nurseTypes)) {
            if($nurse->getCredentials() == 'CMT') {
                if (!in_array('CNA', $nurseTypes)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        // Vaccine check
        if($shift->getProvider()->getRequiresCovidVaccine() && !$nurse->hasVaccineCard()) {
            return false;
        }

        // Filter out shifts that are over 200 miles away
        $nurseZipCode = ioc::get('saPostalCode', ['code' => $nurse->getZipcode()]);
        if ($shift->getStatus() == 'Open') {
            $provider = $shift->getProvider();
            $providerZipCode = ioc::get('saPostalCode', ['code' => $provider->getZipcode()]);
            if ($nurseZipCode && $providerZipCode) {
                $meters = $providerZipCode->getDistance($nurseZipCode);
                $distance = $meters * 0.00062137;
                if ($distance > 200) {
                    file_put_contents('/var/www/virtual/nursestat.elinkstaging.com/tmp/distance-log.txt', 'distance: ' . $distance . PHP_EOL, FILE_APPEND);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param Nurse $nurse
     * @param Shift $shift
     * @param string user_type
     * @throws \Exception
     */
    public function removeFromConflictingShifts($nurse, $shift, $user_type = 'provider')
    {
        $response = ['success' => true];
        // Remove from any shifts on the same day
        $today = $shift->getStart();
        $sameDayShifts = $this->shiftRepository->getShiftsForNurse($nurse, $today)['all'];

        foreach ($sameDayShifts as $sameDayShift) {
            if ($sameDayShift === $shift) {
                continue;
            }

            $d = [
                'shift_id' => $sameDayShift->getId(),
                'is_recurrence' => $sameDayShift->getIsRecurrence(),
                'unique_id' => $sameDayShift->getIsRecurrence() ? $sameDayShift->getRecurrenceUniqueId() : '',
                'user_type' => $user_type,
                'nurse_id' => $sameDayShift->getNurse()->getId()
            ];

            if ($sameDayShift->getStatus() === Shift::STATUS_ASSIGNED) {
                $dsResponse = $this->declineShift($d);
            } else {
                $csResponse = $this->cancelShift($d);

                if(!$csResponse['success']){
                    $response['success'] = false;
                }
            }
        }

        return $response;
    }

    public function automaticClockOutCron()
    {
        $response = ['success' => false];

        $now = new DateTime('now', app::getInstance()->getTimeZone());
        file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', PHP_EOL . PHP_EOL . 'Starting job at: ' . $now->format('g:i a') . PHP_EOL, FILE_APPEND);
        $shifts = $this->shiftRepository->getAutomaticClockOutShifts();

        /** @var Shift $shift */
        foreach ($shifts['all'] as $shift) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', 'Clocking Out for ' . ($shift->getIsRecurrence() ? 'Recurrence ' : 'Shift') . ': ' . $shift->getId() . PHP_EOL, FILE_APPEND);

            $shiftData = [
                'shift_id' => $shift->getId(),
                'is_recurrence' => $shift->getIsRecurrence(),
                'lunch_override' => null,
                'automatic_clock_out' => true,
            ];
            $this->clockOut($shiftData);
        }

        try {
            app::$entityManager->flush();
            $response['success'] = true;
        } catch (\Throwable $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', 'ERROR:' . PHP_EOL, FILE_APPEND);
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/automatic_clock_out_log.txt', $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
        return $response;
    }

    public function overtimeHoursFix()
    {
        $response = ['success' => false];

        $path = app::get()->getConfiguration()->get('tempDir') . '/NurseStatOvertime1.xlsx';

        set_time_limit(7200);

        ini_set('memory_limit', '512M');

        if (!$handle = fopen($path, 'r')) {
            echo "Unable to open excel file\n\r";
            exit;
        }

        $column_array = ['A', 'B', 'C'];

        $sheet_number = 0;
        $start_row = 0;
        $num_rows = 56;
//        $sheet_names = ['Pay Rates', 'Bill Rates', '1.5 incentive PAY Rate', '1.5 incentive BILL Rate', '2 incentive PAY Rate', '2 Incentive BILL Rate', 'Facility Address'];

        $payOrBill = ['pay', 'bill', 'Pay', 'Bill', 'Pay', 'Bill', 'Travel'];
        $incentives = [1, 1, 1.5, 1.5, 2, 2, 0];

        /** @var IReader $reader */
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $reader->setLoadAllSheets();

        $worksheet = $spreadsheet->getActiveSheet();

        for ($i = 1; $i < $num_rows; $i++) {
            try {

                $cellValues = [
                    'provider' => $worksheet->getCell('A' . $i)->getValue(),
                    'nurse' => $worksheet->getCell('B' . $i)->getValue(),
                    'hours' => $worksheet->getCell('C' . $i)->getValue()
                ];

                /** @var NstMember $providerMember */
                $providerMember = ioc::get('saMember', ['company' => $cellValues['provider']]);
                /** @var Provider $provider */
                $provider = $providerMember->getProvider();

                $first = explode(' ', $cellValues['nurse'])[0];
                $first = str_replace(' ', '', $first);
                $last = explode(' ', $cellValues['nurse'])[1];
                $last = str_replace(' ', '', $last);
                if ($last == 'St') $last = 'St Jean';
                /** @var Nurse $nurse */
                $nurse = ioc::get('Nurse', ['first_name' => $first, 'last_name' => $last]);

                $payRate1 = $provider->getPayRates()[$nurse->getCredentials()]['standard_pay'];
                $billRate1 = $provider->getPayRates()[$nurse->getCredentials()]['standard_bill'];
                $payRate = (float)$payRate1 / 2;
                $billRate = (float)$billRate1 / 2;

                $payTotal = (float)$cellValues['hours'] * $payRate;
                $billTotal = (float)$cellValues['hours'] * $billRate;

                echo "Paying " . $first . ' ' . $last . ': ' . $payRate1 . '/2   ->   ' . $payRate . ' * ' . $cellValues['hours'] . ' = ' . $payTotal . "\n";
                echo "Billing " . $cellValues['provider'] . ': ' . $billRate1 . '/2   ->   ' . $billRate . ' * ' . $cellValues['hours'] . ' = ' . $billTotal . "\n";


                /** @var Shift $shift */
                $shift = ioc::resolve('Shift');


                /** @var PayrollPayment $payment */
                $payment = ioc::resolve('PayrollPayment');
                app::$entityManager->persist($payment);

                $clockIn = new DateTime('2022/01/09 06:00:00', app::getInstance()->getTimeZone());
                $clockOut = new DateTime('2022/01/09 06:01:00', app::getInstance()->getTimeZone());


                $shift->setProvider($provider);
                $shift->setNurse($nurse);
                $shift->setStartDate($clockIn);
                $shift->setEndDate($clockIn);
                $shift->setStart($clockIn);
                $shift->setEnd($clockOut); // Doing this before modifying so that it doesn't span over 1 day on the calendar


                if ($clockOut < $clockIn) {
                    $clockOut->modify('+1 day');
                }

                $shift->setClockInTime($clockIn);
                $shift->setClockOutTime($clockOut);
                $shift->setStatus('Completed');
                $shift->setLunchOverride(0);
                $shift->setBonusAmount(0);
                $shift->setHourlyRate($payRate);
                $shift->setBillingRate($billRate);
                $shift->setIsNurseApproved(true);
                $shift->setIsProviderApproved(true);
                $shift->setNurseType($nurse->getCredentials());
                app::$entityManager->persist($shift);

                $payment->setClockedHours($cellValues['hours']);
                $payment->setPayRate($payRate);
                $payment->setPayBonus(0);
                $payment->setPayTravel(0);
                $payment->setPayTotal($payTotal);
                $payment->setBillRate($billRate);
                $payment->setBillBonus(0);
                $payment->setBillTravel(0);
                $payment->setBillTotal($billTotal);
                $payment->setPaymentMethod($nurse->getPaymentMethod());
                $payment->setPaymentStatus('Unpaid');
                $payment->setStatus('Approved');
                $payment->setType('Overtime');
                app::$entityManager->persist($payment);

                $payment->setShift($shift);
                $shift->setPayrollPayment($payment);

                app::$entityManager->flush();
            } catch (\Throwable $e) {
                echo $e->getMessage() . "\n";
            }
        }
        echo "Finished";
        exit;

        $response['success'] = true;
        return $response;
    }

    public function getUpcomingShiftsForNurse($data)
    {
        $response = ['success' => false];

        $nurseId = $data['nurse_id'];
        $nurse = ioc::get('Nurse', ['id' => $nurseId]);


        // Get any shifts for the next week[
        $today = new DateTime('now', app::getInstance()->getTimezone());
        for ($i = 0; $i < 7; $i++) {
            $date = new DateTime($today->format('Y-m-d'), app::getInstance()->getTimeZone());
            $date->modify('+' . $i . ' days');
            // Get all shifts for each day
            $shifts = $this->shiftRepository->getShiftsForNurse($nurse, $date)['all'];
            if (!$shifts) continue;

            /** @var Shift $shift */
            foreach ($shifts as $shift) {
                $response['upcoming_shifts'][] = [
                    'id' => $shift->getId(),
                    'is_recurrence' => $shift->getIsRecurrence(),
                    'status' => $shift->getStatus(),
                    'start' => $shift->getStart()->format('Y-m-d H:i:s'),
                    'end' => $shift->getEnd()->format('Y-m-d H:i:s'),
                    'provider_id' => $shift->getProvider()->getId(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'clock_in_display' => $shift->getClockInTime() ? $shift->getClockInTime()->format('g:i a') : '',
                    'has_clocked_in' => (bool)$shift->getClockInTime(),
                    'has_clocked_out' => (bool)$shift->getClockOutTime(),
                    'has_started_lunch' => (bool)$shift->getLunchStart(),
                    'has_finished_lunch' => (bool)$shift->getLunchEnd()
                ];
            }
        }

        $response['success'] = true;
        return $response;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     */
    public function checkIfShiftShouldSendSMS($shiftId): bool
    {
        if ($shiftId) {
            /** @var Shift $shift */
            $shift = ioc::get('Shift', ['id' => $shiftId]);

            if ($shift) {
                $now = new DateTime('today');
                $start = $shift->getStart();

                if ($start->diff($now)->days <= 5) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * NOT AN ACTUAL ShiftRecurrence OBJECT
     * @throws ORMException
     * @throws IocDuplicateClassException
     * @throws ModRequestAuthenticationException
     * @throws OptimisticLockException
     * @throws IocException
     * @throws ValidateException
     * @throws TransactionRequiredException
     * @throws \Exception
     */
    public function createNewShiftRecurrence($data, mixed $occurrence, $recurrenceEndDate, $rrule, $isCommand = false): void
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
        if (!$data['fromSA'] && !$isCommand) {
            /** @var NstMember $member */
            $member = auth::getAuthMember();

            if (!$member || !$member->getProvider() || $member->getMemberType() != 'Provider') {
                throw new ValidateException('Unable to authenticate user - 2');
            }
            $newShift->setProvider($member->getProvider());
        } else {
            $newShift->setProvider(ioc::get('Provider', ['id' => $data['provider_id']]));
        }
        $newShift->setUntilDate($recurrenceEndDate);
        $newShift->setRecurrenceRules($rrule->getString());
        // $logMessage = 'Shift Reoccurrance created at ' . $newShift->getStartDate()->format('m/d/y') . 'has been created ';
        // self::logShiftAction($logMessage, 'REOCCURANCE');
        app::$entityManager->persist($newShift);
        app::$entityManager->flush($newShift);

        self::assignNurseToShift($newShift, $data['nurse_id'], true);
    }

    public function mergeShifts($data)
    {
        $response = ['success' => false];
        $primaryNurse = ioc::get('Nurse', ['id' => $data['primaryNurseId'], 'is_deleted' => [null, false]]);
        $duplicateNurse = ioc::get('Nurse', ['id' => $data['duplicateNurseId'], 'is_deleted' => [null, false]]);

        if (!is_object($primaryNurse) || !is_object($duplicateNurse)) {
            $response['message'] = 'Error locating nurses.';
            return $response;
        }

        $duplicateNurseShifts = $this->shiftRepository->getAllShiftsForNurse($duplicateNurse)['all'];

        $migratedCount = 0;
        try {
            foreach ($duplicateNurseShifts as $shift) {
                $isRecurrence = $shift->getIsRecurrence() ? ' RECURRENCE' : '';
                file_put_contents(
                    app::get()->getConfiguration()->get('tempDir') . '/nurse_merge_log.txt',
                    'Shift ID: ' . $shift->getId() . $isRecurrence .
                        ' - migrated from nurse ID: ' . $data['duplicateNurseId'] . ' TO nurseId: ' . $data['primaryNurseId'] . PHP_EOL,
                    FILE_APPEND
                );
                $shift->setNurse($primaryNurse);
                $migratedCount++;
            }
            app::$entityManager->flush();
        } catch (Exception $e) {
            $response['duplicateShiftCount'] = 'NONE';
            $response['message'] = $e->getMessage();
            $response['success'] = false;
            return $response;
        }

        $response['duplicateShiftCount'] = $migratedCount;
        $response['success'] = true;
        return $response;
    }

    public function migrateRecurrencesToShifts()
    {
        $response = ['success' => false];

        $app = app::get();
        $io = $app->getCliIO();
        $io->title('Migrate recurrences to shifts');

        // DID YOU RUN THIS BY ACCIDENT?
        $selectField = $io->ask('Are you sure you want to run the migration script y/n', 'y');
        if (strtolower($selectField) != 'y') {
            $io->section(' Exiting.');
            return $response;
        }

        $io->section('Beginning...');
        // GET COUNT OF TEMPLATE SHIFTS
        $dailyShiftCount = $this->shiftRepository->getShiftsByRecurrenceType(['Daily'], true);
        $weeklyShiftCount = $this->shiftRepository->getShiftsByRecurrenceType(['Weekly'], true);
        $customShiftCount = $this->shiftRepository->getShiftsByRecurrenceType(['Custom'], true);
        $shiftRecurrenceRepository = ioc::getRepository('ShiftRecurrence');
        $recurrenceesCount = $shiftRecurrenceRepository->search(null, null, null, null, true);
        // $io->writeln($dailyShiftCount . ' Daily Template shifts to handle.');
        // $io->writeln($weeklyShiftCount . ' Weekly Template shifts to handle.');
        // $io->writeln($customShiftCount . ' Custom Template shifts to handle.');
        $io->writeln($recurrenceesCount . ' recurrences.');
        $continue = $io->ask('Continue? y/n', 'y');
        if (strtolower($continue) != 'y') {
            $io->section(' Exiting.');
            return $response;
        }

        // $this->migrateDailyRecurrences();
        // $this->migrateWeeklyRecurrences();
        // $this->migrateCustomRecurrences();
        $this->migrateJustRecurrences();

        $response['success'] = true;
        $io->writeln('Exiting Successfully.');
        return $response;
    }

    public function migrateDailyRecurrences()
    {
        $app = app::get();
        $io = $app->getCliIO();
        // GET THE FULL LIST OF TEMPLATE SHIFTS
        $templateShifts = $this->shiftRepository->getShiftsByRecurrenceType(['Daily']);

        $count = 0;
        // PRIMARY WORKER LOOP
        foreach ($templateShifts as $key => $templateShift) {
            // WE MIGHT NEED THE ARRAY VALUES
            $shiftDataArray = \sacore\utilities\doctrineUtils::getEntityArray($templateShift);

            // $io->writeln('Template shift# ' . $key . ' ID: ' . $shiftDataArray['id']);
            //Get any shiftRecurrences for this shift
            $shiftRecurrenceRepository = ioc::getRepository('ShiftRecurrence');
            $templateShiftRecurrences = $shiftRecurrenceRepository->findBy(['event' => $shiftDataArray['id']]);

            $startDate = new DateTime($shiftDataArray['start_date']);
            $untilDate = new DateTime($shiftDataArray['until_date']);
            do {
                // $io->writeln('Start Date: ' . $startDate->format('Y-m-d') . ' Until Date: ' . $untilDate->format('Y-m-d'));
                $recurrenceExists = false;
                /** @var Shift $newShift */
                $newShift = ioc::resolve('Shift');
                // ----- TRY TO MATCH AN RECURRENCE TO CURRENT DATE WITHIN RANGE -----
                if (count($templateShiftRecurrences)) {
                    foreach ($templateShiftRecurrences as $recurrence) {
                        if ($recurrence->getStart()->format('Y-m-d') == $startDate->format('Y-m-d')) {
                            //    CREATE SHIFT USING RECURRENCE INFORMATION HERE
                            $ns_startTime = $shiftDataArray['start_time'];
                            $ns_startDate = $startDate;
                            $ns_endTime = $shiftDataArray['end_time'];
                            $ns_endDate = $ns_startDate; // NOT SURE IF THIS NEEDS ADJUSTING
                            $ns_start = $recurrence->getStart();
                            $ns_end = $recurrence->getEnd();
                            if ($ns_endTime < $ns_startTime) {
                                $ns_endDate->modify('+1 day');
                            }
                            // SET ALL THE THINGS
                            $newShift->setBonusAmount($recurrence->getBonusAmount())
                                ->setBonusDescription($recurrence->getBonusDescription())
                                ->setIsEndDateEnabled(false)
                                ->setNurseType($recurrence->getNurseType())
                                ->setRecurrenceType('None')
                                ->setDescription($recurrence->getDescription())
                                ->setName($recurrence->getName())
                                ->setStartTime($ns_startTime)
                                ->setStartDate($ns_startDate)
                                ->setEndTime($ns_endTime)
                                ->setEndDate($ns_endDate)
                                ->setStart($ns_start)
                                ->setEnd($ns_end)
                                ->setCategory($recurrence->getCategory())
                                ->setProvider($recurrence->getProvider())
                                ->setNurse($recurrence->getNurse())
                                ->setFrequency('Daily-migrated')
                                ->setTimezone($recurrence->getTimezone())
                                ->setLunchStart($recurrence->getLunchStart())
                                ->setLunchEnd($recurrence->getLunchEnd())
                                ->setLunchOverride($recurrence->getLunchOverride())
                                ->setStatus($recurrence->getStatus())
                                ->setIsProviderApproved($recurrence->getIsProviderApproved())
                                ->setIsNurseApproved($recurrence->getIsNurseApproved())
                                ->setClockInTime($recurrence->getClockInTime())
                                ->setClockOutTime($recurrence->getClockOutTime())
                                ->setHourlyRate($recurrence->getHourlyRate())
                                ->setBillingRate($recurrence->getBillingRate())
                                ->setIsCovid($recurrence->getIsCovid())
                                ->setIncentive($recurrence->getIncentive())
                                ->setHourlyOvertimeRate($recurrence->getHourlyOvertimeRate())
                                ->setBillingOvertimeRate($recurrence->getBillingOvertimeRate())
                                ->setDateCreated($recurrence->getDateCreated())
                                ->setDateUpdated($recurrence->getDateUpdated())
                                ->setUpdateLog($recurrence->getUpdateLog())
                                ->setNotifiedBySms($recurrence->getNotifiedBySms())
                                ->setNotifiedByPushNotification($recurrence->getNotifiedByPushNotification());

                            $payrollPayments = $recurrence->getPayrollPayments();
                            if ($payrollPayments) {
                                $newShift->setPayrollPayments($recurrence->getPayrollPayments());
                            }
                            $payrollPayment = $recurrence->getPayrollPayment();
                            if ($payrollPayment) {
                                $payrollPayment->setShift($newShift);
                                $newShift->setPayrollPayment($payrollPayment);
                            }
                            $overtimePayment = $recurrence->getOvertimePayment();
                            if ($overtimePayment) {
                                $overtimePayment->setShift($newShift);
                                $newShift->setOvertimePayment($recurrence->getOvertimePayment());
                            }
                            $recurrenceExists = true;
                            app::$entityManager->persist($newShift);
                            app::$entityManager->flush($newShift);
                            app::$entityManager->detach($newShift);
                            $count++;
                        }
                    }
                }
                // ----- END MATCHING RECURRENCE -----

                // ----- IF NO MATCHING RECURRENCE DO GENERIC MAKING UP NEW SHIFT -----
                if (!$recurrenceExists) {
                    //    MAKE NEW SHIFT HERE
                    $ns_startTime = $shiftDataArray['start_time'];
                    $ns_startDate = $startDate;
                    $ns_endTime = $shiftDataArray['end_time'];
                    $ns_endDate = $ns_startDate; // NOT SURE IF THIS NEEDS ADJUSTING
                    $ns_start = new DateTime($startDate->format('Y-m-d' . ' ' . $ns_startTime->format('H:i:s')));
                    $ns_end = new DateTime($startDate->format('Y-m-d' . ' ' . $ns_endTime->format('H:i:s')));
                    if ($ns_endTime < $ns_startTime) {
                        $ns_endDate->modify('+1 day');
                    }

                    $newShift->setBonusAmount($shiftDataArray['bonus_amount'])
                        ->setBonusDescription($shiftDataArray['bonus_description'])
                        ->setIsEndDateEnabled(false)
                        ->setNurseType($shiftDataArray['nurse_type'])
                        ->setRecurrenceType('None')
                        ->setDescription($shiftDataArray['description'])
                        ->setName($shiftDataArray['name'])
                        ->setStartTime($ns_startTime)
                        ->setStartDate($ns_startDate)
                        ->setEndTime($ns_endTime)
                        ->setEndDate($ns_endDate)
                        ->setStart($ns_start)
                        ->setEnd($ns_end)
                        ->setIsCovid($shiftDataArray['is_covid'])
                        ->setIncentive($shiftDataArray['incentive'])
                        ->setCategory($templateShift->getCategory())
                        ->setProvider($templateShift->getProvider())
                        ->setNurse($templateShift->getNurse())
                        ->setFrequency('Daily-migrated')
                        ->setTimezone($shiftDataArray['timezone'])
                        ->setLunchStart($shiftDataArray['lunch_start'])
                        ->setLunchEnd($shiftDataArray['lunch_end'])
                        ->setLunchOverride($shiftDataArray['lunch_override'])
                        ->setStatus($shiftDataArray['status'])
                        ->setIsProviderApproved($shiftDataArray['provider_approved'])
                        ->setIsNurseApproved($shiftDataArray['nurse_approved']);
                    app::$entityManager->persist($newShift);
                    app::$entityManager->flush($newShift);
                    app::$entityManager->detach($newShift);
                    $count++;
                }
                // ----- END GENERIC SHIFT GENERATION -----

                //INCREMENT THE THINGS
                $startDate->modify('+1 days');
            } while ($startDate->format('Y-m-d') <= $untilDate->format('Y-m-d'));
        }

        $io->writeln('Completed migrating Daily recurrences');
        $io->writeln($count . ' shifts created through daily recurrences');
    }

    public function migrateWeeklyRecurrences()
    {
        $app = app::get();
        $io = $app->getCliIO();
        $timezone = app::getInstance()->getTimeZone();

        $templateShifts = $this->shiftRepository->getShiftsByRecurrenceType(['Weekly']);
        $frequency = 'DAILY';

        $shiftsGenerated = 0;

        foreach ($templateShifts as $key => $templateShift) {
            $shiftDataArray = \sacore\utilities\doctrineUtils::getEntityArray($templateShift);

            $startDate = new DateTime($shiftDataArray['start_date']);
            $untilDate = new DateTime($shiftDataArray['until_date']);

            $shiftRecurrenceRepository = ioc::getRepository('ShiftRecurrence');
            $templateShiftRecurrences = $shiftRecurrenceRepository->findBy(['event' => $templateShift->getId()]);

            //----- SETUP RECURRENCE DATA -----
            $utcstartdate = new \DateTime($templateShift->getStart()->format('Ymd G:i:s', true));
            $utcenddate = new \DateTime($templateShift->getEnd()->format('Ymd G:i:s', true));

            $rrule = new Rule(null, $utcstartdate, $utcenddate, $timezone);
            $rrule->setByDay($templateShift->getRecurrenceOptions());
            $_start = $templateShift->getStartDate();

            $recurrenceEndDate = $templateShift->getRecurrenceEndDate();
            if (!$recurrenceEndDate) {
                $newEndDate = $templateShift->getStart();
                $newEndDate->modify('+ 30 days');
                $recurrenceEndDate = $newEndDate;
            }

            $rrule->setFreq($frequency)
                ->setInterval($templateShift->getRecurrenceInterval())
                ->setWeekStart('SU');

            if ($recurrenceEndDate) {
                $rrule->setUntil($recurrenceEndDate);
            } else {
                $rrule->setCount(Event::LARGE_NUMBER);
            }

            $w = new When();
            $w->RFC5545_COMPLIANT = When::IGNORE;

            $until = $rrule->getUntil()->modify('+1 day');

            $w->startDate($_start)
                ->byday($rrule->getByDay())
                ->freq($rrule->getFreqAsText())
                ->interval($rrule->getInterval())
                ->until($until)
                ->generateOccurrences();
            //----- END RECURRENCE DATA SETUP -----

            foreach ($w->occurrences as $occurrence) {
                $recurrenceExists = false;
                /** @var Shift $newShift */
                $newShift = ioc::resolve('Shift');
                $recurrenceStart = new DateTime($occurrence, app::getInstance()->getTimeZone());

                foreach ($templateShiftRecurrences as $recurrence) {
                    if ($recurrenceStart->format('Y-m-d') == $recurrence->getStart()->format('Y-m-d')) {
                        // generate shift based on recurrence
                        $ns_startTime = $shiftDataArray['start_time'];
                        $ns_startDate = $startDate;
                        $ns_endTime = $shiftDataArray['end_time'];
                        $ns_endDate = $ns_startDate; // NOT SURE IF THIS NEEDS ADJUSTING
                        $ns_start = $recurrence->getStart();
                        $ns_end = $recurrence->getEnd();
                        if ($ns_endTime < $ns_startTime) {
                            $ns_endDate->modify('+1 day');
                        }
                        // SET ALL THE THINGS
                        $newShift->setBonusAmount($recurrence->getBonusAmount())
                            ->setBonusDescription($recurrence->getBonusDescription())
                            ->setIsEndDateEnabled(false)
                            ->setNurseType($recurrence->getNurseType())
                            ->setRecurrenceType('None')
                            ->setDescription($recurrence->getDescription())
                            ->setName($recurrence->getName())
                            ->setStartTime($ns_startTime)
                            ->setStartDate($ns_startDate)
                            ->setEndTime($ns_endTime)
                            ->setEndDate($ns_endDate)
                            ->setStart($ns_start)
                            ->setEnd($ns_end)
                            ->setCategory($recurrence->getCategory())
                            ->setProvider($recurrence->getProvider())
                            ->setNurse($recurrence->getNurse())
                            ->setFrequency('Weekly-migrated')
                            ->setTimezone($recurrence->getTimezone())
                            ->setLunchStart($recurrence->getLunchStart())
                            ->setLunchEnd($recurrence->getLunchEnd())
                            ->setLunchOverride($recurrence->getLunchOverride())
                            ->setStatus($recurrence->getStatus())
                            ->setIsProviderApproved($recurrence->getIsProviderApproved())
                            ->setIsNurseApproved($recurrence->getIsNurseApproved())
                            ->setClockInTime($recurrence->getClockInTime())
                            ->setClockOutTime($recurrence->getClockOutTime())
                            ->setHourlyRate($recurrence->getHourlyRate())
                            ->setBillingRate($recurrence->getBillingRate())
                            ->setIsCovid($recurrence->getIsCovid())
                            ->setIncentive($recurrence->getIncentive())
                            ->setHourlyOvertimeRate($recurrence->getHourlyOvertimeRate())
                            ->setBillingOvertimeRate($recurrence->getBillingOvertimeRate())
                            ->setDateCreated($recurrence->getDateCreated())
                            ->setDateUpdated($recurrence->getDateUpdated())
                            ->setUpdateLog($recurrence->getUpdateLog())
                            ->setNotifiedBySms($recurrence->getNotifiedBySms())
                            ->setNotifiedByPushNotification($recurrence->getNotifiedByPushNotification());

                        $payrollPayments = $recurrence->getPayrollPayments();
                        if ($payrollPayments) {
                            $newShift->setPayrollPayments($recurrence->getPayrollPayments());
                        }
                        $payrollPayment = $recurrence->getPayrollPayment();
                        if ($payrollPayment) {
                            $payrollPayment->setShift($newShift);
                            $newShift->setPayrollPayment($payrollPayment);
                        }
                        $overtimePayment = $recurrence->getOvertimePayment();
                        if ($overtimePayment) {
                            $overtimePayment->setShift($newShift);
                            $newShift->setOvertimePayment($recurrence->getOvertimePayment());
                        }

                        $recurrenceExists = true;

                        app::$entityManager->persist($newShift);
                        app::$entityManager->flush($newShift);
                        app::$entityManager->detach($newShift);
                        $shiftsGenerated++;
                        // We do not break out of the method in case multiple recurrences match the date
                    }
                }

                if (!$recurrenceExists) {
                    //generate plain shift
                    $ns_startTime = $shiftDataArray['start_time'];
                    $ns_startDate = $startDate;
                    $ns_endTime = $shiftDataArray['end_time'];
                    $ns_endDate = $ns_startDate; // NOT SURE IF THIS NEEDS ADJUSTING
                    $ns_start = new DateTime($startDate->format('Y-m-d' . ' ' . $ns_startTime->format('H:i:s')));
                    $ns_end = new DateTime($startDate->format('Y-m-d' . ' ' . $ns_endTime->format('H:i:s')));
                    if ($ns_endTime < $ns_startTime) {
                        $ns_endDate->modify('+1 day');
                    }

                    $newShift->setBonusAmount($shiftDataArray['bonus_amount'])
                        ->setBonusDescription($shiftDataArray['bonus_description'])
                        ->setIsEndDateEnabled(false)
                        ->setNurseType($shiftDataArray['nurse_type'])
                        ->setRecurrenceType('None')
                        ->setDescription($shiftDataArray['description'])
                        ->setName($shiftDataArray['name'])
                        ->setStartTime($ns_startTime)
                        ->setStartDate($ns_startDate)
                        ->setEndTime($ns_endTime)
                        ->setEndDate($ns_endDate)
                        ->setStart($ns_start)
                        ->setEnd($ns_end)
                        ->setIsCovid($shiftDataArray['is_covid'])
                        ->setIncentive($shiftDataArray['incentive'])
                        ->setCategory($templateShift->getCategory())
                        ->setProvider($templateShift->getProvider())
                        ->setNurse($templateShift->getNurse())
                        ->setFrequency('Weekly-migrated')
                        ->setTimezone($shiftDataArray['timezone'])
                        ->setLunchStart($shiftDataArray['lunch_start'])
                        ->setLunchEnd($shiftDataArray['lunch_end'])
                        ->setLunchOverride($shiftDataArray['lunch_override'])
                        ->setStatus($shiftDataArray['status'])
                        ->setIsProviderApproved($shiftDataArray['provider_approved'])
                        ->setIsNurseApproved($shiftDataArray['nurse_approved']);

                    app::$entityManager->persist($newShift);
                    app::$entityManager->flush($newShift);
                    app::$entityManager->detach($newShift);
                    $shiftsGenerated++;
                }
            }
        }

        $io->writeln('Completed migrating Weekly Recurrences');
        $io->writeln('Weekly Shifts generated: ' . $shiftsGenerated);
    }

    public function migrateCustomRecurrences()
    {
        $app = app::get();
        $io = $app->getCliIO();
        $count = 0;
        $offset = 0;
        $perPage = 1000;
        $batchCount = 0;
        do {
            app::$entityManager->clear();

            $templateShifts = $this->shiftRepository->search(['recurrence_type' => 'Custom'], null, $perPage, $offset);

            // $io->writeln('Batch count ' . $batchCount . ' ' . $perPage * $batchCount . '-' . $perPage * ($batchCount+1));
            $batchCount++;
            $offset += $perPage;
            foreach ($templateShifts as $key => $templateShift) {
                $shiftDataArray = \sacore\utilities\doctrineUtils::getEntityArray($templateShift);

                $startDate = new DateTime($shiftDataArray['start_date']);

                $shiftRecurrenceRepository = ioc::getRepository('ShiftRecurrence');
                $templateShiftRecurrences = $shiftRecurrenceRepository->findBy(['event' => $templateShift->getId()]);

                foreach ($templateShiftRecurrences as $recurrence) {
                    /** @var Shift $newShift */
                    $newShift = ioc::resolve('Shift');
                    //    CREATE SHIFT USING RECURRENCE INFORMATION HERE
                    $ns_startTime = $shiftDataArray['start_time'];
                    $ns_startDate = $startDate;
                    $ns_endTime = $shiftDataArray['end_time'];
                    $ns_endDate = $ns_startDate; // NOT SURE IF THIS NEEDS ADJUSTING
                    $ns_start = $recurrence->getStart();
                    $ns_end = $recurrence->getEnd();
                    if ($ns_endTime < $ns_startTime) {
                        $ns_endDate->modify('+1 day');
                    }
                    // SET ALL THE THINGS
                    $newShift->setBonusAmount($recurrence->getBonusAmount())
                        ->setBonusDescription($recurrence->getBonusDescription())
                        ->setIsEndDateEnabled(false)
                        ->setNurseType($recurrence->getNurseType())
                        ->setRecurrenceType('None')
                        ->setDescription($recurrence->getDescription())
                        ->setName($recurrence->getName())
                        ->setStartTime($ns_startTime)
                        ->setStartDate($ns_startDate)
                        ->setEndTime($ns_endTime)
                        ->setEndDate($ns_endDate)
                        ->setStart($ns_start)
                        ->setEnd($ns_end)
                        ->setCategory($recurrence->getCategory())
                        ->setProvider($recurrence->getProvider())
                        ->setNurse($recurrence->getNurse())
                        ->setFrequency('Custom-migrated')
                        ->setTimezone($recurrence->getTimezone())
                        ->setLunchStart($recurrence->getLunchStart())
                        ->setLunchEnd($recurrence->getLunchEnd())
                        ->setLunchOverride($recurrence->getLunchOverride())
                        ->setStatus($recurrence->getStatus())
                        ->setIsProviderApproved($recurrence->getIsProviderApproved())
                        ->setIsNurseApproved($recurrence->getIsNurseApproved())
                        ->setClockInTime($recurrence->getClockInTime())
                        ->setClockOutTime($recurrence->getClockOutTime())
                        ->setHourlyRate($recurrence->getHourlyRate())
                        ->setBillingRate($recurrence->getBillingRate())
                        ->setIsCovid($recurrence->getIsCovid())
                        ->setIncentive($recurrence->getIncentive())
                        ->setHourlyOvertimeRate($recurrence->getHourlyOvertimeRate())
                        ->setBillingOvertimeRate($recurrence->getBillingOvertimeRate())
                        ->setDateCreated($recurrence->getDateCreated())
                        ->setDateUpdated($recurrence->getDateUpdated())
                        ->setUpdateLog($recurrence->getUpdateLog())
                        ->setNotifiedBySms($recurrence->getNotifiedBySms())
                        ->setNotifiedByPushNotification($recurrence->getNotifiedByPushNotification());

                    $payrollPayments = $recurrence->getPayrollPayments();
                    if ($payrollPayments) {
                        $newShift->setPayrollPayments($recurrence->getPayrollPayments());
                    }
                    $payrollPayment = $recurrence->getPayrollPayment();
                    if ($payrollPayment) {
                        $payrollPayment->setShift($newShift);
                        $newShift->setPayrollPayment($payrollPayment);
                    }
                    $overtimePayment = $recurrence->getOvertimePayment();
                    if ($overtimePayment) {
                        $overtimePayment->setShift($newShift);
                        $newShift->setOvertimePayment($recurrence->getOvertimePayment());
                    }

                    app::$entityManager->persist($newShift);
                    app::$entityManager->flush($newShift);
                    app::$entityManager->detach($newShift);
                    $count++;
                }
            }
        } while (count($templateShifts) > 0);

        $io->writeln('Completed migrating Custom recurrences');
        $io->writeln('Custom Shifts generated: ' . $count);
    }

    public function migrateJustRecurrences()
    {
        $shiftRecurrenceRepository = ioc::getRepository('ShiftRecurrence');
        $app = app::get();
        $io = $app->getCliIO();
        $count = 0;
        $offset = 0;
        $perPage = 1000;
        $batchCount = 0;
        do {
            app::$entityManager->clear();
            $recurrences = $shiftRecurrenceRepository->search(null, null, $perPage, $offset);
            $batchCount++;
            $offset += $perPage;
            foreach ($recurrences as $recurrence) {
                /** @var Shift $newShift */
                $newShift = ioc::resolve('Shift');
                //    CREATE SHIFT USING RECURRENCE INFORMATION HERE
                $ns_startTime = $recurrence->getStart()->format('h:i:s');
                $ns_startDate = new DateTime($recurrence->getStart()->format('Y-m-d'));
                $ns_endTime = $recurrence->getEnd()->format('h:i:s');
                $ns_endDate = new DateTime($recurrence->getEnd()->format('Y-m-d'));
                $ns_start = $recurrence->getStart();
                $ns_end = $recurrence->getEnd();
                if ($ns_endTime < $ns_startTime) {
                    $ns_endDate->modify('+1 day');
                }
                // SET ALL THE THINGS
                $newShift->setBonusAmount($recurrence->getBonusAmount())
                    ->setBonusDescription($recurrence->getBonusDescription())
                    ->setIsEndDateEnabled(false)
                    ->setNurseType($recurrence->getNurseType())
                    ->setRecurrenceType('None')
                    ->setDescription($recurrence->getDescription())
                    ->setName($recurrence->getName())
                    ->setStartTime($ns_startTime)
                    ->setStartDate($ns_startDate)
                    ->setEndTime($ns_endTime)
                    ->setEndDate($ns_endDate)
                    ->setStart($ns_start)
                    ->setEnd($ns_end)
                    ->setCategory($recurrence->getCategory())
                    ->setProvider($recurrence->getProvider())
                    ->setNurse($recurrence->getNurse())
                    ->setFrequency('Custom-migrated')
                    ->setTimezone($recurrence->getTimezone())
                    ->setLunchStart($recurrence->getLunchStart())
                    ->setLunchEnd($recurrence->getLunchEnd())
                    ->setLunchOverride($recurrence->getLunchOverride())
                    ->setStatus($recurrence->getStatus())
                    ->setIsProviderApproved($recurrence->getIsProviderApproved())
                    ->setIsNurseApproved($recurrence->getIsNurseApproved())
                    ->setClockInTime($recurrence->getClockInTime())
                    ->setClockOutTime($recurrence->getClockOutTime())
                    ->setHourlyRate($recurrence->getHourlyRate())
                    ->setBillingRate($recurrence->getBillingRate())
                    ->setIsCovid($recurrence->getIsCovid())
                    ->setIncentive($recurrence->getIncentive())
                    ->setHourlyOvertimeRate($recurrence->getHourlyOvertimeRate())
                    ->setBillingOvertimeRate($recurrence->getBillingOvertimeRate())
                    ->setDateCreated($recurrence->getDateCreated())
                    ->setDateUpdated($recurrence->getDateUpdated())
                    ->setUpdateLog($recurrence->getUpdateLog())
                    ->setNotifiedBySms($recurrence->getNotifiedBySms())
                    ->setNotifiedByPushNotification($recurrence->getNotifiedByPushNotification());

                app::$entityManager->persist($newShift);
                app::$entityManager->flush($newShift);
                // $payrollPayments = $recurrence->getPayrollPayments();
                // if ($payrollPayments) {
                //     $newShift->setPayrollPayments($recurrence->getPayrollPayments());
                // }
                $payrollPayment = $recurrence->getPayrollPayment();
                if ($payrollPayment) {
                    $payrollPayment->setShift($newShift);
                    $newShift->setPayrollPayment($payrollPayment);
                    app::$entityManager->flush($payrollPayment);
                }
                $overtimePayment = $recurrence->getOvertimePayment();
                if ($overtimePayment) {
                    $overtimePayment->setShift($newShift);
                    $newShift->setOvertimePayment($overtimePayment);
                    app::$entityManager->flush($overtimePayment);
                }

                app::$entityManager->detach($newShift);
                $count++;
            }
            $io->writeln('Count: ' . $count);
        } while (count($recurrences) > 0);
        $io->writeln('Completed migrating recurrences');
        $io->writeln('Shifts generated: ' . $count);
    }

    public function loadRecurrenceData($data)
    {
        $shiftId = $data['id'];
        $response = ['success' => false];

        /** @var NstMemberUsers $user */
        $user = auth::getAuthUser();

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);

        $start = DateTime::createFromFormat('Ymd', $data['start_date']);
        $end = DateTime::createFromFormat('Ymd', $data['end_date']);

        $startTime = $shift->getStart();
        $endTime = $shift->getEnd();

        $now = new DateTime('now', app::getInstance()->getTimeZone());

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
                'bonus_amount' => $shift->getBonusAmount(),
                'bonus_description' => $shift->getBonusDescription(),
                'description' => $shift->getDescription(),
                'recurrence_type' => $shift->getRecurrenceType(),
                'recurrence_options' => $shift->getRecurrenceOptions(),
                'recurrence_end_date' => $shift->getRecurrenceEndDate() ? $shift->getRecurrenceEndDate()->format('Y-m-d') : '',
                'recurrence_interval' => $shift->getRecurrenceInterval(),
                'is_covid' => $shift->getIsCovid(),
                'incentive' => $shift->getIncentive(),
                'status' => $shift->getStatus(),
                'allow_editing' => $start > $now
            ];

            $response['success'] = true;
        }

        // Get last allowed date to create shifts
        $today = new DateTime('now', app::getInstance()->getTimeZone());
        $today->modify('+5 weeks');
        $payrollService = new PayrollService();
        $period = $payrollService->calculatePayPeriodFromDate($today);
        $response['max_start_date'] = $period['end']->format('Y-m-d');
        $response['bonus_allowed'] = $user->getUserType() == 'Admin' || $user->getBonusAllowed();
        $response['covid_allowed'] = $user->getUserType() == 'Admin' || $user->getCovidAllowed();

        return $response;
    }

    public function parseShiftDataForCalendar($data)
    {
        // This data is for configuring the search
        $start = $data['start'];
        $end = $data['end'];
				$memberId = $data['member_id']?: null;
        $nurseId = $data['nurse_id'] ?: null;
        $categoryId = $data['category_id'] ?: null;
        $nurseType = $data['nurse_type'] ?: null;
        $mobile = $data['mobile'] ?: false;
        $category = ioc::get('\sa\events\Category', ['id' => $categoryId]);
        $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();

        // if block here to retain old code in case we need a rollback
        if (false) {
            // Old way
            $shiftData = $this->shiftRepository->getEventsAndRecurrencesBetweenDates(new DateTime($start['date'], $timezone), new DateTime($end['date'], $timezone), $category, $data['backend'], null, $nurseId, $nurseType, $mobile, $data['calendar_type']);

            return $shiftData;
        } else {
            // New way
            $shiftData = $this->shiftRepository->getShiftsBetweenDates(new DateTime($start['date'], $timezone), new DateTime($end['date'], $timezone), $category, $data['backend'], $data['provider_id'], $memberId, $nurseId, $nurseType, $mobile, $data['calendar_type'], null, null, false, false, $data['states_approved_to_work']);

            if ($data['calendar_type'] == 'month') {
                $results = $this->parseMonthlyShiftStatusCounts($shiftData, $data);
                return $results;
            }

            $events = [];
            /** @var Shift $event */
            foreach ($shiftData as $event) {
                $events[] = [
                    'title' => $event?->getName(),
                    'event_id' => $event?->getId(),
                    'parent_id' => $event?->getParentId(),
                    'is_recurrence' => $event?->getRecurrenceType() != 'None',
                    'nurse_type' => $event?->getNurseType(),
                    'url' => app::get()?->getRouter()?->generate(
                        $data['backend'] ? 'sa_shift_edit' : 'edit_shift',
                        [
                            'id' => $event?->getId(),
                            'recurrenceId' => null,
                            'recurrenceUniqueId' => null,
                            'start_date' => $event?->getStartDate()?->format('Ymd'),
                            'end_date' => $event?->getEndDate()?->format('Ymd')
                        ]
                    ),
                    'start' => $event?->getStart()?->format('Y-m-d') . 'T' . $event?->getStart()?->format('H:i:s'),
                    'end' => $event?->getEnd()?->format('Y-m-d') . 'T' . $event?->getEnd()?->format('H:i:s'),
                    'unique_id' => $event?->getId() . '-' . $event?->getStart()?->format('mdY'),
                    'parent_unique_id' => $event?->getParentId() ? $event?->getParentId() . '-' . $event?->getStart()?->format('mdY') : '',
                    'copy_route' => app::get()?->getRouter()?->generate(
                        $data['backend'] ? 'sa_shift_copy' : 'copy_shift',
                        [
                            'id' => $event?->getId(),
                            'recurrenceId' => 0,
                            'recurrenceUniqueId' => $event?->getId() . '-' . $event?->getStart()?->format('mdY'),
                            'start_date' => $event?->getStartDate()?->format('Ymd'),
                            'end_date' => $event?->getEndDate()?->format('Ymd')
                        ]
                    ),
                    'category_id' => $event?->getCategory()?->getId(),
                ];
            }

            return $events;
        }
    }

    public function parseMonthlyShiftStatusCounts($shifts, $searchData)
    {
        //Set some data for searching
        if ($searchData['backend']) {
            $all = !boolval($searchData['provider_id']);
            if ($searchData['provider_id']) {
                $provider = ioc::get('Provider', ['id' => $searchData['provider_id']]);
            }
        } else {
            $all = false;
        }

        if ($searchData['nurse_id']) {
            $nurse = ioc::get('Nurse', ['id' => $searchData['nurse_id']]);
        }
        if ($searchData['category_id']) {
            $category = ioc::get('\sa\events\Category', ['id' => $searchData['category_id']]);
        }
        if ($searchData['nurse_type']) {
            $nurseType = $searchData['nurse_type'];
        }

        $monthEvents = [];
        foreach ($shifts as $shift) {
            $monthDay = $shift->getStart()->format('Y-m-d');
            if (!$searchData['backend']) {
                $provider = $shift?->getProvider();
            }
            // Let's not do work more than once
            if (!array_key_exists($monthDay, $monthEvents) || !array_key_exists($shift->getStatus(), $monthEvents[$monthDay])) {
                $monthEvents[$monthDay][$shift->getStatus()] = $this->shiftRepository->getShiftsCountForToday(
                    $shift->getStart(),
                    $shift?->getStatus(),
                    $provider,
                    $nurse,
                    $category,
                    $nurseType,
                    $all
                );
            }
        }

        return $monthEvents;
    }

    public function fixShiftEndDatetimes()
    {
        $response = ['success' => false];
        $app = app::get();
        $io = $app->getCliIO();
        $io->title('Fix shift end datetimes');

        $selectField = $io->ask('Are you sure you want to fix shift end datetimes? y/n', 'y');
        if (strtolower($selectField) != 'y') {
            $io->section(' Exiting.');
            return $response;
        }
        $io->section('Beginning...');

        $shiftsCount = $this->shiftRepository->getShiftsOnOrAfterToday(true);
        $io->writeln($shiftsCount . ' shifts to correct. ');
        $io->info('Not all shifts will be considered, only shifts with end datetimes that predate the start datetime.');
        $continue = $io->ask('Continue? y/n', 'y');
        if (strtolower($continue) != 'y') {
            $io->section(' Exiting.');
            return $response;
        }
        $shifts = $this->shiftRepository->getShiftsOnOrAfterToday();
        $count = 0;
        foreach ($shifts as $shift) {
            $start = new DateTime($shift->getStart());
            $end = new DateTime($shift->getEnd());
            if ($end < $start) {
                $end->modify('+1 day');
                $shift->setEnd($end);
                app::$entityManager->flush($shift);
                $count++;
                $io->writeln('Shift ID: ' . $shift->getId());
            }
        }

        $io->writeln('Shifts fixed: ' . $count);
        app::$entityManager->clear();

        $response['success'] = true;
        $io->writeln('Exiting Successfully.');
        return $response;
    }

    public function getShiftsInMonthForNurse($data)
    {
        $year = $data['year'];
        $month = $data['month'];
        $member = auth::getAuthMember();
        $data['nurse_id'] = $member->getNurse()->getId();
        $data['statuses'] = ['Pending', 'Assigned', 'Approved'];
        $data['start'] = max(new DateTime("first day of $month $year"), new DateTime('now'));
        $data['end'] = new DateTime("last day of $month $year");
        $data['nurse_type'] = $member->getNurse()->getCredentials();

        if(app::get()->getConfiguration()->get('nurse_states_filter_enabled')->getValue()) {
            $data['nurseStates'] = $member->getNurse()->getStatesAbleToWorkAbbreviated();
        }

        $shifts = $this->shiftRepository->getShiftStatusesForNurseInMonth($data);
        foreach($shifts as $shift) {
            $shiftData[] = ['day_of_month' => $shift->getStart()->format('j'), 'status' => $shift->getStatus()];
        }

        $returnData = [];
        foreach ($shiftData as $row) {
            // Check if day already exists
            $key = array_search($row['day_of_month'], array_column($returnData, 'day'));

            // If so, set additional status for that day
            if ($key) {
                $returnData[$key][strtolower($row['status'])] = true;
            } else {
                $day = ['day' => $row['day_of_month'], strtolower($row['status']) => true];
                $returnData[] = $day;
            }
        }

        return $returnData;
    }

    public function createOverrideForShift($shiftId, $supervisorName, $supervisorCode, $signatureImage)
    {
        /** @var Shift $newShift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);

        if ($shift == null) {
            return null;
        }

        /** @var ShiftOverride $shiftOverride*/
        $shiftOverride = ioc::resolve('ShiftOverride');

        app::$entityManager->persist($shiftOverride);

        $shiftOverride->setSupervisorName($supervisorName);
        $shiftOverride->setSupervisorCode($supervisorCode);
        $shiftOverride->setSupervisorSignature($signatureImage);

        $shift->setShiftOverride($shiftOverride);

        app::$entityManager->flush();
        return $shiftOverride;
    }

    public function getAllShiftCategories()
    {
        $response = ['success' => false, 'categories' => []];
        $categories = ioc::getRepository(ioc::staticResolve('NstCategory'))->findAll();
        foreach ($categories as $category) {
            $response['categories'][] = ['text' => $category->getName(), 'value' => $category->getId()];
        }
        $response['success'] = true;
        return $response;
    }

    /**
     * @param $shiftId
     * @return Shift
     */
    public function goOnBreak($shiftId) {
        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);
        if ($shift->getBreakStartTime() == null) {
            $shift->setIsOnBreak(true);
            $shift->setBreakStartTime(new DateTime());
            app::$entityManager->flush();
            app::$entityManager->clear();
        }

        return [
            'success' => 'true',
            'break_start_time' => $shift->getBreakStartTime(),
        ];
    }

    public function endBreak($shiftId) {
        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);
        if ($shift->getBreakStartTime() != null) {
            $shift->setIsOnBreak(false);
            $shift->setHasTakenBreak(true);
            app::$entityManager->flush();
            app::$entityManager->clear();
        }

        return [
            'success' => 'true',
        ];
    }

    /**
     * @param Shift $shift
     * @return boolean
     */
    public function isOnBreak($shift) {
        // if is on break and the break start time hasn't expired
        if ($shift->getIsOnBreak() && $shift->getBreakStartTime() != null) {
            $breakStartTime = $shift->getBreakStartTime();
            $breakEndTime = clone $breakStartTime;
            $durationInMinutes = $shift->getProvider()->getBreakLengthInMinutes();
            $breakEndTime->modify("+$durationInMinutes minutes");
            $now = new DateTime();
            if ($now < $breakEndTime) {
                return true;
            }
        }
        return false;
    }
}
