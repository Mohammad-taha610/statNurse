@extends('master')
@section('site-container')
@asset::/siteadmin/developer/js/module_creator.js

<script>
	var types = <?=json_encode($types); ?>
</script>

<form role="form" method="post" action="@url('sa_developer_create_module_post')">

	<label class="control-label bolder blue">Module Information</label>

	<div class="row">

		<div class="col-md-6">

			<div class="form-group">
				<label class="control-label no-padding-right" for="form-field-1">Name</label>
				<input type="text" id="form-field-1" placeholder="Module Name" name="name" value="<?=$name?>" class="form-control">
			</div>

			<div class="form-group">
				<label class="control-label no-padding-right" for="form-field-1">Namespace</label>
				<input type="text" id="form-field-1" placeholder="Name Space" name="namespace" value="<?=$namespace?>"  class="form-control">
				<span class="help-block">Format: "vendor\package" Example: "sa\member"</span>
			</div>

		</div>

	</div>

	<hr />

	<label class="control-label bolder blue">Entity Information</label>

	<div class="row">

		<div class="col-md-3">

			<div class="form-group">
				<label class="control-label no-padding-right" for="form-field-1">Name</label>
				<input type="text" id="form-field-1" placeholder="Entity Name" name="entity_name" value="<?=$entity_name?>" class="form-control">
			</div>

		</div>
		<div class="col-md-3">

			<div class="form-group">
				<label class="control-label no-padding-right" for="form-field-1">Table Name</label>
				<input type="text" id="form-field-1" placeholder="Table Name" name="table_name" value="<?=$table_name?>"  class="form-control">
			</div>

		</div>

	</div>

	<div class="row">

		<div class="col-md-6 text-right">

			<button type="button" class="btn btn-primary add-property"><i class="fa fa-plus-circle"></i> Add Property</button>


		</div>

	</div>

	<div class="row">
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label no-padding-right">Name</label>
				<input type="text" placeholder="Property Name" value="id" class="form-control" disabled>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<label class="control-label no-padding-right">Type</label>
				<select class="form-control" disabled>
					<option value="array">id</option>
				</select>
			</div>
		</div>
	</div>

	<div class="row">

		<div class="col-md-6 property-info">

		</div>

	</div>

	<div class="space-4"></div>


	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
			<button class="btn btn-info" type="submit">
				<i class="fa fa-save bigger-110"></i>
				Create
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
<?php
if (is_array($entity_property)) {
    foreach ($entity_property as $i => $e) {
        ?>
		addProperty(<?=$i?>, '<?=$e['name']?>', '<?=$e['type']?>')
		<?php
    }
}
	?>

</script>
@show