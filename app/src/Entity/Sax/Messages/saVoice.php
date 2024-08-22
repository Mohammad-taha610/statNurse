<?php

namespace App\Entity\Sax\Messages;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Thread;

#[Table(name: 'sa_voice')]
#[Entity(repositoryClass: 'saVoiceRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
class saVoice
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    public $id;

    #[Column(type: 'string', nullable: true)]
    public $to_address;

    #[Column(type: 'boolean', nullable: true)]
    public $attempted_send;

    #[Column(type: 'boolean', nullable: true)]
    public $success;

    #[Column(type: 'string', nullable: true)]
    public $status;

    #[Column(type: 'string', nullable: true, length: 8000)]
    public $body;

    #[Column(type: 'datetime', nullable: true)]
    public $date_created;

    #[Column(type: 'datetime', nullable: true)]
    public $date_attempted_send;

    #[Column(type: 'string', nullable: true)]
    public $sid;

    #[Column(type: 'integer', nullable: true)]
    public $batch_id;

    public static function createSend($data)
    {
        $voice = ioc::get('saVoice');
        $voice->setToAddress(preg_replace('/[^0-9]/', '', $data['phone']));
        $voice->setBody($data['body']);
        $voice->setDateCreated(new \sacore\application\DateTime());
        $voice->setAttemptedSend(false);
        $voice->send();

        return $voice;
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
        $thread = new Thread('executeController', 'messagesController', 'messagesCron');
        $thread->run();
    }

    public function getVoiceText()
    {
        preg_match_all('/{[^}]*}/', $this->body, $matches);
        $pieces = preg_split('/{[^}]*}/', $this->body);

        $xml = '<Response>';

        foreach ($pieces as $k => $p) {
            if (! empty($p)) {
                $xml .= '<Say>'.trim($p).'</Say>';
            }

            if (! empty($matches[0][$k])) {
                $command = $matches[0][$k];
                $command = str_replace(['{', '}'], '', $command);
                $command = explode('|', $command);

                switch ($command[0]) {
                    case 'p':
                        $xml .= '<Pause length="'.$command[1].'"/>';
                        break;
                    case 'spell':
                        $xml .= '<Say>'.trim(chunk_split($command[1], 1, ' ')).'</Say>';
                        break;
                    default:
                }
            }
        }
        $xml .= '</Response>';

        return $xml;
    }

    /**
     * Send the email now without using the cron
     */
    public function sendNow()
    {
        $client = new \Services_Twilio(app::get()->getConfiguration()->get('twilio_sid')->getValue(), app::get()->getConfiguration()->get('twilio_token')->getValue());
        $call = $client->account->calls->create(
            app::get()->getConfiguration()->get('twilio_phonenumber')->getValue(), // From a valid Twilio number
            $this->getToAddress(), // Call this number
            (app::get()->getConfiguration()->get('require_ssl')->getValue() ? app::get()->getConfiguration()->get('site_url_secure')->getValue() : app::get()->getConfiguration()->get('site_url')->getValue()).'/sysadmin/messages/voicetext?i='.$this->id.'&t='.$this->getToAddress()
        );

        $this->setAttemptedSend(true);
        $this->setSid($call->sid);
        $this->setStatus($call->status);
        $this->setDateAttemptedSend(new \sacore\application\DateTime());

        app::$entityManager->persist($this);
        app::$entityManager->flush();
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
     * @return saVoice
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
     * @return saVoice
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
     * Set status
     *
     * @param  string  $status
     * @return saVoice
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set body
     *
     * @param  string  $body
     * @return saVoice
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
     * Set date_created
     *
     * @param  \sacore\application\DateTime  $dateCreated
     * @return saVoice
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
     * @return saVoice
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

    /**
     * Set sid
     *
     * @param  string  $sid
     * @return saVoice
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid
     *
     * @return string
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set to_address
     *
     * @param  string  $toAddress
     * @return saVoice
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
}
