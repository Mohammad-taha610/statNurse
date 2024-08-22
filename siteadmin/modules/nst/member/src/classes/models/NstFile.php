<?php

namespace nst\member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use sa\files\saFile;

/**
 * @Entity(repositoryClass="\nst\member\NstFileRepository")
 * @IOC_NAME="saFile"
 */
class NstFile extends saFile
{

    /**
     * @var Nurse $nurse
     * @ManyToOne(targetEntity="\nst\member\Nurse", inversedBy="nurse_files")
     */
    protected $nurse;

    /**
     * @var ApplicationPart2 $nurse_application_part_2
     * @ManyToOne(targetEntity="\nst\applications\ApplicationPart2", inversedBy="application_files")
     * @JoinColumn(name="nurse_application_part_2_id", referencedColumnName="id")
     */
    protected $nurse_application_part_2;

    /**
     * @var Provider $provider
     * @ManyToOne(targetEntity="\nst\member\Provider", inversedBy="provider_files")
     */
    protected $provider;

    /**
     * @var NstFileTag $tag
     * @ManyToOne(targetEntity="nst\member\NstFileTag")
     * @JoinColumn(name="tag_id", referencedColumnName="id")
     */
    protected $tag;

    /**
     * @return Nurse
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * @param Nurse $nurse
     * @return NstFile
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;
        return $this;
    }

    /**
     * @return ApplicationPart2
     */
    public function getNurseApplicationPartTwo()
    {
        return $this->nurse_application_part_2;
    }

    /**
     * @param ApplicationPart2 $nurse_application_part_2
     * @return NstFile
     */
    public function setNurseApplicationPartTwo($nurse_application_part_2)
    {
        $this->nurse_application_part_2 = $nurse_application_part_2;
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
     * @param NstFileTag $tag
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
     * @param Provider $provider
     * @return NstFile
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }



}