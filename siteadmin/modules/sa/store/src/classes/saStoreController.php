<?php

namespace sa\store;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\Request;
use sacore\application\responses\ISaResponse;
use sacore\application\responses\Json;
use sacore\application\responses\Raw;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\saTemplateEngine\TemplateEngineViewException;
use sacore\application\Thread;
use sa\system\saAuth;
use sa\system\saUser;
use sacore\utilities\notification;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

class saStoreController extends saController
{
    const STORE_CACHE_TTL = 900;

    private $storeCacheLog;

    private $installedJson = null;

    public function __construct()
    {
        parent::__construct();

        $cacheManager = app::getInstance()->getCacheManager();

        if (method_exists($cacheManager, 'addPersistentNamespace')) {
            $cacheManager->addPersistentNamespace('store');
        }

        $this->storeCacheLog = $cacheManager->getCache('store');
    }

    /**
     * @return array[]
     */
    public static function getDefaultResources(): array
    {
        return [[
            'type' => 'css',
            'path' => app::get()->getRouter()->generate('sa_store_css', ['file' => 'styles.css']),
        ]];
    }

    public function storeDisabled(): View
    {
        $notify = new notification();
        $notify->addNotification('warning', 'Notice', 'The store is not enabled on this site or instance.');

        return new View('blank');
    }

    public function store_repo_error(): View
    {
        $notify = new notification();
        $notify->addNotification('danger', 'Error', 'The store does not have any repositories configured.');

        return new View('master', 'blank');
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function store(): View
    {
        $repos = app::get()->getConfiguration()->get('store_repositories')->getValue();

        if (! count($repos)) {
            return $this->store_repo_error();
        }

        /** @var Store $store */
        $store = ioc::get('Store');
        if (app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $data = $store->getInstalledModules();
        $storeEnabled = app::get()->getConfiguration()->get('enable_store')->getValue();

        if (! $storeEnabled) {
            return $this->storeDisabled();
        }

        $view = new View('store', static::viewLocation());
        $view->data['modules'] = $data['modules'];
        $view->data['themes'] = $data['themes'];
        $view->data['apis'] = $data['apis'];
        $view->data['info'] = $data['info'];
        $view->data['system'] = $data['system'];

        return $view;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function saStoreCheckForUpdates(): View
    {
        $store_enabled = app::get()->getConfiguration()->get('enable_store')->getValue();

        if (! $store_enabled) {
            return $this->storeDisabled();
        }

        /** @var Store $store */
        $store = ioc::get('Store');

        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $data = $store->getInstalledModules();

        $view = new View('check_for_updates');
        $view->data['modules'] = $data['modules'];
        $view->data['themes'] = $data['themes'];
        $view->data['info'] = $data['info'];

        return $view;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function details(): View
    {
        $store_enabled = app::get()->getConfiguration()->get('enable_store')->getValue();
        if (! $store_enabled) {
            return $this->storeDisabled();
        }

        /** @var Store $store */
        $store = ioc::get('Store');

        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $module = $store->getModuleDetail($_REQUEST['module'], $_REQUEST['version']);

        $packages = $store->getAvailablePackages();
        $module['other_versions'] = $packages[$_REQUEST['module']]['versions'];
        $module['update'] = false;
        $module['installed'] = false;
        $module['installed_version'] = '';

        $installedVersion = $store->getInstalledDetails($module['name']);
        if ($installedVersion) {
            if ($installedVersion['dist']['reference'] != $module['dist']['reference'] || $installedVersion['version_normalized'] > $module['version_normalized']) {
                $module['update'] = true;
                $module['installed_version'] = $installedVersion['version_normalized'];
            }

            $module['installed'] = true;
        }

        $view = new View('details');
        $view->data['module'] = $module;

        return $view;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function detailPicture(Request $request): Raw
    {
        $file = $request->getRouteParams()->get('filename');

        /** @var Store $store */
        $store = ioc::get('Store');
        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $module = $store->getModuleDetail($_REQUEST['module'], $_REQUEST['version']);

        $arrContextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];

        $repo = $module['path'];
        $image = file_get_contents($repo.'/raw/'.$module['dist']['reference'].'/store/'.$file.'?private_token=vVCVYiHbQq4dLc6LgPv_', false, stream_context_create($arrContextOptions));

        $photo = new Raw(200, 'imag/png');
        $photo->data = $image;

        return $photo;
    }

    /**
     * @return Redirect|View
     */
    public function buy(): ISaResponse
    {
        $storeEnabled = app::get()->getConfiguration()->get('enable_store')->getValue();

        if (! $storeEnabled) {
            return $this->storeDisabled();
        }

        return new Redirect(
            app::get()->getRouter()->generate('sa_module_install').'?module='.$_REQUEST['module'].'&version='.$_REQUEST['version']
        );
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function install(): View
    {
        $store_enabled = app::get()->getConfiguration()->get('enable_store')->getValue();

        if (! $store_enabled) {
            return $this->storeDisabled();
        }

        /** @var Store $store */
        $store = ioc::get('Store');
        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $store->updateComposerJSON($_REQUEST['module'], $_REQUEST['version']);
        $store->runComposer();

        $view = new View('install');
        $view->data['version'] = $_REQUEST['version'];
        $view->data['module'] = $_REQUEST['module'];

        return $view;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function update(): View
    {
        $store_enabled = app::get()->getConfiguration()->get('enable_store')->getValue();

        if (! $store_enabled) {
            return $this->storeDisabled();
        }

        /** @var Store $store */
        $store = ioc::get('Store');
        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }
        $store->updateComposerJSON($_REQUEST['module'], $_REQUEST['version']);
        $store->runComposer();

        $view = new View('master', 'update');
        $view->data['version'] = $_REQUEST['version'];
        $view->data['module'] = $_REQUEST['module'];

        return $view;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function updateAll(): View
    {
        $store_enabled = app::get()->getConfiguration()->get('enable_store')->getValue();

        if (! $store_enabled) {
            return $this->storeDisabled();
        }

        /** @var Store $store */
        $store = ioc::get('Store');
        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }
        $store->updateComposerJSONWithAllLatestVersions();
        $store->runComposer();

        $view = new View('master', 'updateall');
        $view->data['version'] = $_REQUEST['version'];
        $view->data['module'] = $_REQUEST['module'];

        return $view;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function uninstall(): View
    {
        $store_enabled = app::get()->getConfiguration()->get('enable_store')->getValue();

        if (! $store_enabled) {
            return $this->storeDisabled();
        }

        /** @var Store $store */
        $store = ioc::get('Store');
        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }
        $store->updateComposerJSON($_REQUEST['module']);
        $store->runComposer();

        $view = new View('uninstall');
        $view->data['version'] = $_REQUEST['version'];
        $view->data['module'] = $_REQUEST['module'];

        return $view;
    }

    public function log()
    {
        $output = $this->storeCacheLog->fetch('log');

        $composerThread = $this->storeCacheLog->fetch('composerThreadId');
        $doctrineThread = $this->storeCacheLog->fetch('doctrineThreadId');
        $postComposerTaskThread = $this->storeCacheLog->fetch('postComposerTaskThreadId');

        $composerThreadStatus = Thread::getThreadStatus($composerThread);
        $doctrineThreadStatus = Thread::getThreadStatus($doctrineThread);
        $postComposerTaskThreadStatus = Thread::getThreadStatus($postComposerTaskThread);

        $response = new Json();

        $response->data['thread_status'] = [
            'composer' => $composerThreadStatus,
            'doctrine' => $doctrineThreadStatus,
            'postComposerTasks' => $postComposerTaskThreadStatus,
        ];

        $formattedOutput = '';

        $response->data['last_line_status'] = '';
        $converter = new AnsiToHtmlConverter();

        if ($output) {
            $output = preg_split("#\n|\r|\r\n#", $output);

            foreach ($output as $line) {
                if (preg_match("/\[([0-9]{2})m/i", $line)) {
                    $formattedOutput .= '<div>';
                    $formattedOutput .= $converter->convert($line);
                } else {
                    $foreground_temp = 'white';
                    if (strpos($line, 'Aborted') !== false || strpos($line, 'RuntimeException') !== false || strpos($line, 'ErrorException') !== false) {
                        $foreground_temp = 'red';
                    } elseif (strpos($line, 'Nothing to update ') !== false || strpos($line, 'Updating') !== false) {
                        $foreground_temp = 'green';
                    } elseif (strpos($line, 'The package has modified files') !== false || strpos($line, 'Discard changes') !== false) {
                        $foreground_temp = 'orange';
                    } elseif (strpos($line, 'Composer') !== false || strpos($line, 'Doctrine') !== false) {
                        $foreground_temp = 'DarkTurquoise';
                    }

                    $formattedOutput .= '<div style="color: '.$foreground_temp.'">';
                    $formattedOutput .= $line;
                }

                $formattedOutput .= '</div>';
            }

            $emptyRemoved = array_filter($output);
            $response->data['last_line_status'] = strip_tags($converter->convert(end($emptyRemoved)));
        }

        $response->data['output'] = $formattedOutput;

        return $response;
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function runComposer()
    {
        /** @var Store $store */
        $store = ioc::get('Store');

        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $store->executeComposer();
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function runDoctrineSchemaUpdate()
    {
        /** @var Store $store */
        $store = ioc::get('Store');

        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $store->runDoctrineSchemaUpdate();
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function composerPostRunTasks()
    {
        /** @var Store $store */
        $store = ioc::get('Store');

        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $store->composerPostRunTasks();
    }

    /**
     * Fired from store.post.tasks listener
     *
     * Register tasks to be run for each
     * module after Store operations
     *
     * @throws IocDuplicateClassException
     * @throws IocException
     */
    public static function registerPostRunTasks($moduleTasks)
    {
        foreach ($moduleTasks as $task) {
            $taskClass = ioc::get($task);

            if ($taskClass instanceof IPostComposerTask) {
                PostComposerTaskManager::registerPostRunTask($taskClass);
            }
        }
    }

    /**
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws TemplateEngineViewException
     * @throws storeException
     */
    public static function getUpdatesWidget($data): mixed
    {
        /** @var saAuth $saAuth */
        $saAuth = ioc::staticResolve('saAuth');
        /** @var saUser $saUser */
        $saUser = $saAuth::getAuthUser();

        /** @var Store $store */
        $store = ioc::get('Store');
        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }
        $moduleData = $store->getInstalledModules();
        $availableUpdates = $moduleData['info']['system_updates'] + $moduleData['info']['installed_updates'];

        $updateWidgetView = new View('sa_store_update_widget');

        if ($availableUpdates == 0) {
            //$data['html'] = 'Your system is up to date!';
            $updateWidgetView->data['type'] = 'info';
            $updateWidgetView->data['message'] = 'Your system is up to date!';
        } elseif (($saUser->getUserType() != saUser::TYPE_DEVELOPER && $saUser->getUserType() != saUser::TYPE_SUPER_USER) || ! app::get()->getConfiguration()->get('enable_store_update_widget')->getValue()) {
            //$data['html'] = 'Your system is up to date!';
            $updateWidgetView->data['type'] = 'info';
            $updateWidgetView->data['message'] = 'Updates are not available.';
            $availableUpdates = 0;
        } else {
            //$data['html'] = $updateWidgetView->getHTML();
            $updateWidgetView->data['type'] = 'success';
            $updateWidgetView->data['message'] = 'New Site Administrator module updates are available.';
        }

        $updateWidgetView->data['total_updates'] = $availableUpdates;
        $data['init_js'] = '';
        $data['destroy_js'] = '';
        $data['html'] = $updateWidgetView->getHTML();
        $data['display_name'] = 'Updates';
        $data['display_icon'] = 'fa fa-area-chart orange';
        $data['id'] = 'sa.dashboard.updates';
        $data['default_location'] = 'dashboard-widgets-right-col';
        $data['default_priority'] = '10';
        $data['auto_refresh'] = true;
        $data['auto_refresh_interval'] = 30000;
        $data['show_header'] = true;

        return $data;
    }

    /**
     * @return array
     *
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws storeException
     */
    public function ajaxGetInformation()
    {
        /** @var Store $store */
        $store = ioc::get('Store');

        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        return $store->getInstalledModules(true);
    }
}
