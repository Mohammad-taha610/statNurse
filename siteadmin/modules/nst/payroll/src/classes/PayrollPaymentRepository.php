<?php


namespace nst\payroll;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use nst\system\NstDefaultRepository;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\utilities\doctrineUtils;

class PayrollPaymentRepository extends NstDefaultRepository
{

    public function getPaymentsBetweenDates($providerId, $startDate, $endDate, $all = false, $paymentMethod = false, $paymentStatus = false, $nurseId = null, $status = null, $dateRange = null, $forInvoice = false) {
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

        if($providerId) {
            $q->andWhere('p.id = :providerId')
                ->setParameter(':providerId', $providerId);
        }

        if (!$all && !$dateRange) {
            $q->andWhere('s.start BETWEEN :start AND :end')
                ->setParameter(':start', $startDate)
                ->setParameter(':end', $endDate);
        }

        if($paymentMethod) {
            $q->andWhere('pp.payment_method = :payment_method')
                ->setParameter(':payment_method', $paymentMethod);
        }

        if($paymentStatus) {
            $q->andWhere('pp.payment_status = :payment_status')
                ->setParameter(':payment_status', $paymentStatus);
        }

        if($nurseId) {
            $q->andWhere('n.id = :nurseId')
                ->setParameter(':nurseId', $nurseId);
        }

        if($status) {
            if ($status == 'Unresolved') {
                $q->andWhere('pp.status IN (:statuses)')
                ->setParameter(':statuses', ['Unresolved', 'Change Requested']);
            } else {
                $q->andWhere('pp.status = :status')
                    ->setParameter(':status', $status);
            }
        }

        if($dateRange) {
            $q->andWhere('s.start BETWEEN :start AND :end')
                ->setParameter(':start', $dateRange[0])
                ->setParameter(':end', $dateRange[1]);
        }

        $result = [];
        $result['shifts'] = $q->getQuery()->getResult();
        $result['payments'] = $result['shifts'];

        return $result;
    }

    public function getNurseYTDHoursAndTotal($nurseId, $endDate) {
        $now = new DateTime('now', app::get()->getTimeZone());
        $today = new DateTime($now->format('Y-m-d') . ' 23:59:59', app::get()->getTimeZone());
        $yearStart = new DateTime(($endDate ? $endDate->format('Y') : $today->format('Y')) . '-01-01 00:00:00', app::get()->getTimeZone());

        $q = $this->createQueryBuilder('pp')
            ->leftJoin('pp.shift', 's')
            ->leftJoin('s.nurse', 'n')
            ->select('pp.pay_total')
            ->addSelect('pp.clocked_hours')
            ->where('n.id = :nurseId')
            ->andWhere('s.start BETWEEN :start AND :end')
            ->setParameter('start', $yearStart)
            ->setParameter('end', $endDate ?? $today)
            ->setParameter('nurseId', $nurseId);

        $total = 0;
        $hours = 0;
        foreach($q->getQuery()->getArrayResult() as $payment) {
            $total += $payment['pay_total'];
            $hours += $payment['clocked_hours'];
        }
        return [
            'total' => $total,
            'hours' => $hours
        ];
    }

    public function getPayments($providerId, $status = null, $dateRange = null) {
        $conditions = [
            'pp.is_deleted = false',
            'pp.is_deleted = 0',
            'pp.is_deleted IS NULL'
        ];

        $q = $this->createQueryBuilder('pp')
            ->leftJoin('pp.shift', 's')
            ->leftJoin('s.provider', 'p')
            ->select('pp')
            ->addSelect('s');
    
        $q->andWhere($q->expr()->orX()->addMultiple($conditions));

        if($providerId) {
            $q->andWhere('p.id = :providerId')
            ->setParameter(':providerId', $providerId);
        }

        if($status) {
            $q->andWhere('pp.status = :status')
                ->setParameter(':status', $status);
        }
        
        if($dateRange) {
            $q->andWhere('s.start BETWEEN :start AND :end')
                ->setParameter(':start', $dateRange[0])
                ->setParameter(':end', $dateRange[1]);
        }

        $result = [];
        $result['shifts'] = $q->getQuery()->getResult();

        return $result;
    }

    public function getPaymentsForNurseInPeriod($nurse, $startDate, $endDate, $all = false) {
        $conditions = [
            'pp.is_deleted = false',
            'pp.is_deleted = 0',
            'pp.is_deleted IS NULL'
        ];

        $q = $this->createQueryBuilder('pp')
            ->leftJoin('pp.shift', 's')
            ->leftJoin('s.nurse', 'n')
            ->select('pp')
            ->addSelect('s');
        $q->andWhere($q->expr()->orX()->addMultiple($conditions));
        if(!$all) {
            $q->andWhere('s.start BETWEEN :startDate AND :endDate')
                ->setParameter(':startDate', $startDate)
                ->setParameter(':endDate', $endDate);
        }
        if($nurse) {
            $q->andWhere('n.id = :nurseId')
                ->setParameter(':nurseId', $nurse->getId());
        }

        $result = [];
        $result['shifts'] = $q->getQuery()->getResult();
        $result['payments'] = $q->getQuery()->getResult();

        $q = $this->createQueryBuilder('pp')
            ->leftJoin('pp.shift_recurrence', 's')
            ->leftJoin('s.nurse', 'n')
            ->select('pp')
            ->addSelect('s');
        $q->andWhere($q->expr()->orX()->addMultiple($conditions));
        if(!$all) {
            $q->andWhere('s.start BETWEEN :startDate AND :endDate')
                ->setParameter(':startDate', $startDate)
                ->setParameter(':endDate', $endDate);
        }
        if($nurse) {
            $q->andWhere('n.id = :nurseId')
                ->setParameter(':nurseId', $nurse->getId());
        }

        $result['shift_recurrences'] = $q->getQuery()->getResult();
        $result['payments'] = array_merge($result['payments'], $q->getQuery()->getResult());
        return $result;
    }
}
