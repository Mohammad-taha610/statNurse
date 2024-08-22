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
class ShowConfigCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('system:show:config')

            // the short description shown while running "php bin/console list"
            ->setDescription('Show the config.')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('name', 'a', InputOption::VALUE_OPTIONAL, "The config variable to see."),
                ))
            )

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you show the config file.')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app = app::get();
        
        $io = $app->getCliIO();
        
        $config = $app->getConfiguration();

        $dataForTable = [];

        /** @var ConfigurationSetting $value */
        foreach($config->getAllSettings() as $value) {

            $valueString = $value->getValue();
            if (is_array($valueString)) {
                $valueString = implode(', ', $valueString);
            }

            if ($input->getOption('name') && $value->getName()==$input->getOption('name')) {
                $dataForTable[] = [ $value->getName(), $valueString, $value->getType() ];
            }
            elseif (!$input->getOption('name'))
            {
                $dataForTable[] = [ $value->getName(), $valueString, $value->getType() ];
            }

        }

        $io->title('Configuration Settings');

        $io->table(['Name', 'Value', 'Type'], $dataForTable);

        return Command::SUCCESS;

    }

}