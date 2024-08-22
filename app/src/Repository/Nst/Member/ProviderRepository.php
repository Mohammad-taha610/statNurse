<?php

namespace App\Repository\Nst\Member;

use App\Entity\Nst\Member\Provider;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Provider::class);
    }

    public function getBlockedNursesForProviders(
        array $providers
    ) {
        $q = $this->createQueryBuilder('p');
        $q->leftJoin('p.blocked_nurses', 'bn')
            ->select('p')
            ->addSelect('bn')
            ->where('p IN (:providers)')
            ->andWhere('p.is_deleted = false')
            ->setParameter('providers', $providers);

        return $q->getQuery()->getResult();
    }

    public function getPreviousNursesForProviders(
        array $providers,
    ) {
        $q = $this->createQueryBuilder('p');
        $q->leftJoin('p.previous_nurses', 'pn')
            ->select('p')
            ->addSelect('pn')
            ->where('p IN (:providers)')
            ->andWhere('p.is_deleted = false')
            ->setParameter('providers', $providers);

        return $q->getQuery()->getResult();
    }

    public function getUnclaimedShiftsCount($providerId) {
        $now = new Datetime('now');
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shifts', 's')
            ->select('count(s)')
            ->where('p.id = :id')
            ->andWhere('s.status = :open')
            ->andWhere('s.start > :now')
            ->setParameter(':open', 'Open')
            ->setParameter(':id', $providerId)
            ->setParameter(':now', $now);

        $count = $q->getQuery()->getResult()[0][1];

        if ($count === null)
            $count = 0;

        return $count;
    }

    public function getShiftRequestsCount($providerId) {
        $now = new Datetime('now');
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shifts', 's')
            ->select('count(s)')
            ->where('p.id = :id')
            ->andWhere('s.status = :pending')
            ->andWhere('s.provider_approved = 0')
            ->andWhere('s.start > :now')
            ->setParameter(':pending', 'Pending')
            ->setParameter(':id', $providerId)
            ->setParameter(':now', $now);

        $count = $q->getQuery()->getResult()[0][1];

        return $count;
    }

    public function getUnresolvedPaymentsCount($providerId) {
        $conditions = [
            'pp.is_deleted = false',
            'pp.is_deleted = 0',
            'pp.is_deleted IS NULL'
        ];
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shifts', 's')
            ->leftJoin('s.payroll_payments', 'pp')
            ->select('count(pp)')
            ->where('p.id = :id')
            ->andWhere('s.status = :shiftStatus')
            ->andWhere('pp.status = :paymentStatus')
            ->andWhere('pp.clocked_hours != 0')
            ->setParameter(':id', $providerId)
            ->setParameter(':shiftStatus', "Completed")
            ->setParameter(':paymentStatus', "Unresolved");
        $q->andWhere($q->expr()->orX()->addMultiple($conditions));

        return  $q->getQuery()->getResult()[0][1];
    }
}
