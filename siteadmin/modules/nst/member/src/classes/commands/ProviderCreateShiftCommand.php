<?php

namespace nst\member\commands;

use nst\member\ProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProviderCreateShiftCommand extends Command
{
    protected function configure()
    {
        $this->setName('provider:create_shift')
            ->setDescription('Provider create shift')
            ->setHelp('')
            ->addOption('data', null, InputOption::VALUE_REQUIRED, 'The JSON string of data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shiftData = $input->getOption('data');

        $data = json_decode($shiftData, true);
        $data['command'] = true;
        $shiftService = new ProviderService();

        $res = $shiftService->handleSaveNewShift($data);
        $output->writeln(json_encode($res));
        return Command::SUCCESS;
    }
}
