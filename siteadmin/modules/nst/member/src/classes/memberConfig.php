<?php


namespace nst\member;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\MiddlewareManager;
use sacore\application\modRequest;
use sacore\application\navItem;

class memberConfig extends \sa\member\memberConfig
{

    static function init()
    {
        MiddlewareManager::register('ProviderAdminMiddleware', 'ProviderAdminMiddleware', MiddlewareManager::MIDDLEWARE_PRIORITY_HIGH);
        MiddlewareManager::register('ApplicationMiddleware', 'ApplicationMiddleware', MiddlewareManager::MIDDLEWARE_PRIORITY_HIGH);

//        modRequest::listen('inventory.kit.category.unlink', 'kitController@unlinkCategoryFromKit', 1, null, true, false);
//        modRequest::listen('inventory.generate.barcode', 'kitController@generateBarCode', 1, null, true, false);
        modRequest::listen('member.profile_sidebar_links', 'NstMemberProfileModRequestListeners@getSidebarLinks',1);
        modRequest::listen('member.get_users_list', 'NstMemberProfileController@getUsersList',1, null, true, false);
        modRequest::listen('member.save_user_data', 'NstMemberProfileController@saveUserData',1, null, true, false);
        modRequest::listen('member.delete_user', 'NstMemberProfileController@deleteUser',1, null, true, false);
        modRequest::listen('sa.member.delete_nst_member_mod', 'SaNstMemberController@deleteNstMemberMod', 1, null, true, false);
        modRequest::listen('member.get_member_type', 'NstMemberController@getMemberType',1, null, true, false);

        modRequest::listen('provider.do_not_return', 'ProviderController@loadDoNotReturnList', 1, null, true, false);
        modRequest::listen('provider.block_nurse', 'ProviderController@blockNurse', 1, null, true, false);
        modRequest::listen('provider.unblock_nurse', 'ProviderController@unblockNurse', 1, null, true, false);
        modRequest::listen('provider.load_assignable_nurses', 'ProviderController@loadAssignableNurses', 1, null, true, false);
        modRequest::listen('provider.load_shift_requests', 'ProviderController@loadShiftRequests', 1, null, true, false);
        modRequest::listen('provider.approve_shift_request', 'ProviderController@approveShiftRequest', 1, null, true, false);
        modRequest::listen('provider.deny_shift_request', 'ProviderController@denyShiftRequest', 1, null, true, false);
        modRequest::listen('provider.load_dashboard_data', 'ProviderController@loadDashboardData', 1, null, true, false);
        modRequest::listen('provider.load_profile_data', 'ProviderController@loadProfileData', 1, null, true, false);
        modRequest::listen('provider.load_upcoming_provider_shifts', 'ProviderController@loadUpcomingProviderShifts', 1, null, true, false);
        modRequest::listen('provider.save_provider_info', 'ProviderController@saveProviderInfo', 1, null, true, false);
        modRequest::listen('provider.get_pbj_report', 'ProviderController@pbjReport', 1, null, true, false);
        modRequest::listen('provider.load_nurse_files', 'ProviderController@loadNurseFiles', 1, null, true, false);
        modRequest::listen('provider.get.nurse_credentials', 'ProviderController@getNurseCredentials', 1, null, true, false);
        modRequest::listen('provider.get_nurse_files', 'ProviderController@providerGetNurseFiles', 1, null, true, false);
        modRequest::listen('provider.get.preset_shift_times', 'ProviderController@getPresetShiftTimes', 1, null, true, false);
        modRequest::listen('provider.get.pay_rates', 'ProviderController@getPayRates', 1, null, true, false);
        modRequest::listen('provider.get.available_nurses', 'ProviderController@getAvailableNurses', 1, null, true, false);
        modRequest::listen('provider.get.create_shift_data', 'ProviderController@getCreateShiftData', 1, null, true, false);
        modRequest::listen('provider.save.new_shift', 'ProviderController@saveNewShift', 1, null, true, false);
        modRequest::listen('provider.cancel_approved_shift', 'ProviderController@cancelShift', 1, null, true, false);
        modRequest::listen('provider.get_pbj_report', 'ProviderController@pbjReport', 1, null, true, false);



        modRequest::listen('nurses.list', 'NurseController@getNursesForNurseList', 1, null, true, false);
        modRequest::listen('nurse.profile', 'NurseController@loadNurseProfileData', 1, null, true, false);
        modRequest::listen('nurse.profile.upcoming_shifts', 'NurseController@loadUpcomingNurseShifts', 1, null, true, false);
        modRequest::listen('nurse.profile.past_shifts', 'NurseController@loadNursePastShifts', 1, null, true, false);
        modRequest::listen('nurse.request_shift', 'NurseController@requestShift', 1, null, true, false);
        modRequest::listen('nurse.search', 'NurseController@search', 1, null, true, false);
        modRequest::listen('nurse.get.metadata', 'NurseController@getMetaData', 1, null, true, false);
        modRequest::listen('nurse.merge.shifts', 'NurseController@mergeShifts', 1, null, true, false);
        modRequest::listen('nurse.merge.data', 'NurseController@mergeNurseData', 1, null, true, false);
        modRequest::listen('nurse.deactivate', 'NurseController@nurseDeactivate', 1, null, true, false);

        // Nurse Backend
        modRequest::listen('sa.member.load_nurses', 'SaNstMemberController@loadNurses', 1, null, true, false);
        modRequest::listen('sa.member.load_nurse_files', 'SaNstMemberController@loadNurseFiles', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_files', 'SaNstMemberController@saveNurseFiles', 1, null, true, false);
        modRequest::listen('sa.member.load_nurse_basic_info', 'SaNstMemberController@loadNurseBasicInfo', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_basic_info', 'SaNstMemberController@saveNurseBasicInfo', 1, null, true, false);

        modRequest::listen('sa.member.load_nurse_checkr_pay_info', 'SaNstMemberController@loadNurseCheckrPayInfo', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_checkr_pay_info', 'SaNstMemberController@saveNurseCheckrPayInfo', 1, null, true, false);
				modRequest::listen('sa.member.create_checkr_pay_worker', 'SaNstMemberController@createCheckrPayWorker', 1, null, true, false);
				//modRequest::listen('sa.member.list_checkr_pay_workers', 'SaNstMemberController@listCheckrPayWorkers', 1, null, true, false);


        modRequest::listen('sa.member.load_nurse_direct_deposit_info', 'SaNstMemberController@loadNurseDirectDepositInfo', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_direct_deposit_info', 'SaNstMemberController@saveNurseDirectDepositInfo', 1, null, true, false);
        modRequest::listen('sa.member.load_nurse_pay_card_info', 'SaNstMemberController@loadNursePayCardInfo', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_pay_card_info', 'SaNstMemberController@saveNursePayCardInfo', 1, null, true, false);
        modRequest::listen('sa.member.load_nurse_emergency_contacts', 'SaNstMemberController@loadNurseEmergencyContacts', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_emergency_contacts', 'SaNstMemberController@saveNurseEmergencyContacts', 1, null, true, false);
        modRequest::listen('sa.member.load_nurse_contact_info', 'SaNstMemberController@loadNurseContactInfo', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_contact_info', 'SaNstMemberController@saveNurseContactInfo', 1, null, true, false);
        modRequest::listen('sa.member.load_nurse_notes', 'SaNstMemberController@loadNurseNotes', 1, null, true, false);
        modRequest::listen('sa.member.save_nurse_notes', 'SaNstMemberController@saveNurseNotes', 1, null, true, false);
        modRequest::listen('sa.member.admin_for_note', 'SaNstMemberController@getAdminNameForNurseNote', 1, null, true, false);

        // Nurse states tab
        modRequest::listen('sa.member.get.nurse.states', 'SaNstMemberController@getNurseStates', 1, null, true, false);
        modRequest::listen('sa.member.save.nurse.states', 'SaNstMemberController@saveNurseStates', 1, null, true, false);

        // executive backend
        modRequest::listen('sa.member.load_executives', 'SaNstMemberController@loadExecutives', 1, null, true, false);
        modRequest::listen('sa.member.load_executive_basic_info', 'SaNstMemberController@loadExecutiveBasicInfo', 1, null, true, false);
        modRequest::listen('sa.member.save_executive_basic_info', 'SaNstMemberController@saveExecutiveBasicInfo', 1, null, true, false);
        modRequest::listen('sa.member.load_executive_facilities', 'SaNstMemberController@loadExecutiveFacilities', 1, null, true, false);
        modRequest::listen('sa.member.load_facilities', 'SaNstMemberController@loadFacilities', 1, null, true, false);
        modRequest::listen('sa.member.remove_executive_facility', 'SaNstMemberController@removeExecutiveFacility', 1, null, true, false);
        modRequest::listen('sa.member.add_executive_facility', 'SaNstMemberController@addExecutiveFacility', 1, null, true, false);

        // Provider Backend
        modRequest::listen('sa.member.load_providers', 'SaNstMemberController@loadProviders', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_files', 'SaNstMemberController@loadProviderFiles', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_filetags', 'SaNstMemberController@loadProviderFileTags', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_files', 'SaNstMemberController@saveProviderFiles', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_filetags', 'SaNstMemberController@saveProviderFileTags', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_basic_info', 'SaNstMemberController@loadProviderBasicInfo', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_basic_info', 'SaNstMemberController@saveProviderBasicInfo', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_contacts', 'SaNstMemberController@loadProviderContacts', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_contact', 'SaNstMemberController@saveProviderContact', 1, null, true, false);
        modRequest::listen('sa.member.delete_provider_contact', 'SaNstMemberController@deleteProviderContact', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_pay_rates', 'SaNstMemberController@loadProviderPayRates', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_pay_rates', 'SaNstMemberController@saveProviderPayRates', 1, null, true, false);
        modRequest::listen('sa.member.check_delete_tag', 'SaNstMemberController@checkIfTagCanBeDeleted', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_nurse_credentials', 'SaNstMemberController@getProviderNurseCredentialsList', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_nurse_credentials', 'SaNstMemberController@saveProviderNurseCredentialsList', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_shift_categories', 'SaNstMemberController@getProviderShiftCategories', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_preset_shift_time', 'SaNstMemberController@saveProviderPresetShiftTime', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_preset_shift_times', 'SaNstMemberController@getProviderPresetShiftTimes', 1, null, true, false);
        modRequest::listen('sa.member.delete_provider_preset_shift_time', 'SaNstMemberController@deleteProviderPresetShiftTime', 1, null, true, false);
        modRequest::listen('sa.member.load_provider_shift_break_duration', 'SaNstMemberController@getProviderShiftBreakDuration', 1, null, true, false);
        modRequest::listen('sa.member.save_provider_shift_break_duration', 'SaNstMemberController@saveProviderShiftBreakDuration', 1, null, true, false);
    }


    static function postInit()
    {
//        modRequest::request('sa.dashboard.add_widget', null, array('id'=>'ncp.inventory.quarantined_kits', 'name'=>'Quarantined Kits', 'action'=>'kitController@getQuarantinedKitsWidget'));
    }


    public static function initRoutes($routes)
    {
        $routes->addWithOptionsAndName('Attempt Login', 'member_login_post', '/member/login')->controller('NstMemberController@attemptLogin')->methods(['POST']); //'protected' => false
        $routes->addWithOptionsAndName('Register', 'member_register', '/member/register')->controller('NstMemberController@register'); //'protected' =>  false
        $routes->addWithOptionsAndName('Attempt Register', 'member_register_post', '/member/register')->controller('NstMemberController@attemptRegister')->methods(['POST']); //'protected' => false

        // $routes->addWithOptionsAndName('Manage Profiles', 'manage_mamber_editz', '/member/profile')->controller('NstMemberController@editmember');
        $routes->addWithOptionsAndName('Edit Profile', 'member_profile', '/member/profile')->controller('NstMemberProfileController@editmember')->middleware('AuthMiddleware'); //'protected_hard' => true
        $routes->addWithOptionsAndName('Dashboard', 'dashboard_default', '/')->controller('NstMemberController@defaultDashboard')->middleware('AuthMiddleware'); //'protected_hard' => true

        $routes->addWithOptionsAndName('SA Manage Nurses', 'manage_nurses', '/siteadmin/nurse')->controller('SaNstMemberController@manageNurses')->defaults(['route_permissions' => ['member_list_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Create Nurse', 'create_nurse', '/siteadmin/nurse/create')->controller('SaNstMemberController@editNurse')->defaults(['route_permissions' => ['member_add_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Merge Nurse', 'merge_nurse', '/siteadmin/nurse/merge')->controller('SaNstMemberController@mergeNurseIndex')->defaults(['route_permissions' => ['member_merge_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Edit Nurse', 'edit_nurse', '/siteadmin/nurse/{id}/edit')->controller('SaNstMemberController@editNurse')->defaults(['route_permissions' => ['member_edit_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Edit Nurse', 'edit_nurse_alt', '/siteadmin/nurse/{nurse_id}/edit')->controller('SaNstMemberController@editNurse')->defaults(['route_permissions' => ['member_edit_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Save Nurse', 'save_nurse', '/siteadmin/nurse/{id}/save')->controller('SaNstMemberController@saveMember')->defaults(['route_permissions' => ['member_edit_nurse']])->middleware('SaPermissionMiddleware')->methods(['POST']);
        $routes->addWithOptionsAndName('SA Delete Nurse', 'delete_nurse', '/siteadmin/nurse/{id}/delete')->controller('SaNstMemberController@deleteNurse')->defaults(['route_permissions' => ['member_delete_nurse']])->middleware('SaPermissionMiddleware');

        $routes->addWithOptionsAndName('SA Manage Jobs', 'manage_jobs', '/siteadmin/job')->controller('JobController@manageJobs')->defaults(['route_permissions' => ['member_list_job']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Create Jobs', 'create_job', '/siteadmin/job/create')->controller('JobController@editJob')->defaults(['route_permissions' => ['member_add_job']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Edit Job', 'edit_job', '/siteadmin/job/{id}/edit')->controller('JobController@editJob')->defaults(['route_permissions' => ['member_edit_job']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Save Job', 'save_job', '/siteadmin/job/{id}/save')->controller('JobController@saveJob')->defaults(['route_permissions' => ['member_edit_job']])->middleware('SaPermissionMiddleware')->methods(['POST']);
        $routes->addWithOptionsAndName('SA Delete Job', 'delete_job', '/siteadmin/job/{id}/delete')->controller('JobController@deleteJob')->defaults(['route_permissions' => ['member_delete_job']])->middleware('SaPermissionMiddleware');

        $routes->addWithOptionsAndName('SA Manage Providers', 'manage_providers', '/siteadmin/provider')->controller('SaNstMemberController@manageProviders')->defaults(['route_permissions' => ['member_list_provider']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Create Provider', 'create_provider', '/siteadmin/provider/create')->controller('SaNstMemberController@editProvider')->defaults(['route_permissions' => ['member_add_provider']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Edit Provider', 'edit_provider', '/siteadmin/provider/{id}/edit')->controller('SaNstMemberController@editProvider')->defaults(['route_permissions' => ['member_edit_provider']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Save Provider', 'save_provider', '/siteadmin/provider/{id}/save')->controller('SaNstMemberController@saveMember')->defaults(['route_permissions' => ['member_edit_provider']])->middleware('SaPermissionMiddleware')->methods(['POST']);
        $routes->addWithOptionsAndName('SA Delete Provider', 'delete_provider', '/siteadmin/provider/{id}/delete')->controller('SaNstMemberController@deleteProvider')->defaults(['route_permissions' => ['member_delete_provider']])->middleware('SaPermissionMiddleware');

        $routes->addWithOptionsAndName('SA Manage Executives', 'manage_executives', '/siteadmin/executive')->controller('SaNstMemberController@manageExecutives')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Create Executive', 'create_executive', '/siteadmin/executive/create')->controller('SaNstMemberController@editExecutive')->middleware('SaPermissionMiddleware');
        // $routes->addWithOptionsAndName('SA Edit Provider', 'edit_provider', '/siteadmin/executive/{id}/edit')->controller('SaNstMemberController@editProvider')->defaults(['route_permissions' => ['member_edit_provider']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('SA Save Executive', 'save_executive', '/siteadmin/executive/{id}/save')->controller('SaNstMemberController@saveMember')->middleware('SaPermissionMiddleware')->methods(['POST']);
        // $routes->addWithOptionsAndName('SA Delete Provider', 'delete_provider', '/siteadmin/executive/{id}/delete')->controller('SaNstMemberController@deleteProvider')->defaults(['route_permissions' => ['member_delete_provider']])->middleware('SaPermissionMiddleware');

        $routes->addWithOptionsAndName('Add Username', 'member_sa_createusers', '/siteadmin/members/{member_id}/edit/username/create')->controller('SaNstMemberController@SaNstEditMemberUsers');
        $routes->addWithOptionsAndName('Edit Username', 'member_sa_editusernames', '/siteadmin/members/{member_id}/edit/username/{id}/edit$')->controller('SaNstMemberController@SaNstEditMemberUsers');

        $routes->addWithOptionsAndName('Edit Tags', 'edit_tags', '/siteadmin/edit_tags')->controller('SaNstMemberController@editTags');

        $routes->addWithOptionsAndName('css', 'inventory_css', '/siteadmin/member/css/{file}')->controller('memberController@css');// 'permissions' => 'developer'
        $routes->addWithOptionsAndName('js', 'inventory_js', '/siteadmin/member/js/{file}')->controller('memberController@js');// 'permissions' => 'developer'

        $routes->addWithOptionsAndName('Nurses List', 'nurse_list', '/nurses/list')->controller('NurseController@listNurses')->defaults(['route_permissions' => ['member_nurse_list']])->middleware('SaPermissionMiddleware')->middleware('AuthMiddleware')->methods(['GET']);
        $routes->addWithOptionsAndName('Nurse Profile', 'nurse_profile', '/nurses/profile/{id}')->controller('NurseController@nurseProfile')->defaults(['route_permissions' => ['member_nurse_profile']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Do Not Return List', 'do_not_return', '/nurses/do_not_return')->controller('ProviderController@listDoNotReturn')->defaults(['route_permissions' => ['member_do_not_return']])->middleware('SaPermissionMiddleware');


				// Webhooks
				$routes->addWithOptionsAndName('Checkr Pay Webhook', 'checkr_pay_webhook', '/webhooks/checkrpay')->controller('CheckrPayService@webhook')->methods(['POST']);
    }

    /**
     * @return array
     */
    public static function getPermissions()
    {
        $permissions = array();
        $permissions['member_list_nurse'] = 'List Nurse';
        $permissions['member_add_nurse'] = 'Add Nurse';
        $permissions['member_edit_nurse'] = 'Edit Nurse';
        $permissions['member_delete_nurse'] = 'Delete Nurse';

        $permissions['member_list_job'] = 'List Job';
        $permissions['member_add_job'] = 'Add Job';
        $permissions['member_edit_job'] = 'Edit Job';
        $permissions['member_delete_job'] = 'Delete Job';

        $permissions['member_list_provider'] = 'List Provider';
        $permissions['member_add_provider'] = 'Add Provider';
        $permissions['member_edit_provider'] = 'Edit Provider';
        $permissions['member_delete_provider'] = 'Delete Provider';

        $permission['member_nurse_list'] = 'Nurses List';
        $permission['member_do_not_return'] = 'Do Not Return List';
        $permission['member_nurse_profile'] = 'Nurse Profile';

        return $permissions;
    }

    static function getNavigation()
    {

        return array(

            // SITEADMIN
            new navItem(array('id' => 'nurse_applications', 'name' => 'Applications', 'routeid' => 'nurse_applications_index', 'icon' => 'fas fa-user', 'parent' => 'nurses')),
            new navItem(array('id' => 'nurses', 'routeid' => 'manage_nurses', 'icon' => 'fa fa-ambulance', 'name' => 'Nurses', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'manageNurses', 'name' => 'Manage Nurses', 'routeid' => 'manage_nurses', 'icon' => 'fas fa-user', 'parent' => 'nurses')),
            new navItem(array('id' => 'createNurse', 'name' => 'Create Nurses', 'routeid' => 'create_nurse', 'icon' => 'fas fa-user-plus', 'parent' => 'nurses')),
            new navItem(array('id' => 'MergeNurse', 'name' => 'Merge Nurses', 'routeid' => 'merge_nurse', 'icon' => 'fas fa-clone', 'parent' => 'nurses')),

            new navItem(array('id' => 'providers', 'routeid' => 'manage_providers', 'icon' => 'fa fa-building', 'name' => 'Providers', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'manageProviders', 'name' => 'Manage Providers', 'routeid' => 'manage_providers', 'icon' => 'fas fa-user', 'parent' => 'providers')),
            new navItem(array('id' => 'createProvider', 'name' => 'Create Provider', 'routeid' => 'create_provider', 'icon' => 'fas fa-user-plus', 'parent' => 'providers')),

            new navItem(['id' => 'executives', 'name' => 'Executive Accounts', 'routeid' => 'executive_accounts', 'icon' => 'fa fa-users', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH]),
            new navItem(array('id' => 'manageProviders', 'name' => 'Manage Executives', 'routeid' => 'manage_executives', 'icon' => 'fas fa-users', 'parent' => 'executives')),
            new navItem(array('id' => 'createProvider', 'name' => 'Create Executive', 'routeid' => 'create_executive', 'icon' => 'fas fa-user-plus', 'parent' => 'executives')),


            new navItem(array('id' => 'tags', 'routeid' => 'manage_tags', 'icon' => 'fa fa-tags', 'name' => 'Tags', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'edit_tags', 'name' => 'Edit Tags', 'routeid' => 'edit_tags', 'icon' => 'fas fa-tags', 'parent' => 'tags')),
            // Commented as it looks like this is a dysfunctional route for the PBJ Report, originally still linking to create_provider
            // Line originally added by William O'Rourke on commit same working pbj report functionality added to branch(...), May 8 2023
            // new navItem(array('id' => 'shifts_report', 'name' => 'Shifts Report', 'routeid' => 'pbj_report', 'icon' => 'fas fa-user-plus', 'parent' => 'providers')),
        );
    }

    public static function getCLICommands()
    {
        return array(
            ioc::staticGet('NursePayPeriodsCommand'),
            ioc::staticGet('NurseRequestShiftCommand'),
            ioc::staticGet('NurseShiftTimeClockCommand'),
            ioc::staticGet('ImportProvidersCommand'),
            ioc::staticGet('ImportNursesCommand'),
            ioc::staticGet('UpdateProvidersCommand'),
            ioc::staticGet('CheckUpcomingNurseShiftsCommand'),
            ioc::staticGet('NurseTBAndLicenseDateImport'),
            ioc::staticGet('NurseDocumentExpirationNotificationCommand'),
            ioc::staticGet('ProviderSetupDefaultNurseCredentialsCommand'),
            ioc::staticGet('NurseImportPaycardAccountNumbersCommand'),
            ioc::staticGet('ApproveShiftCommand'),
            ioc::staticGet('DenyShiftCommand'),
            ioc::staticGet('ProviderCreateShiftCommand'),
						ioc::staticGet('GenerateCheckrPayIDsCommand'),
						ioc::staticGet('ProcessCheckrWebhooksCommand')
        );
    }
}
