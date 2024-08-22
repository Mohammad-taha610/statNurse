<?php

namespace App\Repository\Nst;

use App\Entity\Nst\Member\NstMemberUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NstMemberUsers>
 *
 * @method NstMemberUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method NstMemberUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method NstMemberUsers[]    findAll()
 * @method NstMemberUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NstMemberUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NstMemberUsers::class);
    }

    //    /**
    //     * @return NstMemberUsers[] Returns an array of NstMemberUsers objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?NstMemberUsers
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
