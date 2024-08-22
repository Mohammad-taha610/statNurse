@extends('master)

@section('site-container')

<?php 

$auth = \sa\system\saAuth::getInstance();
$saUser = is_object($auth) ? $auth->getAuthUser() : null;
$canEditItems = $saUser?->hasGroupPermission('payroll-can-edit-items') ? 1 : 0;
?>

<sa-pay-period-view
        :provider__id="$provider_id"
        :period="'$period'"
        :show_unresolved_only="$unresolved_only"
        :can_edit_items="<?= $canEditItems ?>"
></sa-pay-period-view>


@asset::/themes/nst/assets/css/VueTimepicker.css
@asset::/themes/nst/assets/js/VueTimepicker.umd.min.js
@asset::/siteadmin/payroll/js/sa_pay_period_vue.js
