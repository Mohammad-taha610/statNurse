<?php

namespace nst\events;

use nst\payroll\PayrollService;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\responses\View;

class ShiftTests
{
    public static function testPay($data)
    {
        $newShift = ioc::resolve('Shift');

        // $startTime = new DateTime('2023-01-01 18:00:00');
        $startTime = new DateTime($data['start_time']);
        $newShift->setStartTime($startTime);
        $newShift->setStartDate($startTime);
        $newShift->setStart($startTime);
        $newShift->setClockInTime($startTime);

        // $endTime = new DateTime('2023-01-02 07:00:00');
        $endTime = new DateTime($data['end_time']);
        $newShift->setEndTime($endTime);
        $newShift->setEndDate($endTime);
        $newShift->setEnd($endTime);
        $newShift->setClockOutTime($endTime);

        // $newShift->setHourlyRate(10);
        // $newShift->setBillingRate(15);
        
        // get nurse
        $nurse = ioc::getRepository('Nurse')->findOneBy(['id' => $data['nurse_id']]);
        $newShift->setNurse($nurse);
        // get provider
        $provider = ioc::getRepository('Provider')->findOneBy(['id' => $data['provider_id']]);
        $newShift->setProvider($provider);

        app::$entityManager->persist($newShift);
        app::$entityManager->flush($newShift);
        
        $id = $newShift->getId();
        /** @var Shift $shift */
        $shift = ioc::getRepository('Shift')->findOneBy(['id' => $id]);

        $service = new PayrollService();
        $return['payment'] = $service->createShiftPayment($shift);
        $return['id'] = $id;

        $return['success'] = true;
        return $return;
    }

    public static function testClockIn($data)
    {
        $response = ['success' => false];

        /** @var Shift $shift */
        $shift = ioc::resolve('Shift');

        $clock_in_type = $data['clock_in_type'];
        if ($clock_in_type == "natural" || $clock_in_type == "manual") {
            $shift->setClockInType($clock_in_type);
        }

        $now = new DateTime($data['clock_in_time']);
        $shift->setClockInTime($now);
        $shift->setStart($now);
        $shift->setStartDate($now);
        
        /** @var Nurse */
        $nurse = ioc::getRepository('Nurse')->findOneBy(['id' => $data['nurse_id']]);

        /** @var Provider */
        $provider = ioc::getRepository('Provider')->findOneBy(['id' => $data['provider_id']]);

        $shift->setNurse($nurse);
        $nurse->addShift($shift);

        $shift->setProvider($provider);

        $shift->setIsCovid(false);
        $shift->setIncentive(1);

        app::$entityManager->persist($shift);        
        
        $service = new PayrollService();
        $service->initializeShiftRates($shift);

        app::$entityManager->flush();

        $response['shift_id'] = $shift->getId();
        $response['success'] = true;
        return $response;
    }

    public static function testClockOut($data)
    {
        $response = ['success' => false];
        $id = $data['shift_id'];

        /** @var Shift $shift */
        $shift = ioc::get('Shift', ['id' => $id]);

        // if ($this->isOnBreak($shift)) {
        //     $response['message'] = 'You cannot clock out while on break.';
        //     $response['success'] = false;
        //     return $response;
        // }

        if ($data['lunch_override']) {
            $shift->setLunchOverride($data['lunch_override']);
        }

        $end = new DateTime($data['clock_out_time']);
        if ($shift->getEnd() < $shift->getStart()) {
            $end->modify('+1 day');
        }

        $now = new DateTime($data['clock_out_time']);
        $shift->setClockOutTime($data['automatic_clock_out'] ? $end : $now);
        $shift->setEnd($end);

        
        $shift->setStatus(Shift::STATUS_COMPLETED);
        $shift->setClockoutComment($data['clockout_comment']);

        if ($data['clocked_out_early']) {
            $shift->setClockedOutEarly(true);
            $shift->setEarlyClockOutReason($data['early_clock_out_reason']);
        }

        // try {
        //     // store time slip photo
        //     $saf = new saFile();
        //     /** @var NstFile $currentFile */
        //     $timeslipFile = $saf->saveStringToFile($id . 'timeslip.png', base64_decode($data['timeslip']));
        //     app::$entityManager->persist($timeslipFile);
        //     $shift->setTimeslip($timeslipFile);
        // }
        // catch (\Exception $e) {
        //     file_put_contents(app::get()->getConfiguration()->get('tempDir') . '/timeslip.log',   $e->getMessage() . PHP_EOL, FILE_APPEND);
        // }

        $payrollService = new PayrollService();
        $payments = $payrollService->createShiftPayment($shift);
        if (!$payments) {
            
            $response['message'] = 'No payments were created.';
            return $response;
        }
        $payments[0]->setPayBonus(0);
        $payments[0]->setBillBonus(0);
        
        app::$entityManager->flush();

        // Check for clock in/out over 30 minutes before/after start/end
        $clockInDiff = $shift->getClockInTime()->diff($shift->getStart());
        // $clockInMinutes = $clockInDiff->days * 24 * 60;
        $clockInMinutes = $clockInDiff->h * 60;
        $clockInMinutes += $clockInDiff->i;

        $clockOutDiff = $shift->getClockOutTime()->diff($shift->getEnd());
        // $clockOutMinutes = $clockOutDiff->days * 24 * 60;
        $clockOutMinutes = $clockOutDiff->h * 60;
        $clockOutMinutes += $clockOutDiff->i;

        if ($clockInMinutes > 50 || $clockOutMinutes > 50) {
            foreach ($payments as $payment) {
                $payment->setStatus('Unresolved');
                $payment->setRequestDescription('Over 50 minutes early or late.');
            }
            app::$entityManager->flush();
        }

        if ($data['confirmation_message']) {
            foreach ($payments as $payment) {
                $payment->setRequestDescription($data['confirmation_message']);
                $payment->setStatus('Unresolved');
            }
            app::$entityManager->flush();
        }

        if ($data['automatic_clock_out']) {
            foreach ($payments as $payment) {
                $payment->setStatus('Unresolved');
                $payment->setRequestDescription('Automatic Clock Out');
            }
            app::$entityManager->flush();
        }

        // if ($payments) {
        //     $response['success'] = true;

        //     // log
        //     $nurse = $shift->getNurse();
        //     if ($nurse) {
        //         $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
        //         $now = new DateTime('now', app::getInstance()->getTimeZone());
        //         $provider = $shift->getProvider() ? $shift->getProvider()->getMember()->getCompany() : '';
        //         if ($provider) {
        //             $log_msg = sprintf('%s has clocked out at %s for the %s provider', $nurse_name, $now, $provider);
        //             app::getInstance()->getLogger()->info('clockOut: ' . $log_msg);
        //         }
        //     }
        // }

        return $response;
    }
}