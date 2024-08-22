<?php

namespace sa\developer\commands;

use sacore\application\app;
use sacore\application\ioc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Date: 7/26/2018
 *
 * File: BackwardsCompabilityCommand.php
 */
class ShowIOCCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('developer:show:ioc')

            // the short description shown while running "php bin/console list"
            ->setDescription('Shows IOC.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command displays all the current ioc classes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = app::get()->getCliIO();

        $io->title('Show IOC');

        $classes = ioc::getRegisteredClasses();

        $dataForTable = [];

        foreach ($classes as $key => $class) {
            if (empty($key)) {
                continue;
            }

            $resolved = 'Not Resolved';
            $discovered = '';
            if (isset($class['resolved']['full_class'])) {
                $resolved = $class['resolved']['full_class'];
                $discovered = $class['resolved']['discovered_by'];
            }

            $dataForTable[] = [$key, $resolved, $discovered, count($class['classes'])];
        }

        usort($dataForTable, function ($a, $b) {
            if ($a > $b) {
                return 1;
            } elseif ($a < $b) {
                return -1;
            } else {
                return 0;
            }
        });

        $io->table(
            ['Request', 'Resolved', 'Discovered By', '# Classes'],
            $dataForTable
        );
    }
}
