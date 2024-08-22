@extends('master')
@section('site-container')
<?php
if (is_array($dbform))
{
	foreach( $dbform as $form )
	{

		if ($form['form'])
		{
			if (is_array($form['saveRoute']))
			{
				$params = isset($form['saveRoute']['params']) ? $form['saveRoute']['params'] : array();
				if (isset($form['saveRoute']['routeId']))
					$routeid = $form['saveRoute']['routeId'];
				elseif (isset($form['saveRoute']['routeid']))
					$routeid = $form['saveRoute']['routeid'];
			}
			elseif( isset($form['saveRoute']) )
			{
				$routeid = $form['saveRoute'];
				$params = array($id);
			}
			elseif( isset($form['saveRouteId']) )
			{
				$routeid = $form['saveRouteId'];
				$params = [];

                foreach($form['saveRouteParams'] as $param) {
                    $params[$param] = $$param;
                }
			}

            $formActionLink = \sacore\application\app::get()->getRouter()->generate($routeid, $params);
			?>
			<form class="form-horizontal" method="POST" action="<?=$formActionLink?>">
			<?php
		}

		if (!isset($form['useInputFields']))
			$form['useInputFields'] = true;

		?>

		<div class="row">
			<div class="col-xs-12">
				<?php
				$hasItems = false;
				foreach($form['columns'] as $column=>$cinfo )
				{
					if (in_array( $column, $form['exclude'] ) || $cinfo['autoIncrement']) continue;
					$hasItems = true;

					if ( !empty( $form['typeOverrides'][$column] ) ) {
						$cinfo['type'] = $form['typeOverrides'][$column];
					}

					if ( !empty( $form['overrides'][$column] ) ) {
						$cinfo = $form['overrides'][$column];
					}


					if ($form['useInputFields'] && $cinfo['type']=='boolean')
						booleanField($column, $$column, $cinfo);
					elseif ($form['useInputFields'] && $cinfo['type']=='select')
						selectField($column, $$column, $cinfo);
					elseif ($form['useInputFields'] && $cinfo['type'] == 'text')
                        textareaField($column, $$column, $cinfo);
					elseif ($form['useInputFields'] && $cinfo['type']=='boolean')
						divBooleanField($column, $$column, $cinfo);
					elseif ($form['useInputFields'] && $cinfo['type']=='div')
						divField($column, $$column, $cinfo);
					elseif ($form['useInputFields'])
						textField($column, $$column, $cinfo);
					elseif ($cinfo['type']=='iframe')
						frameField($column, $$column, $cinfo);
					elseif ($cinfo['type']=='pre')
						preField($column, $$column, $cinfo);
					else
						divField($column, $$column, $cinfo);
				}
				if (!$hasItems)
				{
					echo 'There are no fields to edit.';
				}
				?>
			</div><!-- /span -->
		</div><!-- /row -->

		<?php
		if ($form['image_upload']) {
			?>

			<div class="form-group">
				<label class="col-sm-2 control-label no-padding-right" for="form-field-color_secondary">Logo: </label>

				<div class="col-sm-9">
					<a href="#" class="btn select-image">Select Image</a>

					<div class="image-holder">
						<?php if ($image_path) { ?><img src="<?= $image_path ?>" width="200" /><br/><?php } ?>
						<input type="hidden" name="image_id" value="<?= $image_id ?>"/>
					</div>
				</div>
			</div>

			<script>
				function fileBrowserSelectCallBack(object) {
					$('.image-holder').html('<img src="' + object.filepath + '" width="200" /><br /><input type="hidden" name="image_id" value="' + object.id + '" />');
				}

				$('.select-image').click(function (e) {

					e.preventDefault();
					window.open("<?=\sacore\utilities\url::make('files_browse')?>?return=object&folder=customization_images", "browser", "location=1,status=0,scrollbars=0,width=600,height=700");

				});
			</script>

			<?php
		}

		if ($form['form'])
		{
			?>
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
			</form>
			<?php
		}

	}
}

function textField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	?>
	<div class="form-group">
      <label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
      <div class="col-sm-10">
        <input class="col-xs-12 col-sm-10" type="text" id="form-field-<?=$column?>" maxlength="<?=$cinfo['length']?>" placeholder="<?=$display?>" value="<?=$value?>" name="<?=$column?>" >
      </div>
    </div>
	<?php
}

function textareaField($column, $value, $cinfo) {
    $display = $column;
    $display = ucwords(str_replace('_', ' ', $display));
?>
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
        <div class="col-sm-10">
            <textarea id="form-field-<?=$column?>" name="<?= $column ?>" id="" rows="6" maxlength="<?=$cinfo['length']?>" class="col-xs-12 col-sm-10"><?= $value ?></textarea>
        </div>
    </div>
<?php
}

function booleanField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	?>
	<div class="form-group">
      <label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
      <div class="col-sm-10">
        <select class="col-xs-12 col-sm-10" id="form-field-<?=$column?>"  name="<?=$column?>">
			<option value="0" <?=$value=='0' ? 'selected="selected"' : ''?>>No</option>
			<option value="1" <?=$value=='1' ? 'selected="selected"' : ''?>>Yes</option>
        </select>
      </div>
    </div>
	<?php
}

function selectField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	?>
	<div class="form-group">
		<label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
		<div class="col-sm-10">
			<select class="col-xs-12 col-sm-10" id="form-field-<?=$column?>"  name="<?=$column?>">
				<?php

				if (is_array($cinfo['values'])) {
					foreach ($cinfo['values'] as $valueinfo) {

						?>
						<option value="<?= $valueinfo['value'] ?>" <?= $value == $valueinfo['value'] ? 'selected="selected"' : '' ?>><?= $valueinfo['name'] ?></option>
						<?php
					}
				}
				?>
			</select>
		</div>
	</div>
	<?php
}

function divBooleanField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	if ( $value )
		$value = 'Yes';
	else
		$value = 'No';

	?>
	<div class="form-group">
      <label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
      <div class="col-sm-10">
            <pre class="col-xs-12 col-sm-10"><?=$value?></pre>
      </div>
    </div>
	<?php
}

function divField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	?>
	<div class="form-group">
      <label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
      <div class="col-sm-10">
            <pre class="col-xs-12 col-sm-10"><?=$value?></pre>
      </div>
    </div>
	<?php
}

function frameField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	?>
	<div class="form-group">
      <label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
      <div class="col-sm-10">
            <div class="col-xs-12 col-sm-10"><?=$value?></div>
      </div>
    </div>
	<?php
}

function preField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	?>
	<div class="form-group">
		<label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
		<div class="col-sm-10">
			<pre class="col-xs-12 col-sm-10"><?=htmlspecialchars($value)?></pre>
		</div>
	</div>
	<?php
}

function divSelectField($column, $value, $cinfo)
{
	$display = $column;
	$display = ucwords(str_replace('_', ' ', $display));

	if ( $value )
		$value = 'Yes';
	else
		$value = 'No';

	?>
	<div class="form-group">
		<label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?>: </label>
		<div class="col-sm-10">
			<pre class="col-xs-12 col-sm-10"><?=$value?></pre>
		</div>
	</div>
	<?php
}?>
@show

