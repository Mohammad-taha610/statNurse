<?php

namespace sa\api;

use sacore\application\DefaultRepository;
use sacore\utilities\mcrypt;

/**
 * Class apiKeyRepository
 * @package sa\api
 */
class apiKeyRepository extends DefaultRepository
{
    /**
     * @param string $clientIdentifier
     * @return ApiKey|null
     */
    public function getKeyForClientIdentifier(string $clientIdentifier) : ?ApiKey
    {
        /** @var ApiKey $apiKey */
        return $this->findOneBy([
            'client_id' => $clientIdentifier,
            'is_active' => true
        ]);
    }

    /**
     * Generates a globally unique ApiKey for
     * client applications
     *
     * @param $clientId - Generally the App package name
     * @return string - The generated Api Key
     */
    public function generateApiKey($clientId): string
    {
        $key = md5(uniqid(time(), true));

        return password_hash($clientId.$key, PASSWORD_BCRYPT);
    }
}