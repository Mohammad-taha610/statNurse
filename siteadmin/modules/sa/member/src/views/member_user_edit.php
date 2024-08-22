@extends('master')
<?php
/**
 * @var string $postRoute
 * @var string $first_name
 * @var string $last_name
 * @var string $username
 * @var saMemberEmail[] $emails
 * @var string $email_new
 * @var boolean $is_primary
 * @var boolean $is_active
 * @var int[] $groups
 */

use sacore\application\app;
use sa\member\saMemberEmail;
use sacore\utilities\url;

?>

@asset::/member/profile/css/stylesheet.css

@view::_member_profile_header_nav


<div class="profile-users-edit">
    <?php if($usernameId == 0){?>
        <h1>Create User</h1>
    <?php }else{?>
        <h1>Manage Users</h1>
    <?php }?>


    <div class="form-group text-right">
        <a href="@url('member_users')" class="btn btn-primary">My Users</a>
    </div>

    <form role="form" method="post" action="<?= $postRoute ?>">
        <div class="row">
            <div class="col-md-6">
                <!-- first name -->
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" value="<?= $first_name ?>" class="form-control"
                           title="First Name">
                </div>
                <!-- end first name -->
            </div>
            <div class="col-md-6">
                <!-- last name -->
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" value="<?= $last_name ?>" class="form-control"
                           title="Last Name">
                </div>
                <!-- end last name -->
            </div>
        </div>

        <!-- username -->
        <div class="form-group col-md-12">
            <label for="username"> Username</label>
            <input type="text" name="username" value="<?= $username ?>" placeholder="Username" class="form-control"/>
        </div>
        <!-- end username -->

        <!-- password -->
        <div class="form-group col-md-12">
            <label for="password">Password</label>
            <?php if($usernameId != 0){?>
            <small>To retain the old password, leave this field blank.</small>
            <?php }?>
            <input type="password" name="password" class="form-control" title="Password"/>
        </div>
        <!-- end password -->

        <!-- email -->
        <div class="form-group col-md-12">
            <label for="email"> Email</label>

            <select name="email" id="email" class="form-control">
                <option value="">--None--</option>
                <?php foreach ($emails as $userEmail) { ?>
                    <option <?= $email["id"] == $userEmail["id"] ? 'selected="selected"' : '' ?> value="<?= $userEmail['id']; ?>">
                        <?= $userEmail['email']; ?>
                    </option>
                <?php } ?>
                <option <?= $email == 'add' ? 'selected="selected"' : '' ?> value="add">Add New Email</option>
            </select>
        </div>
        <!-- end email -->

        <!-- new email -->
        <div class="form-group col-md-12 <?= $email == 'add' ? '' : 'saHidden' ?>" id="email_new_group" style="display: none">
            <label for="email_new">or use a new email</label>

            <input type="text" name="email_new" id="email_new" value="<?= $email_new ?>"
                   placeholder="New Email Address" class="form-control" />
        </div>
        <!-- end new email -->

        <!-- is active -->
        <div class="form-group col-md-12">
            <label for="is_active">

                <input type="checkbox" name="is_active" value="1" title="Activate" <?= $is_active ? 'checked' : ($usernameId == 0)?'checked':'' ?>> Activate this user
            </label>
        </div>
        <!-- end is active -->

        <?php if (app::get()->getConfiguration()->get('member_groups') && app::get()->getConfiguration()->get('member_groups')->getValue() == 'user') { ?>

            <div class="form-group col-md-12">
                <label class="col-sm-3 control-label no-padding-right" for="form-field-4"> Groups </label>

                <div class="col-sm-9">
                    <select name="in_groups[]" id="form-field-4" multiple class="col-xs-10 col-sm-5"
                            style="height: 200px">
                        <?php foreach ($groups as $group) { ?>
                            <option <?= in_array($group->id, $in_groups) ? 'selected' : '' ?>
                                value="<?= $group->id ?>"><?= $group->name ?></option>
                        <?php } ?>
                    </select>
                    <span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
                </div>
            </div>

            <?php
        }
        ?>

        <div class="form-actions">
            <div class="col-md-offset-3 col-md-9">
                <button class="btn btn-info" type="submit">
                    <i class="fa fa-save bigger-110"></i>Save
                </button>

                <button class="btn" type="reset">
                    <i class="fa fa-undo bigger-110"></i>
                    Reset
                </button>
            </div>
        </div>

    </form>
</div>
<script>
    $('#email').change( function () {

        if($(this).val() == 'add'){
            $('#email_new_group').show();
        }else{
            $('#email_new_group').hide();
        }
    });
</script>

@view::_member_profile_footer
@show