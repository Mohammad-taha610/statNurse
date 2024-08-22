<?php


namespace nst\member;


use sa\member\saMemberRepository;

class SaNstMemberRepository extends saMemberRepository
{
    public function getMemberType($id) {
        $q = $this->createQueryBuilder('m')
            ->select('m.member_type')
            ->where('m.id = :id')
            ->setParameter('id', $id);

        return $q->getQuery()->getSingleResult();
    }
}