<?php

namespace sa\store;

use sacore\application\app;
use sacore\application\ioc;

/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 6/16/2017
 * Time: 2:02 PM
 */
class PostComposerTaskManager
{
    const TASK_TYPE_ONCE = 0;

    const TASK_TYPE_ALWAYS = 1;

    private $preComposerData;

    private $postComposerData;

    /**
     * @var \sa\store\Store Store
     */
    private $store;

    private static $taskObjects = [];

    /**
     * PostComposerTaskManager constructor.
     *
     * @param  \sa\store\Store  $store
     */
    public function __construct($store, $preComposerData, $postComposerData)
    {
        $this->store = $store;
        $this->preComposerData = $preComposerData;
        $this->postComposerData = $postComposerData;
    }

    /**
     * Executes all pending Post Composer
     * module tasks
     */
    public function executeTasks()
    {
        /** @var ComposerTaskRepository $taskRepo */
        $taskRepo = ioc::getRepository('ComposerTask');

        /** @var IPostComposerTask $task */
        foreach (static::$taskObjects as $task) {
            $refl = new \ReflectionClass($task);
            $namespace = $refl->getNamespaceName();

            if (! file_exists($this->store->getSessionDirectory().'/modules/'.str_replace('\\', '/', $namespace).'/composer.json')) {
                continue;
            }

            $composerData = json_decode(file_get_contents($this->store->getSessionDirectory().'/modules/'.str_replace('\\', '/', $namespace).'/composer.json'), true);
            $moduleName = $composerData['name'];

            $preModuleData = $this->getModuleData($this->preComposerData, $moduleName);
            $postModuleData = $this->getModuleData($this->postComposerData, $moduleName);

            $className = $refl->getShortName();
            $taskHasExecuted = $taskRepo->findOneBy(['task_name' => $className]);

            if (! $postModuleData) {
                continue;
            }

            if (($task->getType() == PostComposerTaskManager::TASK_TYPE_ONCE && ! $taskHasExecuted) || $task->getType() == PostComposerTaskManager::TASK_TYPE_ALWAYS) {
                //$task->always();
            }

            if (! $preModuleData && $postModuleData['version_normalized'] >= $task->getMinimumVersion()) {
                if ($task->getType() == PostComposerTaskManager::TASK_TYPE_ONCE && $taskHasExecuted) {
                    continue;
                }

                $task->install();

                /** @var ComposerTask $composerTask */
                $composerTask = ioc::resolve('ComposerTask');
                $composerTask->setTaskName($className);
                app::$entityManager->persist($composerTask);

                continue;
            }

            if (($preModuleData && $postModuleData) && $postModuleData['version_normalized'] >= $task->getMinimumVersion()) {
                if ($task->getType() == PostComposerTaskManager::TASK_TYPE_ONCE && $taskHasExecuted) {
                    continue;
                }

                /** @var ComposerTask $composerTask */
                $composerTask = ioc::resolve('ComposerTask');
                $composerTask->setTaskName($className);
                app::$entityManager->persist($composerTask);

                $task->update();
            }

            if (($preModuleData && $postModuleData) && $postModuleData['version_normalized'] < $task->getMinimumVersion()) {
                if ($task->getType() == PostComposerTaskManager::TASK_TYPE_ONCE && ! $taskHasExecuted) {
                    continue;
                }

                if ($taskHasExecuted) {
                    app::$entityManager->remove($taskHasExecuted);
                }

                $task->downgrade();
            }
        }

        app::$entityManager->flush();
    }

    public static function registerPostRunTask(IPostComposerTask $task)
    {
        PostComposerTaskManager::$taskObjects[] = $task;
    }

    /**
     * @param $name
     */
    private function getModuleData($data, $moduleName)
    {
        $matchedInfo = null;

        foreach ($data as $info) {
            if ($info['name'] == $moduleName) {
                $matchedInfo = $info;
                break;
            }
        }

        return $matchedInfo;
    }
}
