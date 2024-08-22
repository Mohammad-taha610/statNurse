<?php

namespace nst\messages;

use sacore\application\responses\View;
use sa\messages\PushNotificationService;

/**
 * @IOC_NAME="saPushNotificationController"
 */
class SaNstPushNotificationController extends \sa\messages\saPushNotificationController
{

    public function sendNotificationViewTEST(): View
    {
        return new View('push_notification_send', $this->viewLocation());
    }

    public static function sendNotification($data): array
    {
        $service = new NstPushNotificationService();
        return $service->sendNotification($data);
    }

    public static function sendNotificationToNurse($data): array
    {
        $service = new NstPushNotificationService();
        return $service->sendNotificationToNurse($data);
    }

    public static function loadNursesWithTokens($data): array
    {
        $service = new NstPushNotificationService();
        return $service->loadNursesWithTokens($data);
    }

    public static function searchNursesWithTokens($data): array
    {
        $service = new NstPushNotificationService();
        return $service->searchNursesWithTokens($data);
    }

    public static function sendShiftNotification($data): array
    {
        $service = new NstPushNotificationService();
        return $service->sendShiftNotification($data);
    }

    public static function sendPushNotification($data): array
    {
        $service = new NstPushNotificationService();

        $data['payload_data'] = [
            'route' => 'viewShift',
            'id' => 84,
            'is_recurrence' => true,
            'date_string' => '12/08/2021'
        ];

        return $service->sendNotificationToNurse($data);
    }
}