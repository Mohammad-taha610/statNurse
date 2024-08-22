@extends('master')

@section('page-title')
PBJ Report
@endsection

@section('site-container')

<pbj-report
    :provider_id="$provider_id"
    :period="'$period'"
></pbj-report>

@asset::/themes/nst/assets/css/VueTimepicker.css
@asset::/themes/nst/assets/js/VueTimepicker.umd.min.js
@asset::/siteadmin/payroll/js/pbj_report_vue.js

@endsection