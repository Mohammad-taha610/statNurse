<?php

namespace App\Repository\Sax;

use App\Entity\Sax\Member\SaMemberAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaMemberAddress>
 *
 * @method SaMemberAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaMemberAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaMemberAddress[]    findAll()
 * @method SaMemberAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaMemberAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaMemberAddress::class);
    }

    //    /**
    //     * @return SaMemberAddress[] Returns an array of SaMemberAddress objects
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

    //    public function findOneBySomeField($value): ?SaMemberAddress
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
