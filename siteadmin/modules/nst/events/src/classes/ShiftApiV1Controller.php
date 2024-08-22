<?php

namespace nst\events;

use nst\member\NstFile;
use nst\member\NstMember;
use nst\member\Nurse;
use nst\member\ProviderService;
use sa\api\ApiController;
use sa\api\Responses\ApiJsonResponse;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\Request;
use sa\files\saFile;
use sa\files\saImage;
use sacore\utilities\doctrineUtils;
use Throwable;

// use Symfony\Component\HttpFoundation\Request;

/**
 * @IOC_NAME="ShiftApiV1Controller"
 */
class ShiftApiV1Controller extends ApiController
{
    /**
     * Load all shifts for the daily calendar view
     * @param Request $request
     * @returns ApiJsonResponse
     */
    public function getShiftsForDay($request) {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $timezone = app::getInstance()->getTimeZone();
        $date = new DateTime($data['date'], $timezone);
        $nextDay = new DateTime($date, $timezone);
        $nextDay->modify("+1 day");
        $memberId = $data['member_id'];
        /** @var NstMember $member */
        $member = ioc::get('NstMember', ['id' => $memberId]);
        /** @var Nurse $nurse */
        $nurse = $member->getNurse();

        // NOTE: comparing the expiration dates below against '$today' is NOT the same as the
        // below 'isValidForNurse' check. These logical checks are for quickly returning in
        // the case that we are beyond the exp date for these documents
        $today = new DateTime('now');
        $credential = $nurse->getCredentials();

        if ($nurse->getSkinTestExpirationDate() <= $today) {
           $response->data['response']['shifts'] = [];
           $response->data['response']['skin_test_expired'] = true;
           $response->data['response']['success'] = true;
           return $response;
        }

        if ($nurse->getLicenseExpirationDate() <= $today) {
            $response->data['response']['shifts'] = [];
            $response->data['response']['license_expired'] = true;
            $response->data['response']['success'] = true;
            return $response;
        }

        // LPN/RN cannot work without a valid CPR license
        if ($credential == 'LPN' || $credential == 'RN') {
            if ($nurse->getCprExpirationDate() <= $today) {
                $response->data['response']['shifts'] = [];
                $response->data['response']['cpr_expired'] = true;
                $response->data['response']['success'] = true;
                return $response;
            }
        }

        $shiftService = new ShiftService();

        $requestData = [
            'start' => ['date' => $date],
            'end' => ['date' => $date],
            'nurse_id' => null,
            'member_id' => $memberId,
            'category_id' => null,
            'nurse_type' => null,
            'nurse_credential' => $credential,
            'mobile' => true,
            'states_approved_to_work' => $nurse->getStatesAbleToWorkAbbreviated(),
        ];
        $shiftData = $shiftService->loadCalendarShifts($requestData);

        $approvedShifts = [];
        $finished = false;
        $shifts = [];
        foreach ($shiftData['shifts'] as $shift) {
            if ($shift['status'] == 'Approved') {
                if ($shift['nurse_id'] == $nurse->getId()) {
                    $shifts = [$shift];
                    $finished = true;
                    break;
                }
                $approvedShifts[] = $shift;
            }
        }

        if (!$finished) {
            $providerService = new ProviderService();
            $today = new DateTime('now', app::getInstance()->getTimeZone());
            foreach ($shiftData['shifts'] as $shift) {
                if ($shift['nurse_id'] && $shift['nurse_id'] != $nurse->getId()) {
                    continue;
                }

                if (!$shift['nurse_id'] && $shift['status'] != 'Open') {
                    continue;
                }
                if (!$shiftService->isValidForNurse($shift['id'], $nurse)) {
                    continue;
                }

                if ($providerService->hasBlockedNurse($shift['provider_id'], $nurse->getId())) {
                    continue;
                }

                // Credentials Check
                $nurseTypes = explode('/', trim($shift['nurse_type']));
                if (is_array($nurseTypes) && in_array('CNA', $nurseTypes)) {
                    if (!in_array('CMT', $nurseTypes))
                        $nurseTypes[] = 'CMT';
                }
                if (!in_array($nurse->getCredentials(), $nurseTypes)) {
                    if($nurse->getCredentials() == 'CMT') {
                        if (!in_array('CNA', $nurseTypes)) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                // TODO - Check this DateTime
                $shiftDate = new DateTime($shift['start'], app::getInstance()->getTimeZone());
                if ($shiftDate->format('Ymd') != $date->format('Ymd')) {
                    continue;
                }
                if ($shiftDate < $today && $shift['status'] != 'Completed') {
                    continue;
                }

                if ($shift['status'] == 'Approved' && $shift['nurse_id'] != $nurse->getId()) {
                    continue;
                }

                // Check for conflicts with approved shifts
                if ($shift['status'] != 'Approved') {
                    $shouldContinue = false;
                    foreach ($approvedShifts as $approvedShift) {
                        if ($approvedShift['id'] != $shift['id'] && $approvedShift['nurse_id'] == $nurse->getId() && $shiftService->isConflicting($shift['start'], $shift['end'], $approvedShift['start'], $approvedShift['end'])) {
                            $shouldContinue = true;
                            continue;
                        }
                    }
                    if ($shouldContinue) {
                        continue;
                    }
                }

                if ($shift['is_recurrence']) {
                    // If the recurrence exists in the database, it should show its id, otherwise show the unique_id as its id
                    if ($shift['recurrence_id']) {
                        $shift['id'] = $shift['recurrence_id'];
                    } else {
                        $shift['id'] = $shift['unique_id'];
                    }
                }
                if (!$shift['unique_id']) {
                    $shift['unique_id'] = "";
                }
                $shifts[] = $shift;
            }
        }
        $response->data['response']['shifts'] = $shifts;
        $response->data['response']['success'] = true;

        return $response;
    }

    /**
     * Load the data for a single shift to display to a nurse
     * @param Request $request
     * @returns ApiJsonResponse
     */
    public function getShiftData($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $shiftService = new ShiftService();
        $member = modRequest::request('auth.member', false);
        if ($member) {
            $shiftData = $shiftService->getShiftData($data, $member);
        } else {
            $shiftData = $shiftService->getShiftData($data);
        }

        $response->data['response']['success'] = true;
        $response->data['response']['shift'] = $shiftData;
        return $response;
    }

    /**
     * Request a shift (or shift recurrence) from the app
     * @param Request $request
     * @returns ApiJsonResponse
     */
    public static function requestShift($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $data['user_type'] = "nurse";

        $shiftService = new ShiftService();
        try {
            $response->data['response'] = $shiftService->requestShift($data);
        } catch (\Throwable $e) {
            echo $e->getMessage();
            exit;
        }
        return $response;
    }

    /**
     * Cancel a shift (or shift recurrence) from the app
     * @param Request $request
     * @returns ApiJsonResponse
     */
    public function cancelShift($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $data['user_type'] = "nurse";

        app::get()->getLogger()->addError("ShiftApiV1Controller->cancelShift data: ", $data);

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->cancelShift($data);

        return $response;
    }

    /**
     * Accept a shift (or shift recurrence) from the app
     * @param Request $request
     * @returns ApiJsonResponse
     */
    public function acceptShift($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $data['user_type'] = "nurse";

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->acceptShift($data);

        return $response;
    }

    /**
     * Decline a shift (or shift recurrence) from the app
     * @param Request $request
     * @returns ApiJsonResponse
     */
    public function declineShift($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $data['user_type'] = "nurse";

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->declineShift($data);

        return $response;
    }

    public function setIsPreferred($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $data['user_type'] = "nurse";

        $providerService = new ProviderService();
        $response->data['response'] = $providerService->setIsPreferred($data);

        return $response;
    }

    public function clockIn($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);
        $data['user_type'] = "nurse";

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->clockIn($data);

        return $response;
    }

    public function clockOut($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->clockOut($data);

        return $response;
    }

    public function startLunch($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->startLunch($data);

        return $response;
    }

    public function endLunch($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->endLunch($data);

        return $response;
    }

    public function getDashboardData($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->getDashboardData($data);

        return $response;
    }

    public static function getUpcomingShiftsForNurse($request)
    {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $service = new ShiftService();
        $response->data['response'] = $service->getUpcomingShiftsForNurse($data);

        return $response;
    }

    public static function getCalendarStatusBubbles($request)
    {
        try {
            $response = new ApiJsonResponse();
            $data = json_decode($request->getContent(), true);
    
            $service = new ShiftService();
            $response->data['response']['data']['days'] = $service->getShiftsInMonthForNurse($data);
    
            return $response;
        } catch (Throwable  $e) {
            $response = new ApiJsonResponse();
            $response->data['response']['data']['success'] = false;
            $response->data['response']['data']['error'] = $e->getMessage();
            $response->data['response']['data']['errorFile'] = $e->getFile();
            $response->data['response']['data']['errorFileLine'] = $e->getLine();
            return $response;
        }
    }
    
    public static function createShiftOverride($request): ApiJsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $shiftService = new ShiftService();
        $shiftId = $data['shift_id'];
        $supervisorName = $data['supervisor_name'];
        $supervisorCode = $data['supervisor_code'];
        $supervisorSignatureBase64 = $data['supervisor_signature'];

        $saf = new saFile();
        /** @var NstFile $currentFile */
        $currentFile = $saf->saveStringToFile($shiftId . 'override.png', base64_decode($supervisorSignatureBase64));
        app::$entityManager->persist($currentFile);
        app::$entityManager->flush();
        $shiftOverride = $shiftService->createOverrideForShift($shiftId, $supervisorName, $supervisorCode, $currentFile);

        $response = new ApiJsonResponse();
        $response->data['response'] = [
            'success' => true,
            'data' => $shiftOverride
        ];

        return $response;
    }

    public static function getShiftConfiguration($request): ApiJsonResponse
    {
        $response = new ApiJsonResponse();
        $config = [
            'enable_gps' => app::get()->getConfiguration()->get('enable_gps')->getValue()
        ];

        $response->data['response'] = $config;
        return $response;
    }

    public static function goOnBreak($request): ApiJsonResponse {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->goOnBreak($data['shift_id']);

        return $response;
    }

    public static function endBreak($request): ApiJsonResponse {
        $response = new ApiJsonResponse();
        $data = json_decode($request->getContent(), true);

        $shiftService = new ShiftService();
        $response->data['response'] = $shiftService->endBreak($data['shift_id']);

        return $response;
    }
}
