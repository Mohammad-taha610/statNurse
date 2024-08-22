@extends('master)

@section('site-container')

<?php
$auth = \sa\system\saAuth::getInstance();
$saUser = is_object($auth) ? $auth->getAuthUser() : null;
$canCreateApprovedShifts = $saUser?->hasGroupPermission('events-create-approved-shifts') ? 1 : 0;
?>

<sa-shift-calendar-view
        :provider_id="$provider_id"
        :nurse_id="$nurse_id"
        :category_id="$category_id"
        :can_delete_past_shifts="<?= $canCreateApprovedShifts ?>"
></sa-shift-calendar-view>

@asset::/shift/js/sa_shift_calendar_vue.js
@asset::/shift/js/shift_calendar_monthly_vue.js
@asset::/shift/js/shift_calendar_weekly_vue.js
@asset::/shift/js/shift_calendar_daily_vue.js
@asset::/shift/js/shift_calendar_event_vue.js
@asset::/shift/js/sa_create_shift_modal_vue.js
@asset::/themes/nst/assets/vendor/toastr/js/toastr.min.js
@asset::/themes/nst/assets/vendor/toastr/css/toastr.min.css