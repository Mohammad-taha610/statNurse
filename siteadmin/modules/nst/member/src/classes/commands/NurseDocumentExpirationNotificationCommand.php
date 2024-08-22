<?php

namespace nst\member\commands;

use nst\member\NurseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NurseDocumentExpirationNotificationCommand extends Command
{

    protected function configure()
    {
        $this->setName('cron:nurse-doc-exp-notif')
            ->setDescription('Notify nurse 30, 15 or 1 days beore documents expiring')
            ->setHelp('');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nurseService = new NurseService();
        $nurseService->documentExpirationNotificationCron();

        return Command::SUCCESS;
    }
}