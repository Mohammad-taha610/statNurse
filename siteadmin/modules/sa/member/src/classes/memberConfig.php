<?php
namespace sa\member;

use sacore\application\app;
use sacore\application\modDataRequest;
use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\navItem;
use sacore\application\resourceRoute;
use sacore\application\route;
use sacore\application\saRoute;
use sacore\application\Event;
use sacore\application\staticResourceRoute;
use sa\system\RoutePermission;
use sacore\application\ioc;
use sacore\application\MiddlewareManager;

abstract class memberConfig extends moduleConfig
{
//    public static function getRoutes()
//    {
//
//        return array(
//
//
//
//        );
//    }

    public static function initRoutes($routes)
    {

        /* -------------- API ROUTES ------------------- */

        $routes->addWithOptionsAndName('API Logon', 'api_member_login', '/api/member/login')->controller('memberAPIController@login'); //'protected' =>  false
        $routes->addWithOptionsAndName('API Logoff', 'api_member_logoff', '/api/member/logoff')->controller('memberAPIController@logoff')->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('API Is Logged In', 'api_member_isloggedin', '/api/member/is_logged_in')->controller('memberAPIController@isLoggedIn'); //'protected' =>  false
        $routes->addWithOptionsAndName('API Get My Profile', 'api_get_myprofile', '/api/member/profile')->controller('memberAPIController@getMyProfile')->middleware('AuthMiddleware'); //

        $routes->addWithOptionsAndName('API Location Push Service Update ', 'api_member_push_token_update', '/api/member/pushtoken/update')->controller('memberAPIController@pushTokenUpdate')->middleware('AuthMiddleware'); //


        /* -------------- FRONTEND ROUTES ------------------- */
        $routes->addWithOptionsAndName('User Not Allowed', 'user_not_allowed', '/member/una')->controller('memberController@userNotAllowed'); //'protected' =>  false


        $routes->addWithOptionsAndName('Dashboard', 'dashboard_home', '/dashboard')->controller('memberController@dashboard')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Login', 'member_login', '/member/login')->controller('memberController@login'); //'protected' =>  false
        $routes->addWithOptionsAndName('Attempt Login', 'member_login_post', '/member/login')->controller('memberController@attemptLogin')->methods(['POST']); //'protected' => false
        $routes->addWithOptionsAndName('Login JS Redirect', 'member_loginjsredirect', '/member/login_redirect')->controller('memberController@login_redirect'); //'protected' =>  false
        $routes->addWithOptionsAndName('Logoff', 'member_logoff', '/member/logoff')->controller('memberController@logoff')->middleware('AuthMiddleware');
        $routes->addWithOptionsAndName('Sign Up', 'member_signup', '/member/signup')->controller('memberController@signup'); //'protected' =>  false
        $routes->addWithOptionsAndName('Member Confirmation', 'member_signup_confirmation', '/member/signup/confirmation')->controller('memberController@signupConfirmation'); //'protected' =>  false
        $routes->addWithOptionsAndName('Sign Up', 'member_signup_post', '/member/signup')->controller('memberController@signupsave')->methods(['POST']); //'protected' =>  false
        $routes->addWithOptionsAndName('Password reset', 'member_reset', '/member/resetpassword')->controller('memberController@resetPassword'); //'protected' =>  false
        $routes->addWithOptionsAndName('Password reset', 'member_reset_post', '/member/resetpassword')->controller('memberController@attemptResetPassword')->methods(['POST']); //'protected' =>  false
        $routes->addWithOptionsAndName('Password reset', 'member_reset_change', '/member/resetpasswordconfirm')->controller('memberController@resetPasswordChange'); //'protected' =>  false
        $routes->addWithOptionsAndName('Password reset', 'member_reset_change_post', '/member/resetpasswordconfirm')->controller('memberController@resetPasswordChangeSave')->methods(['POST']); //'protected' =>  false

        $routes->addWithOptionsAndName('Edit Profile', 'member_profile', '/member/profile')->controller('memberProfileController@editMember')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Save Member', 'member_profile_save', '/members/profile/edit')->controller('memberProfileController@saveMember')->methods(['POST'])->middleware('AuthMiddleware'); //, 'protected_hard' => true

        $routes->addWithOptionsAndName('Edit Profile', 'member_profile_avatar_upload', '/member/profile/avatarupload')->controller('memberProfileController@saveMemberAvatar')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptions('member_profile_avatar', '/member/profile/avatar.jpg')->controller('memberProfileController@getMemberAvatar')->middleware('AuthMiddleware'); //
        $routes->addWithOptions('member_profile_mediumavatar', '/member/profile/mediumavatar.jpg')->controller('memberProfileController@getMemberMediumAvatar')->middleware('AuthMiddleware'); //
        $routes->addWithOptions('member_profile_smallavatar', '/member/profile/smallavatar.jpg')->controller('memberProfileController@getMemberSmallAvatar')->middleware('AuthMiddleware'); //
        $routes->addWithOptions('member_profile_miniavatar', '/member/profile/miniavatar.jpg')->controller('memberProfileController@getMemberMiniAvatar')->middleware('AuthMiddleware'); //

        $routes->addWithOptionsAndName('Upload Avatar', 'sa_member_avatar_upload', '/siteadmin/member/{id}/avatar-upload')->controller('saMemberController@uploadAvatar')->methods(['POST'])->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Get Avatar', 'sa_member_avatar', '/siteadmin/member/{id}/avatar')->controller('saMemberController@getAvatar')->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Delete Avatar', 'sa_member_avatar_remove', '/siteadmin/member/{id}/avatar/remove')->controller('saMemberController@removeAvatar')->methods(['POST'])->middleware('AuthMiddleware'); //

        // MEMBER USERNAMES
        $routes->addWithOptionsAndName('Users', 'member_users', '/member/profile/users')->controller('memberProfileController@viewUsers')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Add Username', 'member_createusers', '/member/profile/edit/username/create')->controller('memberProfileController@editMemberUsers')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Save Username', 'member_saveusernames', '/member/profile/edit/username/{id}/edit')->controller('memberProfileController@saveMemberUsers')->methods(['POST'])->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Edit Username', 'member_editusernames', '/member/profile/edit/username/{id}/edit')->controller('memberProfileController@editMemberUsers')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Delete Username', 'member_deleteusernames', '/member/profile/edit/username/{id}/delete')->controller('memberProfileController@deleteMemberUsers')->middleware('AuthMiddleware'); //, 'protected_hard' => true

        // MEMBER EMAILS
        $routes->addWithOptionsAndName('Email Addresses', 'member_email_addresses', '/member/profile/email-addresses')->controller('memberProfileController@viewEmailAddresses')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Add Email', 'member_createemail', '/member/profile/edit/email/create')->controller('memberProfileController@editMemberEmail')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Save Email','member_saveemail', '/member/profile/edit/email/{id}/edit')->controller('memberProfileController@saveMemberEmail')->methods(['POST'])->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Edit Email', 'member_editemail', '/member/profile/edit/email/{id}/edit')->controller('memberProfileController@editMemberEmail')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Delete Email', 'member_deleteemail', '/member/profile/edit/email/{id}/delete')->controller('memberProfileController@deleteMemberEmail')->middleware('AuthMiddleware'); //, 'protected_hard' => true

        // MEMBER ADDRESSES
        $routes->addWithOptionsAndName('Addresses', 'member_addresses', '/member/profile/addresses')->controller('memberProfileController@viewAddresses')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Add Address', 'member_createaddress', '/member/profile/edit/address/create')->controller('memberProfileController@editMemberAddress')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Save Address', 'member_saveaddress', '/member/profile/edit/address/{id}/edit')->controller('memberProfileController@saveMemberAddress')->methods(['POST'])->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Edit Address', 'member_editaddress', '/member/profile/edit/address/{id}/edit')->controller('memberProfileController@editMemberAddress')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Delete Address', 'member_deleteaddress', '/member/profile/edit/address/{id}/delete')->controller('memberProfileController@deleteMemberAddress')->middleware('AuthMiddleware'); //, 'protected_hard' => true

        // MEMBER PHONE'S
        $routes->addWithOptionsAndName('Phone Numbers', 'member_phone_numbers', '/member/profile/phone-numbers')->controller('memberProfileController@viewPhoneNumbers')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Add Phone', 'member_createphone', '/member/profile/edit/phone/create')->controller('memberProfileController@editMemberPhone')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Save Phone', 'member_savephone', '/member/profile/edit/phone/{id}/edit')->controller('memberProfileController@saveMemberPhone')->methods(['POST'])->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Edit Phone', 'member_editphone', '/member/profile/edit/phone/{id}/edit')->controller('memberProfileController@editMemberPhone')->middleware('AuthMiddleware'); //, 'protected_hard' => true
        $routes->addWithOptionsAndName('Delete Phone', 'member_deletephone', '/member/profile/edit/phone/{id}/delete')->controller('memberProfileController@deleteMemberPhone')->middleware('AuthMiddleware'); //, 'protected_hard' => true

        $routes->addWithOptionsAndName('View Member Notification History', 'member_notification_history', '/member/notifications')->controller('memberController@viewNotificationHistory')->middleware('AuthMiddleware'); //, 'protected_hard' => false


        $routes->addWithOptionsAndName('images', 'member_images', '/member/profile/images/{file}')->controller('memberController@images');
        $routes->addWithOptionsAndName('css', 'member_css', '/member/profile/css/{file}')->controller('memberController@css');
        $routes->addWithOptionsAndName('js', 'member_js', '/member/profile/js/{file}')->controller('memberController@js');

        /* -------------- SITEADMIN ROUTES ------------------- */
        // MEMBER

        $routes->addWithOptionsAndName('Members Export', 'member_sa_export', '/siteadmin/members/export')->controller('saMemberExportController@exportAll');//'permissions' => 'members_list,members_export'


        $routes->addWithOptionsAndName('Manage ' . app::get()->getConfiguration()->get('member_module_name')->getValue() . 's', 'member_sa_accounts', '/siteadmin/members')->controller('saMemberController@manageMembers');//'permissions' => 'members_list,members_view'
        $routes->addWithOptionsAndName('Create ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'member_sa_account_create', '/siteadmin/members/create')->controller('saMemberController@editMember');//'permissions' => 'members_add'
        $routes->addWithOptionsAndName('Edit ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'member_sa_account_edit', '/siteadmin/members/{id}/edit')->controller('saMemberController@editMember');//'permissions' => 'members_view'
        $routes->addWithOptionsAndName('Save ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'member_sa_account_save', '/siteadmin/members/{id}/save')->controller('saMemberController@saveMember')->methods(['POST']);//'permissions' => 'members_edit,members_add'
        $routes->addWithOptionsAndName('Delete ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'member_sa_account_delete', '/siteadmin/members/{id}/delete')->controller('saMemberController@deleteMember');//'permissions' => 'members_delete'
        $routes->addWithOptionsAndName('SA User Login as ' . app::get()->getConfiguration()->get('member_module_name'), 'member_sa_account_superuser_login', '/siteadmin/members/superuser/{id}/login')->controller('saMemberController@saUserLoginAsMember');//'permissions' => 'members_login_user'

        // MEMBER USERNAMES
        $routes->addWithOptionsAndName('Add Username', 'member_sa_createusers', '/siteadmin/members/{member_id}/edit/username/create')->controller('saMemberController@editMemberUsers');
        $routes->addWithOptionsAndName('Save Username', 'member_sa_saveusernames', '/siteadmin/members/{member_id}/edit/username/{id}/edit$')->controller('saMemberController@saveMemberUsers')->methods(['POST']);
        $routes->addWithOptionsAndName('Edit Username', 'member_sa_editusernames', '/siteadmin/members/{member_id}/edit/username/{id}/edit$')->controller('saMemberController@editMemberUsers');
        $routes->addWithOptionsAndName('Delete Username', 'member_sa_deleteusernames', '/siteadmin/members/{member_id}/edit/username/{id}/delete')->controller('saMemberController@deleteMemberUsers');

        // MEMBER EMAILS
        $routes->addWithOptionsAndName('Add Email', 'member_sa_createemail', '/siteadmin/members/{member_id}/edit/email/create')->controller('saMemberController@editMemberEmail');
        $routes->addWithOptionsAndName('Save Email', 'member_sa_saveemail', '/siteadmin/members/{member_id}/edit/email/{id}/edit')->controller('saMemberController@saveMemberEmail')->methods(['POST']);
        $routes->addWithOptionsAndName('Edit Email', 'member_sa_editemail', '/siteadmin/members/{member_id}/edit/email/{id}/edit')->controller('saMemberController@editMemberEmail');
        $routes->addWithOptionsAndName('Delete Email', 'member_sa_deleteemail', '/siteadmin/members/{member_id}/edit/email/{id}/delete')->controller('saMemberController@deleteMemberEmail');

        // MEMBER ADDRESSES
        $routes->addWithOptionsAndName('Add Address', 'member_sa_createaddress', '/siteadmin/members/{member_id}/edit/address/create')->controller('saMemberController@editMemberAddress');
        $routes->addWithOptionsAndName('Save Address', 'member_sa_saveaddress', '/siteadmin/members/{member_id}/edit/address/{id}/edit')->controller('saMemberController@saveMemberAddress')->methods(['POST']);
        $routes->addWithOptionsAndName('Edit Address', 'member_sa_editaddress', '/siteadmin/members/{member_id}/edit/address/{id}/edit')->controller('saMemberController@editMemberAddress');
        $routes->addWithOptionsAndName('Delete Address', 'member_sa_deleteaddress', '/siteadmin/members/{member_id}/edit/address/{id}/delete')->controller('saMemberController@deleteMemberAddress');

        // MEMBER PHONE'S
        $routes->addWithOptionsAndName('Add Phone', 'member_sa_createphone', '/siteadmin/members/{member_id}/edit/phone/create')->controller('saMemberController@editMemberPhone');
        $routes->addWithOptionsAndName('Save Phone', 'member_sa_savephone', '/siteadmin/members/{member_id}/edit/phone/{id}/edit')->controller('saMemberController@saveMemberPhone')->methods(['POST']);
        $routes->addWithOptionsAndName('Edit Phone', 'member_sa_editphone', '/siteadmin/members/{member_id}/edit/phone/{id}/edit')->controller('saMemberController@editMemberPhone');
        $routes->addWithOptionsAndName('Delete Phone', 'member_sa_deletephone', '/siteadmin/members/{member_id}/edit/phone/{id}/delete')->controller('saMemberController@deleteMemberPhone');

        // GROUPS
        $routes->addWithOptionsAndName('Manage Groups', 'member_sa_group', '/siteadmin/groups')->controller('saMemberController@manageGroups');//'permissions' => 'members_groups_list'
        $routes->addWithOptionsAndName('Create Group', 'member_sa_group_create', '/siteadmin/groups/create')->controller('saMemberController@editGroup');//'permissions' => 'members_groups_add'
        $routes->addWithOptionsAndName('Edit Group', 'member_sa_group_edit', '/siteadmin/groups/{id}/edit')->controller('saMemberController@editGroup');//'permissions' => 'members_groups_view'
        $routes->addWithOptionsAndName('Save Group', 'member_sa_group_save', '/siteadmin/groups/{id}/edit')->controller('saMemberController@saveGroup')->methods(['POST']);//'permissions' => 'members_groups_edit,members_groups_add'
        $routes->addWithOptionsAndName('Delete Group', 'member_sa_group_delete', '/siteadmin/groups/{id}/delete')->controller('saMemberController@deleteGroup');//'permissions' => 'members_groups_delete'

        $routes->addWithOptionsAndName('Add Group to ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'member_sa_addgrouptomember', '/siteadmin/members/{id}/edit/addgroup')->controller('saMemberController@addMembertoGroup');//'permissions' => 'members_groups_view'
        $routes->addWithOptionsAndName('Add Group to ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'member_sa_addgrouptomember_save', '/siteadmin/members/{id}/edit/addgroup')->controller('saMemberController@addMembertoGroupSave')->methods(['POST']);//'permissions' => 'members_groups_view'
        $routes->addWithOptionsAndName('Delete Group to ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'member_sa_deletememberfromgroup', '/siteadmin/members/{member_id}/group/{id}/delete')->controller('saMemberController@deleteMemberFromGroup');//'permissions' => 'members_groups_view'
        // OTHER
        $routes->addWithOptionsAndName('Human Verify', 'member_humanverify', '/member/humanverify')->controller('memberController@humanVerify'); //'protected' =>  false
        $routes->addWithOptionsAndName('Human Verify', 'member_humanverifypost', '/member/humanverify')->controller('memberController@humanVerifyAttempt')->methods(['POST']); //'protected' =>  false

        $routes->addWithOptionsAndName('Device Verify', 'member_machineverify', '/member/machineverify')->controller('MemberTwoFactorController@machineVerify')->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Device Verify', 'member_machineverifycode', '/member/machineverifycode')->controller('MemberTwoFactorController@machineVerifyCode')->methods(['POST'])->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Device Verify', 'member_machineverifycodeverify', '/member/machineverifycodeverify')->controller('MemberTwoFactorController@machineVerifyCodeVerify')->methods(['POST'])->middleware('AuthMiddleware'); //

        $routes->addWithOptionsAndName('Device Verify', 'member_machineverifyvoice', '/member/machineverifyvoice')->controller('MemberTwoFactorController@machineVerifyVoice'); //'protected' =>  false
        $routes->addWithOptionsAndName('Device Verify', 'member_machineverifyvoicetext', '/member/machineverifyvoicetext')->controller('MemberTwoFactorController@machineVerifyVoiceText'); //'protected' =>  false

        $routes->addWithOptionsAndName('Additional Authentication Required', 'member_two_factor_verify', '/member/two-factor-auth')->controller('MemberTwoFactorController@twoFactorVerify')->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Additional Authentication Required', 'member_two_factor_verify_user_input', '/member/two-factor-auth-code')->controller('MemberTwoFactorController@twoFactorVerifyUserInput')->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Additional Authentication Required', 'member_two_factor_verify_user_input_validate', '/member/two-factor-auth-code-validate')->controller('MemberTwoFactorController@twoFactorVerifyUserInputValidate')->methods(['POST'])->middleware('AuthMiddleware'); //


        $routes->addWithOptionsAndName('Additional Authentication Setup', 'member_additionalauthsetup', '/member/additional-auth-setup')->controller('MemberTwoFactorController@additionalAuthSetup')->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Additional Authentication Setup', 'member_additionalauthsetup_test', '/member/additional-auth-setup/test')->controller('MemberTwoFactorController@additionalAuthSetupTest')->middleware('AuthMiddleware'); //
        $routes->addWithOptionsAndName('Additional Authentication Setup', 'member_additionalauthsetup_test_submit', '/member/additional-auth-setup/test/save')->controller('MemberTwoFactorController@additionalAuthSetupTestSubmit')->methods(['POST'])->middleware('AuthMiddleware'); //

//            member_two_factor_verify
        $routes->addWithOptionsAndName('Manage Users', 'member_sa_users', '/siteadmin/members/users')->controller('saMemberController@manageUsers');//'permissions' => 'members_manage_users'
        $routes->addWithOptionsAndName('Login as User', 'member_sa_userlogin', '/siteadmin/members/{member_id}/username/{id}/login')->controller('saMemberController@loginAsUser');

        // API ROUTES
//        $routes->addWithOptionsAndName('API Login', 'api_member_login', '/api/v1/member/saMember/login')->controller('MemberApiV1Controller@login')->middleware('ApiRouteMiddleware')->methods(['POST', 'GET']);
    }

    public static function getPermissions()
    {
        $permissions = array();
        $permissions['members_list'] = 'List Members';
        $permissions['members_add'] = 'Add Member';
        $permissions['members_edit'] = 'Edit Member';
        $permissions['members_view'] = 'View Member';
        $permissions['members_delete'] = 'Delete Member';
        $permissions['members_login_user'] = 'Member User Login';
        $permissions['members_groups_list'] = 'List Groups';
        $permissions['members_groups_add'] = 'Add Group';
        $permissions['members_groups_edit'] = 'Edit Group';
        $permissions['members_groups_view'] = 'View Group';
        $permissions['members_groups_delete'] = 'Delete Group';
        $permissions['members_manage_users'] = 'Manage Users';

        return $permissions;
    }

    static function getNavigation()
    {

        return array(

            new navItem(array('id' => 'logout', 'name' => 'Logout', 'routeid' => 'member_logoff', 'icon' => 'fa fa-double-angle-right', 'parent' => 'system')),

            // SITEADMIN
            new navItem(array('id' => 'saMember', 'name' => app::get()->getConfiguration()->get('member_module_name')->getValue() . 's', 'icon' => 'fas fa-users', 'parent' => 'siteadmin_root')),
            new navItem(array('id' => 'manageMembers', 'name' => 'Manage ' . app::get()->getConfiguration()->get('member_module_name')->getValue() . 's', 'subpattern' => '/siteadmin/members/[0-9]{1,}', 'routeid' => 'member_sa_accounts', 'icon' => 'fas fa-user', 'parent' => 'saMember')),
            new navItem(array('id' => 'createMember', 'name' => 'Create ' . app::get()->getConfiguration()->get('member_module_name')->getValue(), 'routeid' => 'member_sa_account_create', 'icon' => 'fas fa-user-plus', 'parent' => 'saMember')),
            new navItem(array('id' => 'groups', 'name' => 'Manage Groups', 'routeid' => 'member_sa_group', 'icon' => 'fas fa-users', 'parent' => 'saMember')),
            new navItem(array('id' => 'manageUsers', 'name' => 'Manage Users', 'routeid' => 'member_sa_users', 'icon' => 'fa fa-user', 'parent' => 'saMember'))
        );
    }

    static function getCLICommands()
    {
        return array(
            ioc::staticGet('DeleteMembersCommand')
        );
    }

    static function init()
    {
        MiddlewareManager::register('AuthMiddleware', 'AuthMiddleware', MiddlewareManager::MIDDLEWARE_PRIORITY_HIGH);

        modRequest::listen('app.pre.routeVerify', 'auth@isAllowedToRunRoute', 25, 'memberAuth');

        modRequest::listen('sa.member.login.async', 'memberController@memberLoginAsync', 1, null, true, false);
        modRequest::listen('sa.member.get_all_groups', 'saMemberController@getAllGroups');
        modRequest::listen('auth.object', 'auth@getInstance');
        modRequest::listen('auth.member_id', 'auth@getAuthMemberId');
        modRequest::listen('auth.member', 'auth@getAuthMember');
        modRequest::listen('auth.member.groups', 'auth@getAuthMemberGroups');
        modRequest::listen('auth.user', 'auth@getAuthUser');
        modRequest::listen('auth.user_id', 'auth@getAuthUserId');
        modRequest::listen('app.header', 'memberController@headerWidget');
        modRequest::listen('site.header', 'memberController@headerWidget');
        modRequest::listen('auth.ping', 'memberController@ping');
        modRequest::listen('member.dashboard', 'memberController@getDashboardItems');
        modRequest::listen('member.newNotifications', 'memberController@getNewNotifications', 1, null, true, true);
        modRequest::listen('member.newNotificationsCount', 'memberController@getNewNotificationsCount', 1, null, true, true);
        modRequest::listen('member.markNotificationsViewed', 'memberController@markNotificationsAsViewed', 1, null, true, true);
        modRequest::listen('global.assets.js', 'auth@getUserSessionJs', 1, null, false, false);
        modRequest::listen('global.inline.js', 'auth@getUserSessionInlineJs', 1, null, true, false);

        /**
         * Returns a list of links to be rendered in the member profile
         * sidebar navigation.
         *
         * Example implementation
         *
         *      $data[] = array(
         *          [0] => array(
         *              'label' => $label,
         *              'href'  => $link,
         *              'children' => array(
         *                  [0] => array(
         * 'label' => ...,
         *                      'href' => ...,
         *                      'children' => array(...)
         *                  ),
         *                  ...
         *              )
         *          ),
         *          ...
         *      );
         *
         *      return $data;
         */
        modRequest::listen('member.profile_sidebar_links', 'MemberProfileModRequestListeners@getSidebarLinks');

        /**
         * Returns a list if HTML templates to be rendered in the profile's sidebar.
         *
         * Example implementation
         *
         *      $data[] = "{HTML template}";
         *      return $data;
         */
        modRequest::listen('member.profile_sidebar_widgets', 'MemberProfileModRequestListeners@getSidebarWidgets');

        modRequest::listen('member.login.customRedirect', 'memberController@modRequestLoginCustomRedirect');
        modRequest::listen('site.elements', 'MemberElementsController@elements');

        modRequest::listen('member.session.extend', 'memberSessionController@extendSession', 1, null, true, true);
        modRequest::listen('member.session.logoff', 'memberSessionController@logoffSession', 1, null, true, true);

        modRequest::listen('auth.user.front', 'memberController@frontGetCurrentUser', 1, null, true, true);
    }

    static function postInit()
    {
        modRequest::request('api.registerEntityAPI', null, array('entity' => 'saMember', 'controller' => 'MemberApiV1Controller'));
    }

    public static function getSettings()
    {
        $module_settings = array(
            'member_groups' => array('type' => 'select', 'options' => array('member', 'user'), 'module' => 'Member', 'default' => 'member'),
            'member_login' => array('type' => 'text', 'module' => 'Member', 'default' => '/member/login'),
            'member_module_name' => array('module' => 'Member', 'default' => 'Member'),
            'member_confirmation_email' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'allow_muliple_logins' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'allow_soft_login' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'member_session_timeout_enabled' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'member_session_timeout' => array('type' => 'text', 'module' => 'Member', 'default' => null),
            'member_session_timeout_interval' => array('type' => 'text', 'module' => 'Member', 'default' => null),
            'member_device_verify' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'member_two_factor_require' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'member_two_factor_use_phone' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'password_reset_ttl' => array('type' => 'text', 'module' => 'Member', 'default' => 2700),
            'signup_form_use_recaptcha' => array('type' => 'boolean', 'module' => 'Member', 'default' => false),
            'allow_manual_add_member' => array('type' => 'boolean', 'module' => 'Member', 'default' => true),
            'sso_user_refresh_ttl' => array('type' => 'integer', 'module' => 'Member', 'default' => 600000),
            'enable_public_member_signup' => array('type' => 'boolean', 'module' => 'Member', 'default' => true)
        );

        return $module_settings;
    }
}
   
