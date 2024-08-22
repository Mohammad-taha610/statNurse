@extends('master')

@section('page-title')
Shift Calendar
@endsection

@section('site-container')

<full-calendar-index-view></full-calendar-index-view>

@asset::/shift/js/shift_calendar_vue.js
@asset::/shift/js/shift_calendar_monthly_vue.js
@asset::/shift/js/shift_calendar_weekly_vue.js
@asset::/shift/js/shift_calendar_daily_vue.js
@asset::/shift/js/shift_calendar_event_vue.js
@asset::/shift/js/create_shift_modal_vue.js
@asset::/shift/css/create_shift_modal_css.css

@endsection