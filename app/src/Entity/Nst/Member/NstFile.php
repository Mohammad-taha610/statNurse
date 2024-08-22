<?php

namespace App\Entity\Nst\Member;

use App\Entity\Sax\Files\saFile;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @IOC_NAME="saFile"
 */
#[Entity]
class NstFile extends saFile
{
    /**
     * @var Nurse $nurse
     */
    #[ManyToOne(targetEntity: Nurse::class, inversedBy: 'nurse_files')]
    protected $nurse;

    /**
     * @var Provider $provider
     */
    #[ManyToOne(targetEntity: Provider::class, inversedBy: 'provider_files')]
    protected $provider;

    /**
     * @var NstFileTag $tag
     */
    #[ManyToOne(targetEntity: NstFileTag::class)]
    #[JoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    protected $tag;

    /**
     * @return Nurse
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * @param  Nurse  $nurse
     * @return NstFile
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;

        return $this;
    }

    /**
     * @return NstFileTag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param  NstFileTag  $tag
     * @return NstFile
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param  Provider  $provider
     * @return NstFile
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }
}
