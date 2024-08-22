<div class="account-wrapper">

		<div class="text-center col-sm-12">
			<h2>Your Crash Photos</h2>
		</div>

		<div>
			<?php
				if($photos)
				{
					foreach($photos as $photo)
					{
						?>
						<div class="col-md-4 col-sm-12">
							<img src="/themes/inspinia/assets/images/<?=$photo ?>" alt="Crash Photo" />
						</div>
						<?php
					}
				}
				else
				{
					echo "There are no photos to display.";
				}
			?>
		</div>

</div>