<?php

namespace App\Entity\Nst\Events;

use App\Entity\Nst\Member\NstFile;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @IOC_NAME="ShiftOverride"
 */
#[Table(name: 'ShiftOverride')]
#[Entity(repositoryClass: 'ShiftOverrideRepository')]
#[HasLifecycleCallbacks]
class ShiftOverride
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string', nullable: true)]
    protected $supervisor_name;

    #[Column(type: 'string', nullable: true)]
    protected $supervisor_code;

    #[OneToOne(targetEntity: NstFile::class)]
    #[JoinColumn(name: 'signature_file_id', referencedColumnName: 'id')]
    protected $supervisor_signature;

    /** @return NstFile */
    public function getSupervisorSignature()
    {
        return $this->supervisor_signature;
    }

    /**
     * @param  NstFile  $supervisor_signature
     * @return $this
     */
    public function setSupervisorSignature($supervisor_signature)
    {
        $this->supervisor_signature = $supervisor_signature;

        return $this;
    }

    /**
     * @return string
     */
    public function getSupervisorName()
    {
        return $this->supervisor_name;
    }

    /**
     * Summary of setSupervisorName
     *
     * @param  mixed  $supervisor_name
     * @return ShiftOverride
     */
    public function setSupervisorName($supervisor_name)
    {
        $this->supervisor_name = $supervisor_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSupervisorCode()
    {
        return $this->supervisor_code;
    }

    /**
     * @param  mixed  $supervisor_code
     * @return ShiftOverride
     */
    public function setSupervisorCode($supervisor_code)
    {
        $this->supervisor_code = $supervisor_code;

        return $this;
    }
}
