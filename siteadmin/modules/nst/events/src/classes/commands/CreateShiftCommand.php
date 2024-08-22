<?php

namespace nst\events\commands;

use nst\events\ShiftController;
use nst\events\ShiftService;
use nst\payroll\PayrollService;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\Redirect;
use sacore\application\ValidateException;
use sacore\utilities\notification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateShiftCommand extends Command
{

    private $params;

    protected function configure()
    {
        $this
            ->setName('shifts:create_shift')
            ->addOption('data', null, InputOption::VALUE_REQUIRED, 'The JSON string of data');
    }


    protected
    function execute(InputInterface $input, OutputInterface $output)
    {
        $jsonData = $input->getOption('data');
        $data = json_decode($jsonData, true);
        $providerId = $data['provider_id'];

        $notify = new notification();

        $shiftService = new ShiftService();

        $response = [];
        try {
            $shiftArray = $shiftService->saveShift($data, $providerId, true);
            if (!$shiftArray['success']) {
                $response['success'] = false;
                $notify->addNotification('danger', 'Error', $shiftArray['message']);
                return Command::FAILURE;
            }
            $notify->addNotification('success', 'Success', 'Shift saved successfully.');
            // initialize pay rates
            $service = new PayrollService();
            $shift = ioc::getRepository('Event')->find($shiftArray['shift_id']);
            $service->initializeShiftRates($shift);
            $response['success'] = true;
            $response['url'] = app::get()->getRouter()->generate('events_index');
        } catch (ValidateException $e) {
            $output->writeln($e->getMessage());
            $notify->addNotification('danger', 'Error', $e->getMessage());
            $response['success'] = false;
            $response['error'] = $e->getMessage();
            return Command::FAILURE;
        }

        if ($response) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}
