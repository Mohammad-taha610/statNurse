<?php

namespace nst\messages;

use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message;
use paragraph1\phpFCM\Notification;
use paragraph1\phpFCM\Recipient\Device;
use paragraph1\phpFCM\Recipient\Topic;
use nst\events\Shift;
use nst\member\Nurse;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Thread;
use sa\messages\PushNotification;
use sa\messages\PushNotificationService;
use sacore\utilities\doctrineUtils;

/**
 * @IOC_NAME="PushNotificationService"
 */
class NstPushNotificationService extends PushNotificationService
{
    /** @var null|string $serverKey  */
    private static $serverKey = null;
    /** @var null|Client $client  */
    private static $client = null;
    /** @var \GuzzleHttp\Client $guzzle_client */
    private static $guzzle_client;
    /** @var string $fcm_url */
    private static $fcm_url = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Summary of markAllNotificationsAsRead
     * @param int $nurse_id
     * @return mixed
     */
    public static function markAllNotificationsAsRead($nurse_id)
    {
        $response = ['success' => false];

        /** @var NstPushNotificationRepository $notificationRepo */
        $notificationRepo = ioc::getRepository('NstPushNotification');
        $notificationRepo->markAllNotificationsAsRead($nurse_id);

        $response['success'] = true;
        return $response;
    }

    public static function markNotificationAsRead($data)
    {
        $response = ['success' => false];

        /** @var NstPushNotification $notification */
        $notification = ioc::get('NstPushNotification', ['id' => $data['id']]);
        $notification->setIsRead(true);
        app::$entityManager->flush();
        
        $response['success'] = true;
        return $response;
    }
    
    public static function getNotificationsForNurse($data) {
        $response = ['success' => false];

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $data['nurse_id']]);

        if(!$nurse) {
            $response['message'] = 'Cannot find nurse with id: ' . $data['nurse_id'];
            return $response;
        }

        $notifications = ioc::getRepository('NstPushNotification')->findBy(['nurse' => $nurse, 'is_read' => false]);
        $response['notifications'] = doctrineUtils::getEntityCollectionArray($notifications);

        $response['success'] = true;
        return $response;
    }

    public static function sendNotification($data) {
        $response = ['success' => false];

        modRequest::request('messages.startPushNotificationBatch');

        static::createSend($data);

        modRequest::request('messages.commitPushNotificationBatch');

        $response['success'] = true;
        return $response;
    }

    public static function sendNotificationToNurse($data)
    {
        $response = ['success' => false];

        modRequest::request('messages.startPushNotificationBatch');

        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $data['nurse_id']]);
        $data['nurse'] = $nurse;

        /**
         * @var \nst\member\NstMember $member
         */
        $member = $nurse->getMember();

        // get first entry in sa_member_users for our nurse
        $user = $member->getUsers()[0];

        // get the push token from our sa_push_tokens table that matches the nurse based on user_id
        $pushToken = ioc::getRepository('PushToken')->findOneBy([
            'user_id' => $user->getId(),
        ]);

        // put token in notification
        $data['token'] = $pushToken?->getToken();
        static::createSend($data);

        modRequest::request('messages.commitPushNotificationBatch');

        $response['success'] = true;
        return $response;
    }

    public static function sendNotificationToProvider($data) {
        $response = ['success' => false];

        modRequest::request('messages.startPushNotificationBatch');

        static::createSend($data);

        modRequest::request('messages.commitPushNotificationBatch');

        $response['success'] = true;
        return $response;
    }

    /**
     * Create push notification instance for
     * sending.
     *
     * @param $data
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\IocException
     * @throws Exception
     */
    public static function createSend($data) {
        /** @var NstPushNotification $pushNotification */
        $pushNotification = ioc::get('NstPushNotification');
        if($data['nurse']) {
            $pushNotification->setNurse($data['nurse']);
        }
        if($data['provider_id']) {
            $provider = ioc::get('Provider', ['id' => $data['provider_id']]);
            $pushNotification->setProvider($provider);
        }
        $pushNotification->setIsRead(false);
        $pushNotification->setToken($data['token']);
        $pushNotification->setTopic($data['topic']);
        $pushNotification->setTitle($data['title']);
        $pushNotification->setMessage($data['message']);
        $pushNotification->setPayloadData($data['payload_data']);
        $pushNotification->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
        $pushNotification->setBatchId(static::$_batch_id);
        $pushNotification->setAttemptedSend(false);

        app::$entityManager->persist($pushNotification);
        app::$entityManager->flush();

        if(!static::$_batch) {
            static::initMessagesThread();
        }
    }

    /**
     * Initiates the messages cron thread that will check for
     * unsent notifications, e-mails, & SMS messages.
     *
     * @throws Exception
     */
    protected static function initMessagesThread()
    {
        $thread = new Thread(
            'executeController',
            'messagesController',
            'messagesCron',
            array(
                'batch_id' => static::$_batch_id
            ));

        $thread->run();
    }

    public static function loadNursesWithTokens($data) {
        $response = ['success' => false];

        $nurses = ioc::getRepository('Nurse')->findAll();
        /** @var Nurse $nurse */
        foreach($nurses as $nurse) {
            $response['nurses'][] = [
                'id' => $nurse->getId(),
                'name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
                'token' => $nurse->getFirebaseToken()
            ];
        }

        $response['success'] = true;
        return $response;
    }

    public static function searchNursesWithTokens($data)
    {
        $response = ['success' => false];

        $nurses = ioc::getRepository('Nurse')->searchNurseByName($data['term']);

        /** @var Nurse $nurse */
        foreach ($nurses as $nurse) {
            $response['nurses'][] = [
                'id' => $nurse->getId(),
                'name' => $nurse->getMember()->getFirstName() . ' ' . $nurse->getMember()->getLastName(),
                'token' => $nurse->getFirebaseToken()
            ];
        }

        $response['success'] = true;
        return $response;
    }

    /** Replace {{...}} in notification templates with actual data */
    public function performNotificationTemplateDataReplacement($data) {
        $text = $data['text'];

        foreach($data as $k => $v) {
            if($k == 'text') {
                continue;
            }
            $text = str_replace('{{' . $k . '}}', $v, $text);
        }

        return $text;
    }

    public static function sendShiftNotification($data) {
        $response = ['success' => false];
        $isRecurrence = $data['is_recurrence'];
        $type = $data['type'];
        $nurse = $data['nurse'];
        $timeUntilStart = $data['time_until_start'];
        $shift = $data['shift'];
        $notificationService = new NstPushNotificationService();
        switch($type) {
            case 'Denied':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Nurse) Denied Shift Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Shift Denied');
                    $template->setMessage('Shift has been denied for {{provider_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Nurse) Denied Shift Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'nurse_id' => $nurse->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToNurse($notificationData);
                break;
            case 'Removed':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Nurse) Removed From Shift Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Removed From Shift');
                    $template->setMessage('You have been removed from a shift for {{provider_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Nurse) Removed From Shift Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'nurse_id' => $nurse->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToNurse($notificationData);
                break;
            case 'Approved':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Nurse) Approved Shift Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Shift Approved');
                    $template->setMessage('Shift has been approved for {{provider_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Nurse) Approved Shift Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'nurse_id' => $nurse->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToNurse($notificationData);
                break;
            case 'Assigned':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Nurse) Assigned Shift Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Assigned Shift');
                    $template->setMessage('You have been assigned a shift for {{provider_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Nurse) Assigned Shift Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'nurse_id' => $nurse->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToNurse($notificationData);
                break;
            case 'Admin Assigned':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Nurse) Admin Assigned Shift Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Admin Assigned Shift');
                    $template->setMessage('An admin has assigned you a shift for {{provider_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Nurse) Admin Assigned Shift Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'nurse_id' => $nurse->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToNurse($notificationData);
                break;
            case 'Canceled':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Nurse) Canceled Shift Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Shift Canceled');
                    $template->setMessage('Your shift for {{provider_name}} on {{shift_date}} at {{shift_time}} has been canceled.');
                    $template->setName('(Nurse) Canceled Shift Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'nurse_id' => $nurse->getId()
                ];
                $notificationService->sendNotificationToNurse($notificationData);
                break;
            case 'Starting':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Nurse) Shift Starting Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Shift Starting in {{time_until_start}}');
                    $template->setMessage('Your shift for {{provider_name}} on {{shift_date}} starts at {{shift_time}}.');
                    $template->setName('(Nurse) Shift Starting Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle(),
                    'time_until_start' => $timeUntilStart
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'provider_name' => $shift->getProvider()->getMember()->getCompany(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'nurse_id' => $nurse->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToNurse($notificationData);
                break;
            default:
                break;
        }

        $response['success'] = true;
        return $response;
    }

    public static function sendProviderNotification($data) {
        $response = ['success' => false];
        $isRecurrence = $data['is_recurrence'];
        $type = $data['type'];
        $provider = $data['provider'];
        $shift = $data['shift'];
        $notificationService = new NstPushNotificationService();
        switch($type) {
            case 'Requested':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Provider) Shift Requested Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Shift Requested');
                    $template->setMessage('A shift has been requested by {{nurse_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Provider) Shift Requested Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'nurse_name' => $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'provider_id' => $provider->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToProvider($notificationData);
                break;
            case 'Removed':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Provider) Removed From Shift Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Removed From Shift');
                    $template->setMessage('{{nurse_name}} has been removed from a shift on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Provider) Removed From Shift Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'nurse_name' => $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'provider_id' => $provider->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToProvider($notificationData);
                break;
            case 'Accepted':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Provider) Shift Accepted Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Shift Accepted');
                    $template->setMessage('A shift has been accepted by {{nurse_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Provider) Shift Accepted Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'nurse_name' => $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'provider_id' => $provider->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToProvider($notificationData);
                break;
            case 'Declined':
                /** @var NstPushNotificationTemplate $template */
                $template = ioc::getRepository('NstPushNotificationTemplate')->findOneBy(['name' => '(Provider) Shift Declined Template']);
                if(!$template) {
                    /** @var NstPushNotificationTemplate $template */
                    $template = ioc::resolve('NstPushNotificationTemplate');
                    $template->setTitle('Shift Declined');
                    $template->setMessage('A shift has been declined by {{nurse_name}} on {{shift_date}} at {{shift_time}}.');
                    $template->setName('(Provider) Shift Declined Template');
                    app::$entityManager->persist($template);
                    app::$entityManager->flush();
                }

                $titleData = [
                    'text' => $template->getTitle()
                ];
                $messageData = [
                    'text' => $template->getMessage(),
                    'nurse_name' => $shift->getNurse()->getMember()->getFirstName() . ' ' . $shift->getNurse()->getMember()->getLastName(),
                    'shift_date' => $shift->getStart()->format('m/d/y'),
                    'shift_time' => $shift->getStart()->format('g:ia')
                ];

                $notificationData = [
                    'title' => $notificationService->performNotificationTemplateDataReplacement($titleData),
                    'message' => $notificationService->performNotificationTemplateDataReplacement($messageData),
                    'provider_id' => $provider->getId(),
                    'payload_data' => [
                        'route' => 'viewShift',
                        'id' => $shift->getId(),
                        'is_recurrence' => $isRecurrence,
                        'date_string' => $shift->getStart()->format('m/d/Y')
                    ]
                ];
                $notificationService->sendNotificationToProvider($notificationData);
                break;

        }

        $response['success'] = true;
        return $response;
    }

    /**
     * Reach out to Firebase API and
     * send push notification.
     *
     * @param PushNotification $notification
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function sendNow($notification)
    {
        try {

            static::initClient();

            $outgoingNotification = new Notification(
                $notification->getTitle(),
                $notification->getMessage()
            );

            $message = new Message();

            if (!empty($notification->getToken())) {
                $message->addRecipient(new Device($notification->getToken()));
            }

            if (!empty($notification->getTopic())) {
                $message->addRecipient(new Topic($notification->getTopic()));
            }

            $message->setNotification($outgoingNotification);
            $message->setData(is_array($notification->getPayloadData()) ? $notification->getPayloadData() : []);

            //$response = static::$client->send($message);

            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/nstmessages.txt', 'sending message in extended module' . PHP_EOL, FILE_APPEND);
            $response = static::sendFirebasePushNotification($message);

            $serverResponse = $response->getBody()->getContents();
            $responseJson = json_decode($serverResponse, true);

            if ($responseJson['success'] == '0') {
                if ($responseJson['results'][0]['error'] == 'NotRegistered') {
                    /** @var PushToken $token */
                    $token = ioc::getRepository('PushToken')->findOneBy(array('token' => $notification->getToken()));

                    if ($token) {
                        app::$entityManager->remove($token);
                        app::$entityManager->flush($token);
                    }
                }
            }

            $notification->setResponse($serverResponse);
            $notification->setAttemptedSend(true);
            $notification->setDateAttemptedSend(new DateTime());
            $notification->setSuccess($responseJson['success'] ? true : false);

            if (!$responseJson['success'] && $responseJson['results'][0]['error'] == 'InvalidRegistration') {
                $matchingToken = ioc::getRepository('PushToken')->findOneBy(array('token' => $notification->getToken()));

                if ($matchingToken) {
                    app::$entityManager->remove($matchingToken);
                }
            }

            app::$entityManager->flush();
        } catch (\Throwable $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/nstmessages.txt', (new DateTime())->format('Y-m-d h:i-s') . ' ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    private static function sendFirebasePushNotification(Message $message)
    {
        return static::$guzzle_client->request(
            "POST",
            static::$fcm_url,
            [
                'headers' => [
                    'Authorization' => sprintf('key=%s', static::$serverKey),
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($message)
            ]
        );
    }

    /**
     * @throws Exception
     */
    private static function initClient()
    {
        if (!static::$guzzle_client) {
            static::$guzzle_client = new \GuzzleHttp\Client();
        }
        if (!static::$serverKey) {
            static::$serverKey = app::get()->getConfiguration()->get('fcm_server_key')->getValue();
        }

        if (!static::$serverKey) {
            throw new Exception('A valid server key is required to send push notifications. Please update the configuration with your key.');
        }

        if (!static::$client) {
            static::$client = new Client();
            static::$client->setApiKey(static::$serverKey);
            static::$client->injectHttpClient(new \GuzzleHttp\Client());
        }
    }
}

