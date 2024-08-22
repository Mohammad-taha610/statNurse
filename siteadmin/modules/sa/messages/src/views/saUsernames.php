@extends('master')
@section('site-container')
<?php

use sacore\application\app;

?>
<form class="form-horizontal" role="form" method="post" action="<?=$postRoute?>">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">First Name</label>

        <div class="col-sm-9">
            <input type="text" name="first_name" id="form-field-1" value="<?=$first_name?>" placeholder="First Name" class="col-xs-10 col-sm-5" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">Last Name</label>

        <div class="col-sm-9">
            <input type="text" name="last_name" id="form-field-1" value="<?=$last_name?>" placeholder="Last Name" class="col-xs-10 col-sm-5" />
        </div>
    </div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Username</label>

		<div class="col-sm-9">
			<input type="text" name="username" id="form-field-1" value="<?=$username?>" placeholder="Username" class="col-xs-10 col-sm-5" />
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"> Password </label>

		<div class="col-sm-9">
			<input type="password" name="password" id="form-field-2"  value="<?=$password?>" placeholder="To retain the old password, leave this field blank." class="col-xs-10 col-sm-5" />
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

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-4"> Is Active </label>

		<div class="col-sm-9">
			<select name="is_active" id="form-field-4" class="col-xs-10 col-sm-5">
				<option <?=$is_active=='1' ? 'selected="selected"' : ''?> value="1">Yes</option>
				<option <?=$is_active=='0' ? 'selected="selected"' : ''?> value="0">No</option>
			</select>
			<span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
		</div>
	</div>

	<div class="space-4"></div>

    <?php if (app::get()->getConfiguration()->get('member_groups')->getValue()=='user') { ?>

        <div class="form-group">
            <label class="col-sm-3 control-label no-padding-right" for="form-field-4"> Groups </label>

            <div class="col-sm-9">
                <select name="in_groups[]" id="form-field-4" multiple class="col-xs-10 col-sm-5" style="height: 200px">
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