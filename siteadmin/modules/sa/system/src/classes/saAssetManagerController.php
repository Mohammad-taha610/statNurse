<?php
/**
 * Date: 10/25/2017
 *
 * File: saAssetManagerController.php
 */

namespace sa\system;


use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\Thread;
use sacore\utilities\notification;
use sacore\utilities\url;

/**
 * Manages assets
 *
 *
 * Class saAssetManagerController
 * @package sa\system
 */
class saAssetManagerController extends saController
{

    /** @var \Doctrine\Common\Cache\CacheProvider $cacheLog */
    protected $cacheLog;


    /**
     * saAssetManagerController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $cacheManager = app::getInstance()->getCacheManager();

        if(method_exists($cacheManager, 'addPersistentNamespace')) {
            $cacheManager->addPersistentNamespace('asset_cache_build');
        }

        $this->cacheLog = $cacheManager->getCache('asset_cache_build');
    }


    /**
     * Deletes the public build directory and returns to the last viewed page
     *
     * @return Redirect
     */
    public function flush() {

        AssetBuildManager::flushBuildDirectory();

        modRequest::request('system.cache.flush', null, array('asset_combining'));

        $notify = new notification();
        $notify->addNotification('success', 'Build directory flushed successfully', 'The build folder has been flushed. ');
        return new Redirect( $_SERVER['HTTP_REFERER'] );

    }

    public static function modRequestCacheRebuild() {

        if (!app::get()->getConfiguration()->get('cache_assets')->getValue())
            return;

        /** @var saAssetManagerController $controller */
        $controller = ioc::staticGet('saAssetManagerController');
        $controller = new $controller();


        $id = $controller->cacheLog->fetch('buildThreadId');
        $status = Thread::getThreadStatus($id);
        
        if ($status != null && !$status['running']) {
            $thread = new Thread('executeController', 'saAssetManagerController', 'buildNow');
            $thread->run();
            $controller->cacheLog->save('buildThreadId', $thread->getId(), 3600);
        } else if($status == null) {
            $thread = new Thread('executeController', 'saAssetManagerController', 'buildNow');
            $thread->run();
            $controller->cacheLog->save('buildThreadId', $thread->getId(), 3600);
        }
    }


    public function build() {

        $id = $this->cacheLog->fetch('buildThreadId');
        $status = Thread::getThreadStatus($id);
        if (!$status['running']) {
            $thread = new Thread('executeController', 'saAssetManagerController', 'buildNow');
            $thread->run();
            $this->cacheLog->save('buildThreadId', $thread->getId(), 3600);
        }
        else
        {
            $notify = new notification();
            $notify->addNotification('warning', 'Already in progress', 'The asset build is already in progress. ');
        }

        $response = new View('cache_console');
        $response->addCSSResources([app::get()->getRouter()->generate('system_css',['file' => 'console_styles.css'])]);
        return $response;
    }

    public function buildNow() {

        /** @var AssetBuildManager $manager */
        $manager = ioc::get('AssetBuildManager');
        $manager->startBuild($this->cacheLog);

    }

    public function buildLog() {



        $output =  $this->cacheLog->fetch('log');

        $id = $this->cacheLog->fetch('buildThreadId');
        $status = Thread::getThreadStatus($id);

        $response = new Json();

        $response->data['thread_status'] = $status;

        $formattedOutput = '';

        if ( $output ) {
            $output = preg_split("#\n|\r|\r\n#", $output);

            $foreground = '';
            $background = '';

            foreach($output as $line) {

                $line = preg_replace_callback("/\[([0-9]{2})m/i", function($matches) {
                    return "\ncolor-".$matches[1]."\n";
                }, $line);

                $parts = preg_split("#\n#", $line);

                $formattedOutput .= '<div>';
                foreach($parts as $part) {

                    $foreground_temp = $foreground;

                    if (strpos($line, 'Aborted')!==false || strpos($line, 'RuntimeException')!==false || strpos($line, 'ErrorException')!==false) {
                        $foreground_temp = 'red';
                    }
                    elseif (strpos($line, 'Nothing to update ')!==false || strpos($line, 'Updating')!==false) {
                        $foreground_temp = 'green';
                    }
                    elseif (strpos($line, 'The package has modified files')!==false || strpos($line, 'Discard changes')!==false) {
                        $foreground_temp = 'orange';
                    }
                    elseif (strpos($line, 'Composer')!==false || strpos($line, 'Doctrine')!==false) {
                        $foreground_temp = 'DarkTurquoise';
                    }

                    $part = str_replace(' ', '&nbsp;', $part);

                    $formattedOutput .= '<span style="color: '.$foreground_temp.'; background-color: '.$background.';">'.$part.'</span>';
                }
                $formattedOutput .= '</div>';
            }

        }

        $response->data['output'] = $formattedOutput;

        return $response;

    }

}