@extends('master')

@section('page-title')
Manage Providers
@endsection

@section('site-container')

<manage-providers-view></manage-providers-view>

@asset::/siteadmin/member/js/manage_providers_vue.js
@asset::/themes/nst/js/vue-mask.min.js
@show