<?php

namespace nst\events\commands;

use nst\events\ShiftService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AutomaticClockOutCommand extends Command
{

    protected function configure() {
        $this->setName('cron:automatic_clock_out')
            ->setDescription('Clock out any nurses from shifts at least 6 hours old.');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $service = new ShiftService();

        $response = $service->automaticClockOutCron();

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}