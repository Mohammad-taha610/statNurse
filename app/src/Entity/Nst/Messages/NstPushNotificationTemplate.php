<?php

namespace App\Entity\Nst\Messages;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'NstPushNotificationTemplate')]
#[Entity(repositoryClass: 'NstPushNotificationTemplateRepository')]
#[InheritanceType('SINGLE_TABLE')]
class NstPushNotificationTemplate
{
    /**
     * @var int $id
     */
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    /**
     * @var string $name
     */
    #[Column(type: 'string', nullable: true)]
    protected $name;

    /**
     * @var string $title
     */
    #[Column(type: 'string', nullable: true)]
    protected $title;

    /**
     * @var string $message
     */
    #[Column(type: 'string', nullable: true)]
    protected $message;

    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int  $id
     * @return NstPushNotificationTemplate
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param  string  $title
     * @return NstPushNotificationTemplate
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param  string  $message
     * @return NstPushNotificationTemplate
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     * @return NstPushNotificationTemplate
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
