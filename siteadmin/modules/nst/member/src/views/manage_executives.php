@extends('master')

@section('page-title')
Manage Executives
@endsection

@section('site-container')
<manage-executives-view></manage-executives-view>

@asset::/siteadmin/member/js/manage_executives_vue.js
@asset::/themes/nst/js/vue-mask.min.js
@show
