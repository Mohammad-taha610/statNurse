<?php

namespace nst\member;

use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;

class LoginAttemptService
{

    /**
     * @param $nurseUsername
     * @return void
     */
    public static function newLoginAttempt($nurseUsername) {
        /**  @var LoginAttempt $login_attempts */
        $login_attempts = ioc::resolve('LoginAttempt');

        $login_attempts->setUsername($nurseUsername);
        $login_attempts->setAttemptTime(new \sacore\application\DateTime('now', app::getInstance()->getTimeZone()));

        app::$entityManager->persist($login_attempts);
        app::$entityManager->flush();
    }

    public static function isNurseLockedOut($nurseUsername)
    {
        /** @var LoginAttempt[] $loginAttempts */
        $loginAttempts = ioc::getRepository('LoginAttempt')->findBy(['username' => $nurseUsername]);

        // gets array of attempts in last {$minutes} minutes
        $getAttempts = function($minutes) use ($loginAttempts) {
            return count(
                array_filter(
                    $loginAttempts,
                    function ($attempt) use ($minutes) {
                        $now = new DateTime('now');
                        $timeSinceLastLogin = $now->diff($attempt->getAttemptTime());
                        return $timeSinceLastLogin->i < $minutes && $timeSinceLastLogin->h*60 < $minutes && $timeSinceLastLogin->days == 0;
                    }
                )
            );
        };

        $minuteIntervals = [5, 10, 60, 24*60];
        $maxAllowedForInterval = [3, 5, 6, 7];

        for ($i = 0; $i < count($minuteIntervals); $i++) {
            $interval = $minuteIntervals[$i];
            $maxAttempts = $maxAllowedForInterval[$i];
            $attempts = $getAttempts($interval);
            if ($attempts > $maxAttempts) {
                return true;
            }
        }

        return false;
    }

    public static function clearLoginAttempts($nurseUsername) {
        /** @var LoginAttempt[] $loginAttempts */
        $loginAttempts = ioc::getRepository('LoginAttempt')->findBy(['username' => $nurseUsername]);

        foreach ($loginAttempts as $attempt) {
            app::$entityManager->remove($attempt);
        }
        app::$entityManager->flush();
    }
}