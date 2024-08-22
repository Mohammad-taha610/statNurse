@extends('vueless_master')
@section('site-container')
<form class="form-horizontal" role="form" method="post" action="@url('member_sa_addgrouptomember_save', {'id':$memberId})">

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-3"> Group </label>
		<div class="col-sm-9">
			<select name="group_id" id="form-field-3" class="col-xs-10 col-sm-5">
				<?php foreach($groups as $group) { ?>
				<option value="<?=$group->id?>"><?=$group->name?></option>
				<?php } ?>
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