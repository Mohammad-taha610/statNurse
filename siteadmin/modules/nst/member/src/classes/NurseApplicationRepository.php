<?php


namespace nst\member;


class NurseApplicationRepository extends \sacore\application\DefaultRepository
{
    public function search($fieldsToSearch, $orderBy = null, $perPage = null, $offset = null, $count = false, $secondary_sort = null, $where_andor = 'and', $search_start = true, $search_end = true)
    {
        $query = $this->createQueryBuilder('q');
        $orderByCustom = false;

        // Check if we are ordering by a value stored in our json data
        foreach ($orderBy as $key => $value) {
            if ($key == "first_name"
                || $key == "last_name"
                || $key == "phone_number"
                || $key == "email") {
                $orderByCustom = true;
            }
        }

        if ($orderBy && !$orderByCustom) {
            foreach ($orderBy as $f => $d) {
                $query->addOrderBy('q.' . $f, $d);
            }
        } else if ($orderBy && $orderByCustom) {
            //if ordering on values in json data do custom substring index
            foreach ($orderBy as $f => $d) {
                $query->addSelect(
                    'SUBSTRING_INDEX(SUBSTRING_INDEX(q.nurse, \'' . trim($f) . '\\":\\"\', -1), \'\\",\', 1) AS HIDDEN custom_order_index'
                );
                $query->addOrderBy('custom_order_index', $d);
            }
        }

        if ($secondary_sort) {
            foreach ($secondary_sort as $f => $d) {
                $query->addOrderBy('q.' . $f, $d);
            }
        }

        if ($perPage) {
            $query->setMaxResults($perPage);
        }

        if ($offset) {
            $query->setFirstResult($offset);
        }

        $has_declined_at = false;
        $has_approved_at = false;
        $has_submitted_at = false;
        $has_saved_at = false;
        $has_submitted_date = false;

        if (is_array($fieldsToSearch)) {
            foreach ($fieldsToSearch as $f => $v) {
                if ($f === 'declined_at') {
                    $has_declined_at = true;
                } else if ($f === 'approved_at') {
                    $has_approved_at = true;
                } else if ($f === 'submitted_at' && $v !== 'saved') {
                    $has_submitted_at = true;

                    if (strtotime($v)) {
                        $has_submitted_date = true;
                    }
                }

                if ($f === 'submitted_at' && $v === 'saved') {
                    $has_saved_at = true;
                } else {
                    if (is_array($v)) {
                        foreach ($v as $k => $val) {
                            if ($where_andor == 'or') {
                                $query->orWhere('q.' . $f . ' LIKE :x' . $k);
                            } else {
                                $query->andWhere('q.' . $f . ' LIKE :x' . $k);
                            }
                            $query->setParameter(':x' . $k, $val );
                        }
                    } else {
                        if($where_andor == 'or') {
                            $query->orWhere('q.' . $f . ' LIKE :' . $f);
                        }
                        else {
                            $query->andWhere('q.' . $f . ' LIKE :' . $f);
                        }
                        $query->setParameter(':' . $f, ($search_start ? '%' : '') . $v . ($search_end ? '%' : ''));
                    }
                }
            }
        }            
            
        if ($has_declined_at) {
            $query->andWhere('q.submitted_at IS NOT NULL');
            $query->andWhere('q.approved_at IS NULL');
            $query->andWhere('q.declined_at IS NOT NULL');
        }
        else if ($has_approved_at) {
            $query->andWhere('q.submitted_at IS NOT NULL');
            $query->andWhere('q.approved_at IS NOT NULL');
            $query->andWhere('q.declined_at IS NULL');
        }
        else if ($has_saved_at) {
            $query->andWhere('q.submitted_at IS NULL');
            $query->andWhere('q.approved_at IS NULL');
            $query->andWhere('q.declined_at IS NULL');
        }
        //custom WHERE statements to filter results per client request
        else if ($has_submitted_at) {
            $query->andWhere('q.submitted_at IS NOT NULL');
            if (!$has_submitted_date) {
                $query->andWhere('q.approved_at IS NULL');
                $query->andWhere('q.declined_at IS NULL');
            }
        }

        if ($count) {
            $query->select('count(q.id)');
            return $query->getQuery()->getSingleScalarResult();
        } else {
            return $query->getQuery()->getResult();
        }
    }
}