<?php

use sacore\application\app;

?>
@extends('master')
@asset::/member/profile/js/avatar.js
@asset::/vendor/blueimp/jquery-file-upload/css/jquery.fileupload.css
@asset::/vendor/blueimp/jquery-file-upload/js/vendor/jquery.ui.widget.js
@asset:://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js
@asset:://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js
@asset::/vendor/blueimp/jquery-file-upload/js/jquery.iframe-transport.js
@asset::/vendor/blueimp/jquery-file-upload/js/jquery.fileupload.js
@asset::/vendor/blueimp/jquery-file-upload/js/jquery.fileupload-process.js
@asset::/vendor/blueimp/jquery-file-upload/js/jquery.fileupload-image.js
@asset::/vendor/blueimp/jquery-file-upload/js/jquery.fileupload-validate.js
@asset::/vendor/blueimp/jquery-file-upload/js/jquery.fileupload-ui.js

@section('site-container')
<style>
    .profile-photo {
        color: #BDBDBD;
        margin-top: 20px;
        max-height: 200px; 
    }
    
    .image-preview-container {
        padding: 20px 20px 20px 20px; min-height: 200px;
    }
    
    #profile-image-dropzone:hover {
        cursor: pointer;
    }

    img.profile-photo {
        margin: 0 auto;
    }
    
</style>

<script type="text/javascript">

var deleteAvatarUrl = "@url('sa_member_avatar_remove', {'id':$memberId})";

$(document).ready( function() {
    var hash = window.location.hash.substring(1);
    if (hash!='') {
        $('a[href="#'+hash+'"]').click();
    }
});

$(document).on('click', '.remove-avatar', function(e) {
    e.preventDefault();
    console.log('here');
    $.ajax({
        url: deleteAvatarUrl,
        dataType: 'json',
        'method': 'POST',
        complete: function(data) {
            $('.avatar-container').html(
                '<i class="fa fa-photo profile-photo fa-4x"></i>' +
                '<p style="color: #9E9E9E">Click/Drag to upload</p>'
            );
            
            $('.remove-avatar').hide();
        }
    });
    
});

</script>

<div class="row">
<div class="col-xs-12">
<form id="profile-form" class="form-horizontal" method="POST" action="<?=$postRoute?>">
  <div class="tabbable">
    <ul class="nav nav-tabs padding-16">
      <li class="active">
        <a data-toggle="tab" href="#edit-basic">
          <i class="green fa fa-edit bigger-125"></i>
          Basic Info
        </a>
      </li>
      <?php if ($memberId) { ?>
      <li class="">
        <a data-toggle="tab" href="#edit-usernames">
          <i class="blue fa fa-key bigger-125"></i>
          Username/Password
        </a>
      </li>

      <li class="">
        <a data-toggle="tab" href="#edit-emailaddresses">
          <i class="blue fa fa-envelope bigger-125"></i>
          Email Addresses
        </a>
      </li>

      <li class="">
        <a data-toggle="tab" href="#edit-addresses">
          <i class="blue fa fa-envelope bigger-125"></i>
          Addresses
        </a>
      </li>

      <li class="">
        <a data-toggle="tab" href="#edit-phone">
          <i class="blue fa fa-phone bigger-125"></i>
          Phone Numbers
        </a>
      </li>

          <?php if (  app::get()->getConfiguration()->get('member_groups') =='member') { ?>

          <li class="">
            <a data-toggle="tab" href="#edit-groups">
              <i class="blue fa fa-group bigger-125"></i>
              Groups
            </a>
          </li>
            <?php
          }
      }
      ?>
      <li class="">
        <a data-toggle="tab" href="#edit-misc">
          <i class="blue fa fa-cog bigger-125"></i>
          Misc
        </a>
      </li>

        <?php if($other_tabs) { ?>
            <?php foreach($other_tabs as $tab) { ?>
                <li class="">
                    <a data-toggle="tab" href="#<?= $tab['id'] ?>">
                        <i class="blue <?= $tab['icon'] ?> bigger-125"></i>
                        <?= $tab['name'] ?>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>

    <div class="tab-content profile-edit-tab-content">
      <div id="edit-basic" class="tab-pane active">
        <h4 class="header blue bolder smaller">General</h4>
        <div class="row">
          <div class="col-xs-12 col-sm-4">
            <?php if($id != 0) { ?>
              
            <div id="profile-image-dropzone" class="ace-file-input ace-file-multiple" data-upload-route="@url('sa_member_avatar_upload', {'id':$memberId})" data-avatar-url="@url('sa_member_avatar', {'id':$memberId})?>" data-delete-url="@url('sa_member_avatar_remove', {'id':$memberId})">
                <input id="avatar-input" type="file" name="avatar">
                <div data-action="change-profile-photo" class="image-preview-container text-center well">
                    <?php if (!empty($avatar)) { ?>
                        <div class="avatar-container">
                            <img style="margin: 0 auto;" class="img-responsive profile-photo existing-profile-photo" src="@url('sa_member_avatar', {'id':$memberId})?1=<?=time()?>" />
                            <p style="color: #9E9E9E">Click/Drag to update</p>
                        </div>
                    <?php } else { ?>
                        <div class="avatar-placeholder-container">
                            <i class="fa fa-photo profile-photo fa-4x"></i>
                            <p style="color: #9E9E9E">Click/Drag to upload</p>
                        </div>
                    <?php } ?>
                    <span class="loading-icon" style="display: none;">
                        <i class="fa fa-spinner fa-spin"></i>
                    </span>
                </div>
                <div class="row remove-btn-container" style="<?= !empty($avatar) ? '' : 'display: none;' ?>">
                    <div class="col-xs-12 text-center">
                        <a href="#" class="remove-avatar btn btn-danger btn-xs"><i class="fa fa-trash"></i>&nbsp;Remove Avatar</a>
                    </div>
                </div>
            </div>
            <?php } else { ?>
                <div id="profile-image-dropzone" class="ace-file-input ace-file-multiple" data-upload-route="@url('sa_member_avatar_upload', {'id':$memberId})" data-avatar-url="@url('sa_member_avatar', {'id':$memberId})" data-delete-url="@url('sa_member_avatar_remove', {'id':$memberId})">
                    <input id="avatar-input" type="file" name="avatar">
                    <div class="image-preview-container text-center well">
                        <?php if (!empty($avatar)) { ?>
                            <div class="avatar-container">
                                <img style="margin: 0 auto;" class="img-responsive profile-photo existing-profile-photo" src="@url('sa_member_avatar', {'id':$memberId})?1=<?=time()?>" />
                                <p style="color: #9E9E9E">Click/Drag to update</p>
                            </div>
                        <?php } else { ?>
                            <div class="avatar-placeholder-container">
                                <i class="fa fa-photo profile-photo fa-4x"></i>
                                <p style="color: #9E9E9E">Save member first before uploading an avatar.</p>
                            </div>
                        <?php } ?>
                        <span class="loading-icon" style="display: none;">
                            <i class="fa fa-spinner fa-spin"></i>
                        </span>
                    </div>
                    <div class="row remove-btn-container" style="<?= !empty($avatar) ? '' : 'display: none;' ?>">
                        <div class="col-xs-12 text-center">
                            <a href="#" class="remove-avatar btn btn-danger btn-xs"><i class="fa fa-trash"></i>&nbsp;Remove Avatar</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
          </div>

          <div class="vspace-xs"></div>

          <div class="col-xs-12 col-sm-8">

            <div class="form-group">
              <label class="col-sm-4 control-label no-padding-right" for="form-field-company">Company</label>

              <div class="col-sm-8">
                <input class="col-xs-12 col-sm-10" type="text" id="form-field-company" placeholder="Company" value="<?=$company?>" name="company" >
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-4 control-label no-padding-right" for="form-field-homepage">Homepage</label>

              <div class="col-sm-8">
                <input class="col-xs-12 col-sm-10" type="text" id="form-field-homepage" placeholder="Homepage" value="<?=$homepage?>" name="homepage" >
              </div>
            </div>
              
            <div class="form-group">
              <label class="col-sm-4 control-label no-padding-right" for="form-field-first_name">First Name</label>

              <div class="col-sm-8">
                <input class="col-xs-12 col-sm-10" type="text" id="form-field-first_name" placeholder="First Name" value="<?=$first_name?>" name="first_name" >
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-4 control-label no-padding-right" for="form-field-middle_name">Middle Name</label>

              <div class="col-sm-8">
                <input class="col-xs-12 col-sm-10" type="text" id="form-field-middle_name" placeholder="Middle Name" value="<?=$firstmiddle_name_name?>" name="middle_name" >
              </div>
            </div>

            <div class="space-4"></div>

            <div class="form-group">
              <label class="col-sm-4 control-label no-padding-right" for="form-field-first">Last Name</label>

              <div class="col-sm-8">
                <input class="col-xs-12 col-sm-10" type="text" id="form-field-last" placeholder="Last Name" value="<?=$last_name?>" name="last_name">
              </div>
            </div>
          </div>
        </div>

        <hr>


        <div class="space-4"></div>


        <div class="form-group">
          <label class="col-sm-3 control-label no-padding-right" for="form-field-comment">Comment</label>

          <div class="col-sm-9">
            <textarea id="form-field-comment" name="comment"><?=$comment?></textarea>
          </div>
        </div>

        <div class="space-4"></div>

        <div class="form-group">
          <label class="col-sm-3 control-label no-padding-right" for="form-field-4">Is Pending</label>

          <div class="col-sm-9">
              <select name="is_pending" id="form-field-4">
                <option <?=$is_pending=='0' ? 'selected="selected"' : ''?> value="0">No</option>
                <option <?=$is_pending=='1' ? 'selected="selected"' : ''?> value="1">Yes</option>
              </select>
          </div>
        </div>

        <div class="space-4"></div>

        <div class="form-group">
          <label class="col-sm-3 control-label no-padding-right" for="form-field-5">Is Active</label>

          <div class="col-sm-9">
              <select name="is_active" id="form-field-5">
                <option <?=$is_active=='1' ? 'selected="selected"' : ''?> value="1">Yes</option>
                <option <?=$is_active=='0' ? 'selected="selected"' : ''?> value="0">No</option>
              </select>
          </div>
        </div>



      </div>

    @view::saProfile_table
    @view::saProfile_dbForm

        <?php foreach($other_tabs as $tab) { ?>
            <div id="<?= $tab['id'] ?>" class="tab-pane">
                <div class="form-group">
                    <h4 class="header blue bolder smaller"><?= $tab['name'] ?></h4>
                </div>
                <?= $tab['html'] ?>
            </div>
        <?php } ?>
    </div>
  </div>

    <div class="clearfix form-actions">
        <div class="col-md-offset-3 col-md-9">
            <button class="btn btn-info" type="submit">
                <i class="fa fa-save bigger-110"></i>
                Save
            </button>

            &nbsp; &nbsp;
            <button class="btn" type="reset">
                <i class="fa fa-undo bigger-110"></i>
                Reset
            </button>
        </div>
    </div>
    <input type="hidden" id="form-field-pass1" name="sa_member_id" value="<?=$sa_member_id?>">
</form>
</div>
</div>
@show