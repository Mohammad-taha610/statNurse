<?php

namespace App\Entity\Sax\Member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use sacore\application\ValidateException;
use sacore\utilities\fieldValidation;

#[Table(name: 'sa_member_group')]
#[Entity(repositoryClass: 'saMemberGroupRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[HasLifecycleCallbacks]
class saMemberGroup
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    public $id;

    #[Column(type: 'string', nullable: true)]
    public $name;

    #[Column(type: 'string', nullable: true)]
    public $description;

    #[ManyToMany(targetEntity: saMember::class, mappedBy: 'groups')]
    protected $members;

    #[Column(type: 'boolean', nullable: true)]
    protected $is_default;

    #[PrePersist]
    public function validate()
    {
        $fv = new fieldValidation();
        $fv->isNotEmpty($this->name, 'Please enter a group name.');

        if ($fv->hasErrors()) {
            throw new ValidateException(implode('<br />', $fv->getErrors()));
        }
    }

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
     * @return saMemberGroup
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
     * Set description
     *
     * @param  string  $description
     * @return saMemberGroup
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function toArray()
    {

        return get_object_vars($this);
    }

    /**
     * @return mixed
     */
    public function getisDefault()
    {
        return $this->is_default;
    }

    /**
     * @param  mixed  $is_default
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
    }
}
