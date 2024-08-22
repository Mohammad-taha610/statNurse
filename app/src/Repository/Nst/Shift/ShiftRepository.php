<?php

namespace App\Repository\Nst\Shift;

use App\Entity\Nst\Events\Shift;
use App\Entity\Nst\Member\Provider;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ShiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shift::class);
    }

    public function getShiftCountAggregates(
        array     $providers,
        \DateTime $start,
        DateTime  $end,
        int       $nurseFilterId = null,
        int       $categoryFilterId = null,
        string    $credentialFilter = null,
        int       $providerFilterId = null,
    )
    {
        // go back one day on start and ahead one day on end
        $start->modify('-1 day');
        $end->modify('+1 day');
        // set start to 00 and end to 23:59
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);
        $qb = $this->createQueryBuilder('s');
        $qb->select('COUNT(s.id) as shiftCount, s.status as shiftStatus, s.start as shiftDate')
            ->leftJoin('s.nurse', 'n')
            ->leftJoin('s.category', 'c')
            ->andWhere('s.start >= :start')
            ->andWhere('s.end <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        // Add provider filter if set
        if ($providerFilterId !== null) {
            $qb->andWhere('s.provider = :provider')
                ->setParameter('provider', $providerFilterId);
        } elseif (!empty($providers)) {
            $qb->andWhere('s.provider IN (:providers)')
                ->setParameter('providers', $providers);
        }

        if ($nurseFilterId !== null) {
            $qb->andWhere('n.id = :nurseFilterId')
                ->setParameter('nurseFilterId', $nurseFilterId);
        }

        if ($categoryFilterId !== null) {
            $qb->andWhere('c.id = :categoryFilterId')
                ->setParameter('categoryFilterId', $categoryFilterId);
        }

        if ($credentialFilter !== null) {
            $qb->andWhere('s.nurseType = :credentialFilter')
                ->setParameter('credentialFilter', $credentialFilter);
        }

        $qb->groupBy('shiftDate, shiftStatus');

        return $qb->getQuery()->getResult();
    }

    public function findShiftsByProviderAndDateRange(Provider $provider, \DateTime $start, \DateTime $end, string $calendarMode): array
    {
        $start->modify('-1 day');
        $end->modify('+1 day');
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.provider = :provider')
            ->andWhere('s.start >= :start')
            ->andWhere('s.end <= :end')
            ->setParameter('provider', $provider)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $qb->getQuery()->getResult();
    }

    public function getShiftRequestsForProviders(
        array $providers,
    )
    {
        $now = new Datetime('now');

        $q = $this->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->select('s')
            ->where('p.id IN (:ids)')
            ->andWhere('s.status = :pending')
            ->andWhere('s.provider_approved = 0')
            ->andWhere('s.start > :now')
            ->setParameter(':pending', 'Pending')
            ->setParameter(':ids', $providers)
            ->setParameter(':now', $now);

        return $q->getQuery()->getResult();
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param Provider $provider
     * @return Shift[]
     */
    public function providerShiftsInTimeFrame(DateTime $start, DateTime $end, Provider $provider)
    {
        $start->setTime(0, 0, 0);
        $end->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.provider = :provider')
            ->andWhere('s.start >= :start')
            ->andWhere('s.end <= :end')
            ->setParameter('provider', $provider)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $qb->getQuery()->getResult();
    }

    public function getShifts(array $providers, $page, $offset, $start)
    {
        // Create the base query builder instance
        $qb = $this->createQueryBuilder('s');
        $qb->leftJoin('s.provider', 'p')
            ->leftJoin('s.nurse', 'n')
            ->andWhere('s.start >= :start')
            ->setParameter('start', $start);

        if (!empty($providers)) {
            $qb->andWhere('s.provider IN (:providers)')
                ->setParameter('providers', $providers);
        }

        // Create a clone for counting total results
        $countQb = clone $qb;
        $countQb->select('COUNT(s.id)');
        $totalShifts = $countQb->getQuery()->getSingleScalarResult();

        // Calculate total pages
        $totalPages = ceil($totalShifts / $offset);

        // Modify the original query for pagination
        $qb->select('s')
            ->orderBy('s.start', 'ASC')
            ->setFirstResult(($page - 1) * $offset)
            ->setMaxResults($offset);

        $shifts = $qb->getQuery()->getResult();

        // Return both the shifts and the total pages
        return [
            'shifts' => $shifts,
            'totalPages' => $totalPages
        ];
    }
}
