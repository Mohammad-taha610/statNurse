<?php

namespace nst\member\commands;

use nst\member\NurseService;
use nst\member\ProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NurseTBAndLicenseDateImport extends Command
{

    protected function configure() {
        $this->setName('nurse:import_tb_and_license_dates')
            ->setDescription('Import nurse TB and license dates from excel, csv etc')
            ->setHelp('Import nurse TB and license dates from excel, csv etc');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $nurseService = new NurseService();
        $response = $nurseService->importNurseSkintestAndVaccineData();

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }

}