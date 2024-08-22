@extends('master')
@section('site-container')

@asset::/member/profile/css/stylesheet.css

@view::_member_profile_header_nav


<div class="profile-edit">
    <h1>Profile</h1>
    <form method="POST" action="<?= $postRoute ?>" enctype="multipart/form-data">

        <div class="row">
            <div class="col-sm-6">
                <!-- avatar -->
                <div id="profile-image-dropzone"
                     class="image-uploader"
                     data-upload-route="@url('member_profile_avatar_upload')">

                    <label class="image-preview-container" for="avatar" data-action="change-profile-photo">
                        <span class="image-wrapper">
                            <!-- loading icon -->
                            <span class="loading-icon">
                                <i class="fa fa-spinner fa-spin"></i>
                            </span>
                            <!-- end loading icon -->

                            <!-- image -->
                            <span class="file-name">
                                <?php if (!empty($avatar)) { ?>
                                    <img src="@url('member_profile_avatar')?1=<?=time()?>" class="profile-photo" />
                                <?php } else { ?>
                                    <i class="fa fa-photo profile-photo"></i>
                                <?php } ?>
                            </span>
                            <!-- end image -->
                        </span>
                    </label>

                    <div class="action-buttons">
                        <button class="btn btn-primary" data-action="change-profile-photo">Change Photo</button>
                    </div>

                    <!-- file input -->
                    <input id="fileupload" type="file" name="avatar" class="hidden"
                           value="<?= (!empty($image)) ? $image->getId() : '' ?>">

                </div>
            </div>

            <!-- end avatar -->
        </div>



<!-- first name -->
<div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" name="first_name" value="<?= $first_name ?>" title="First Name" class="form-control">
</div>
<!-- end first name -->

<!-- middle name -->
<div class="form-group">
    <label for="middle_name">Middle name</label>
    <input type="text" name="middle_name" value="<?= $middle_name ?>" title="Middle name" class="form-control">
</div>
<!-- end middle name -->

<!-- last name -->
<div class="form-group">
    <label for="last_name">Last name</label>
    <input type="text" name="last_name" value="<?= $last_name ?>" title="Last name" class="form-control">
</div>
<!-- end last name -->

<!-- company -->
<div class="form-group">
    <label for="company">Company</label>
    <input type="text" name="company" value="<?= $company ?>" title="Company" class="form-control">
</div>
<!-- end company -->

<!-- company -->
<div class="form-group">
    <label for="homepage">Homepage</label>
    <input type="text" name="homepage" value="<?= $homepage ?>" title="Homepage" class="form-control">
</div>
<!-- end company -->
<?php
$app = \sacore\application\app::get();
$config = $app->getConfiguration();
if(!$config->get('member_two_factor_require', true)->getValue()){ ?>
<!-- require two factor -->
<div class="form-group">
    <label><input type="checkbox" name="require_two_factor" <?= $require_two_factor ? 'checked' : ''; ?>>Require Two Factor</label>
</div>
<!-- end require two factor -->
<?php } ?>

<div class="clearfix form-actions">
    <div class="col-md-offset-3 col-md-9">
        <button class="btn btn-info" type="submit">
            <i class="fa fa-ok bigger-110"></i>
            Save
        </button>

        <button class="btn" type="reset">
            <i class="fa fa-undo bigger-110"></i>
            Reset
        </button>
    </div>
</div>
<input type="hidden" id="form-field-pass1" name="sa_member_id" value="<?= $sa_member_id ?>">

</form>
</div>

@view::_member_profile_footer
@show