<?php

namespace sa\developer;

use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\utilities\configReader;
use sacore\utilities\configWriter;

class saUnitTestingController extends saController
{
    public function unitTesting()
    {
        $previousReport = '';

        if (! empty($_REQUEST['standalone'])) {
            $view = new View('unitTesting', $this->viewLocation());
            $view->data['refresh'] = true;
        } else {
            $view = new View('unitTesting', $this->viewLocation());
            $view->data['refresh'] = false;

            $log = ioc::staticResolve('saUnitTestingLog');
            $previousLogs = app::$entityManager->getRepository($log)->getRecent();

            foreach ($previousLogs as $previousLog) {
                $previousReport .= '<strong>Test Time:</strong> '.$previousLog->getTestTime()->format('m/d/Y G:i:s').'<br />'.$previousLog->getResults().'<hr />';
            }
        }

        $view->data['previousreport'] = static::formatTestReport($previousReport);
        $view->data['uid'] = ! empty($_REQUEST['uid']) ? $_REQUEST['uid'] : rand(0, 999999);
        $view->setXSSSanitation(false);

        return $view;
    }

    public static function formatTestReport($report)
    {
        $report = preg_replace('/(Errors|Error|Failures|Failed|Fail)/i', '<span style="color: red; font-weight: bold">$1</span>', $report);

        $report = preg_replace('/(Missing|Invalid)/i', '<span style="color: orange; font-weight: bold">$1</span>', $report);

        $report = preg_replace('/(Assertions|Tests)/i', '<span style="color: green; font-weight: bold">$1</span>', $report);

        $report = preg_replace('/(\d)/i', '<span style="font-weight: bold">$1</span>', $report);

        return $report;
    }

    public static function executeUnitTesting()
    {
        $tempDir = app::get()->getConfiguration()->get('tempDir')->getValue();
        unlink($tempDir.'/unittesting.sqlite3');

        file_put_contents(app::getAppPath().'/modules/sa/developer/bin/config.xml', '<phpunit bootstrap="../../../../includes/app.php">
	<testsuites>
		<testsuite name="Testing">
		    <directory>../../../../../*/vendor/sa/*/test</directory>
			<directory>../../../../../*/test</directory>
			<directory>../../../../../*/modules/*/*/*/test</directory>
		</testsuite>
	</testsuites>
	<php>
		<server name="UNITTESTING" value="5"/>
        <server name="SERVER_NAME" value="unit_testing"/>
	</php>
</phpunit>');

//        $config = configReader::readConfigWithTypeInfo(true, true);
        $config = app::get()->getConfiguration();

        $config->get('db_server')->setType('string');
        $config->get('db_server')->setValue('');
        $config->get('db_name')->setType('string');
        $config->get('db_name')->setValue('');
        $config->get('db_username')->setType('string');
        $config->get('db_username')->setValue('');
        $config->get('db_password')->setType('string');
        $config->get('db_password')->setValue('');
        $config->get('db_driver')->setType('string');
        $config->get('db_driver')->setValue('pdo_sqlite');
        $config->get('db_path')->setType('string');
        $config->get('db_path')->setValue($tempDir.'/unittesting.sqlite3');

        $config->setConfigFile('unitTestingConfig.json');
        $config->persist();
//        configWriter::writeConfigWithTypeInfo($config, 'unitTestingConfig.json');

        $command = app::get()->getConfiguration()->get('php_path_executable')->getValue().' '.app::getAppPath().'/modules/sa/developer/bin/phpunit.phar -c '.app::getAppPath().'/modules/sa/developer/bin/config.xml 2>&1';

        exec($command, $data);

        $report = '';

        //$data[0] = str_replace(' by Sebastian Bergmann.', '', $data[0]);
        foreach ($data as $line) {
            $report .= $line.'<br />';
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

        return ['datetime' => $date->format('m/d/Y g:i:s A'), 'test_num' => $status[0], 'test_assertions' => $status[1], 'test_fail' => $status[2], 'test_errors' => $status[3], 'report' => static::formatTestReport($report)];
    }
}
