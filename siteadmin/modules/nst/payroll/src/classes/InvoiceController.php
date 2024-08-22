<?php


namespace nst\payroll;


use sacore\application\controller;
use sacore\application\responses\View;
use sa\member\auth;

class InvoiceController extends controller
{
    public function viewInvoices($request) {
        $view = new View('invoices_list');
        $provider = auth::getAuthMember()->getProvider();

        $view->data['provider_id'] = $provider->getId();
        return $view;
    }

    public static function loadInvoices($data) {
        $invoiceService = new InvoiceService();

        $response = $invoiceService->loadInvoices($data);

        return $response;
    }



}