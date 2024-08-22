@extends('master)

@section('site-container')

<sa-edit-invoice-view
    id="$id"
></sa-edit-invoice-view>


@asset::/applications/js/file-uploader.js
@asset::/siteadmin/payroll/js/sa_edit_invoice_vue.js