<?php
/**
 * Date: 7/25/2018
 *
 * File: BruteForceManager.php
 */

namespace sa\system;


use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\modRequest;
use sacore\utilities\IP;

/**
 * Class BruteForceManager
 * @package sa\system
 */
class BruteForceManager
{

    /** @var \Doctrine\Common\Cache\CacheProvider */
    protected static $ip_cache;

    /**
     * Track a possible offense
     * @param null $ip
     * @throws \Exception
     */
    public static function track($ip) {

        if (!static::$ip_cache) {
            app::get()->getCacheManager()->addPersistentNamespace('brute_force_ips');
            static::$ip_cache = app::get()->getCacheManager()->getCache('brute_force_ips');
        }


        if ( static::$ip_cache->contains( $ip ) )
        {
            $invalid_attempts = static::$ip_cache->fetch( $ip );
            $invalid_attempts['attempts']++;
            $invalid_attempts['datetime'] = new DateTime();
            static::$ip_cache->save( $ip, $invalid_attempts );
        }
        else
        {
            static::$ip_cache->save( $ip, ['attempts'=>1, 'datetime'=>new DateTime(), 'emailed_5'=>false, 'emailed_30'=>false, 'emailed_in'=>false ] );
        }

    }

    /**
     * Track a possible offense
     * @param null $ip
     * @return bool
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    public static function isTrusted($ip)
    {
        $config = app::get()->getConfiguration();

        $attempts_count_5_minute_block = $config->get('brute_force_lockout_attempts')->getValue();
        $attempts_count_30_minute_block = $attempts_count_5_minute_block * 1.5;
        $attempts_count_indefinite_block = $attempts_count_5_minute_block * 2;


        $enabled = $config->get('brute_force_check_enabled')->getValue();
        $site_email = $config->get('site_email')->getValue();
        $site_name = $config->get('site_name')->getValue();

        if (!$enabled)
            return true;


        $now = new DateTime();


        if (!static::$ip_cache) {
            app::get()->getCacheManager()->addPersistentNamespace('brute_force_ips');
            static::$ip_cache = app::get()->getCacheManager()->getCache('brute_force_ips');
        }

        $data = static::$ip_cache->contains($ip) ? static::$ip_cache->fetch($ip) : null;

        if ($data) {
            $difference = $data['datetime']->diff($now);
            $minutes = $difference->days * 24 * 60;
            $minutes += $difference->h * 60;
            $minutes += $difference->i;

            if ($data['attempts'] == $attempts_count_5_minute_block && $minutes <= 5) {

                $body = '<h1>'.$site_name.' - Brute Force Attempt Blocked</h1>';
                $body .= '<h3>'.$ip.' was blocked for 5 minutes. This is the First Block.</h3>';

                if (!$data['emailed_5']) {
                    modRequest::request('messages.sendEmail', array('to' => $site_email, 'body' => $body, 'subject' => $site_name.' - Brute Force Blocked'));
                    $data['emailed_5'] = true;
                    static::$ip_cache->save($ip, $data);
                }

                return false;
            }

            if ($data['attempts'] == $attempts_count_30_minute_block && $minutes <= 30) {

                $body = '<h1>'.$site_name.' - Brute Force Attempt Blocked</h1>';
                $body .= '<h3>'.$ip.' was blocked for 30 minutes. This is the Second Block.</h3>';

                if (!$data['emailed_30']) {
                    modRequest::request('messages.sendEmail', array('to' => $site_email, 'body' => $body, 'subject' => $site_name.' - Brute Force Blocked'));
                    $data['emailed_30'] = true;
                    static::$ip_cache->save($ip, $data);
                }

                return false;
            }

            if ($data['attempts'] >= $attempts_count_indefinite_block) {

                $body = '<h1>'.$site_name.' - Brute Force Attempt Blocked</h1>';
                $body .= '<h3>'.$ip.' was blocked indefinitely. This is the Third Block.</h3>';

                if (!$data['emailed_in']) {
                    modRequest::request('messages.sendEmail', array('to' => $site_email, 'body' => $body, 'subject' => $site_name.' - Brute Force Blocked'));
                    $data['emailed_in'] = true;
                    static::$ip_cache->save($ip, $data);
                }

                return false;
            }
        }

        return true;

    }

    /**
     * Track a possible offense
     * @param null $ip
     */
    public static function forgive($ip)
    {

        if (!static::$ip_cache) {
            app::get()->getCacheManager()->addPersistentNamespace('brute_force_ips');
            static::$ip_cache = app::get()->getCacheManager()->getCache('brute_force_ips');
        }


        if (static::$ip_cache->contains($ip)) {
            static::$ip_cache->delete($ip);
        }

    }

}