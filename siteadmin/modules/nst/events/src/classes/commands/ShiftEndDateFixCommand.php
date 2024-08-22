<?php

namespace nst\events\commands;
use nst\events\ShiftService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShiftEndDateFixCommand extends Command
{


    protected function configure() {
        $this->setName('shifts:fix_end_datetimes')
            ->setDescription('Checks all shifts today and beyond and corrects end datetime if it predates start datetime');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $service = new ShiftService();

        $response = $service->fixShiftEndDatetimes();

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}