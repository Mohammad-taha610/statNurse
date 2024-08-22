<?php
namespace sa\system\commands;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\system\AssetBuildManager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Date: 7/26/2018
 *
 * File: BackwardsCompabilityCommand.php
 */
class BuildAssetsCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('system:assets:build')

            // the short description shown while running "php bin/console list"
            ->setDescription('Build the assets directory.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to build the assets directory.')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();
        
        $io = $app->getCliIO();

        $owner = app::get()->getConfiguration()->get('owner_group', true)->getValue();

        $cacheManager = app::getInstance()->getCacheManager();

        if(method_exists($cacheManager, 'addPersistentNamespace')) {
            $cacheManager->addPersistentNamespace('asset_cache_build');
        }

        $cacheLog = $cacheManager->getCache('asset_cache_build');

        /** @var AssetBuildManager $manager */
        $manager = ioc::get('AssetBuildManager');
        $manager->startBuild($cacheLog);

        $build_dir = app::get()->getConfiguration()->get('public_directory')->getValue(). DIRECTORY_SEPARATOR . 'build';

        $command = $this->getApplication()->find('system:permissions:fix');

        $userInput = new ArrayInput(
            array(
                '-o' => $owner
            )
        );

        $returnCode = $command->run($userInput, $output);

        $io->success('The asset build was successfully built.');
        return Command::SUCCESS;
    }

}