<?php

namespace nst\member\commands;

use nst\member\NurseService;
use nst\member\ProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportNursesCommand extends Command
{

    protected function configure() {
        $this->setName('import:nurses')
            ->setDescription('Import nurses from an excel spreadsheet')
            ->setHelp('Imports nurses using the data from the excel spreadsheet provided by NurseStat');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $nurseService = new NurseService();
        $response = $nurseService->importNurses([]);

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }

}