<?php
namespace sa\system\commands;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\system\AssetBuildManager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Date: 7/26/2018
 *
 * File: BackwardsCompabilityCommand.php
 */
class FlushAssetBuildDirectoryCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('system:assets:flush-build')

            // the short description shown while running "php bin/console list"
            ->setDescription('Flush Build Directory.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to flush the asset build directory by deleting all of its contents.')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();

        $io = $app->getCliIO();

        AssetBuildManager::flushBuildDirectory();

        modRequest::request('system.cache.flush', null, array('asset_combining'));

        $io->success('The build was flushed successfully.');

        return Command::SUCCESS;
    }

}