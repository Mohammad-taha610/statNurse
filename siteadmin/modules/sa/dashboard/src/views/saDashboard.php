@extends('master')

@section('site-container')

@asset::/siteadmin/dashboard/js/sa_dashboard.js
@asset::/siteadmin/dashboard/css/sa_dashboard.css
<div class="container-fluid dashboard-widgets-main-container">
    <div class="row dashboard-widgets-top-row">
        <div class="col-xs-12 dashboard-widgets-top-col placement" data-location="dashboard-widgets-top-col">

        </div>
    </div>
    <div class="row dashboard-widgets-main-row">
        <div class="col-xs-12 col-lg-8 dashboard-widgets-left-col placement" data-location="dashboard-widgets-left-col">
            <div style="margin: 1px"></div>
        </div>
        <div class="col-xs-12 col-lg-4 dashboard-widgets-right-col placement" data-location="dashboard-widgets-right-col">

        </div>
    </div>
    <div class="row dashboard-widgets-bottom-row">
        <div class="col-xs-12 dashboard-widgets-bottom-col placement" data-location="dashboard-widgets-bottom-col">

        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 widget-tobemoved">

        </div>
    </div>
</div>



<div class="ace-settings-container" id="ace-settings-container" style="position: absolute; top: 115px;">
    <div class="btn btn-app btn-xs btn-primary ace-settings-btn" id="ace-settings-btn">
        <i class="ace-icon fa fa-cog bigger-130"></i>
    </div>

    <div class="ace-settings-box clearfix" id="ace-settings-box" style="border: 2px solid #146397; width: 400px; padding: 10px;">
        <div class="pull-left width-100 text-center">
            <div class="ace-settings-item">
                Add new widgets by clicking on one of the following:
            </div>
        </div>

        <div class="row">
            <?php
            foreach ($available_widgets as $widget) {
                echo '<div class=" col-md-12 col-lg-12">
                <a class="btn addWidget" style="display: block; margin: 1px" data-widget_id="'.$widget['id'].'"><i class="fa fa-plus"></i>'.$widget['name'].'</a>
             </div>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    var user_settings = <?= json_encode($settings) ?>;
    var available_widgets = <?= json_encode($available_widgets) ?>;
</script>
@show