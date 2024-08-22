<?php

namespace sa\messages;

use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message;
use paragraph1\phpFCM\Notification;
use paragraph1\phpFCM\Recipient\Device;
use paragraph1\phpFCM\Recipient\Topic;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\Thread;

class PushNotificationService {
    
    /** @var bool $_batch */
    public static $_batch = false;
    /** @var integer|null  */
    public static $_batch_id = null;
    /** @var null|string $serverKey  */
    private static $serverKey = null;
    /** @var null|Client $client  */
    private static $client = null;
    
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
        /** @var PushNotification $pushNotification */
        $pushNotification = ioc::get('PushNotification');
        $pushNotification->setToken($data['token']);
        $pushNotification->setTopic($data['topic']);
        $pushNotification->setTitle($data['title']);
        $pushNotification->setMessage($data['message']);
        $pushNotification->setPayloadData($data['payload_data']);
        $pushNotification->setDateCreated(new DateTime());
        $pushNotification->setBatchId(static::$_batch_id);
        $pushNotification->setAttemptedSend(false);
        
        app::$entityManager->persist($pushNotification);
        app::$entityManager->flush($pushNotification);
        
        if(!static::$_batch) {
            static::initMessagesThread();
        }
    }

    /**
     * Start a Push Notification batch. If sending more than one 
     * push notification at a time (probably almost always the case), 
     * wrap sending notifications in a batch
     */
    public static function startBatch() {
        static::$_batch = true;
        static::$_batch_id = rand(0, 999999999);
    }

    /**
     * Commit the push notification batch 
     * and queue for sending.
     */
    public static function commitBatch() {
        static::$_batch = false;
        
        static::initMessagesThread();
        
        static::$_batch_id = null;
    }

    /**
     * Initiates the messages cron thread that will check for 
     * unsent notifications, e-mails, & SMS messages.
     * 
     * @throws Exception
     */
    private static function initMessagesThread() {
        $thread = new Thread(
            'executeController', 
            'messagesController', 
            'messagesCron', 
            array(
                'batch_id' => static::$_batch_id
            ));
        
        $thread->run();
    }

    /**
     * Reach out to Firebase API and
     * send push notification.
     *
     * @param PushNotification $notification
     * @throws Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function sendNow($notification) {
        static::initClient();
        
        $outgoingNotification = new Notification(
            $notification->getTitle(), 
            $notification->getMessage()
        );

        $message = new Message();

        if(!empty($notification->getToken())) {
            $message->addRecipient(new Device($notification->getToken()));
        }

        if(!empty($notification->getTopic())) {
            $message->addRecipient(new Topic($notification->getTopic()));
        }

        $message->setNotification($outgoingNotification);
        $message->setData(is_array($notification->getPayloadData()) ? $notification->getPayloadData() : []);
        
        $response = static::$client->send($message);
        $serverResponse = $response->getBody()->getContents();
        $responseJson = json_decode($serverResponse, true);

        if($responseJson['success'] == '0') {
            if($responseJson['results'][0]['error'] == 'NotRegistered') {
                /** @var PushToken $token */
                $token = ioc::getRepository('PushToken')->findOneBy(array('token' => $notification->getToken()));

                if($token) {
                    app::$entityManager->remove($token);
                    app::$entityManager->flush($token);
                }
            }
        }

        $notification->setResponse($serverResponse);
        $notification->setAttemptedSend(true);
        $notification->setDateAttemptedSend(new DateTime());
        $notification->setSuccess($responseJson['success'] ? true : false);
        
        if(!$responseJson['success'] && $responseJson['results'][0]['error'] == 'InvalidRegistration') {
            $matchingToken = ioc::getRepository('PushToken')->findOneBy(array('token' => $notification->getToken()));
            
            if($matchingToken) {
                app::$entityManager->remove($matchingToken);
            }
        }
        
        app::$entityManager->flush();
    }

    /**
     * @throws Exception
     */
    private static function initClient() {
        if(!static::$serverKey) {
            static::$serverKey = app::get()->getConfiguration()->get('fcm_server_key')->getValue();
        }

        if(!static::$serverKey) {
            throw new Exception('A valid server key is required to send push notifications. Please update the configuration with your key.');
        }

        if(!static::$client) {
            static::$client = new Client();
            static::$client->setApiKey(static::$serverKey);
            static::$client->injectHttpClient(new \GuzzleHttp\Client());
        }
    }
}
