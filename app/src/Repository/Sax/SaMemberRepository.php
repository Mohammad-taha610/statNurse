<?php

namespace App\Repository\Sax;

use App\Entity\Sax\Member\SaMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaMember>
 *
 * @method SaMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaMember[]    findAll()
 * @method SaMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaMember::class);
    }

    //    /**
    //     * @return SaMember[] Returns an array of SaMember objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SaMember
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
