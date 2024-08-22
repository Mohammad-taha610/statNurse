<?php

namespace nst\member\commands;


use nst\events\Shift;
use nst\member\Nurse;
use sacore\application\app;
use sacore\application\ioc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Date: 6/17/2021
 *
 * File: NurseRequestShiftCommand.php
 */
class NurseRequestShiftCommand extends Command
{
    protected function configure() {
        $this->setName('nurse:request_shift')
            ->setDescription('Request a shift as a nurse')
            ->setHelp('This command lets you act as a nurse requesting a shift')
            ->addArgument('nurse_id', InputArgument::REQUIRED, 'Nurse Id')
            ->addArgument('shift_id', InputArgument::REQUIRED, 'Shift Id');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $nurseId = $input->getArgument('nurse_id');
        $shiftId = $input->getArgument('shift_id');

        $app = app::get();

        $io = $app->getCliIO();

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $shiftId]);
        /** @var Nurse $nurse */
        $nurse = ioc::get('Nurse', ['id' => $nurseId]);

        $shift->setNurse($nurse);
        $shift->setStatus('Pending');
        $shift->setIsNurseApproved(true);

        $provider = $shift->getProvider();
        if(!$nurse->getPreviousProviders()->contains($provider)) {
            $nurse->addPreviousProvider($provider);
        }
        if(!$provider->getPreviousNurses()->contains($nurse)) {
            $provider->addPreviousNurse($nurse);
        }

        $covidPayAddition = 0;
        $covidBillAddition = 0;
        if($shift->getIsCovid()) {
            $covidPayAddition = $provider->getPayRates()[$nurse->getCredentials()]['covid']
                - $provider->getPayRates()[$nurse->getCredentials()]['standard'];
            $covidBillAddition = $provider->getPayRates()[$nurse->getCredentials()]['covid_bill']
                - $provider->getPayRates()[$nurse->getCredentials()]['standard_bill'];
        }

        // Pay rate is multiplied before adding covid pay, Bill rate is multiplied after covid pay is added.
        $payRate = ( $provider->getPayRates()[$nurse->getCredentials()]['standard'] * $shift->getIncentive() ) + $covidPayAddition;
        $billRate = ( $provider->getPayRates()[$nurse->getCredentials()]['standard_bill'] + $covidBillAddition ) * $shift->getIncentive();
        $shift->setHourlyRate($payRate);
        $shift->setBillingRate($billRate);

        $nurse->addShift($shift);

        app::$entityManager->flush();

        return Command::SUCCESS;
    }
}