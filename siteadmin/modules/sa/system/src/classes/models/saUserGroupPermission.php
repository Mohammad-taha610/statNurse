<?php
namespace sa\system;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use Johnstyle\GoogleAuthenticator\GoogleAuthenticator;
use Johnstyle\GoogleAuthenticator\GoogleAuthenticatorException;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\ValidateException;
use sacore\utilities\doctrineUtils;
use sacore\utilities\fieldValidation;
use sacore\utilities\mcrypt;

/**
 * @Entity(repositoryClass="saUserGroupPermissionRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="sa_user_group_permission")
 */
class saUserGroupPermission
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** 
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /** 
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $grouping;

    /**
     * @var string
     * @Column(type="string", nullable=true, unique=true) 
     */
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
     * @return integer 
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
     * @param string $name
     *
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
     * @param  string  $permission_code
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
     * @param  string  $grouping
     *
     * @return  self
     */ 
    public function setGrouping(string $grouping)
    {
        $this->grouping = $grouping;

        return $this;
    }
}