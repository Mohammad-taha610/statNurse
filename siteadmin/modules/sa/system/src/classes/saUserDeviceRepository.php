<?php

namespace sa\system;

use sacore\application\DefaultRepository;


/**
 * saUserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class saUserDeviceRepository extends DefaultRepository
{
    public function searchByUser($user, $orderBy=null, $perPage=null, $offset=null, $count = false, $secondary_sort=null, $where_andor = 'and') {
        $query = $this->createQueryBuilder('q');

        if ($orderBy) {
            foreach($orderBy as $f=>$d) {
                $query->addOrderBy('q.'.$f, $d);
            }
        }

        if ($secondary_sort) {
            foreach($secondary_sort as $f=>$d) {
                $query->addOrderBy('q.'.$f, $d);
            }
        }

        if ($perPage) {
            $query->setMaxResults($perPage);
        }

        if ($offset) {
            $query->setFirstResult($offset);
        }

        if($distinct) {
            $query->distinct(true);
        }

        $query->andWhere('q.user = :user and q.is_active=:active');
        $query->setParameter(':user', $user);
        $query->setParameter(':active', true);

        if($count) {
            $query->select('count(q.id)');
            return $query->getQuery()->getSingleScalarResult();
        }
        else
            return $query->getQuery()->getResult();
    }
}
