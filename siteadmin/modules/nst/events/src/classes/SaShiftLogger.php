<?php

namespace nst\events;

use Dubture\Monolog\Reader\LogReader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use sacore\application\app;
use sacore\application\DateTime;

/**
 * @IOC_NAME="SaShiftLogger"
 */
class SaShiftLogger {
    /** @var Logger $logger */
    private Logger $logger;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        // Create the logger
        $this->logger = new Logger('saShiftActionLog');

        $config = app::get()->getConfiguration();

        $this->logger->pushHandler(new StreamHandler($config->get('tempDir').'/sa_shift_actions.log', 200)); // 200 = INFO
    }

    public function log($message, $context = []): void
    {
        $this->logger->addInfo($message, $context);
    }

    public static function get($data) {
        $config = app::get()->getConfiguration();
        $logFile = $config->get('tempDir').'/sa_shift_actions.log';
        $log = array();

        if (file_exists($logFile)) {
            $pattern = '/\[(?P<date>.*)\] (?P<logger>\w+).(?P<level>\w+): (?P<message>.*[^ ]+)[ ]{1,2}(?P<context>[^ ]+) (?P<extra>[^ ]+)/';
            $reader = new LogReader($logFile, $pattern);

            $now = new DateTime();
            $start = new DateTime($data['start_date']);
            $startTime = strtotime($start);

            $end = new DateTime($data['end_date']);
            if ($end == $start) {
                $end->modify('+1 days');
            }
            $endTime = strtotime($end);

            foreach ($reader as $r) {
                /** @var \DateTime $original_date */
                $original_date = $r['date'];
                $a = $r;

                if(strlen($data['nurse_name']) && !str_contains(($a['message']), (($data['nurse_name'])))) {
                    continue;
                }

                if ($original_date) {
                    $date = new DateTime( );
                    $date->setTimestamp( $original_date->getTimestamp() );
                    $rTimestamp = $original_date->getTimestamp();
                    if($rTimestamp < $startTime || $rTimestamp > $endTime) {
                        continue;
                    }

                    $a['date'] = $date->format('m/d/Y g:i:s A');
                } else {
                    $a['date'] = '';
                }

                if(strlen($data['provider_name']) && !str_contains(strtolower($a['message']), strtolower($data['provider_name']))) {
                    continue;
                }

                if(strlen($data['credential']) && !str_contains(strtolower($a['message']), strtolower('('.$data['credential'].')'))) {
                    continue;
                }

                if (str_contains($a['message'], 'Stack trace:')) {
                    $a['message'] = str_replace('Stack trace:', '<br /><br />Stack trace:', $a['message']);
                    $a['message'] = preg_replace('/\s([0-9]{1,}\.)\s/', '<br />$1 ', $a['message']);
                }

                if (!$a['message'])
                    $a['message'] = "";

                if ($data['search'] && str_contains(strtolower($a['message']), strtolower($data['search']))) {
                    $log[] = $a;
                }
                elseif (!$data['search']) {
                    $log[] = $a;
                }

            }
        }

        $log = array_reverse($log);

        $count = count($log);

        if(!strlen($log[0]['message'])) {
            unset($log[0]);
        }

        return array('logs'=>array_values($log), 'total_count'=>$count );
    }
}
