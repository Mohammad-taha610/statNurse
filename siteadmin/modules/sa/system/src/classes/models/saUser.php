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
 * @Entity(repositoryClass="saUserRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="sa_user"),indexes={@Index(name="IDX_system_username_idx", columns={"username"})
 */
class saUser
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="string") */
    protected $last_name;
    /** @Column(type="string") */
    protected $first_name;
    /** @Column(type="string") */
    protected $username;
    /** @Column(type="string", nullable=true) */
    protected $email;
    /** @Column(type="string") */
    protected $password;
    /** @Column(type="string", nullable=true) */
    protected $cell_number;
    /** @Column(type="boolean", nullable=true) */
    protected $is_active;
    /** @Column(type="datetime", nullable=true) */
    protected $last_login;
    /** @Column(type="datetime", nullable=true) */
    protected $date_created;
    /** @Column(type="string", nullable=true) */
    protected $user_key;
    /** @Column(type="integer", nullable=true) */
    protected $user_type;
    /** @Column(type="string", nullable=true) */
    protected $login_type;
    /** @Column(type="integer", nullable=true) */
    protected $remote_id;
    /** @Column(type="array", nullable=true) */
    protected $user_display_settings;
    /** @Column(type="array", nullable=true) */
    protected $permissions;
    /** @Column(type="string", nullable=true) */
    protected $google_auth_secret;

    /** @OneToMany(targetEntity="saUserLoginActivity", mappedBy="user", cascade={"remove"}) */
    protected $login_activity;
    /** @OneToMany(targetEntity="saUserDevice", mappedBy="user", cascade={"persist"}) */
    protected $devices;

    /** @OneToMany(targetEntity="SaUserLoginKey", mappedBy="sa_user", cascade={"all"})  */
    protected $login_keys;

    /** @OneToMany(targetEntity="saUserPushToken", mappedBy="user", cascade={"all"}) */
    protected $pushTokens;

    /** @Column(type="boolean", nullable=true) */
    protected $is_location_restricted;
    /** @Column(type="array", nullable=true) */
    protected $allowed_login_locations;

    protected $is_changing_password = false;
    protected $is_logging_in = false;

    /** @Column(type="string", nullable=true) */
    protected $password_encryption_type;

    /** 
     * @ManyToOne(targetEntity="saUserGroup", inversedBy="sa_users")
     * @JoinColumn(name="sausergroup_id", referencedColumnName="id")
     */
    protected $sa_user_group = null;

    const TYPE_STANDARD = 0;
    const TYPE_SUPER_USER = 1;
    const TYPE_DEVELOPER = 2;

    const TYPE_LOGIN_REMOTE = 'remote';
    const TYPE_LOGIN_LOCAL = 'local';

    /**
     * @PrePersist @PreUpdate
     */
    public function validate()
    {
        $fv = new fieldValidation();
        $fv->isNotEmpty($this->last_name, 'Please enter a last name.');
        $fv->isNotEmpty($this->first_name, 'Please enter a first name.');
        $fv->isNotEmpty($this->username, 'Please enter a username.');

        if (!$this->getId()) {
            $saUsers = ioc::staticResolve('saUser');
            $user = app::$entityManager->getRepository($saUsers)->findOneBy(array('username' => $this->username));
            if (is_object($user)) {
                $fv->adderror('That username is already in use. Please try another username.');
            }
            $this->setDateCreated( new DateTime() );
            $fv->isNotEmpty($this->password, 'Please enter a password.');
        }

        $sa_device_verify = app::get()->getConfiguration()->get('sa_device_verify')->getValue();
        $sa_device_verify_method = app::get()->getConfiguration()->get('sa_device_verify_method')->getValue();

        if ($sa_device_verify && $sa_device_verify_method=='SMS' && !$this->is_logging_in) {
            $fv->isNotEmpty($this->cell_number, 'A cell number is required for two factor authentication.');
        }

        $this->login_type = $this->login_type ? $this->login_type : static::TYPE_LOGIN_LOCAL;

        $this->user_type = $this->user_type ? $this->user_type : static::TYPE_STANDARD;

        if ($this->is_changing_password) {
            $charcounts = $fv->getPasswordLength();
            $fv->isStrongPassword($this->getPassword(true), 'Please enter a stronger password. A moderate password is required.  A strong password is recommended. A strong password contains at least '.$charcounts['minLength'].' characters, 1 upper and lower case letter and 1 number.');
        }

        if ($fv->hasErrors()) {
            throw new ValidateException(implode('<br />', $fv->getErrors()));
        }
    }

    public function hasPermission($permission) {
        $permissions = $this->getPermissions();

        if ($this->user_type==SELF::TYPE_SUPER_USER)
            return true;

        foreach($permissions as $module) {
            foreach($module as $permissionToTest=>$value) {
                if ($permissionToTest==$permission) {
                    return $value;
                }
            }
        }

        return false;
    }




    /**
     * @return boolean
     */
    public function isIsLoggingIn()
    {
        return $this->is_logging_in;
    }

    /**
     * @param boolean $is_logging_in
     * @return saUser
     */
    public function setIsLoggingIn($is_logging_in)
    {
        $this->is_logging_in = $is_logging_in;
        return $this;
    }



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->login_activity = new ArrayCollection();
        $this->devices = new ArrayCollection();
        $this->login_keys = new ArrayCollection();
        $this->pushTokens = new ArrayCollection();
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return saUser
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
     * @return saUser
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
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return saUser
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
     * @return saUser
     */
    public function setPassword($password)
    {
        if (empty($this->user_key)) {
            $this->user_key = md5(uniqid(time(), TRUE));
        }

        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->password_encryption_type = 'PASSWORD_BCRYPT';

        return $this;

//        $mcrypt = new mcrypt( $this->user_key );
//        $this->password = $mcrypt->encrypt( $password );
//
//        $this->is_changing_password = true;
//
//        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return saUser
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
     * @return saUser
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
     * @return saUser
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function toArray() {

        return get_object_vars($this);
    }

    /**
     * Set user_key
     *
     * @param string $userKey
     * @return saUser
     */
    public function setUserKey($userKey)
    {
        $this->user_key = $userKey;

        return $this;
    }

    /**
     * Get user_key
     *
     * @return string 
     */
    public function getUserKey()
    {
        return $this->user_key;
    }

    /**
     * Set userDisplaySettings
     *
     * @param null $key
     * @param array $userDisplaySettings
     * @return saUser
     */
    public function setUserDisplaySettings($key=null, $userDisplaySettings)
    {
        if ($key) {
            $this->user_display_settings[$key] = $userDisplaySettings;
        }
        else{
            $this->user_display_settings = $userDisplaySettings;
        }


        return $this;
    }

    /**
     * Get userDisplaySettings
     *
     * @param null $key
     * @return array
     */
    public function getUserDisplaySettings($key=null)
    {

        if ($key && isset( $this->user_display_settings[$key] ) ) {
            return $this->user_display_settings[$key];
        }
        elseif (is_array($this->user_display_settings) && !$key) {
            return $this->user_display_settings;
        }
        else{
            return array();
        }

    }

    /**
     * Set permissions
     *
     * @param array $permissions
     *
     * @return saUser
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
        if (!is_array($this->permissions))
            $this->permissions = array();

        return $this->permissions;
    }


    /**
     * Add loginActivity
     *
     * @param saUserLoginActivity $loginActivity
     *
     * @return saUser
     */
    public function addLoginActivity(saUserLoginActivity $loginActivity)
    {
        $this->login_activity[] = $loginActivity;

        return $this;
    }

    /**
     * Remove loginActivity
     *
     * @param saUserLoginActivity $loginActivity
     */
    public function removeLoginActivity(saUserLoginActivity $loginActivity)
    {
        $this->login_activity->removeElement($loginActivity);
    }

    /**
     * Get loginActivity
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLoginActivity()
    {
        return $this->login_activity;
    }


    /**
     * Set userType
     *
     * @param integer $userType
     *
     * @return saUser
     */
    public function setUserType($userType)
    {
        $this->user_type = $userType;

        return $this;
    }

    /**
     * Get userType
     *
     * @return integer
     */
    public function getUserType()
    {
        return $this->user_type;
    }

    /**
     * Set cellNumber
     *
     * @param string $cellNumber
     *
     * @return saUser
     */
    public function setCellNumber($cellNumber)
    {
        $this->cell_number = $cellNumber;

        return $this;
    }

    /**
     * Get cellNumber
     *
     * @return string
     */
    public function getCellNumber()
    {
        return $this->cell_number;
    }


    /**
     * Add device
     *
     * @param saUserDevice $device
     *
     * @return saUser
     */
    public function addDevice(saUserDevice $device)
    {
        $this->devices[] = $device;

        return $this;
    }

    /**
     * Remove device
     *
     * @param saUserDevice $device
     */
    public function removeDevice(saUserDevice $device)
    {
        $this->devices->removeElement($device);
    }

    /**
     * Get devices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * Set loginType
     *
     * @param string $loginType
     *
     * @return saUser
     */
    public function setLoginType($loginType)
    {
        $this->login_type = $loginType;

        return $this;
    }

    /**
     * Get loginType
     *
     * @return string
     */
    public function getLoginType()
    {
        return $this->login_type;
    }

    /**
     * Set remoteId
     *
     * @param integer $remoteId
     *
     * @return saUser
     */
    public function setRemoteId($remoteId)
    {
        $this->remote_id = $remoteId;

        return $this;
    }

    /**
     * Get remoteId
     *
     * @return integer
     */
    public function getRemoteId()
    {
        return $this->remote_id;
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
     * @return saUser $this
     */
    public function addLoginKey($login_keys)
    {
        $this->login_keys[] = $login_keys;
        return $this;
    }

    public function removeLoginKey(SaUserLoginKey $loginKey) {
        $this->login_keys->removeElement($loginKey);
    }

    public function addPushToken(saUserPushToken $pushToken)
    {
        $this->pushTokens[] = $pushToken;
        return $this;
    }

    public function removePushToken(saUserPushToken $pushToken) {
        $this->pushTokens->removeElement($pushToken);
    }

    public function getPushTokens() {
        return $this->pushTokens;
    }

    /**
     * @return mixed
     */
    public function getGoogleAuthSecret()
    {
        return $this->google_auth_secret;
    }

    public function reissueGoogleAuthSecret() {

        $google = new GoogleAuthenticator();
        $secretKey = $google->getSecretKey();
        $this->google_auth_secret = $secretKey;
        return $secretKey;
    }

    /**
     * @param mixed $google_auth_secret
     * @return saUser
     */
    public function setGoogleAuthSecret($google_auth_secret)
    {
        $this->google_auth_secret = $google_auth_secret;
        return $this;
    }

    public function getGoogleAuthQRCode() {

        $hostname = app::get()->getConfiguration()->get('site_url')->getValue();
        $hostname = preg_replace('/http:\/\/|http:\/\//', '', $hostname);

        $url = null;
        try {
            $google = new GoogleAuthenticator($this->getGoogleAuthSecret());
            $url = $google->getQRCodeUrl($hostname);
        }
        catch(GoogleAuthenticatorException $e) {


        }

        return $url;
    }

    /**
     * @return mixed
     */
    public function getisLocationRestricted()
    {
        return $this->is_location_restricted;
    }

    /**
     * @param mixed $is_location_restricted
     */
    public function setIsLocationRestricted($is_location_restricted)
    {
        $this->is_location_restricted = $is_location_restricted;
    }

    /**
     * @return mixed
     */
    public function getAllowedLoginLocations()
    {
        return $this->allowed_login_locations;
    }

    /**
     * @param mixed $allowed_login_locations
     */
    public function setAllowedLoginLocations($allowed_login_locations)
    {
        $this->allowed_login_locations = $allowed_login_locations;
    }

    /**
     * @return mixed
     */
    public function getPasswordEncryptionType()
    {
        return $this->password_encryption_type;
    }

    /**
     * @param mixed $password_encryption_type
     */
    public function setPasswordEncryptionType($password_encryption_type)
    {
        $this->password_encryption_type = $password_encryption_type;
    }
    
    /**
     * Get the value of sa_user_group
     */ 
    public function getSaUserGroup()
    {
        return $this->sa_user_group;
    }

    /**
     * Set the value of sa_user_group
     * @param mixed $sa_user_group
     * @return  self
     */ 
    public function setSaUserGroup($sa_user_group)
    {
        $this->sa_user_group = $sa_user_group;

        return $this;
    }

    public function hasGroupPermission($permission) {
        // Super Users always have permission
        if ($this->user_type==SELF::TYPE_DEVELOPER) {
            return true;
        }

        // Assuming saUser only has a single group...
        if ($this->sa_user_group) {
            // Check if the group has the requested permisison
           return $this->sa_user_group->hasPermission($permission);
        }

        return false;
    }
}
