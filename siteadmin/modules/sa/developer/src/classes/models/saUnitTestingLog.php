<?php

namespace sa\developer;

use sacore\application\app;
use sacore\application\ioc;

/**
 * @Entity(repositoryClass="saUnitTestingLogRepository")
 *
 * @InheritanceType("SINGLE_TABLE")
 *
 * @DiscriminatorColumn(name="discriminator", type="string")
 *
 * @Table(name="sa_unit_testing_log")
 */
class saUnitTestingLog
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="text") */
    protected $results;

    /** @Column(type="integer", length=8000,) */
    protected $test_num;

    /** @Column(type="integer") */
    protected $assertions_num;

    /** @Column(type="integer") */
    protected $failed_num;

    /** @Column(type="datetime") */
    public $test_time;

    /**
     * Set results
     *
     * @param  string  $results
     * @return saUnitTestingLog
     */
    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Get results
     *
     * @return string
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set test_num
     *
     * @param  int  $testNum
     * @return saUnitTestingLog
     */
    public function setTestNum($testNum)
    {
        $this->test_num = $testNum;

        return $this;
    }

    /**
     * Get test_num
     *
     * @return int
     */
    public function getTestNum()
    {
        return $this->test_num;
    }

    /**
     * Set assertions_num
     *
     * @param  int  $assertionsNum
     * @return saUnitTestingLog
     */
    public function setAssertionsNum($assertionsNum)
    {
        $this->assertions_num = $assertionsNum;

        return $this;
    }

    /**
     * Get assertions_num
     *
     * @return int
     */
    public function getAssertionsNum()
    {
        return $this->assertions_num;
    }

    /**
     * Set failed_num
     *
     * @param  int  $failedNum
     * @return saUnitTestingLog
     */
    public function setFailedNum($failedNum)
    {
        $this->failed_num = $failedNum;

        return $this;
    }

    /**
     * Get failed_num
     *
     * @return int
     */
    public function getFailedNum()
    {
        return $this->failed_num;
    }

    /**
     * Set test_time
     *
     * @param  \sacore\application\DateTime  $testTime
     * @return saUnitTestingLog
     */
    public function setTestTime($testTime)
    {
        $this->test_time = $testTime;

        return $this;
    }

    /**
     * Get test_time
     *
     * @return \sacore\application\DateTime
     */
    public function getTestTime()
    {
        return $this->test_time;
    }

    public static function saveLog($report, $test_num, $assertions_num, $failed_num)
    {
        //LOG THE TEST IF ANY THING CHANGES, LOG THE TEST IF THE DAYS CHANGE, DONT LOG THE TEST IF ITS ON THE SAME DAY AND SAME RESULTS AS PREVIOUS TEST

        $logClass = ioc::staticResolve('saUnitTestingLog');
        $repo = app::$entityManager->getRepository($logClass);

        $lastLog = $repo->getRecent(1);

        if (count($lastLog) > 0) {
            $lastLog = $lastLog[0];
        }

        if ($lastLog) {
            $lastLogDate = $lastLog->getTestTime()->format('Y-m-d');
            if ($lastLogDate == date('Y-m-d')) {
                $log = $repo->getLog($test_num, $assertions_num, $failed_num, date('Y-m-d'));

                if (! $log) {
                    $log = new saUnitTestingLog();
                } else {
                    $log = $log[0];
                }
            } else {
                $log = new saUnitTestingLog();
            }
        } else {
            $log = new saUnitTestingLog();
        }

        $log->setResults($report);
        $log->setTestNum($test_num);
        $log->setAssertionsNum($assertions_num);
        $log->setFailedNum($failed_num);
        $log->setTestTime(new \sacore\application\DateTime());

        app::$entityManager->persist($log);
        app::$entityManager->flush();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
