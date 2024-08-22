<?php


namespace nst\member;


use DateInterval;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use Monolog\Logger;
use nst\events\Shift;
use nst\events\ShiftRepository;
use nst\events\ShiftService;
use nst\events\SaShiftLogger;
use nst\messages\SmsService;
use nst\payroll\PayrollService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sa\member\auth;
use sa\member\saMember;
use sa\member\saMemberEmail;
use sa\member\saMemberPhone;
use sa\member\saMemberUsers;
use sa\system\saAuth;
use sacore\utilities\doctrineUtils;

class ProviderService
{
    /** @var ShiftRepository $shiftRepository */
    protected $shiftRepository;

    /** @var ProviderRepository $providerRepository */
    protected $providerRepository;

    /** @var ProviderPayRateRepository $payRateRepository */
    protected $payRateRepository;

    /** @var NstMember $member */
    protected $member;

    /** @var Provider $provider */
    protected $provider;

    /** @var SaShiftLogger $shiftLogger */
    protected $shiftLogger;

    /** @var PayrollService $payrollService */
    protected $payrollService;

    /** @var SmsService $smsService */
    protected $smsService;

    public function __construct()
    {
        $this->providerRepository = ioc::getRepository('Provider');
        $this->payRateRepository = ioc::getRepository('ProviderPayRate');
        $this->shiftRepository = ioc::getRepository('Shift');
        $this->shiftLogger = new SaShiftLogger();
        $this->payrollService = new PayrollService();
        $this->smsService = new SmsService();

        /** @var NstMember $member */
        $this->member = auth::getAuthMember();
        $this->getProvider();
    }

    public function getProvider()
    {
        if (!$this->member || $this->member->getMemberType() != 'Provider') {
            $this->provider = null;
            return;
            // throw new Exception("Member is not a registered provider");
        }

        $this->provider = $this->member->getProvider();
    }

    /** Load all nurses that have worked with the provider but are not on the DO NOT RETURN list */
    public function loadAssignableNurses($data)
    {
        $id = $data['provider_id'];
        $response = ['success' => false];
        $types = strlen($data['nurse_type']) ? explode('/', $data['nurse_type']) : [];

        $start = new DateTime($data['start_date'] . ' ' . $data['start_time'], app::getInstance()->getTimeZone());
        $end = new DateTime($data['start_date'] . ' ' . $data['end_time'], app::getInstance()->getTimeZone());

        if ($end < $start) {
            $end->modify('+1 day');
        }

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $id]);
        if (!$provider) {
            return $response;
        }
        $previousNurses = $provider->getPreviousNurses();
        /** @var NurseRepository $nurseRepo */
        //$nurseRepo = ioc::getRepository('Nurse');
        //$previousNurses = $nurseRepo->findNursesOfTypes($types);
        $blockedNurses = $provider->getBlockedNurses();

        /** @var NurseRepository $nurseRepo */
        $nurseRepo = ioc::getRepository('Nurse');

        /** @var Nurse $nurse */
        foreach ($previousNurses as $nurse) {
            $nurseCreds = $nurse->getCredentials();
            if (!$blockedNurses->contains($nurse) && !$nurse->getIsDeleted()) {
                if (is_array($types) && in_array('CNA', $types)) {
                    if (!in_array('CMT', $types)) {
                        $types[] = 'CMT';
                    }
                } else if ($types == 'CNA') {
                    $types = ['CMT', 'CNA'];
                } else if ($types == 'CMT') {
                    $types = ['CNA', 'CMT'];
                }
                // Nurse is disabled if they have a shift in the time period.
                if (in_array($nurseCreds, $types)) {
                    $isAvailable = $nurseRepo->getNurseAvailability($start, $end, $nurse);
                    $response['nurses'][] = [
                        'id' => $nurse->getId(),
                        'name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
                        'disabled' => !$isAvailable,
                        'type' => $nurseCreds
                    ];
                }

                $response['success'] = true;
            }
        }

        return $response;
    }

    public function loadDoNotReturnList()
    {
        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();
        $response = ['success' => false];

        $blockedNurses = $provider->getBlockedNurses();
        /** @var Nurse $nurse */
        foreach ($blockedNurses as $nurse) {
            $response['nurses'][] = [
                'id' => $nurse->getId(),
                'first_name' => $nurse->getMember()->getFirstName(),
                'last_name' => $nurse->getMember()->getLastName(),
                'profile' => app::get()->getRouter()->generate('nurse_profile', ['id' => $nurse->getId()]),
            ];

            $response['success'] = true;
        }

        return $response;
    }

    public function loadShiftRequests()
    {
        $response = ['success' => false];

        /** @var NstMember $member */
        $member = auth::getAuthMember();

        /** @var Provider $provider */
        $provider = $member->getProvider();

        $shifts = $this->shiftRepository->getShiftRequestsForDashboardByProvider($provider);

        if ($shifts) {
            /** @var Shift $shift */
            foreach ($shifts as $shift) {
                if (!$shift->getNurse()) {
                    continue;
                }
                $response['shifts'][] = [
                    'id' => $shift->getId(),
                    'name' => $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() . ' (' . $shift->getNurse()->getCredentials() . ')',
                    'start_time' => $shift->getStart()->format('g:i A'),
                    'end_time' => $shift->getEnd()->format('g:i A'),
                    'date' => $shift->getIsEndDateEnabled() ?
                        $shift->getStart()->format('m/d/Y') . ' - ' . $shift->getEnd()->format('m/d/Y') :
                        $shift->getStart()->format('m/d/Y'),
                    'nurse_profile' => app::get()->getRouter()->generate('nurse_profile', ['id' => $shift->getNurse()->getId()]),
                    'shift_route' => app::get()->getRouter()->generate('edit_shift', ['id' => $shift->getId()]),
                    'shift_name' => $shift->getName(),
                    'sorting_date' => $shift->getStart()->format('Y-m-d') . ' ' . $shift->getStart()->format('h:i:s')
                ];
            }

            usort($response['shifts'], function ($a, $b) {
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

    public function loadDashboardData($data)
    {
        ini_set('memory_limit', '-1');
        $response = ['success' => false];

        /** @var NstMember $member */
        $member = auth::getAuthMember();

        /** @var Provider $provider */
        $provider = $member->getProvider();

        $payrollService = new PayrollService();

        $unclaimedShifts = $this->providerRepository->getUnclaimedShiftsCount($provider->getId());
        $shiftRequests = self::getShiftRequestCount($provider);
        $unresolvedPayments = $this->providerRepository->getUnresolvedPaymentsCount($provider->getId());
        $payPeriod = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        $shifts = $provider->getShifts();

        //echo 'provider shifts: ' . json_encode($shifts); die;

        $response['shifts'] = [];

        if ($shifts) {
            $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();

            /** @var Shift $shift */
            foreach ($shifts as $shift) {
                if ($shift->isRecurring()) {
                    $recurrence_start = $shift->getStartDate();
                    $recurrence_end = $shift->getUntilDate();
                    $recurrence_interval = $shift->getRecurrenceInterval();
                    $recurrence_type = $shift->getRecurrenceType();
                    $recurrence_rules = $shift->getRecurrenceRules();
                    $response['recurrence'] = [
                        'start' => $recurrence_start,
                        'end' => $recurrence_end,
                        'interval' => $recurrence_interval,
                        'type' => $recurrence_type,
                        'rules' => $recurrence_rules
                    ];
                }
                $startDate = $shift->getStart();
                if ($startDate < new DateTime('now', app::getInstance()->getTimeZone())) {
                    continue;
                }
                $endDate = $shift->getEnd();
                $startTime = $shift->getStart();
                $endTime = $shift->getEnd();

                $isInArray = in_array($shift->getId(), array_column($response['shifts'], 'ID'));

                if (!$isInArray) {
                    $response['shifts'][] = [
                        'id' => $shift->getId(),
                        'nurse_name' => $shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() . ' (' . $shift->getNurse()->getCredentials() . ')' : 'Unassigned',
                        'date' => $shift->getIsEndDateEnabled() ? $startDate->format('m/d/Y') . ' - ' . $endDate->format('m/d/Y') : $shift->getStart()->format('m/d/Y'),
                        'start_time' => $startTime->format('g:i A'),
                        'end_time' => $endTime->format('g:i A'),
                        'status' => $shift->getStatus(),
                        'shift_route' => app::get()->getRouter()->generate('edit_shift', ['id' => $shift->getId()]),
                        'nurse_route' => $shift->getNurse() ? app::get()->getRouter()->generate('nurse_profile', ['id' => $shift->getNurse()->getId()]) : '',
                        'sorting_date' => $startDate->format('Y-m-d') . ' ' . $startTime->format('h:i:s'),
                        'start' => $shift->getStart()
                    ];
                }
            }

            $response['items'][] = [
                'name' => 'Unclaimed Shifts',
                'value' => $unclaimedShifts,
                'color' => 'mr-3 bgl-primary text-primary',
                'icon' => 'las la-exclamation-triangle',
                'route' => app::get()->getRouter()->generate('events_index'),
                'table' => false,
            ];
            $response['items'][] = [
                'name' => 'Shift Requests',
                'value' => $shiftRequests,
                'color' => 'mr-3 bgl-warning text-warning',
                'icon' => 'las la-bell',
                'route' => app::get()->getRouter()->generate('shift_requests'),
                'table' => false
            ];
            $response['items'][] = [
                'name' => 'Unresolved Payments',
                'value' => $unresolvedPayments,
                'color' => 'mr-3 bgl-danger text-danger',
                'icon' => 'las la-list-alt',
                'route' => app::get()->getRouter()->generate('provider_unresolved_pay'),
                'table' => false
            ];
            $response['items'][] = [
                'name' => 'Current Pay Period',
                'value' => $payPeriod['display'],
                'color' => 'mr-3 bgl-success text-success',
                'icon' => 'las la-dollar-sign',
                'route' => app::get()->getRouter()->generate('provider_current_pay_period'),
                'table' => false
            ];
            $response['items'][] = [
                'name' => 'Upcoming Shifts',
                'value' => '',
                'color' => '',
                'icon' => '',
                'table' => true
            ];

            $response['shifts'] = self::sortShifts($response['shifts']);

            $response['success'] = true;
        }

        return $response;
    }

    public function loadNurseFiles($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        if (!$member->getProvider()) {
            return $response;
        }

        if (!$nurse) {
            return $response;
        }

        $files = $nurse->getNurseFiles();
        $response['files'] = [];
        /** @var NstFile $file */
        foreach ($files as $file) {
            if (!$file->getTag()?->getShowInProviderPortal()) {
                continue;
            }

            $response['files'][] = [
                'id' => $file->getId(),
                'filename' => $file->getFilename(),
                'type' => $file->getFileType(),
                'route' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                'tag' => $file->getTag() ? [
                    'id' => $file->getTag()->getId(),
                    'name' => $file->getTag()->getName()
                ] : []
            ];
        }

        $response['tags'] = [];
        $tags = ioc::getRepository('NstFileTag')->findBy(['type' => 'Nurse']);
        /** @var NstFileTag $tag */
        foreach ($tags as $tag) {
            $response['tags'][] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'description' => $tag->getDescription(),
            ];
        }

        $response['success'] = true;
        return $response;
    }

    public static function getShiftRequestCount($provider)
    {
        $providerRepo = ioc::getRepository('Provider');
        return $providerRepo->getShiftRequestsCount($provider->getId());
    }

    public function approveShiftRequest($data)
    {
        $id = $data['id'];
        $response = ['success' => false];

        try {
            /** @var Shift shift */
            $shift = ioc::get('Shift', ['id' => $id]);
            if (!$shift) {
                $response['message'] = "Failure to retrieve shift for editing";
                return $response;
            }

            /** @var Nurse $nurse */
            $nurse = $shift->getNurse();
            if (!$nurse) {
                $response['message'] = "No nurse assigned to shift to approve";
                return $response;
            }

            $shift->setIsProviderApproved(true);
            $shift->setStatus('Approved');
            app::$entityManager->flush();

            $service = new PayrollService();
            $service->initializeShiftRates($shift);

            // twilio sms
            $this->smsService->handleSendSms($shift, ['message_type' => 'approve_shift', 'by' => 'provider', 'nurse' => $nurse]);

            if (!isset($data['command'])) {
                // log
                /** @var NstMemberUsers $user */
                $currentUser = auth::getAuthUser();
                $currentUserName = $currentUser?->getFirstName() . ' ' . $currentUser?->getLastName();

                $startDate = $shift->getStart()->format('Y-m-d');
                $start = $shift->getStart()->format('H:i');
                $end = $shift->getEnd()->format('H:i');
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                $providerName = $this->member->getCompany();
                $credentials = $shift->getNurseType();
                $successLogMessage = "Facility $providerName, user($currentUserName) approved shift for nurse $nurse_name. Shift details: ($credentials) $startDate from $start to $end ($id).";
                $this->shiftLogger->log($successLogMessage, ['action' => 'APPROVED']);

                $response['success'] = true;
            }
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function denyShiftRequest($data)
    {
        $id = $data['id'];
        $response = ['success' => false];

        try {
            $shift = ioc::get('Shift', ['id' => $id]);
            if ($shift) {
                $previous_nurse = $shift->getNurse();
                $shift->setIsProviderApproved(false);
                $shift->setIsNurseApproved(false);
                $shift->setNurse(null);
                $shift->setStatus('Open');
                app::$entityManager->flush();
                $response['success'] = true;

                // twilio sms
                $date = $shift->getStart()->format('m/d/Y');
                $time = $shift->getStart()->format('H:i');

                $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
                $provider = $shift->getProvider();
                $provider_name = $provider->getName();
                if ($provider_name) {
                    $smsBody = 'DENIED SHIFT - ' . $intro . ' Shift on ' . $date . ' at ' . $time . ' has been DENIED by ' . $provider_name;
                } else {
                    $smsBody = 'DENIED SHIFT - ' . $intro . ' Shift on ' . $date . ' at ' . $time . ' has been DENIED by your Provider';
                }
                // Nurse SMS
                if ($previous_nurse) {
                    if ($previous_nurse->getReceivesSMS()) {
                        modRequest::request('messages.startSMSBatch');
                        modRequest::request('messages.sendSMS', array('phone' => $previous_nurse->getPhoneNumber(), 'body' => $smsBody));
                        modRequest::request('messages.commitSMSBatch');
                    }
                }

                // log
                /** @var Nurse $nurse */
                $nurse = $shift->getNurse();
                if ($nurse) {
                    $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                    /** @var Provider $provider */
                    $provider = $shift->getProvider();
                    if ($provider) {
                        app::get()->getLogger()->addError("Shift requested by " . $nurse_name . " was denied by " . $provider->getName());
                    }
                }
            }
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;

    }

    /** Add nurse to the DO NOT RETURN list */
    public function blockNurse($data)
    {
        $id = $data['id'];
        $response = ['success' => false];
        /** @var NstMember $member */
        $member = auth::getAuthMember();

        /** @var Provider $provider */
        $provider = $member->getProvider();

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        if (!$provider->getBlockedNurses()->contains($nurse)) {
            $provider->addBlockedNurse($nurse);
            $nurse->addBlockedProvider($provider);
        }

        try {
            app::$entityManager->flush();
            $response['success'] = true;

            // log
            if ($nurse && $provider) {
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                app::get()->getLogger()->addError("The nurse " . $nurse_name . " was blocked (do not return) by " . $provider->getName());
            }
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;

    }

    /** Remove nurse from the DO NOT RETURN list */
    public function unblockNurse($data)
    {
        $id = $data['id'];
        $response = ['success' => false];

        /** @var NstMember $member */
        $member = auth::getAuthMember();

        /** @var Provider $provider */
        $provider = $member->getProvider();

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        if ($provider->getBlockedNurses()->contains($nurse)) {
            $provider->removeBlockedNurse($nurse);
            $nurse->removeBlockedProvider($provider);
        }

        try {
            app::$entityManager->flush();
            $response['success'] = true;

            // log
            if ($nurse && $provider) {
                $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                app::get()->getLogger()->addError("The nurse " . $nurse_name . " was unblocked (removed from: do not return) by " . $provider->getName());
            }
        } catch (ORMException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;

    }


    private function sortShifts($shifts)
    {

        $sortedShifts = $shifts;
        usort($sortedShifts, function ($a, $b) {
            $aDate = new DateTime($a['sorting_date']);
            $bDate = new DateTime($b['sorting_date']);
            if ($aDate >= $bDate) {
                return 1;
            } else {
                return -1;
            }
        });

        return $sortedShifts;
    }

    public static function loadProfileData($data)
    {
        $response = ['success' => false];
        /** @var NstMember $member */
        $member = auth::getAuthMember();
        /** @var Provider $provider */
        $provider = $member->getProvider();

        if ($member && $provider) {
            $response['provider'] = [
                'name' => $member->getFirstName() . ' ' . $member->getLastName(),
                'company' => $member->getCompany(),
                'email' => $member->getEmails()[0] ? $member->getEmails()[0]->getEmail() : '',
                'phone' => $member->getPhones()[0] ? $member->getPhones()[0]->getPhone() : '',
            ];

            $response['success'] = true;
        }

        return $response;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     * @throws ModRequestAuthenticationException
     * @throws \Exception
     */
    public static function loadUpcomingProviderShifts($data)
    {
        $response = ['success' => false];

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $shifts = ioc::getRepository('Shift')->findBy(['provider' => $provider]);
        if ($shifts) {
            $now = new DateTime('now', app::getInstance()->getTimeZone());
            /** @var Shift $shift */
            foreach ($shifts as $shift) {

                if ($shift->getStart() < $now) {
                    continue;
                }

                $response['shifts'][] = [
                    'id' => $shift->getId(),
                    'nurse_name' => $shift->getNurse() ? $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName() : 'Unassigned',
                    'start_time' => $shift->getStart()->format('g:i A'),
                    'end_time' => $shift->getEnd()->format('g:i A'),
                    'date' => $shift->getIsEndDateEnabled() ?
                        $shift->getStart()->format('m/d/Y') . ' - ' . $shift->getEnd()->format('m/d/Y') :
                        $shift->getStart()->format('m/d/Y'),
                    'nurse_profile' => $shift->getNurse() ? app::get()->getRouter()->generate('nurse_profile', ['id' => $shift->getNurse()->getId()]) : '',
                    'shift_route' => app::get()->getRouter()->generate('edit_shift', ['id' => $shift->getId()]),
                    'shift_name' => $shift->getName(),
                    'sorting_date' => $shift->getStart()->format('Y-m-d') . ' ' . $shift->getStart()->format('h:i:s')
                ];
            }

            usort($response['shifts'], function ($a, $b) {
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

    public static function saveProviderInfo($data)
    {
        $response = ['success' => false];

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $member->setCompany($data['company']);
        if ($data['email']) {
            /** @var saMemberEmail $email */
            $email = $member->getEmails() ? $member->getEmails()[0] : null;
            if ($email) {
                $email->setEmail($data['email']);
            } else {
                /** @var saMemberEmail $email */
                $email = ioc::resolve('saMemberEmail');
                $email->setMember($member);
                $email->setEmail($data['email']);
                $email->setType('Personal');
                $email->setIsActive(true);
                $email->setIsPrimary(true);
                app::$entityManager->persist($email);
            }
        }

        if ($data['phone']) {
            /** @var saMemberPhone $phone */
            $phone = $member->getPhones() ? $member->getPhones()[0] : null;
            if ($phone) {
                $phone->setPhone($data['phone']);
            } else {
                /** @var saMemberPhone $phone */
                $phone = ioc::resolve('saMemberPhone');
                $phone->setMember($member);
                $phone->setPhone($data['phone']);
                $phone->setType('Personal');
                $phone->setIsActive(true);
                $phone->setIsPrimary(true);
                app::$entityManager->persist($phone);
            }
        }

        app::$entityManager->flush();
        $response['success'] = true;

        return $response;
    }

    /**
     * @param Provider $provider
     * @param string $nurse_type
     * @param string $rate_type
     * @param bool $is_covid
     * @param float $incentive
     * @param bool $pay_or_bill
     * @throws ORMException
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\IocException
     */
    public function setPayRate($provider, $nurse_type, $rate_type, $is_covid, $incentive, $pay_or_bill, $rate)
    {
        /** @var ProviderPayRate $payRate */
        $payRate = $this->payRateRepository->findOneBy([
            'provider' => $provider,
            'nurse_type' => $nurse_type,
            'rate_type' => $rate_type,
            'is_covid' => $is_covid,
            'incentive' => $incentive,
            'pay_or_bill' => $pay_or_bill
        ]);

        if (!$payRate) {
            $payRate = ioc::resolve('ProviderPayRate');
            app::$entityManager->persist($payRate);
            $provider->addPayRate($payRate);
        }
        $payRate->setRate($rate);
        $payRate->setProvider($provider);
        $payRate->setNurseType($nurse_type);
        $payRate->setRateType($rate_type);
        $payRate->setIsCovid($is_covid);
        $payRate->setIncentive($incentive);
        $payRate->setPayOrBill($pay_or_bill);

        app::$entityManager->flush();

    }

    // This is ugly, but some of the sheets just had different numbers
    private function getProviderSheetCellValues($sheetNumber, $worksheet, $i)
    {

        $DtoX = [
            'facility' => $worksheet->getCell('D' . $i)->getValue(),
            'cna' => $worksheet->getCell('F' . $i)->getValue(),
            'lpn' => $worksheet->getCell('G' . $i)->getValue(),
            'rn' => $worksheet->getCell('H' . $i)->getValue(),
            'kma' => $worksheet->getCell('I' . $i)->getValue(),
            'stipend' => $worksheet->getCell('J' . $i)->getValue(),
            'covid' => $worksheet->getCell('K' . $i)->getValue(),
            'cna_ot' => $worksheet->getCell('L' . $i)->getCalculatedValue(),
            'lpn_ot' => $worksheet->getCell('M' . $i)->getOldCalculatedValue(),
            'rn_ot' => $worksheet->getCell('N' . $i)->getOldCalculatedValue(),
            'cna_covid' => $worksheet->getCell('O' . $i)->getOldCalculatedValue(),
            'lpn_covid' => $worksheet->getCell('P' . $i)->getOldCalculatedValue(),
            'rn_covid' => $worksheet->getCell('Q' . $i)->getOldCalculatedValue(),
            'cna_ot_covid' => $worksheet->getCell('R' . $i)->getOldCalculatedValue(),
            'lpn_ot_covid' => $worksheet->getCell('S' . $i)->getOldCalculatedValue(),
            'rn_ot_covid' => $worksheet->getCell('T' . $i)->getOldCalculatedValue(),
            'kma_ot' => $worksheet->getCell('U' . $i)->getOldCalculatedValue(),
            'kma_covid' => $worksheet->getCell('V' . $i)->getOldCalculatedValue(),
            'kma_ot_covid' => $worksheet->getCell('W' . $i)->getOldCalculatedValue(),
        ];

        $AtoY = [
            'facility' => $worksheet->getCell('A' . $i)->getValue(),
            'cna' => $worksheet->getCell('C' . $i)->getOldCalculatedValue(),
            'lpn' => $worksheet->getCell('D' . $i)->getOldCalculatedValue(),
            'rn' => $worksheet->getCell('E' . $i)->getOldCalculatedValue(),
            'kma' => $worksheet->getCell('F' . $i)->getOldCalculatedValue(),
            'stipend' => $worksheet->getCell('G' . $i)->getValue(),
            'covid' => $worksheet->getCell('H' . $i)->getValue(),
            'cna_ot' => $worksheet->getCell('M' . $i)->getOldCalculatedValue(),
            'lpn_ot' => $worksheet->getCell('N' . $i)->getOldCalculatedValue(),
            'rn_ot' => $worksheet->getCell('O' . $i)->getOldCalculatedValue(),
            'cna_covid' => $worksheet->getCell('P' . $i)->getOldCalculatedValue(),
            'lpn_covid' => $worksheet->getCell('Q' . $i)->getOldCalculatedValue(),
            'rn_covid' => $worksheet->getCell('R' . $i)->getOldCalculatedValue(),
            'cna_ot_covid' => $worksheet->getCell('S' . $i)->getOldCalculatedValue(),
            'lpn_ot_covid' => $worksheet->getCell('T' . $i)->getOldCalculatedValue(),
            'rn_ot_covid' => $worksheet->getCell('U' . $i)->getOldCalculatedValue(),
            'kma_ot' => $worksheet->getCell('V' . $i)->getOldCalculatedValue(),
            'kma_covid' => $worksheet->getCell('W' . $i)->getOldCalculatedValue(),
            'kma_ot_covid' => $worksheet->getCell('X' . $i)->getOldCalculatedValue(),
        ];

        $AtoU = [
            'facility' => $worksheet->getCell('A' . $i)->getValue(),
            'cna' => $worksheet->getCell('C' . $i)->getOldCalculatedValue(),
            'lpn' => $worksheet->getCell('D' . $i)->getOldCalculatedValue(),
            'rn' => $worksheet->getCell('E' . $i)->getOldCalculatedValue(),
            'kma' => $worksheet->getCell('F' . $i)->getOldCalculatedValue(),
            'stipend' => $worksheet->getCell('G' . $i)->getValue(),
            'covid' => $worksheet->getCell('H' . $i)->getValue(),
            'cna_ot' => $worksheet->getCell('I' . $i)->getOldCalculatedValue(),
            'lpn_ot' => $worksheet->getCell('J' . $i)->getOldCalculatedValue(),
            'rn_ot' => $worksheet->getCell('K' . $i)->getOldCalculatedValue(),
            'cna_covid' => $worksheet->getCell('L' . $i)->getOldCalculatedValue(),
            'lpn_covid' => $worksheet->getCell('M' . $i)->getOldCalculatedValue(),
            'rn_covid' => $worksheet->getCell('N' . $i)->getOldCalculatedValue(),
            'cna_ot_covid' => $worksheet->getCell('O' . $i)->getOldCalculatedValue(),
            'lpn_ot_covid' => $worksheet->getCell('P' . $i)->getOldCalculatedValue(),
            'rn_ot_covid' => $worksheet->getCell('Q' . $i)->getOldCalculatedValue(),
            'kma_ot' => $worksheet->getCell('R' . $i)->getOldCalculatedValue(),
            'kma_covid' => $worksheet->getCell('S' . $i)->getOldCalculatedValue(),
            'kma_ot_covid' => $worksheet->getCell('T' . $i)->getOldCalculatedValue(),
        ];

        if ($sheetNumber == 0 || $sheetNumber == 1)
            return $DtoX;
        if ($sheetNumber == 2 || $sheetNumber == 4)
            return $AtoY;
        if ($sheetNumber == 3 || $sheetNumber == 5) {
            return $AtoU;
        }
        return null;
    }


    public function importProviders($data)
    {
        $response = ['success' => false];

        $path = $data['path'] ?: app::get()->getConfiguration()->get('tempDir') . '/provider_rates_import.xlsx';

        set_time_limit(7200);

        ini_set('memory_limit', '512M');

        if (!$handle = fopen($path, 'r')) {
            echo "Unable to open excel file\n\r";
            exit;
        }

        $payRateHeaders = [
            'Facility',
            'CNA',
            'LPN',
            'RN',
            'KMA',
            'Stipend',
            'Travel',
            'Covid',
            'CNA OT',
            'LPN OT',
            'RN OT',
            'CNA COVID',
            'LPN COVID',
            'RN COVID',
            'CNA OT COVID',
            'LPN OT COVID',
            'RN OPT COVID',
            'KNA OT',
            'KMA COVID',
            'KMA OT COVID'
        ];
        $column_array = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'];

        $sheet_number = 0;
        $start_row = [7, 3, 5, 6, 6, 6, 5];
        $num_rows = 100;
//        $sheet_names = ['Pay Rates', 'Bill Rates', '1.5 incentive PAY Rate', '1.5 incentive BILL Rate', '2 incentive PAY Rate', '2 Incentive BILL Rate', 'Facility Address'];
        $sheet_names = ['Pay Rates', 'Bill Rates', '1.5x Pay', '1.5x Bill', '2x Pay', '2x Bill', 'Facility Address'];

        $payOrBill = ['pay', 'bill', 'Pay', 'Bill', 'Pay', 'Bill', 'Travel'];
        $incentives = [1, 1, 1.5, 1.5, 2, 2, 0];

        /** @var Reader\Xlsx $reader */
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $readFilter = new ProviderImportReadFilter($num_rows, $column_array, 5);
        $reader->setReadFilter($readFilter);
        $spreadsheet = $reader->load($path);
        $reader->setLoadSheetsOnly($sheet_names);

        // Rates
        for ($sheet_number = 0; $sheet_number < 2; $sheet_number++) {
            echo "Sheet: " . $sheet_names[$sheet_number] . "\n\r";
            $worksheet = $spreadsheet->getSheetByName($sheet_names[$sheet_number]);
            if (!$worksheet) {
                echo "Unable to access spreadsheet: " . $sheet_names[$sheet_number] . "\n\r";
                exit;
            }

            $facilityKeywordFound = false;

            for ($i = $start_row[$sheet_number]; $i < $num_rows; $i++) {
                //echo "Rates row: " . $i . "\n\r";
                $cellValues = static::getProviderSheetCellValues($sheet_number, $worksheet, $i);
                if (!$facilityKeywordFound && !trim($cellValues['facility']) == 'Facility') {
                    continue;
                } else
                    if (!$facilityKeywordFound) {
                        $facilityKeywordFound = true;
                        continue;
                    }

                if ($facilityKeywordFound && trim($cellValues['facility']) == '') {
                    break;
                }


                $member = ioc::get('NstMember', ['company' => $cellValues['facility']]);
//                /** @var Provider $provider */
//                $provider = ioc::get('Provider', ['name' => $cellValues['facility']]);
                if (!$member) {
                    echo "No Provider with name: " . $cellValues['facility'] . "\n\r";
                    /** @var NstMember $member */
                    $member = ioc::resolve('saMember');
                    $member->setFirstName('Temp');
                    $member->setLastName('Name');
                    $member->setCompany($cellValues['facility']);
                    $member->setIsActive(true);
                    $member->setMemberType('Provider');
                    app::$entityManager->persist($member);

                    /** @var NstMemberUsers $user */
                    $user = ioc::resolve('saMemberUsers');
                    $username = strtolower(str_replace(' ', '_', $cellValues['facility'])) . '_temp';
                    $user->setFirstName('Temp');
                    $user->setLastName('User');
                    $user->setUsername($username);
                    $user->setPassword('temp1234');
                    $user->setUserType('Admin');
                    $user->setIsActive(true);
                    $user->setIsPrimaryUser(true);
                    app::$entityManager->persist($user);

                    $member->addUser($user);
                    $user->setMember($member);


                    /** @var Provider $provider */
                    $provider = ioc::resolve('Provider');
                    app::$entityManager->persist($provider);

                    $provider->setMember($member);
                    $member->setProvider($provider);
                } else {
                    $provider = $member->getProvider();
                }

                if ($provider) {
                    echo "Setting pay rates for Provider: " . $provider->getId() . "\n\r";
                }

                if (!$provider->getPayRates()) {
                    $this->initializePayRates($provider);
                }
                $payRates = $provider->getPayRates();
                $payRates['CNA']['standard_' . $payOrBill[$sheet_number]] = $cellValues['cna'];
                $payRates['CMT']['standard_' . $payOrBill[$sheet_number]] = $cellValues['kma'];
                $payRates['LPN']['standard_' . $payOrBill[$sheet_number]] = $cellValues['lpn'];
                $payRates['RN']['standard_' . $payOrBill[$sheet_number]] = $cellValues['rn'];
                switch ($payOrBill[$sheet_number]) {
                    case 'pay':
                        $provider->setCovidPayAmount($cellValues['covid'] ?: 0);
                        break;
                    case 'bill':
                        $provider->setCovidBillAmount($cellValues['covid'] ?: 0);
                        break;
                }

                $provider->setPayRates($payRates);
//                static::setPayRate($provider, 'CNA', 'Standard', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['cna']);
//                static::setPayRate($provider, 'CNA', 'Overtime', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['cna_ot']);
//                static::setPayRate($provider, 'CNA', 'Standard', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['cna_covid']);
//                static::setPayRate($provider, 'CNA', 'Overtime', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['cna_ot_covid']);
//
//                static::setPayRate($provider, 'LPN', 'Standard', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['lpn']);
//                static::setPayRate($provider, 'LPN', 'Overtime', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['lpn_ot']);
//                static::setPayRate($provider, 'LPN', 'Standard', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['lpn_covid']);
//                static::setPayRate($provider, 'LPN', 'Overtime', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['lpn_ot_covid']);
//
//                static::setPayRate($provider, 'CMT', 'Standard', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['kma']);
//                static::setPayRate($provider, 'CMT', 'Overtime', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['kma_ot']);
//                static::setPayRate($provider, 'CMT', 'Standard', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['kma_covid']);
//                static::setPayRate($provider, 'CMT', 'Overtime', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['kma_ot_covid']);
//
//                static::setPayRate($provider, 'RN', 'Standard', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['rn']);
//                static::setPayRate($provider, 'RN', 'Overtime', false, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['rn_ot']);
//                static::setPayRate($provider, 'RN', 'Standard', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['rn_covid']);
//                static::setPayRate($provider, 'RN', 'Overtime', true, $incentives[$sheet_number], $payOrBill[$sheet_number], $cellValues['rn_ot_covid']);

                $provider->setTravel($cellValues['travel']);
                $provider->setStipend($cellValues['stipend']);

                app::$entityManager->flush();
            }
        }

        // Addresses
        $worksheet = $spreadsheet->getSheetByName('Facility Address');
        if (!$worksheet) {
            echo "Unable to access spreadsheet: 'Facility Address'";
            exit;
        }

        for ($i = 5; $i < $num_rows; $i++) {
            $cellValues = [
                'facility' => $worksheet->getCell('C' . $i)->getValue(),
                'street' => $worksheet->getCell('E' . $i)->getValue(),
                'city' => $worksheet->getCell('F' . $i)->getValue(),
                'state' => $worksheet->getCell('G' . $i)->getValue(),
                'zip' => $worksheet->getCell('H' . $i)->getValue(),
                'phone' => $worksheet->getCell('I' . $i)->getValue()
            ];
            echo 'Facility: ' . $cellValues['facility'] . "\n";

            $member = ioc::get('NstMember', ['company' => $cellValues['facility']]);
//                /** @var Provider $provider */
//                $provider = ioc::get('Provider', ['name' => $cellValues['facility']]);
            if (!$member) {
                echo "No Provider with name: " . $cellValues['facility'] . "\n";
                // Create provider
                continue;
            }
            /** @var Provider $provider */
            $provider = $member->getProvider();

            $provider->setStreetAddress($cellValues['street']);
            $provider->setCity($cellValues['city']);
            $provider->setStateAbbreviation($cellValues['state']);
            $provider->setZipcode($cellValues['zip']);
            $provider->setFacilityPhoneNumber($cellValues['phone']);
            app::$entityManager->flush();

        }

        echo "Finished";
        exit;

        $response['success'] = true;

        return $response;

    }

    public static function importProvidersOld($data)
    {
        $response = ['success' => false];

        $path = $data['path'];
        $freshImport = $data['fresh_import'];

        $headers = [
            'Facility',
            'CNA',
            'LPN',
            'RN',
            'Covid CNA',
            'Covid LPN',
            'Covid RN'
        ];

        set_time_limit(7200);

        ini_set('memory_limit', '512M');

        if (!$handle = fopen($path, 'r')) {
            echo "Unable to open excel file";
            exit;
        }

        $sheet_name = 'Pay Rates';
        $current_row = 0;
        $start_row = 7;
        $column_array = ['B', 'D', 'E', 'F', 'H', 'I', 'J'];
        $num_rows = 100;

        /** @var Reader\Xlsx $reader */
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $readFilter = new ProviderImportReadFilter($num_rows, $column_array, $start_row);
        $reader->setReadFilter($readFilter);
        $spreadsheet = $reader->load($path);
        $reader->setLoadSheetsOnly([$sheet_name]);
        $worksheet = $spreadsheet->getActiveSheet();

        if (!$worksheet) {
            echo "Unable to access spreadsheet";
            exit;
        }

        for ($i = $start_row; $i < $num_rows; $i++) {
            $cellValues = [
                'facility' => $worksheet->getCell('B' . $i)->getValue(),
                'cna' => $worksheet->getCell('D' . $i)->getValue(),
                'lpn' => $worksheet->getCell('E' . $i)->getValue(),
                'rn' => $worksheet->getCell('F' . $i)->getValue(),
                'covid_cna' => $worksheet->getCell('H' . $i)->getValue(),
                'covid_lpn' => $worksheet->getCell('I' . $i)->getValue(),
                'covid_rn' => $worksheet->getCell('J' . $i)->getValue(),
            ];

            if (!$cellValues['facility']) {
                continue;
            }
            /** @var NstMember $member */
            $member = ioc::resolve('saMember');
            $member->setFirstName('Temp');
            $member->setLastName('Name');
            $member->setCompany($cellValues['facility']);
            $member->setIsActive(true);
            $member->setMemberType('Provider');
            app::$entityManager->persist($member);

            /** @var NstMemberUsers $user */
            $user = ioc::resolve('saMemberUsers');
            $username = strtolower(str_replace(' ', '_', $cellValues['facility'])) . '_temp';
            $user->setFirstName('Temp');
            $user->setLastName('User');
            $user->setUsername($username);
            $user->setPassword('temp1234');
            $user->setUserType('Admin');
            $user->setIsActive(true);
            $user->setIsPrimaryUser(true);
            app::$entityManager->persist($user);

            $member->addUser($user);
            $user->setMember($member);

            $payRates = [
                'CNA' => [
                    'standard' => (float)$cellValues['cna'],
                    'covid' => $cellValues['covid_cna'] ? (float)$cellValues['covid_cna'] : (float)$cellValues['cna']
                ],
                'LPN' => [
                    'standard' => (float)$cellValues['lpn'],
                    'covid' => $cellValues['covid_lpn'] ? (float)$cellValues['covid_lpn'] : (float)$cellValues['lpn']
                ],
                'RN' => [
                    'standard' => (float)$cellValues['rn'],
                    'covid' => $cellValues['covid_rn'] ? (float)$cellValues['covid_rn'] : (float)$cellValues['rn']
                ],
            ];

            /** @var Provider $provider */
            $provider = ioc::resolve('Provider');
            $provider->setPayRates($payRates);
            app::$entityManager->persist($provider);

            $provider->setMember($member);
            $member->setProvider($provider);


            app::$entityManager->flush();

        }


        return $response;
    }

    public static function setIsPreferred($data)
    {
        $response = ['success' => false];
        $providerId = $data['provider_id'];
        $nurseId = $data['nurse_id'];
        $isPreferred = $data['is_preferred'];

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $providerId]);

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurseId]);

        if ($isPreferred && !$nurse->getPreferredProviders()->contains($provider)) {
            $nurse->addPreferredProvider($provider);
        }

        if (!$isPreferred && $nurse->getPreferredProviders()->contains($provider)) {
            $nurse->removePreferredProvider($provider);
        }

        app::$entityManager->flush();
        $response['success'] = true;

        return $response;
    }

    /**
     * @param Provider $provider
     */
    public function initializePayRates($provider)
    {
        $provider->setPayRates([
            'CNA' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
            'CMT' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
            'LPN' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
            'RN' => [
                'standard_pay' => 0,
                'standard_bill' => 0,
            ],
        ]);
        $provider->setCovidPayAmount(0);
        $provider->setCovidBillAmount(0);
    }

    public function calculatePayRate($data)
    {
        /** @var Provider $provider */
        $shift = $data['shift'];
        $provider = $data['provider'];
        $nurseType = $data['nurse_type'];
        if ($data['nurse_type'] && $shift) {
            // Logic for CMTs working CNA shifts getting CNA pay
            $nurseType = ($data['nurse_type'] == 'CMT' && !str_contains($shift->getNurseType(), 'CMT') && str_contains($shift->getNurseType(), 'CNA')) ? 'CNA' : $data['nurse_type'];
        } else {
            // Logic for if we are trying to set shift rates when shift is created
            $nurseType = explode('/', $shift->getNurseType())[0];
        }
        $type = strtolower($data['rate_type']);
        $isCovid = $data['is_covid'];
        $incentive = $data['incentive'];
        $payOrBill = strtolower($data['pay_or_bill']);

        $rateType = 'standard_' . $payOrBill;

        $rate = $provider->getPayRates()[$nurseType][$rateType];
        $ot = $type == 'overtime' ? $rate / 2 : 0;
        switch ($payOrBill) {
            case 'pay':
                $rate *= $incentive;                                                // Incentive
                $rate += $ot;                                                       // Overtime
                $rate += $isCovid ? $provider->getCovidPayAmount() : 0;             // Covid
                break;
            case 'bill':
                $rate += $isCovid ? $provider->getCovidBillAmount() : 0;            // Covid
                $rate *= $type == 'overtime' ? 1.5 : 1;                             // Overtime
                $rate *= $incentive;                                                // Incentive
                break;
        }

        return $rate;
    }

    public function updateTravelAndStipendInfo()
    {
        $response = ['success' => false];

        $providers = ioc::getRepository('Provider')->findAll();

        $payrollService = new PayrollService();
        /** @var Provider $provider */
        foreach ($providers as $provider) {
            echo "Provider: " . $provider->getMember()->getCompany() . "\n";;

            if ($provider->getTravel()) {
                $provider->setUsesTravelPay(true);
                app::$entityManager->flush();
                /** @var Shift $shift */
                foreach ($provider->getShifts() as $shift) {
                    $payment = $shift->getPayrollPayment();
                    if ($payment) {
                        $shift->setPayrollPayment(null);
                        app::$entityManager->remove($payment);
                        $payrollService->createShiftPayment($shift, $shift->getIsRecurrence());
                        app::$entityManager->flush();
                    }
                }
            }
            if ($provider->getStipend()) {
                foreach ($provider->getShifts() as $shift) {
                    echo "Shift Stipend" . "\n";
                    $shift->setDescription($shift->getDescription() && !str_contains($shift->getDescription(), 'Stipend') ? $shift->getDescription() . ' -- Stipend ' : 'Stipend');
                }
            }
            app::$entityManager->flush();
        }
        echo 'Finished';
        exit;

        $response['success'] = true;
        return $response;
    }

    public function hasBlockedNurse($providerId, $nurseId)
    {
        $provider = ioc::getRepository('Provider')->findOneBy(['id' => $providerId]);
        $blockedNurses = $provider->getBlockedNurses();

        /** @var Nurse $nurse */
        foreach ($blockedNurses as $nurse) {
            if ($nurse->getId() == $nurseId) {
                return true;
            }
        }

        return false;
    }

    public static function pbjReport($data)
    {
        $response = ['success' => false];
        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $data['provider_id']]);

        if (!$provider) {
            return $response;
        }

        /** @var Shift $shifts [] */
        $shiftIds = ioc::getRepository('Shift')->providerShiftsInTimeFrame($data['start'], $data['end'], $provider);

        $returnShifts = array();
        foreach ($shiftIds as $shift) {
            /** @var Shift $shift */
            $shift = ioc::getRepository('Shift')->findOneBy(['id' => $shift]);
            $shiftInfo['date'] = (string)$shift->getStart();

            $shiftInfo['clocked_hours'] = array();
            $shiftInfo['bill_rate'] = array();
            $shiftInfo['bonus'] = 0;
            $shiftInfo['travel_pay'] = 0;
            $shiftInfo['holiday_pay'] = 0;
            $shiftInfo['bill_total'] = array();

            /** @var PayrollPayment $payrollPayment [] */
            $payrollPayments = $shift?->getPayrollPayments();
            foreach ($payrollPayments as $payment) {
                array_push($shiftInfo['clocked_hours'], round($payment->getClockedHours(), 2));
                array_push($shiftInfo['bill_rate'], $payment->getBillRate());
                $shiftInfo['bonus'] += $payment->getBillBonus();
                $shiftInfo['travel_pay'] = $payment->getBillTravel();
                $shiftInfo['holiday_pay'] = $payment->getBillHoliday();
                array_push($shiftInfo['bill_total'], $payment->getBillTotal());
            }

            // skip shifts that do not have a bill total
            if (empty(array_filter($shiftInfo['bill_total'], function ($bill_total) {
                return $bill_total != 0;
            }))) {
                continue;
            }

            /** @var Nurse $nurse */
            $nurse = $shift?->getNurse();
            if ($nurse) {
                $firstName = $nurse?->getFirstName();
                $lastName = $nurse?->getLastName();
                $shiftInfo['credentials'] = $nurse?->getCredentials();
                $shiftInfo['nurse_name'] = $firstName . " " . $lastName;
            }
            array_push($returnShifts, $shiftInfo);
        }

        $response['line_items'] = $returnShifts;
        $response['success'] = true;
        return $response;
    }

    public function getNurseCredentials($data)
    {
        $response = ['success' => false];

        $response['credentials'] = doctrineUtils::getEntityCollectionArray($this->provider->getNurseCredentials());

        $response['success'] = true;
        return $response;
    }

    public function getPresetShiftTimes($data)
    {
        $response = ['success' => false];

        if ($data['provider_id']) {
            $this->provider = ioc::get('Provider', ['id' => $data['provider_id']]);
        }

        $presetShifTtimes = $this->provider->getPresetShiftTimes();
        foreach ($presetShifTtimes as $shiftTime) {
            $time = doctrineUtils::getEntityArray($shiftTime);
            $time['text'] = $time['human_readable'];
            $time['category_id'] = $shiftTime?->getCategory()?->getId();
            $response['presetShiftTimes'][] = $time;
        }
        $response['success'] = true;
        return $response;
    }

    public function getPayRates()
    {
        $response = ['success' => false];

        $payRates = $this->provider->getPayRates();

        $response['payRates'] = $payRates;
        $response['success'] = true;
        return $response;
    }

    /** Load all nurses that have worked with the provider but are not on the DO NOT RETURN list */
    public function getAvailableNurses($data)
    {
        $response = ['success' => false];

        $types = strlen($data['nurse_type']) ? explode('/', $data['nurse_type']) : [];

        $previousNurses = $this->provider->getPreviousNurses();
        $blockedNurses = $this->provider->getBlockedNurses();

        /** @var Nurse $nurse */
        foreach ($previousNurses as $nurse) {
            $nurseCreds = $nurse->getCredentials();
            if (!$blockedNurses->contains($nurse) && !$nurse->getIsDeleted()) {
                if (is_array($types) && in_array('CNA', $types)) {
                    if (!in_array('CMT', $types)) {
                        $types[] = 'CMT';
                    }
                } else if (is_array($types) && in_array('CMT', $types)) {
                    if (!in_array('CNA', $types)) {
                        $types[] = 'CNA';
                    }
                } else if ($types == 'CNA') {
                    $types = ['CMT', 'CNA'];
                } else if ($types == 'CMT') {
                    $types = ['CNA', 'CMT'];
                }

                // Nurse is disabled if they have a shift in the time period.
                if (in_array($nurseCreds, $types)) {
                    $response['nurses'][] = [
                        'value' => $nurse->getId(),
                        'text' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
                        'type' => $nurseCreds
                    ];
                }

                $response['success'] = true;
            }
        }

        return $response;
    }

    public function handleSaveNewShift($data)
    {
        $response = ['success' => false];
        $response['error'] = [];

        if (!is_array($data['shift']['dates']) || !count($data['shift']['dates'])) {
            $response['message'] = "Please select dates";
            return $response;
        }

        $shiftsSucceeded = 0;
        $shiftsFailed = 0;

        // May need to adjust to add a count variable in here at some point to create grouped shifts
        foreach ($data['shift']['dates'] as $date) {
            // create shift with $date and $data['shift'] in loop
            try {
                $this->saveNewShift($date, $data['shift']);
                $shiftsSucceeded += 1 * (int)$data['shift']['copies'];
            } catch (\Throwable $t) {
                $shiftsFailed += 1 * (int)$data['shift']['copies'];
                if (!in_array($t->getMessage(), $response['error'])) {
                    $response['error'][] = $t->getMessage();
                }
            }
        }

        // Logging shift creation info
        $startDate = $data['shift']['dates'][0];
        $endDate = end($data['shift']['dates']);

        $startTime = $data['shift']['selectedTime']['start_time'];
        $endTime = $data['shift']['selectedTime']['end_time'];
        if (!isset($data['command'])) {
            $providerName = $this?->provider?->getMember()?->getCompany();

            /** @var NstMemberUsers $user */
            $currentUser = auth::getAuthUser();
            if ($currentUser) {
                $currentUserName = $currentUser?->getFirstName() . ' ' . $currentUser?->getLastName();
            } else {

                $auth = saAuth::getInstance();
                /** @var saUser $user */
                $currentUser = $auth->getAuthUser();
            }

            $successLogMessage = "$shiftsSucceeded Shifts created ranging from $startDate to $endDate at $startTime to $endTime by facility $providerName User: $currentUserName";
            $this->shiftLogger->log($successLogMessage, ['action' => 'CREATED']);

            $response['succeeded'] = $shiftsSucceeded;
            if ($shiftsFailed) {
                $successLogMessage = "$shiftsFailed Shifts failed to save ranging from $startDate to $endDate at $startTime to $endTime by facility $providerName User: $currentUserName";
                $this->shiftLogger->log($successLogMessage, ['action' => 'ERROR']);

                $response['failed'] = $shiftsFailed;
                return $response;
            }
        }
        $response['success'] = true;
        return $response;
    }

    public function saveNewShift($date, $shift_data)
    {
        // Maybe log save shift action here?
        $response = ['success' => false];
        $nurseId = $shift_data['nurse'] ? $shift_data['nurse']['value'] : null;

        /** @var Shift $shift */
        $shift = ioc::resolve('Shift');

        $timezone = !(empty($data['timezone'])) ? new \DateTimeZone($data['timezone']) : app::getInstance()->getTimeZone();

        // Convert this into ShiftTimes object when possible
        $startTime = new DateTime($shift_data['selectedTime']['start_time']);
        $startDate = new DateTime($date);
        $endTime = new DateTime($shift_data['selectedTime']['end_time']);
        $endDate = new DateTime($date);
        if ($endTime < $startTime) {
            $endDate->modify('+1 day');
        }
        $start = new DateTime($date . ' ' . $shift_data['selectedTime']['start_time']);
        $end = new DateTime($endDate->format('Y-m-d') . ' ' . $shift_data['selectedTime']['end_time']);

        // make sure shift does not cover more than 16 hours
        $hourdiff = ceil((strtotime($end) - strtotime($start)) / 3600);
        if ($hourdiff > 16) {
            throw new Exception('Cannot create a shift spanning more than 16 hours');
        }

        /**
         * Check if shift time overlaps times from today, if so deny saveShift
         */
        $shiftService = new ShiftService();
        $today = new DateTime($date);

        $nurse = ioc::get('Nurse', ['id' => $nurseId]);
        if ($nurse) {
            $sameDayShifts = $this->shiftRepository->getShiftsForNurse($nurse, $today)['all'];
            /** @var Shift $sdShift */
            foreach ($sameDayShifts as $sdShift) {
                if ($shiftService->isConflicting($start, $end, $sdShift->getStart(), $sdShift->getEnd())) {
                    $response['message'] = "An existing shift already exists for this Nurse at the given time and date";
                    throw new Exception("An existing shift already exists for this Nurse at the given time and date");
                    return $response; // which is success => false at this point
                }
            }
        }

        // Set initial shift data
        $shift->setBonusAmount($shift_data['bonus']['amount'] ?: '')
            ->setBonusDescription($shift_data['bonus']['description'] ?: '')
            ->setIsEndDateEnabled(false)
            ->setNurseType($shift_data['credential'])
            ->setRecurrenceType('None')
            ->setDescription('')
            ->setName('')
            ->setStartTime($startTime)
            ->setStartDate($startDate)
            ->setEndTime($endTime)
            ->setEndDate($endDate)
            ->setStart($start)
            ->setEnd($end)
            ->setIsCovid(filter_var($shift_data['isCovid'], FILTER_VALIDATE_BOOLEAN))
            ->setIncentive($shift_data['premiumRate']['value']);

        // Save Category
        $category = ioc::get('Category', ['id' => $shift_data['selectedTime']['category_id']]);
        if (!$category) {

            if ($shift_data['selectedTime']['category_id'] == 0) {

                $category = ioc::get('Category', ['name' => 'Custom']);
                if (!$category) {
                    throw new Exception('Unable to find category');
                }
            } else {
                throw new Exception('Unable to find category');
            }
        }
        $shift->setCategory($category);



        //Set Provider as owner of the shift
        if (!$this->provider) {
            try {

                $this->provider = ioc::get('Provider', ['id' => $shift_data['provider_id']]);
                if (!$this->provider) {
                    throw new Exception('Unable to find provider');
                }
            } catch (\Throwable $t) {
                throw new Exception('Unable to authenticate user');
            }
        }

        $shift->setProvider($this->provider);

        app::$entityManager->persist($shift);

        $shift->setRecurrenceOptions(null);
        $shift->setRecurrenceEndDate(null);
        $shift->setRecurrenceInterval(1);

        $shiftService->assignNurseToShift($shift, $nurseId, true);

        app::$entityManager->flush($shift);

        for ($i = 1; $i < $shift_data['copies']; $i++) {
            $copiedShift = clone $shift;
            $shiftService->assignNurseToShift($copiedShift, null, true);
            $copiedShift->setParentId($shift->getId());
            app::$entityManager->persist($copiedShift);
        }

        $data['start_date'] = $date;
        $data['start_time'] = $shift_data['selectedTime']['start_time'];
        $data['provider_id'] = $shift->getProvider()->getId();
        $inFiveDays = (new DateTime())->getTimestamp() + 432000;
        if ($shift->getStart()->getTimeStamp() < $inFiveDays && $shift->getStatus() == 'Open') {
            $shiftService->NotifyPreferredNurses($data);
        }
        app::$entityManager->flush();
        $this->payrollService->initializeShiftRates($shift);
        $response['shift_id'] = $shift->getId();
        $response['success'] = true;

        return $response;
    }

    /**
     * Intended to be used exclusively in a command
     */
    public function setDefaultProviderNurseCredentials()
    {
        $providers = $this->providerRepository->findAll();
        $nurseCredentialsRepo = ioc::getRepository('NurseCredential');

        $credentials = $nurseCredentialsRepo->findBy(['name' => array('CNA', 'CMT', 'LPN/RN', 'CMT/LPN/RN')]);

        $count = 0;
        foreach ($providers as $provider) {
            $nurseCredentials = $provider->getNurseCredentials();
            foreach ($credentials as $credential) {
                if (!$nurseCredentials->contains($credential)) {
                    echo 'Provider: ' . $provider->getMember()->getCompany() . 'Adding credential: ' . $credential->getName() . PHP_EOL;
                    $provider->addNurseCredential($credential);
                }
            }
            app::$entityManager->persist($provider);

            $count++;
            if ($count > 99) {
                app::$entityManager->flush();
                $count = 0;
            }
        }

        app::$entityManager->flush();

        return true;
    }

    public function cancelShift($data): array
    {
        // TODO: Need to make this method more flexible in the future to account for declining vs canceling
        $response = ['success' => false];
        $shift = $this->shiftRepository->findOneBy(['id' => $data['shift_id']]);

        if ($shift) {
            $now = new Datetime();
            if ($shift->getStatus() == 'Approved' && ($shift->getStart()->getTimestamp() - $now->getTimestamp()) < 7200) {
                $response['message'] = 'Cannot cancel an approved shift within 2 hours of shift start time.';
                return $response;
            }

            $nurse = $shift->getNurse();
            if ($nurse) {
                $oldShiftStatus = $shift->getStatus();

                $shift->setNurse(null);
                $shift->setStatus(Shift::STATUS_OPEN);
                $shift->setIsProviderApproved(false);
                $shift->setIsNurseApproved(false);
                app::$entityManager->flush();

                $shiftService = new ShiftService();
                $shiftService->NotifyPreferredNurses([
                    'provider_id' => $this->provider->getId(),
                    'nurse_type' => $shift->getNurseType(),
                    'start_date' => $shift->getStartDate()->format('y-m-d'),
                    'start_time' => $shift->getStartTime()->format('H:i:s'),
                    'end_time' => $shift->getEndTime()->format('H:i:s')]);


                $this->smsService->handleSendSms($shift, ['message_type' => 'cancel_shift', 'by' => 'provider', 'nurse' => $nurse]);

                // Logging
                $providerName = $this?->member?->getCompany();

                /** @var NstMemberUsers $user */
                $currentUser = auth::getAuthUser();
                $currentUserName = $currentUser?->getFirstName() . ' ' . $currentUser?->getLastName();

                $startDate = $shift->getStart()->format('Y-m-d');
                $start = $shift->getStart()->format('H:i');
                $end = $shift->getEnd()->format('H:i');

                $successLogMessage = "$oldShiftStatus Shift canceled for date $startDate from $start to $end by facility $providerName User: $currentUserName";
                $this->shiftLogger->log($successLogMessage, ['action' => 'CANCELED']);

                $response['success'] = true;
            } else {
                $shift->setStatus(Shift::STATUS_OPEN);
                $shift->setIsProviderApproved(false);
                $shift->setIsNurseApproved(false);
                app::$entityManager->flush();
                $response['message'] = 'Shift has no working nurse';
            }
        } else {
            $response['message'] = 'Could not retrieve shift for editing';
        }

        return $response;
    }

    public static function providerGetNurseFiles($data)
    {
        $response = ['success' => false];
        $id = $data['id'];
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        if (!$nurse) {
            return $response;
        }

        $files = $nurse->getNurseFiles();
        $response['files'] = [];
        /** @var NstFile $file */
        foreach ($files as $file) {

            if (!$file?->getTag()?->getShowInProviderPortal()) {
                continue;
            } else {

                $response['files'][] = [

                    'id' => $file->getId(),
                    'filename' => $file->getFilename(),
                    'type' => $file->getFileType(),
                    'route' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
                    'tag' => $file->getTag() ? [

                        'id' => $file->getTag()->getId(),
                        'name' => $file->getTag()->getName()
                    ] : []
                ];
            }
        }

        usort($response['files'], function ($a, $b) {
            $aName = $a['tag']['name'] ?? '';
            $bName = $b['tag']['name'] ?? '';
            return strcmp($aName, $bName);
        });

        $response['success'] = true;
        return $response;
    }
}
