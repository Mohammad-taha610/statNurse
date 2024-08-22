@extends('master)

@section('site-container')

<export-vendors-confirmation-view
    code="$code"
    state="$state"
    realm_id="$realmId"
></export-vendors-confirmation-view>


@asset::/siteadmin/quickbooks/js/sa_export_vendors_confirmation_vue.js