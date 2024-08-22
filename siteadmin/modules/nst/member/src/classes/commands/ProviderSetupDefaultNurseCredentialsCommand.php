<?php

namespace nst\member\commands;

use nst\member\ProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProviderSetupDefaultNurseCredentialsCommand extends Command
{

    protected function configure() {
        $this->setName('provider:setup-default-nurse-credentials')
            ->setDescription('Sets default nurse credential relationships. Should only be run once after the update with this feature rolls out')
            ->setHelp('');
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $providerService = new ProviderService();
        $response = $providerService->setDefaultProviderNurseCredentials();

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }
}