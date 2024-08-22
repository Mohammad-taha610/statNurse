<?php

namespace sa\messages;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="pushNotificationRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="sa_push_notifications")
 */
class PushNotification {

    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;
    /** @Column(type="text") */
    protected $token;
    /** @Column(type="string", nullable=true) */
    protected $topic;
    /** @Column(type="string") */
    protected $title;
    /** @Column(type="string") */
    protected $message;
    /** @Column(type="text") */
    protected $response;
    /** @Column(type="datetime") */
    protected $date_created;
    /** @Column(type="boolean", nullable=true) */
    protected $attempted_send;
    /** @Column(type="datetime", nullable=true) */
    protected $date_attempted_send;
    /** @Column(type="boolean", nullable=true) */
    protected $success;
    /** @Column(type="integer", nullable=true) */
    protected $batch_id;
    /** @Column(type="array", nullable=true) */
    protected $payload_data;
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param mixed $topic
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param mixed $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * @return mixed
     */
    public function getDateAttemptedSend()
    {
        return $this->date_attempted_send;
    }

    /**
     * @param mixed $date_attempted_send
     */
    public function setDateAttemptedSend($date_attempted_send)
    {
        $this->date_attempted_send = $date_attempted_send;
    }
    
    /**
     * @return mixed
     */
    public function getAttemptedSend()
    {
        return $this->attempted_send;
    }
    
    /**
     * @param mixed $attempted_send
     */
    public function setAttemptedSend($attempted_send)
    {
        $this->attempted_send = $attempted_send;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param mixed $success
     */
    public function setSuccess($success)
    {
        $this->success = $success;
    }

    /**
     * @return mixed
     */
    public function getBatchId()
    {
        return $this->batch_id;
    }

    /**
     * @param mixed $batch_id
     */
    public function setBatchId($batch_id)
    {
        $this->batch_id = $batch_id;
    }

    /**
     * @return mixed
     */
    public function getPayloadData()
    {
        return $this->payload_data;
    }

    /**
     * @param mixed $payload_data
     */
    public function setPayloadData($payload_data)
    {
        $this->payload_data = $payload_data;
    }
}
