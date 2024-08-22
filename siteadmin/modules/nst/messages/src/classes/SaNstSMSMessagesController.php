<?php

namespace nst\messages;

use nst\messages\SmsService;
use sacore\application\responses\View;

class SaNstSMSMessagesController extends \sacore\application\saController
{

    public function sendSMSView(): View
    {

        return new View('sms_send', $this->viewLocation());
    }

    public static function sendProviderSMS($data)
    {
        $service = new SMSService();
        return $service->sendProviderSMS($data);
    }

    public static function sendNurseSMS($data)
    {
        $service = new SMSService();
        return $service->sendNurseSMS($data);
    }

    public static function sendApplicantSMS($data)
    {
        $service = new SMSService();
        return $service->sendApplicantSMS($data);
    }

    public static function getNurseSMSMessages($data)
    {
        $service = new SMSService();
        return $service->getNurseSMSMessages($data);
    }

    public static function getApplicantSMSMessages($data)
    {
        $service = new SMSService();
        return $service->getApplicantSMSMessages($data);
    }

    public static function recieveNewSMS()
    {
        $service = new SMSService();
        return $service->recieveNewSMS();
    }

    public function unreadSMSView(): View
    {

        return new View('nurse_sms_unread', $this->viewLocation());
    }

    public static function getNursesWithUnreadSMS()
    {
        $service = new SMSService();
        return $service->getNursesWithUnreadSMS();
    }
}