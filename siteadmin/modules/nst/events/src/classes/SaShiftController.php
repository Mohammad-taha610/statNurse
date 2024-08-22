<?php

namespace nst\events;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use nst\member\NstMember;
use nst\member\Provider;
use nst\member\ProviderService;
use nst\messages\SmsService;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use sacore\application\app;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Request;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sa\events\SaEventsController;
use sa\member\auth;
use sa\system\saUser;
use sacore\utilities\notification;

/**
 * @IOC_NAME="SaEventsController"
 */
class SaShiftController extends SaEventsController
{
    public function viewShiftCalendar($request): View
    {
        $providerId = $_GET['p'];
        $nurseId = $_GET['n'];
        $categoryId = $_GET['c'];

        $view = new View('sa_shift_calendar', $this->viewLocation(), false);
        $view->data['provider_id'] = $providerId ?: 1;
        $view->data['nurse_id'] = $nurseId ?: 0;
        $view->data['category_id'] = $categoryId ?: 0;
        return $view;
    }

    /** @param Request $request */
    public function viewProviderShiftCalendar($request): View
    {
        $view = new View('sa_shift_calendar', $this->viewLocation(), false);
        $memberId = $request->getRouteParams()->get('member_id');
        $member = ioc::get('NstMember', ['id' => $memberId]);


        $view->data['provider_id'] = $member && $member->getProvider() ? $member->getProvider()->getId() : 0;
        return $view;
    }

    public static function loadShiftCalendarData($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadShiftCalendarData($data);
    }

    public function shiftRequests($request): View
    {
        return new View('sa_shift_requests');
    }

    public function createShift($request): View
    {
        $view = new View('sa_edit_shift', $this->viewLocation());
        $view->data['id'] = 0;
        return $view;
    }

    public function shiftActionLog($request): View
    {
//        $saShiftLogger = new SaShiftLogger();
//        $saShiftLogger->log("test delete", ['action' => 'DELETED']);
//        $saShiftLogger->log("test cancel", ['action' => 'CANCELLED']);
//        $saShiftLogger->log("test approve", ['action' => 'APPROVED']);
//        $saShiftLogger->log("test denied", ['action' => 'DENIED']);
        return new View('sa_shift_action_log');
    }

    public function shiftTests($request): View
    {
        $view = new View('shift_tests', $this->viewLocation());
        return $view;
    }

    public function edit($request): View
    {
        $id = $request->getRouteParams()->get('id');
        $view = new View('sa_edit_shift', $this->viewLocation());
        $view->data['id'] = $id;

        $providerId = 0;
        $nurseId = 0;
        if($id) {
            $shift = ioc::get('Shift', ['id' => $id]);
            $providerId = $shift->getProvider() ? $shift->getProvider()->getId() : 0;
            $nurseId = $shift->getNurse() ? $shift->getNurse()->getId() : 0;
        }
        $view->data['source_id'] = 0;
        $view->data['provider_id'] = $providerId;
        $view->data['nurse_id'] = $nurseId;
        $view->data['recurrence_id'] = 0;
        $view->data['recurrence_unique_id'] = 0;
        $view->data['recurrence_source_id'] = 0;
        $view->data['is_recurrence'] = 0;
        $view->data['is_copy'] = 0;
        $view->data['start_date'] = 0;
        $view->data['end_date'] = 0;
        return $view;
    }

    public function copyShift($request): View
    {
        $id = $request->getRouteParams()->get('id');
        $view = new View('sa_edit_shift', $this->viewLocation());
        $view->data['source_id'] = $id;
        $view->data['id'] = 0;
        $view->data['is_copy'] = true;
        return $view;
    }

    public static function saveShift($data): Redirect|array
    {
        $data = $data['params'];
        if(empty($data)) {
            //Todo: this needs to be changed
            return new Redirect(app::get()->getRouter()->generate('sa_events_index'));
        }

        $notify = new notification();
        $data['notification'] = $notify;

        $saShiftService = new SaShiftService();

        $response = [];
        try {
            $shiftArray = $saShiftService->saveShift($data);
            app::get()->getLogger()->addError('SaShiftController::saveShift - shiftArray: ' . json_encode($shiftArray));
            if (!$shiftArray['success']) {
                $response = $shiftArray;
                $notify->addNotification('danger', 'Error', $shiftArray['message']);
                return $response;
            }
            $notify->addNotification('success', 'Success', 'Shift saved successfully.');

            /** @var Provider $provider */
            ioc::get('Provider', ['id' => $data['provider_id']]);
            $response['success'] = true;
            $response['url'] = app::get()->getRouter()->generate('sa_shift_calendar');

            // send twilio sms
            // Move all this into a logging service another time...
            /** @var Shift $shift */
            $shift = ioc::get('Shift', ['id' => $shiftArray['shift_id']]);
            if ($shift) {
                $nurse = $shift->getNurse();
                if ($data['action_type'] === 'create') {
                    $intro = ($data['status'] === 'Pending' ? 'A ' : 'An ') . ucfirst($data['status']);
                    $intro = $intro === 'A Pending' ? 'A new ' : $intro;
                    $date = $shift->getStart()->format('m/d/Y');
                    $time = $shift->getStart()->format('H:i');
                    $smsService = new SmsService();
                    if ($nurse) {
                        $smsService->handleSendSms($shift, ['message_type' => 'created_shift', 'by' => 'siteadmin', 'nurse' => $nurse]);
                    } else {
                        $smsService->handleSendSms($shift, ['message_type' => 'created_shift', 'by' => 'siteadmin']);
                    }

                    /** @var saUser $currentUser */
                    if ($nurse) {
                        $nurseCreds = $nurse->getCredentials();
                        $nurse_name = $nurse->getMiddleName() !== '' ? $nurse->getFirstName() . ' ' . $nurse->getMiddleName() . ' ' . $nurse->getLastName() : $nurse->getFirstName() . ' ' . $nurse->getLastName();
                        $logBody = $intro . ' Shift for ' . $nurse_name . ' ('.$nurseCreds.') on ' . $date . ' at ' . $time . ' has been CREATED by NurseStat';
                    } else {
                        $logBody = $intro . ' Shift on ' . $date . ' at ' . $time . ' has been CREATED by NurseStat';
                    }
                    $currentUser = modRequest::request('sa.user');
                    $saShiftLogger = new SaShiftLogger();
                    $logMessage = $logBody . ' - User: ' . $currentUser?->getFirstName() . ' ' . $currentUser?->getLastName();
                    $saShiftLogger->log($logMessage, ['action' => 'CREATED']);
                }
            }

        } catch(ValidateException|IocDuplicateClassException|IocException|ModRequestAuthenticationException|OptimisticLockException|ORMException|InvalidArgument|InvalidRRule|Exception $e) {
            $response['success'] = false;
            $response['error'] = $e->getMessage();
            $response['errorCode'] = $e->getCode();
            if(!$e->getCode()) {
                $notify->addNotification('danger', 'Error', $e->getMessage());
            }
        }

        return $response;
    }

    /** @deprecated */
    public static function loadRecurrenceData($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadRecurrenceData($data);
    }


    public static function loadShiftData($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadShiftData($data);
    }

    public static function loadProviders(): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadProviders();
    }

    public static function loadNurses(): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadNurses();
    }

    public static function loadCalendarFilters(): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadCalendarFilters();
    }

    public static function deleteShift($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->deleteShift($data);
    }

    public static function loadAssignableNurses($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadAssignableNurses($data);
    }

    public static function loadCategories(): Json
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadCategories();
    }

    public static function loadShiftRequests($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->loadShiftRequests($data);
    }

    public static function approveShiftRequest($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->approveShiftRequest($data);
    }

    public static function denyShiftRequest($data): array
    {
        $saShiftService = new SaShiftService();

        return $saShiftService->denyShiftRequest($data);
    }

    public static function massDeleteShifts($data): array
    {
        $shiftService = new SaShiftService();

        return $shiftService->massDeleteShifts($data);
    }
    public static function callInShift($data): array
    {
        $shiftService = new SaShiftService();

        return $shiftService->callInShift($data);
    }
}