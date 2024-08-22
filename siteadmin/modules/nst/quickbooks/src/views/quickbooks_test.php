@extends('master)

@section('site-container')

<quickbooks-test-view
        code="$code"
        state="$state"
        realm_id="$realmId"
></quickbooks-test-view>


@asset::/siteadmin/quickbooks/js/quickbooks_test_vue.js