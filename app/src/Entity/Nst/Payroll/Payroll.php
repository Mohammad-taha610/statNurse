<?php

namespace App\Entity\Nst\Payroll;

use App\Entity\Nst\Member\Nurse;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'payroll')]
#[Entity(repositoryClass: 'PayrollRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class Payroll
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[Column(type: 'date')]
    protected $start;

    #[Column(type: 'date')]
    protected $end;

    #[OneToMany(mappedBy: 'payroll', targetEntity: PayrollItem::class)]
    protected $payrollItems;

    #[ManyToOne(targetEntity: Nurse::class, inversedBy: 'payrolls')]
    protected $nurse;

    public function getUniqueDescriptor()
    {
        $nurseMember = $this->getMember();
        $firstName = $nurseMember->getFirstName();
        $lastName = $nurseMember->getLastName();
        $uniqueDescriptor = $firstName.' '.$lastName.' | '.$this->getStart()->format('m/d/Y').' - '.$this->getEnd()->format('m/d/Y');

        return $uniqueDescriptor;

    }

    public function getAmount()
    {
        $amount = 0;
        foreach ($this->payrollItems as $payrollItem) {
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
     * @param  \DateTime  $end
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
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
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
