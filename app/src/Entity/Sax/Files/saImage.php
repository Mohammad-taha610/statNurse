<?php

namespace App\Entity\Sax\Files;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity]
class saImage extends saFile
{
    #[OneToOne(targetEntity: 'saImage', cascade: ['remove'])]
    #[JoinColumn(name: 'large_id', referencedColumnName: 'id')]
    protected $large;

    #[OneToOne(targetEntity: 'saImage', cascade: ['remove'])]
    #[JoinColumn(name: 'medium_id', referencedColumnName: 'id')]
    protected $medium;

    #[OneToOne(targetEntity: 'saImage', cascade: ['remove'])]
    #[JoinColumn(name: 'small_id', referencedColumnName: 'id')]
    protected $small;

    #[OneToOne(targetEntity: 'saImage', cascade: ['remove'])]
    #[JoinColumn(name: 'xsmall_id', referencedColumnName: 'id')]
    protected $xsmall;

    #[OneToOne(targetEntity: 'saImage', cascade: ['remove'])]
    #[JoinColumn(name: 'micro_id', referencedColumnName: 'id')]
    protected $micro;

    #[OneToOne(targetEntity: 'saImage', cascade: ['remove'])]
    #[JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;

    #[Column(type: 'string', nullable: true)]
    protected $size_name;

    /**
     * @return mixed
     */
    public function getLarge()
    {
        if ($this->size_name == 'lg') {
            return $this;
        } else {
            return $this->large;
        }
    }

    /**
     * @param  mixed  $large
     */
    public function setLarge($large)
    {
        $this->large = $large;
    }

    /**
     * @return mixed
     */
    public function getMedium()
    {
        if ($this->size_name == 'md') {
            return $this;
        } else {
            return $this->medium;
        }
    }

    /**
     * @param  mixed  $medium
     */
    public function setMedium($medium)
    {
        $this->medium = $medium;
    }

    /**
     * @return mixed
     */
    public function getMicro()
    {
        if ($this->size_name == 'micro') {
            return $this;
        } else {
            return $this->micro;
        }
    }

    /**
     * @param  mixed  $micro
     */
    public function setMicro($micro)
    {
        $this->micro = $micro;
    }

    /**
     * @return mixed
     */
    public function getSmall()
    {
        if ($this->size_name == 'sm') {
            return $this;
        } else {
            return $this->small;
        }
    }

    /**
     * @param  mixed  $small
     */
    public function setSmall($small)
    {
        $this->small = $small;
    }

    /**
     * @return mixed
     */
    public function getXsmall()
    {
        if ($this->size_name == 'xs') {
            return $this;
        } else {
            return $this->xsmall;
        }
    }

    /**
     * @param  mixed  $xsmall
     */
    public function setXsmall($xsmall)
    {
        $this->xsmall = $xsmall;
    }

    /**
     * @return mixed
     */
    public function getSizeName()
    {
        return $this->size_name;
    }

    /**
     * @param  mixed  $size
     */
    public function setSizeName($size)
    {
        $this->size_name = $size;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        if ($this->size_name == 'original') {
            return $this;
        } else {
            return $this->original;
        }
    }

    /**
     * @param  mixed  $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }
}
