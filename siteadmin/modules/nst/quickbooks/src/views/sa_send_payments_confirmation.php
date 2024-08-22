@extends('master)

@section('site-container')

<send-payments-confirmation-view
    code="$code"
    state="$state"
    realm_id="$realmId"
    payment_ids="$payment_ids"
></send-payments-confirmation-view>

@asset::/siteadmin/quickbooks/js/sa_send_payments_confirmation_vue.js