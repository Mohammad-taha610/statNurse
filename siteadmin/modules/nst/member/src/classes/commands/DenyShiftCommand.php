<?php


namespace nst\member\commands;

use nst\member\ProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DenyShiftCommand extends Command
{
    protected function configure()
    {
        $this->setName('shifts:deny')
            ->setDescription('Provider deny shift')
            ->setHelp('');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'Shift ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shiftId = $input->getOption('id');

        $data = [
            'id' => $shiftId,
        ];
        $shiftService = new ProviderService();
        $shiftService->denyShiftRequest($data);

        return Command::SUCCESS;
    }
}
