<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
	<?php
		foreach($sitemap as $module) {
			foreach($module as $url ) { ?>
				<url>
					<?php foreach($url as $k => $v) {

						if (!in_array($k, $allowed_tags))
							continue;

						?>
						<<?=$k ?>>
							<?= htmlentities($v) ?>
						</<?=$k ?>>
					<?php } ?>
				</url>
			<?php
		 } }
	?>
</urlset>