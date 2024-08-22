<?php
/**
 * Date: 12/22/2015
 *
 * File: Store.php
 */

namespace sa\store;

use sacore\application\app;
use sacore\application\Event;
use sacore\application\Exception;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Thread;
use sacore\utilities\doctrineUtils;

class Store
{
    private $packagesFromJSON = null;

    private $storeCacheLog;

    private $session_directory = null;

    private $installer_mode = false;

    const STORE_CACHE_TTL = 900;

    public function __construct()
    {
        $cacheManager = app::getInstance()->getCacheManager();
        if (method_exists($cacheManager, 'addPersistentNamespace')) {
            $cacheManager->addPersistentNamespace('store');
        }
        $this->storeCacheLog = $cacheManager->getCache('store');
        $this->session_directory = app::getAppPath();
    }

    /**
     * Run composer in a thread
     *
     * @throws Exception
     */
    public function runComposer()
    {
        $thread = new Thread('executeController', 'saStoreController', 'runComposer');
        $thread->run();
        $this->storeCacheLog->save('composerThreadId', $thread->getId(), saStoreController::STORE_CACHE_TTL);
        $this->storeCacheLog->delete('doctrineThreadId');
        $this->storeCacheLog->delete('postComposerTaskThreadId');
    }

    /**
     * @param  bool  $newline
     */
    public function writeToStoreLog($message, $newline = true)
    {
        $log = $this->storeCacheLog->fetch('log');
        $log .= $message.($newline ? "\n" : '');
        $this->storeCacheLog->save('log', $log, saStoreController::STORE_CACHE_TTL);
    }

    public function setSessionDirectory($directory)
    {
        $this->session_directory = $directory;
    }

    /**
     * @throws storeException
     */
    public function setPharInstallerMode()
    {
        if (! app::get()->isPharMode()) {
            throw new storeException('The application is not running in a phar');
        }

        $this->installer_mode = true;

        $physical_path = getcwd();
        $physical_path = explode('/', $physical_path);
        array_pop($physical_path);
        $physical_path = implode('/', $physical_path).'/siteadmin';
        if (! file_exists($physical_path)) {
            mkdir($physical_path, 0755);
        }
        $this->setSessionDirectory($physical_path);
    }

    /**
     * @param  false  $forceRefresh
     * @return array
     */
    public function getAvailablePackages($forceRefresh = false)
    {
        $packages = ['packages' => []];

        $repositories = app::get()->getConfiguration()->get('store_repositories')->getValue();

        if (! $this->packagesFromJSON) {
            if (! $this->storeCacheLog->contains('composer-packages-time') || $forceRefresh) {
                foreach ($repositories as $repo) {
                    $includesJson = json_decode(file_get_contents($repo.'/packages.json'), true);

                    foreach ($includesJson['includes'] as $include => $data) {
                        $individualRepoPackages = json_decode(file_get_contents($repo.'/'.$include), true);

                        $packages['packages'] = array_merge($packages['packages'], $individualRepoPackages['packages']);
                    }
                }

                if (count($packages['packages']) > 0) {
                    $this->storeCacheLog->save('composer-packages', $packages, 300);
                    $this->storeCacheLog->save('composer-packages-time', time(), 300);
                    $this->storeCacheLog->save('composer-repos-hash', md5(serialize($repositiories)), 300);
                }
            } else {
                $packages = $this->storeCacheLog->fetch('composer-packages');
            }
        } else {
            $packages = $this->packagesFromJSON;
        }

        $this->packagesFromJSON = $packages;
        $allPackages = [];

        foreach ($packages['packages'] as $packageName => $package) {
            $name = null;

            foreach ($package as $version => $info) {
                $name = $info['name'];

                if (! $name) {
                    continue;
                }

                $info['published'] = $info['extra']['store']['published'] ? true : false;
                $allPackages[$name]['versions'][$version] = $info;

                if ($info['version'] > $allPackages[$name]['latest_version']['version'] && strpos($info['version'], '-dev') === false && strpos($info['version'], 'dev-') === false) {
                    $allPackages[$name]['latest_version'] = $info;
                }
            }
        }

        if (count($allPackages) == 0 && ! $forceRefresh) {
            $allPackages = $this->getAvailablePackages(true);
        }

        $hash = $this->storeCacheLog->contains('composer-repos-hash') ? $this->storeCacheLog->fetch('composer-repos-hash') : '';
        if ($hash != md5(serialize($repositories)) && ! $forceRefresh) {
            $allPackages = $this->getAvailablePackages(true);
        }

        return $allPackages;
    }

    /**
     * @return array[]
     */
    public function getAvailableModules()
    {
        $packages = $this->getAvailablePackages();

        $modules = [];
        $themes = [];
        $other = [];

        foreach ($packages as $package) {
            $package = $package['latest_version'];

            if ($package['type'] == 'siteadmin-module' && $package['published']) {
                $modules[] = $package;
            } elseif ($package['type'] == 'siteadmin-theme' && $package['published']) {
                $themes[] = $package;
            } else {
                $other[] = $package;
            }
        }

        return ['modules' => $modules, 'themes' => $themes, 'other' => $other];
    }

    /**
     * @param  false  $forceRefresh
     */
    public function getInstalledModules($forceRefresh = false): array
    {
        $packages = $this->getAvailablePackages($forceRefresh);

        $modules = [];
        $themes = [];
        $apis = [];
        $system = [];
        $other = [];

        $info['installed_updates'] = 0;
        $info['system_updates'] = 0;
        $info['installed_modules'] = 0;
        $info['installed_themes'] = 0;
        $info['available_packages'] = 0;

        foreach ($packages as $packageInfo) {
            $installedVersion = $this->getInstalledDetails($packageInfo['latest_version']['name']);

            $package = [];
            $frozen_updates = false;

            foreach ($packageInfo['versions'] as $packageVersion) {
                if (strpos($packageVersion['version'], '-dev') !== false || strpos($packageVersion['version'], 'dev-') !== false) {
                    continue;
                }

                if ($installedVersion && isset($packageVersion['extra']['store']['min_version_update_lock']) && $packageVersion['extra']['store']['min_version_update_lock'] > $installedVersion['version']) {
                    $frozen_updates = true;

                    continue;
                }

                if (version_compare($packageVersion['version'], $package['version']) > 0) {
                    $package = $packageVersion;
                }
            }

            $package['update'] = false;
            $package['installed'] = false;
            $package['installed_version'] = '';
            $package['frozen_updates'] = $frozen_updates;

            if ($installedVersion && strpos($installedVersion['version'], 'dev-') !== false) {
                $package = $packageInfo['versions'][$installedVersion['version']];

                if ($installedVersion['dist']['reference'] != $package['dist']['reference']) {
                    $package['update'] = true;
                }

                $package['installed_version'] = $installedVersion['version'];
                $package['installed'] = true;
                $info['installed_packages']++;
            } elseif ($installedVersion) {
                if ($installedVersion['dist']['reference'] != $package['dist']['reference'] || $installedVersion['version'] > $package['version']) {
                    $package['update'] = true;
                }

                $package['installed_version'] = $installedVersion['version'];
                $package['installed'] = true;
                $info['installed_packages']++;
            }

            $categoryType = $package['type'];

            if (isset($package['extra']['store']['type'])) {
                $categoryType = $package['extra']['store']['type'];
            }

            $subtype = 'none';
            if (isset($package['extra']['store']['sub-type'])) {
                $subtype = $package['extra']['store']['sub-type'];
            }

            $store_only_show_installed = app::get()->getConfiguration()->get('store_only_show_installed')->getValue();

            if (! $package['installed'] && $store_only_show_installed) {
                // DO NOTHING
            } elseif ($categoryType == 'siteadmin-module' && $package['published']) {
                $modules[$subtype][] = $package;
                $info['available_packages']++;

                if ($package['installed']) {
                    $info['installed_modules']++;
                }

                if ($package['update']) {
                    $info['installed_updates']++;
                    $info['siteadmin-module-updates']++;
                }
            } elseif ($categoryType == 'siteadmin-theme' && $package['published']) {
                $themes[$subtype][] = $package;
                $info['available_packages']++;

                if ($package['installed']) {
                    $info['installed_themes']++;
                }

                if ($package['update']) {
                    $info['installed_updates']++;
                    $info['siteadmin-theme-updates']++;
                }
            } elseif ($categoryType == 'siteadmin-api' && $package['published']) {
                $apis[$subtype][] = $package;
                $info['available_packages']++;

                if ($package['installed']) {
                    $info['installed_modules']++;
                }

                if ($package['update']) {
                    $info['installed_updates']++;
                    $info['siteadmin-api-updates']++;
                }
            } elseif ($package['name'] == 'nursestat/sacore' || $package['name'] == 'sa/module/system' || $package['name'] == 'sa/coreviews') {
                $system[$subtype][] = $package;
                $info['available_packages']++;

                if ($package['update']) {
                    $info['system_updates']++;
                }
            } else {
                $other[$subtype][] = $package;
            }
        }

        $storeInfo = null;
        foreach ($modules['none'] as $k => $v) {
            if ($v['name'] == 'sa/module/store') {
                $storeInfo = $modules['none'][$k];
                break;
            }
        }

        // FORCE THE STORE TO UPDATE FIRST
        if ($storeInfo['version'] != $storeInfo['installed_version']) {
            $apis = [];
            $other = [];
            $themes = [];
            $info['installed_updates'] = 1;
            $info['system_updates'] = 0;
            $modules = [
                'none' => [
                    $storeInfo,
                ],
            ];
        }

        return [
            'modules' => $modules,
            'themes' => $themes,
            'apis' => $apis,
            'other' => $other,
            'system' => $system,
            'info' => $info,
        ];
    }

    /**
     * @return mixed
     *
     * @throws storeException
     */
    public function getModuleDetail($moduleName, $versionNumber)
    {
        $packages = $this->getAvailablePackages();

        $module = $packages[$moduleName]['versions'][$versionNumber];

        if (empty($module)) {
            throw new storeException('Unable to retrieve that module: '.$moduleName.' Version: '.$versionNumber);
        }

        return $module;
    }

    /**
     * @return false|mixed
     */
    public function getInstalledDetails($moduleName)
    {
        if (empty($this->installedJson)) {
            $this->installedJson = json_decode(file_get_contents($this->session_directory.'/vendor/composer/installed.json'), true);
        }

        foreach ($this->installedJson['packages'] as $installedPackage) {
            if ($installedPackage['name'] == $moduleName) {
                return $installedPackage;
            }
        }

        return false;
    }

    public function updateComposerJSONWithAllLatestVersions()
    {
        $data = $this->getInstalledModules();

        $repositiories = app::get()->getConfiguration()->get('store_repositories')->getValue();

        if (! file_exists($this->session_directory.'/composer.json')) {
            $composerJson['repositories'] = [];
            foreach ($repositiories as $repo) {
                $composerJson['repositories'][] = [
                    'type' => 'composer',
                    'url' => $repo,
                ];
            }

            $composerJson['require'] = [
                'sa/siteadmin' => '>=3.1.1.968',
                'sa/module/store' => '1.*',
                'sa/module/dashboard' => '1.*',
            ];

            file_put_contents($this->session_directory.'/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
        }

        $composerJson = json_decode(file_get_contents($this->session_directory.'/composer.json'), true);
        $composerJson['repositories'] = [];

        foreach ($repositiories as $repo) {
            $composerJson['repositories'][] = [
                'type' => 'composer',
                'url' => $repo,
            ];
        }

        foreach ($data as $name => $type) {
            if ($name == 'info') {
                continue;
            }

            foreach ($type as $subtype) {
                foreach ($subtype as $package) {
                    if (isset($composerJson['require'][$package['name']]) && $package['update']) {
                        $composerJson['require'][$package['name']] = strpos($package['version'], 'dev-') !== false ? $package['version'].' as 9.9.9.9' : $package['version'];
                    }
                }
            }
        }

        file_put_contents($this->session_directory.'/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
    }

    /**
     * @param  null  $version
     */
    public function updateComposerJSON($module, $version = null)
    {
        $repositiories = app::get()->getConfiguration()->get('store_repositories')->getValue();

        $composerJson = json_decode(file_get_contents($this->session_directory.'/composer.json'), true);

        $composerJson['repositories'] = [];
        foreach ($repositiories as $repo) {
            $composerJson['repositories'][] = [
                'type' => 'composer',
                'url' => $repo,
            ];
        }

        if (! $version) {
            unset($composerJson['require'][$module]);
        } else {
            $composerJson['require'][$module] = strpos($version, 'dev-') !== false ? $version.' as 9.9.9.9' : $version;
        }

        file_put_contents($this->session_directory.'/composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
    }

    /**
     * Run composer in a blocking state
     * use runComposer to run in a thread.
     *
     * @param  bool  $dontSpinThreads
     *
     * @throws Exception
     * @throws ModRequestAuthenticationException
     */
    public function executeComposer($dontSpinThreads = false)
    {
        if ($this->storeCacheLog->contains('operations_running')) {
            $running = $this->storeCacheLog->fetch('operations_running');
            if ($running) {
                return;
            }
        }

        $this->storeCacheLog->save('operations_running', true, saStoreController::STORE_CACHE_TTL);
        $this->storeCacheLog->delete('log');

        $this->writeToStoreLog('Store Operations will only run a single thread.  Returning to this screen will display current progress. ');
        $this->writeToStoreLog('Composer Operations Beginning');

        set_time_limit(600);
        ini_set('memory_limit', '2000M'); // COMPOSER REQUIRES LOTS OF MEMORY

        if (file_exists($this->session_directory.'/vendor/composer/installed.json')) {
            copy($this->session_directory.'/vendor/composer/installed.json', app::get()->getConfiguration()->get('tempDir')->getValue().'/composerPreInstall.json');
        }

        $allow_cli_composer = app::get()->getConfiguration()->get('allow_cli_composer')->getValue();

        $this->writeToStoreLog('Preparing Composer as an include');

        if (file_exists(app::get()->getConfiguration()->get('tempDir')->getValue().'/composer/vendor/autoload.php') == true) {
            // Do Nothing
        } else {
            if (! file_exists($this->session_directory.'/composer.phar') || empty($_SESSION['composer_last_download']) || (time() - $_SESSION['composer_last_download']) > 86400) {
                $_SESSION['composer_last_download'] = time();
                file_put_contents($this->session_directory.'/composer.phar', file_get_contents('https://pkg.elinkstaging.com/composer-archive/composer.phar'));
                $this->writeToStoreLog('Downloading Composer');
            }

            $composerPhar = new \Phar($this->session_directory.'/composer.phar');
            $this->writeToStoreLog('Extracting Composer');
            $composerPhar->extractTo(app::get()->getConfiguration()->get('tempDir')->getValue().'/composer');
        }

        require_once app::get()->getConfiguration()->get('tempDir')->getValue().'/composer/vendor/autoload.php';

        putenv('COMPOSER_HOME='.app::get()->getConfiguration()->get('tempDir')->getValue().'/composer/vendor/bin/composer');

        $input = new \Symfony\Component\Console\Input\ArrayInput(['command' => 'update', '-d' => $this->session_directory]);

        $output = new ComposerStoreOutput(ComposerStoreOutput::VERBOSITY_VERBOSE, true);

        $output->setWriteCache($this->storeCacheLog);
        $application = new \Composer\Console\Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exiting the script
        $application->run($input, $output);

        $this->writeToStoreLog('Composer Operations Done');

        $app = app::get();

        if (method_exists($app, 'invalidateModuleCache')) {
            $app->invalidateModuleCache();
        } else {
            unset($_SESSION['moduleCache']);
        }

        if ($dontSpinThreads) {
            return;
        }

        try {
            /** @var \Doctrine\DBAL\Configuration $config */
            $config = app::$entityManager->getConnection()->getConfiguration();
            $config->getMetadataCacheImpl()->deleteAll();
            $config->getQueryCacheImpl()->deleteAll();
            $config->getResultCacheImpl()->deleteAll();
        } catch(\Exception $e) {
        }

        modRequest::request('assets.rebuild');
        modRequest::request('system.cache.flush', ['php opcache']);

        // wait for disk operations to complete
        sleep(2);

        $thread = new Thread('executeController', 'saStoreController', 'runDoctrineSchemaUpdate');
        $thread->run();
        $this->storeCacheLog->save('doctrineThreadId', $thread->getId(), saStoreController::STORE_CACHE_TTL);

        sleep(2);

        // Retry doctrine if the system has crashed on initial try
        $error_tries = 0;
        $queue_tries = 0;

        while (true) {
            $doctrineThreadStatus = Thread::getThreadStatus($thread->getId());

            if ($error_tries > 5) {
                break;
            }

            if ($queue_tries > 15) {
                break;
            }

            if ($doctrineThreadStatus['has_run']) {
                break;
            }

            if ($doctrineThreadStatus['running'] || $doctrineThreadStatus['queued']) {
                sleep(2);
                $queue_tries++;

                continue;
            }

            if ($doctrineThreadStatus['has_errors']) {
                $thread->run();
                $this->writeToStoreLog('Doctrine Operations Retry #'.($error_tries + 1));
                sleep(2);
                $doctrineThreadStatus = Thread::getThreadStatus($thread->getId());
                $error_tries++;
            }
        }

        if ($doctrineThreadStatus['has_run'] && $doctrineThreadStatus['has_errors']) {
            $this->writeToStoreLog('Failed to run Doctrine Operations, giving up. Attempted to run, but it resulted in errors.');
            $this->storeCacheLog->save('operations_running', false, saStoreController::STORE_CACHE_TTL);
        } elseif ($queue_tries > 15) {
            $this->writeToStoreLog('Failed to run Doctrine Operations, giving up. It was queued to run, but never started.');
            $this->storeCacheLog->save('operations_running', false, saStoreController::STORE_CACHE_TTL);
        }
    }

    public function runDoctrineSchemaUpdate()
    {
        try {
            /** @var \Doctrine\DBAL\Configuration $config */
            $config = app::$entityManager->getConnection()->getConfiguration();
            $config->getMetadataCacheImpl()->deleteAll();
            $config->getQueryCacheImpl()->deleteAll();
            $config->getResultCacheImpl()->deleteAll();
        } catch(\Exception $e) {
        }

        set_time_limit(300);
        $this->writeToStoreLog('Doctrine Operations Beginning');

        $result = doctrineUtils::updateSchema();
        $this->writeToStoreLog($result);

        $this->writeToStoreLog('Composer &amp; Doctrine Operations Complete');

        $thread = new Thread('executeController', 'saStoreController', 'composerPostRunTasks');
        $thread->run();
        $this->storeCacheLog->save('postComposerTaskThreadId', $thread->getId());
    }

    /**
     * Runs after composer completes
     * operations for store module changes
     */
    public function composerPostRunTasks()
    {
        $preComposerData = [];
        $postComposerData = [];

        $tempDir = app::get()->getConfiguration()->get('tempDir')->getValue();

        if (file_exists($tempDir.'/composerPreInstall.json')) {
            $preComposerData = json_decode(file_get_contents($tempDir.'/composerPreInstall.json'), true);
        }

        if (file_exists($this->session_directory.'/vendor/composer/installed.json')) {
            $postComposerData = json_decode(file_get_contents($this->session_directory.'/vendor/composer/installed.json'), true);
        }

        set_time_limit(300);

        $this->writeToStoreLog('Post Composer Tasks Running');

        $taskManager = new PostComposerTaskManager($this, $preComposerData, $postComposerData);
        $taskManager->executeTasks();

        $this->writeToStoreLog('Post Composer Tasks Complete');

        $this->storeCacheLog->save('operations_running', false, saStoreController::STORE_CACHE_TTL);

        Event::fire('post.composer.tasks');
    }

    public function exec_enabled(): bool
    {
        $disabled = explode(',', ini_get('disable_functions'));

        return ! in_array('exec', $disabled);
    }

    public function getSessionDirectory(): string
    {
        return $this->session_directory;
    }
}
