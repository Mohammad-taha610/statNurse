@extends('master')
@section('site-container')

<div class="layout_div_container">
    <div class="region header-image" id="region_0">
        <div id="sa-pe-header-image-container" style=" position:relative; min-height:1500px; height:100%; background-size: cover; background-repeat: no-repeat; background-position: center; background-image: url(/assets/files/page_editor_header_images/IMG_3372.jpg);">
            <h1><?= $category['name'] ?></h1>
        </div>
    </div>
</div>
<div class="container">
	<div class="row">
		<div class="col-12">

			<?php foreach ($events as $event) { ?>
				<div class="listEvent">
				    <a data-toggle="collapse" data-parent="#accordion" class="collapsed" href="#<?= $event['id'] ?>">
				        <div class="header">
				            <div class="date">
				            	<span class="day"><?= ($event['start_date'])->format('j') ?></span>
				            	<span class="month"><?= ($event['start_date'])->format('M') ?></span>
				            </div>
				            <div class="name">
				            	<?= $event['name'] ?> 
				            	<?= $event['location_name'] ? ' | '.$event['location_name'] : '' ?>
				            	<?= $event['start_time'] ? ' | '.$event['start_time']->format('g:i a') : '' ?>
				            </div>
				            <div class="arrow"><i class="fa fa-angle-down" aria-hidden="true"></i></div>
				        </div>
				    </a>
				    <div id="<?= $event['id'] ?>" class="panel-collapse collapse description">
				    	<div class="panel-body">
				    		<?= $event['description'] ?>
				    	</div>
				    </div>
				</div>
			<?php } ?>

		</div>
	</div>
</div>
@show