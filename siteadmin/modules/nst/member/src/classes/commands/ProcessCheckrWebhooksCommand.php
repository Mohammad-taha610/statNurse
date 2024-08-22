<?php

namespace nst\member\commands;

use nst\member\CheckrPayService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCheckrWebhooksCommand extends Command
{

    protected function configure() {
        $this->setName('cron:process_checkr_pay_webhooks')
            ->setDescription('Process Checkr Pay Webhooks');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $checkrPayService = new CheckrPayService();

        $response = $checkrPayService->processCheckrPayWebhooksCron();

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}