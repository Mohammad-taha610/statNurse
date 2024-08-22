@extends('master)

@section('site-container')

<sa-view-invoice-vue
    id="$id"
    code="$code"
    state="$state"
    realm_id="$realmId"
    provider_id="$provider_id"
></sa-view-invoice-vue>

@asset::/siteadmin/payroll/js/sa_view_invoice_vue.js