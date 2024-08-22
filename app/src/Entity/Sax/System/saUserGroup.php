<?php

namespace App\Entity\Sax\System;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'sa_user_group')]
#[Entity(repositoryClass: 'saUserGroupRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class saUserGroup
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string', nullable: true)]
    protected $name;

    #[Column(type: 'string', nullable: true)]
    protected $description;

    #[Column(type: 'string', nullable: true)]
    protected $code;

    /**
     * Many Groups have Many Users.
     */
    #[OneToMany(targetEntity: 'saUser', mappedBy: 'sa_user_group')]
    protected $sa_users;

    #[Column(type: 'array', nullable: true)]
    protected $permissions;

    #[Column(type: 'boolean', nullable: true)]
    protected $is_admin;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sa_users = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function hasPermission($permission)
    {
        $permissions = $this->getPermissions();

        foreach ($permissions as $module) {
            foreach ($module as $permissionToTest => $value) {
                if ($permissionToTest == $permission) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        return false;
    }

    /**
     * Set name
     *
     * @param  string  $name
     * @return saUserGroup
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
     * @return saUserGroup
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

    /**
     * Set code
     *
     * @param  string  $code
     * @return saUserGroup
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get saUsers
     *
     * @return ArrayCollection
     */
    public function getSaUsers()
    {
        return $this->sa_users;
    }

    /**
     * Set permissions
     *
     * @param  array  $permissions
     * @return saUserGroup
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Get permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        if (! is_array($this->permissions)) {
            $this->permissions = [];
        }

        return $this->permissions;
    }

    /**
     * Set is_admin
     *
     * @param  bool  $is_admin
     * @return saUserGroup
     */
    public function setIsAdmin($is_admin)
    {
        $this->is_admin = $is_admin;

        return $this;
    }

    /**
     * Get is_admin
     *
     * @return bool
     */
    public function getIsAdmin()
    {
        return $this->is_admin;
    }
}
