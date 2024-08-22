@extends('master')

@section('page-title')
Invoices
@endsection

@section('site-container')

<invoices-list-view
    :provider_id="$provider_id"
></invoices-list-view>

@asset::/siteadmin/payroll/js/invoices_list_vue.js

@endsection