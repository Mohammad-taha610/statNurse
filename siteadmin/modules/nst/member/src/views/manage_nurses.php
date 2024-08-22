@extends('master')

@section('page-title')
Manage Nurses
@endsection

@section('site-container')

<manage-nurses-view></manage-nurses-view>

@asset::/siteadmin/member/js/manage_nurses_vue.js
@asset::/themes/nst/js/vue-mask.min.js
@show