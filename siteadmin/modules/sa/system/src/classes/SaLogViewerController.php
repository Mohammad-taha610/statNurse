<?php
/**
 * Date: 4/26/2018
 *
 * File: SaLogViewerController.php
 */

namespace sa\system;


use Dubture\Monolog\Reader\LogReader;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\responses\View;
use sacore\application\saController;
use sa\search\searchItemResult;

class SaLogViewerController extends saController
{
    public function index() {
        $view = new View('log');

        return $view;
    }

    public static function getLogData($data) {

        $config = app::get()->getConfiguration();
        $logFile = $config->get('tempDir').'/sa_log_v2.log';
        $log = array();

        if (file_exists($logFile)) {
            $pattern = '/\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>.*[^ ]+)[ ]{1,2}(?P<context>[^ ]+) (?P<extra>[^ ]+)/';
            $reader = new LogReader($logFile, $pattern);


            foreach ($reader as $r) {

                /** @var \DateTime $original_date */
                $original_date = $r['date'];


                $a = $r;

                if ($original_date) {
                    $date = new DateTime( );
                    $date->setTimestamp( $original_date->getTimestamp() );

                    // var_dump($date);

                    $a['date'] = $date->format('m/d/Y g:i:s A');
                } else {
                    $a['date'] = '';
                }

                if (strpos($a['message'], 'Stack trace:')!==false) {
                    $a['message'] = str_replace('Stack trace:', '<br /><br />Stack trace:', $a['message']);


                    $a['message'] = preg_replace('/\s([0-9]{1,}\.)\s/', '<br />$1 ', $a['message']);
                }

                if (!$a['message'])
                    $a['message'] = "";

                if ($data['search'] && strpos(strtolower($a['message']), strtolower($data['search']) )!==false) {
                    $log[] = $a;
                }
                elseif (!$data['search']) {
                    $log[] = $a;
                }

            }
        }

        $log = array_reverse($log);

        $log = array_slice($log, 0, 500);

        unset($log[0]);

        return array('logs'=>array_values($log) );
    }

}
