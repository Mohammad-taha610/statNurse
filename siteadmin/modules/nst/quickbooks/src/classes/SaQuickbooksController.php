<?php

namespace nst\quickbooks;

use sacore\application\responses\View;
use sacore\application\saController;

class SaQuickbooksController extends \sacore\application\saController
{
    public function viewTest($request) {
        $view = new View('quickbooks_test');

        $view->data['code'] = $_GET['code'];
        $view->data['state'] = $_GET['state'];
        $view->data['realmId'] = $_GET['realmId'];
        return $view;
    }

    public static function runTest($data) {
        $quickbooksService = new QuickbooksService();

        $response = $quickbooksService->runTest($data);

        return $response;
    }

    public function generateQuickbooksInvoice($data) {
        $quickbooksService = new QuickbooksService();

        $response = $quickbooksService->generateQuickbooksInvoice($data);

        return $response;
    }

    public static function getAuthRoute($data) {
        $quickbooksService = new QuickbooksService();

        $response = $quickbooksService->getAuthRoute($data);

        return $response;
    }

    public function viewExportVendors($request) {
        $view = new View('sa_export_vendors');

        return $view;
    }
    public function viewExportVendorsConfirmation($request) {
        $view = new View('sa_export_vendors_confirmation');

        $view->data['code'] = $_GET['code'];
        $view->data['state'] = $_GET['state'];
        $view->data['realmId'] = $_GET['realmId'];

        return $view;
    }
    public function viewExportCustomers($request) {
        $view = new View('sa_export_customers');

        return $view;
    }
    public function viewExportCustomersConfirmation($request) {
        $view = new View('sa_export_customers_confirmation');

        $view->data['code'] = $_GET['code'];
        $view->data['state'] = $_GET['state'];
        $view->data['realmId'] = $_GET['realmId'];

        return $view;
    }

    public function viewSendPaymentsConfirmation($request) {
        $view = new View('sa_send_payments_confirmation');

        $view->data['code'] = $_GET['code'];
        $view->data['state'] = $_GET['state'];
        $view->data['realmId'] = $_GET['realmId'];
        $view->data['payment_ids'] = $_SESSION['payment_ids'];

        return $view;
    }

    public static function getExportVendorsRoute($data) {
        $service = new QuickbooksService();
        $response = $service->getExportVendorsRoute($data);
        return $response;
    }
    public static function exportVendors($data) {
        $service = new QuickbooksService();
        $response = $service->exportVendors($data);
        return $response;
    }
    public static function getExportCustomersRoute($data) {
        $service = new QuickbooksService();
        $response = $service->getExportCustomersRoute($data);
        return $response;
    }
    public static function exportCustomers($data) {
        $service = new QuickbooksService();
        $response = $service->exportCustomers($data);
        return $response;
    }

    public static function getPaymentsAuthRoute($data) {
        $service = new QuickbooksService();
        $response = $service->getPaymentsAuthRoute($data);
        return $response;
    }
    public static function sendPaymentsToQuickbooks($data) {
        $service = new QuickbooksService();
        $response = $service->sendPaymentsToQuickbooks($data);
        return $response;
    }

}
