@extends('master)

@section('site-container')

<export-customers-confirmation-view
    code="$code"
    state="$state"
    realm_id="$realmId"
></export-customers-confirmation-view>


@asset::/siteadmin/quickbooks/js/sa_export_customers_confirmation_vue.js