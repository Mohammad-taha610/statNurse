<?php


namespace nst\member;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use sa\member\saMemberUsers;
use sacore\application\app;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\View;
use sacore\application\ValidateException;

/**
 * Class NstMemberUsers
 * @package nst\member
 * @Entity(repositoryClass="\sa\member\saMemberUsersRepository")
 * @HasLifecycleCallbacks
 * @IOC_NAME="saMemberUsers"
 */
class NstMemberUsers extends saMemberUsers
{

    /**
     * @var bool $bonus_allowed
     * @Column(type="boolean", nullable=true)
     */
    protected $bonus_allowed;

    /**
     * @var bool $covid_allowed
     * @Column(type="boolean", nullable=true)
     */
    protected $covid_allowed;

    /**
     * @var string $user_type
     * @Column(type="string")
     */
    protected $user_type;

    /**
     * @return string
     */
    public function getUserType()
    {
        return $this->user_type;
    }

    /**
     * @param string $user_type
     * @return NstMemberUsers
     */
    public function setUserType($user_type)
    {
        $this->user_type = $user_type;
        return $this;
    }

    /**
     * @return bool
     */
    public function getBonusAllowed()
    {
        return $this->bonus_allowed;
    }

    /**
     * @param bool $bonus_allowed
     * @return NstMemberUsers
     */
    public function setBonusAllowed($bonus_allowed)
    {
        $this->bonus_allowed = $bonus_allowed;
        return $this;
    }

    /**
     * @return bool
     */
    public function getCovidAllowed()
    {
        return $this->covid_allowed;
    }

    /**
     * @param bool $covid_allowed
     * @return NstMemberUsers
     */
    public function setCovidAllowed($covid_allowed)
    {
        $this->covid_allowed = $covid_allowed;
        return $this;
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
                $view->data['body'] = '<h1>Forgot Your Password!</h1><br />
                We received a request to reset your password. <br/><br /> 
    
                To reset your password, click on the link below (or copy and paste the URL into your browser): <br />
    
                <a href="' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/member/resetpasswordconfirm?k=' . $user->password_reset_key . '&i=' . $user->password_reset_key2 . '">' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/member/resetpasswordconfirm?k=' . $user->password_reset_key . '&i=' . $user->password_reset_key2 . '</a> <br /> <br />
    
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
}