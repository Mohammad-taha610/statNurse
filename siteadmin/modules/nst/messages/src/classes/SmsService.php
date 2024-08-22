<?php

namespace nst\messages;

use nst\events\SaShiftLogger;
use nst\member\Nurse;
use nst\member\Provider;
use nst\member\NurseApplication;
use nst\member\NurseApplicationPartTwo;
use sacore\application\modRequest;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;

class SmsService
{
    /** @var \SaShiftLogger $shiftLogger */
    protected $shiftLogger;

    public function __construct()
    {
        $this->shiftLogger = new SaShiftLogger();
    }

    public function handleSendSms($shift, $data) 
    {
        $response = ['success' => false];
        if(!$shift) {
            return $response;
        }

        switch ($data['message_type']) {
            case 'deny_request':
                self::denyMessage($shift, $data);
                break;
            case 'approve_request':
                self::approveMessage($shift, $data);
                break;
            case 'remove_shift':
                self::removeMessage($shift, $data);
                break;
            case 'deleted_shift':
                self::deletedMessage($shift, $data);
                break;
            case 'assign_shift':
                self::assignMessage($shift, $data);
                break;
            case 'create_shift':
                self::createMessage($shift, $data);
                break;
            case 'call_in':
                self::callInMessage($shift, $data);
                break;    
            case 'request_shift':
                self::requestMessage($shift, $data);
                break;
            case 'cancel_shift':
                self::cancelMessage($shift, $data);
                break;
            default:
        }
    }

    public function shouldSendSmsToNurse($shiftStart) 
    {
        $days = app::get()->getConfiguration()->get('nurse_send_sms_up_to_days')->getValue();
        $today = new DateTime('NOW');
        $limit = new DateTime($today->format('Y-m-d') . ' 23:59:59');
        $limit->modify("+$days days");

        if($shiftStart->getTimestamp() <= $limit->getTimestamp()){
            return true;
        }

        return false;
    }

    public function sendSms($message, $phoneNumber) 
    {
        modRequest::request('messages.startSMSBatch');
        modRequest::request('messages.sendSMS', array('phone' => $phoneNumber, 'body' => $message));
        modRequest::request('messages.commitSMSBatch');
    }

    public function sendTwilioSms($message, $phone)
    {
        $response = ['success' => false];
    
        try {

            $twilio_sid = app::get()->getConfiguration()->get('twilio_sid')->getValue();
            $twilio_token = app::get()->getConfiguration()->get('twilio_token')->getValue();
            $twilio_phone = app::get()->getConfiguration()->get('twilio_phonenumber')->getValue();
            $client = new \Twilio\Rest\Client($twilio_sid, $twilio_token);

            $client->messages->create(
                $phone,
                [
                    'from' => $twilio_phone,
                    'body' => $message
                ]
            );

            $response['success'] = true;
        } catch (\Exception $e) {

            $response['error'] = $e->getMessage();
            $response['recipient'] = $phone;
            return $response;
        }

        return $response;
    }

    public function denyMessage($shift, $data) 
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = $shift->getNurse();
        $nursePhone = $nurse->getPhoneNumber();
        $nurseCreds = $nurse->getCredentials();
        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')' : $nurse->getFirstName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';

        if ($data['by'] == 'provider') {
            $nurseMessage = 'REQUESTED SHIFT - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was DENIED by ' . $provider_name . '.';
            if(self::shouldSendSmsToNurse($shift->getStart())) {
                self::sendSms($nurseMessage, $nursePhone);
            }
        } elseif ($data['by'] == 'siteadmin') {
            // $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            // $providerMessage = 'DENIED SHIFT - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been DENIED by NurseStat';
            // $provider = $shift->getProvider();
            // $contacts = $provider->getContacts();
            // foreach ($contacts as $contact) {
            //     if ($contact->getReceivesSMS()) {
            //         self::sendSms($providerMessage, $contact->getPhoneNumber());
            //     }
            // }
            
            $nurseMessage = 'REQUESTED SHIFT DENIED - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was DENIED by NurseStat.';

            if($nurse && $nurse->getReceivesSMS()) {
                if(self::shouldSendSmsToNurse($shift->getStart())) {
                    self::sendSms($nurseMessage, $nursePhone);
                }
            }
        }        
    }

    public function approveMessage($shift, $data) 
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = $shift->getNurse();
        $nursePhone = $nurse->getPhoneNumber();
        $nurseCreds = $nurse->getCredentials();
        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')' : $nurse->getFirstName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';

        if ($data['by'] == 'provider') {
            $nurseMessage = 'REQUESTED SHIFT - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was APPROVED by ' . $provider_name . '.';
            if(self::shouldSendSmsToNurse($shift->getStart())) {
                self::sendSms($nurseMessage, $nursePhone);
            }
        } elseif ($data['by'] == 'siteadmin') {
            // $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            // $providerMessage = 'APPROVED SHIFT - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been APPROVED by NurseStat';
            // $provider = $shift->getProvider();
            // $contacts = $provider->getContacts();
            // foreach ($contacts as $contact) {
            //     if ($contact->getReceivesSMS()) {
            //         self::sendSms($providerMessage, $contact->getPhoneNumber());
            //     }
            // }
            
            $nurseMessage = 'REQUESTED SHIFT APPROVED - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was APPROVED by NurseStat.';

            if($nurse && $nurse->getReceivesSMS()) {
                if(self::shouldSendSmsToNurse($shift->getStart())) {
                    self::sendSms($nurseMessage, $nursePhone);
                }
            }
        }        
    }

    public function removeMessage($shift, $data) 
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = $shift->getNurse();
        $nursePhone = $nurse->getPhoneNumber();
        $nurseCreds = $nurse->getCredentials();
        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')' : $nurse->getFirstName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';

        if ($data['by'] == 'provider') {
            $nurseMessage = $shift->getStatus() . ' SHIFT REMOVED - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was REMOVED by ' . $provider_name . '.';
            if(self::shouldSendSmsToNurse($shift->getStart())) {
                self::sendSms($nurseMessage, $nursePhone);
            }
        } elseif ($data['by'] == 'siteadmin') {
            $providerMessage = $shift->getStatus() . ' SHIFT REMOVED - for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been REMOVED by NurseStat';
            $nurseMessage = $shift->getStatus() . ' SHIFT REMOVED - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was REMOVED by NurseStat.';
            $provider = $shift->getProvider();
            $contacts = $provider->getContacts();
            
            // new restriction to only send if in the next day
            $in24hours = (new DateTime('now'))->modify('+24 hours');
            if($this->isInNext24Hours($shift) && $shift->getStatus() == "Approved") {
                foreach ($contacts as $contact) {
                    if ($contact->getReceivesSMS()) {
                        self::sendSms($providerMessage, $contact->getPhoneNumber());
                    }
                }
            }

            if($nurse && $nurse->getReceivesSMS()) {
                if(self::shouldSendSmsToNurse($shift->getStart())) {
                    self::sendSms($nurseMessage, $nursePhone);
                }
            }
        }        
    }

    public function deletedMessage($shift, $data) 
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = $shift->getNurse();
        // This will also not send a message to the provider, which is preferable
        if (!$nurse) {
            return;
        }
        $nursePhone = $nurse->getPhoneNumber();
        $nurseCreds = $nurse->getCredentials();
        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')' : $nurse->getFirstName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';

        if ($data['by'] == 'provider') {
            $nurseMessage = $shift->getStatus() . ' SHIFT REMOVED - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was REMOVED by ' . $provider_name . '.';
            if(self::shouldSendSmsToNurse($shift->getStart())) {
                self::sendSms($nurseMessage, $nursePhone);
            }
        } elseif ($data['by'] == 'siteadmin') {
            $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            $providerMessage = $shift->getStatus() . ' SHIFT REMOVED - ' . $intro . ' Shift for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been REMOVED by NurseStat';
            $nurseMessage = $shift->getStatus() . ' SHIFT REMOVED - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was REMOVED by NurseStat.';
            $provider = $shift->getProvider();
            $contacts = $provider->getContacts();
            
            // new restriction to only send if in the next day
            if($this->isInNext24Hours($shift) && $shift->getStatus() == "Approved") {
                foreach ($contacts as $contact) {
                    if ($contact->getReceivesSMS()) {
                        self::sendSms($providerMessage, $contact->getPhoneNumber());
                    }
                }
            }

            if($nurse && $nurse->getReceivesSMS()) {
                if(self::shouldSendSmsToNurse($shift->getStart())) {
                    self::sendSms($nurseMessage, $nursePhone);
                }
            }
        }        
    }

    public function assignMessage($shift, $data) 
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = is_object($data['nurse']) ? $data['nurse'] : $shift->getNurse();
        $nursePhone = $nurse->getPhoneNumber();
        $nurseCreds = $nurse->getCredentials();
        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')' : $nurse->getFirstName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';

        if ($data['by'] == 'provider') {
            $nurseMessage = 'SHIFT ASSIGNED TO YOU - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was ASSIGNED TO YOU by ' . $provider_name . '.';
            if(self::shouldSendSmsToNurse($shift->getStart())) {
                self::sendSms($nurseMessage, $nursePhone);
            }
        } elseif ($data['by'] == 'siteadmin') {
            // $intro = ($shift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($shift->getStatus());
            // $providerMessage = 'SHIFT ASSIGNED - TO ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been ASSIGNED by NurseStat';
            // $provider = $shift->getProvider();
            // $contacts = $provider->getContacts();
            // foreach ($contacts as $contact) {
            //     if ($contact->getReceivesSMS()) {
            //         self::sendSms($providerMessage, $contact->getPhoneNumber());
            //     }
            // }

            $nurseMessage = 'SHIFT ASSIGNED TO YOU - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was ASSIGNED TO YOU by NurseStat.';
            
            if($nurse && $nurse->getReceivesSMS()) {
                if(self::shouldSendSmsToNurse($shift->getStart())) {
                    self::sendSms($nurseMessage, $nursePhone);
                }
            }
        }        
    }

    public function createMessage($shift, $data)
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = is_object($data['nurse']) ? $data['nurse'] : $shift->getNurse();
        $nursePhone = $nurse?->getPhoneNumber();
        $nurseCreds = $nurse?->getCredentials();
        $nurse_name = $nurse?->getMiddleName() !== '' ? $nurse?->getFirstName() . ' ' . $nurse?->getMiddleName() . ' ' . $nurse?->getLastName() . '(' . $nurseCreds . ')' : $nurse?->getFirstName() . ' ' . $nurse?->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';

        if ($data['by'] == 'provider') {
            $nurseMessage = 'SHIFT ASSIGNED TO YOU - for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was ASSIGNED TO YOU by ' . $provider_name . '.';
            if(self::shouldSendSmsToNurse($shift->getStart())) {
                self::sendSms($nurseMessage, $nursePhone);
            }
        } elseif ($data['by'] == 'siteadmin') {
            $providerMessage = $shift->getStatus() . ' SHIFT CREATED - for ' . $nurse_name . ' on ' . $date . ' at ' . $time . ' has been CREATED by NurseStat';
            $nurseMessage = $shift->getStatus() . ' SHIFT CREATED FOR YOU - ' . $shift->getStatus() .' shift for ' . $provider_name . ' on ' . $date . ' at ' . $time . ' was CREATED FOR YOU by NurseStat.';
            $provider = $shift->getProvider();
            $contacts = $provider->getContacts();

            // new restriction to only send if in the next day
            if($this->isInNext24Hours($shift) && $shift->getStatus() == "Approved") {
                foreach ($contacts as $contact) {
                    if ($contact->getReceivesSMS()) {
                        self::sendSms($providerMessage, $contact->getPhoneNumber());
                    }
                }
            }
            
            if($nurse && $nurse->getReceivesSMS()){
                if(self::shouldSendSmsToNurse($shift->getStart())) {
                    self::sendSms($nurseMessage, $nursePhone);
                }
            }
        }
    }

    // CAHNGE THIS TO SEND REQUEST DATA FOR SMS, ETC
    public function requestMessage($shift, $data)
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = is_object($data['nurse']) ? $data['nurse'] : '';
        $nursePhone = $nurse?->getPhoneNumber();
        $nurseCreds = $nurse?->getCredentials();
        $nurseName = $nurse?->getMiddleName() !== '' ? $nurse?->getFirstName() . ' ' . $nurse?->getMiddleName() . ' ' . $nurse?->getLastName() . '(' . $nurseCreds . ')' : $nurse?->getFirstName() . ' ' . $nurse?->getLastName() . '(' . $nurseCreds . ')';
        $providerName = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';
        $shiftNurseType = $shift->getNurseType();

        if ($data['by'] == 'nurse') {
            $providerMessage = "REQUESTED - ($shiftNurseType) shift on $date at $time has been requested by $nurseName";
            $nurseMessage = "REQUESTED SHIFT - Shift at $providerName on $date at $time was REQUESTED successfully";
            $provider = $shift->getProvider();
            $contacts = $provider->getContacts();

            // send if in next 48 hours
            if($this->isInNext48Hours($shift)) {
                foreach ($contacts as $contact) {
                    if ($contact->getReceivesSMS()) {
                        self::sendSms($providerMessage, $contact->getPhoneNumber());
                    }
                }
            }
            
            if($nurse && $nurse->getReceivesSMS()){
                if(self::shouldSendSmsToNurse($shift->getStart())) {
                    self::sendSms($nurseMessage, $nursePhone);
                }
            }
            app::get()->getLogger()->addError('requestShift (nurse): ' . $nurseMessage);
            
            // This was a poor decission. Should abstract logging out of smsService ASAP 
            $logMessage = "Shift for facility $providerName on $date at $time has been Requested by $nurseName (Nurse)";
            $this->shiftLogger->log($logMessage, ['action' => 'REQUESTED']);
        } elseif ($data['by'] == 'siteadmin') {
            // SMS not needed for siteadmin actions at this time

            /** @var saUser $currentUser */
            $currentUser = modRequest::request('sa.user');
            $username = $currentUser?->getFirstName() . ' ' . $currentUser?->getLastName();
            $logMessage = "PENDING SHIFT - Shift ($shiftNurseType) at facility $providerName on $date at $time was set to PENDING for nurse $nurseName by NurseStat - SiteAdmin User: $username";

            // This was a poor decission. Should abstract logging out of smsService ASAP 
            $this->shiftLogger->log($logMessage, ['action' => 'REQUESTED']);
        }
    }

    public function cancelMessage($shift, $data) {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $nurse = $data['nurse'];
        $nursePhone = $nurse->getPhoneNumber();
        $nurseCreds = $nurse->getCredentials();
        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')' : $nurse->getFirstName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';

        if ($data['by'] == 'provider') {
            $nurseMessage = "SHIFT CANCELLED - Approved shift for $provider_name on $date at $time has been canceled by the facility";
            if(self::shouldSendSmsToNurse($shift->getStart())) {
                self::sendSms($nurseMessage, $nursePhone);
            }
        } elseif ($data['by'] == 'siteadmin') {
            // Not written out yet
        }     
    }

    public function isInNext24Hours($shift) 
    {
        $nowStamp = (new Datetime('now'))->getTimestamp();
        $in24hoursStamp = (new DateTime('now'))->add(new \DateInterval("PT24H"))->getTimestamp();
        $shiftStartStamp = (new DateTime($shift->getStart()))->getTimestamp();
        
        return (($shiftStartStamp > $nowStamp) && ($shiftStartStamp < $in24hoursStamp));
    }

    public function callInMessage($shift, $data)
    {
        $date = $shift->getStart()->format('m/d/Y');
        $time = $shift->getStart()->format('H:i');
        $timeEnd = $shift->getEnd()->format('H:i');
        $nurse = $shift->getNurse();
        $nursePhone = $nurse->getPhoneNumber();
        $nurseCreds = $nurse->getCredentials();
        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')' : $nurse->getFirstName() . ' ' . $nurse->getLastName() . '(' . $nurseCreds . ')';
        $provider_name = $shift->getProvider()->getMember()->getCompany() ?? 'your provider';
        $Message = "NurseStat removed $nurse_name from shift on $date, $time - $timeEnd due to a call-in and the the shift has been reposted.";
        $provider = $shift->getProvider();
        if($this->isInNext24Hours($shift)) {
            //Change this to 48 when updating 
            $contacts = $provider->getContacts();
            foreach ($contacts as $contact) {
                if ($contact->getReceivesSMS()) {
                    self::sendSms($Message, $contact->getPhoneNumber());
                }
            }
        }   
    }

    public function isInNext48Hours($shift) 
    {
        $nowStamp = (new Datetime('now'))->getTimestamp();
        $in24hoursStamp = (new DateTime('now'))->add(new \DateInterval("PT48H"))->getTimestamp();
        $shiftStartStamp = (new DateTime($shift->getStart()))->getTimestamp();
        
        return (($shiftStartStamp > $nowStamp) && ($shiftStartStamp < $in24hoursStamp));
    }

    public static function sendNurseSMS($data)
    {
        $response = ['success' => false];

        $user = modRequest::request('sa.user');
        $smsService = new SmsService();

        /** @var NstMessage $sms */
        $sms = ioc::resolve('NstMessage');
        $sms->setMessage($data['message']);
        $sms->setSaUser($user);
        $sms->setDateCreated(new DateTime());
        $sms->setHasBeenViewed(false);
        $sms->setNumberOfMedia(0);
        
        foreach ($data['recipients'] as $recipient) {
            
            $nurse = ioc::get('Nurse', $recipient);

            if ($nurse->getReceivesSMS()) {
                
                $sms->addNurse($nurse);
                $nurse->addMessage($sms);
                
                $phone = $nurse->getPhoneNumber();

                try {

                    $smsService->sendTwilioSms($data['message'], $phone);
                    $sms->setWasSentSuccessfully(true);
                } catch (\Exception $e) {

                    $sms->setWasSentSuccessfully(false);
                    $response['nurses_message_failed'][] = $nurse->getId();
                }
            }
        }

        app::$entityManager->persist($sms);
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function sendApplicantSMS($data)
    {
        $response = ['success' => false];

        $user = modRequest::request('sa.user');
        $smsService = new SmsService();

        /** @var NstMessage $sms */
        $sms = ioc::resolve('NstMessage');
        $sms->setMessage($data['message']);
        $sms->setSaUser($user);
        $sms->setDateCreated(new DateTime());
        $sms->setHasBeenViewed(false);
        $sms->setNumberOfMedia(0);

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['application_id']]);
        if (!$application) {
            $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => $data['applicant_id']]);
        }

        /** @var NstMember $member */
        $member = $application->getMember();
        
        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        $sms->setApplication($application2);
        $application2->addSmsMessage($sms);

        $phone = $application->getPhoneNumber();

        try {

            $smsService->sendTwilioSms($data['message'], $phone);
            $sms->setWasSentSuccessfully(true);
        } catch (\Exception $e) {

            $sms->setWasSentSuccessfully(false);
            $response['message']['application_id'] = $application->getId();
            $response['message']['error_message'] = $e->getMessage();
        }

        app::$entityManager->persist($sms);
        app::$entityManager->flush();

        $response['success'] = true;
        return $response;
    }

    public static function sendProviderSMS($data)
    {
        $response = ['success' => false];
        $message = $data['message'];
        $smsService = new SmsService();

        foreach ($data['recipients'] as $recipient) {

            $provider = ioc::get('Provider', $recipient);
            $phone = $provider->getFacilityPhoneNumber();
            $smsService->sendTwilioSms($message, $phone);
        }

        $response['success'] = true;
        return $response;
    }

    public static function recieveNewSMS()
    {
        $response = ['success' => false];

        $twilio_sid = app::get()->getConfiguration()->get('twilio_sid')->getValue();
        $twilio_token = app::get()->getConfiguration()->get('twilio_token')->getValue();
        $twilio_phone = app::get()->getConfiguration()->get('twilio_phonenumber')->getValue();
        $twilio = new \Twilio\Rest\Client($twilio_sid, $twilio_token);

        $sms_messages = $twilio->messages->read([], 500);

        foreach ($sms_messages as $sms) {

            // skip messages from site admin as they are saved when they are sent
            if ($sms->to != $twilio_phone) {
                continue;
            }

            $phone = $sms->from;

            $utf8Body = preg_replace('/[^\x{80}-\x{7FF}\x{0}-\x{7F}]/u', '', $sms->body);

            /** @var Nurse $nurse */
            $nurse = ioc::getRepository('Nurse')->findNurseByPhoneNumber($phone)[0];

            if ($nurse) {

                /** @var NstMessage $nurseMessages[] */
                $nurseMessages = $nurse->getMessages();
                foreach ($nurseMessages as $nurseMessage) {
                    if ($nurseMessage->getDateCreated() == $sms->dateSent && $nurseMessage->getMessage() == $utf8Body) {
                        continue 2;
                    }
                }

                /** @var NstMessage $smsObject */
                $smsObject = ioc::resolve('NstMessage');
                $nurse->addMessage($smsObject);

                $smsObject->addNurse($nurse);
                $smsObject->setMessage($utf8Body);
                $smsObject->setDateCreated($sms->dateSent);
                $smsObject->setWasSentSuccessfully(true);
                $smsObject->setHasBeenViewed(false);
                $smsObject->setNumberOfMedia($sms->numMedia); // currently not capturing the actual media, just the number of media sent
                $smsObject->setSid($sms->sid);

            } else {

                // add message to nurse application 2
                $application1 = ioc::getRepository('ApplicationPart1')->findOneBy(['phone_number' => $phone]);
                $application2 = $application1->getMember()->getApplicationPart2();

                /** @var NstMessage $applicationMessages[] */
                $applicationMessages = $application2->getSmsMessages();
                foreach ($applicationMessages as $applicationMessage) {
                    if ($applicationMessage->getDateCreated() == $sms->dateSent && $applicationMessage->getMessage() == $utf8Body) {
                        continue 2;
                    }
                }

                /** @var NstMessage */
                $smsObject = ioc::resolve('NstMessage');
                $application2->addSmsMessage($smsObject);

                $smsObject->setApplication($application2);
                $smsObject->setMessage($utf8Body);
                $smsObject->setDateCreated($sms->dateSent);
                $smsObject->setWasSentSuccessfully(true);
                $smsObject->setHasBeenViewed(false);
                $smsObject->setNumberOfMedia($sms->numMedia); // currently not capturing the actual media, just the number of media sent
                $smsObject->setSid($sms->sid);
            }

            app::$entityManager->persist($smsObject);
            app::$entityManager->flush();
        }

        $response['success'] = true;
        return $response;
    }

    public static function getNurseSMSMessages($data)
    {
        $response = ['success' => false];

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', $data['id']);

        /** @var NstMessage $sms */
        $nurseMessages = $nurse->getMessages();

        foreach ($nurseMessages as $nurseMessage) {

            try {

                $saUser = $nurseMessage?->getSaUser();
                if ($saUser) {
                    $saUserFullName = $saUser?->getFirstName() . ' ' . $saUser?->getLastName();
                } else { $saUserFullName = null; }
            } catch (\Exception $e) {

                $saUserFullName = 'Cannot retrieve SA User';
                $response['error_retrieving_sa_user_name'] = $e->getMessage();
            }

            $response['messages'][] = [

                'id' => $nurseMessage->getId(),
                'nurse' => $nurse->getFirstName() . ' ' . $nurse->getLastName(),
                'user' => $saUserFullName,
                'message' => $nurseMessage->getMessage(),
                'date_created' => $nurseMessage->getDateCreated()->format('m/d/Y H:i:s'),
                'sent_successfully' => $nurseMessage->getWasSentSuccessfully(),
                'viewed' => $nurseMessage->getHasBeenViewed(),
                'number_of_media' => $nurseMessage->getNumberOfMedia(),
                'sid' => $nurseMessage?->getSid()
            ];

            $nurseMessage->setHasBeenViewed(true);
        }

        app::$entityManager->flush();

        usort($response['messages'], function ($a, $b) {
            return $a['date_created'] <=> $b['date_created'];
        });

        $response['success'] = true;
        return $response;
    }

    public static function getApplicantSMSMessages($data)
    {
        $response = ['success' => false];

        /** @var ApplicationPart1 $application */
        $application = ioc::getRepository('ApplicationPart1')->findOneBy(['id' => (int) $data['application_id']]);

        /** @var NstMember $member */
        $member = $application->getMember();

        /** @var ApplicationPart2 $application2 */
        $application2 = $member->getApplicationPart2();

        /** @var NstMessage $sms */
        $applicationMessages = $application2->getSmsMessages();

        foreach ($applicationMessages as $applicationMessage) {

            try {

                $saUser = $applicationMessage?->getSaUser();
                if ($saUser) {
                    $saUserFullName = $saUser?->getFirstName() . ' ' . $saUser?->getLastName();
                } else { $saUserFullName = null; }
            } catch (\Exception $e) {

                $saUserFullName = 'Cannot retrieve SA User';
                $response['error_retrieving_sa_user_name'] = $e->getMessage();
            }

            $response['messages'][] = [

                'id' => $applicationMessage->getId(),
                'nurse' => $member->getFirstName() . ' ' . $member->getLastName(),
                'user' => $saUserFullName,
                'message' => $applicationMessage->getMessage(),
                'date_created' => $applicationMessage->getDateCreated()->format('m/d/Y H:i:s'),
                'sent_successfully' => $applicationMessage->getWasSentSuccessfully(),
                'viewed' => $applicationMessage->getHasBeenViewed(),
                'number_of_media' => $applicationMessage->getNumberOfMedia(),
                'sid' => $applicationMessage?->getSid()
            ];

            $applicationMessage->setHasBeenViewed(true);
        }

        app::$entityManager->flush();

        if (is_array($response['messages'])) {

            usort($response['messages'], function ($a, $b) {
                return $a['date_created'] <=> $b['date_created'];
            });
        }

        $response['success'] = true;
        return $response;
    }

    public static function getNursesWithUnreadSMS()
    {
        $response = ['success' => false];

        /** @var Nurse[] $nurses */
        $nurses = ioc::getRepository('Nurse')->findNursesWithUnreadMessages();
        
        foreach ($nurses as $nurseInfo) {

            if ($nurseInfo['is_deleted']) {
                continue;
            }
            
            $nurse = ioc::get('Nurse', $nurseInfo['id']);
            $nurseMessages = $nurse->getMessages();
            $unreadMessages = [];
            foreach ($nurseMessages as $message) {

                if ($message->getHasBeenViewed() == false) {
                    $unreadMessages[] = [

                        'message_body' => $message->getMessage(),
                        'date_created' => $message->getDateCreated()->format('m/d/Y H:i:s')
                    ];
                }
            }

            usort($unreadMessages, function($a, $b) {
                return strtotime($b['date_created']) - strtotime($a['date_created']);
            });            
            $mostRecentMessage = $unreadMessages[0];

            $response['nurses'][] = [

                'id' => $nurseInfo['id'],
                'member_id' => $nurseInfo['member_id'],
                'first_name' => $nurseInfo['first_name'],
                'last_name' => $nurseInfo['last_name'],
                'phone_number' => $nurseInfo['phone_number'],
                'unread_messages_count' => count($unreadMessages),
                'most_recent_message' => $mostRecentMessage,
                'profile_link' => app::get()->getRouter()->generate('member_sa_account_edit', ['id' => $nurseInfo['member_id']])
            ];
        }

        $response['success'] = true;
        return $response;
    }
}
