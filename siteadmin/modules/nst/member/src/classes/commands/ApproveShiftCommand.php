<?php

namespace nst\member\commands;

use nst\member\ProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApproveShiftCommand extends Command
{
    protected function configure() {
        $this->setName('shifts:approve')
            ->setDescription('Provider approve shift')
            ->setHelp('');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Shift ID');
        $this->addOption('is_recurrence', null, InputOption::VALUE_REQUIRED, 'Is a recurrence (bool)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $shiftId = $input->getOption('id');
        $isRecurrence = $input->getOption('is_recurrence');

        $data = [
            'id' => $shiftId,
            'is_recurrence' => $isRecurrence,
            'command' => true
        ];
        $shiftService = new ProviderService();
        $shiftService->approveShiftRequest($data);

        return Command::SUCCESS;
    }
}
