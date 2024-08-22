@extends('master')

@section('site-container')
@asset::/siteadmin/system/css/settings.css
@asset::/siteadmin/system/js/settings.js
<form class="form-horizontal" id="settings-form" method="POST" data-browser="@url('files_browse')">
    <div class="tabbable" id="settings-tabs">
        <ul class="nav nav-tabs padding-16">
			<?php
			$current_module = "";
			$first = true;
			foreach($settings as $module => $modsettings) {

				$mName = preg_replace('#[^a-z0-9A_Z]#', '', $module);
				?>
				<li class="<?=$first ? 'active' : ''?>">
					<a data-toggle="tab" href="#<?php echo $mName; ?>"><i class="blue fa fa-cog bigger-125"></i><?php echo ucwords( str_replace('_', ' ', $module)); ?></a>
				</li>
				<?php
				$first = false;
			}
			?>
        </ul>
        <div class="tab-content profile-edit-tab-content">
			<?php
            $current_module = "";
			$variableCount = 0;
            foreach($settings as $module => $modsettings) {

				$mName = preg_replace('#[^a-z0-9A_Z]#', '', $module);
				$friendly_module_name = ucwords( str_replace(array('_', 'id', 'Id'), ' ', $module));

				?>
				<div id="<?php echo $mName; ?>" class="tab-pane <?=$variableCount==0 ? 'active' : ''?>">
					<div class="form-group">
						<h4 class="header blue bolder smaller"><?php echo $friendly_module_name ?></h4>
					</div>
					<div class="row">
						<?php

						foreach ($modsettings as $k => $v) {

						    if (in_array($k, $hidden))
						        continue;

							$original_setting_name = ucwords(preg_replace('#[^a-z0-9]|id$#i', ' ', $k));
							$setting_name = substr($original_setting_name, 0, 15);

							$imageid = substr(md5(time().rand(0,9999999)), 0, -10);

						?>

						<div class="col-xs-6 col-md-3 col-lg-2 widget-box">
							<div class="widget-header">
								<?php
								if($v['type'] == 'image_uploader') {
									if ($setting_name!=$original_setting_name)
										$setting_name .= '...';
									?>
									<div class="col-md-7 col-xs-7 col-lg-7 ">
										<label for="<?= $k ?>"><?= $setting_name ?></label><br />
										<div class="var_name"><?=$k?></div>
									</div>
									<div class="col-md-5 col-xs-5 col-lg-5 text-right">
										<a href="#" style="margin-top: 6px" data-module="<?= $module ?>" data-container="<?= $imageid ?>" data-input="<?= $k ?>" class="btn btn-xs btn-primary select-image">
											<i class="fa fa-upload"></i>
										</a>

										<?php if ($v['value'])
											{
												?>
												<a href="#" style="margin-top: 6px" data-module="<?= $module ?>"
												   data-container="<?= $imageid ?>" data-input="<?= $k ?>"
												   class="btn btn-xs btn-danger delete-image">
													<i class="fa fa-trash"></i>
												</a>
											<?php
										}
									?>
									</div>
									<?php
								}
								else
								{
									?>
									<div class="col-md-12 col-xs-12 col-lg-12 ">
										<label for="<?= $k ?>"><?= $setting_name ?></label><br />
										<div class="var_name"><?=$k?></div>
									</div>
									<?php
								}
								?>

							</div>
							<div class="widget-main">
								<?php
								if ($v['type'] == 'select' && $v['options']) {
									?>
									<select name="<?=$module?>[<?= $k ?>]">
										<?php
										if (is_array($v['options'])) {
											foreach ($v['options'] as $option) {
												?>
												<option value="<?= $option ?>" <?= ($v['value'] == $option) ? ' selected ' : '' ?>> <?= $option ?></option>
												<?php
											}
										}
										?>
									</select>
									<?php
								}
								elseif ($v['type'] == 'array') {
                                    // make this better in the future
								    ?>
                                    <input type="text" class="form form-control" name="<?=$module?>[<?= $k ?>]" value="<?= implode(';', $v['value']) ?>">
                                    <?php
                                }
								elseif($v['type'] == 'image_uploader') {
									?>
										<div class="text-center">
											<div class="image-holder <?= $imageid ?>">
												<?php if ($v['value']) { ?>
                                                    <img src="<?=\sacore\application\app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id'=>$v['value']])?>'" height="35" /><br/>
                                                    <input type="hidden" name="<?=$module?>[<?= $k ?>]" value="<?= $v['value'] ?>"/>
												<?php } else {
													?>
														No image selected
													<?php
												} ?>
											</div>

										</div>
									<?php

								}
								elseif ($v['type'] == 'boolean') {
									$boolean_is_true = ($v['value'] === 1 || $v['value'] === '1' || $v['value']);

									?>

									<input type="radio" <?= $boolean_is_true ? 'checked' : '' ?> name="<?=$module?>[<?= $k ?>]"
										   value="1"> Yes

									&nbsp; &nbsp; &nbsp;

									<input type="radio" <?= !$boolean_is_true ? 'checked' : '' ?> name="<?=$module?>[<?= $k ?>]"
										   value="0"> No

									<?php
								}
								elseif ($k == 'theme') {
									$themes = scandir(\sacore\application\app::getAppPath() . '/themes');
									?>
									<select name="<?=$module?>[<?= $k ?>]">
										<?php
										foreach ($themes as $theme) {
											if ($theme == '.' || $theme == '..') {
												continue;
											}
											?>
											<option
												value="<?= $theme ?>" <?= $v['value'] == $theme ? ' selected ' : '' ?>><?= $theme ?></option>
											<?php
										}
										?>
									</select>
									<?php
								}
								elseif ($v['type'] == 'password') {
									?>
									<input type="password" class="form form-control" name="<?=$module?>[<?= $k ?>]" value="<?= $v['value'] ?>">
									<?php
								}
								else {
									?>
									<input type="text" class="form form-control" name="<?=$module?>[<?= $k ?>]" value="<?= $v['value'] ?>">
									<?php
								}
								?>
							</div>
						</div>
						<?php

						}

						$variableCount++;
						?>
					</div>
				</div>
				<?php
			}
			?>
        </div>
    </div>

    <div class="clearfix form-actions">
        <div class="col-md-offset-3 col-md-9">
            <button class="btn btn-info" type="submit"><i class="fa fa-save bigger-110"></i>Save</button>
            &nbsp; &nbsp;
            <button class="btn" type="button"  onclick="history.go(-1)"><i class="fa fa-undo bigger-110"></i>Cancel</button>
        </div>
    </div>
</form>

<script type="text/javascript">

	$(document).ready( function() {

		var hash = window.location.hash.substring(1);
		if (hash!='')
			$('a[href="#'+hash+'"]').click();

		var querystring = getUrlVars();
		if ( querystring['tab'] !='' )
			$('a[href="#'+querystring['tab']+'"]').click();

	});

	function getUrlVars()
	{
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}
</script>
@show