<?php

namespace sa\member;
use DateInterval;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\View;
use sacore\application\ValidateException;
use sacore\utilities\fieldValidation;
use sacore\utilities\mcrypt;
use sacore\utilities\url;

/**
 * @Entity(repositoryClass="saMemberUsersRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @HasLifecycleCallbacks
 * @Table(name="sa_member_users", indexes={
 * @Index(name="IDX_member_users_username", columns={"username"}),
 * @Index(name="IDX_member_users_member", columns={"member_id"})
 * })
 */

class saMemberUsers {

    /** @Id @Column(type="integer") @GeneratedValue */
	protected $id;
    /** @Column(type="string") */
    protected $last_name;
    /** @Column(type="string") */
    protected $first_name;
    /** @Column(type="string") */
    protected $username;
    /** @Column(type="string", nullable=true, length=3000) */
    protected $password;
    /** @Column(type="string", nullable=true) */
    protected $password_reset_key;
    /** @Column(type="string", nullable=true) */
    protected $password_reset_key2;
    /** @Column(type="integer", nullable=true) */
    protected $password_reset_ttl;
    /** @Column(type="boolean", nullable=true) */
    protected $is_active;
    /** @Column(type="datetime", nullable=true) */
    protected $last_login;
    /** @Column(type="datetime", nullable=true) */
    protected $last_active;
    /** @Column(type="datetime", nullable=true) */
    protected $date_created;
    /** @Column(type="datetime", nullable=true) */
    protected $date_updated;
    /** @Column(type="datetime", nullable=true) */
    protected $password_reset_date;
    /** @Column(type="integer", nullable=true) */
    protected $login_count;
    /** @Column(type="string", nullable=true) */
    protected $user_key;
    /** @Column(type="string", nullable=true) */
    protected $user_machine_uuid;
    /** @Column(type="string", nullable=true, length=500) */
    protected $user_agent;
	/** @Column(type="string", nullable=true) */
	protected $password_encryption_type;
    /** @Column(type="boolean", nullable=true) */
    protected $is_newsletter;
    
    /** @Column(type="string", nullable=true) */
    protected $google_authenticator_key;
    /** @Column(type="boolean", nullable=true) */
    protected $is_two_factor_setup;

    /** @ManyToOne(targetEntity="saMemberEmail", cascade={"persist"}) */
    protected $email;

    /** @OneToMany(targetEntity="saMemberPhone", mappedBy="user", cascade={"all"}) */
    protected $phones;

    /** @ManyToOne(targetEntity="saMember", inversedBy="users", fetch="EAGER") */
    protected $member;

    /** @Column(type="boolean", nullable=true) */
    protected $is_primary_user;

    /** @Column(type="string", nullable=true) */
    protected $position;
    /** @ManyToMany(targetEntity="saMemberGroup", fetch="EAGER") */
    protected $groups;

    /** @OneToMany(targetEntity="saMemberLoginKey", mappedBy="user", cascade={"all"}) */
    protected $login_keys;

    protected $freezeDateUpdate = false;
    
    /** @Column(type="array", nullable=true) **/
    protected $other;

    /**
     * @PrePersist @PreUpdate
     */
    public function validate()
    {
        $fv = new fieldValidation();
        $fv->isNotEmpty($this->last_name, 'Please enter a last name.');
        $fv->isNotEmpty($this->first_name, 'Please enter a first name.');
        $fv->isNotEmpty($this->username, 'Please enter a username.');
        $fv->isNotEmpty($this->getPassword(true), 'Please enter a password.');

        if (!$this->getId()) {
            $saMemberUsers = ioc::staticResolve('saMemberUsers');
            $users = app::$entityManager->getRepository($saMemberUsers)->findBy(array('username' => $this->username));
            if($users) {
                foreach($users as $user) {
                    $existing_member = $user->getMember();
                    if($existing_member) {
                        if($existing_member->getIsDeleted() != true) {
                            $fv->adderror('That username is already in use. Please try another username.');
                        }
                    }
                }
            }

            $fv->isNotEmpty($this->password, 'Please enter a password.');
            $this->setDateCreated( new \sacore\application\DateTime() );
            $this->setLoginCount(0);
        }

        if ( app::get()->getConfiguration()->get('member_groups') !='user' && count($this->getGroups())>0 ) {
            $fv->adderror('User groups are not enabled. To use this feature please add the member_groups config const and set it to user.');
        }

        if (!$this->freezeDateUpdate) {
            $this->setDateUpdated(new \sacore\application\DateTime());
        }

        if ($fv->hasErrors()) {
            throw new ValidateException(implode('<br />', $fv->getErrors()));
        }

        if($this->getIsNewsletter()) {
            modRequest::request('saUser.newsletter', $this);
        }
    }

    /**
     * @param $reset_key_1
     * @param $reset_key_2
     * @param int $ttlSeconds
     * @return saMemberUsers|boolean
     * @throws \Exception
     */
    public static function getUserFromPasswordResetRequest($reset_key_1, $reset_key_2)
    {
        $query = ioc::getRepository('saMemberUsers')->createQueryBuilder('user');
        $query->where('user.password_reset_key = :reset_key_1');
        $query->andWhere('user.password_reset_key2 = :reset_key_2');
        $query->andWhere('user.is_active = 1');
        $query->setParameter(':reset_key_1', $reset_key_1);
        $query->setParameter(':reset_key_2', $reset_key_2);
        $query->setMaxResults(1);

        /** @var saMemberUsers $user */
        $user = $query->getQuery()->getOneOrNullResult();

        if($user) {
            $resetTtl = $user->getPasswordResetTtl();


            if($resetTtl === null) {
                $resetTtl = app::get()->getConfiguration()->get('password_reset_ttl')->getValue();
            }

            $adjustedDate = new DateTime();
            $adjustedDate->sub(new DateInterval('PT' . $resetTtl . 'S'));

            if($user->getPasswordResetDate() < $adjustedDate && $resetTtl != 0) {
                $user = null;
            }
        }

        return $user;
    }

    public static function requestResetPassword($username, $sendEmail = true, $ttl = 2700) {
        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $saMemberEmail = ioc::staticResolve('saMemberEmail');

        /** @var saMemberUsers $user */
        $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array('username'=>$username, 'is_active' => true));

		if ($user) {
            $user->setPasswordResetKey( md5($username . time() . $_SERVER['REMOTE_ADDR']) );
            $user->setPasswordResetKey2( md5($user->id . time()));
            $user->setPasswordResetDate( new \sacore\application\DateTime() );
            $user->setPasswordResetTtl($ttl);

            app::$entityManager->flush();
            
            if($sendEmail) {
                /** @var saMemberEmail $memberEmail */
                $memberEmail = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array('email' => $username, 'member' => $user->getMember(), 'is_active' => true));
                if (!$memberEmail) {
                    // If the username does not equal a email on account then find the primary email address.
                    $memberEmail = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array('is_primary' => true, 'member' => $user->getMember(), 'is_active' => true));
                }
                if (!$memberEmail) {
                    // If we dont have a primary email address send to the first email address on the account.
                    $memberEmail = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array('member' => $user->getMember(), 'is_active' => true));
                }

                $view = new View('email');
                $view->data['body'] = '<h1>Forgot Your Password?</h1><br />
                We received a request to reset your password. <br/><br /> 
    
                To reset your password, click on the link below (or copy and paste the URL into your browser): <br />
    
                <a href="' . $_SERVER['SERVER_PROTOCOL'] . $_SERVER['HTTP_HOST'] . '/member/resetpasswordconfirm?k=' . $user->password_reset_key . '&i=' . $user->password_reset_key2 . '">' . $_SERVER['SERVER_PROTOCOL'] . $_SERVER['HTTP_HOST'] . '/member/resetpasswordconfirm?k=' . $user->password_reset_key . '&i=' . $user->password_reset_key2 . '</a> <br /> <br />
    
                This email will expire in two hours.
                ';
                $view->data['sitename'] = app::get()->getConfiguration()->get('site_name')->getValue();
                $view->data['siteurl'] = app::get()->getConfiguration()->get('site_url')->getValue();
                $view->setXSSSanitation(false);
                $body = $view->getHTML();

                if ($memberEmail) {
                    modRequest::request('messages.startEmailBatch');
                    modRequest::request('messages.sendEmail', array(
                        'to' => $memberEmail->getEmail(), 
                        'body' => $body, 
                        'subject' => 'Password Reset'
                    ));
                    modRequest::request('messages.commitEmailBatch');
                } elseif (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    modRequest::request('messages.startEmailBatch');
                    modRequest::request('messages.sendEmail', array(
                        'to' => $username, 
                        'body' => $body, 
                        'subject' => app::get()->getConfiguration()->get('site_name')->getValue() . '- Password Reset'
                    ));
                    modRequest::request('messages.commitEmailBatch');
                } else {
                    throw new Exception('We couldn\'t send the password reset email');
                }
            }
		} else {
            throw new Exception('The username specified doesn\'t exist');
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
     * Set username
     *
     * @param string $username
     * @return saMemberUsers
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return saMemberUsers
     */
    public function setPassword($password)
    {
        if (empty($this->user_key)) {
            $this->user_key = md5(uniqid(time(), TRUE));
        }

        $this->password = password_hash($password, PASSWORD_BCRYPT);
		$this->password_encryption_type = 'PASSWORD_BCRYPT';

        return $this;
    }

    /**
     * Get password
     *
     * @param bool $unencrypt
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password_reset_key
     *
     * @param string $passwordResetKey
     * @return saMemberUsers
     */
    public function setPasswordResetKey($passwordResetKey)
    {
        $this->password_reset_key = $passwordResetKey;

        return $this;
    }

    /**
     * Get password_reset_key
     *
     * @return string 
     */
    public function getPasswordResetKey()
    {
        return $this->password_reset_key;
    }

    /**
     * Set password_reset_key2
     *
     * @param string $passwordResetKey2
     * @return saMemberUsers
     */
    public function setPasswordResetKey2($passwordResetKey2)
    {
        $this->password_reset_key2 = $passwordResetKey2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPasswordResetTtl()
    {
        return $this->password_reset_ttl;
    }

    /**
     * @param mixed $password_reset_ttl
     */
    public function setPasswordResetTtl($password_reset_ttl)
    {
        $this->password_reset_ttl = $password_reset_ttl;
    }

    /**
     * Get password_reset_key2
     *
     * @return string 
     */
    public function getPasswordResetKey2()
    {
        return $this->password_reset_key2;
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return saMemberUsers
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set last_login
     *
     * @param \sacore\application\DateTime $lastLogin
     * @return saMemberUsers
     */
    public function setLastLogin($lastLogin)
    {
        $this->last_login = $lastLogin;

        return $this;
    }

    /**
     * Get last_login
     *
     * @return \sacore\application\DateTime
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * Set date_created
     *
     * @param \sacore\application\DateTime $dateCreated
     * @return saMemberUsers
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Get date_created
     *
     * @return \sacore\application\DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set password_reset_date
     *
     * @param \sacore\application\DateTime $passwordResetDate
     * @return saMemberUsers
     */
    public function setPasswordResetDate($passwordResetDate)
    {
        $this->password_reset_date = $passwordResetDate;

        return $this;
    }

    /**
     * Get password_reset_date
     *
     * @return \sacore\application\DateTime
     */
    public function getPasswordResetDate()
    {
        return $this->password_reset_date;
    }

    /**
     * Set login_count
     *
     * @param integer $loginCount
     * @return saMemberUsers
     */
    public function setLoginCount($loginCount)
    {
        $this->login_count = $loginCount;

        return $this;
    }

    /**
     * Get login_count
     *
     * @return integer 
     */
    public function getLoginCount()
    {
        return $this->login_count;
    }

    /**
     * Set member
     *
     * @param \sa\member\saMember $member
     * @return saMemberUsers
     */
    public function setMember(\sa\member\saMember $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \sa\member\saMember 
     */
    public function getMember()
    {
        return $this->member;
    }

    public function toArray() {

        return get_object_vars($this);
    }


    /**
     * Set user_key
     *
     * @param string $userKey
     * @return saMemberUsers
     */
    protected function setUserKey($userKey)
    {
        $this->user_key = $userKey;

        return $this;
    }

    /**
     * Get user_key
     *
     * @return string 
     */
    public  function getUserKey()
    {
        return $this->user_key;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return saMemberUsers
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    
        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return saMemberUsers
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    
        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return saMemberUsers
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set email
     *
     * @param \sa\member\saMemberEmail $email
     * @return saMemberUsers
     */
    public function setEmail(\sa\member\saMemberEmail $email = null)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return \sa\member\saMemberEmail 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set date_updated
     *
     * @param \sacore\application\DateTime $dateUpdated
     * @return saMemberUsers
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->date_updated = $dateUpdated;
    
        return $this;
    }

    /**
     * Get date_updated
     *
     * @return \sacore\application\DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->login_keys = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add groups
     *
     * @param \sa\member\saMemberGroup $groups
     * @return saMemberUsers
     */
    public function addGroup(\sa\member\saMemberGroup $groups)
    {
        $this->groups[] = $groups;
    
        return $this;
    }

    /**
     * Remove groups
     *
     * @param \sa\member\saMemberGroup $groups
     */
    public function removeGroup(\sa\member\saMemberGroup $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set is_primary_user
     *
     * @param boolean $isPrimaryUser
     * @return saMemberUsers
     */
    public function setIsPrimaryUser($isPrimaryUser)
    {
        $this->is_primary_user = $isPrimaryUser;
    
        return $this;
    }

    /**
     * Get is_primary_user
     *
     * @return boolean 
     */
    public function getIsPrimaryUser()
    {
        if($this->is_primary_user == null) {
            return false;
        } else {
            return $this->is_primary_user;
        }
    }

    /**
     * @return mixed
     */
    public function getLastActive()
    {
        return $this->last_active;
    }

    /**
     * @param mixed $last_active
     */
    public function setLastActive($last_active)
    {
        $this->last_active = $last_active;
    }

    /**
     * @param boolean $freezeDateUpdate
     */
    public function setFreezeDateUpdate($freezeDateUpdate)
    {
        $this->freezeDateUpdate = $freezeDateUpdate;
    }

    /**
     * @return mixed
     */
    public function getLoginKeys()
    {
        return $this->login_keys;
    }

    /**
     * @param mixed $login_keys
     * @return saMemberUsers
     */
    public function addLoginKey($login_keys)
    {
        $this->login_keys[] = $login_keys;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserMachineUuid()
    {
        return $this->user_machine_uuid;
    }

    /**
     * @param mixed $user_machine_uuid
     */
    public function setUserMachineUuid($user_machine_uuid)
    {
        $this->user_machine_uuid = $user_machine_uuid;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     * @param string $user_agent
     */
    public function setUserAgent($user_agent)
    {
        $this->user_agent = $user_agent;
    }



    /**
     * Set passwordEncryptionType
     *
     * @param string $passwordEncryptionType
     *
     * @return saMemberUsers
     */
    public function setPasswordEncryptionType($passwordEncryptionType)
    {
        $this->password_encryption_type = $passwordEncryptionType;

        return $this;
    }

    /**
     * Get passwordEncryptionType
     *
     * @return string
     */
    public function getPasswordEncryptionType()
    {
        return $this->password_encryption_type;
    }

    /**
     * Remove loginKey
     *
     * @param \sa\member\saMemberLoginKey $loginKey
     */
    public function removeLoginKey(\sa\member\saMemberLoginKey $loginKey)
    {
        $this->login_keys->removeElement($loginKey);
    }

    /**
     * @return mixed
     */
    public function getIsNewsletter()
    {
        return $this->is_newsletter;
    }

    /**
     * @param mixed $is_newsletter
     */
    public function setIsNewsletter($is_newsletter)
    {
        $this->is_newsletter = $is_newsletter;
    }

    /**
     * Set googleAuthenticatorKey
     *
     * @param string $googleAuthenticatorKey
     *
     * @return saMemberUsers
     */
    public function setGoogleAuthenticatorKey($googleAuthenticatorKey)
    {
        $this->google_authenticator_key = $googleAuthenticatorKey;

        return $this;
    }

    /**
     * Get googleAuthenticatorKey
     *
     * @return string
     */
    public function getGoogleAuthenticatorKey()
    {
        return $this->google_authenticator_key;
    }

    /**
     * Set isTwoFactorSetup
     *
     * @param boolean $isTwoFactorSetup
     *
     * @return saMemberUsers
     */
    public function setIsTwoFactorSetup($isTwoFactorSetup)
    {
        $this->is_two_factor_setup = $isTwoFactorSetup;

        return $this;
    }

    /**
     * Get isTwoFactorSetup
     *
     * @return boolean
     */
    public function getIsTwoFactorSetup()
    {
        return $this->is_two_factor_setup;
    }

    /**
     * Add phone
     *
     * @param \sa\member\saMemberPhone $phone
     *
     * @return saMemberUsers
     */
    public function addPhone(\sa\member\saMemberPhone $phone)
    {
        $this->phones[] = $phone;

        return $this;
    }

    /**
     * Remove phone
     *
     * @param \sa\member\saMemberPhone $phone
     */
    public function removePhone(\sa\member\saMemberPhone $phone)
    {
        $this->phones->removeElement($phone);
    }

    /**
     * Get phones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhones()
    {
        return $this->phones;
    }

    
    /**
     * @return mixed
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * @param mixed $other
     */
    public function setOther($other)
    {
        $this->other = $other;
    }
}
