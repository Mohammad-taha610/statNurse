<?php
namespace sa\system\commands;

use sacore\application\app;
use sacore\application\ioc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Date: 7/26/2018
 *
 * File: BackwardsCompabilityCommand.php
 */
class ClearCacheCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('system:clear-cache')

            // the short description shown while running "php bin/console list"
            ->setDescription('Clear Cache Stores.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to clear one or all cache stores.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();

        $environment = $input->getOption('e');

        $io = $app->getCliIO();

        $cacheManager = $app->getCacheManager();

        $caches = $cacheManager->getCacheNamespaces();

        if (count($caches)==0) {

            $io->caution('The cache is clear.');

            return;
        }

        $caches[] = 'php opcache';
        $caches[] = 'All';


        $cache = $io->choice('What cache would you like to clear?', $caches);
        $controller = ioc::get('saCacheController');

        if ($cache=='All') {
            $cache = $cacheManager->getCacheNamespaces();
            $cache[] = 'php opcache';
        }

        $controller->flushSystemCache($cache);

        $io->success('The cache was flushed/cleared successfully.');

        return Command::SUCCESS;
    }

}