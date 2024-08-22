<?php


namespace nst\payroll;

use nst\member\NstMember;
use nst\member\Nurse;
use nst\member\Provider;
use sacore\application\app;
use sacore\application\controller;
use sacore\application\DateTime;
use sacore\application\ioc;
use sacore\application\responses\View;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sa\files\saFile;
use sa\member\auth;

class PayrollController extends controller {

    public function viewCurrentPayPeriod() {
        $payrollService = new PayrollService();

        $period = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $view = new View('pay_period');
        $view->data['period'] = $period['combined'];
        $view->data['provider_id'] = $provider->getId();
        $view->data['unresolved_only'] = 0;
        return $view;
    }

    public function viewUnresolvedPay() {
        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $view = new View('pay_period');
        $view->data['period'] = 'all';
        $view->data['provider_id'] = $provider->getId();
        $view->data['unresolved_only'] = true;
        return $view;
    }

    public function viewPaymentHistory() {
        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $view = new View('pay_period');
        $view->data['period'] = 'all';
        $view->data['provider_id'] = $provider->getId();
        $view->data['unresolved_only'] = 0;
        return $view;
    }

    public static function getPayPeriods($data) {
        $payrollService = new PayrollService();

        return $payrollService->getPayPeriods($data);
    }

    public static function getShiftPayments($data) {
        $payrollService = new PayrollService();

        return $payrollService->getShiftPayments($data);
    }

    public static function getNursePayments($data) {
        $payrollService = new PayrollService();

        return $payrollService->getNursePayments($data);
    }

    public static function getReportInPdf($data) {
        $payrollService = new PayrollService();
        return $payrollService->getReportPdf($data);
    }

    public static function getReportInExcel($data) {
        $payrollService = new PayrollService();
        return $payrollService->getReportInExcel($data);
    }


    public static function resolvePayment($data) {
        $payrollService = new PayrollService();
        return $payrollService->resolvePayment($data);
    }

    public static function requestChange($data) {
        $payrollService = new PayrollService();
        return $payrollService->requestChange($data);
    }

    public static function cancelChangeRequest($data) {
        $payrollService = new PayrollService();
        return $payrollService->cancelChangeRequest($data);
    }

    public static function fixShiftTimezones() {
        $service = new PayrollService();
        return $service->fixShiftTimezones();
    }

    public function viewPbjReport() {
        $payrollService = new PayrollService();

        $period = $payrollService->calculatePayPeriodFromDate(new DateTime('now', app::getInstance()->getTimeZone()));

        /** @var NstMember $member */
        $member = auth::getAuthMember();
        $provider = $member->getProvider();

        $view = new View('pbj_report');
        $view->data['period'] = $period['combined'];
        $view->data['provider_id'] = $provider->getId();
        return $view;
    }
}
