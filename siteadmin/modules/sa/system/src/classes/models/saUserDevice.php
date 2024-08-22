<?php

namespace sa\system;

use Johnstyle\GoogleAuthenticator\GoogleAuthenticator;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modelResult;
use sacore\application\modRequest;

/**
 * @Entity(repositoryClass="saUserDeviceRepository")
 * @HasLifecycleCallbacks
 * @Table(name="sa_user_devices")
 */
class saUserDevice {

    const TYPE_GOOGLE_AUTHENTICATOR = 'GA';
    const TYPE_SMS = 'SMS';

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @ManyToOne(targetEntity="saUser", inversedBy="devices", cascade={"persist"}) */
    protected $user;
    /** @Column(type="string", nullable=true) */
    protected $type;
    /** @Column(type="string", nullable=true) */
    protected $machine_id;
    /** @Column(type="string", nullable=true) */
    protected $code;
    /** @Column(type="boolean", nullable=true) */
    protected $verified;
    /** @Column(type="string", nullable=true) */
    protected $description;
    /** @Column(type="datetime", nullable=true) */
    protected $issue_date;
    /** @Column(type="datetime", nullable=true) */
    protected $last_activity_date;
    /** @Column(type="boolean", nullable=true) */
    protected $is_active;
    /** @OneToOne(targetEntity="\sa\messages\saSMS") */
    protected $sms_message;


    /**
     * Issue verification code
     *
     * @param $type
     * @param $machineId
     * @param $user saUser
     * @return bool|saUserDevice|void
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\IocException
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    public static function issueVerificationCode($type, $machineId, $user)
    {
        if ($type==static::TYPE_SMS) {
            $cell_number = $user->getCellNumber();
            if (empty($cell_number)) {
                return null;
            }
        }

        /** @var saUserDevice $device */
        $device = ioc::get('saUserDevice', array('machine_id'=>$machineId, 'verified'=>false, 'user'=>$user) );

        if (!$device)
            $device = ioc::resolve('saUserDevice');

        $device->setVerified(false) ;
        $device->setUser($user);
        $device->setMachineId($machineId);
        $device->setIssueDate( new DateTime() );
        $device->setIsActive( true );
        $device->setType($type);

        if ($type==static::TYPE_SMS) {

            $device->setCode(rand(10000, 99999));
            $sms = modRequest::request('messages.sendSMS', '0',
                array(
                    'phone' => preg_replace('/[^0-9]/', '', $user->getCellNumber()),
                    'body' => 'Your Site Administrator security code is: ' . $device->getCode()
                )
            );
            $device->setSmsMessage($sms);

        }

        app::$entityManager->persist($device);
        app::$entityManager->flush();
        return $device;

    }

    public static function isDeviceVerified($machineId, $user, $log_activity=true)
    {
        /** @var saUserDevice $device */
        $device = ioc::get('saUserDevice', array('machine_id'=>$machineId, 'user'=>$user, 'verified'=>true, 'is_active'=>true) );

        if ($device)
        {
            $device->setLastActivityDate(new DateTime());
            app::$entityManager->flush($device);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param $type
     * @param $machineId
     * @param saUser $user
     * @param $code
     * @param string $description
     * @return bool
     */
    public static function checkVerificationCode($type, $machineId, $user, $code, $description='')
    {
        if ($type==static::TYPE_GOOGLE_AUTHENTICATOR) {

            $secret = $user->getGoogleAuthSecret();
            if (!$secret) {
                return false;
            }

            $google = new GoogleAuthenticator($secret);
            if (!$google->verifyCode($code, 3)) {
                return false;
            }

            $device = app::$entityManager->getRepository(ioc::staticResolve('saUserDevice'))->findOneBy(array('type'=>static::TYPE_GOOGLE_AUTHENTICATOR, 'machine_id' => $machineId, 'user' => $user, 'verified' => false));
            $device->setCode($code);
        }
        else{
            $device = app::$entityManager->getRepository(ioc::staticResolve('saUserDevice'))->findOneBy(array('type'=>static::TYPE_SMS, 'machine_id' => $machineId, 'user' => $user, 'verified' => false, 'code' => $code));
        }

        if ($device)
        {
            $device->setVerified(true);
            $device->setDescription($description);
            app::$entityManager->persist($device);
            app::$entityManager->flush();

            return true;
        }
        else
        {
            return false;
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
     * Set machineId
     *
     * @param string $machineId
     *
     * @return saUserDevice
     */
    public function setMachineId($machineId)
    {
        $this->machine_id = $machineId;

        return $this;
    }

    /**
     * Get machineId
     *
     * @return string
     */
    public function getMachineId()
    {
        return $this->machine_id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return saUserDevice
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
     * Set verified
     *
     * @param boolean $verified
     *
     * @return saUserDevice
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Get verified
     *
     * @return boolean
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return saUserDevice
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
     * Set issueDate
     *
     * @param \DateTime $issueDate
     *
     * @return saUserDevice
     */
    public function setIssueDate($issueDate)
    {
        $this->issue_date = $issueDate;

        return $this;
    }

    /**
     * Get issueDate
     *
     * @return \DateTime
     */
    public function getIssueDate()
    {
        return $this->issue_date;
    }

    /**
     * Set user
     *
     * @param \sa\system\saUser $user
     *
     * @return saUserDevice
     */
    public function setUser(\sa\system\saUser $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \sa\system\saUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set smsMessage
     *
     * @param \sa\messages\saSMS $smsMessage
     *
     * @return saUserDevice
     */
    public function setSmsMessage(\sa\messages\saSMS $smsMessage = null)
    {
        $this->sms_message = $smsMessage;

        return $this;
    }

    /**
     * Get smsMessage
     *
     * @return \sa\messages\saSMS
     */
    public function getSmsMessage()
    {
        return $this->sms_message;
    }

    /**
     * Set lastActivityDate
     *
     * @param \DateTime $lastActivityDate
     *
     * @return saUserDevice
     */
    public function setLastActivityDate($lastActivityDate)
    {
        $this->last_activity_date = $lastActivityDate;

        return $this;
    }

    /**
     * Get lastActivityDate
     *
     * @return \DateTime
     */
    public function getLastActivityDate()
    {
        return $this->last_activity_date;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return saUserDevice
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
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
     * @return saUserDevice
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }


}
