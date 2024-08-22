<?php

namespace sa\system;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'sa_user_group_permission')]
#[Entity(repositoryClass: 'saUserGroupPermissionRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class saUserGroupPermission
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    /**
     * @var string
     */
    #[Column(type: 'string', nullable: true)]
    protected $name;

    /**
     * @var string
     */
    #[Column(type: 'string', nullable: true)]
    protected $grouping;

    /**
     * @var string
     */
    #[Column(type: 'string', nullable: true, unique: true)]
    protected $permission_code;

    /**
     * Constructor
     */
    public function __construct()
    {
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

    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return self
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
     * Get the value of permission_code
     *
     * @return  string
     */
    public function getPermissionCode()
    {
        return $this->permission_code;
    }

    /**
     * Set the value of permission_code
     *
     *
     * @return  self
     */
    public function setPermissionCode(string $permission_code)
    {
        $this->permission_code = $permission_code;

        return $this;
    }

    /**
     * Get the value of grouping
     *
     * @return  string
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    /**
     * Set the value of grouping
     *
     *
     * @return  self
     */
    public function setGrouping(string $grouping)
    {
        $this->grouping = $grouping;

        return $this;
    }
}
