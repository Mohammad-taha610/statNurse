<?php

namespace nst\events\commands;
use nst\events\ShiftService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteShiftCommand extends Command
{
    protected function configure()
    {
        $this->setName('shift:delete_shift')
            ->setDescription('Delete Shift')
            ->setHelp('')
            ->addOption('data', null, InputOption::VALUE_REQUIRED, 'The JSON string of data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shiftData = $input->getOption('data');

        $data = json_decode($shiftData, true);
        $data['command'] = true;
        $shiftService = new ShiftService();
        $shiftService->deleteShift($data, true);

        return Command::SUCCESS;
    }
}
