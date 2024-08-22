<?php

namespace nst\applications;

use sacore\application\route;
use sacore\application\navItem;
use sacore\application\saRoute;
use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\ioc;

class applicationsConfig extends moduleConfig
{
    public static function init()
    {
        modRequest::listen('nurse.application.store', 'NurseApplicationController@store', 1, null, true, false);
        modRequest::listen('nurse.application.socialSecurityNumber', 'NurseApplicationController@getSocialSecurityNumber', 1, null, true, false);
        modRequest::listen('nurse.application.storeTwo', 'NurseApplicationController@storeTwo', 1, null, true, false);
        modRequest::listen('nurse.application.submission_email', 'NurseApplicationController@submissionEmail', 1, null, true, false);
        modRequest::listen('nurse.background_check.store', 'NurseBackgroundCheckController@store', 1, null, true, false);
        modRequest::listen('nurse.application.update', 'SaNurseApplicationController@update', 1, null, true, false);
        modRequest::listen('nurse.application.approve', 'SaNurseApplicationController@approveNurseDeprecated', 1, null, true, false);
        modRequest::listen('nurse.application.decline', 'SaNurseApplicationController@declineNurse', 1, null, true, false);
        modRequest::listen('nurse.application.approved_applicant_email', 'SaNurseApplicationController@sendApprovedEmail', 1, null, true, false);
        modRequest::listen('nurse.application.declined_applicant_email', 'SaNurseApplicationController@sendDeclinedEmail', 1, null, true, false);
        modRequest::listen('nurse.application.submitted_page', 'NurseApplicationController@navigateToSubmittedPage', 1, null, true, false);
        // modRequest::listen('nurse.application.login', 'NurseApplicationController@applicationLogin', 1, null, true, false);
        modRequest::listen('nurse.application.generatei9', 'SaNurseApplicationController@generatei9', 1, null, true, false);
        modRequest::listen('nurse.application.delete_application_file', 'NurseApplicationController@deleteApplicationFile', 1, null, true, false);

        // new application portal modrequests
        modRequest::listen('nurse.application.createLogin', 'NurseApplicationController@createLogin', 1, null, true, false);
        modRequest::listen('nurse.application.loginApplicant', 'NurseApplicationController@loginApplicant', 1, null, true, false);
        modRequest::listen('nurse.application.logoutApplicant', 'NurseApplicationController@logoutApplicant', 1, null, true, false);
        modRequest::listen('nurse.application.checkSession', 'NurseApplicationController@checkSession', 1, null, true, false);
        modRequest::listen('nurse.application.saveApplicationProgress', 'NurseApplicationController@saveApplicationProgress', 1, null, true, false);
        modRequest::listen('nurse.application.loadApplicationProgress', 'NurseApplicationController@loadApplicationProgress', 1, null, true, false);
        modRequest::listen('nurse.application.saveFilesProgress', 'NurseApplicationController@saveFilesProgress', 1, null, true, false);
        modRequest::listen('nurse.application.loadFilesProgress', 'NurseApplicationController@loadFilesProgress', 1, null, true, false);
        modRequest::listen('nurse.application.loadDrugScreenProgress', 'NurseApplicationController@loadDrugScreenProgress', 1, null, true, false);
        modRequest::listen('nurse.application.loadBackgroundCheckProgress', 'NurseApplicationController@loadBackgroundCheckProgress', 1, null, true, false);
        modRequest::listen('nurse.application.sendMobileFileUpload', 'NurseApplicationController@sendMobileFileUpload', 1, null, true, false);
        modRequest::listen('nurse.application.startDrugScreen', 'NurseApplicationController@startDrugScreen', 1, null, true, false);
        modRequest::listen('nurse.application.startBackgroundCheck', 'NurseApplicationController@startBackgroundCheck', 1, null, true, false);
        modRequest::listen('nurse.application.signBackgroundCheckAgreement', 'NurseApplicationController@signBackgroundCheckAgreement', 1, null, true, false);
        
        // new sa application modrequests
        modRequest::listen('nurse.application.sa.loadApplicationData', 'SaNurseApplicationController@loadApplicationData', 1, null, true, false);
        modRequest::listen('nurse.application.newApprove', 'SaNurseApplicationController@approveNurse', 1, null, true, false);
        modRequest::listen('nurse.application.newDecline', 'SaNurseApplicationController@declineNurse', 1, null, true, false);
        modRequest::listen('nurse.application.loadApplications', 'SaNurseApplicationController@loadApplications', 1, null, true, false);
        modRequest::listen('nurse.application.acceptLicense', 'SaNurseApplicationController@acceptLicense', 1, null, true, false);
        modRequest::listen('nurse.application.rejectLicense', 'SaNurseApplicationController@rejectLicense', 1, null, true, false);
        modRequest::listen('nurse.application.getAgreementPDF', 'SaNurseApplicationController@getAgreementPDF', 1, null, true, false);
        modRequest::listen('nurse.application.setAgreementPDF', 'SaNurseApplicationController@setAgreementPDF', 1, null, true, false);
        modRequest::listen('nurse.application.getBackgroundCheckAgreement', 'SaNurseApplicationController@getBackgroundCheckAgreement', 1, null, true, false);
        modRequest::listen('nurse.application.setBackgroundCheckAgreement', 'SaNurseApplicationController@setBackgroundCheckAgreement', 1, null, true, false);
        modRequest::listen('nurse.application.acceptDrugScreen', 'SaNurseApplicationController@acceptDrugScreen', 1, null, true, false);
        modRequest::listen('nurse.application.acceptBackgroundCheck', 'SaNurseApplicationController@acceptBackgroundCheck', 1, null, true, false);
    }

    public static function initRoutes($routes)
    {
        $routes->addWithOptionsAndName('Nurse Applications Deprecated', 'nurse_applications_index_deprecated', '/siteadmin/nurse/applications/deprecated')->controller('SaNurseApplicationController@index')->defaults(['route_permissions' => ['member_list_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Nurse Applications', 'manage_nurse_applications', '/siteadmin/nurse/applications')->controller('SaNurseApplicationController@manageApplications')->defaults(['route_permissions' => ['member_list_nurse']])->middleware('SaPermissionMiddleware');
        /** Nurse Application is deprecated, Nurse Application View is current */
        $routes->addWithOptionsAndName('Nurse Application', 'nurse_applications_show', '/siteadmin/nurse/applications/{application}')->controller('SaNurseApplicationController@show')->defaults(['route_permissions' => ['member_list_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Nurse Application View', 'nurse_applications_view', '/siteadmin/applications/{application}')->controller('SaNurseApplicationController@applicationView')->defaults(['route_permissions' => ['member_list_nurse']])->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Update Agreement', 'update_app_agreement_view', '/siteadmin/update-application-agreement')->controller('SaNurseApplicationController@updateAgreement')->middleware('SaPermissionMiddleware');
        $routes->addWithOptionsAndName('Mobile File Upload', 'mobile_file_upload', '/applications/mobile-file-upload/{application}')->controller('NurseApplicationController@mobileFileUpload')->middleware('SaPermissionMiddleware');

        $routes->addWithOptionsAndName('Nurse Application Form', 'application_form', '/nurse-application')->controller('NurseApplicationController@index');

        // THIS IS FOR VIEWING THE APPLICATION FORM IN A FULL-PAGE VIEW FOR PDF PRINTING
        // $routes->addWithOptionsAndName('Nurse Application Form PDF print', 'application_form_pdf_print', '/nurse-application-pdf-print')->controller('NurseApplicationController@indexPdfPrint');
        $routes->addWithOptionsAndName('Nurse Application Form Two', 'application_form_two', '/nurse-application-continued')->controller('NurseApplicationController@indexTwo');
        $routes->addWithOptionsAndName('Nurse Application Form Store', 'application_form_store', '/nurse-application')->methods(['post'])->controller('NurseApplicationController@store');
        $routes->addWithOptionsAndName('Nurse Application Form Two Store', 'application_form_store_two', '/nurse-application-continued')->methods(['post'])->controller('NurseApplicationController@storeTwo');

        $routes->addWithOptionsAndName('Nurse Application Background Check', 'application_background_check_index', '/application-background-check')->controller('NurseBackgroundCheckController@index');

        $routes->addWithOptionsAndName('js', 'applications_js', '/applications/js/{file}')->controller('NurseApplicationController@js');
        $routes->addWithOptionsAndName('css', 'applications_css', '/applications/css/{file}')->controller('NurseApplicationController@css');
    }

    public static function getNavigation()
    {
        return [

            new navItem(array('id' => 'applications', /*'routeid' => 'manage_applications',*/ 'icon' => 'fa fa-desktop', 'name' => 'Applications', 'parent' => 'siteadmin_root', 'priority' => navItem::PRIORITY_HIGH)),
            new navItem(array('id' => 'nurse_applications_deprecated', 'name' => 'Applications Deprecated', 'routeid' => 'nurse_applications_index_deprecated', /*'icon' => 'fas fa-user',*/ 'parent' => 'applications')),
            new navItem(array('id' => 'manage_applications', 'name' => 'Applications', 'routeid' => 'manage_nurse_applications', /*'icon' => 'fas fa-user',*/ 'parent' => 'applications')),
            new navItem(array('id' => 'update_app_agreement_view', 'name' => 'Update Agreement', 'routeid' => 'update_app_agreement_view', /*'icon' => 'fas fa-user',*/ 'parent' => 'applications')),
        ];
    }

    public static function getCLICommands()
    {
        return array(
            ioc::staticGet('EncryptNurseAppSocialSecurityNumberCommand')
        );
    }
}
