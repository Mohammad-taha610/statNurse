<?php

namespace sa\member;

use Johnstyle\GoogleAuthenticator\GoogleAuthenticator;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modelResult;
use sacore\application\modRequest;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="sa_member_devices")
 */
class saMemberDevice {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @ManyToOne(targetEntity="saMemberUsers", cascade={"persist"}) */
    protected $user;
    /** @Column(type="string", nullable=true) */
    protected $machine_id;
    /** @Column(type="string", nullable=true) */
    protected $code;
    /** @Column(type="boolean", nullable=true) */
    protected $google_auth;
    /** @Column(type="boolean", nullable=true) */
    protected $verified;
    /** @Column(type="string", nullable=true) */
    protected $description;


    /**
     * Issue verification code
     *
     * @param $machineId
     * @param $user \sa\member\saMemberUsers
     * @param bool $phoneid
     * @throws \sacore\application\Exception
     */
    public static function issueVerificationCode($machineId, $user, $phoneid=false, $google_auth=false)
    {
        /** @var saMemberDevice $device */
        $device = app::$entityManager->getRepository( ioc::staticResolve('saMemberDevice') )->findOneBy( array('machine_id'=>$machineId) );
        if ($device)
            return;

        $device = ioc::resolve('saMemberDevice');
        $device->setVerified(false) ;
        $device->setUser($user);
        $device->setMachineId($machineId);

        if ($google_auth) {
            $device->setGoogleAuth(true);
        }
        else
        {
            $device->setCode( rand(10000, 99999) );
        }

        app::$entityManager->persist($device);

        if ($phoneid)
        {
            $member = $user->getMember();
            /** @var saMemberPhone $phone */
            $phone = app::$entityManager->getRepository( ioc::staticResolve('saMemberPhone') )->findOneBy( array('id'=>$phoneid, 'member'=>$member) );

            if ($phone && $phone->getType()=='mobile')
            {
                modRequest::request('messages.sendSMS', '0', array('phone'=>preg_replace('/[^0-9]/', '', $phone->getPhone()), 'body'=>'Your security code is: '.$device->getCode()) );
            }
            elseif ($phone)
            {
                modRequest::request('messages.sendVoice', '0', array(
                        'phone'=>preg_replace('/[^0-9]/', '', $phone->getPhone()),
                        'body'=>'{p|2} Hello {p|1} Thank You for using eye4techology. {p|1} Your security code is: {spell|'.$device->getCode().'}. {p|1} Your security code is: {spell|'.$device->getCode().'}. {p|1} Good Bye. '
                    )
                );
            }
        }

        app::$entityManager->flush();

    }

    public static function isDeviceVerified($machineId, $user)
    {
        /** @var saMemberDevice $device */
        $device = app::$entityManager->getRepository( ioc::staticResolve('saMemberDevice') )->findOneBy( array('machine_id'=>$machineId, 'user'=>$user, 'verified'=>true) );

        if ($device)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param string $machineId
     * @param saMemberUsers $user
     * @param integer $code
     * @param string $description
     * @return bool
     */
    public static function checkVerificationCode($machineId, $user, $code, $description='')
    {
        /** @var saMemberDevice $device */
        $device = app::$entityManager->getRepository( ioc::staticResolve('saMemberDevice') )->findOneBy( array('machine_id'=>$machineId, 'user'=>$user, 'verified'=>false), array('id'=>'DESC') );

        if ($device && !$device->getGoogleAuth() && $code==$device->getCode() && $code)
        {
            $device->setVerified(true);
            $device->setDescription($description);
            app::$entityManager->persist($device);
            app::$entityManager->flush();
            return true;
        }
        elseif ($device && $device->getGoogleAuth() && $code)
        {

            $google = new GoogleAuthenticator($user->getGoogleAuthenticatorKey());

            if ($google->verifyCode($code, 5)) {
                $device->setVerified(true);
                $device->setCode($code);
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
     * Set machine_id
     *
     * @param string $machineId
     * @return saMemberDevice
     */
    public function setMachineId($machineId)
    {
        $this->machine_id = $machineId;
    
        return $this;
    }

    /**
     * Get machine_id
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
     * @return saMemberDevice
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
     * @return saMemberDevice
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
     * @return saMemberDevice
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
     * Set user
     *
     * @param \sa\member\saMemberUsers $user
     * @return saMemberDevice
     */
    public function setUser(\sa\member\saMemberUsers $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \sa\member\saMemberUsers 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set googleAuth
     *
     * @param boolean $googleAuth
     *
     * @return saMemberDevice
     */
    public function setGoogleAuth($googleAuth)
    {
        $this->google_auth = $googleAuth;

        return $this;
    }

    /**
     * Get googleAuth
     *
     * @return boolean
     */
    public function getGoogleAuth()
    {
        return $this->google_auth;
    }
}
