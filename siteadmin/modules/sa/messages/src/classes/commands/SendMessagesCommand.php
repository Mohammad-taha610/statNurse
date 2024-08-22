<?php
namespace sa\messages\commands;

use sacore\application\app;
use sacore\application\ioc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Date: 7/26/2018
 *
 * File: BackwardsCompabilityCommand.php
 */
class SendMessagesCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('messages:send-all')

            // the short description shown while running "php bin/console list"
            ->setDescription('Send Queued Messages.')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('batch_id', 'b', InputOption::VALUE_OPTIONAL, "The controller to execute"),
                ))
            )

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to send all queued messages.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();

        $batch_id = $input->getOption('batch_id');

        $io = $app->getCliIO();

        $messagesController = ioc::get('messagesController');
        $messagesController->messagesCron($batch_id);

        if ($batch_id)
            $io->note('Sent messages for batch id: '.$batch_id);


    }

}