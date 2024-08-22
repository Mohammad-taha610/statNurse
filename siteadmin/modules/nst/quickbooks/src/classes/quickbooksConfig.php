<?php

namespace nst\quickbooks;


use sacore\application\modRequest;
use sacore\application\navItem;

class  quickbooksConfig extends \sacore\application\moduleConfig
{
    public static function init() {
        modRequest::listen('sa.quickbooks.test', 'SaQuickbooksController@runTest', 1, null, true, false);
        modRequest::listen('sa.quickbooks.get_auth_route', 'SaQuickbooksController@getAuthRoute', 1, null, true, false);
        modRequest::listen('sa.quickbooks.get_export_vendors_route', 'SaQuickbooksController@getExportVendorsRoute', 1, null, true, false);
        modRequest::listen('sa.quickbooks.export_vendors', 'SaQuickbooksController@exportVendors', 1, null, true, false);
        modRequest::listen('sa.quickbooks.get_export_customers_route', 'SaQuickbooksController@getExportCustomersRoute', 1, null, true, false);
        modRequest::listen('sa.quickbooks.export_customers', 'SaQuickbooksController@exportCustomers', 1, null, true, false);
        modRequest::listen('sa.quickbooks.get_payments_auth_route', 'SaQuickbooksController@getPaymentsAuthRoute', 1, null, true, false);
        modRequest::listen('sa.quickbooks.send_payments_to_quickbooks', 'SaQuickbooksController@sendPaymentsToQuickbooks', 1, null, true, false);
    }

    public static function initRoutes($routes) {
        $routes->addWithOptionsAndName('SA Quickbooks Test', 'sa_quickbooks_test', '/siteadmin/quickbooks/test')->controller('SaQuickbooksController@viewTest')->middleware('SaAuthMiddleware');
        $routes->addWithOptionsAndName('SA Export Vendors', 'export_vendors', '/siteadmin/quickbooks/export_vendors')->controller('SaQuickbooksController@viewExportVendors');
        $routes->addWithOptionsAndName('SA Export Vendors Confirmation', 'export_vendors_confirmation', '/siteadmin/quickbooks/export_vendors_confirmation')->controller('SaQuickbooksController@viewExportVendorsConfirmation');
        $routes->addWithOptionsAndName('SA Export Customers', 'export_customers', '/siteadmin/quickbooks/export_customers')->controller('SaQuickbooksController@viewExportCustomers');
        $routes->addWithOptionsAndName('SA Export Customers Confirmation', 'export_customers_confirmation', '/siteadmin/quickbooks/export_customers_confirmation')->controller('SaQuickbooksController@viewExportCustomersConfirmation');
        $routes->addWithOptionsAndName('SA Send Payments Confirmation', 'send_payments_confirmation', '/siteadmin/quickbooks/send_payments_confirmation')->controller('SaQuickbooksController@viewSendPaymentsConfirmation');

        $routes->addWithOptionsAndName('css', 'quickbooks_css', '/siteadmin/quickbooks/css/{file}')->controller('QuickbooksController@css');// 'permissions' => 'developer'
        $routes->addWithOptionsAndName('js', 'quickbooks_js', '/siteadmin/quickbooks/js/{file}')->controller('QuickbooksController@js');// 'permissions' => 'developer'
    }

    public static function getNavigation()
    {
        return [
            new navItem(array('id' => 'quickbooks', 'icon' => 'fa fa-envelope', 'name' => 'Quickbooks', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH)),
//            new navItem(array('id' => 'sa_add_invoice', 'routeid' => 'sa_add_invoice', 'icon' => 'fa fa-file', 'name' => 'Add Invoice', 'parent' => 'invoices', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'sa_generate_invoice', 'routeid' => 'sa_generate_invoice', 'icon' => 'fa fa-bolt', 'name' => 'Generate Invoice', 'parent' => 'quickbooks', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'sa_view_invoices', 'routeid' => 'sa_view_invoices', 'icon' => 'fa fa-bars', 'name' => 'View Invoices', 'parent' => 'quickbooks', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'exportVendors', 'routeid' => 'export_vendors', 'icon' => 'fas fa-users', 'name' => 'Export Vendors', 'parent' => 'quickbooks')),
            new navItem(array('id' => 'exportCustomers', 'routeid' => 'export_customers', 'icon' => 'fas fa-users', 'name' => 'Export Customers', 'parent' => 'quickbooks')),
//            new navItem(['id' => 'sa_quickbooks_test', 'routeid' => 'sa_quickbooks_test', 'icon' => 'fa fa-list', 'name' => 'Quickbooks Test', 'parent' => 'invoices', 'priority' => navItem::PRIORITY_HIGH])
        ];
    }

    public static function getSettings() {
        return [
            'quickbooks_export_group' => ['type' => 'integer', 'module' => 'Quickbooks', 'default' => 0],
            'quickbooks_invoice_number' => ['type' => 'integer', 'module' => 'Quickbooks', 'default' => 1500],
            'quickbooks_site_url' => ['type' => 'text', 'module' => 'Quickbooks'],
            'quickbooks_base_url' => ['type' => 'text', 'module' => 'Quickbooks', 'default' => 'Production'],
            'quickbooks_client_id' => ['type' => 'text', 'module' => 'Quickbooks'],
            'quickbooks_client_secret' => ['type' => 'text', 'module' => 'Quickbooks'],
            'quickbooks_authorization_code' => ['type' => 'text', 'module' => 'Quickbooks'],
            'quickbooks_realm_id' => ['type' => 'text', 'module' => 'Quickbooks'],
        ];
    }
}