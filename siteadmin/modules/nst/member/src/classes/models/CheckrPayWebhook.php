<?php

namespace nst\member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;
use sacore\application\DateTime;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @Entity
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @Table(name="checkr_pay_webhooks")
 */
class CheckrPayWebhook
{

	/** @Id @Column(type="integer") @GeneratedValue */
	public $id;

	/** @Column(type="string", nullable=false) */
	public $webhook_id;

	/** @Column(type="json", nullable=false) */
	public $data;

	/** @Column(type="string", nullable=false) */
	public $status;

	/** @Column(type="datetime", nullable=false) */
	public $created_at;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getWebhookId()
	{
		return $this->webhook_id;
	}

	/**
	 * @param string $webhook_id
	 * @return CheckrPayWebhook;
	 */
	public function setWebhookId($webhook_id)
	{
		$this->webhook_id = $webhook_id;
		return $this;
	}

	/**
	 * @return json
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param json $data
	 * @return CheckrPayWebhook;
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

		/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 * @return CheckrPayWebhook;
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateCreated()
	{
		return $this->created_at;
	}

	/**
	 * @param  datetime  $created_at
	 * @return datetime
	 */
	public function setDateCreated($created_at)
	{
		$this->created_at = $created_at;
		return $this;
	}
}
