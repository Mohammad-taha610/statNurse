<?php

namespace nst\events\commands;
use nst\events\ShiftService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OvertimeHoursFixCommand extends Command
{


    protected function configure() {
        $this->setName('import:overtime_hours_fix')
            ->setDescription('Import overtime hours');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $service = new ShiftService();

        $response = $service->overtimeHoursFix();

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}