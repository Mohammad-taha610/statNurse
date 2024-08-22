<?php

namespace sa\system;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use sacore\application\DateTime;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @InheritanceType("SINGLE_TABLE")
 * @Table(name="sa_user_login_keys")
 */
class SaUserLoginKey
{
    /** @Id @Column(type="integer") @GeneratedValue  */
    protected $id;
    /** @Column(type="string", nullable=true) */
    protected $uuid;
    /** @Column(type="string", nullable=true) */
    protected $type;
    /** @Column(type="string", nullable=true) */
    protected $date_issued;
    /** @Column(type="boolean", nullable=true) */
    protected $revoked;
    /** @ManyToOne(targetEntity="saUser", inversedBy="login_keys") */
    protected $sa_user;

    public function __construct()
    {
        $this->setUuid($this->gen_uuid());
    }

    /**
     * Generate unique id for login key
     *
     * @return string
     */
    private function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    /**
     * @PrePersist
     * @PreUpdate
     */
    public function validate()
    {
        $sa_user_id = $this->getId();

        // Set Date Issued upon creation
        if(empty($sa_user_id)) {
            $this->date_issued = new DateTime();
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getDateIssued()
    {
        return $this->date_issued;
    }

    /**
     * @param mixed $date_issued
     */
    public function setDateIssued($date_issued)
    {
        $this->date_issued = $date_issued;
    }

    /**
     * @return mixed
     */
    public function getRevoked()
    {
        return $this->revoked;
    }

    /**
     * @param mixed $revoked
     */
    public function setRevoked($revoked)
    {
        $this->revoked = $revoked;
    }

    /**
     * @return saUser
     */
    public function getSaUser()
    {
        return $this->sa_user;
    }

    /**
     * @param mixed $sa_user
     */
    public function setSaUser($sa_user)
    {
        $this->sa_user = $sa_user;
    }
}