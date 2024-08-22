<?php
/**
 * Date: 3/16/2018
 *
 * File: saClusterNodeAPI.php
 */

namespace sa\system;


use sa\api\api;
use sa\api\apiController;
use sacore\application\app;
use sacore\application\CacheManager;
use sacore\application\Configuration;
use sacore\application\ConfigurationSetting;
use sacore\application\modRequest;

/**
 * Class saClusterNodeAPIController
 * @package sa\system
 */
class saClusterNodeAPIController extends apiController
{

    /**
     * @api
     */
    public function saveCombinedJSFile($urldata, $json) {

        $file = app::get()->getConfiguration()->get('public_directory')->getValue() . '/build/combined/js/' . $json['hash'] . '.js';

        if (!file_exists($file))
            file_put_contents($file, $json['content']);

        $this->api->response(200, ['error' => false]);

    }

    /**
     * @api
     */
    public function saveCombinedCSSFile($urldata, $json) {

        $file = app::get()->getConfiguration()->get('public_directory')->getValue() . '/build/combined/css/' . $json['hash'] . '.css';

        if (!file_exists($file))
            file_put_contents($file, $json['content']);

        $this->api->response(200, ['error' => false]);

    }

    /**
     * @api
     */
    public function flushNodeCache($urldata, $json) {


        $environments = app::get()->getAllAvailableEnvironments();

        if (!isset($environments[ $json['environment'] ])) {
            $this->api->response(200, ['error'=>true] );
            return;
        }

        if (file_exists(app::getAppPath().'/config/'.$environments[ $json['environment'] ]['configfile'])) {

            $liveEnv = app::getEnvironment();
            if ( $liveEnv['name']==$json['environment'] ) {
                $config = app::get()->getConfiguration();
                $cacheManager = app::get()->getCacheManager();
            }
            else
            {
                $config = new Configuration(app::getAppPath() . '/config/' . $environments[$json['environment']]['configfile']);

                $cacheDriver = 'File System';
                $cacheDriverHost = '127.0.0.1';
                $cacheDriverSetting = $config->get('cache_driver');
                if ($cacheDriverSetting)
                    $cacheDriver = $cacheDriverSetting->getValue();

                $cacheDriverHostSetting = $config->get('cache_driver_host');
                if ($cacheDriverHostSetting)
                    $cacheDriverHost = $cacheDriverHostSetting->getValue();

                $cacheManager = new CacheManager($json['environment'], $cacheDriver, $cacheDriverHost, $environments[$json['environment']]['devmode']);
                $cacheManager->addPersistentNamespace('threads');

            }



            $c = new saCacheController();
            $c->flushSystemCache($json['namespaces'], false, $cacheManager);

            $this->api->response(200, ['error' => false]);

        }
        else
        {
            $this->api->response(200, ['error'=>true] );
        }



    }

    /**
     * @api
     */
    public function updateSettings($urldata, $json) {

        $environments = app::get()->getAllAvailableEnvironments();

        if (!isset($environments[ $json['environment'] ])) {
            $this->api->response(200, ['error'=>true] );
            return;
        }

        if (file_exists(app::getAppPath().'/config/'.$environments[ $json['environment'] ]['configfile'])) {
            
            $liveEnv = app::getEnvironment();
            if ( $liveEnv['name']==$json['environment'] ) {
                $config = app::get()->getConfiguration();
            }
            else
            {
                $config = new Configuration(app::getAppPath() . '/config/' . $environments[$json['environment']]['configfile']);
            }

            foreach ($json['changes'] as $k => $v) {
                $config->get($k)->setValue($v);
            }

            $config->persist();

            $this->api->response(200, ['error' => false]);

        }
        else
        {
            $this->api->response(200, ['error'=>true] );
        }

    }

}