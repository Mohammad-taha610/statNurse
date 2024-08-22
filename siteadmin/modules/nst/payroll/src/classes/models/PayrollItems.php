<?php


namespace nst\payroll;

/**
 * @Entity(repositoryClass="PayrollItemsRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="payroll_items")
 */
class PayrollItems{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $type;

    /** @Column(type="string") */
    protected $description;

    /** @Column(type="decimal") */
    protected $amount;

    /** @Column(type="boolean") */
    protected $approved;

    /** @Column(type="string") */
    protected $status;

    /** @Column(type="boolean") */
    protected $bonus;

    /** @ManyToOne(targetEntity="Payroll", inversedBy="payrollItems") */
    protected $payroll;

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
     * @param string $type
     *
     * @return PayrollItems
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
     * @param string $description
     *
     * @return PayrollItems
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
     * @param string $amount
     *
     * @return PayrollItems
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
     * @param bool $approved
     *
     * @return PayrollItems
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
     * Set status.
     *
     * @param string $status
     *
     * @return PayrollItems
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set bonus.
     *
     * @param bool $bonus
     *
     * @return PayrollItems
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
     * @param \nst\payroll\Payroll|null $payroll
     *
     * @return PayrollItems
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