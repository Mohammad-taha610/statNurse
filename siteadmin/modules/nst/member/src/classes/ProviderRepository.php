<?php


namespace nst\member;


use nst\events\Shift;
use nst\system\NstDefaultRepository;
use sacore\application\app;
use sacore\application\DateTime;

class ProviderRepository extends NstDefaultRepository{

    /**
     * Retrieve all nurses that have worked with the provider and are not on the DO NOT RETURN list
     * @param int $id   -  Provider Id
     * @return array
     */
    public function getAssignableNurses($id) {
        $sub = $this->createQueryBuilder('ps')
            ->leftJoin('ps.blocked_nurses', 'ns')
            ->select('ns.id')
            ->where('ps.id = :id')
            ->setParameter(':id', $id);

        $q = $this->createQueryBuilder('p');
        $q->leftJoin('p.previous_nurses', 'n')
            ->leftJoin('n.member', 'm')
            ->select('n.id')
            ->addSelect('m.first_name')
            ->addSelect('m.last_name')
            ->where('p.id = :id')
            ->andWhere($q->expr()->notIn('n.id', $sub->getQuery()->getDQL()))
            ->andWhere('m.is_deleted = 0')
            ->setParameter(':id', $id);

        return $q->getQuery()->getArrayResult();
    }

    public function getShiftRequests($id) {
        $result = [];

        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shifts', 's')
            ->leftJoin('s.nurse', 'n')
            ->leftJoin('n.member', 'm')
            ->select('p')
            ->addSelect('s')
            ->addSelect('n')
            ->addSelect('m.first_name')
            ->addSelect('m.last_name')
            ->where('p.id = :id')
            ->andWhere('s.nurse_approved = 1')
            ->andWhere('s.provider_approved = 0')
            ->setParameter(':id', $id);

        $result['shifts'] = $q->getQuery()->getArrayResult();

        /*$q = $this->createQueryBuilder('p')
            ->leftJoin('p.shift_recurrences', 'r')
            ->leftJoin('r.nurse', 'n')
            ->leftJoin('n.member', 'm')
            ->select('p')
            ->addSelect('r')
            ->addSelect('n')
            ->addSelect('m.first_name')
            ->addSelect('m.last_name')
            ->where('p.id = :id')
            ->andWhere('r.nurse_approved = 1')
            ->andWhere('r.provider_approved = 0')
            ->setParameter(':id', $id);

        $result['shift_recurrences'] = $q->getQuery()->getArrayResult();*/

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function getUnclaimedShiftsCount($id) {
        $now = new Datetime('now');
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shifts', 's')
            ->select('count(s)')
            ->where('p.id = :id')
            ->andWhere('s.status = :open')
            ->andWhere('s.start > :now')
            ->setParameter(':open', 'Open')
            ->setParameter(':id', $id)
            ->setParameter(':now', $now);

        $count = $q->getQuery()->getResult()[0][1];

        if ($count === null)
            $count = 0;

        return $count;
    }

    public function getShiftRequestsCount($id) {
        $now = new Datetime('now');
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shifts', 's')
            ->select('count(s)')
            ->where('p.id = :id')
            ->andWhere('s.status = :pending')
            ->andWhere('s.provider_approved = 0')
            ->andWhere('s.start > :now')
            ->setParameter(':pending', 'Pending')
            ->setParameter(':id', $id)
            ->setParameter(':now', $now);

        $count = $q->getQuery()->getResult()[0][1];

        return $count;
    }

    public function getUnresolvedPaymentsCount($id) {
        $conditions = [
            'pp.is_deleted = false',
            'pp.is_deleted = 0',
            'pp.is_deleted IS NULL'
        ];
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shifts', 's')
            ->leftJoin('s.payroll_payments', 'pp')
            ->select('count(pp)')
            ->where('p.id = :id')
            ->andWhere('s.status = :shiftStatus')
            ->andWhere('pp.status = :paymentStatus')
            ->andWhere('pp.clocked_hours != 0')
            ->setParameter(':id', $id)
            ->setParameter(':shiftStatus', "Completed")
            ->setParameter(':paymentStatus', "Unresolved");        
        $q->andWhere($q->expr()->orX()->addMultiple($conditions));

        return  $q->getQuery()->getResult()[0][1];
    }


    public function getUpcomingNurseShifts($id)
    {
        $now = new DateTime('now', app::getInstance()->getTimeZone());
        $result = [];

        $q = $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.shifts', 's')
            ->leftJoin('p.member', 'm')
            ->addSelect('s.start_date')
            ->addSelect('s.start_time')
            ->addSelect('m.first_name')
            ->addSelect('m.last_name')
            ->where('p.id = :id')
            ->andWhere('s.start_date >= :now')
            ->andWhere('s.start_time >= :now')
            ->setParameter(':id', $id)
            ->setParameter(':now', $now);

        $result['shifts'] = $q->getQuery()->getArrayResult();

        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.shift_recurrences', 'r')
            ->leftJoin('r.nurse', 'n')
            ->leftJoin('p.member', 'm')
            ->select('m.first_name')
            ->addSelect('m.last_name')
            ->addSelect('r.start')
            ->addSelect('r.status')
            ->where('n.id = :id')
            ->andWhere('r.start >= :now')
            ->andWhere('r.recurrenceExists = 1')
            ->setParameter(':id', $id)
            ->setParameter(':now', $now);

        $result['recurring_shifts'] = $q->getQuery()->getArrayResult();


        return $result;
    }

    public function getProvidersThatWorkedInQuarter($dateRange, $count = false, $offset = null, $limit = null)
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $q = $this->createQueryBuilder('n')
            ->select('n')
            ->leftJoin('n.shifts', 's')
            ->innerJoin('s.payroll_payments', 'pp')
            ->where('s.start BETWEEN :start AND :end')
            ->andWhere('s.status = :completed')
            ->setParameter(':start', $start)
            ->setParameter(':end', $end)
            ->setParameter(':completed', "Completed")
            ->orderBy('n.id', 'ASC')
            ->distinct();

        if ($limit) {
            $q->setMaxResults($limit);
        }

        if ($offset) {
            $q->setFirstResult($offset);
        }

        if ($count) {
            $q->select('COUNT(DISTINCT n.id)');

            return $q->getQuery()->getSingleScalarResult();
        } else {
            return $q->getQuery()->getResult();
        }
    }

    public function getProvidersThatDidNotWorkInQuarter($dateRange, $count = false, $offset = null, $limit = null)
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
    
        $q = $this->createQueryBuilder('n2')
            ->select('n2')
            ->leftJoin('n2.shifts', 's2')
            ->innerJoin('s2.payroll_payments', 'pp2')
            ->where('s2.start < :start')
            ->andWhere(
                's2.start = (
                    SELECT MAX(s3.start)
                    FROM nst\events\shift s3
                    WHERE s3.provider = n2
                )'
            )
            // ->andWhere('s2.status = :completed')
            ->setParameter(':start', $start)
            ->groupBy('n2.id');

            // ->setParameter(':completed', 'Completed');
    
        // $q = $this->createQueryBuilder('n')
        //     ->select('n')
        //     ->where('n.id NOT IN (' . $subQuery->getDQL() . ')')
        //     ->orderBy('n.id', 'ASC')
        //     ->setParameter(':start', $start)
        //     ->setParameter(':end', $end)
        //     // ->setParameter(':completed', 'Completed')
        //     ->distinct();
            
    
        if ($limit) {
            $q->setMaxResults($limit);
        }
    
        if ($offset) {
            $q->setFirstResult($offset);
        }
    
        if ($count) {
            $q->select('COUNT(DISTINCT n2.id)');
    
            return $q->getQuery()->getSingleScalarResult();
        } else {
            return $q->getQuery()->getResult();
        }
    }

}