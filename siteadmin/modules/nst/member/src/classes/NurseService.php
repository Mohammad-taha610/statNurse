<?php


namespace nst\member;


use nst\events\SaShiftLogger;
use nst\events\Shift;
use nst\events\ShiftRepository;
use nst\events\ShiftService;
use nst\messages\SmsService;
use nst\payroll\PayrollPayment;
use nst\payroll\PayrollService;
use nst\system\NstStateRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\member\auth;
use sa\member\saMemberAddress;
use sa\member\saMemberEmail;
use sa\member\saMemberPhone;
use sa\system\saState;
use sa\system\saUser;
use sacore\utilities\doctrineUtils;
use nst\messages\NstPushNotificationService;

class NurseService
{

    /** @var PayrollService $payrollService */
    protected $payrollService;

    /** @var NurseRepository */
    protected $nurseRepository;

    /** @var \DateTimeZone $timezone */
    protected $timezone;
    
    /** @var \SmsService $smsService */
    protected $smsService;

    public function __construct()
    {
        $this->nurseRepository = ioc::getRepository('Nurse');
        $this->timezone = app::getInstance()->getTimeZone();
        $this->payrollService = new PayrollService();
        $this->smsService = new SmsService();
    }

    public function loadNurseProfileData($data)
    {
        $id = $data['id'];
        $response = ['success' => false];

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        if($nurse) {
            $member = $nurse->getMember();
            /** @var saMemberAddress $address */
            //$address = $member->getAddresses() ? $member->getAddresses()[0] : null;
            /** @var saMemberPhone $phone */
            //$phone = $member->getPhones() ? $member->getPhones()[0] : null;
            /** @var saMemberEmail $email */
            //$email = $member->getEmails() ? $member->getEmails()[0] : null;
            $nurseAppService = ioc::resolve('NurseApplicationService');

            $response['nurse'] = [
                'first_name' => $nurse->getMember()->getFirstName(),
                'middle_initial' => $nurse->getMember()->getMiddleName() ? strtoupper(substr($nurse->getMember()->getMiddleName() , 0, 1)) : '',
                'last_name' => $nurse->getMember()->getLastName(),
                'is_blocked' => $provider !== null && $provider->getBlockedNurses()->contains($nurse),
                'phone' => $nurse->getPhoneNumber(),
                'email' => $nurse->getEmailAddress(),
                'credentials' => $nurse->getCredentials() ?: '',
                'avatar' => ($nurse->getMember()->getAvatar() !== null) ? $nurse->getMember()->getAvatar() : '',
                'birthday' => $nurse->getDateOfBirth() !== null ? $nurse->getDateOfBirth()->format('Y-m-d') : '',
                'application' => $nurse->getMember()->getNurseApplication() !== null ? $nurseAppService->getFullApplication( $nurse->getMember()->getNurseApplication() ) : ''
            ];
            $response['success'] = true;
        } else {
            $response['message'] = 'Nurse ID: ' . $id . ' not found!';
        }
        return $response;
    }

    public function loadUpcomingNurseShifts($data) {
        $id = $data['id'];
        $response = ['success' => false];

        $shiftData = $this->nurseRepository->getUpcomingNurseShifts($id);

        if($shiftData) {
            foreach($shiftData['shifts'] as $shift) {
                $response['shifts'][] = [
                    'name' => $shift['company'],
                    'date' => $shift['start_date']->format('m-d-Y'),
                    'time' => $shift['start_time']->format('g:i a'),
                    'status' => $shift['status'],
                    'sorting_date' => $shift['start_date']->format('Y-m-d') . ' ' . $shift['start_time']->format('h:i:s')
                ];
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

            if($response['shifts']) {
                $response['success'] = true;
            }

        }

        return $response;
    }

    public function loadNursePastShifts($data) {
        $id = $data['id'];
        $response = ['success' => false];

        $shiftData = $this->nurseRepository->getNursePastShifts($id);

        if($shiftData) {
            foreach($shiftData['shifts'] as $shift) {
                $response['shifts'][] = [
                    'name' => $shift['company'],
                    'date' => $shift['start_date']->format('m-d-Y'),
                    'time' => $shift['start_time']->format('g:i a'),
                    'status' => $shift['status'],
                    'sorting_date' => $shift['start_date']->format('Y-m-d') . ' ' . $shift['start_time']->format('h:i:s')
                ];
            }

            usort($response['shifts'], function($a, $b) {
                $aDate = new DateTime($a['date']);
                $bDate = new DateTime($b['date']);
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

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \sacore\application\IocException
     * @throws \Doctrine\ORM\ORMException
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \Exception
     */
    public function requestShift($data) {
        $id = $data['id'];
        $nurse_id = $data['nurse_id'];
        $response = ['success' => false];
        $user_type = $data['user_type'] ?: '';

        if($data['shift']) {
            $shift = $data['shift'];
        }

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurse_id]);

        $shiftService = new ShiftService();
        $rfcsResponse = $shiftService->removeFromConflictingShifts($nurse, $shift, $user_type);

        // If success is false, likely can't remove the nurse from a shift starting in the next 2 hours
        if(!$rfcsResponse['success']){
            return $response;
        }

        if($shift && $nurse) {
            $shift->setNurse($nurse);
            $shift->setStatus('Pending');
            $shift->setIsNurseApproved(true);

            // TODO - Add link for provider to approve or decline the requested shift
            // send twilio sms, and handle logging to SaShiftLogger
            if (!$data['is_creation']) {
                $this->smsService->handleSendSms($shift, ['message_type' => 'request_shift', 'by' => 'siteadmin', 'nurse' => $nurse]);
            } elseif ($data['is_creation']) {
                $this->smsService->handleSendSms($shift, ['message_type' => 'create_shift', 'by' => 'siteadmin', 'nurse' => $nurse]);
            }

            app::$entityManager->flush();
            $response['success'] = true;
        }

        return $response;
    }

    public function acceptShift($data) {
        $id = $data['id'];
        $nurse_id = $data['nurse_id'];
        $response = ['success' => false];
        $user_type = $data['user_type'] ?: 'nurse';

        if($data['shift']) {
            $shift = $data['shift'];
        }

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurse_id]);

        if($shift && $nurse) {
            if(!$shift->getProvider() || !$shift->getIsProviderApproved()) {
                return $response;
            }
            $shiftService = new ShiftService();
            $rfcsResponse = $shiftService->removeFromConflictingShifts($nurse, $shift, $user_type);

            // If success is false, likely can't remove the nurse from a shift starting in the next 2 hours
            if(!$rfcsResponse['success']){
                return $response;
            }
            
            $shift->setNurse($nurse);
            $shift->setStatus(Shift::STATUS_APPROVED);
            $shift->setIsNurseApproved(true);

            // TODO - Send provider a notification
            // Moved to ShiftService.php

            $response['success'] = true;
        }

        return $response;
    }

    public function declineShift($data) {
        $id = $data['id'];
        $nurse_id = $data['nurse_id'];
        $response = ['success' => false];
        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurse_id]);

        if($shift && $nurse) {
            if(!$shift->getProvider() || !$shift->getIsProviderApproved() || $shift->getStatus() != 'Assigned') {
                return $response;
            }
            $nurse->removeShift($shift);
            $shift->setNurse(null);
            $shift->setStatus('Open');
            $shift->setIsNurseApproved(false);
            $shift->setIsProviderApproved(false);

            // TODO - Send provider a notification
            // Moved to ShiftService.php

            $response['success'] = true;
        }

        return $response;
    }

    public function removeFromShift($data) {
        $id = $data['id'];
        $nurse_id = $data['nurse_id'];
        $response = ['success' => false];

        /** @var Shift $shift */
        /** @var Nurse $nurse */
        if($data['shift']) {
            $shift = $data['shift'];
            $nurse = $shift->getNurse();
        } else {
            $shift = ioc::get('Shift', ['id' => $id]);
            $nurse = ioc::get('Nurse', ['id' => $nurse_id]);
        }

        if($shift && $nurse) {
            $nurse->removeShift($shift);
            $shift->setNurse(null);
            $shift->setStatus('Open');
            $shift->setIsNurseApproved(false);
            $shift->setIsProviderApproved(false);

            // TODO - Send nurse a notification
            $notificationData = [
                'nurse' => $nurse,
                'type' => 'Removed',
                'shift' => $shift
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);
            // TODO - Send provider a notification
            $notificationData = [
                'provider' => $nurse,
                'type' => 'Removed',
                'shift' => $shift
            ];
            modRequest::request('nst.messages.sendShiftNotification', $notificationData);

            $response['success'] = true;
        }

        return $response;
    }

    public static function getNursesForNurseList($data) {
        $response = ['success' => false];
        $providerId = $data['provider_id'];
        $filters = $data['filters'];

        /** @var Provider $provider */
        $provider = ioc::get('Provider', ['id' => $providerId]);
        $prevNurses = $provider->getPreviousNurses();

        /** @var Nurse $nurse */
        foreach ($prevNurses as $nurse) {
            // Filter first

            $validNurse = true;

            if($nurse->getIsDeleted()) {
                $validNurse = false;
            }

            switch($filters['worked_with']) {
                case 'Yes':
                    $validNurse = $prevNurses->contains($nurse);
                    break;
                case 'No':
                    $validNurse = !$prevNurses->contains($nurse);
                    break;
                default:
                    break;
            }

            if($validNurse) {
                switch($filters['unresolved_pay']) {
                    case 'Yes':
                        $validNurse = self::hasUnresolvedPay($nurse);
                        break;
                    case 'No':
                        $validNurse = !self::hasUnresolvedPay($nurse);
                        break;
                    default:
                        break;
                }
            }

            if($validNurse) {
                switch($filters['blocked']) {
                    case 'Yes':
                        $validNurse = $provider->getBlockedNurses()->contains($nurse);
                        break;
                    case 'No':
                        $validNurse = !$provider->getBlockedNurses()->contains($nurse);
                        break;
                    default:
                        break;
                }
            }

            if(!$validNurse) {
                continue;
            }

            $member = $nurse->getMember();
            $response['nurses'][] = [
                'profile' => app::get()->getRouter()->generate('nurse_profile', ['id' => $nurse->getId()]),
                'first_name' => $member->getFirstName(),
                'last_name' => $member->getLastName(),
                'credentials' => is_array($nurse->getCredentials()) ? implode(', ', $nurse->getCredentials()) : $nurse->getCredentials()
            ];
        }

        $response['success'] = true;

        return $response;
    }

    /**
     * @param Nurse $nurse
     * @returns bool
     */
    public static function hasUnresolvedPay($nurse) {
        $shifts = $nurse->getShifts();

        /** @var Shift $shift */
        foreach($shifts as $shift) {
            if(($shift->getPayrollPayment() && $shift->getPayrollPayment()->getStatus() == 'Unresolved') ||
                ($shift->getBonusPayment() && $shift->getBonusPayment()->getStatus() == 'Unresolved')) {
                return true;
            }
        }

        return false;
    }

    public static function importNurses($data) {
        $response = ['success' => false];

        $path = app::get()->getConfiguration()->get('tempDir') . '/nurseimport.xlsx';

        $headers = [
            'Frist Name',
            'Last Name',
            'Credentials',
            'Street Address',
            'APT#',
            'City',
            'State',
            'Zip Code',
            'Phone',
            'Email Address',
            'SS#',
            'Password',
            'License',
            'TB',
            'CPR',
            'ACLS',
            'DOH',
            'DOB',
            'Routing #',
            'Account#'
        ];

        set_time_limit(7200);
        ini_set('memory_limit','512M');
        if(!$handle = fopen($path, 'r')) {
            echo "Unable to open excel file";exit;
        }
        $sheet_name = 'Sheet2';
        $start_row = 2;
        $column_array = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
        $num_rows = 700;

        /** @var Reader\Xlsx $reader */
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $readFilter = new ProviderImportReadFilter($num_rows, $column_array, $start_row);
        $reader->setReadFilter($readFilter);
        $spreadsheet = $reader->load($path);
        $reader->setLoadSheetsOnly([$sheet_name]);
        $worksheet = $spreadsheet->getActiveSheet();
        if(!$worksheet) {
            echo "Unable to access spreadsheet";
        }
        $usernames = [];

        for($i = $start_row; $i < $num_rows; $i++) {
            echo $i . "<br>\n";
            $cellValues = [
                'first_name' => $worksheet->getCell('A' . $i)->getValue(),
                'last_name' => $worksheet->getCell('B' . $i)->getValue(),
                'credentials' => $worksheet->getCell('C' . $i)->getValue(),
                'address' => $worksheet->getCell('D' . $i)->getValue(),
                'apt' => $worksheet->getCell('E' . $i)->getValue(),
                'city' => $worksheet->getCell('F' . $i)->getValue(),
                'state' => $worksheet->getCell('G' . $i)->getValue(),
                'zipcode' => $worksheet->getCell('H' . $i)->getValue(),
                'phone' => $worksheet->getCell('I' . $i)->getValue(),
                'email' => $worksheet->getCell('J' . $i)->getValue(),
                'dob' => $worksheet->getCell('K' . $i)->getValue(),
                'ssn' => $worksheet->getCell('L' . $i)->getValue(),
                'password' => $worksheet->getCell('M' . $i)->getValue(),
                'license' => $worksheet->getCell('N' . $i)->getValue(),
                'tb' => $worksheet->getCell('O' . $i)->getValue(),
                'cpr' => $worksheet->getCell('P' . $i)->getValue(),
                'acls' => $worksheet->getCell('Q' . $i)->getValue(),
                'doh' => $worksheet->getCell('R' . $i)->getValue(),
                'routing' => $worksheet->getCell('S' . $i)->getValue(),
                'account' => $worksheet->getCell('T' . $i)->getValue(),
            ];

            if (!$cellValues['first_name']) {
                continue;
            }

            $cellValues['first_name'] = str_replace(' ' , '', $cellValues['first_name']);
            $cellValues['last_name'] = str_replace(' ' , '', $cellValues['last_name']);

            $shouldPersist = false;
            $member = ioc::getRepository('NstMember')->findOneBy(['first_name' => $cellValues['first_name'], 'last_name' => $cellValues['last_name']]);
            if($member) {
                /** @var NstMember $member */
                $nurse = $member->getNurse();

                /** @var NstMemberUsers $user */
                $user = $member->getUsers()[0];
            }
            else {
                $shouldPersist = true;
                /** @var NstMember $member */
                $member = ioc::resolve('saMember');
                /** @var NstMemberUsers $user */
                $user = ioc::resolve('saMemberUsers');
                /** @var Nurse $nurse */
                $nurse = ioc::resolve('Nurse');
            }

            $newPassword = strtolower($cellValues['first_name'])
                        . explode('-', $cellValues['ssn'])[2];
            $username = strtolower(substr($cellValues['first_name'], 0, 1)) . strtolower($cellValues['last_name']);

            echo $i . ': ' . $username . ' -- ' . $newPassword . "\n";

            if(in_array($username, $usernames)) {
                $username .= $i;
            }
            $usernames[] = $username;

            try {
                $member->setFirstName($cellValues['first_name']);
                $member->setLastName($cellValues['last_name']);
                $member->setMemberType('Nurse');
                $user->setFirstName($cellValues['first_name']);
                $user->setLastName($cellValues['last_name']);
                $nurse->setFirstName($cellValues['first_name']);
                $nurse->setLastName($cellValues['last_name']);
//                if (!$user->getUsername()) {
                $user->setUsername($username);
                $user->setPassword($newPassword);
//                }
                $nurse->setCredentials($cellValues['credentials'] == 'KMA' ? 'CMT' : $cellValues['credentials']);
                $nurse->setStreetAddress($cellValues['address']);
                $nurse->setAptNumber($cellValues['apt']);
                $nurse->setCity($cellValues['city']);
                /** @var saState $state */
                $state = $cellValues['state'] ? ioc::getRepository('saState')->findOneBy(['abbreviation' => $cellValues['state']]) : null;
                $nurse->setState($state ? $state->getName() : '');
                $nurse->setZipcode($cellValues['zipcode']);
                $nurse->setPhoneNumber($cellValues['phone']);
                $nurse->setEmailAddress($cellValues['email']);
                $nurse->setSSN($cellValues['ssn']);
                $nurse->setLicenseExpirationDate($cellValues['license'] != null ? new DateTime($cellValues['license'], app::getInstance()->getTimeZone()) : null);
                $nurse->setSkinTestExpirationDate($cellValues['tb'] != null ? new DateTime($cellValues['tb'], app::getInstance()->getTimeZone()) : null);
                $nurse->setCprExpirationDate($cellValues['cpr'] != null ? new DateTime($cellValues['cpr'], app::getInstance()->getTimeZone()) : null);
                $nurse->setAclsExpirationDate($cellValues['acls'] != null ? new DateTime($cellValues['acls'], app::getInstance()->getTimeZone()) : null);
                $nurse->setDateOfHire($cellValues['doh'] != null ? new DateTime($cellValues['doh'], app::getInstance()->getTimeZone()) : null);
                $nurse->setDateOfBirth($cellValues['dob'] != null ? new DateTime($cellValues['dob'], app::getInstance()->getTimeZone()) : null);
                $nurse->setRoutingNumber($cellValues['routing'] ?: null);
                $nurse->setAccountNumber($cellValues['account'] ?: null);
                $nurse->setPaymentMethod($cellValues['routing'] && $cellValues['account'] ? 'Direct Deposit' : 'Pay Card');

                $member->setIsActive(true);
                $user->setIsActive(true);

                if(!$user->getEmail()) {
                    if(count($member->getEmails()) > 0) {
                        $user->setEmail($member->getEmails()[0]);
                    } else {
                        /** @var saMemberEmail $email */
                        $email = ioc::resolve('saMemberEmail');
                        $email->setEmail($cellValues['email'] ?? $cellValues['first_name'] . '_' . $cellValues['last_name'] . '@nursestatky.com');
                        $email->setIsActive(true);
                        $email->setIsPrimary(true);
                        $email->setMember($member);
                        $email->setType('Personal');
                        app::$entityManager->persist($email);
                        $user->setEmail($email);
                    }
                }

                if ($shouldPersist) {
                    app::$entityManager->persist($member);
                    app::$entityManager->persist($user);
                    app::$entityManager->persist($nurse);

                    $user->setMember($member);
                    $member->setNurse($nurse);
                    $nurse->setMember($member);
                }

                app::$entityManager->flush();
            } catch(\Throwable $e) {
                echo "Caught exception: " . $e->getMessage() . "\n";
            }
        }

        $response['success'] = true;
        return $response;
    }

    public static function getAccountData($data) {
        $response = ['success' => false];

        $id = $data['id'];
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);

        if($nurse) {
            $response['success'] = true;

            try {
                $phone = str_replace('-', '', $nurse->getPhoneNumber());
                $phone = str_replace(' ', '', $phone);
                $phone = str_replace('(', '', $phone);
                $phone = str_replace(')', '', $phone);
                $response['first_name'] = $nurse->getMember()->getFirstName();
                $response['last_name'] = $nurse->getMember()->getLastName();
                $response['middle_name'] = $nurse->getMember()->getMiddleName();
                $response['phone_number'] = $phone;
                $response['email'] = $nurse->getEmailAddress();
                $response['street_one'] = $nurse->getStreetAddress();
                $response['street_two'] = $nurse->getStreetAddress2();
                $response['zipcode'] = $nurse->getZipcode();
                $response['city'] = $nurse->getCity();
                $response['state'] = $nurse->getState();
                $response['dob_string'] = $nurse->getDateOfBirth() ? $nurse->getDateOfBirth()->format('m/d/Y') : '';
            }
            catch(\Exception $e) {
                echo "Exception: " . $e->getMessage();exit;
            }
        }

        return $response;
    }

    public static function saveAccountData($data) {
        $response = ['success' => false];

        $id = $data['id'];
        $account = $data['data'];

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $id]);
        if($nurse) {
            /** @var NstMember $member */
            $member = $nurse->getMember();

            $nurse->setFirstName($account['first_name']);
            $nurse->setMiddleName($account['middle_name']);
            $nurse->setLastName($account['last_name']);
            $nurse->setDateOfBirth($account['dob_string'] ? new DateTime($account['dob_string']) : null);
            $member->setFirstName($account['first_name']);
            $member->setMiddleName($account['middle_name']);
            $member->setLastName($account['last_name']);
            $nurse->setEmailAddress($account['email']);

            $nurse->setZipcode($account['zipcode']);
            $nurse->setStreetAddress($account['street_one']);
            $nurse->setStreetAddress2($account['street_two']);

            $nurse->setCity($account['city']);

            $state = ioc::getRepository('saState')->findOneBy(['name' => $account['state']]);
            if($account['state'] && !$state) {
                $response['message'] = 'Cannot find state with name: ' . $account['state'];
                return $response;
            }
            $nurse->setState($account['state']);

            app::$entityManager->flush();

            $response['success'] = true;
        }

        return $response;
    }

    public static function getAllStateNames($data) {
        $response = ['success' => false];

        /** @var NstStateRepository $stateRepo */
        $stateRepo = ioc::getRepository('NstState');
        $response['states'] = $stateRepo->getAllStateNames();

        $response['success'] = true;
        return $response;
    }

    public function getPayrollConfigurationData($data) {
        $response = ['success' => false];

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $data['id']]);
        $response['payment_method'] = $nurse->getPaymentMethod();
        $response['routing_number'] = $nurse->getRoutingNumber();
        $response['account_number'] = $nurse->getAccountNumber();
        $response['street_one'] = $nurse->getPaymentStreetAddress() ?? $nurse->getStreetAddress();
        $response['street_two'] = $nurse->getPaymentStreetAddress2() ?? $nurse->getStreetAddress2();
        $response['zipcode'] = $nurse->getPaymentZipcode() ?? $nurse->getZipcode();
        $response['city'] = $nurse->getPaymentCity() ?? $nurse->getCity();
        $response['state'] = $nurse->getPaymentState() ?? $nurse->getState();
        $response['pay_card_number'] = $nurse->getPayCardAccountNumber();

        $response['success'] = true;
        return $response;
    }

    public function savePayrollConfigurationData($data) {
        $response = ['success' => false];
        try {
            /** @var Nurse $nurse */
            $nurse = ioc::get('Nurse', ['id' => $data['id']]);

						/*	Checkr Pay Early Transition Nurses. Will be removing this code after initial transition date. July 22nd, 2024.
						*		The if check below skips saving any data so as not to overwrite the manually set payment_method.
						*/
						$checkrNurses = [
							'1795'
						];
						if (in_array($data['id'], $checkrNurses)) {
							$response['success'] = true;
        			return $response;
						}
						// While initally disabled, decided to use to differentiate paycard payments from direct deposit payments until Checkr Pay adds additional functionality.
            $nurse->setPaymentMethod($data['payment_method']);
            switch($data['payment_method']) {
                case 'Direct Deposit':
                    $nurse->setAccountNumber($data['account_number']);
                    $nurse->setRoutingNumber($data['routing_number']);
                    break;
                case 'Check':
                    $nurse->setPaymentStreetAddress($data['street_one']);
                    $nurse->setPaymentStreetAddress2($data['street_two']);
                    $nurse->setPaymentZipcode($data['zipcode']);
                    $nurse->setPaymentCity($data['city']);

                    $state = ioc::getRepository('saState')->findOneBy(['name' => $data['state']]);
                    if($data['state'] && !$state) {
                        $response['message'] = 'Cannot find state with name: ' . $data['state'];
                        return $response;
                    }
                    $nurse->setPaymentState($data['state']);
                    break;
                case 'Pay Card':
                    $nurse->setPayCardAccountNumber($data['pay_card_number']);
                    break;
                default:
                    break;
            }
            app::$entityManager->flush();

        } catch(\Throwable $e) {
            echo $e->getMessage();exit;
        }
        $response['success'] = true;
        return $response;
    }

    public function getPayReportsData($data) {
        $response = ['success' => false];

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $data['id']]);

        $payrollService = new PayrollService();
        $payPeriodData = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));
        $response['current_data'] = [];
        $response['current_data']['payments'] = [];
        $response['current_data']['pay_period'] = $payPeriodData['display'];
        $response['current_data']['payment_method'] = $nurse->getPaymentMethod();

        $start = $payPeriodData['start'];
        $end = $payPeriodData['end'];

        // Get payments for each provider and add the totals
        $serviceData = [
            'pay_period' => $payPeriodData['combined'],
            'nurse_id' => $data['id'],
            'return_payments' => true
        ];
        $payments = $payrollService->getShiftPayments($serviceData)['payments'];

        $providers = [];
        $overallPay = 0;
        $overallHours = 0;
        /** @var PayrollPayment $payment */
        foreach($payments as $payment) {
            $shift = $payment->getShift();
            $provider = $shift->getProvider();

            $total = $payment->getPayTotal();
            // If the provider is already in the list, just add to the totals
            if($p = $providers[$provider->getId()]) {
                $p['hours_worked'] += $payment->getClockedHours();
                $p['pay_total'] += $payment->getPayTotal();
                $p['total'] += $total;
                $p['shifts_worked'] += 1;
                $p['bonus'] += $payment->getPayBonus();
                $p['travel'] += $payment->getPayTravel();
                $p['payments'][] = [
                    'id' => $payment->getId(),
                    'hours' => number_format($payment->getClockedHours(), 2),
                    'total' => '$' . number_format($total, 2),
                    'pay_rate' => '$' . number_format($payment->getPayRate(), 2),
                    'bonus' => $payment->getPayBonus() ? '$' . number_format($payment->getPayBonus(), 2) : 'None',
                    'covid' => $shift->getIsCovid() ? 'Yes' : 'No',
                    'incentive' => $shift->getIncentive() ? $shift->getIncentive() . 'x' : 'None',
                    'travel' => $payment->getPayTravel() ? '$' . number_format($payment->getPayTravel(), 2) : 'None',
                    'status' => $payment->getStatus(),
                    'start' => $shift->getStart()->format('Y-m-d H:i:s'),
                    'end' => $shift->getEnd()->format('Y-m-d H:i:s')
                ];
                $providers[$provider->getId()] = $p;
            } else {
                // Else add the provider to the list
                $providers[$provider->getId()] = [
                    'hours_worked' => $payment->getClockedHours(),
                    'pay_total' => $payment->getPayTotal(),
                    'total' => $total,
                    'bonus' => $payment->getPayBonus(),
                    'travel' => $payment->getPayTravel(),
                    'shifts_worked' => 1,
                    'provider_name' => $provider->getMember()->getCompany(),
                    'payments' => [[
                        'id' => $payment->getId(),
                        'hours' => number_format($payment->getClockedHours(), 2),
                        'total' => '$' . number_format($total, 2),
                        'pay_rate' => '$' . number_format($payment->getPayRate(), 2),
                        'bonus' => $payment->getPayBonus() ? '$' . number_format($payment->getPayBonus(), 2) : 'None',
                        'covid' => $shift->getIsCovid() ? 'Yes' : 'No',
                        'incentive' => $shift->getIncentive() ? $shift->getIncentive() . 'x' : 'None',
                        'travel' => $payment->getPayTravel() ? '$' . number_format($payment->getPayTravel(), 2) : 'None',
                        'status' => $payment->getStatus(),
                        'start' => $shift->getStart()->format('Y-m-d H:i:s'),
                        'end' => $shift->getEnd()->format('Y-m-d H:i:s')
                    ]]
                ];
            }
            $overallPay += $payment->getPayTotal();
            $overallHours += $payment->getClockedHours();

            $response['success'] = true;
        }
        foreach($providers as $id => $p) {
            $response['current_data']['providers'][] = [
                'id' => $id,
                'bonus' => $p['bonus'] ? '$' . number_format($p['bonus'], 2) : 'None',
                'travel' => $p['travel'] ? '$' . number_format($p['travel'], 2) : 'None',
                'hours_worked' => number_format($p['hours_worked'], 2),
                'total' => '$' . number_format($p['total'], 2),
                'shifts_worked' => $p['shifts_worked'],
                'provider_name' => $p['provider_name'],
                'payments' => $p['payments']
            ];
        }
        $response['current_data']['overall_pay'] = $overallPay;
        $response['current_data']['overall_hours'] = $overallHours;


        $payPeriodTotals = $this->getPayPeriodTotalsForNurse($nurse, false);
        $payPeriods = [];
        if($payPeriodTotals) {
            foreach($payPeriodTotals as $k => $v) {
                $payPeriods[] = [
                    'pay_period' => $k,
                    'hours' => number_format($v['hours'], 2),
                    'total' => '$' . number_format($v['total'], 2)
                ];
            }
        }

        $page = $data['page'];
        usort($payPeriods, function($a, $b) {
            $aStart = new DateTime(explode('_', $a['pay_period'])[0]);
            $bStart = new DateTime(explode('_', $b['pay_period'])[0]);

            return $aStart <= $bStart ? 1 : -1;
        });
        $itemsPerPage = 10;
        $totalPages = ceil(count($payPeriods) / $itemsPerPage);
        $payPeriods = array_chunk($payPeriods, $itemsPerPage)[$page - 1];

        $response['previous_data']['total_pages'] = $totalPages;
        $response['previous_data']['pay_periods'] = $payPeriods;
        $response['success'] = true;
        return $response;
    }

    public function mergeDuplicateNurse($data){
        $response = ['success' => false];
        $this->nurseRepository = ioc::getRepository('Nurse');

        $primaryNurse = $this->nurseRepository->findOneBy(['id' => $data['primaryNurseId']]);
        $primaryMember = $primaryNurse->getMember();
        $primaryNurseApplication  = $primaryMember->getNurseApplication();

        $duplicateNurse = $this->nurseRepository->findOneBy(['id' => $data['duplicateNurseId']]);
        $duplicateMember = $duplicateNurse->getMember();
        $duplicateNurseApplication  = $duplicateMember->getNurseApplication();


        // migrate shifts to primary nurse
        $shiftService = new ShiftService();
        $shifts = $shiftService->migrateShiftsToPrimaryNurse($data);

        foreach($shifts as $shift){
            echo "<pre>";
            \Doctrine\Common\Util\Debug::dump($shift);
            echo "</pre>";
        }
        die();
        //

        $response['success'] = true;
        return $response;
    }

    public function search($data){
        $response = ['success' => false];
        $this->nurseRepository = ioc::getRepository('Nurse');

        $results = $this->nurseRepository->search(
            $data['fields'],
            $data['order_by'],
            $data['per_page'],
            $data['offset'],
            $data['count'],
            $data['secondary_sort'],
            $data['where_andor'],
            $data['search_start'],
            $data['search_end']
        );

        $response['fields'] = $data['fields'];
        $response['nurses'] = doctrineUtils::getEntityCollectionArray($results);
        $response['success'] = true;
        return $response;
    }

    public function getMetaData($data){
        $response = ['success' => false];
        $metaData = app::$entityManager->getClassMetadata(ioc::staticResolve('Nurse'));

        $response['metaData'] = $metaData->getFieldNames();

        $response['success'] = true;
        return $response;
    }

    public function mergeNurseData($data){
        $response = ['success' => false];

        $primaryNurse = ioc::get('Nurse', ['id' => $data['primaryNurseId'], 'is_deleted' => [null, false]]);
        $duplicateNurse = ioc::get('Nurse', ['id' => $data['duplicateNurseId'], 'is_deleted' => [null, false]]);
        if(!is_object($primaryNurse) || !is_object($duplicateNurse)){
            $response['message'] = 'Error locating nurses.';
            return $response;
        }
        $primaryNurseArray = doctrineUtils::getEntityArray($primaryNurse);
        $duplicateNurseArray = doctrineUtils::getEntityArray($duplicateNurse);

        $fieldsChanged = 0;
        foreach($primaryNurseArray as $key => $pField){
            if($pField == null || $pField == ""){
                if($duplicateNurseArray[$key] != null && $duplicateNurseArray[$key] != ""){
                    $primaryNurseArray[$key] = $duplicateNurseArray[$key];
                    $fieldsChanged++;
                }
            }
        }

        doctrineUtils::setEntityData($primaryNurseArray, $primaryNurse);
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public function nurseDeactivate($nurseId){
        $response = ['success' => false];

        $nurse = ioc::get('Nurse', ['id' => $nurseId, 'is_deleted' => [null, false]]);
        if(!is_object($nurse)){
            $response['message'] = 'Could not locate nurse.';
            return $response;
        }
        $nurse->getMember()->setIsActive(false);
        $nurse->getMember()->setIsDeleted(true);
        $nurse->setIsDeleted(true);
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function importNurseSkintestAndVaccineData() {
        $response = ['success' => false];
        $app = app::get();
        $io = $app->getCliIO();
        $io->title('Import Nurse Skin test and License information');
        $selectField = $io->ask('Are you sure you want to run this import? y/n', 'y');
        if(strtolower($selectField) != 'y'){
            $io->section(' Exiting.');
            return $response;
        }

        $io->writeln('Looking for Xlsx file: ExpirationDates.xlsx...');

        $path = app::get()->getConfiguration()->get('tempDir') . '/ExpirationDates.xlsx';
        if(!$handle = fopen($path, 'r')) {
            $io->writeln('Unable to locate or open Xlsx file...');
            $io->section(' Exiting.');
            return $response;
        }

        $io->writeln('Located file and proceding.');

        $skippedLogPath = app::get()->getConfiguration()->get('tempDir') . '/nurse_tb_license_skip.log';
        //clear log to start
        file_put_contents($skippedLogPath, '' );

        $sheet_name = 'Sheet2';
        $start_row = 2;
        $column_array = ['A', 'B', 'C', 'D', 'E'];
        $num_rows = 958;

        /** @var Reader\Xlsx $reader */
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $readFilter = new ProviderImportReadFilter($num_rows, $column_array, $start_row);
        $reader->setReadFilter($readFilter);
        $spreadsheet = $reader->load($path);
        $reader->setLoadSheetsOnly([$sheet_name]);
        $worksheet = $spreadsheet->getActiveSheet();
        if(!$worksheet) {
            $io->writeln('Unable to access file' );
        }

        for($i = $start_row; $i < $num_rows; $i++) {
            $tbDate = $worksheet->getCell('C' . $i)->getValue();
            if (!is_numeric($tbDate) && $tbDate != 'BLANK') {
                $tbDate = new DateTime($tbDate);
            } else if ($tbDate === 'BLANK') {
                $tbDate = null;
            } else {
                $daysToModTb = $tbDate;
                $tbDate = new DateTime('1899-12-31');
                $tbDate->modify("+$daysToModTb day -1 day");
            }

            $licenseDate = $worksheet->getCell('D' . $i)->getValue();
            if (!is_numeric($licenseDate) && $licenseDate != 'BLANK') {
                $licenseDate = new DateTime($licenseDate);
            } else if ($licenseDate === 'BLANK') {
                $licenseDate = null;
            } else {
                $daysToModLicense = $licenseDate;
                $licenseDate = new DateTime('1899-12-31');
                $licenseDate->modify("+$daysToModLicense day -1 day");
            }

            $cellValues = [
                'first_name' => $worksheet->getCell('A' . $i)->getValue(),
                'last_name' => $worksheet->getCell('B' . $i)->getValue(),
                'TB Skin Test' => $tbDate,
                'License' => $licenseDate,
                'Profile' => $worksheet->getCell('E' . $i)->getValue(),
            ];

            if (!$cellValues['first_name']) {
                continue;
            }

            $nurse = ioc::getRepository('Nurse')->findOneBy(['first_name' => $cellValues['first_name'], 'last_name' => $cellValues['last_name']]);
            if(!$nurse) {
                $message = 'Skipping line '. $i .', name: ' . $cellValues['first_name'] . ' ' . $cellValues['last_name'] . '. No Nurse found.';
                $io->writeln($message);
                file_put_contents($skippedLogPath, $message . PHP_EOL, FILE_APPEND);
            } else {
                $io->writeln('Line:  '. $i .', Skin Test: ' . $cellValues['TB Skin Test']?->format('Y-m-d') . ' License: ' . $cellValues['License']?->format('Y-m-d'));
            }


            //Only flush when we have to - PERFORMANCE
            $shouldFlush = false;
            try {
                if ($cellValues['TB Skin Test']){
                    $nurse->setSkinTestExpirationDate($cellValues['TB Skin Test']);
                    $shouldFlush = true;
                }
                if ($cellValues['License']) {
                    $nurse->setLicenseExpirationDate($cellValues['License']);
                    $shouldFlush = true;
                }

                if ($shouldFlush) {
                    app::$entityManager->flush($nurse);
                }
            } catch(\Throwable $e) {
                $io->writeln('Caught exception: ' . $e->getMessage());
            }

            // Clear every 100, just in case - PERFORMANCE
            if($i % 100) {
                app::$entityManager->clear();
            }
        }
        $io->writeln('Completing successfully.');

        $response['success'] = true;

        return $response;
    }

    public function setUpNursePayPeriodTotals() {
//        $nurses = ioc::getRepository('Nurse')->findAll();

        $nurses = ioc::getRepository('Nurse')->findBy(['first_name' => 'Kelvin']);
        /** @var Nurse $nurse */
        foreach($nurses as $nurse) {
            echo "Nurse: " . $nurse->getId() . "\n";
            $this->getPayPeriodTotalsForNurse($nurse);
        }
        echo "finished";exit;
    }

    private function getPayPeriodTotalsForNurse($nurse, $includeCurrent = true) {


        $payPeriodTotals = [];

        $shifts = ioc::getRepository('Shift')->findBy(['nurse' => $nurse, 'status' => 'Completed']);
        $shiftRecurrences = ioc::getRepository('ShiftRecurrence')->findBy(['nurse' => $nurse, 'status' => 'Completed']);
        $now = new DateTime('now', app::get()->getTimeZone());
        /** @var Shift $shift */
        foreach($shifts as $shift) {
            $payment = $shift->getPayrollPayment();
            $otPayment = $shift->getOvertimePayment();

            // Check validity
            if((!$payment && !$otPayment)
                || !$shift->getProvider()
                || $payment->getStatus() == 'Unresolved'
                || $payment->getBillTotal() <= 0) continue;

            // Find the pay period for the shift

            $payPeriodData = $this->payrollService->calculatePayPeriodFromDate($shift->getStart());
            $payPeriod = $payPeriodData['combined'];

            if(!$includeCurrent && $payPeriodData['end'] > $now) {
                continue;
            }

            // Initialize pay period in the array if it doesn't exist, and add totals for the pay period
            if($payment) {
                $payPeriodTotals[$payPeriod]['total'] = $payPeriodTotals[$payPeriod]['total'] ? $payPeriodTotals[$payPeriod]['total'] + $payment->getPayTotal() : $payment->getPayTotal();
                $payPeriodTotals[$payPeriod]['hours'] = $payPeriodTotals[$payPeriod]['hours'] ? $payPeriodTotals[$payPeriod]['hours'] + $payment->getClockedHours() : $payment->getClockedHours();
            }
            if($otPayment) {
                $payPeriodTotals[$payPeriod]['total'] = $payPeriodTotals[$payPeriod]['total'] ? $payPeriodTotals[$payPeriod]['total'] + $otPayment->getPayTotal() : $otPayment->getPayTotal();
                $payPeriodTotals[$payPeriod]['hours'] = $payPeriodTotals[$payPeriod]['hours'] ? $payPeriodTotals[$payPeriod]['hours'] + $otPayment->getClockedHours() : $otPayment->getClockedHours();
            }
        }

//        /** @var ShiftRecurrence $shift */
//        foreach($shiftRecurrences as $shift) {
//            $payment = $shift->getPayrollPayment();
//            $otPayment = $shift->getOvertimePayment();
//
//            // Check validity
//            if((!$payment && !$otPayment)
//                || !$shift->getProvider()
//                || $payment->getStatus() == 'Unresolved'
//                || $payment->getBillTotal() <= 0) continue;
//
//            // Find the pay period for the shift
//
//            $payPeriodData = $this->payrollService->calculatePayPeriodFromDate($shift->getStart());
//            $payPeriod = $payPeriodData['combined'];
//
//            // Initialize pay period in the array if it doesn't exist, and add totals for the pay period
//            if($payment) {
//                $payPeriodTotals[$payPeriod]['total'] = $payPeriodTotals[$payPeriod]['total'] ? $payPeriodTotals[$payPeriod]['total'] + $payment->getPayTotal() : $payment->getPayTotal();
//                $payPeriodTotals[$payPeriod]['hours'] = $payPeriodTotals[$payPeriod]['hours'] ? $payPeriodTotals[$payPeriod]['hours'] + $payment->getClockedHours() : $payment->getClockedHours();
//            }
//            if($otPayment) {
//                $payPeriodTotals[$payPeriod]['total'] = $payPeriodTotals[$payPeriod]['total'] ? $payPeriodTotals[$payPeriod]['total'] + $otPayment->getPayTotal() : $otPayment->getPayTotal();
//                $payPeriodTotals[$payPeriod]['hours'] = $payPeriodTotals[$payPeriod]['hours'] ? $payPeriodTotals[$payPeriod]['hours'] + $otPayment->getClockedHours() : $otPayment->getClockedHours();
//            }
//        }

        $nurse->setPayPeriodTotals($payPeriodTotals);
        app::$entityManager->flush();

        return $payPeriodTotals;
    }


    public function sendSmsCodeToNurse($phone_number) {
        // generate code
        $code = strval(rand(100000, 999999));

        $sms_body = 'Verification Code: ' . $code;

        /**
         * @var \nst\messages\SmsVerificationCode $smsVerificationCode
         */
        $smsVerificationCode = ioc::resolve('SmsVerificationCode');

        $smsVerificationCode->setCode($code);
        $smsVerificationCode->setPhoneNumber($phone_number);
        $smsVerificationCode->setTimeSent(new \DateTime());

        app::$entityManager->persist($smsVerificationCode);
        app::$entityManager->flush($smsVerificationCode);

        // send sms
        modRequest::request('messages.startSMSBatch');
        modRequest::request('messages.sendSMS', array('phone' => $phone_number, 'body' => $sms_body));
        modRequest::request('messages.commitSMSBatch');

        return $code;
    }

    public function updateNursePhone($phone_number, $verification_code, $nurse_id) {
        /** @var \nst\messages\SmsVerificationCode $smsVerification */
        $sms_verification = ioc::getRepository('SmsVerificationCode')->findBy(['phone_number' => $phone_number], ['id' => 'DESC'])[0];

        // make sure the code was sent 15 mins ago or less
        $time_diff = (new DateTime())->diff($sms_verification->getTimeSent());
        $not_expired = $time_diff->y == 0
            && $time_diff->m == 0
            && $time_diff->d == 0
            && $time_diff->h == 0
            && $time_diff->i <= 15; // ensure it was sent less than 15 mins ago

        if ($sms_verification->getCode() == $verification_code && $not_expired) {
            /** @var Nurse $nurse */
            $nurse = ioc::get('Nurse', ['id' => $nurse_id]);
            if ($nurse) {
                $nurse->setPhoneNumber($phone_number);
                app::$entityManager->flush($nurse);
            }
            return true;
        } 
        else {
            // expired or invalid code
            return false;
        }
    }
    
    public function documentExpirationNotificationCron() {
        $nurses = ioc::getRepository('Nurse')->findAll();
        $now = new DateTime((new Datetime())->format('Y-m-d'));
        file_put_contents(
            app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'documentExpirationNotificationLog.log', 
            $now->format('Y-m-d') . ' BEGIN:' . PHP_EOL, 
            FILE_APPEND);

        /** @var Nurse $nurse */
        foreach ($nurses as $nurse) {
            // Nursing License expiration
            $nursingLicenseExpDate = $nurse->getLicenseExpirationDate();
            if ($nursingLicenseExpDate) {
                $nursingLicenseExpDate = new DateTime($nursingLicenseExpDate->format('Y-m-d'));
                $days = (int)$now->diff($nursingLicenseExpDate)->format("%r%a");
                
                if ($days === 30 || $days === 15 || $days === 1) {
                    $this->handleDocumentExpirationNotification($days, 'Nursing License', $nurse);
                }
            }

            // TB Skin Test expiration
            $skinTestExpDate = $nurse->getSkinTestExpirationDate();
            if ($skinTestExpDate) {
                $skinTestExpDate = new DateTime($skinTestExpDate->format('Y-m-d'));
                $days = (int)$now->diff($skinTestExpDate)->format("%r%a");
                
                if ($days === 30 || $days === 15 || $days === 1) {
                    $this->handleDocumentExpirationNotification($days, 'TB Skin Test', $nurse);
                }
            }

            // CPR expiration
            $cprExpDate = $nurse->getCprExpirationDate();
            if ($cprExpDate) {
                $cprExpDate = new DateTime($cprExpDate->format('Y-m-d'));
                $days = (int)$now->diff($cprExpDate)->format("%r%a");
                
                if ($days === 30 || $days === 15 || $days === 1) {
                    $this->handleDocumentExpirationNotification($days, 'CPR', $nurse);
                }
            }
        }

        return;
    }

    /**
     * @param int $days
     * @param string $documentName
     * @param Nurse $nurse
     */
    public function handleDocumentExpirationNotification($days, $documentName, $nurse) 
    {
        $notificationService = new NstPushNotificationService();
        
        file_put_contents(
            app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'documentExpirationNotificationLog.log', 
            'Nurse: ' . $nurse->getFirstName() . ' ' . $nurse->getLastName() . ", Nurse ID: " . $nurse->getId() . ", $documentName is expiring in $days days" . PHP_EOL, 
            FILE_APPEND);

        $notificationData = [
            'title' => "$documentName Document expiring",
            'message' => "Your $documentName is expiring in $days days",
            'nurse_id' => $nurse->getId()
        ];

        return $notificationService->sendNotificationToNurse($notificationData);
    }
}