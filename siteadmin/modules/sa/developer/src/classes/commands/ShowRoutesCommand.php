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
class ShowRoutesCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('developer:show:routes')

            // the short description shown while running "php bin/console list"
            ->setDescription('Shows routes.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command displays all the current routes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = app::get()->getCliIO();

        $io->title('Show Routes');

        $routes = app::getInstance()->getRoutes();
        $dataForTable = [];

        foreach ($routes as $method) {
            foreach ($method as $priorityGroup) {
                foreach ($priorityGroup as $routeGroup) {
                    /** @var \sacore\application\route $route */
                    foreach ($routeGroup as $route) {
                        if (is_object($route)) {
                            $dataForTable[] = [
                                //$route->id,
                                //                                $route->name,
                                $route->getCleanRoute(),
                                $route->method,
                                $route->getAction(),
                                //$route->getRouteType()
                            ];
                        }
                    }
                }
            }
        }

        $io->table(
            ['Route', 'Method', 'Action'],
            $dataForTable
        );
    }
}
