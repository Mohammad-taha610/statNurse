@extends('master')
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
<script>
    window.addEventListener('load', function() {

        $("#submit").click(function (event) {
            event.preventDefault();
            var errors = [];
            $(".errors").html("");
            var kit = $("input[name=title]").val();
            if (kit == "") {
                errors.push("Please enter a Title");
            }

            if (errors.length === 0) {
                $(".errors").html("");
                $(".errors").hide();
                // $("submit").submit();
                $('#btnclick').trigger('click');
            } else {
                $(".errors").show();
                $.each(errors, function (index, value) {
                    $(".errors").append(value + "<br>");

                });
            }

        })
    });
</script>
<div id="vue-context" class="row">
    <div class="col-xs-12">
        <div class="alert alert-danger errors" role="alert" style="display:none">
        </div>
        <div class="tabbable">
            <ul class="nav nav-tabs padding-16">
                <li class="active">
                    <a data-toggle="tab" href="#edit-basic">
                        <i class="blue fa fa-edit bigger-125"></i>
                        Basic Info
                    </a>
                </li>
                <li class="">
                    <a data-toggle="tab" href="#edit-license">
                        <i class="orange2 fa fa-clock bigger-125"></i>
                        Schedule
                    </a>
                </li>
            </ul>
            <div class="tab-content profile-edit-tab-content">
                <div id="edit-basic" class="tab-pane active">
                    <div class="form-group">
                        <h4 class="header blue bolder smaller">Basic Info</h4>
                    </div>
                    <form id="material-form" class="form-horizontal material-form" method="POST" action="@url('save_provider',{'id':$id})">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="title">First Name</label>
                                    <input  class="form-control" name="first_name" type="text" placeholder="First Name" value="<?=$first_name?>"/>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="title">Last Name</label>
                                    <input  class="form-control" name="last_name" type="text" placeholder="Last Name" value="<?=$last_name?>"/>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="active">Is Active</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option <?=$is_active==1 ? 'selected' : ''?> value="1">Yes</option>
                                        <option <?=$is_active==0 ? 'selected' : ''?> value="0">No</option>
                                        <option <?=is_null($is_active) ? 'selected' : ''?>  disabled>Is Active?</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="title">Username</label>
                                    <input  class="form-control" name="username" type="text" placeholder="Username" value="<?=$username?>"/>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="title">Password</label>
                                    <input  class="form-control" name="password" type="password" placeholder="To retain the old password, leave this field blank." value="<?=$password?>"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                <div class="clearfix form-actions">
                                    <button class="btn btn-info" type="button" id="submit">
                                        <i class="fa fa-save bigger-110"></i>
                                        Save
                                    </button>
                                    <input type ="submit" id="btnclick" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="edit-license" class="tab-pane">
                    <div class="form-group">
                        <h4 class="header blue bolder smaller">Licenses</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@show