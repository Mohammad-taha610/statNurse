@extends('master')

@section('site-container')

<form class="form-horizontal" role="form" method="post" action="<?=$postRoute?>">
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-1"> Street</label>

		<div class="col-sm-9">
			<input type="text" name="street_one" id="form-field-1" value="<?=$street_one?>" placeholder="Street" class="col-xs-10 col-sm-5">
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2"> Unit, etc.</label>

		<div class="col-sm-9">
			<input type="text" name="street_two" id="form-field-2" value="<?=$street_two?>" placeholder="Unit, etc." class="col-xs-10 col-sm-5">
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-3"> City</label>

		<div class="col-sm-9">
			<input type="text" name="city" id="form-field-3" value="<?=$city?>" placeholder="City" class="col-xs-10 col-sm-5">
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-3"> Country</label>

		<div class="col-sm-9">
			<select name="country" id="country" class="col-xs-10 col-sm-5">
                <option value="">Please select a country...</option>
				<?php
                    foreach($countries as $countryitem) {
				?>
					<option <?=$countryitem==$country ? 'selected="selected"' : ''?> value="<?=$countryitem->getId()?>"><?=$countryitem->getName()?></option>
					<?php
				}
				?>
			</select>
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-3"> State/Province</label>

		<div class="col-sm-9">
			<select name="state" id="state" class="col-xs-10 col-sm-5" data-previous_selection="<?= $state ? $state->getId() : '' ?>">
                <option value="">Please select a state/province...</option>
				<?php
                if ( !empty($states) ) {
                    foreach($states as $stateitem) {
						?>
						<option value="<?=$stateitem->getId()?>"><?=$stateitem->getName()?></option>
						<?php
                    }
                }
				?>
			</select>
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-4"> Postal Code</label>

		<div class="col-sm-9">
			<input type="text" name="postal_code" id="form-field-4" value="<?=$postal_code?>" placeholder="Postal Code" class="col-xs-10 col-sm-5">
		</div>
	</div>

	<div class="space-4"></div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-5"> Type </label>

		<div class="col-sm-9">
			<select name="type" id="form-field-5" class="col-xs-10 col-sm-5">
				<option <?= $type=='personal' ? 'selected="selected"' : ''?> value="personal">Personal</option>
				<option <?= $type=='work' ? 'selected="selected"' : ''?> value="work">Work</option>
				<option <?= $type=='secondary' ? 'selected="selected"' : ''?> value="secondary">Secondary</option>
				<option <?= $type=='other' ? 'selected="selected"' : ''?> value="other">Other</option>
                
                <?php if($otherAddressTypes) { ?>
                    <?php foreach($otherAddressTypes as $addressType) { ?>
                        <option <?= $type == $addressType ? 'selected="selected"' : '' ?>><?= ucfirst($addressType) ?></option>
                    <?php } ?>
                <?php } ?>
			</select>
			<span class="help-inline col-xs-12 col-sm-7">
				<span class="middle"><!-- Inline help text --></span>
			</span>
		</div>
	</div>

	<div class="space-4"></div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="latitude"> Latitude</label>

		<div class="col-sm-9">
			<input type="text" name="latitude" id="form-field-4" value="<?=$latitude?>" placeholder="Latitude" class="col-xs-10 col-sm-5">
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="longitude"> Longitude</label>

		<div class="col-sm-9">
			<input type="text" name="longitude" id="form-field-4" value="<?=$longitude?>" placeholder="Longitude" class="col-xs-10 col-sm-5">
		</div>
	</div>

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

@asset::/member/profile/js/profile.js
@show