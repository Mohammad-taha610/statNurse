<?php

namespace nst\payroll;

use sacore\application\Request;
use sacore\application\responses\View;
use sacore\application\saController;

class SaInvoiceController extends saController
{
    public function addInvoice($request): View
    {
        $view = new View('sa_edit_invoice');

        $view->data['id'] = 0;
        return $view;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function viewGenerateInvoice($request): View
    {
        $view = new View('sa_generate_invoice');

        $providerId = $request->getRouteParams()->get('provider_id');
        $payPeriod = $request->getRouteParams()->get('pay_period');
        $view->data['provider_id'] = $providerId ?: 0;
        $view->data['pay_period'] = $payPeriod ?: '';

        return $view;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function viewInvoice($request): View
    {
        $view = new View('sa_view_invoice');

        $view->data['code'] = $_GET['code'];
        $view->data['state'] = $_GET['state'];
        $view->data['realmId'] = $_GET['realmId'];
        $view->data['id'] = $_SESSION['invoice_id'];
        $view->data['provider_id'] = $_SESSION['provider_id'];

        return $view;
    }

    /**
     * @param Request $request
     */
    public function editInvoice($request): View
    {
        $view = new View('sa_edit_invoice');
        $id = $request->getRouteParams()->get('id');

        $view->data['id'] = $id;
        return $view;
    }

    public function viewInvoices($request): View
    {
        return new View('sa_view_invoices');
    }

    public function generate1099($request): View
    {
        return new View('sa_generate_1099');
    }

    public static function loadAdminInvoices($data): array
    {
        $invoiceService = new InvoiceService();

        return $invoiceService->loadAdminInvoices($data);
    }

    public static function loadInvoiceData($data): array
    {
        $invoiceService = new InvoiceService();

        return $invoiceService->loadInvoiceData($data);
    }

    public static function saveInvoiceData($data): array
    {
        $invoiceService = new InvoiceService();

        return $invoiceService->saveInvoiceData($data);
    }

    public static function generateInvoice($data): array
    {
        $invoiceService = new InvoiceService();

        return $invoiceService->generateInvoice($data);
    }

    public static function showInvoice($data) {
        $invoiceService = new InvoiceService();

        return $invoiceService->showInvoice($data);
    }

    public static function deleteInvoice($data): array
    {
        $invoiceService = new InvoiceService();

        return $invoiceService->deleteInvoice($data);
    }
}