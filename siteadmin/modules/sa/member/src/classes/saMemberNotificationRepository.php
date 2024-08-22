<?php

namespace sa\member;

use Doctrine\ORM\EntityRepository;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;

/**
 * saMemberNotificationRepository
 */
class saMemberNotificationRepository extends \sacore\application\DefaultRepository
{
    public function getNotificationHistoryForMember($member, $days)
    {

    }

    public function getNewNotificationsForMember($member, $count = false)
    {
        return $this->getNotificationsForMember($member, $count, true);
    }

    public function getNotificationsForMember($member, $count = false, $newOnly = false, DateTime $startTime = null, DateTime $endTime = null)
    {
        $query = $this->createQueryBuilder('n');
        $query->where('n.member = :member')
            ->setParameter(':member',$member);

        if($newOnly) {
            $query->andWhere('n.is_viewed = false');
        }

        if($startTime) {
            $query->andWhere('n.date_created >= :startTime')
                ->setParameter(':startTime',$startTime->format("Y-m-d G:i:s"));
        }
        if($endTime) {
            $query->andWhere('n.date_created <= :endTime')
                ->setParameter(':endTime',$endTime->format("Y-m-d G:i:s"));;
        }

        if($count) {
            $query->select('COUNT(n.id)');
            return $query->getQuery()->getSingleScalarResult();
        }
        else {
            $query->select('n.id,n.message,n.date_created,n.link,n.image_url');
            return $query->getQuery()->getResult();
        }

    }

    public function addNewNotificationToMember($member, $message, $link = null, $imageUrl = null)
    {
        /** @var saMemberNotification $notification */
        $notification = ioc::get('saMemberNotification');
        $notification->setMember($member);
        $notification->setMessage($message);
        $notification->setLink($link);
        $notification->setImageUrl($imageUrl);
        app::$entityManager->persist($notification);
        app::$entityManager->flush($notification);
    }
}
