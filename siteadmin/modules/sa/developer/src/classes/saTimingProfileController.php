<?php
/**
 * Date: 5/10/2018
 *
 * File: saTimingProfileController.php
 */

namespace sa\developer;

use sacore\application\app;
use sacore\application\responses\View;
use sacore\application\saController;

class saTimingProfileController extends saController
{
    public function showTiming()
    {
        $view = new View('timing');

        return $view;
    }

    public function showTimingAjax()
    {
        $profiles = app::get()->getCacheManager()->getCache('sa_timing_profile')->fetch('timing_profile');

        $toReturn = [];

        uasort($profiles, function ($a, $b) {
            if ($a['instance_start'] == $b['instance_start']) {
                return 0;
            }

            return ($a['instance_start'] > $b['instance_start']) ? -1 : 1;
        });

        foreach ($profiles as $profile) {
            uasort($profile['timings'], function ($a, $b) {
                if ($a['order'] == $b['order']) {
                    return 0;
                }

                return ($a['order'] < $b['order']) ? -1 : 1;
            });

            $toReturn[] = $profile;
        }

        return ['instances' => $toReturn];
    }
}
