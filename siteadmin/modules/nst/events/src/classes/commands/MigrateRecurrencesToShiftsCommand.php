<?php

namespace nst\events\commands;
use nst\events\ShiftService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateRecurrencesToShiftsCommand extends Command
{


    protected function configure() {
        $this->setName('shifts:migrate_recurrences')
            ->setDescription('Migrate recurrences to normal shifts');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $service = new ShiftService();

        $response = $service->migrateRecurrencesToShifts();

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}