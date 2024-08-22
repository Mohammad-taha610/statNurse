<?php


namespace nst\member;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Query\Expr\Join;
use nst\system\NstDefaultRepository;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\responses\Json;
use function Doctrine\ORM\QueryBuilder;

class CheckrPayWebhookRepository extends NstDefaultRepository
{

/*     public function getNurseByMemberName($firstName, $lastName, $order = 'DESC', $andOr = "AND")
    {
        $qb = $this->createQueryBuilder('n');
        $qb->select('n')
            ->innerJoin(ioc::staticGet('saMember'), 'm', Join::WITH, 'n.member = m')
            ->where('n.lastName LIKE  :lastName ' . $andOr . ' n.firstName LIKE :firstName')
            ->setParameter('lastName', $lastName)
            ->setParameter('firstName', $firstName)
            ->orderBy('m.lastName', $order)
            ->orderBy('m.firstName', $order);


        $result = $qb->getQuery()->getResult();

        return $result;
    } */
}
