@extends('master')

@section('page-title')
Payment History
@endsection

@section('site-container')

<pay-period-view
        :provider_id="$provider_id"
        :period="'$period'"
        :show_unresolved_only="$unresolved_only"
></pay-period-view>

@asset::/themes/nst/assets/css/VueTimepicker.css
@asset::/themes/nst/assets/js/VueTimepicker.umd.min.js
@asset::/siteadmin/payroll/js/pay_period_vue.js

@endsection