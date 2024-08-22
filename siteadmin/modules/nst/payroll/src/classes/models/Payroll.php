<?php


namespace nst\payroll;

/**
 * @Entity(repositoryClass="PayrollRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="payroll")
 */
class Payroll{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="date") */
    protected $start;

    /** @Column(type="date")*/
    protected $end;

    /** @OneToMany(targetEntity="PayrollItems", mappedBy="payroll") */
    protected $payrollItems;

    /** @ManyToOne(targetEntity="\nst\member\Nurse", inversedBy="payrolls") */
    protected $nurse;

    public function getUniqueDescriptor(){
        $nurseMember = $this->getMember();
        $firstName = $nurseMember->getFirstName();
        $lastName = $nurseMember->getLastName();
        $uniqueDescriptor = $firstName . ' ' . $lastName . ' | ' . $this->getStart()->format('m/d/Y') . ' - ' . $this->getEnd()->format('m/d/Y');
        return $uniqueDescriptor;

    }

    public function getAmount(){
        $amount = 0;
        foreach($this->payrollItems as $payrollItem){
            $amount += $payrollItem->getAmount();
        }
        return $amount;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payrollItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set start.
     *
     * @param $start
     * @return Payroll
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end.
     *
     * @param \DateTime $end
     *
     * @return payroll
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Add payrollItem.
     *
     * @param \nst\payroll\PayrollItem $payrollItem
     *
     * @return Payroll
     */
    public function addPayrollItem(\nst\payroll\PayrollItem $payrollItem)
    {
        $this->payrollItems[] = $payrollItem;

        return $this;
    }

    /**
     * Remove payrollItem.
     *
     * @param \nst\payroll\PayrollItem $payrollItem
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePayrollItem(\nst\payroll\PayrollItem $payrollItem)
    {
        return $this->payrollItems->removeElement($payrollItem);
    }

    /**
     * Get payrollItems.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayrollItems()
    {
        return $this->payrollItems;
    }

    /**
     * Set nurse.
     *
     * @param \nst\member\Nurse|null $nurse
     *
     * @return Payroll
     */
    public function setNurse(\nst\member\Nurse $nurse = null)
    {
        $this->nurse = $nurse;

        return $this;
    }

    /**
     * Get nurse.
     *
     * @return \nst\member\Nurse|null
     */
    public function getNurse()
    {
        return $this->nurse;
    }

}