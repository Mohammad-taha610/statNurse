<?php
if (is_array($dbform))
{
	foreach( $dbform as $form )
	{


		if (empty($singleTable['noDataMessage']))
			$singleTable['noDataMessage'] = 'No data is currently available.'
		?>
		<div id="<?=$form['tabid']?>" class="tab-pane">
			<div class="space-10"></div>
			<h4 class="header primary--text bolder smaller"><?=$form['title']?></h4>
			<div class="row">
				<div class="col-xs-12">
					<?php
					$hasItems = false;
					foreach($form['columns'] as $column=>$cinfo )
					{
						if (in_array( $column, $form['exclude'] )) continue;
						$display = $column;
						$display = ucwords(str_replace('_', ' ', $display));
						$hasItems = true;
						?>
						<div class="form-group">
			              <label class="col-sm-2 control-label no-padding-right" for="form-field-<?=$column?>"><?=$display?></label>

			              <div class="col-sm-10">
			                <input class="col-xs-12 col-sm-10" type="text" id="form-field-<?=$column?>" placeholder="<?=$display?>" value="<?=$$column?>" name="<?=$column?>" >
			              </div>
			            </div>
						<?php
					}
					if (!$hasItems)
					{
						echo 'There are no miscellaneous items to edit.';
					}
					?>
				</div><!-- /span -->
			</div><!-- /row -->
		</div>
		<?php

		//echo '<pre>'.print_r($form, true).'</pre>';
	}
}
?>