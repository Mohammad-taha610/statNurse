<?php

namespace nst\member\commands;


use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use nst\events\ShiftRecurrence;
use nst\events\ShiftRecurrenceService;
use nst\events\ShiftService;
use nst\member\NstMember;
use nst\member\Nurse;
use nst\member\Provider;
use nst\payroll\Payroll;
use nst\payroll\PayrollPayment;
use nst\payroll\PayrollService;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Date: 6/17/2021
 *
 * File: NurseShiftRecurrenceTimeClockCommand.php
 */
class NurseShiftRecurrenceTimeClockCommand extends Command
{
    protected function configure() {
        $this->setName('nurse:shift_recurrence_time_clock')
            ->setDescription('Test clock in and clock out times')
            ->setHelp('This command lets you act as a nurse clocking in and out of a shift')
            ->addArgument('shift_id', InputArgument::REQUIRED, 'ShiftRecurrenceRecurrence Id')
            ->addArgument('clock_in_time', InputArgument::REQUIRED, 'Clock In Time')
            ->addArgument('clock_out_time', InputArgument::REQUIRED, 'Clock Out Time')
            ->addArgument('status', InputArgument::REQUIRED, 'Status');
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws IocException
     * @throws IocDuplicateClassException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $shiftId = $input->getArgument('shift_id');
        $clockIn = $input->getArgument('clock_in_time');
        $clockOut = $input->getArgument('clock_out_time');
        $status = $input->getArgument('status');

        $app = app::get();

        $io = $app->getCliIO();

        /** @var ShiftRecurrence $shift */
        $shift = ioc::get('ShiftRecurrence', ['id' => $shiftId]);
        $shiftService = new ShiftService();
        $payrollService = new PayrollService();
        /** @var Nurse $nurse */
        $nurse = $shift->getNurse();
        /** @var NstMember $nurseMember */
        $nurseMember = $nurse->getMember();

        $timezone = app::getInstance()->getTimeZone();

        $clockInTime = new DateTime($clockIn, app::getInstance()->getTimeZone());
        $clockOutTime = new DateTime($clockOut, app::getInstance()->getTimeZone());

        $shift->setClockInTime($clockInTime);
        $shift->setClockOutTime($clockOutTime);

        $clockedHours = $shiftService->getShiftRecurrenceClockedHours($shift);
        $payPeriod = $payrollService->calculatePayPeriodFromDate($shift->getStart());

        $nurseHours = $payrollService->getHoursForPayPeriod($nurse, $payPeriod);
        $overtimeHours = 0;
        $standardHours = $clockedHours;
        if($nurseHours >= 40) {
            $overtimeHours = $clockedHours;
            $standardHours = 0;
        } else {
            $difference = 40 - ($nurseHours + $clockedHours);
            if($difference < 0) {
                $overtimeHours = -$difference;
                $standardHours = $clockedHours - $overtimeHours;
            }
        }
        $standardHours = number_format($standardHours, 2);
        $overtimeHours = number_format($overtimeHours, 2);

        /** @var Provider $provider */
        $provider = $shift->getProvider();

        // Calculate Payment amount
        // (Rate * Incentive) + (Overtime addition) + (Covid Addition)
        $standardRate = $provider->getPayRates()[$nurse->getCredentials()]['standard'];
        $payRate = $standardRate * $shift->getIncentive();
        if($shift->getIsCovid()) {
            $covidRate = $provider->getPayRates()[$nurse->getCredentials()]['covid'];
            $covidAddition = $covidRate - $standardRate;
            $payRate += $covidAddition;
        }
        $paymentAmount = $payRate * $standardHours;
        $overtimeRate = $payRate + ($standardRate * 0.5);
        $overtimeAmount = $overtimeRate * $overtimeHours;

        // Billing amount
        // (Rate + Covid) * (Overtime 1.5) * (Incentive)
        $standardBill = $provider->getPayRates()[$nurse->getCredentials()]['standard_bill'];
        $billRate = $standardBill;
        if($shift->getIsCovid()) {
            $covidBillRate = $provider->getPayRates()[$nurse->getCredentials()]['covid_bill'];
            $billRate = $covidBillRate;
        }
        $billRate *= $shift->getIncentive();
        $billingAmount = $billRate * $standardHours;
        $overtimeBillRate = $billRate * 1.5;
        $overtimeBillAmount = $overtimeBillRate * $overtimeHours;

        if($standardHours) {
            /** @var PayrollPayment $payment */
            $payment = ioc::resolve('PayrollPayment');

            $payment->setStatus($status);
            $payment->setAmount($paymentAmount);
            $payment->setHourlyRate($payRate);
            $payment->setBillingAmount($billingAmount);
            $payment->setBillingRate($billRate);
            $payment->setShiftRecurrence($shift);
            $payment->setType('Standard');
            $payment->setClockedHours($standardHours);
            $shift->setPayrollPayment($payment);

            $invoiceDescription = $nurse->getCredentials();
            $invoiceDescription .= $shift->getIsCovid() ? ' Covid ' : ' ';
            $invoiceDescription .= 'Rate ' . $nurseMember->getFirstName() . ' ' . $nurseMember->getLastName() . ' ' . $shift->getStart()->format('m/d/y');
            if ($shift->getIncentive() > 1) {
                $invoiceDescription .= ' (*' . $shift->getIncentive() . ' incentive)';
            }
            $payment->setInvoiceDescription($invoiceDescription);
            app::$entityManager->persist($payment);
        }
        // TODO - add travel pay
        // TODO - add stipends

        // If overtime exists, create a separate payment for it
        if($overtimeBillAmount) {
            /** @var PayrollPayment $overtimePayment */
            $overtimePayment = ioc::resolve('PayrollPayment');

            $overtimePayment->setStatus($status);
            $overtimePayment->setAmount($overtimeAmount);
            $overtimePayment->setHourlyRate($overtimeRate);
            $overtimePayment->setBillingAmount($overtimeBillAmount);
            $overtimePayment->setBillingRate($overtimeBillRate);
            $overtimePayment->setShiftRecurrence($shift);
            $overtimePayment->setType('Overtime');
            $overtimePayment->setClockedHours($overtimeHours);
            $shift->setPayrollPayment($overtimePayment);

            $overtimeDescription = $nurse->getCredentials();
            $overtimeDescription .= $shift->getIsCovid() ? ' Covid Overtime ' : ' Overtime ';
            $overtimeDescription .= 'Rate ' . $nurseMember->getFirstName() . ' ' . $nurseMember->getLastName() . ' ' . $shift->getStart()->format('m/d/y');
            if($shift->getIncentive() > 1) {
                $overtimeDescription .= ' (*' . $shift->getIncentive() . ' incentive)';
            }
            $overtimePayment->setInvoiceDescription($overtimeDescription);
            app::$entityManager->persist($overtimePayment);
        }



        if($shift->getBonusAmount()) {
            /** @var PayrollPayment $bonus */
            $bonus = ioc::resolve('PayrollPayment');
            $bonus->setStatus($status);
            $bonus->setShiftRecurrence($shift);
            $bonus->setType('Bonus');

            // TODO - Is bonus amount same for billing and regular payment?
            $bonus->setAmount($shift->getBonusAmount());
            $bonus->setHourlyRate($shift->getBonusAmount());
            $bonus->setBillingAmount($shift->getBonusAmount());
            $bonus->setBillingRate($shift->getBonusAmount());
            $bonus->setDescription($shift->getBonusDescription());

            $bonusInvoiceDescription = 'Facility Incentive Bonus ' . $nurseMember->getFirstName() . ' ' . $nurseMember->getLastName();
            $bonus->setInvoiceDescription($bonusInvoiceDescription);

            $shift->setBonusPayment($bonus);
            app::$entityManager->persist($bonus);
        }

        app::$entityManager->flush();

        return Command::SUCCESS;
    }
}