<div class="main-content">

<div class="breadcrumbs" id="breadcrumbs">

	<ul class="breadcrumb">
		<li>
			<i class="fa fa-home home-icon"></i>
			<a href="/siteadmin/dashboard">Home</a>
		</li>

		<?php

		$breadcrumbs = \sacore\utilities\breadcrumb::get()->getLinks();
		foreach( $breadcrumbs as $i => $link )
		{
			if ($i == 0) { continue; }
			if ($link['active'])
			{
				?>
				<li class="active">
					<?=$link['name']?>
				</li>
				<?php
			}
			else
			{
				?>
				<li>
					<a href="<?=$link['link']?>"><?=$link['name']?></a>
				</li>
				<?php
			}
		}
		?>
	</ul>

	<div class="nav-search" id="nav-search">
		<form class="form-search">
			<span class="input-icon">
				<input type="text" placeholder="Search ..." class="nav-search-input" id="nav-search-input" autocomplete="off">
                <i class="icon-search fa fa-search fa-arrow-circle-left"></i>
			</span>
		</form>
	</div>
</div>

<div class="page-content">
	<div class="page-header">
		<h1><?=!empty($page_name) ? $page_name : $this->appContext->activeRoute->name?> </h1>
		<a href="#" onclick="window.history.go(-1); return false;"><i class="icon-search fa fa-search fa-arrow-circle-left"></i>  Back</a>
	</div>
	@view::notifications
