@extends('master')

@section('page-title')
Manage Applications
@endsection

@section('site-container')

<manage-applications-view></manage-applications-view>

@asset::/themes/nst/js/vue-mask.min.js
@asset::/applications/js/sa_manage_applications_vue.js
@show