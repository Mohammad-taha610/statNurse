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

class NurseRepository extends NstDefaultRepository
{

    public function getNurseByMemberName($firstName, $lastName, $order = 'DESC', $andOr = "AND")
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
    }

    public function getNurseAvailability($start, $end, $nurse)
    {
        $q1 = $this->createQueryBuilder('n');
        $q1->leftJoin('n.shifts', 's')
            ->select('n')
            ->addSelect('s')
            ->where('n = :nurse')
            ->andWhere('n.is_deleted = false')
            ->andWhere('s.start <= :end')
            ->andWhere('s.end >= :start')
            ->setParameter('nurse', $nurse)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if (count($q1->getQuery()->getResult()) > 0) {
            return false;
        }

        return true;
    }

    public function getUpcomingNurseShifts($id)
    {
        $now    = new DateTime('now', app::getInstance()->getTimeZone());
        $result = [];

        $q = $this->createQueryBuilder('n')
            ->select('n')
            ->leftJoin('n.shifts', 's')
            ->leftJoin('s.provider', 'p')
            ->leftJoin('p.member', 'm')
            ->addSelect('s.start_date')
            ->addSelect('s.start_time')
            ->addSelect('m.company')
            ->where('n.id = :id')
            ->andWhere('s.start_date >= :now')
            ->andWhere('s.start_time >= :now')
            ->setParameter(':id', $id)
            ->setParameter(':now', $now);

        $result['shifts'] = $q->getQuery()->getArrayResult();

        return $result;
    }

    public function getNursePastShifts($id)
    {
        $now    = new DateTime('now', app::getInstance()->getTimeZone());
        $result = [];

        $q = $this->createQueryBuilder('n')
            ->select('n')
            ->leftJoin('n.shifts', 's')
            ->leftJoin('s.provider', 'p')
            ->leftJoin('p.member', 'm')
            ->addSelect('s.start_date')
            ->addSelect('s.start_time')
            ->addSelect('m.company')
            ->where('n.id = :id')
            ->andWhere('s.start_date < :now')
            ->andWhere('s.start_time < :now')
            ->setParameter(':id', $id)
            ->setParameter(':now', $now);

        $result['shifts'] = $q->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * @param Provider $provider
     * @param Json $filters
     * [
     *  'worked_with' => ['All', 'Yes', 'No'],
     *  'unresolved_pay' => ['All', 'Yes', 'No'],
     *  'blocked' => ['All', 'Yes', 'No']
     * ]
     */
    public function findWithFilters($filters, $providerId)
    {
        $provider = ioc::get('Provider', ['id' => $providerId]);

        $q = $this->createQueryBuilder('n')
            ->leftJoin('n.member', 'm')
            ->leftJoin('n.previous_providers', 'pp')
            ->select('n');


        switch ($filters['worked_with']) {
            case 'No':
                $subQ = $this->createQueryBuilder('nn')
                    ->leftJoin('nn.previous_providers', 'p')
                    ->select('p.id');

                $q->andWhere($q->expr()->notIn(':provider_id', $subQ->getQuery()->getDQL()))
                    ->setParameter(':provider_id', $providerId);
                break;
            case 'Yes':
                $subQ = $this->createQueryBuilder('nn')
                    ->leftJoin('nn.previous_providers', 'p')
                    ->select('p.id');

                $q->andWhere($q->expr()->In(':provider_id', $subQ->getQuery()->getDQL()))
                    ->setParameter(':provider_id', $providerId);
                break;
            default:
                break;
        }

        switch ($filters['unresolved_pay']) {
            case 'No':
                $subQ = $this->createQueryBuilder('nn')
                    ->leftJoin('nn.shifts', 's')
                    ->leftJoin('s.payroll_payment', 'pp')
                    ->select('nn.id')
                    ->where('pp.status = :unresolved')
                    ->setParameter(':unresolved', 'Unresolved');

                $q->andWhere($q->expr()->notIn('n.id', $subQ->getQuery()->getDQL()))
                    ->setParameter(':unresolved', 'Unresolved');
                break;
            case 'Yes':
                $subQ = $this->createQueryBuilder('nn')
                    ->leftJoin('nn.shifts', 's')
                    ->leftJoin('s.payroll_payment', 'pp')
                    ->select('nn.id')
                    ->where('pp.status = :unresolved')
                    ->setParameter(':unresolved', 'Unresolved');

                $q->andWhere($q->expr()->In('n.id', $subQ->getQuery()->getDQL()))
                    ->setParameter(':unresolved', 'Unresolved');
                break;
            default:
                break;
        }

        switch ($filters['blocked']) {
            case 'No':
                $subQ = $this->createQueryBuilder('nn')
                    ->leftJoin('nn.blocked_providers', 'bp')
                    ->select('bp.id')
                    ->where('bp.id = :providerId')
                    ->setParameter(':providerId', $providerId);

                $q->andWhere($q->expr()->notIn('pp.id', $subQ->getQuery()->getDQL()))
                    ->setParameter(':providerId', $providerId);
                break;
            case 'Yes':
                $subQ = $this->createQueryBuilder('nn')
                    ->leftJoin('nn.blocked_providers', 'bp')
                    ->select('bp.id')
                    ->where('bp.id = :providerId')
                    ->setParameter(':providerId', $providerId);

                $q->andWhere($q->expr()->In('pp.id', $subQ->getQuery()->getDQL()))
                    ->setParameter(':providerId', $providerId);
                break;
            default:
                break;
        }
        $q->andWhere('n.is_deleted = 0');
        $q->andWhere('m.is_deleted = 0');

        return $q->getQuery()->getResult();
    }

    public function findNursesOfTypes($types)
    {
        if (count($types) > 0) {
            if (is_array($types) && in_array('CNA', $types)) {
                if (!in_array('CMT', $types))
                    $types[] = 'CMT';
            }
            $q = $this->createQueryBuilder('n')
                ->select('n');
            if (count($types) === 1) {
                $q->andWhere($q->expr()->in('n.credentials', $types));
            } else {
                $q->andWhere('n.credentials IN (:types)')
                    ->setParameter('types', $types);
            }

            return $q->getQuery()->getResult();
        } else {
            return $this->findAll();
        }
    }

    public function getNurse1099Info($nurseId)
    {

        $q = $this->createQueryBuilder('q')
            ->leftJoin('q.member', 'm')
            ->leftJoin('m.users', 'u')
            ->select('m.id')
            ->addSelect('u.user_key')
            ->addSelect('q.last_name')
            ->addSelect('q.first_name')
            ->addSelect('q.ssn')
            ->addSelect('q.street_address')
            ->addSelect('q.street_address_2')
            ->addSelect('q.apt_number')
            ->addSelect('q.city')
            ->addSelect('q.state')
            ->addSelect('q.zipcode')
            ->where('q.id = :nurse_id')
            ->setParameter(':nurse_id', $nurseId);

        return $q->getQuery()->getArrayResult()[0];
    }

    public function searchFlexible($fieldsToSearch, $orderBy = null, $perPage = null, $offset = null, $count = false, $secondary_sort = null, $where_andor = 'and', $search_start = true, $search_end = true)
    {
        $query = $this->createQueryBuilder('q');

        if ($orderBy) {
            foreach ($orderBy as $f => $d) {
                $query->addOrderBy('q.' . $f, $d);
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

        //My take on an improved search which allows for minimal implicit table joining
        $joinedSpaces = [];
        if (is_array($fieldsToSearch)) {
            foreach ($fieldsToSearch as $f => $v) {
                $tableAlias = "q.";
                if (stristr($f, '.')) {
                    [$joinedAlias, $f] = explode('.', $f);
                    if (!in_array($joinedAlias, $joinedSpaces)) {
                        $joinedSpaces[] = $joinedAlias;
                        $query->leftJoin('q.' . $joinedAlias, $joinedAlias);
                    }
                    $tableAlias = $joinedAlias . ".";
                }
                if (is_array($v)) {
                    $orStatement = $query->expr()->orX();
                    foreach ($v as $index => $value) {
                        $orStatement->add($tableAlias . $f . ' LIKE :' . $f . $index);
                        $query->setParameter(':' . $f . $index, ($search_start ? '%' : '') . $value . ($search_end ? '%' : ''));
                    }
                    $query->andWhere($orStatement);
                } else {
                    if ($where_andor == 'or') {
                        $query->orWhere($tableAlias . $f . ' LIKE :' . $f);
                    } else {
                        $query->andWhere($tableAlias . $f . ' LIKE :' . $f);
                    }
                    $query->setParameter(':' . $f, ($search_start ? '%' : '') . $v . ($search_end ? '%' : ''));
                }
            }
        }

        if ($count) {
            $query->select('count(q.id)');

            return $query->getQuery()->getSingleScalarResult();
        } else {
            return $query->getQuery()->getResult();
        }
    }

    public function searchNurseByName($terms)
    {
        $terms = explode(' ', $terms);

        $query = $this->createQueryBuilder('n')
            ->select('n')
            ->setMaxResults(50);

        if (!$terms) {
            return $query->getQuery()->getResult();
        }

        // Using rand() here to assist in generating dynamic parameter names for dynamic search terms
        foreach ($terms as $term) {
            $rand = rand(0, 99999);
            $query->orWhere("n.first_name like :term$rand OR n.last_name like :term$rand")
                ->setParameter(":term$rand", $term . '%');
        }

        return $query->getQuery()->getResult();
    }

    public function searchNurseByFirstAndLastFuzzy($first, $last)
    {

        $query = $this->createQueryBuilder('n')
            ->select('n')
            ->where('n.first_name like :fName')
            ->andWhere('n.last_name like :lName')
            ->setMaxResults(1);

        $query->setParameter(":fName", $first . '%')
            ->setParameter(":lName", $last . '%');

        return $query->getQuery()->getResult();
    }

    public function getNursesThatWorkedInQuarter($dateRange, $count = false, $offset = null, $limit = null)
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
            ->orderBy('n.first_name', 'DESC')
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

    public function getNursesThatDidNotWorkInQuarter($dateRange, $count = false, $offset = null, $limit = null)
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
                    WHERE s3.nurse = n2
                )'
            )
            ->setParameter(':start', $start)
            ->groupBy('n2.id');
    
        // $q = $this->createQueryBuilder('n')
        //     ->select('n')
        //     ->where('n.id NOT IN (' . $subQuery->getDQL() . ')')
        //     ->orderBy('n.first_name', 'ASC')
        //     ->setParameter(':start', $start);
    
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

    public function manageNurses()
    {
        $q = $this->createQueryBuilder('n')
            ->select('n.id as nurse_id, n.email_address, n.phone_number, n.city, n.state, n.credentials, m.id as member_id, m.first_name, m.middle_name, m.last_name, m.is_deleted, m.date_created, mu.username, mu.last_login')
            ->innerJoin('n.member', 'm')
            ->innerJoin('m.users', 'mu')
            ->orderBy('n.first_name', 'ASC');

        return $q->getQuery()->getResult();
    }
    
     /**
     * twilio phone numbers format: +15555555555
     * 99% of nurse phone numbers format: (555) 555-5555
     * 1% of nurse phone numbers format: 555-555-5555
     * .01% of nurse phone numbers format: 5555555555
     */
    public function findNurseByPhoneNumber($nursePhoneNumber)
    {
        $normalizedPhoneNumber = preg_replace('/[^0-9]/', '', $nursePhoneNumber);
        if (strlen($normalizedPhoneNumber) == 11) {
            $normalizedPhoneNumber = substr($normalizedPhoneNumber, 1);
        }

        $areaCode = substr($normalizedPhoneNumber, 0, 3);
        $phoneNumber = substr($normalizedPhoneNumber, 3);

        $formattedPhoneNumber = '(' . $areaCode . ') ' . substr($phoneNumber, 0, 3) . '-' . substr($phoneNumber, 3);
        $formattedPhoneNumber2 = $areaCode . '-' . substr($phoneNumber, 0, 3) . '-' . substr($phoneNumber, 3);

        $q = $this->createQueryBuilder('n')
            ->select('n')
            ->where('n.phone_number like :phone')
            ->orWhere('n.phone_number like :phone2')
            ->orWhere('n.phone_number like :phone3')
            ->setParameter(':phone', $formattedPhoneNumber . '%')
            ->setParameter(':phone2', $formattedPhoneNumber2 . '%')
            ->setParameter(':phone3', $normalizedPhoneNumber . '%')
            ->setMaxResults(1);

        return $q->getQuery()->getResult();
    }

    public function findNursesWithUnreadMessages()
    {
        $q = $this->createQueryBuilder('n')
            ->select('n.id, n.first_name, n.last_name, n.phone_number, m.id as member_id, m.is_deleted')
            ->innerJoin('n.member', 'm')
            ->innerJoin('n.messages', 'msg')
            ->where('msg.has_been_viewed = :has_been_viewed')
            ->setParameter(':has_been_viewed', false)
            ->orderBy('n.first_name', 'ASC')
            ->distinct();

        return $q->getQuery()->getResult();
    }

    public function getNursesActivePayBetween($dateRange, $count = false, $offset = null, $limit = null)
    {

        $q = $this->createQueryBuilder('n')
        ->select('n.first_name', 'n.last_name', 'SUM(pp.pay_total) AS totalPay', 'n.id', 'p.state_abbreviation', 'p.city', 'n.credentials')
        ->leftJoin('n.shifts', 's')
        ->leftJoin('s.provider', 'p')
        ->innerJoin('s.payroll_payments', 'pp')
        ->where('s.start BETWEEN :start AND :end')
        ->andWhere('s.status = :completed')
        ->groupBy('n.id')
        ->setParameter(':start', $dateRange['start'])
        ->setParameter(':end', $dateRange['end'])
        ->setParameter(':completed', 'Completed');
            
    
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
            return $q->getQuery()->getArrayResult();
        }
    }

    public function getNursesActivePayBetweenState($dateRange, $count = false, $offset = null, $limit = null)
    {
        
        $q = $this->createQueryBuilder('n')
        ->select('n.first_name', 'n.last_name', 'SUM(pp.pay_total) AS totalPay', 'n.id', 'p.state_abbreviation', 'p.city', 'n.credentials')
        ->leftJoin('n.shifts', 's')
        ->leftJoin('s.provider', 'p')
        ->innerJoin('s.payroll_payments', 'pp')
        ->where('s.start BETWEEN :start AND :end')
        ->andWhere('s.status = :completed')
        ->andWhere('LOWER(p.state_abbreviation) LIKE :state')
        ->groupBy('n.id')
        ->setParameter(':start', $dateRange['start'])
        ->setParameter(':end', $dateRange['end'])
        ->setParameter(':state', $dateRange['state'])
        ->setParameter(':completed', 'Completed');
            
    
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
            return $q->getQuery()->getArrayResult();
        }
    }
}
