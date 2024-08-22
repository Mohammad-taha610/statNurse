@extends('master)

@section('site-container')

<sa-reports-view
        :provider__id="$provider_id"
        :period="'$period'"
        :show_unresolved_only="$unresolved_only"
></sa-reports-view>


@asset::/themes/nst/assets/css/VueTimepicker.css
@asset::/themes/nst/assets/js/VueTimepicker.umd.min.js
@asset::/siteadmin/payroll/js/sa_reports_vue.js
@asset::/siteadmin/payroll/js/sa_report_inactive_vue.js
@asset::/siteadmin/payroll/js/sa_report_DNR_vue.js
@asset::/siteadmin/payroll/js/sa_report_earnings_vue.js
@asset::/siteadmin/payroll/js/sa_report_stub_vue.js
@asset::/siteadmin/payroll/js/sa_create_payment_modal_vue.js
@asset::/themes/nst/js/vue-mask.min.js
