<?php

namespace sa\messages;

use eye4tech\worm\wormModel;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modelResult;
use sacore\application\Thread;
use sacore\utilities\fieldValidation;

/**
 * @Entity(repositoryClass="saSMSRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="sa_sms")
 */
class saSMS {

    /** @Id @Column(type="integer") @GeneratedValue */
    public $id;
    /** @Column(type="string", nullable=true) */
    public $to_address;
    /** @Column(type="boolean", nullable=true) */
    public $attempted_send;
    /** @Column(type="boolean", nullable=true) */
    public $success;
    /** @Column(type="string", nullable=true) */
    public $status;
    /** @Column(type="string", nullable=true, length=8000) */
    public $body;
    /** @Column(type="datetime", nullable=true) */
    public $date_created;
    /** @Column(type="datetime", nullable=true) */
    public $date_attempted_send;
    /** @Column(type="string", nullable=true) */
    public $sid;
    /** @Column(type="integer", nullable=true) */
    public $batch_id;

    public static $_batch = false;
    public static $_batch_id = null;

    public static function createSend($data)
    {
        file_put_contents(
            app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'saSms.log',
            'Creating Send'. PHP_EOL, FILE_APPEND
        );
        $sms = ioc::get('saSMS');
        $sms->setToAddress( preg_replace('/[^0-9]/', '', $data['phone']) );
        $sms->setBody($data['body']);
        $sms->setDateCreated( new \sacore\application\DateTime() );
        $sms->setAttemptedSend(false);
        $sms->setBatchId(static::$_batch_id);
        $sms->send();
        
        return $sms;
    }

    public static function startBatch() {
        file_put_contents(
            app::get()->getConfiguration()->get('tempDir') . DIRECTORY_SEPARATOR . 'saSms.log',
            'Starting Batch for SMS'. PHP_EOL, FILE_APPEND
        );
        static::$_batch = true;
        static::$_batch_id = rand(0, 999999999);
    }

    public static function commitBatch() {
        static::$_batch = false;
        $thread = new Thread('executeController', 'messagesController', 'messagesCron', array('batch_id'=>static::$_batch_id));
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
        if (!static::$_batch) {
            $thread = new Thread('executeController', 'messagesController', 'messagesCron');
            $thread->run();
        }
    }


    /**
     * Send the email now without using the cron
     */
    public function sendNow()
    {
        file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'saSms.log',
            'Attempting to send message' . PHP_EOL, FILE_APPEND);
        $client = new \Twilio\Rest\Client(app::get()->getConfiguration()->get('twilio_sid')->getValue(), app::get()->getConfiguration()->get('twilio_token')->getValue());
        try {
            $message = $client->messages->create(
                trim($this->getToAddress()),
                array(
                    'from' => app::get()->getConfiguration()->get('twilio_phonenumber')->getValue(),
                    'body' => $this->body
                )
            );

            $this->setAttemptedSend(true);
            $this->setSid($message->sid);
            $this->setStatus($message->status);
            $this->setDateAttemptedSend( new \sacore\application\DateTime() );

            app::$entityManager->persist($this);
            app::$entityManager->flush();
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'saSms.log',
                'Success sending message: ' . $this->id . PHP_EOL, FILE_APPEND);
        }
        catch (TwilioException $te) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'saSms.log',
                'TE Failed sending message: ' . $te->getMessage() . PHP_EOL, FILE_APPEND);
        }
        catch (\Exception $e) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'saSms.log',
                'E Failed sending message: ' . $e->getMessage() . PHP_EOL, FILE_APPEND); 
        }
    }

    public function checkStatus()
    {
        $client = new \Twilio\Rest\Client(app::get()->getConfiguration()->get('twilio_sid')->getValue(), app::get()->getConfiguration()->get('twilio_token')->getValue());
        $message = $client->account->messages($this->getSid())->fetch();

        $this->setStatus($message->status);
        app::$entityManager->flush();
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
     * Set attempted_send
     *
     * @param boolean $attemptedSend
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
     * @return boolean
     */
    public function getAttemptedSend()
    {
        return $this->attempted_send;
    }

    /**
     * Set success
     *
     * @param boolean $success
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
     * @return boolean
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Set status
     *
     * @param string $status
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
     * @param string $body
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
     * @param \sacore\application\DateTime $dateCreated
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
     * @param \sacore\application\DateTime $dateAttemptedSend
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
     * @param string $sid
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
     * @param string $toAddress
     * @return saSMS
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
     * Set batchId
     *
     * @param integer $batchId
     *
     * @return saSMS
     */
    public function setBatchId($batchId)
    {
        $this->batch_id = $batchId;

        return $this;
    }

    /**
     * Get batchId
     *
     * @return integer
     */
    public function getBatchId()
    {
        return $this->batch_id;
    }
}
