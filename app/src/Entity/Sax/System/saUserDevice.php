<?php

namespace App\Entity\Sax\System;

use App\Entity\Sax\Messages\saSMS;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: 'saUserDeviceRepository')]
#[HasLifecycleCallbacks]
#[Table(name: 'sa_user_devices')]
class saUserDevice
{
    public const TYPE_GOOGLE_AUTHENTICATOR = 'GA';

    public const TYPE_SMS = 'SMS';

    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue]
    protected int $id;

    #[ManyToOne(targetEntity: saUser::class, cascade: ['persist'], inversedBy: 'devices')]
    protected $user;

    #[Column(type: 'string', nullable: true)]
    protected $type;

    #[Column(type: 'string', nullable: true)]
    protected $machine_id;

    #[Column(type: 'string', nullable: true)]
    protected $code;

    #[Column(type: 'boolean', nullable: true)]
    protected $verified;

    #[Column(type: 'string', nullable: true)]
    protected $description;

    #[Column(type: 'datetime', nullable: true)]
    protected $issue_date;

    #[Column(type: 'datetime', nullable: true)]
    protected $last_activity_date;

    #[Column(type: 'boolean', nullable: true)]
    protected $is_active;

    //* @OneToOne(targetEntity="\sa\messages\saSMS")
    #[OneToOne(targetEntity: saSMS::class)]
    protected $sms_message;
}
