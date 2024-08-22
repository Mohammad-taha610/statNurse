<?php

namespace sa\developer\commands;

use sacore\application\app;
use sacore\application\DateTime;
use sa\developer\saUnitTestingLog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunUnitTestsCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('developer:run:unit-tests')

            // the short description shown while running "php bin/console list"
            ->setDescription('Runs Unit Tests.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command runs all of the unit tests.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = app::get()->getCliIO();

        $io->title('Unit Tests');

        $tempDir = app::get()->getConfiguration()->get('tempDir')->getValue();
        unlink($tempDir.'/unittesting.sqlite3');

        file_put_contents(app::getAppPath().'/modules/sa/developer/bin/config.xml', '<phpunit stopOnFailure="false" stopOnError="false" bootstrap="../../../../includes/app.php">
	<testsuites>
		<testsuite name="Testing">
		    <directory>../../../../../*/vendor/sa/*/test</directory>
			<directory>../../../../../*/test</directory>
			<directory>../../../../../*/modules/*/*/test</directory>
		</testsuite>
	</testsuites>
	<php>
		<server name="UNITTESTING" value="5"/>
        <server name="SERVER_NAME" value="unit_testing"/>
	</php>
</phpunit>');
//        app::get()->disable_safe_mode();
//        die();

        $config = app::get()->getConfiguration();

        //Begin writing to unit test config and setting the unique db parameters
        $config->setConfigFile('../config/unitTestingConfig.json');
        $config->get('db_server')->setType('string');
        $config->get('db_server')->setFile('../config/unitTestingConfig.json');
        $config->get('db_server')->setValue('');
        $config->get('db_name')->setType('string');
        $config->get('db_name')->setFile('../config/unitTestingConfig.json');
        $config->get('db_name')->setValue('');
        $config->get('db_username')->setType('string');
        $config->get('db_username')->setFile('../config/unitTestingConfig.json');
        $config->get('db_username')->setValue('');
        $config->get('db_password')->setType('string');
        $config->get('db_password')->setFile('../config/unitTestingConfig.json');
        $config->get('db_password')->setValue('');
        $config->get('db_driver')->setType('string');
        $config->get('db_driver')->setFile('../config/unitTestingConfig.json');
        $config->get('db_driver')->setValue('pdo_sqlite');
        $config->get('db_driver_secondary')->setType('string');
        $config->get('db_driver_secondary')->setFile('../config/unitTestingConfig.json');
        $config->get('db_driver_secondary')->setValue('');
        $config->get('db_path')->setType('string');
        $config->get('db_path')->setFile('../config/unitTestingConfig.json');
        $config->get('db_path')->setValue($tempDir.'/unittesting.sqlite3');
        $config->get('hasBeenSetup')->setType('boolean');
        $config->get('hasBeenSetup')->setFile('../config/unitTestingConfig.json');
        $config->get('hasBeenSetup')->setValue('1');

        //Copy values from current dev config for tests that require them
        $config->get('allowed_additional_files_types')->setFile('../config/unitTestingConfig.json');
        $config->get('site_block_password')->setFile('../config/unitTestingConfig.json');
        $config->get('site_robot_indexable')->setFile('../config/unitTestingConfig.json');
        $config->get('sa_device_verify')->setFile('../config/unitTestingConfig.json');
        $config->get('sa_device_verify_method')->setFile('../config/unitTestingConfig.json');
        $config->get('sa_session_timeout')->setFile('../config/unitTestingConfig.json');
        $config->get('remote_assistance')->setFile('../config/unitTestingConfig.json');
        $config->get('recaptcha_public')->setFile('../config/unitTestingConfig.json');
        $config->get('recaptcha_private')->setFile('../config/unitTestingConfig.json');
        $config->get('sa_login_two_factor_method')->setFile('../config/unitTestingConfig.json');
        $config->get('disable_settings')->setFile('../config/unitTestingConfig.json');
        $config->get('public_directory')->setFile('../config/unitTestingConfig.json');
        $config->get('thread_domain')->setFile('../config/unitTestingConfig.json');
        $config->get('settings_no_sync')->setFile('../config/unitTestingConfig.json');
        $config->get('enable_public_member_signup')->setFile('../config/unitTestingConfig.json');

        $config->persist(['../config/unitTestingConfig.json']);

        $command = app::get()->getConfiguration()->get('php_path_executable')->getValue().' '.app::getAppPath().'/modules/sa/developer/bin/phpunit.phar -c '.app::getAppPath().'/modules/sa/developer/bin/config.xml 2>&1';

        exec($command, $data);

        $report = '';

        //$data[0] = str_replace(' by Sebastian Bergmann.', '', $data[0]);
        foreach ($data as $line) {
            $report .= $line."\n";
        }

        $status = end($data);
        $status = explode(',', $status);
        foreach ($status as $key => $value) {
            $status[$key] = preg_replace('/[^0-9]/', '', $value);
        }

        $status[0] = $status[0] ? $status[0] : 0;
        $status[1] = $status[1] ? $status[1] : 0;
        $status[2] = $status[2] ? $status[2] : 0;

        if (strpos($report, 'Fatal error:') && $status[2] == 0) {
            $status[2]++;
        }

        if (strpos($report, 'syntax error:') && $status[2] == 0) {
            $status[2]++;
        }

        if (strpos($report, 'Parse error:') && $status[2] == 0) {
            $status[2]++;
        }

        if ($status[2] > 0) {
            saUnitTestingLog::saveLog($report, $status[0], $status[1], $status[2]);
        }

        $date = new DateTime();

//        return array('datetime'=>$date->format('m/d/Y g:i:s A'), 'test_num'=>$status[0], 'test_assertions'=>$status[1], 'test_fail'=>$status[2], 'test_errors'=>$status[3], 'report'=>static::formatTestReport($report));

//        return Command::SUCCESS;
        $io->text(['datetime' => $date->format('m/d/Y g:i:s A'), 'test_num' => $status[0], 'test_assertions' => $status[1], 'test_fail' => $status[2], 'test_errors' => $status[3], 'report' => static::formatTestReport($report)]);

        return Command::SUCCESS;
    }

    public static function formatTestReport($report)
    {
        return $report;
    }
}
