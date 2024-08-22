<?php

namespace App\Entity\Sax\System;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'saClusterNodesRepository')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'sa_cluster_nodes')]
class saClusterNode
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $name;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $api_key;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $client_id;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $sa_api_url;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $environment;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return saClusterNode
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set apiKey
     *
     * @param  string  $apiKey
     * @return saClusterNode
     */
    public function setApiKey($apiKey)
    {
        $this->api_key = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * Set clientId
     *
     * @param  string  $clientId
     * @return saClusterNode
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set saApiUrl
     *
     * @param  string  $saApiUrl
     * @return saClusterNode
     */
    public function setSaApiUrl($saApiUrl)
    {
        $this->sa_api_url = $saApiUrl;

        return $this;
    }

    /**
     * Get saApiUrl
     *
     * @return string
     */
    public function getSaApiUrl()
    {
        return $this->sa_api_url;
    }

    /**
     * Set environment
     *
     * @param  string  $environment
     * @return saClusterNode
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
