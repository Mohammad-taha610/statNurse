<?php

namespace nst\system;


/**
 * @IOC_NAME="saStateRepository"
 */
class NstStateRepository extends \sa\system\saStateRepository
{
    public function getAllStateNames() {
        $q = $this->createQueryBuilder('s');
        $q->select('s.name');
        return $q->getQuery()->getResult();
    }

    public function getAllStates() {
        $q = $this->createQueryBuilder('s');

        $q->select('s')
        ->where('s.name <> :empty_name'); // filter values that have empty names (These do exist in the current database apparently...)

        $q->setParameter(':empty_name', '');
        
        return $q->getQuery()->getResult();
    }
}