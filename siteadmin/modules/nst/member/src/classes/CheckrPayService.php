<?php

namespace nst\member;

use sacore\application\app;
use sacore\application\Request;
use sacore\application\DateTime;
use sacore\application\ioc;

class CheckrPayService
{

	private $checkrPayApiUrl;
	private $checkrPayAccessKey;
	private $checkrPaySecretKey;
	private $checkrPayWebhookKey;

	public function __construct()
	{
		$this->checkrPayApiUrl = app::get()->getConfiguration()->get('checkr_pay_base_url')->getValue();
		$this->checkrPayAccessKey = app::get()->getConfiguration()->get('checkr_pay_access_key')->getValue();
		$this->checkrPaySecretKey = app::get()->getConfiguration()->get('checkr_pay_secret_key')->getValue();
		$this->checkrPayWebhookKey = app::get()->getConfiguration()->get('checkr_pay_webhook_key')->getValue();
	}

	public function listWorkers($metadata = null, $createdBefore = null, $createdAfter = null)
	{
		$data = [
			"metadata" => $metadata,
			"created_before" => $createdBefore,
			"created_after" => $createdAfter
		];
		// Form the Nurse Data for the request 
		$jsonData = json_encode($data);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->checkrPayApiUrl . 'workers',
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HEADER => FALSE,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonData),
				'X-CHECKR-PAY-ACCESS-KEY: ' . $this->checkrPayAccessKey,
				'X-CHECKR-PAY-SECRET-KEY: ' . $this->checkrPaySecretKey
			),
		));
		$response = curl_exec($curl);
		if (curl_errno($curl)) {
			throw new \Exception('Curl error: ' . curl_error($curl));
		}
		curl_close($curl);
		$response = json_decode($response);
		return $response;
	}

	public function createWorker($nurse)
	{
		$metadata = strval($nurse->getId());
		$phoneNumber = $nurse->getPhoneNumber();
		$email = $nurse->getEmailAddress();
		$data = [
			"metadata" => $metadata,
			"profile" => [
				"phoneNumber" => $phoneNumber,
				"email" => $email,
			]
		];
		// Form the Nurse Data for the request 
		$jsonData = json_encode($data);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->checkrPayApiUrl . 'workers',
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HEADER => FALSE,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => array(
				'Accept: application/json',
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonData),
				'X-CHECKR-PAY-ACCESS-KEY: ' . $this->checkrPayAccessKey,
				'X-CHECKR-PAY-SECRET-KEY: ' . $this->checkrPaySecretKey
			),
		));
		$response = curl_exec($curl);
		if (curl_errno($curl)) {
			throw new \Exception('Curl error: ' . curl_error($curl));
		}
		curl_close($curl);
		// Decode the response. should be in the format:
		// {
		//     "id": "123e4567-e89b-12d3-a456-426614174000",
		//     "metadata": "driver_12345",
		//     "createdAt": 1385798567,
		//     "temporaryAuthenticationToken": "dNJDUdffkgn3k209rt73o223of",
		//     "backgroundCheckId": "e44aa283528e6fde7d542194",
		//     "profile": {
		//     "phoneNumber": "+14155552671",
		//     "email": "john@smith.com",
		//     "address": {}
		//     },
		//     "status": "pending""
		// }
		// See checkr Pay documentation: https://docs.checkrpay.com/#operation/post-worker
		$response = json_decode($response, TRUE);
		return $response;
	}

	public function webhook(Request $request)
	{
		$tempDir = app::get()->getConfiguration()->get('tempDir')->getValue();
		//file_put_contents($tempDir . '/checkrpay-webhook.log', $request . PHP_EOL, FILE_APPEND);

		$apiKey = $this->checkrPayWebhookKey;
		$requestBody = json_decode($request->getContent(), TRUE);

		$computedHash = hash_hmac('sha256', json_encode($requestBody), $apiKey, true);
		$computedHash = base64_encode($computedHash);
		//file_put_contents($tempDir . '/checkrpay-webhook.log', $computedHash . PHP_EOL, FILE_APPEND);

		$receivedHash = $request->headers->get('X-CHECKRPAY-SIGNATURE');
		//file_put_contents($tempDir . '/checkrpay-webhook.log', $receivedHash . PHP_EOL, FILE_APPEND);

		if (hash_equals($computedHash, $receivedHash)) {
			file_put_contents($tempDir . '/checkrpay-webhook.log', new DateTime() . PHP_EOL, FILE_APPEND);
			file_put_contents($tempDir . '/checkrpay-webhook.log', $request->getContent() . PHP_EOL, FILE_APPEND);
			$id = ioc::get('CheckrPayWebhook', ['webhook_id' => $requestBody['id']]);
			if ($id == NULL) {
				$webhook = new CheckrPayWebhook();
				$webhook->setWebhookId($requestBody['id']);
				$webhook->setData(json_encode($requestBody));
				$webhook->setStatus('pending');
				$webhook->setDateCreated(new DateTime('now', app::getInstance()->getTimeZone()));
				app::$entityManager->persist($webhook);
        app::$entityManager->flush();
			}
			return http_response_code(200);
		} else {
			file_put_contents($tempDir . '/checkrpay-webhook.log', new DateTime() . PHP_EOL, FILE_APPEND);
			file_put_contents($tempDir . '/checkrpay-webhook.log', 'Error, hash-mismatch' . PHP_EOL, FILE_APPEND);
			return http_response_code(401);
		}
	}

	public function processCheckrPayWebhooksCron()
	{
		$response = ['success' => false];

		$webhooks = ioc::getRepository('CheckrPayWebhook')->findBy(['status' => 'pending'], ['id' => 'ASC']);
		foreach ($webhooks as $webhook) {
			$webhookData = json_decode($webhook->data, TRUE);
			if ($webhookData['type'] == 'worker.payment_method_updated') {
				// Process change
				$nurse = ioc::get('Nurse', ['checkr_pay_id' => $webhookData['data']['workerId']]);
				
				if ($webhookData['data']['object']['payoutMethods']['selected'] == 'ACHDirectDeposit') {
					// Change to Direct Deposit
					$nurse->setPaymentMethod('Direct Deposit');
					app::$entityManager->flush();
					$webhook->setStatus('completed');
					app::$entityManager->flush();
				} else {
					// Change to Pay Card
					$nurse->setPaymentMethod('Pay Card');
					app::$entityManager->flush();
					$webhook->setStatus('completed');
					app::$entityManager->flush();
				}
			} else {
				// Ignore all other event types
				$webhook->setStatus('ignored');
				app::$entityManager->flush();
			}
		}

		return $response = ['success' => true];
	}
}
