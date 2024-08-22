@extends('vueless_master')
@section('site-container')
<?php

use sacore\application\app;

?>

<?php // member_type conditionally divides inputs it so creating a nurse doesn't see provider fields, separates headers
    if ($member_type == "Provider") {
?>
    <h3 style="text-align:left;">Editing Provider User's Username / Password</h3>
<?php } else if($member_type == "Nurse") { ?>
    <h3 style="text-align:left;">Editing Nurse Username / Password</h3>
<?php } else{ ?>
    <h3 style="text-align:left;">Creating Username / Password</h3>
<?php } ?>

<form class="form-horizontal" role="form" method="post" action="<?=$postRoute?>">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">First Name</label>

        <div class="col-sm-9">
            <input type="text" name="first_name" id="form-field-1" value="<?=$first_name?>" placeholder="First Name" class="col-xs-10 col-sm-5" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Last Name</label>

        <div class="col-sm-9">
            <input type="text" name="last_name" id="form-field-2" value="<?=$last_name?>" placeholder="Last Name" class="col-xs-10 col-sm-5" />
        </div>
    </div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-3"> Username</label>

		<div class="col-sm-9">
			<input type="text" name="username" id="form-field-3" value="<?=$username?>" placeholder="Username" class="col-xs-10 col-sm-5" />
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-4"> Password </label>

		<div class="col-sm-9">
			<input type="password" name="password" id="form-field-4"  value="<?=$password?>" placeholder="To retain the old password, leave this field blank." class="col-xs-10 col-sm-5" />
			<span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
		</div>
	</div>

    <div class="space-4"></div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="email"> Email</label>

        <div class="col-sm-9">
            <select name="email" id="email" class="col-xs-10 col-sm-5">
                <?php foreach($emails as $emaill) { ?>
                    <option <?=$email==$emaill['id'] ? 'selected="selected"' : '' ?> value="<?=$emaill['id'];?>"><?=$emaill['email'];?></option>
                <?php } ?>			
                <option value="">--None--</option>
                <option <?=$email=='add' ? 'selected="selected"' : '' ?> value="add">Add New Email</option>
            </select>
			<span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
        </div>
    </div>

    <div class="form-group <?=$email=='add' ? '' : 'saHidden' ?>" id="email_new_group">
        <label class="col-sm-3 control-label no-padding-right" for="email_new"> Email Address </label>

        <div class="col-sm-9">
            <input type="text" name="email_new" id="email_new"  value="<?=$email_new?>" placeholder="New Email Address" class="col-xs-10 col-sm-5" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-5"> Is Active </label>

        <div class="col-sm-9">
            <select name="is_active" id="form-field-5" class="col-xs-10 col-sm-5">
                <option <?=$is_active=='1' ? 'selected="selected"' : ''?> value="1">Yes</option>
                <option <?=$is_active=='0' ? 'selected="selected"' : ''?> value="0">No</option>
            </select>
            <span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
        </div>
    </div>

	<div class="space-4"></div>

<?php // member_type conditionally divides inputs it so creating a nurse doesn't see provider fields 
if ($member_type == "Provider") {
    ?>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-6"> User Type </label>

        <div class="col-sm-9">
            <select name="user_type" id="form-field-6" class="col-xs-10 col-sm-5">
                <option value="">--None--</option>
                <option <?=$user_type == 'Admin' ? 'selected="selected"' : ''?> value="Admin">Admin</option>
                <option <?=$user_type == 'Scheduler' ? 'selected="selected"' : ''?> value="Scheduler">Scheduler</option>
            </select>
            <span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
        </div>
    </div>

    <div class="space-4"></div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-7"> Allowed to add Bonus/Incentive Pay </label>

        <div class="col-sm-9">
            <select name="bonus_allowed" id="form-field-7" class="col-xs-10 col-sm-5">
                <option <?=$bonus_allowed=='1' ? 'selected="selected"' : ''?> value="1">Yes</option>
                <option <?=$bonus_allowed=='0' ? 'selected="selected"' : ''?> value="0">No</option>
            </select>
            <span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
        </div>
    </div>

    <div class="space-4"></div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-8"> Allowed to add Covid Pay </label>

        <div class="col-sm-9">
            <select name="covid_allowed" id="form-field-8" class="col-xs-10 col-sm-5">
                <option <?=$covid_allowed=='1' ? 'selected="selected"' : ''?> value="1">Yes</option>
                <option <?=$covid_allowed=='0' ? 'selected="selected"' : ''?> value="0">No</option>
            </select>
            <span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
        </div>
    </div>

<?php } else {
    // else statement is required to set Nurses who do not get the Is Active select to be set active by default
    ?>
<?php } ?>

    <div class="space-4"></div>

    <?php if (app::get()->getConfiguration()->get('member_groups')->getValue()=='user') { ?>

        <div class="form-group">
            <label class="col-sm-3 control-label no-padding-right" for="form-field-9"> Groups </label>

            <div class="col-sm-9">
                <select name="in_groups[]" id="form-field-9" multiple class="col-xs-10 col-sm-5" style="height: 200px">
                    <?php foreach($groups as $group) { ?>
                        <option <?=in_array($group->id, $in_groups) ? 'selected' : ''?> value="<?=$group->id?>"><?=$group->name?></option>
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


	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit">
				<i class="fa fa-save bigger-110"></i>
				Submit
			</button>

			&nbsp; &nbsp; &nbsp;
			<button class="btn" type="reset">
				<i class="fa fa-undo bigger-110"></i>
				Reset
			</button>
		</div>
	</div>

	
</form>

<script>
    $('#unlocked_span').hide();
    $('#unlocked_account').hide();

    if (<?=$account_locked ? 1 : 0?>) {
        $('#account_locked').show();
        $('#account_label').show();
    }
    else {
        $('#account_locked').hide();
        $('#unlock_label').hide();
    }

    $('#unlock_button').on("click", function(e) {
        e.preventDefault();
        $('#unlocked_span').show();
        $('#unlock_button').hide();
        $('#unlocked_account').val("true")
    })

    $('#email').change(function() {

        if ( $(this).val()=='add' ) {
            $('#email_new_group').show();
        }
        else {
            $('#email_new').val('');
            $('#email_new_group').hide();
        }
    });

</script>
@show