<?php

namespace sa\messages;

use \sacore\application\app;
use \sacore\application\controller;
use sacore\application\ioc;
use \sacore\application\route;
use \sacore\application\navItem;
use \sacore\application\modelResult;
use \sacore\application\view;
use \sacore\utilities\url;
use \sacore\utilities\notification;

class messagesController extends controller {

	public function voiceText($request)
	{
        $voice = app::$entityManager->getRepository( ioc::staticResolve('saVoice') )->findOneBy( array('to_address'=>$request->get('t'), 'id'=> $request->get('i')) );

		if ($voice)
		{
			header('Content-Type: text/xml');
			echo $voice->getVoiceText();
			exit;
		}
		else
		{
			header('Content-Type: text/xml');
			echo '<Response><Say>Invalid voice message</Say></Response>';
			exit;
		}
	}

	public function messagesCli($args) {
		$this->messagesCron(array('batch_id' => $args['b']));
	}

	public function messagesCron($data)
	{
		set_time_limit(1200);

		$batch_id = null;
		if (is_array($data) && !empty($data['batch_id']) ) {
			$batch_id = $data['batch_id'];
		}
		elseif (!empty($data)) {
			$batch_id = $data;
		}
		
		
		$totalemailcount = app::$entityManager->getRepository( ioc::staticResolve('saEmail') )->getNewCount( $batch_id );
		$emailcount = 0;
		$batch_size = 50;

		while($emailcount < $totalemailcount) {
			$emailRS = app::$entityManager->getRepository(ioc::staticResolve('saEmail'))->getNew( $batch_id, $batch_size, 0 );
			foreach ($emailRS as $email) {
				$email->sendNow();
				$emailcount++;
			}
		}

        $cli_io = null;
		if (method_exists(app::get(), 'getCliIO')) {
            $cli_io = app::get()->getCliIO();
        }

        if ($cli_io)
        {
            $cli_io->title('Messages Cron');
        }

		if ($cli_io)
            $cli_io->note('Attempted to Send '.$emailcount.' emails.');
		
		// PUSH NOTIFICATIONS
		$notificationCount = $this->sendPushNotifications($batch_id);
		
		if($cli_io) {
		    $cli_io->note('Attempted to Send ' . $notificationCount . ' push notifications.');
        }
		
        // SEND VOICE MESSAGES
		$voicecount = 0;
        $voiceRS = app::$entityManager->getRepository( ioc::staticResolve('saVoice') )->findBy( array('attempted_send'=>0) );

		foreach($voiceRS as $voice)
		{
			$voice->sendNow();
			$voicecount++;
		}

        if ($cli_io)
            $cli_io->note('Attempted to Send '.$voicecount.' voice messages.');

		$totalsmscount = app::$entityManager->getRepository( ioc::staticResolve('saSMS') )->getNewCount( $batch_id );
		$smscount = 0;
		$batch_size = 50;
		while($smscount < $totalsmscount) {
			$smsRS = app::$entityManager->getRepository(ioc::staticResolve('saSMS'))->getNew( $batch_id, $batch_size, 0 );
			foreach ($smsRS as $sms) {
				$sms->sendNow();
				$smscount++;
			}
		}

        if ($cli_io)
            $cli_io->note('Attempted to Send '.$smscount.' sms messages.');

		// WAIT FOR THE SMS GATEWAY TO DELIVER MESSAGES BEFORE CHECKING STATUS
		if ($smscount>0) {
            sleep(5);

            $count = 0;
            $smsRS = app::$entityManager->getRepository(ioc::staticResolve('saSMS'))->findBy(array('status' => 'queued'));
            foreach ($smsRS as $sms) {
                $sms->checkStatus();
            }
        }

		// WAIT FOR THE SMS GATEWAY TO DELIVER MESSAGES BEFORE CHECKING STATUS
		if ($smscount>0) {
			sleep(5);

			$count = 0;
			$smsRS = app::$entityManager->getRepository(ioc::staticResolve('saSMS'))->findBy(array('status' => 'queued'));
			foreach ($smsRS as $sms) {
				$sms->checkStatus();
			}
		}

		// WAIT FOR THE SMS GATEWAY TO DELIVER MESSAGES BEFORE CHECKING STATUS
		if ($smscount>0) {
			sleep(5);

			$count = 0;
			$smsRS = app::$entityManager->getRepository(ioc::staticResolve('saSMS'))->findBy(array('status' => 'queued'));
			foreach ($smsRS as $sms) {
				$sms->checkStatus();
			}
		}
	}
	
	private function sendPushNotifications($batchId) {
        /** @var pushNotificationRepository $pushNotificationRepo */
        $pushNotificationRepo = ioc::getRepository('PushNotification');
        $totalPushNotificationCount = $pushNotificationRepo->getNewCount($batchId);
        $notificationCount = 0;
        $batch_size = 50;

        while($notificationCount < $totalPushNotificationCount) {
            $pendingNotifications = $pushNotificationRepo->getNew($batchId, $batch_size, 0);

            /** @var PushNotification $notification */
            foreach($pendingNotifications as $notification) {
                /** @var PushNotificationService $notifyService */
                $notifyService = ioc::staticGet('PushNotificationService');
                $notifyService::sendNow($notification);
                
                $notificationCount++;
            }
        }
        
        return $notificationCount;
    }

}
