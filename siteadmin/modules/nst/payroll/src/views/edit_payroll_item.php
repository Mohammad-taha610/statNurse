@extends('master')
@asset::/siteadmin/payroll/js/edit_payroll_item_vue.js
@section('site-container')
<style>
    .main-content .page-content .materials-details-container #material-form.material-form .margin-row div.col-md-5 {
        padding-left: 0px;
        padding-right: 0px;
    }
    .btn-primary.print-btn a {
        color: #fff;
    }

    .vs__selected-options input{
        border:none;
    }
    .profile-edit-tab-content .form-group {
        margin-right:0;
    }
</style>
<div id="vue-context" class="row">
    <edit-payroll-item></edit-payroll-item>
</div>
@show