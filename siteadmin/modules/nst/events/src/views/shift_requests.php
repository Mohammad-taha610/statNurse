@extends('master')

@section('page-title')
Shift Requests
@endsection

@section('site-container')

<shift-requests-view></shift-requests-view>

@asset::/shift/js/shift_requests_vue.js

@endsection