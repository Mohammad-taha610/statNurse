<?php

namespace sa\store\commands;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Thread;
use sa\store\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Date: 7/26/2018
 *
 * File: BackwardsCompabilityCommand.php
 */
class StoreUpdateAllCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('store:update')

            // the short description shown while running "php bin/console list"
            ->setDescription('Updates one or more modules.')
            ->setDefinition(
                new InputDefinition([
                    //                    new InputOption('name', 's', InputOption::VALUE_REQUIRED, "The config variable to change."),
                    //                    new InputOption('value', 'l', InputOption::VALUE_REQUIRED, "The value to set on the config variable."),

                ])
            )

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to update modules.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = app::get();
        $config = $app->getConfiguration();

        $io = $app->getCliIO();

        $store_enabled = $config->get('enable_store')->getValue();
        if (! $store_enabled) {
            $io->warning('The store is disabled');

            return;
        }

        /** @var Store $store */
        $store = ioc::get('\sa\store\Store');
        if (method_exists(app::get(), 'isPharMode') && app::get()->isPharMode()) {
            $store->setPharInstallerMode();
        }

        $data = $store->getInstalledModules();

        $modulesForTable = [];
        $choices = [];

        foreach ($data['modules'] as $modules) {
            foreach ($modules as $mod) {
                if ($mod['update']) {
                    $modulesForTable[] = [$mod['name'], $mod['installed_version'], $mod['version']];
                    $choices[] = $mod['name'];
                }
            }
        }

        foreach ($data['themes'] as $modules) {
            foreach ($modules as $mod) {
                if ($mod['update']) {
                    $modulesForTable[] = [$mod['name'], $mod['installed_version'], $mod['version']];
                    $choices[] = $mod['name'];
                }
            }
        }

        foreach ($data['api'] as $modules) {
            foreach ($modules as $mod) {
                if ($mod['update']) {
                    $modulesForTable[] = [$mod['name'], $mod['installed_version'], $mod['version']];
                    $choices[] = $mod['name'];
                }
            }
        }

        $choices[] = 'All';

        $io->title('Store Updates');

        $io->table(['System Updates', 'Module Updates'], [[$data['info']['system_updates'], $data['info']['installed_updates']]]);

        $io->table(['Name', 'Installed Version', 'New Version'], $modulesForTable);

        $question = new ChoiceQuestion(
            'Please select the modules you would like to update',
            $choices,
            ''
        );
        $question->setMultiselect(true);

        $selections = $io->askQuestion($question);

        if (in_array('All', $selections)) {
            $store->updateComposerJSONWithAllLatestVersions();
        } else {
            foreach ($selections as $module) {
                $index = array_search($module, $selections);
                $store->updateComposerJSON($module, $modulesForTable[$index][2]);
            }
        }

        $cacheManager = app::getInstance()->getCacheManager();
        if (method_exists($cacheManager, 'addPersistentNamespace')) {
            $cacheManager->addPersistentNamespace('store');
        }
        $storeCacheLog = $cacheManager->getCache('store');

        $thread = new Thread('executeController', 'saStoreController', 'runComposer');
        $thread->run();
        $storeCacheLog->save('composerThreadId', $thread->getId(), 600);
        $storeCacheLog->delete('doctrineThreadId');
        $storeCacheLog->delete('postComposerTaskThreadId');
    }
}
