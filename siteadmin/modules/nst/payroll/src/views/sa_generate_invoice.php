@extends('master)

@section('site-container')

<sa-generate-invoice-view
        _provider_id="$provider_id"
        _pay_period="$pay_period"
></sa-generate-invoice-view>

@asset::/siteadmin/payroll/js/sa_generate_invoice_vue.js