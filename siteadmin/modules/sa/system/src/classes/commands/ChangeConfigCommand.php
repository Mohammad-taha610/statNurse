<?php
namespace sa\system\commands;

use sacore\application\app;
use sacore\application\ConfigurationSetting;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\system\AssetBuildManager;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
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
class ChangeConfigCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('system:config:change')

            // the short description shown while running "php bin/console list"
            ->setDescription('Show the config.')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('name', 's', InputOption::VALUE_REQUIRED, "The config variable to change."),
                    new InputOption('value', 'l', InputOption::VALUE_REQUIRED, "The value to set on the config variable."),

                ))
            )

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you set a config variable.')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();
        
        $io = $app->getCliIO();
        
        $config = $app->getConfiguration();

        $dataForTable = [];

        $io->title('Proposed Configuration Change');

        $configSetting = $config->get($input->getOption('name'));

        if ($configSetting) {
            $io->text('Original Version');
            $io->table(['Name', 'Value'], [ [$configSetting->getName(), $configSetting->getValue()] ]);

            $configSetting->setValue($input->getOption('value'));
            $io->text('New Version');
        }
        else
        {
            $configSetting = new ConfigurationSetting($input->getOption('name'), $input->getOption('value'));
            $io->text('New setting to be added');
        }


        $io->table(['Name', 'Value'], [ [$configSetting->getName(), $configSetting->getValue()] ]);

        $confirm = $io->confirm('Are you sure you want to save this change?', true);

        if ($confirm) {
            $config->persist();
            $io->success('The config was successfully saved.');
        }
        else
        {
            $io->warning('The config change was aborted.');

        }

        return Command::SUCCESS;
    }

}