<?php

namespace nst\member\commands;

use nst\member\ProviderService;
use nst\payroll\PayrollService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProvidersCommand extends Command
{

    protected function configure() {
        $this->setName('update:providers')
            ->setDescription('FIXES PAYMENTS. ONLY RUN ONCE!')
            ->setHelp('');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $payrollService = new PayrollService();
        $response = $payrollService->getPayStubPDF([]);

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}