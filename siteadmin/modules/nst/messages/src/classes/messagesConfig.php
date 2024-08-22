<?php

namespace nst\messages;

use sacore\application\modRequest;
use sacore\application\staticResourceRoute;
use sacore\application\navItem;

class messagesConfig extends \sa\messages\messagesConfig
{
    public static function initRoutes($routes)
    {
        // Resources
        $routes->addWithOptionsAndName('images', 'nst_message_images', '/messages/images/{file}')->controller('NstMessagesController@images');
        $routes->addWithOptionsAndName('css', 'nst_message_css', '/messages/css/{file}')->controller('NstMessagesController@css');
        $routes->addWithOptionsAndName('js', 'nst_message_js', '/messages/js/{file}')->controller('NstMessagesController@js');

        $routes->addWithOptionsAndName('Send Push Notification', 'sa_push_notification_send','/siteadmin/push_notifications/sendtest')->controller('SaNstPushNotificationController@sendNotificationViewTEST'); // 'permission' => 'send_push_notifications'
        $routes->addWithOptionsAndName('Send SMS', 'sa_sms_send', '/siteadmin/sms/sms_send')->controller('SaNstSMSMessagesController@sendSMSView');
        $routes->addWithOptionsAndName('Unread SMS Messages', 'sa_sms_unread', '/siteadmin/nurse/unread_sms')->controller('SaNstSMSMessagesController@unreadSMSView');
    }

    public static function init()
    {
        modRequest::listen('nst.messages.sendNotification', 'SaNstPushNotificationController@sendNotification', 1, null, true, false);
        modRequest::listen('nst.messages.sendNotificationToNurse', 'SaNstPushNotificationController@sendNotificationToNurse', 1, null, true, false);
        modRequest::listen('nst.messages.loadNursesWithTokens', 'SaNstPushNotificationController@loadNursesWithTokens', 1, null, true, false);
        modRequest::listen('nst.messages.searchNursesWithTokens', 'SaNstPushNotificationController@searchNursesWithTokens', 1, null, true, false);
        modRequest::listen('nst.messages.sendShiftNotification', 'SaNstPushNotificationController@sendShiftNotification', 1, null, false, false);
        modRequest::listen('nst.messages.sendPushNotification', 'SaNstPushNotificationController@sendPushNotification', 1, null, true, false);
        modRequest::listen('nst.messages.sendProviderSMS', 'SaNstSMSMessagesController@sendProviderSMS', 1, null, true, false);
        modRequest::listen('nst.messages.sendNurseSMS', 'SaNstSMSMessagesController@sendNurseSMS', 1, null, true, false);
        modRequest::listen('nst.messages.sendApplicantSMS', 'SaNstSMSMessagesController@sendApplicantSMS', 1, null, true, false);
        modRequest::listen('nst.messages.getNurseSMSMessages', 'SaNstSMSMessagesController@getNurseSMSMessages', 1, null, true, false);
        modRequest::listen('nst.messages.getApplicantSMSMessages', 'SaNstSMSMessagesController@getApplicantSMSMessages', 1, null, true, false);
        modRequest::listen('nst.messages.recieveNewSMS', 'SaNstSMSMessagesController@recieveNewSMS', 1, null, true, false);
        modRequest::listen('nst.messages.getNursesWithUnreadSMS', 'SaNstSMSMessagesController@getNursesWithUnreadSMS', 1, null, true, false);
    }

    public static function getNavigation()
    {
        return array(
            new navItem(array( 'id'=>'mass_sms', 'name'=>'Send Mass SMS', 'routeid'=>'sa_sms_send', 'icon'=>'fa fa-angle-double-right', 'parent'=>'sa_messages' )),
            new navItem(array( 'id'=>'unread_nurse_sms', 'name'=>'Unread SMS Messages', 'routeid'=>'sa_sms_unread', 'icon'=>'fa fa-angle-double-right', 'parent'=>'nurses' )),
        );

    }

    public static function getSettings()
    {
        $module_settings = array(
            'nurse_send_sms_up_to_days' => array('type' => 'text', 'module' => 'Messages', 'default' => 5)
        );

        return $module_settings;
    }
}