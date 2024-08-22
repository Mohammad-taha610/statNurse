<?php


namespace nst\member;

use sacore\application\app;
use sa\member\auth;
use sa\member\MemberProfileModRequestListeners;

class NstMemberProfileModRequestListeners extends MemberProfileModRequestListeners
{
    /**
     * Adds default profile sidebar links.
     *
     * @param array $data
     * @return array
     */
    public static function getSidebarLinks(array $data = array()): array
    {
        /** @var NstMemberUsers $user */
        $user = auth::getAuthUser();

        $data = [];
        $data[] = array(
            "label" => "Dashboard",
            "link"  => app::get()->getRouter()->generate('dashboard_home'),
            'icon' => 'las la-server'
        );

        $data[] = array(
            "label" => "Shift Calendar",
            'icon' => 'las la-calendar',
            "link" => "/",
            "children" => array(
                array(
                    "label" => "Manage Shift Calendar",
                    "link" => app::get()->getRouter()->generate('events_index')
                ),
                array(
                    "label" => "Create Shift",
                    "link" => app::get()->getRouter()->generate('create_shift')
                ),
                array(
                    "label" => "Shift Requests",
                    "link" => app::get()->getRouter()->generate('shift_requests')
                ),
                array(
                    "label" => "Review Shifts",
                    "link" => app::get()->getRouter()->generate('review_shifts')
                ),
            )
        );

        $data[] = array(
            "label" => "Nurses",
            'icon' => 'las la-user-nurse',
            "link" => "/",
            "children" => array(
                array(
                    "label" => "Nurses List",
                    "link" => app::get()->getRouter()->generate('nurse_list')
                ),
                array(
                    "label" => "Do Not Return List",
                    "link" => app::get()->getRouter()->generate('do_not_return')
                )
            )
        );

        $data[] = array(
            "label" => "Review Invoices",
            'icon' => 'las fa-paper-plane',
            "link" => app::get()->getRouter()->generate('provider_invoices')
        );

        // $data[] = array(
        //     "label" => "Invoices",
        //     'icon' => 'las la-user-nurse',
        //     "link" => app::get()->getRouter()->generate('review_shifts')
        // );
        
        if($user->getUserType() == 'Admin') {
            $data[] = array(
                "label" => "Current Pay Period",
                "icon" => "las la-dollar-sign",
                "link" => app::get()->getRouter()->generate('provider_current_pay_period')
            );
        }


//        if($user->getUserType() == 'Admin') {
//            $data[] = array(
//                "label" => "Invoices",
//                "icon" => "las la-dollar-sign",
//                "link" => app::get()->getRouter()->generate('provider_invoices')
//            );
//
//            $data[] = array(
//                "label" => "Payments",
//                "icon" => "las la-piggy-bank",
//                "link" => "/",
//                "children" => array(
//                    array(
//                        "label" => "Payment History",
//                        "link" => app::get()->getRouter()->generate('provider_payment_history')
//                    ),
//                    array(
//                        "label" => "Current Pay Period",
//                        "link" => app::get()->getRouter()->generate('provider_current_pay_period')
//                    ),
//                    array(
//                        "label" => "Unresolved Pay",
//                        "link" => app::get()->getRouter()->generate('provider_unresolved_pay')
//                    )
//                )
//            );
//        }
        return $data;
    }

}
