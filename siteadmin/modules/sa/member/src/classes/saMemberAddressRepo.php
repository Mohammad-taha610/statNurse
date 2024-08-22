<?php

namespace sa\member;

use sacore\application\DefaultRepository;

class saMemberAddressRepo extends DefaultRepository {

    /**
     * @param saMember $member
     * @return saMemberAddress
     */
    public function getActivePrimaryAddress(saMember $member) {
        $query = $this->createQueryBuilder('address');
        
        $query->where('address.member = :member');
        $query->setParameter(':member', $member);
        $query->andWhere('address.is_primary = true');
        $query->andWhere('address.is_active = true');
        $query->setMaxResults(1);
        
        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Gives all distinct address types stored
     * in the database
     */
    public function getAllDistinctAddressTypes() {
        $query = $this->createQueryBuilder('address');
        $query->select('address.type');
        $query->andWhere('address.type != :personal');
        $query->setParameter(':personal', 'personal');
        $query->andWhere('address.type != :work');
        $query->setParameter(':work', 'work');
        $query->andWhere('address.type != :secondary');
        $query->setParameter(':secondary', 'secondary');
        $query->andWhere('address.type != :other');
        $query->setParameter(':other', 'other');
        $query->distinct();
        
        $results = $query->getQuery()->getResult();
        
        $types = array();
        
        if(count($results)) {
            foreach($results as $result) {
                $types[] = $result['type'];
            }
        }
        
        return $types;
    }
}