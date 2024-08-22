<?php

namespace App\Repository\Nst\Member;

use App\Entity\Nst\Member\Nurse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NurseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nurse::class);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param Nurse $nurse
     * @return bool
     */
    public function getNurseAvailability(\DateTime $start, \DateTime $end, Nurse $nurse)
    {
        $q1 = $this->createQueryBuilder('n');
        $q1->leftJoin('n.shifts', 's')
            ->select('n')
            ->addSelect('s')
            ->where('n = :nurse')
            ->andWhere('n.is_deleted = false')
            ->andWhere('s.start <= :end')
            ->andWhere('s.end >= :start')
            ->setParameter('nurse', $nurse)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if (count($q1->getQuery()->getResult()) > 0) {
            return false;
        }

        return true;
    }
}
