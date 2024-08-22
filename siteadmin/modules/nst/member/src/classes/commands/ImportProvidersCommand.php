<?php

namespace nst\member\commands;

use nst\member\ProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProvidersCommand extends Command
{

    protected function configure() {
        $this->setName('import:providers')
            ->setDescription('Import providers and their wages from an excel spreadsheet')
            ->setHelp('Imports providers using the data from the excel spreadsheet provided by NurseStat')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to spreadhseet')
            ->addArgument('fresh_import', InputArgument::OPTIONAL, 'Fresh Import? (Delete all providers and import from scratch) [1 or 0]');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $path = $input->getArgument('path');
        $freshImport = $input->getArgument('fresh_import');

        $data = [
            'path' => $path,
            'fresh_import' => $freshImport
        ];
        $providerService = new ProviderService();
        $response = $providerService->importProviders($data);

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }

}