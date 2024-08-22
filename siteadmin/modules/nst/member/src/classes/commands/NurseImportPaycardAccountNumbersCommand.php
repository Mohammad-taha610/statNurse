<?php

namespace nst\member\commands;

use sacore\application\app;
use sacore\application\ioc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\Console\Style\SymfonyStyle;
use nst\member\Nurse;
use nst\member\NurseRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class NurseImportPaycardAccountNumbersCommand extends Command
{

    protected function configure()
    {
        $this->setName('nst:nurse_import_paycard_acc_numbers')
            ->setDescription('Import nurse paycard account numbers')
            ->setHelp('');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = app::get();
        /** @var SymfonyStyle $io */
        $io = $app->getCliIO();
        $path = app::get()->getConfiguration()->get('tempDir') . '/nursePaycardAccountNumberImport.xlsx';
        $failedLogFile = app::get()->getConfiguration()->get('tempDir') . '/nursePaycardAccountNumberImportFailures.log';
        file_put_contents($failedLogFile, 'BEGIN' . PHP_EOL);

        set_time_limit(7200);
        ini_set('memory_limit','512M');
        if(!$handle = fopen($path, 'r')) {
            $io->writeln("Unable to open excel file");
            return Command::FAILURE;
        }
        fclose($handle);

        /** @var Xlsx $reader */
        $reader = IOFactory::createReader('Xlsx');
        $reader->setLoadSheetsOnly('Sheet1');
        $reader->setReadDataOnly(true);
        $worksheetTotalRows = array_pop($reader->listWorksheetInfo($path))['totalRows'];
        $io->writeln("Worksheet total rows... " . $worksheetTotalRows);
        $spreadsheet = $reader->load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $csvSpreadsheet = new Spreadsheet();
        $csvWorksheet = $csvSpreadsheet->getActiveSheet();

        $csvRow = 1;

        for ($i = 2; $i < $worksheetTotalRows; $i++) {
            $firstName = $worksheet->getCell('G' . $i)->getValue();
            $lastName = $worksheet->getCell('F' . $i)->getValue();
            $paycardAccountNumber = $worksheet->getCell('E' . $i)->getValue();
            $fullname = $firstName . ' ' . $lastName;

            
            /** @var NurseRepository */
            $nurseRepository = ioc::getRepository('Nurse');

            /** @var Nurse $nurse */
            // $nurse = $nurseRepository->findOneBy(['last_name' => $lastName.'%', 'first_name' => $firstName.'%']);
            $nurse = array_pop($nurseRepository->searchNurseByFirstAndLastFuzzy($firstName, $lastName));
            if (!$nurse) {
                $io->writeln("No nurse for: " . $fullname);
                file_put_contents($failedLogFile, $fullname . PHP_EOL, FILE_APPEND);
                $csvWorksheet->setCellValue('C'.$csvRow, $firstName);
                $csvWorksheet->setCellValue('B'.$csvRow, $lastName);
                $csvWorksheet->setCellValue('A'.$csvRow, $paycardAccountNumber);
                $csvRow++;
            } else {
                $nurse->setPayCardAccountNumber($paycardAccountNumber);
                $io->writeln("SUCCESS: " . $fullname . ' Paycard Account Number set');
            }
        }

        app::$entityManager->flush();

        $writer = new Csv($csvSpreadsheet);
        $writer->setSheetIndex(0);
        $writer->save(app::get()->getConfiguration()->get('tempDir') . '/nursePaycardAccountNumberImportFaulures.csv');
        file_put_contents($failedLogFile, 'END' . PHP_EOL, FILE_APPEND);
        return Command::SUCCESS;
    }
}