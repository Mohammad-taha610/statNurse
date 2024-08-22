<?php

namespace App\Repository\Sax;

use App\Entity\Sax\Member\SaMemberUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaMemberUsers>
 *
 * @method SaMemberUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaMemberUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaMemberUsers[]    findAll()
 * @method SaMemberUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaMemberUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaMemberUsers::class);
    }

    //    /**
    //     * @return SaMemberUsers[] Returns an array of SaMemberUsers objects
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

    //    public function findOneBySomeField($value): ?SaMemberUsers
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
