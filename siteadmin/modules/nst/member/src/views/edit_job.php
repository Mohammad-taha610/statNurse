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
                $('#actualSubmitButton').trigger('click');
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
            </ul>
            <div class="tab-content profile-edit-tab-content">
                <div id="edit-basic" class="tab-pane active">
                    <div class="form-group">
                        <h4 class="header blue bolder smaller">Basic Info</h4>
                    </div>
                    <div class="row">
                        <form id="material-form" class="form-horizontal material-form" method="POST" action="@url('save_job',{'id':$id})">
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input class="form-control" name="title" type="text" placeholder="Title" value="<?=$title?>"/>
                                </div>
                            </div>
                            <div class="col-xs-12 text-center">
                                <div class="clearfix form-actions">
                                    <button class="btn btn-info" type="button" id="submit">
                                        <i class="fa fa-save bigger-110"></i>
                                        Save
                                    </button>
                                    <input type="submit" id="actualSubmitButton" style="display: none"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@show