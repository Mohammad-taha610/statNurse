<?php


namespace nst\payroll;

use sacore\application\app;
use sacore\application\modRequest;
use sacore\application\navItem;

class payrollConfig extends \sacore\application\moduleConfig{

    static function init()
    {
        // OLD
        modRequest::listen('payroll.payroll_item.save', 'payrollItemController@save', 1, null, true, false);
        modRequest::listen('payroll.payroll_item.load', 'payrollItemController@loadVue', 1, null, true, false);
        modRequest::listen('payroll.payroll_item.load.payroll', 'payrollItemController@loadVuePayroll', 1, null, true, false);
        modRequest::listen('payroll.payroll_item.load.scheduledShift', 'payrollItemController@loadVueScheduledShift', 1, null, true, false);

//        modRequest::listen('inventory.kit.category.unlink', 'kitController@unlinkCategoryFromKit', 1, null, true, false);
//        modRequest::listen('inventory.generate.barcode', 'kitController@generateBarCode', 1, null, true, false);
        // PROVIDER PAYROLL
        modRequest::listen('payroll.payroll.save', 'PayrollController@save', 1, null, true, false);
        modRequest::listen('payroll.payroll.load', 'PayrollController@loadVue', 1, null, true, false);
        modRequest::listen('payroll.get_pay_periods', 'PayrollController@getPayPeriods', 1, null, true, false);
        modRequest::listen('payroll.get_shift_payments', 'PayrollController@getShiftPayments', 1, null, true, false);
        modRequest::listen('payroll.get_nurse_payments', 'PayrollController@getNursePayments', 1, null, true, false);
        modRequest::listen('payroll.resolve_payment', 'PayrollController@resolvePayment', 1, null, true, false);
        modRequest::listen('payroll.request_change', 'PayrollController@requestChange', 1, null, true, false);
        modRequest::listen('payroll.cancel_change_request', 'PayrollController@cancelChangeRequest', 1, null, true, false);
        modRequest::listen('sa.payroll.report_to_pdf', 'PayrollController@getReportInPdf', 1, null, true, false);
        modRequest::listen('sa.payroll.report_to_excel', 'PayrollController@getReportInExcel', 1, null, true, false);
        // PROVIDER INVOICES
        modRequest::listen('payroll.load_invoices', 'InvoiceController@loadInvoices', 1, null, true, false);


        // SITEADMIN PAYROLL
        modRequest::listen('sa.payroll.get_pay_periods', 'SaPayrollController@getPayPeriods', 1, null, true, false);
        modRequest::listen('sa.payroll.get_shift_payments', 'SaPayrollController@getShiftPayments', 1, null, true, false);
        modRequest::listen('sa.payroll.get_nurse_payments', 'SaPayrollController@getNursePayments', 1, null, true, false);
        modRequest::listen('sa.payroll.get_nurse_shift_payments_for_reports', 'SaPayrollController@getNursePaymentsForReports', 1, null, true, false);
        modRequest::listen('sa.payroll.get_single_nurse_shift_payments_for_reports', 'SaPayrollController@getSingleNursePaymentsForReports', 1, null, true, false);
        modRequest::listen('sa.payroll.resolve_payment', 'SaPayrollController@resolvePayment', 1, null, true, false);
        modRequest::listen('sa.payroll.save_payment_changes', 'SaPayrollController@savePaymentChanges', 1, null, true, false);
        modRequest::listen('sa.payroll.save_payment_changes_for_reports', 'SaPayrollController@savePaymentChangesForReports', 1, null, true, false);
        modRequest::listen('sa.payroll.create_payment', 'SaPayrollController@saveManualPayment', 1, null, true, false);
        modRequest::listen('sa.payroll.generate_nacha_file', 'SaPayrollController@generateNachaFile', 1, null, true, false);
        modRequest::listen('sa.payroll.load_payment_data', 'SaPayrollController@loadPaymentData', 1, null, true, false);
        modRequest::listen('sa.payroll.mark_all_as_paid', 'SaPayrollController@markAllAsPaid', 1, null, true, false);
        modRequest::listen('sa.payroll.find_conflicting_payments', 'SaPayrollController@findConflictingPayments', 1, null, true, false);
        modRequest::listen('sa.payroll.delete_payment', 'SaPayrollController@deletePayment', 1, null, true, false);
        modRequest::listen('sa.payroll.soft_delete_payment', 'SaPayrollController@softDeletePayment', 1, null, true, false);
        modRequest::listen('sa.payroll.get_payments_by_shift', 'SaPayrollController@getPaymentsByShift', 1, null, true, false);
        modRequest::listen('sa.payroll.gen_1099s', 'SaPayrollController@generate1099s', 1, null, true, false);
        modRequest::listen('sa.payroll.get_nurses_with_shift', 'SaPayrollController@getNursesWithShift', 1, null, true, false);
        modRequest::listen('sa.payroll.check_for_1099', 'SaPayrollController@checkFor1099', 1, null, true, false);
        modRequest::listen('sa.payroll.export_1099s', 'SaPayrollController@export1099s', 1, null, true, false);
        modRequest::listen('sa.payroll.gen_1099_export_group', 'SaPayrollController@gen1099ExportGroup', 1, null, true, false);
        modRequest::listen('sa.payroll.get_inactive_nurses', 'SaPayrollController@getInactiveNurses', 1, null, true, false);
        modRequest::listen('sa.payroll.get_inactive_providers', 'SaPayrollController@getInactiveProviders', 1, null, true, false);
        modRequest::listen('sa.payroll.get_dnr_nurse_report', 'SaPayrollController@getDnrNurseReport', 1, null, true, false);
        modRequest::listen('sa.payroll.get_dnr_provider_report', 'SaPayrollController@getDnrProviderReport', 1, null, true, false);

        modRequest::listen('sa.payroll.get_earnings_report', 'SaPayrollController@getEarningsReport', 1, null, true, false);
        modRequest::listen('sa.payroll.get_earnings_report_state', 'SaPayrollController@getEarningsReportState', 1, null, true, false);
        modRequest::listen('sa.payroll.get_shifts_report', 'SaPayrollController@getShiftsReport', 1, null, true, false);
        modRequest::listen('sa.payroll.get_shifts_report_nurse', 'SaPayrollController@getShiftsReportNurse', 1, null, true, false);
        modRequest::listen('sa.payroll.get_schedule_report', 'SaPayrollController@getScheduleReport', 1, null, true, false);
        modRequest::listen('sa.payroll.get_schedule_report_nurse', 'SaPayrollController@getScheduleReportNurse', 1, null, true, false);
        modRequest::listen('sa.payroll.get_all_nurse_names', 'SaPayrollController@getAllNurseNames', 1, null, true, false);
        modRequest::listen('sa.payroll.get_pay_summary_pdf', 'SaPayrollController@getPayStubPDF', 1, null, true, false);
        modRequest::listen('sa.payroll.download_pay_summary_pdf', 'SaPayrollController@downloadPayStubPDF', 1, null, true, false);


        // SITEADMIN INVOICES
        modRequest::listen('sa.invoices.load_invoices', 'SaInvoiceController@loadAdminInvoices', 1, null, true, false);
        modRequest::listen('sa.invoices.load_invoice_data', 'SaInvoiceController@loadInvoiceData', 1, null, true, false);
        modRequest::listen('sa.invoices.generate_invoice', 'SaInvoiceController@generateInvoice', 1, null, true, false);
        modRequest::listen('sa.invoices.save_invoice_data', 'SaInvoiceController@saveInvoiceData', 1, null, true, false);
        modRequest::listen('sa.invoices.show_invoice', 'SaInvoiceController@showInvoice', 1, null, true, false);
        modRequest::listen('sa.invoices.delete_invoice', 'SaInvoiceController@deleteInvoice', 1, null, true, false);

        // PAYCARD FUNDING FILE GENERATION
        modRequest::listen('sa.payroll.generate_paycard_file_xlsx', 'SaPayrollController@generatePaycardFileXlsx', 1, null, true, false);
        modRequest::listen('sa.payroll.generate_paycard_file_csv', 'SaPayrollController@generatePaycardFileCsv', 1, null, true, false);


				// CHECKR PAY BATCH UPLOAD
				modRequest::listen('sa.payroll.generate_checkr_pay_file', 'SaPayrollController@generateCheckrPayCsv', 1, null, true, false);

        // Government reporting
        modRequest::listen('sa.payroll.get.governement.report', 'GovernmentReportingController@getReport', 1, null, true, false);
        modRequest::listen('sa.payroll.generate.governement.report', 'GovernmentReportingController@generateReport', 1, null, true, false);
        modRequest::listen('sa.payroll.generate.governement.report.batch', 'GovernmentReportingController@generateReportBatch', 1, null, true, false);
        modRequest::listen('sa.payroll.generate.governement.report.zip', 'GovernmentReportingController@generateReportZip', 1, null, true, false);
        modRequest::listen('sa.payroll.get.governement.report.batching.data', 'GovernmentReportingController@getBatchingData', 1, null, true, false);

    }


    static function postInit()
    {
//        modRequest::request('sa.dashboard.add_widget', null, array('id'=>'ncp.inventory.quarantined_kits', 'name'=>'Quarantined Kits', 'action'=>'kitController@getQuarantinedKitsWidget'));
    }


    public static function initRoutes($routes){
        // FRONT END
        $routes->addWithOptionsAndName('Provider View Payment History', 'provider_payment_history', '/payroll/history')->controller('PayrollController@viewPaymentHistory')->middleware('AuthMiddleware')->middleware('ProviderAdminMiddleware');
        $routes->addWithOptionsAndName('Provider View Current Pay Period', 'provider_current_pay_period', '/payroll/period/current')->controller('PayrollController@viewCurrentPayPeriod')->middleware('AuthMiddleware')->middleware('ProviderAdminMiddleware');
        $routes->addWithOptionsAndName('Provider View Unresolved Pay', 'provider_unresolved_pay', '/payroll/unresolved')->controller('PayrollController@viewUnresolvedPay')->middleware('AuthMiddleware')->middleware('ProviderAdminMiddleware');
        $routes->addWithOptionsAndName('Provider View Invoices', 'provider_invoices', '/invoices')->controller('InvoiceController@viewInvoices')->middleware('AuthMiddleware')->middleware('ProviderAdminMiddleware');
        $routes->addWithOptionsAndName('Provider View PBJ Report', 'pbj_report', '/pbj_report')->controller('PayrollController@viewPbjReport')->middleware('AuthMiddleware')->middleware('ProviderAdminMiddleware');


        // SITEADMIN
        $routes->addWithOptionsAndName('Payment History', 'sa_payment_history', '/siteadmin/payroll/history')->controller('SaPayrollController@viewPaymentHistory')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Current Pay Period', 'sa_current_pay_period', '/siteadmin/payroll/period/current')->controller('SaPayrollController@viewCurrentPayPeriod')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Unresolved Pay', 'sa_unresolved_pay', '/siteadmin/payroll/unresolved')->controller('SaPayrollController@viewUnresolvedPay')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Reports', 'sa_reports', '/siteadmin/payroll/reports')->controller('SaPayrollController@viewReports')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Reports', 'sa_admin_reports', '/siteadmin/adminreports/reports')->controller('SaPayrollController@viewAdminReports')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Add Invoice', 'sa_add_invoice', '/siteadmin/invoices/add')->controller('SaInvoiceController@addInvoice')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Generate Invoice', 'sa_generate_invoice', '/siteadmin/invoices/generate')->controller('SaInvoiceController@viewGenerateInvoice')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Generate Invoice', 'sa_generate_invoice_send', '/siteadmin/invoices/generate/{provider_id}/{pay_period}')->controller('SaInvoiceController@viewGenerateInvoice')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('View Invoice', 'sa_view_invoice', '/siteadmin/invoices/view')->controller('SaInvoiceController@viewInvoice')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Edit Invoice', 'sa_edit_invoice', '/siteadmin/invoices/{id}/edit')->controller('SaInvoiceController@editInvoice')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('View Invoices', 'sa_view_invoices', '/siteadmin/invoices')->controller('SaInvoiceController@viewInvoices')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('Generate 1099', 'sa_gen_1099', '/siteadmin/gen_1099')->controller('SaInvoiceController@generate1099');
        $routes->addWithOptionsAndName('Download 1099', 'sa_download_1099', '/siteadmin/download_1099')->controller('SaPayrollController@export1099s');
        $routes->addWithOptionsAndName('Download 1099 CSV', 'sa_download_1099_csv', '/siteadmin/download_1099_csv')->controller('SaPayrollController@export1099csv');
        
        // PAY CARD FILE DOWNLOAD 
        $routes->addWithOptionsAndName('Download Paycard Upload Xlsx', 'sa_download_paycard_upload_xlsx', '/siteadmin/download_paycard_upload_xlsx')->controller('SaPayrollController@getPaycardFileXlsx');
        $routes->addWithOptionsAndName('Download Paycard Upload Csv', 'sa_download_paycard_upload_csv', '/siteadmin/download_paycard_upload_csv')->controller('SaPayrollController@getPaycardFileCsv');

				// CHECKR PAY FILE DOWNLOAD
				$routes->addWithOptionsAndName('Download Checkr Pay Upload Csv', 'sa_download_checkr_pay_upload_csv', '/siteadmin/download_checkr_pay_upload_csv')->controller('SaPayrollController@getCheckrPayFileCsv');

        // RESOURCES
        $routes->addWithOptionsAndName('css', 'payroll_css', '/siteadmin/payroll/css/{file}')->controller('PayrollController@css');// 'permissions' => 'developer'
        $routes->addWithOptionsAndName('js', 'payroll_js', '/siteadmin/payroll/js/{file}')->controller('PayrollController@js');// 'permissions' => 'developer'
        
        
        // Government reporting
        $routes->addWithOptionsAndName('Download Government report zip', 'sa_payroll_download_governement_report_zip', '/siteadmin/payroll/download/governement/report/zip/{year}/{quarter}')->controller('GovernmentReportingController@downloadReportZip');// 'permissions' => 'developer', '/siteadmin/payroll/js/{file}')->controller('GovernmentReportingController@downloadReportZip');// 'permissions' => 'developer
    }

    /**
     * @return array
     */
    public static function getPermissions()
    {
        $permissions = array();
        $permissions['payroll_list_payroll'] = 'List Payroll';
        $permissions['payroll_add_payroll'] = 'Add Payroll';
        $permissions['payroll_edit_payroll'] = 'Edit Payroll';
        $permissions['payroll_delete_payroll'] = 'Delete Payroll';

        $permissions['payroll_list_payroll_item'] = 'List Payroll Item';
        $permissions['payroll_add_payroll_item'] = 'Add Payroll Item';
        $permissions['payroll_edit_payroll_item'] = 'Edit Payroll Item';
        $permissions['payroll_delete_payroll_item'] = 'Delete Payroll Item';

        return $permissions;
    }

    static function getNavigation()
    {

        return array(
            // SITEADMIN
            new navItem(array('id' => 'payroll', 'icon' => 'fa fa-dollar-sign', 'name' => 'Payroll', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'sa_payment_history', 'routeid' => 'sa_payment_history', 'icon' => 'fa fa-dollar-sign', 'name' => 'Payment History', 'parent' => 'payroll', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'sa_current_pay_period', 'routeid' => 'sa_current_pay_period', 'icon' => 'fa fa-dollar-sign', 'name' => 'Current Pay Period', 'parent' => 'payroll', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'sa_unresolved_pay', 'routeid' => 'sa_unresolved_pay','icon' => 'fa fa-dollar-sign', 'name' => 'Unresolved Pay', 'parent' => 'payroll', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'sa_reports', 'routeid' => 'sa_reports','icon' => 'fa fa-dollar-sign', 'name' => 'Reports', 'parent' => 'payroll', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'generate_1099', 'routeid' => 'sa_gen_1099','icon' => 'fa fa-dollar-sign', 'name' => '1099 Generation', 'parent' => 'quickbooks', 'priority' => navItem::PRIORITY_LOW)),

            new navItem(array('id' => 'adminreports', 'icon' => 'fa fa-dollar-sign', 'name' => 'Admin Reports', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'sa_admin_reports', 'routeid' => 'sa_admin_reports', 'icon' => 'fa fa-dollar-sign', 'name' => 'Admin Reports', 'parent' => 'adminreports', 'priority' => navItem::PRIORITY_HIGH)),
        );
    }

}