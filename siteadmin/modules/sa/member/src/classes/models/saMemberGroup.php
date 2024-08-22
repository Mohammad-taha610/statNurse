<?php
namespace sa\member;
use sacore\application\ValidateException;
use sacore\utilities\fieldValidation;

/**
 * @Entity(repositoryClass="saMemberGroupRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @HasLifecycleCallbacks
 * @Table(name="sa_member_group")
 */

class saMemberGroup
{
    /** @Id @Column(type="integer") @GeneratedValue */
    public $id;
    /** @Column(type="string", nullable=true) */
    public $name;
    /** @Column(type="string", nullable=true) */
    public $description;
    /** 
     * @ManyToMany(targetEntity="saMember", mappedBy="groups") 
     */
	protected $members;
	/** @Column(type="boolean", nullable=true) */
    protected $is_default;
	
    /**
     * @PrePersist @PreUpdate
     */
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
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
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
     * @param string $description
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


    public function toArray() {

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
     * @param mixed $is_default
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
    }
}
