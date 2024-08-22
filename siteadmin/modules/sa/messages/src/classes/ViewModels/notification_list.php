<?php

namespace sa\member\ViewModels;


use Doctrine\Common\Util\Debug;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\IViewModel;
use sa\member\auth;


/**
 * Class notification_list
 * @package sa\catalog\ViewModels
 */
class notification_list implements IViewModel
{

    public $notifications = array();

    public function __construct($data)
    {
        $startTime = null;
        if($data['days']) {
            $startTime = new DateTime(date("Y-m-d G:i:s",strtotime("-" . $data['days'] ." days")));
        }
        $endTime = new DateTime();
        $member = auth::getAuthMember();

        $this->notifications = ioc::getRepository('saMemberNotification')->getNotificationsForMember($member,false,false,$startTime, $endTime);
    }

    public function getXssSanitationExcludes()
    {
        // TODO: Implement getXssSanitationExcludes() method.
    }
}