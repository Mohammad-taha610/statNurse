<?php
namespace sa\member;

use sacore\application\app;
use sacore\utilities\url;

/**
 * Class MemberProfileModRequestListeners
 * @package sa\member
 */
class MemberProfileModRequestListeners
{
    /**
     * Adds default profile sidebar links.
     *
     * @param array $data
     * @return array
     */
    public static function getSidebarLinks(array $data = array()): array
    {
        $data[] = array(
            "label" => "Dashboard",
            "link"  => app::get()->getRouter()->generate('dashboard_home')
        );

        $data[] = array(
            "label" => "Account",
            "link" => app::get()->getRouter()->generate('member_profile'),
            "children" => array(
                array(
                    "label" => "Basic Info",
                    "link" => app::get()->getRouter()->generate('member_profile')
                ),
                array(
                    "label" => "Username/Password",
                    "link" => app::get()->getRouter()->generate('member_users')
                ),
                array(
                    "label" => "Emails",
                    "link" => app::get()->getRouter()->generate("member_email_addresses")
                ),
                array(
                    "label" => "Addresses",
                    "link" => app::get()->getRouter()->generate("member_addresses")
                ),
                array(
                    "label" => "Phone Numbers",
                    "link" => app::get()->getRouter()->generate("member_phone_numbers")
                )
            )
        );

        return $data;
    }

    /**
     * Adds HTML widgets to profile sidebar.
     *
     * @param array $data
     * @return array
     */
    public static function getSidebarWidgets(array $data = array()): array
    {
        return $data;
    }
}
