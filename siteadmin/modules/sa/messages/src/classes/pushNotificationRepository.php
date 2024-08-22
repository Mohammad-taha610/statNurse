<?php

namespace sa\messages;

use sacore\application\DefaultRepository;

class pushNotificationRepository extends DefaultRepository {

    public function getNewCount($batch_id = null) {
        $qb = $this->createQueryBuilder('notification');
        $qb->select('COUNT(notification)');
        $qb->where('notification.attempted_send = 0');
        
        if ($batch_id) {
            $qb->andWhere('notification.batch_id = :batch_id');
            $qb->setParameter(':batch_id', $batch_id);
        } else {
            $qb->andWhere('notification.batch_id IS NULL');
        }
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getNew($batch_id = null, $limit = 50, $offset = 0) {
        $qb = $this->createQueryBuilder('notification');
        $qb->where('notification.attempted_send = 0');
        
        if ($batch_id) {
            $qb->andWhere('notification.batch_id = :batch_id');
            $qb->setParameter(':batch_id', $batch_id);
        } else {
            $qb->andWhere('notification.batch_id IS NULL');
        }
        
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        
        return $qb->getQuery()->getResult();
    }
    
}