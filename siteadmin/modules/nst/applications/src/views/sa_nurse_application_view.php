@extends('master')

@section('page-title')
Nurse Application
@endsection

@section('site-container')

<?php

$application_id = $application->getId();
?>

<div data-app>
    <v-app>
        <application-profile-view
            :application_id="<?php echo $application_id; ?>"
        ></application-profile-view>
    </v-app>
</div>

@asset::/themes/nst/js/vue-mask.min.js
@asset::/applications/js/file-uploader.js
@asset::/applications/js/sa_application_profile_vue.js
@asset::/applications/js/sa_manage_applications_basic_info_vue.js
@asset::/applications/js/sa_manage_applications_application_vue.js
@asset::/applications/js/sa_manage_applications_licenses_vue.js
@asset::/applications/js/sa_manage_applications_files_vue.js
@asset::/applications/js/sa_manage_applications_drug_screen_vue.js
@asset::/applications/js/sa_manage_applications_background_check_vue.js
@asset::/applications/js/sa_manage_applications_messaging_vue.js
@show