@extends('master')

@section('site-container')
<form class="form-horizontal" role="form" method="post" action="@url('member_sa_group_save',{'id':$groupId})">
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Name</label>

		<div class="col-sm-9">
			<input type="text" name="name" id="form-field-1" value="<?=$name?>" placeholder="Name" class="col-xs-10 col-sm-5">
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"> Description</label>

		<div class="col-sm-9">
			<textarea type="text" name="description" rows="10" id="form-field-2" placeholder="Description" class="col-xs-10 col-sm-5"><?=$description?></textarea>
		</div>
	</div>

	<div class="space-4"></div>
    
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="is_default">Is Default</label>
        <div class="col-sm-9">
            <select id="is_Default" name="is_default" class="col-xs-10 col-sm-5 is_default">
                <option value="1" <?= $is_default ? 'selected' : '' ?>>Yes</option>
                <option value="0" <?= !$is_default ? 'selected' : '' ?>>No</option>
            </select>
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