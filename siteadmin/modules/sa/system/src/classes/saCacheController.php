<?php
/**
 * Date: 10/25/2017
 *
 * File: saCacheController.php
 */

namespace sa\system;


use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sa\sa3ApiClient\ApiClientException;
use sa\sa3ApiClient\Sa3ApiClient;
use sacore\utilities\notification;
use sacore\utilities\url;

class saCacheController extends saController
{
    public function flushCache() {

        $notify = new notification();

        if (!class_exists('\sacore\application\CacheManager')) {

            $notify->addNotification('danger', 'Unsupported', ' This feature is not supported on this version of SA3.  Please update the core. ');


            $response = new View('generic');
            $response->data['data'] = '';

        }
        else {
            $cacheManager = app::get()->getCacheManager();

//            if (url::method() == 'POST') {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $cacheMsg = '';
                if (is_array($_POST['cache'])) {
                    $notify->addNotification('success', 'Cache flushed successfully', ' The following caches have been flushed: ' . implode(', ', $_POST['cache']));

                    $this->flushSystemCache($_POST['cache']);
                }

                $response = new Redirect( app::get()->getRouter()->generate('sa_flush_cache') );
            }
            else {

                $caches = $cacheManager->getCacheNamespaces();
                $caches[] = 'php opcache';
                $response = new View( 'cache');
                $response->data['caches'] = $caches;

            }

        }
        


        return $response;
    }

    public static function modRequestFlushSystemCache($namespaces, $replicateToNodes=true, $cacheManager=null) {

        $controller = ioc::get('saCacheController');
        $controller->flushSystemCache($namespaces, $replicateToNodes, $cacheManager);

    }


    /**
     * @param $namespaces - Array containing list of namespaces for cache clearing.
     * @return null
     */
    public function flushSystemCache($namespaces, $replicateToNodes=true, $cacheManager=null) {

        if(!count($namespaces) || !class_exists('\sacore\application\CacheManager')) {
            return;
        }

        $notify = new notification();

        if (!$cacheManager)
            $cacheManager = app::get()->getCacheManager();

        foreach($namespaces as $namespace) {
            if ($namespace=='php opcache') {
                if (function_exists('\opcache_reset')) {
                    \opcache_reset();
                }
            }
            else
            {
                $cacheManager->getCache($namespace)->deleteAll();
            }
        }

        /**
         * Replicate to nodes
         */
        if (count($namespaces)>0 && $replicateToNodes) {
            $nodes = ioc::getRepository('saClusterNode')->findAll();
            /** @var saClusterNode $node */
            foreach ($nodes as $node) {

                try {
                    $client = new Sa3ApiClient($node->getSaApiUrl(), $node->getClientId(), $node->getApiKey());
                    if (!$client->isConnected()) {
                        continue;
                    }

                    $result = $client->custom->sanode->flushNodeCache(['environment'=>$node->getEnvironment(), 'namespaces'=>$namespaces]);
                    if ($result['response']['error']) {
                        $notify->resetNotifications();
                        $notify->addNotification('danger', 'Replication Error', ' The node ' . $node->getSaApiUrl() . ' reported an error syncing cache.');
                    }

                }
                catch(ApiClientException $e) {
                    $notify->resetNotifications();
                    $notify->addNotification('danger', 'Replication Error', ' The node '.$node->getSaApiUrl().' is not available.');
                }


            }
        }
    }
}