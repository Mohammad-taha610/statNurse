<?php


namespace nst\events;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\navItem;

class eventsConfig extends \sa\events\eventsConfig
{

    static function init()
    {
        modRequest::listen('shifts.loadCalendar', 'ShiftController@loadCalendar', 1, null, true, true);

        // Providers
        modRequest::listen('shift.create_shift', 'ShiftController@save', 1, null, true, false);
        modRequest::listen('shift.save_shift', 'ShiftController@save', 1, null, true, false);
        modRequest::listen('shift.cancel_shift', 'ShiftController@cancelShift', 1, null, true, false);
        modRequest::listen('shift.save_recurrence', 'ShiftController@saveRecurrence', 1, null, true, false);
        modRequest::listen('shift.load.categories', 'ShiftController@loadCategories', 1, null, true, false);
        modRequest::listen('shift.load_shift_data', 'ShiftController@loadShiftData', 1, null, true, false);
        modRequest::listen('shift.load_recurrence_data', 'ShiftController@loadRecurrenceData', 1, null, true, false);
        modRequest::listen('shift.delete_shift', 'ShiftController@deleteShift', 1, null, true, false);
        modRequest::listen('shift.approve_shift_request', 'ShiftController@approveShiftRequest', 1, null, true, false);
        modRequest::listen('shift.deny_shift_request', 'ShiftController@denyShiftRequest', 1, null, true, false);
        modRequest::listen('shift.load_calendar_filters', 'ShiftController@loadCalendarFilters', 1, null, true, false);
        modRequest::listen('shift.mass_delete_shifts', 'ShiftController@massDeleteShifts', 1, null, true, false);

        // SA
        modRequest::listen('sa.shift.load_calendar', 'SaShiftController@loadShiftCalendarData', 1, null, true, false);
        modRequest::listen('sa.shift.save_shift', 'SaShiftController@saveShift', 1, null, true, false);
        modRequest::listen('sa.shift.load_shift_data', 'SaShiftController@loadShiftData', 1, null, true, false);
        modRequest::listen('sa.shift.load_recurrence_data', 'SaShiftController@loadRecurrenceData', 1, null, true, false);
        modRequest::listen('sa.shift.load_providers', 'SaShiftController@loadProviders', 1, null, true, false);
        modRequest::listen('sa.shift.load_nurses', 'SaShiftController@loadNurses', 1, null, true, false);
        modRequest::listen('sa.shift.load_calendar_filters', 'SaShiftController@loadCalendarFilters', 1, null, true, false);
        modRequest::listen('sa.shift.delete_shift', 'SaShiftController@deleteShift', 1, null, true, false);
        modRequest::listen('sa.shift.load_assignable_nurses', 'SaShiftController@loadAssignableNurses', 1, null, true, false);
        modRequest::listen('sa.shift.load.categories', 'SaShiftController@loadCategories', 1, null, true, false);
        modRequest::listen('sa.shift.load_shift_requests', 'SaShiftController@loadShiftRequests', 1, null, true, false);
        modRequest::listen('sa.shift.approve_shift_request', 'SaShiftController@approveShiftRequest', 1, null, true, false);
        modRequest::listen('sa.shift.deny_shift_request', 'SaShiftController@denyShiftRequest', 1, null, true, false);
        modRequest::listen('sa.shift.call_in_shift', 'SaShiftController@callInShift', 1, null, true, false);
        modRequest::listen('sa.shift.mass_delete_shifts', 'SaShiftController@massDeleteShifts', 1, null, true, false);
        modRequest::listen('sa.shift.action_log', 'SaShiftLogger@get', 1, null, true, false);

        // test methods
        modRequest::listen('test.shift.clock_in', 'ShiftTests@testClockIn', 1, null, true, false);
        modRequest::listen('test.shift.clock_out', 'ShiftTests@testClockOut', 1, null, true, false);
    }


    static function postInit()
    {
        modRequest::request('api.registerEntityAPI', null, array('entity' => 'Shift', 'controller' => 'ShiftApiV1Controller'));
//        modRequest::request('sa.dashboard.add_widget', null, array('id'=>'ncp.inventory.quarantined_kits', 'name'=>'Quarantined Kits', 'action'=>'kitController@getQuarantinedKitsWidget'));
    }


    public static function initRoutes($routes)
    {
        //Todo: Add permissions to these routes
        //Shift Entity CRUD
        $routes->addWithOptionsAndName('Provider View Shifts', 'events_index', '/shifts' )->controller('EventsController@index')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Provider Create Shift', 'create_shift', '/shift/create')->controller('ShiftController@providerCreateShift')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Provider Review Shifts', 'review_shifts', '/shift/review')->controller('ShiftController@providerReviewShift')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Provider Edit Shift', 'edit_shift', '/shift/{id}/edit')->controller('ShiftController@providerEditShift')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Provider Copy Shift', 'copy_shift', '/shift/{id}/copy')->controller('ShiftController@providerCopyShift')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Provider Save Shift', 'save_shift', '/shift/{id}/save')->controller('ShiftController@providerSaveShift')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Provider Shift Requests', 'shift_requests', '/shift/requests')->controller('ShiftController@providerShiftRequests')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Provider Pending Shifts', 'pending_shifts', '/shift/pending')->controller('ShiftController@providerPendingShifts')->middleware('AuthMiddleware');

        $routes->addWithOptionsAndName('Create Shift', 'sa_events_create', '/siteadmin/shifts/create')->controller('SaShiftController@createShift');
        $routes->addWithOptionsAndName('Edit Shift', 'sa_shift_edit', '/siteadmin/shifts/{id}/edit')->controller('SaShiftController@edit');
        $routes->addWithOptionsAndName('Copy Shift', 'sa_shift_copy', '/siteadmin/shifts/{id}/copy')->controller('SaShiftController@copyShift');
        $routes->addWithOptionsAndName('Save Shift', 'sa_shift_save', '/siteadmin/shifts/save')->controller('SaShiftController@saveShift')->methods(['POST']);
        $routes->addWithOptionsAndName('Shift Requests', 'sa_shift_requests', '/siteadmin/shifts/requests')->controller('SaShiftController@shiftRequests');

        $routes->addWithOptionsAndName('css', 'shifts_css', '/shift/css/{file}')->controller('ShiftController@css');
        $routes->addWithOptionsAndName('js', 'shifts_js', '/shift/js/{file}')->controller('ShiftController@js');

        $routes->addWithOptionsAndName('Shift Calendar', 'sa_shift_calendar', '/siteadmin/shifts/calendar')->controller('SaShiftController@viewShiftCalendar')->middleware('SaAuthMiddleware'); // 'permissions'=>'schedulers';
        $routes->addWithOptionsAndName('Shift Calendar', 'sa_provider_shift_calendar', '/siteadmin/shifts/calendar/provider/{member_id}')->controller('SaShiftController@viewProviderShiftCalendar')->middleware('SaAuthMiddleware');

        $routes->addWithOptionsAndName('Shifts Action Log', 'sa_shifts_log', '/siteadmin/shifts/actionlog')->controller('SaShiftController@shiftActionLog')->middleware('SaAuthMiddleware');

        $routes->addWithOptionsAndName('Shift Tests', 'sa_shifts_tests', '/siteadmin/shifts/tests')->controller('SaShiftController@shiftTests')->middleware('SaAuthMiddleware');
    }

    /**
     * @return array
     */
    public static function getPermissions()
    {
        $permissions = array();
//        $permissions['member_list_nurse'] = 'List Nurse';

        return $permissions;
    }

    static function getNavigation()
    {

        return array(
            // SITEADMIN
            new navItem(array('id' => 'shift_calendar', 'routeid' => 'sa_shift_calendar', 'icon' => 'fa fa-calendar', 'name' => 'Shift Calendar', 'parent' => 'saEvents', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'shift_requests', 'routeid' => 'sa_shift_requests', 'icon' => 'fa fa-list', 'name' => 'Shift Requests', 'parent' => 'saEvents', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'shifts_action_log', 'routeid' => 'sa_shifts_log', 'icon' => 'fa fa-calendar', 'name' => 'Action Log', 'parent' => 'saEvents', 'priority' => navItem::PRIORITY_HIGH)),
        );
    }

    public static function getSettings() {
        $module_settings = [
            'open_status_name' => ['type' => 'text', 'module' => 'events', 'default' => 'Open'],
            'pending_status_name' => ['type' => 'text', 'module' => 'events', 'default' => 'Pending'],
            'approved_status_name' => ['type' => 'text', 'module' => 'events', 'default' => 'Approved'],
            'nurse_clockin_window' => ['type' => 'text', 'module' => 'events', 'default' => 1],
            'enable_gps' => ['type' => 'boolean', 'module' => 'events', 'default' => false],
            'enforce_gps' => ['type' => 'boolean', 'module' => 'events', 'default' => false],
            'provider_time' => ['type' => 'boolean', 'module' => 'events', 'default' => false],
            'nurse_states_filter_enabled' => ['type' => 'boolean', 'module' => 'events', 'default' => false]
        ];

        return $module_settings;
    }

    public static function getCLICommands()
    {
        return array(
            ioc::staticGet('AutomaticClockOutCommand'),
            ioc::staticGet('OvertimeHoursFixCommand'),
            ioc::staticGet('MigrateRecurrencesToShiftsCommand'),
            ioc::staticGet('ShiftEndDateFixCommand'),
            ioc::staticGet('CreateShiftCommand'),
            ioc::staticGet('DeleteShiftCommand'),
        );

    }
}
