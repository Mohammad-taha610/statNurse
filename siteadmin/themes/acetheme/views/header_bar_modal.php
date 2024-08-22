<div class="navbar navbar-default" id="navbar">
<script>
	try{ace.settings.check('navbar' , 'fixed')}catch(e){}

	$(document).ready( function() {
		$('#close_modal').click( function() {
			window.parent.closeFrameModal();
		})
	})

</script>

	<div class="navbar-container" id="navbar-container">
		<div class="navbar-header pull-left">
			<a href="<?= \sacore\application\app::get()->getConfiguration()->get('site_url')->getValue() ?>/siteadmin" class="navbar-brand">
				<small>
					<?php

                    $siteadminImageId = \sacore\application\app::get()->getConfiguration()->get('siteadmin_image_id')->getValue();

                    /** @var \sa\saFiles\saImage $image */
                    $image = \sacore\application\ioc::get('saImage', $siteadminImageId);
                    if ($image) {
                        $siteadmin_image_path = \sacore\utilities\url::make('files_browser_view_file', $image->getFolder(), $image->getFilename());
                        ?>
                            <img src="<?=$siteadmin_image_path?>" class="img-responsive"/>
                        <?php
                    } else {
                        $siteadmin_image_path = $assetfolder.'/images/sa.png';
                        ?>
                        <img src="<?=$siteadmin_image_path?>" height="45px" />
                        Site Administrator
                        <?php
                    }

					?>

				</small>
			</a><!-- /.brand -->
		</div><!-- /.navbar-header -->

		<div class="navbar-header pull-right" role="navigation">
			<ul class="nav ace-nav">

				<li class="light-blue font20" >
					<btn class="btn" id="close_modal" type="button">Close <i class="fa fa-times"></i></btn>
				</li>

			</ul><!-- /.ace-nav -->
		</div><!-- /.navbar-header -->
	</div><!-- /.container -->
</div>

<div class="main-container saMarginTop10" id="main-container">
<script>
	try{ace.settings.check('main-container' , 'fixed')}catch(e){}
</script>
