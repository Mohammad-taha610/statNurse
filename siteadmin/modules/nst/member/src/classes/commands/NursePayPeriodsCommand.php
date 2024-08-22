<?php

namespace nst\member\commands;

use nst\member\NurseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NursePayPeriodsCommand extends Command
{

    protected function configure()
    {
        $this->setName('update:nurse_pay_periods')
            ->setDescription('Update nurse pay period totals')
            ->setHelp('');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nurseService = new NurseService();
        $nurseService->setUpNursePayPeriodTotals();

        return Command::SUCCESS;
    }
}