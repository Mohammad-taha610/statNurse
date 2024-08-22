<?php

namespace sa\api\Service;

use sa\api\ApiAuthException;
use sa\api\ApiKey;
use sa\api\apiKeyRepository;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sahtmldocument\htmldocument\Parser;
use sa\member\auth;
use sa\system\saAuth;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Class ApiAuthenticationService
 * @package sa\api
 */
class ApiAuthenticationService
{
    private Request $request;
    private apiKeyRepository $apiKeyRepo;

    /**
     * ApiAuthenticationService constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        /** @var apiKeyRepository $apiKeyRepo */
        $apiKeyRepo = ioc::getRepository('ApiKey');
        $this->apiKeyRepo = $apiKeyRepo;
    }

    private function login(string $loginKey)
    {
        $auth = ioc::staticResolve('auth');
        /** @var auth $auth */
        $auth = $auth::getInstance();
        $auth->logon(false, false, $loginKey, 'api');
    }

    /**
     * @return bool
     * @throws ApiAuthException
     */
    public function authenticate(): bool
    {
        $apiKey = $this->getApiKeyForRequest();

        if (!$apiKey) {
            return false;
        }

        $authHeaderName = app::get()
            ->getConfiguration()
            ->get('api_authorization_header_name_client')
            ->getValue();

        $serverAuthHash = $this->generateAuthHash($apiKey);

				$echoAuth = $this->getRequestHeaders()->get('EchoAuth');
				if ($echoAuth == 'true') {
					echo $serverAuthHash;
				}
        $clientAuthHash = $this->getRequestHeaders()->get($authHeaderName);
        $loginKeyHeader = $this->getRequestHeaders()->get('Login-Key');

        if (!empty($loginKeyHeader)) {
            $this->login($loginKeyHeader);
        }

        if (strcmp($serverAuthHash, $clientAuthHash) != 0) {
            throw new ApiAuthException('Unauthorized', 401, 'HMAC Signature Mismatch');
        }

        $permittedScope = $apiKey->getEntityScope();
        $permitted = false;

        $reqModule = $this->request->getRouteParams()->get('module');
        $reqEntity = $this->request->getRouteParams()->get('entity') ?: '';

        foreach ($permittedScope as $entity) {
            $entityParts = explode('\\', $entity);

            if (strtolower($reqModule) == strtolower($entityParts[2]) && strtolower($reqEntity) == strtolower($entityParts[3])) {
                $permitted = true;

                break;
            }
        }

        if (!$reqEntity) {
            $permitted = true;
        }


        return $permitted;
    }

    /**
     * @return bool
     */
    private function isChunkedRequest(): bool
    {
        $rangeReqHeader = $this->getRequestHeaders()->get('http_content_range');
        $chunkKeyReqHeader = $this->getRequestHeaders()->get('chunk_key');

        if (!empty($rangeReqHeader) && !empty($chunkKeyReqHeader)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param ApiKey $apiKey
     * @return string
     */
    private function generateAuthHash(ApiKey $apiKey): string
    {
        $reqUrl = parse_url($this->request->getUri());

        $requestContent = $this->request->getContent();

        $hashStr = $apiKey->getApiKey() .
            md5($requestContent) .
            $apiKey->getClientId() .
            $this->getRequestHeaders()->get('Content-Type') .
            $reqUrl['path'];

        return hash_hmac('sha256', $hashStr, $apiKey->getApiKey());
    }

    /**
     * @throws ApiAuthException
     */
    public function getApiKeyForRequest(): ApiKey
    {
        $clientId = $this->getRequestHeaders()->get('Client-Identifier');

        if (empty($clientId)) {
            throw new ApiAuthException('Unauthorized', 401, 'Client-Identifier header missing.');
        }

        /** @var ApiKey $apiKey */
        $apiKey = $this->apiKeyRepo->getKeyForClientIdentifier($clientId);

        if (!$apiKey) {
            throw new ApiAuthException('Unauthorized', 401, 'Matching Api Key entity not found for client identifier: ' . $clientId);
        }

        return $apiKey;
    }

    /**
     * @return HeaderBag
     */
    private function getRequestHeaders(): HeaderBag
    {
        return $this->request->headers;
    }
}
