@extends('master')

@section('site-container')
<form class="form-horizontal" role="form" method="post" action="<?=$postRoute?>">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Email</label>

        <div class="col-sm-9">
            <input type="text" name="email" id="form-field-1" value="<?=$email?>" placeholder="Email" class="col-xs-10 col-sm-5">
        </div>
    </div>

    <div class="space-4"></div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2"> Type </label>

        <div class="col-sm-9">
            <select name="type" id="form-field-2" class="col-xs-10 col-sm-5">
                <option <?=$type=='personal' ? 'selected="selected"' : ''?> value="personal">Personal</option>
                <option <?=$type=='work' ? 'selected="selected"' : ''?> value="work">Work</option>
                <option <?=$type=='secondary' ? 'selected="selected"' : ''?> value="secondary">Secondary</option>
                <option <?=$type=='other' ? 'selected="selected"' : ''?> value="other">Other</option>
            </select>
            <span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
        </div>
    </div>

    <div class="space-4"></div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-3"> Is Primary </label>

        <div class="col-sm-9">
            <select name="is_primary" id="form-field-3" class="col-xs-10 col-sm-5">
                <option <?=$is_primary=='1' ? 'selected="selected"' : ''?> value="1">Yes</option>
                <option <?=$is_primary=='0' ? 'selected="selected"' : ''?> value="0">No</option>
            </select>
            <span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
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
@show