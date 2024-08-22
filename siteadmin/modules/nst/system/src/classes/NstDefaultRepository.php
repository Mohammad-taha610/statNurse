<?php

namespace nst\system;

class NstDefaultRepository extends \sacore\application\DefaultRepository{

    public function paginatedSearch($fieldsToSearch, $perPage, $currentPage, $sort, $sortDir){
        $totalRecords = $this->search($fieldsToSearch, null, null, null, true);
        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalPages = ceil($totalRecords / $perPage);

        $entities = $this->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));
        return [$entities, $totalRecords, $totalPages];
    }

    public function search($fieldsToSearch, $orderBy=null, $perPage=null, $offset=null, $count = false, $secondary_sort=null, $where_andor = 'and', $search_start = true, $search_end = true) {
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

        //My take on an improved search which allows for minimal implicit table joining
        $joinedSpaces = [];
        if (is_array($fieldsToSearch) ) {
            foreach ($fieldsToSearch as $f => $v) {
                $tableAlias = "q.";
                if(stristr($f,'.')){
                    [$joinedAlias,$f] = explode('.',$f);
                    if(!in_array($joinedAlias,$joinedSpaces)){
                        $joinedSpaces[] = $joinedAlias;
                        $query->leftJoin('q.' . $joinedAlias, $joinedAlias);
                    }
                    $tableAlias = $joinedAlias . ".";
                }
                if(is_array($v)) {
                    $orStatement = $query->expr()->orX();
                    foreach ($v as $index => $value) {
                        $orStatement->add($tableAlias . $f . ' LIKE :' . $f . $index);
                        $query->setParameter(':' . $f . $index, ($search_start ? '%' : '') . $value . ($search_end ? '%' : ''));
                    }
                    $query->andWhere($orStatement);
                }
                else {
                    if ($where_andor == 'or') {
                        $query->orWhere($tableAlias . $f . ' LIKE :' . $f);
                    } else {
                        $query->andWhere($tableAlias . $f . ' LIKE :' . $f);
                    }
                    $query->setParameter(':' . $f, ($search_start ? '%' : '') . $v . ($search_end ? '%' : ''));

                }
            }
        }

        if($count) {
            $query->select('count(q.id)');
            return $query->getQuery()->getSingleScalarResult();
        }
        else {
            return $query->getQuery()->getResult();
        }
    }
}