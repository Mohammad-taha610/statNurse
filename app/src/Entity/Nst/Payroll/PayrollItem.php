<?php

namespace App\Entity\Nst\Payroll;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'payroll_item')]
#[Entity(repositoryClass: 'PayrollItemRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class PayrollItem
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string')]
    protected $type;

    #[Column(type: 'string')]
    protected $description;

    #[Column(type: 'decimal')]
    protected $amount;

    #[Column(type: 'boolean')]
    protected $approved;

    #[Column(type: 'boolean')]
    protected $bonus;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type.
     *
     * @param  string  $type
     * @return PayrollItem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set description.
     *
     * @param  string  $description
     * @return PayrollItem
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set amount.
     *
     * @param  string  $amount
     * @return PayrollItem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set approved.
     *
     * @param  bool  $approved
     * @return PayrollItem
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get approved.
     *
     * @return bool
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Set bonus.
     *
     * @param  bool  $bonus
     * @return PayrollItem
     */
    public function setBonus($bonus)
    {
        $this->bonus = $bonus;

        return $this;
    }

    /**
     * Get bonus.
     *
     * @return bool
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * Set payroll.
     *
     *
     * @return PayrollItem
     */
    public function setPayroll(\nst\payroll\Payroll $payroll = null)
    {
        $this->payroll = $payroll;

        return $this;
    }

    /**
     * Get payroll.
     *
     * @return \nst\payroll\Payroll|null
     */
    public function getPayroll()
    {
        return $this->payroll;
    }
}
