@extends('master')

@section('site-container')
<style>
    .margin-row{
        margin: 30px 20px 30px 20px;
    }

    #vue-component-context input{
        border: none;
    }

    .vs__selected-options input{
        border:none;
    }
    .btn-primary.print-btn a {
        color: #fff;
    }
    .generatebarcode, .removebarcodeimage {
        margin-top:15px;
    }
    .barcode-box {
        margin-top: 30px;
        background: #f7f7f7;
        padding: 15px;
        max-width: 250px;
        margin: 25px auto 15px;
        border: 1px solid #eee;
    }
    .btn-primary.print-btn a {
        color: #fff;
    }

    .pTag{
        font-size: 16px;
    }

</style>
<div class="materials-details-container">
    <div class="row" style="margin:0">
        <div class="col-xs-12 col-sm-12 col-md-6">
            <!--        <form id="vue-context" class="form-horizontal material-form" method="POST" action="url('save_equipment',{'id':$id})">-->
            <div id="vue-context" class="form-horizontal material-form">

                <div class="form-group">
                    <h2>Shift Information</h2>
                    <h3>Provider</h3><p class="pTag"> $provider</p>
                    <h3>Time</h3><p <p class="pTag"> $startTime To $endTime</p>
                </div>
                <edit-pending-shift></edit-pending-shift>
            </div>
        </div>
    </div>
</div>
</div>

@asset::/shift/js/edit_pending_shift_vue.js

@show
