<?php

namespace App\Entity\Sax\Member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use sacore\application\DateTime;

#[Table(name: 'sa_member_login_keys')]
#[Index(name: 'IDX_member_login_key_member', columns: ['user_id'])]
#[Entity]
#[HasLifecycleCallbacks]
#[InheritanceType('SINGLE_TABLE')]
class saMemberLoginKey
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    public $id;

    #[Column(type: 'string', nullable: true)]
    public $uuid;

    #[Column(type: 'string', nullable: true)]
    public $type;

    #[Column(type: 'datetime', nullable: true)]
    public $date_issued;

    #[Column(type: 'boolean', nullable: true)]
    public $revoked;

    #[ManyToOne(targetEntity: 'saMemberUsers', inversedBy: 'login_keys')]
    protected $user;

    /**
     * saMemberLoginKey constructor.
     */
    public function __construct()
    {
        $this->setUuid($this->gen_uuid());
    }

    private function gen_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),

            // 16 bits for "time_mid"
            mt_rand(0, 0xFFFF),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0FFF) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3FFF) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF)
        );
    }

    #[PrePersist]
    public function validate()
    {
        $member_id = $this->getId();
        if (empty($member_id)) {
            $this->date_issued = new DateTime();
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
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param  mixed  $uuid
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
     * @param  mixed  $type
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
     * @param  mixed  $date_issued
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
     * @param  mixed  $revoked
     */
    public function setRevoked($revoked)
    {
        $this->revoked = $revoked;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param  mixed  $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
