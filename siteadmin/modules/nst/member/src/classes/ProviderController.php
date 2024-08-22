<?php


namespace nst\member;

use nst\events\ShiftService;
use sacore\application\app;
use sacore\application\controller;
use sacore\application\saController;
use sacore\application\responses\View;
use sacore\application\ioc;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sa\member\auth;


class ProviderController extends controller
{

    public function listDoNotReturn($request): View
    {
        return new View('do_not_return_list');
    }

    public static function loadDoNotReturnList(): array
    {
        $providerService = new ProviderService();

        return $providerService->loadDoNotReturnList();
    }

    public static function loadAssignableNurses($data): array
    {
        $providerService = new ProviderService();

        return $providerService->loadAssignableNurses($data);
    }

    public static function loadShiftRequests(): array
    {
        $providerService = new ProviderService();

        return $providerService->loadShiftRequests();
    }

    public static function approveShiftRequest($data): array
    {
        $providerService = new ProviderService();

        return $providerService->approveShiftRequest($data);
    }

    public static function denyShiftRequest($data): array
    {
        $providerService = new ProviderService();

        return $providerService->denyShiftRequest($data);
    }

    public static function blockNurse($data): array
    {
        $providerService = new ProviderService();

        return $providerService->blockNurse($data);
    }

    public static function unblockNurse($data): array
    {
        $providerService = new ProviderService();

        return $providerService->unblockNurse($data);
    }

    public static function loadDashboardData($data): array
    {
        $providerService = new ProviderService();

        return $providerService->loadDashboardData($data);
    }

    public static function loadProfileData($data): array
    {
        $providerService = new ProviderService();

        return $providerService->loadProfileData($data);
    }

    public static function loadUpcomingProviderShifts($data): array
    {
        $providerService = new ProviderService();

        return $providerService->loadUpcomingProviderShifts($data);
    }

    public static function saveProviderInfo($data): array
    {
        $providerService = new ProviderService();

        return $providerService->saveProviderInfo($data);
    }

    public static function importProviders($data) {
        $providerService = new ProviderService();

        return $providerService->importProviders($data);
    }

    public static function updateTravelAndStipendInfo() {
        $service = new ProviderService();
        return $service->updateTravelAndStipendInfo();
    }

    public static function pbjReport($data) {
        $service = new ProviderService();
        return $service->pbjReport($data);
    }

    public static function loadNurseFiles($data) {
        $service = new ProviderService();
        return $service->loadNurseFiles($data);
    }

    public static function getNurseCredentials($data) {
        $service = new ProviderService();
        return $service->getNurseCredentials($data);
    }

    public static function getPresetShiftTimes($data) {
        $service = new ProviderService();
        return $service->getPresetShiftTimes($data);
    }

    public static function getPayRates($data) {
        $service = new ProviderService();
        return $service->getPayRates($data);
    }

    public static function getAvailableNurses($data): array
    {
        $service = new ProviderService();
        return $service->getAvailableNurses($data);
    }


    public static function getCreateShiftData($data): array
    {
        $response = ['success' => false];
        $providerService = new ProviderService();
        $shiftService = new ShiftService();

        /** @var NstMemberUser $user */
        $user = auth::getAuthUser();

        $response['credentials'] = $providerService->getNurseCredentials($data)['credentials'];
        $response['presetShiftTimes'] = $providerService->getPresetShiftTimes($data)['presetShiftTimes'];
        $response['payRates'] = $providerService->getPayRates($data)['payRates'];
        $response['shiftCategories'] = $shiftService->getAllShiftCategories()['categories'];
        $response['userType'] = $user->getUserType();


        $response['success'] = true;
        return $response;
    }

    public static function saveNewShift($data): array
    {
        $service = new ProviderService();
        return $service->handleSaveNewShift($data);
    }

    public static function cancelShift($data)
    {
        $service = new ProviderService();
        return $service->cancelShift($data);
    }

    public static function providerGetNurseFiles($data)
    {
        $service = new ProviderService();
        return $service->providerGetNurseFiles($data);
    }
}
