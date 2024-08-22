<?php

namespace App\Repository\Nst\Payroll;

use App\Entity\Nst\Payroll\PayrollPayment;
use App\Repository\Nst\Shift\ShiftRecurrenceRepository;
use App\Repository\Nst\Shift\ShiftRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PayrollPaymentRepository extends ServiceEntityRepository
{
    private ShiftRepository $shiftRepository;
    private ShiftRecurrenceRepository $shiftRecurrenceRepository;

    public function __construct(
        ManagerRegistry           $registry,
        ShiftRepository           $shiftRepository,
        ShiftRecurrenceRepository $shiftRecurrenceRepository
    )
    {
        parent::__construct($registry, PayrollPayment::class);
        $this->shiftRepository = $shiftRepository;
        $this->shiftRecurrenceRepository = $shiftRecurrenceRepository;
    }

    public function getPaymentsBetweenDates(
        $providerId,
        $startDate,
        $endDate,
        $all = false,
        $paymentMethod = false,
        $paymentStatus = false,
        $nurseId = null,
        $status = null,
        $dateRange = null,
        $forInvoice = false
    ): array
    {
        $conditions = [
            'pp.is_deleted = false',
            'pp.is_deleted = 0',
            'pp.is_deleted IS NULL'
        ];

        // Shift query
        $q = $this->createQueryBuilder('pp')
            ->leftJoin('pp.shift', 's')
            ->leftJoin('s.provider', 'p')
            ->leftJoin('s.nurse', 'n')
            ->addSelect('pp, s, n')
            ->orderBy('n.first_name', 'ASC')
            ->addOrderBy('s.start', 'ASC');

        $q->andWhere($q->expr()->orX()->addMultiple($conditions));

        if ($providerId) {
            $q->andWhere('p.id = :providerId')
                ->setParameter(':providerId', $providerId);
        }

        if (!$all && !$dateRange) {
            $q->andWhere('s.start BETWEEN :start AND :end')
                ->setParameter(':start', $startDate)
                ->setParameter(':end', $endDate);
        }

        if ($paymentMethod) {
            $q->andWhere('pp.payment_method = :payment_method')
                ->setParameter(':payment_method', $paymentMethod);
        }

        if ($paymentStatus) {
            $q->andWhere('pp.payment_status = :payment_status')
                ->setParameter(':payment_status', $paymentStatus);
        }

        if ($nurseId) {
            $q->andWhere('n.id = :nurseId')
                ->setParameter(':nurseId', $nurseId);
        }

        if ($status) {
            if ($status == 'Unresolved') {
                $q->andWhere('pp.status IN (:statuses)')
                    ->setParameter(':statuses', ['Unresolved', 'Change Requested']);
            } else {
                $q->andWhere('pp.status = :status')
                    ->setParameter(':status', $status);
            }
        }

        if ($dateRange) {
            $q->andWhere('s.start BETWEEN :start AND :end')
                ->setParameter(':start', $dateRange[0])
                ->setParameter(':end', $dateRange[1]);
        }

        return $q->getQuery()->getResult();
    }

    public function getShiftForPayment(PayrollPayment $payrollPayment)
    {
        $shift = $payrollPayment->getShift();
        if (!$shift) {
            $shift = $this->shiftRepository->findOneBy(['payroll_payment' => $this]);
            if (!$shift) {
                $shift = $this->shiftRepository->findOneBy(['overtime_payment' => $this]);
            }
            if (!$shift) {
                if (!$payrollPayment->getShiftRecurrence()) {
                    $shift = $this->shiftRecurrenceRepository->findOneBy(['payroll_payment' => $this]);
                    if (!$shift) {
                        $shift = $this->shiftRecurrenceRepository->findOneBy(['overtime_payment' => $this]);
                    }
                }
            }
            return $shift;
        }
        return $shift;
    }
}
