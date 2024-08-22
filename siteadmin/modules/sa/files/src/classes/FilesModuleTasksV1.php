<?php

namespace sa\files;

use Doctrine\ORM\Query\ResultSetMapping;
use sacore\application\app;
use sa\store\IPostComposerTask;
use sa\store\PostComposerTaskManager;

/**
 * Class FilesModuleTasksV1
 * @package sa\files
 */
class FilesModuleTasksV1 implements IPostComposerTask {

    /**
     * @return int
     */
    public function getType() {
        return PostComposerTaskManager::TASK_TYPE_ONCE;
    }
    
    /**
     * @return string
     */
    public function getMinimumVersion()
    {
        return '1.0.0.73';
    }

    public function install()
    {
        // DO NOTHING
    }

    public function update()
    {
        $sql = 'UPDATE sa_file SET filename_key = SUBSTR(filename, 1, 7);';
        
        $connection = app::$entityManager->getConnection();
        $connection->executeQuery($sql);
    }

    public function downgrade()
    {
        // DO NOTHING
    }
    
    public function always()
    {
        // DO NOTHING
    }
}