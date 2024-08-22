<?php

namespace sa\messages;

use sacore\application\ioc;
use sacore\application\modDataRequest;
use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\route;
use sacore\application\saRoute;
use sacore\application\resourceRoute;
use sacore\application\navItem;
use sacore\application\staticResourceRoute;

abstract class messagesConfig extends  moduleConfig {

	static function initRoutes($routes)
    {
        $routes->addWithOptionsAndName('Manage Emails', 'email_cron','/siteadmin/messages/cron')->controller('messagesController@messagesCron');
        $routes->addWithOptionsAndName('Voice Message Text', 'voice_message_text','/siteadmin/messages/voicetext')->controller('messagesController@voiceText');

        $routes->addWithOptionsAndName('Manage Emails', 'sa_emails', '/siteadmin/emails')->defaults(['route_permissions' => ['system_view_email']])->controller('saMessagesController@manageEmails')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); // 'permissions' => 'system_view_email'
        //Todo: I think I may have imported these entirely wrong, I will have to comb over everthing in saMessages and saEmails
        $routes->addWithOptionsAndName('Create Emails', 'sa_emails_create', '/siteadmin/emails/create')->controller('saMessagesController@createEmails');
        $routes->addWithOptionsAndName('Edit Emails', 'sa_emails_edit','/siteadmin/emails/{id}/edit')->defaults(['route_permissions' => ['system_edit_email']])->controller('saMessagesController@editEmails')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); //'permissions' => 'system_edit_email'
        $routes->addWithOptionsAndName('Save Emails', 'sa_emails_save','/siteadmin/emails/{id}/save')->defaults(['route_permissions' => ['system_save_email']])->controller('saMessagesController@saveEmails')->methods(['POST'])->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); //'permissions' => 'system_save_email'
        $routes->addWithOptionsAndName('Delete Emails', 'sa_emails_delete', '/siteadmin/emails/{id}/delete')->defaults(['route_permissions' => ['system_delete_email']])->controller('saMessagesController@deleteEmails')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); //'permissions' => 'system_delete_email'
        $routes->addWithOptionsAndName('Resend E-mail', 'sa_emails_resend', '/siteadmin/messages/email/resend/{id}')->controller('saMessagesController@resendEmail');

        $routes->addWithOptionsAndName('Manage SMS', 'sa_sms', '/siteadmin/sms')->defaults(['route_permissions' => ['system_view_sms']])->controller('saMessagesController@manageSMS')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); // 'permissions' => 'system_view_sms'
        $routes->addWithOptionsAndName('Create SMS', 'sa_sms_create', '/siteadmin/sms/create')->controller('saMessagesController@createSms');
        $routes->addWithOptionsAndName('Edit SMS', 'sa_sms_edit', '/siteadmin/sms/{id}/edit')->defaults(['route_permissions' => ['system_edit_sms']])->controller('saMessagesController@editSms')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); // 'permissions' => 'system_edit_sms'
        $routes->addWithOptionsAndName('Save SMS', 'sa_sms_save', '/siteadmin/sms/{id}/save')->defaults(['route_permissions' => ['system_save_sms']])->controller('saMessagesController@saveSms')->methods(['POST'])->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); // 'permissions' => 'system_save_sms'
        $routes->addWithOptionsAndName('Delete SMS', 'sa_sms_delete','/siteadmin/sms/{id}/delete')->defaults(['route_permissions' => ['system_delete_sms']])->controller('saMessagesController@deleteSms')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); // 'permissions' => 'system_delete_sms'

        $routes->addWithOptionsAndName('Manage Voice Messages', 'sa_voice', '/siteadmin/voice')->defaults(['route_permissions' => ['system_view_voice']])->controller('saMessagesController@manageVoice')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); //'permissions' => 'system_view_voice'
        $routes->addWithOptionsAndName('Create Voice Messages', 'sa_voice_create', '/siteadmin/voice/create')->controller('saMessagesController@editVoice');
        $routes->addWithOptionsAndName('Edit Voice Messages', 'sa_voice_edit', '/siteadmin/voice/{id}/edit')->defaults(['route_permissions' => ['system_edit_voice']])->controller('saMessagesController@editVoice')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); //'permissions' => 'system_edit_voice'
        $routes->addWithOptionsAndName('Save Voice Message', 'sa_voice_save', '/siteadmin/voice/{id}/save')->defaults(['route_permissions' => ['system_save_voice']])->controller('saMessagesController@saveVoice')->methods(["POST"])->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); //'permissions' => 'system_save_voice'
        $routes->addWithOptionsAndName('Delete Voice Message', 'sa_voice_delete', '/siteadmin/voice/{id}/delete')->defaults(['route_permissions' => ['system_delete_voice']])->controller('saMessagesController@deleteVoice')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); //'permissions' => 'system_delete_voice'

        $routes->addWithOptionsAndName('Send Push Notification', 'push_notification_send','/siteadmin/push-notifications/send')->defaults(['route_permissions' => ['send_push_notifications']])->controller('saPushNotificationController@sendNotificationView')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); // 'permission' => 'send_push_notifications'
        $routes->addWithOptionsAndName('Push Notification View', 'sa_notification_view','/siteadmin/push-notifications/{id}')->controller('saPushNotificationController@show');
        $routes->addWithOptionsAndName('Push Notification Index', 'push_notification_index', '/siteadmin/push-notifications')->defaults(['route_permissions' => ['send_push_notifications']])->controller('saPushNotificationController@index')->middleware('SaAuthMiddleware')->middleware('SaPermissionMiddleware'); // 'permission' => 'send_push_notifications'
        $routes->addWithOptionsAndName('Central - Acknowledge All', 'messages_central_acknowledge_all', '/siteadmin/emails/ack-all')->controller('saMessagesController@acknowledgeAll');

        $routes->addWithOptionsAndName('images', 'messages_sa_images', '/siteadmin/messages/images/{file}')->controller('saPushNotificationController@img');
        $routes->addWithOptionsAndName('css', 'messages_sa_css', '/siteadmin/messages/css/{file}')->controller('saPushNotificationController@css');
        $routes->addWithOptionsAndName('js', 'messages_sa_js', '/siteadmin/messages/js/{file}')->controller('saPushNotificationController@js');
    }

    static function init() {
        modDataRequest::listen('messages.sendEmail', 'saEmail@createSend');
        modDataRequest::listen('messages.sendSMS', 'saSMS@createSend');
        modDataRequest::listen('messages.sendVoice', 'saVoice@createSend');

		modDataRequest::listen('messages.startEmailBatch', 'saEmail@startBatch');
		modDataRequest::listen('messages.commitEmailBatch', 'saEmail@commitBatch');
		modDataRequest::listen('messages.startSMSBatch', 'saSMS@startBatch');
		modDataRequest::listen('messages.commitSMSBatch', 'saSMS@commitBatch');
		
		modRequest::listen('messages.startPushNotificationBatch', 'PushNotificationService@startBatch');
        modRequest::listen('messages.sendPushNotification', 'PushNotificationService@createSend');	    
        modRequest::listen('messages.commitPushNotificationBatch', 'PushNotificationService@commitBatch');
        
        modRequest::listen('siteadmin.notification.queue', 'saPushNotificationController@queueNotification', 1, null, true, true);

        modRequest::request('api.registerEntityAPI', null, array('controller'=>'PushTokenApiController', 'entity'=>'messages\PushToken') );
    }

    static function getCLICommands() {
        return array(
            ioc::staticGet('SendMessagesCommand')
        );
    }

	public static function getPermissions() {
		$permissions = array();
		$permissions['system_view_email'] = 'View Email';
		$permissions['system_edit_email'] = 'Edit Emails';
		$permissions['system_save_email'] = 'Save Email';
		$permissions['system_delete_email'] =' Delete Email';

		$permissions['system_view_sms'] = 'View SMS';
		$permissions['system_edit_sms'] = 'Edit SMS';
		$permissions['system_save_sms'] = 'Save SMS';
		$permissions['system_delete_sms'] =' Delete SMS';

		$permissions['system_view_voice'] = 'View Voice';
		$permissions['system_edit_voice'] = 'Edit Voice';
		$permissions['system_save_voice'] = 'Save Voice';
		$permissions['system_delete_voice'] =' Delete Voice';
		
		$permissions['send_push_notifications'] = 'Send Push Notifications';

		return $permissions;
	}

	static function getNavigation() {
		return array(
            // FRONT END

            // SITEADMIN
            new navItem(array( 'id'=>'sa_messages', 'name'=>'Messages', 'icon'=>'fa fa-envelope-square', 'parent'=>'sa_settings'  )),
            new navItem(array( 'name'=>'Manage Emails',  'routeid'=>'sa_emails', 'subpattern'=>'^/siteadmin/emails/', 'icon'=>'fa fa-angle-double-right', 'parent'=>'sa_messages'  )),
            new navItem(array( 'name'=>'Manage SMS',  'routeid'=>'sa_sms', 'subpattern'=>'^/siteadmin/sms/', 'icon'=>'fa fa-angle-double-right', 'parent'=>'sa_messages'  )),
            new navItem(array( 'name'=>'Manage Voice Messages',  'routeid'=>'sa_voice', 'subpattern'=>'^/siteadmin/voice/', 'icon'=>'fa fa-angle-double-right', 'parent'=>'sa_messages'  )),
		
            new navItem(array( 'name'=>'Manage Push Notifications', 'routeid'=>'push_notification_index', 'icon'=>'fa fa-angle-double-right', 'parent'=>'sa_messages' )),
            new navItem(array( 'name'=>'Send Push Notification', 'routeid'=>'push_notification_send', 'icon'=>'fa fa-angle-double-right', 'parent'=>'sa_messages' ))
        );
	}

	static function getDatabase() {
        return array(
            'wormConfig'=>array(
                'alternativeNamespaces'=>array(
                    'sa\messages'),	),
            'tables'=>array()
        );
	}

    public static function getSettings() {
        $module_settings = array(
            'smtp_auth_type' => array('type' => 'select', 'options'=>array('PLAIN','LOGIN', 'NTLM', 'CRAM-MD5', 'XOAUTH2'), 'default' => 'PLAIN'),
            'fcm_server_key' => array('type' => 'text', 'default' => ''),
            'smtp_from' => array(),
            'smtp_fromName' => array(),
            'smtp_host' => array(),
            'smtp_smtp_auth' => array('type' => 'boolean', 'default' => false),
            'smtp_username' => array(),
            'smtp_password' => array('type' => 'password'),
            'smtp_port' => array('default' => 25),
            'smtp_bind_ip' => array('type' => 'text', 'default' => ''),
            'smtp_secure' => array('type' => 'select', 'options'=>array('', 'ssl', 'tls'), 'default' => ''),
            'smtp_verify_ssl' => array('type' => 'boolean', 'default' => true),
            'enable_mail_catcher' => array('type' => 'boolean', 'default' => false),
            'mail_catcher_address' => array('type' => 'text', 'default' => ''),
        );

        return $module_settings;
    }
}