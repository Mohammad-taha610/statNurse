<?php

namespace App\Entity\Sax\Messages;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;
use PHPMailer\PHPMailer\PHPMailer;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Thread;

#[Table(name: 'sa_email')]
#[Index(name: 'IDX_email_attempted_send', columns: ['attempted_send'])]
#[Entity(repositoryClass: 'saEmailRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
class saEmail
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    public $id;

    #[Column(type: 'string', nullable: true)]
    public $to_address;

    #[Column(type: 'string', nullable: true)]
    public $cc_address;

    #[Column(type: 'string', nullable: true)]
    public $bcc_address;

    #[Column(type: 'boolean', nullable: true)]
    public $attempted_send;

    #[Column(type: 'boolean', nullable: true)]
    public $success;

    #[Column(type: 'text', nullable: true, length: 16777220)]
    public $response;

    #[Column(type: 'text', nullable: true, length: 16777220)]
    public $body;

    #[Column(type: 'string', nullable: true)]
    public $subject;

    #[Column(type: 'string', nullable: true)]
    public $reply_to;

    #[Column(type: 'string', nullable: true)]
    public $from_name;

    #[Column(type: 'datetime', nullable: true)]
    public $date_created;

    #[Column(type: 'datetime', nullable: true)]
    public $date_attempted_send;

    #[Column(type: 'array', nullable: true)]
    public $attachments;

    #[Column(type: 'integer', nullable: true)]
    public $batch_id;

    #[Column(type: 'array', nullable: true)]
    public $other;

    #[Column(type: 'boolean', nullable: true)]
    protected $central_acknowledged;

    public static $_batch = false;

    public static $_batch_id = null;

    public $_useTemplate = true;

    public $_theme = true;

    public static function createSend($data)
    {
        $files = [];

        if (is_array($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                if (! empty($attachment['contents'])) {
                    $temp_filename = app::get()->getConfiguration()->get('tempDir')->getValue().'/'.time().rand(0, 99999).$attachment['name'];
                    file_put_contents($temp_filename, $attachment['contents']);
                    $files[] = ['name' => $attachment['name'], 'path' => $temp_filename];
                }
            }
        }

        //			$all_to_addresses = explode(';',$data['to']);
        //			foreach($all_to_addresses as $to)
        //			{
        //
        //			}
        /** @var saEmail $email */
        $email = ioc::get('saEmail');
        $email->setToAddress($data['to']);
        $email->setCcAddress($data['cc']);
        $email->setBccAddress($data['bcc']);
        $email->setBody($data['body']);
        $email->setSubject($data['subject']);
        $email->setReplyTo($data['reply_to']);
        $email->setFromName($data['from_name']);
        $email->setDateCreated(new \sacore\application\DateTime());
        $email->setAttemptedSend(false);
        $email->setAttachments($files);
        $email->setBatchId(static::$_batch_id);
        $email->setOther($data['other'] ? $data['other'] : null);

        $email->send($data['keep_attachments']);

        return $email;
    }

    public static function startBatch()
    {
        static::$_batch = true;
        static::$_batch_id = rand(0, 999999999);
    }

    public static function commitBatch()
    {
        static::$_batch = false;
        $thread = new Thread('executeController', 'messagesController', 'messagesCron', ['batch_id' => static::$_batch_id]);
        $thread->run();
        static::$_batch_id = null;
    }

    /**
     * Save the email
     */
    public function save()
    {
        app::$entityManager->persist($this);
        app::$entityManager->flush();
    }

    /**
     * Save the email and then fire off the cron to send the email
     */
    public function send()
    {
        $this->save();
        if (! static::$_batch) {
            $thread = new Thread('executeController', 'messagesController', 'messagesCron');
            $thread->run();
        }
    }

    public function setTheme($theme)
    {
        $this->_theme = $theme;
    }

    /**
     * Send the email now without using the cron
     */
    public function sendNow($keep_attachments = false)
    {

        ob_flush();
        ob_start();
        $mail = new PHPMailer();

        $mail->SMTPDebug = true;
        $mail->IsSMTP();
        $mail->From = app::get()->getConfiguration()->get('smtp_from')->getValue();
        $fromName = $this->getFromName();
        if (! empty($fromName)) {
            $mail->FromName = $fromName;
        } else {
            $mail->FromName = app::get()->getConfiguration()->get('smtp_fromName')->getValue();
        }

        $mail->Host = app::get()->getConfiguration()->get('smtp_host')->getValue();
        $mail->SMTPAuth = app::get()->getConfiguration()->get('smtp_smtp_auth')->getValue();
        $mail->Username = app::get()->getConfiguration()->get('smtp_username')->getValue();
        $mail->Password = app::get()->getConfiguration()->get('smtp_password')->getValue();
        $mail->AuthType = app::get()->getConfiguration()->get('smtp_auth_type')->getValue();
        $mail->CharSet = 'UTF-8';

        $ssl = '';
        if (app::get()->getConfiguration()->get('smtp_secure')) {
            $ssl = app::get()->getConfiguration()->get('smtp_secure')->getValue();
        }

        $port = '25';
        if (app::get()->getConfiguration()->get('smtp_port')) {
            $port = app::get()->getConfiguration()->get('smtp_port')->getValue();
        }

        $mail->SMTPSecure = $ssl;
        $mail->Port = $port;

        $mail->SMTPOptions = [];

        if (! app::get()->getConfiguration()->get('smtp_verify_ssl')->getValue()) {
            $mail->SMTPOptions['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ];
        }

        if (app::get()->getConfiguration()->get('smtp_bind_ip')->getValue()) {
            $mail->SMTPOptions['socket'] = [
                'bindto' => app::get()->getConfiguration()->get('smtp_bind_ip')->getValue().':0',
            ];
        }

        $attachments = $this->getAttachments();
        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment['path'], $attachment['name']);
            }
        }

        if (app::get()->getConfiguration()->get('enable_mail_catcher')->getValue()) {
            $mail->AddAddress(trim(app::get()->getConfiguration()->get('mail_catcher_address')->getValue()));
        } else {

            // To addresses can be separated by semicolons
            $to_array = explode(';', $this->getToAddress());
            foreach ($to_array as $to) {
                $mail->AddAddress(trim($to));
            }

            $bcc_array = explode(';', $this->getBccAddress());
            foreach ($bcc_array as $bcc) {
                if ($bcc) {
                    $mail->addBCC($bcc);
                }
            }

            $cc_array = explode(';', $this->getCcAddress());
            foreach ($cc_array as $cc) {
                if ($cc) {
                    $mail->addCC($cc);
                }
            }
        }

        $mail->Subject = $this->getSubject();

        $replyTo = trim($this->getReplyTo());
        if (! empty($replyTo)) {
            $mail->addReplyTo($this->getReplyTo());
        }

        $mail->Body = $this->getBody();
        $mail->AltBody = strip_tags($this->getBody());
        $success = ($mail->Send() ? '1' : '0');
        $mail->ClearAddresses();
        $debugInfo = ob_get_clean();

        if (! empty($mail->isError())) {
            $success = '0';
            $debugInfo .= PHP_EOL.$mail->ErrorInfo;
        }

        $this->setAttemptedSend(true);
        $this->setSuccess($success);
        $this->setResponse($debugInfo);
        $this->setDateAttemptedSend(new \sacore\application\DateTime());

        app::$entityManager->persist($this);
        app::$entityManager->flush();

        if (! $keep_attachments) {
            if (is_array($attachments)) {
                foreach ($this->getAttachments() as $attachment) {
                    @unlink($attachment['path']);
                }
            }
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
     * Set attempted_send
     *
     * @param  bool  $attemptedSend
     * @return saEmail
     */
    public function setAttemptedSend($attemptedSend)
    {
        $this->attempted_send = $attemptedSend;

        return $this;
    }

    /**
     * Get attempted_send
     *
     * @return bool
     */
    public function getAttemptedSend()
    {
        return $this->attempted_send;
    }

    /**
     * Set success
     *
     * @param  bool  $success
     * @return saEmail
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Get success
     *
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Set response
     *
     * @param  string  $response
     * @return saEmail
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set body
     *
     * @param  string  $body
     * @return saEmail
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set subject
     *
     * @param  string  $subject
     * @return saEmail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set date_created
     *
     * @param  \sacore\application\DateTime  $dateCreated
     * @return saEmail
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
     * Set date_attempted_send
     *
     * @param  \sacore\application\DateTime  $dateAttemptedSend
     * @return saEmail
     */
    public function setDateAttemptedSend($dateAttemptedSend)
    {
        $this->date_attempted_send = $dateAttemptedSend;

        return $this;
    }

    /**
     * Get date_attempted_send
     *
     * @return \sacore\application\DateTime
     */
    public function getDateAttemptedSend()
    {
        return $this->date_attempted_send;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    public function getProperties()
    {

    }

    /**
     * Set to_address
     *
     * @param  string  $toAddress
     * @return saEmail
     */
    public function setToAddress($toAddress)
    {
        $this->to_address = $toAddress;

        return $this;
    }

    /**
     * Get to_address
     *
     * @return string
     */
    public function getToAddress()
    {
        return $this->to_address;
    }

    /**
     * Set cc_address
     *
     * @param  string  $ccAddress
     * @return saEmail
     */
    public function setCcAddress($ccAddress)
    {
        $this->cc_address = $ccAddress;

        return $this;
    }

    /**
     * Get cc_address
     *
     * @return string
     */
    public function getCcAddress()
    {
        return $this->cc_address;
    }

    /**
     * Set bcc_address
     *
     * @param  string  $bccAddress
     * @return saEmail
     */
    public function setBccAddress($bccAddress)
    {
        $this->bcc_address = $bccAddress;

        return $this;
    }

    /**
     * Get bcc_address
     *
     * @return string
     */
    public function getBccAddress()
    {
        return $this->bcc_address;
    }

    /**
     * Set attachments
     *
     * @param  array  $attachments
     * @return saEmail
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set batchId
     *
     * @param  int  $batchId
     * @return saEmail
     */
    public function setBatchId($batchId)
    {
        $this->batch_id = $batchId;

        return $this;
    }

    /**
     * Get batchId
     *
     * @return int
     */
    public function getBatchId()
    {
        return $this->batch_id;
    }

    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->reply_to;
    }

    /**
     * @param  mixed  $reply_to
     */
    public function setReplyTo($reply_to)
    {
        $this->reply_to = $reply_to;
    }

    /**
     * @return mixed
     */
    public function getFromName()
    {
        return $this->from_name;
    }

    /**
     * @param  mixed  $from_name
     */
    public function setFromName($from_name)
    {
        $this->from_name = $from_name;
    }

    /**
     * @return mixed
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * @param  mixed  $other
     */
    public function setOther($other)
    {
        $this->other = $other;
    }

    /**
     * @return mixed
     */
    public function getCentralAcknowledged()
    {
        return $this->central_acknowledged;
    }

    /**
     * @param  mixed  $central_acknowledged
     */
    public function setCentralAcknowledged($central_acknowledged)
    {
        $this->central_acknowledged = $central_acknowledged;
    }
}
