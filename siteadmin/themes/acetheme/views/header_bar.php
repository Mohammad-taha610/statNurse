<?php
	$backgroundColor = '#438eb9';
	if(\sacore\application\app::getIsInSafeMode())
		$backgroundColor = '#FF0000';
	elseif( \sacore\application\app::get()->getConfiguration()->get('siteadmin_header_bg')->getValue())
		$backgroundColor = \sacore\application\app::get()->getConfiguration()->get('siteadmin_header_bg')->getValue();
?>
<div class="navbar navbar-default" id="navbar" style="background: <?=$backgroundColor ?>;">
<script>
	try{ace.settings.check('navbar' , 'fixed')}catch(e){}
</script>

	<div class="navbar-container" id="navbar-container">
		<div class="navbar-header pull-left">
			<a href="<?=\sacore\application\app::get()->getConfiguration()->get('site_url')->getValue() ?>/siteadmin" class="navbar-brand">
				<small>
					<?php

					if (!empty(\sacore\application\app::get()->getConfiguration()->get('siteadmin_image_id')->getValue())) {
						/** @var \sa\safiles\saImage $image */
						$image = \sacore\application\ioc::get('saImage', \sacore\application\app::get()->getConfiguration()->get('siteadmin_image_id')->getValue());
						if ($image) {
							$siteadmin_image_path = \sacore\utilities\url::make('files_browser_view_file', $image->getFolder(), $image->getFilename());
							?>
								<img src="<?=$siteadmin_image_path?>" class="img-responsive sa-logo"/>

							<?php
						}
						else
						{
							$siteadmin_image_path = $assetfolder.'/images/sa.png';
							?>
							<img src="<?=$siteadmin_image_path?>" height="45px" />
							Site Administrator
							<?php
						}
					}
					else{
						$siteadmin_image_path = $assetfolder.'/images/sa.png';
						?>
						<img src="<?=$siteadmin_image_path?>" height="45px" />
						Site Administrator
						<?php
					}
					?>



				</small>
			</a><!-- /.brand -->

			<?php

			if (\sacore\application\app::getIsInSafeMode()) {
				echo '<a class="safe-mode-link navbar-brand" href="'.\sacore\utilities\url::make('system_safemode').'"><small> | SAFE MODE</small></a>';
			}
			?>

		</div><!-- /.navbar-header -->

		<div class="navbar-header pull-right " role="navigation">
			<ul class="nav ace-nav sa-header-nav">
                <li class="light-blue navbar-header-hide-small">
                    <a href="<?php echo \sacore\application\app::get()->getConfiguration()->get('site_url')->getValue(); ?>" target="_blank" title="Open Web Site in New Tab">
                        <i class="fa fa-link fa"></i>
                    </a>
                </li>
                
				<?= \sacore\application\modRequest::request('sa.header') ?>
			</ul><!-- /.ace-nav -->
		</div><!-- /.navbar-header -->
	</div><!-- /.container -->
</div>

<div class="main-container" id="main-container">



<script>
	try{ace.settings.check('main-container' , 'fixed')}catch(e){}

	$(document).ready(function() {
	    $('#navbar-header-file-browser').click(function() {
            window.open("/siteadmin/files/browse?uploadOnly=1", "browser", "location=1,status=0,scrollbars=0,width=600,height=700");
        });
    });
</script>