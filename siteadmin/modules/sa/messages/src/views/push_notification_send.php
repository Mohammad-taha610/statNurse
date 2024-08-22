@extends('master')
@section('site-container')
@asset::/siteadmin/messages/js/sendPushNotification.js

<style>
    [v-cloak] {
        display: none;
    }
</style>

<?php if(!$hasBeenConfigured) { ?>
    <div class="row">
        <div class="col-sm-12 col-md-9 col-md-offset-2 alert alert-block alert-warning">
            <h4 style="display: inline" class="yellow"><strong><i class="fa fa-exclamation-triangle yellow"></i> &nbsp;Note:</strong></h4>&nbsp;&nbsp;&nbsp;&nbsp;
            Your site has NOT been been configured to send push notifications. If you experience any issues, please contact <strong>support@elinkdesign.com</strong>.
        </div>
    </div>
<?php } ?>

<div id="push-notification-container" class="form-horizontal" style="margin-top: 40px;" v-cloak>

    <div class="row" v-if="successMessage !== null">
        <div class="col-sm-12 col-md-9 col-md-offset-2 alert alert-block alert-success">
            <h4 style="display: inline" class="yellow"><strong><i class="fa fa-exclamation-triangle green"></i> &nbsp;Success:</strong></h4>&nbsp;&nbsp;&nbsp;&nbsp;
            {{ successMessage }}
        </div>
    </div>

    <div class="row" v-if="errorMessage !== null">
        <div class="col-sm-12 col-md-9 col-md-offset-2 alert alert-block alert-danger">
            <h4 style="display: inline" class="yellow"><strong><i class="fa fa-exclamation-triangle red"></i> &nbsp;Error:</strong></h4>
            {{ errorMessage }}
        </div>
    </div>


    <div class="col-md-8 col-md-push-2">
        <div class="col-md-12">
            <div class="form-group">
                <div class="col-md-12">
                    <h4><strong>Title:</strong></h4>
                    *Maximum 1024 characters
                    <input v-model="title" placeholder="Title" class="form-control" maxlength="1024">
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <div class="col-md-12">
                    <h4><strong>Message:</strong></h4>
                    *Maximum 1024 characters
                    <textarea v-model="message" class="form-control" maxlength="1024" rows="10"></textarea>
                </div>
            </div>
        </div>

        <div class="col-md-12 text-center">
            <button id="notification-send-submit" v-on:click="sendNotification" class="btn" :disabled="isSubmitDisabled">
                <span v-if="isLoading === false"><i class="fa fa-send"></i>&nbsp;Queue Notification</span>
                <span v-if="isLoading === true"><i class="fa fa-circle-o-notch fa-spin"></i>&nbsp;Queueing Notification</span>
            </button>
        </div>
    </div>
</div>
@show