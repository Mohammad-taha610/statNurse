<?php

namespace sa\developer\commands;

use sacore\application\app;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Date: 7/26/2018
 *
 * File: BackwardsCompabilityCommand.php
 */
class ShowInstalledPackagesCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('developer:show:installed-packages')

            // the short description shown while running "php bin/console list"
            ->setDescription('Shows Installed Packages.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command displays all the installed packages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = app::get()->getCliIO();

        $io->title('Installed Packages');

        $packages = json_decode(file_get_contents(app::getAppPath().'/vendor/composer/installed.json'), true);

        usort($packages, function ($a, $b) {
            if ($a['name'] == $b['name']) {
                return 0;
            }

            return ($a['name'] < $b['name']) ? -1 : 1;
        });

        $packagesForTables = [];

        foreach ($packages as $pkg) {
            $packagesForTables[] = [$pkg['name'], $pkg['version_normalized'], $pkg['description']];
        }

        $io->table(
            ['Name', 'Version', 'Description'],
            $packagesForTables
        );

        return COMMAND::SUCCESS;
    }
}
