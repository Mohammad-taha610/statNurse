<?php

namespace App\DTO\Member;

use App\Entity\Nst\Member\Provider;

class ProviderDTO
{
    public int $id;
    public string $company;
    public string $providerRoute;

    /**
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->id = $provider->getId();
        $this->company = $provider->getMember()->getCompany();
        $this->providerRoute = "/executive/provider/{$provider->getId()}";
    }
}
