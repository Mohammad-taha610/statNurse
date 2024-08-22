<?php
namespace sa\member;

use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Event;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\ValidateException;
use sacore\application\view;
use \sacore\utilities\fieldValidation;
use sacore\utilities\doctrineUtils;
use Captcha\Captcha;

/**
 * @Entity(repositoryClass="saMemberRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="sa_member", indexes={
 * @Index(name="IDX_member_last_name", columns={"last_name"}),
 * @Index(name="IDX_member_first_name", columns={"first_name"})
 * })
 */
class saMember
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="string", nullable=true) */
    protected $company;
    /** @Column(type="string", nullable=true) */
    protected $last_name;
    /** @Column(type="string", nullable=true) */
    protected $middle_name;
    /** @Column(type="string", nullable=true) */
    protected $first_name;
    /** @Column(type="boolean", nullable=true) */
    protected $is_active;
    /** @Column(type="boolean", nullable=true) */
    protected $is_deleted;
    /** @Column(type="boolean", nullable=true) */
    protected $is_pending;
    /** @Column(type="datetime", nullable=true) */
    protected $date_created;
    /** @Column(type="integer", nullable=true) */
    protected $avatar;
    /** @Column(type="string", nullable=true) */
    protected $facebook_id;
    /** @Column(type="string", nullable=true) */
    protected $twitter_id;
    /** @Column(type="string", nullable=true) */
    protected $instagram_id;
    /** @Column(type="string", nullable=true, length=3000) */
    protected $comment;
    /** @Column(type="datetime", nullable=true) */
	protected $customer_since_date;

    /** @Column(type="string", nullable=true) */
    protected $old_id;

    /** @OneToMany(targetEntity="saMemberEmail", mappedBy="member", cascade={"all"}) */
    protected $emails;
    /**
     * @OneToMany(targetEntity="saMemberUsers", mappedBy="member", cascade={"all"})
     * @OrderBy({"last_name" = "ASC"})
     */
    protected $users;
    /** @OneToMany(targetEntity="saMemberAddress", mappedBy="member", cascade={"all"}) */
    protected $addresses;
    /** @OneToMany(targetEntity="saMemberPhone", mappedBy="member", cascade={"all"}) */
    protected $phones;
    /** 
     * @ManyToMany(targetEntity="saMemberGroup", inversedBy="members", fetch="EAGER") 
     */
    protected $groups;
    /** @Column(type="array", nullable=true) */
    protected $other;
    /** @Column(type="string", nullable=true) */
    protected $homepage;

    /** @OneToMany(targetEntity="saMemberNotification", mappedBy="member") */
    protected $notifications;

    /** @Column(type="boolean", nullable=true) */
    protected $require_two_factor;

    /**
     * @PrePersist @PreUpdate
     */
    public function validate()
    {
        $fv = new fieldValidation();
        $fv->isNotEmpty($this->last_name, 'Please enter a last name.');
        $fv->isNotEmpty($this->first_name, 'Please enter a first name.');

        if(empty($this->old_id)) {
            $this->old_id = null;
        }

        if (!$this->getId()) {
            $this->setDateCreated( new DateTime() );
        }

        if (  app::get()->getConfiguration()->get('member_groups')->getValue() && app::get()->getConfiguration()->get('member_groups')->getValue() !='member' && count($this->getGroups())>0 ) {
            $fv->adderror('Member groups are not enabled. To use this feature please add the member_groups config const and set it to member.');
        }

        if ($fv->hasErrors()) {
            throw new ValidateException(implode('<br />', $fv->getErrors()));
        }
    }
    
    /**
     * @PreRemove
     */
    public function preRemove()
    {
        Event::fire('member.pre.delete', $this);
    }

    // NO STATE MODEL FUNCTIONS
    // THESE FUNCTIONS SHOULD ALL BE STATIC
    public static function saveMember($data, $setPending = false)
    {
        /** @var saMember $member */
        $member = ioc::resolve('saMember');
        $member = doctrineUtils::setEntityData($data, $member);
        $member->setDateCreated( new DateTime() );

        $member->setLastName($data['last_name']);
        $member->setFirstName($data['first_name']);
        $member->setCompany($data['company']);
        $member->setMiddleName($data['middle_name']);
        $member->setIsActive( true );
        
        if($setPending) {
            $member->setIsPending(true);
        } else {
            $member->setIsPending(false);
        }
        
        $email = ioc::resolve('saMemberEmail');
        $member->addEmail($email);
        $email->setEmail($data['email']);
        $email->setType('personal');
        $email->setIsActive(true);
        $email->setIsPrimary(true);
        $email->setMember($member);

        if (!empty($data['street_one'])) {
            $address = ioc::resolve('saMemberAddress');
            $member->addAddress($address);
            $address->setStreetOne($data['street_one']);
            $address->setStreetTwo($data['street_two']);
            $address->setCity($data['city']);
            $address->setPostalCode($data['postal_code']);
            $address->setState($data['state']);
            $address->setCountry($data['country']);
            $address->setType('personal');
            $address->setIsActive(true);
            $address->setIsPrimary(true);
            $address->setMember($member);
        }

        if (!empty($data['phone'])) {

            $phone = ioc::resolve('saMemberPhone');
            $member->addPhone($phone);
            $phone->setPhone($data['phone']);
            $phone->setType('personal');
            $phone->setIsActive(true);
            $phone->setIsPrimary(true);
            $phone->setMember($member);
        }

        $user = ioc::resolve('saMemberUsers');
        $user->setDateCreated(new DateTime());
        $member->addUser($user);
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setUsername($data['email']);
        $user->setMember($member);
        $user->setPassword($data['password']);
        $user->setIsActive(true);
        $user->setEmail($email);

        $groupSetting = app::get()->getConfiguration()->get('member_groups')->getValue();

        if ( !empty($data['group_name']) ) {
            /** @var saMemberGroup $group */
            $group = app::$entityManager->getRepository( ioc::staticResolve('saMemberGroup') )->findOneBy( array( 'name' => $data['group_name'] ) );
            
            if($groupSetting == 'user' && $group) {
                $user->addGroup($group);    
            } else if($group) {
                $member->addGroup($group);
            }
        }

        $defaultGroups = ioc::getRepository('saMemberGroup')->findBy(array('is_default' => true));

        if($defaultGroups) {
            /** @var saMemberGroup $group */
            foreach($defaultGroups as $defaultGroup) {
                if($groupSetting == 'user') {
                    $user->addGroup($defaultGroup);
                } else {
                    $member->addGroup($defaultGroup);
                }
            }
        }

        app::$entityManager->persist($member);
        app::$entityManager->flush();

        return $member;
    }

    public static function memberSignUp($data, $send_confirmation_email = false)
    {
        $fv = new fieldValidation();
        $usingRecaptcha = app::get()->getConfiguration()->get('signup_form_use_recaptcha')->getValue();

        if ($usingRecaptcha) {
            $captcha = new Captcha();
            $captcha->setPrivateKey(app::get()->getConfiguration()->get('recaptcha_private')->getValue());
            $captcha->setPublicKey(app::get()->getConfiguration()->get('recaptcha_public')->getValue());
            $captcha->setRemoteIp($_SERVER['REMOTE_ADDR']);
            $response = $captcha->check();

            if (!$response->isValid())
            {
                $fv->adderror('Your reCAPTCHA submission was invalid. Please try again.');
            }
        }
        
        
        $fv->isNotEmpty($data['password'], 'Please provide a password.');
        $fv->isEqual($data['email'], $data['email2'], 'Your email addresses do not match.');
        $fv->isEqual($data['password'], $data['password2'], 'Your passwords do not match.');
        
        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array('username' => $data['email']));
        if (is_object($user)) {
            $existing_member = $user->getMember();
            if($existing_member) {
                if($existing_member->getIsDeleted() != true) {
                    $fv->adderror('Please enter another email address because the one you entered is already being used.');
                }
            }
        }

        if ($fv->hasErrors()) {
            throw new ValidateException( $fv->getHtml() );
        }
        
        $sendConfirmationEmail = false;
        
        if($send_confirmation_email || app::get()->getConfiguration()->get('member_confirmation_email')->getValue()) {
            $sendConfirmationEmail = true;
        }
        
        $member = static::saveMember($data, $sendConfirmationEmail);

        if ($sendConfirmationEmail) {
            static::sendConfirmationEmail(  $data['email'] );
        } else {
            $auth = auth::getInstance();
            $auth->logon($data['email'], $data['password']);
        }

        return $member;
    }

    public static function sendConfirmationEmail($username)
	{
        $saMemberUsers = ioc::staticResolve('saMemberUsers');
        $saMemberEmail = ioc::staticResolve('saMemberEmail');

        /** @var saMemberUsers $user */
        $user = app::$entityManager->getRepository($saMemberUsers)->findOneBy(array('username'=>$username));

		if ($user) {
            $fv = new fieldValidation();
			$confirmation_key = $fv->keyGeneration(10,32);
            $user->setPasswordResetKey( $confirmation_key );

            app::$entityManager->flush();

            /** @var saMemberEmail $memberEmail */
            $memberEmail = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array('email'=>$username, 'member'=>$user->getMember(), 'is_active'=>true));

            if (!$memberEmail) {
                // If the username does not equal an email on account then find the primary email address.
                $memberEmail = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array('is_primary'=>true, 'member'=>$user->getMember(), 'is_active'=>true));
            }

            if (!$memberEmail) {
                // If we dont have a primary email address send to the first email address on the account.
                $memberEmail = app::$entityManager->getRepository($saMemberEmail)->findOneBy(array('member'=>$user->getMember(), 'is_active'=>true));
            }

            $confirmation_url = app::get()->getConfiguration()->get('site_url')->getValue() . '/member/signup/confirmation' . '?i=' . $user->getId() . '&k=' . $confirmation_key;

            $view = new View('email');
            $site_name = app::get()->getConfiguration()->get('site_name')->getValue();

$view->data['body'] = <<<EOF

			<h1>Welcome!</h1>
			<p>Thank you for creating an account with $site_name. Please click on the following url to confirm your account, <a href="$confirmation_url">$confirmation_url</a> or cut and paste the URL into your address bar in your browser.</p>

            Thank You,<br />
            $site_name
EOF;

            $view->setXSSSanitation(false);
            $body = $view->getHTML();

            if ($memberEmail) {
                modRequest::request('messages.sendEmail', array( 'to'=>$memberEmail->getEmail(), 'body'=>$body, 'subject'=>app::get()->getConfiguration()->get('site_name')->getValue() . ' - Welcome Confirmation'  ));
            }
            elseif(filter_var($username, FILTER_VALIDATE_EMAIL)) {
                modRequest::request('messages.sendEmail', array( 'to'=>$username, 'body'=>$body, 'subject'=>app::get()->getConfiguration()->get('site_name')->getValue() . ' - Welcome Confirmation' ));
            }
            else {

                throw new Exception('We couldn\'t send the confirmation email');
            }

		}
		else
		{

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
     * Set company
     *
     * @param string $company
     * @return saMember
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return saMember
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
     * Set middle_name
     *
     * @param string $middleName
     * @return saMember
     */
    public function setMiddleName($middleName)
    {
        $this->middle_name = $middleName;

        return $this;
    }

    /**
     * Get middle_name
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return saMember
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
     * Set is_active
     *
     * @param boolean $isActive
     * @return saMember
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
     * Set is_pending
     *
     * @param boolean $isPending
     * @return saMember
     */
    public function setIsPending($isPending)
    {
        $this->is_pending = $isPending;

        return $this;
    }

    /**
     * Get is_pending
     *
     * @return boolean
     */
    public function getIsPending()
    {
        return $this->is_pending;
    }

    /**
     * Set date_created
     *
     * @param DateTime $dateCreated
     * @return saMember
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Get date_created
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set avatar
     *
     * @param integer $avatar
     * @return saMember
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return integer
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return saMember
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Add emails
     *
     * @param \sa\member\saMemberEmail $emails
     * @return saMember
     */
    public function addEmail(\sa\member\saMemberEmail $emails)
    {
        $this->emails[] = $emails;

        return $this;
    }

    /**
     * Remove emails
     *
     * @param \sa\member\saMemberEmail $emails
     */
    public function removeEmail(\sa\member\saMemberEmail $emails)
    {
        $this->emails->removeElement($emails);
    }

    /**
     * Get emails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add users
     *
     * @param \sa\member\saMemberUsers $users
     * @return saMember
     */
    public function addUser(\sa\member\saMemberUsers $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \sa\member\saMemberUsers $users
     */
    public function removeUser(\sa\member\saMemberUsers $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add addresses
     *
     * @param \sa\member\saMemberAddress $addresses
     * @return saMember
     */
    public function addAddress(\sa\member\saMemberAddress $addresses)
    {
        $this->addresses[] = $addresses;

        return $this;
    }

    /**
     * Remove addresses
     *
     * @param \sa\member\saMemberAddress $addresses
     */
    public function removeAddress(\sa\member\saMemberAddress $addresses)
    {
        $this->addresses->removeElement($addresses);
    }

    /**
     * Get addresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Add phones
     *
     * @param \sa\member\saMemberPhone $phones
     * @return saMember
     */
    public function addPhone(\sa\member\saMemberPhone $phones)
    {
        $this->phones[] = $phones;

        return $this;
    }

    /**
     * Remove phones
     *
     * @param \sa\member\saMemberPhone $phones
     */
    public function removePhone(\sa\member\saMemberPhone $phones)
    {
        $this->phones->removeElement($phones);
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
     * Add groups
     *
     * @param \sa\member\saMemberGroup $groups
     * @return saMember
     */
    public function addGroup(\sa\member\saMemberGroup $groups)
    {
        if(!$this->groups->contains($groups)) {
            $this->groups[] = $groups;
        }
        
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

    public function clearGroups()
    {
        $this->groups->clear();
    }

    public function toArray() {

        return get_object_vars($this);
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->emails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->phones = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->is_deleted = false;
    }

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * @param mixed $facebook_id
     */
    public function setFacebookId($facebook_id)
    {
        $this->facebook_id = $facebook_id;
    }

    /**
     * @return mixed
     */
    public function getTwitterId()
    {
        return $this->twitter_id;
    }

    /**
     * @param mixed $twitter_id
     */
    public function setTwitterId($twitter_id)
    {
        $this->twitter_id = $twitter_id;
    }

        /**
     * @return mixed
     */
    public function getInstagramId()
    {
        return $this->instagram_id;
    }

    /**
     * @param mixed $instagram_id
     */
    public function setInstagramId($instagram_id)
    {
        $this->instagram_id = $instagram_id;
    }

	//	this method can be overloaded in child classes
	public function memberAsArray ()
	{
	    return doctrineUtils::getEntityArray($this);
	}

	/**
     * Set customerSinceDate
     *
     * @param \DateTime $customerSinceDate
     *
     * @return saMember
     */
    public function setCustomerSinceDate($customerSinceDate)
    {
        $this->customer_since_date = $customerSinceDate;

        return $this;
    }

    /**
     * Get customerSinceDate
     *
     * @return \DateTime
     */
    public function getCustomerSinceDate()
    {
        return $this->customer_since_date;
    }


    /**
     * Set oldId
     *
     * @param string $oldId
     *
     * @return saMember
     */
    public function setOldId($oldId)
    {
        $this->old_id = $oldId;

        return $this;
    }

    /**
     * Get oldId
     *
     * @return string
     */
    public function getOldId()
    {
        return $this->old_id;
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


    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return saMember
     */
    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    /**
     * @return mixed
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * @param mixed $homepage
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    /**
     * Add notification
     *
     * @param \sa\member\saMemberNotification $notification
     *
     * @return saMember
     */
    public function addNotification(\sa\member\saMemberNotification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \sa\member\saMemberNotification $notification
     */
    public function removeNotification(\sa\member\saMemberNotification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @return mixed
     */
    public function getRequireTwoFactor()
    {
        return $this->require_two_factor;
    }

    /**
     * @param mixed $require_two_factor
     */
    public function setRequireTwoFactor($require_two_factor)
    {
        $this->require_two_factor = $require_two_factor;
        return $this;
    }

}
