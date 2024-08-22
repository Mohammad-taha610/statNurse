<?php

namespace nst\member\commands;

use Exception;
use nst\events\Shift;
use nst\events\ShiftApiV1Controller;
use nst\events\ShiftRecurrence;
use nst\events\ShiftService;
use nst\member\Nurse;
use nst\member\Provider;
use sa\api\Responses\ApiJsonResponse;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CheckUpcomingNurseShiftsCommand extends Command
{
    protected function configure() {
        $this->setName('cron:check_upcoming_shifts')
            ->setDescription('Check upcoming shifts for nurses to see if it starts within 24 hours')
            ->setHelp('');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $response = ['success' => false];

        $nurses = ioc::getRepository('Nurse')->findAll();
        if($nurses) {
            /** @var Nurse $nurse */
            foreach ($nurses as $nurse) {
                if (!$nurse->getMember() || $nurse->getMember()->getIsDeleted()) {
                    continue;
                }
                $data = [ 'nurse_id' => $nurse->getId() ];
                $shifts = $this->getUpcomingShiftsForNurse($data);

                if ($shifts) {
                    /** @var Shift $shift */
                    foreach ($shifts as $shift) {
                        $now = new \DateTime('today');
                        $shiftDateTime = new \DateTime($shift['start']);
                        $shiftDateTimeDiff = $now->diff($shiftDateTime);

                        if ($shiftDateTimeDiff->format("%a") === '1') {
                            if ($nurse->getReceivesSMS() && $shift->getNurse() === $nurse) {
                                /** @var Shift $iocShift */
                                $iocShift = ioc::get('Shift', ['id' => $shift['id']]);
                                /** @var Provider $provider */
                                $provider = $iocShift->getProvider();
                                if ($provider && !$iocShift->getNotifiedBySMS()) {
                                    $intro = ($iocShift->getStatus() === 'Pending' ? 'A ' : 'An ') . ucfirst($iocShift->getStatus());
                                    $date = $iocShift->getStart()->format('m/d/Y');
                                    $time = $iocShift->getStart()->format('H:i');
                                    $providerName = $provider->getName() !== '' ? $provider->getName() : 'your provider';
                                    $smsBody = 'UPCOMING SHIFT - ' . $intro . ' Shift for ' . $providerName . ' starts on ' . $date . ' at ' . $time;

                                    if ($nurse->getPhoneNumber()) {
                                        modRequest::request('messages.startSMSBatch');
                                        modRequest::request('messages.sendSMS', array('phone' => $nurse->getPhoneNumber(), 'body' => $smsBody));
                                        modRequest::request('messages.commitSMSBatch');

                                        $iocShift->setNotifiedBySMS(true);
                                        app::$entityManager->persist($iocShift);
                                        app::$entityManager->flush();
                                    }

                                    $response['success'] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        if($response['success']) {
            return Command::SUCCESS;
        }
        return Command::FAILURE;
    }

    private function getUpcomingShiftsForNurse(array $data)
    {
        $service = new ShiftService();
        return $service->getUpcomingShiftsForNurse($data)['upcoming_shifts'];
    }
}
