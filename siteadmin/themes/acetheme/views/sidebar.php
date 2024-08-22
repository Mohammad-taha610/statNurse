<a class="menu-toggler" id="menu-toggler" href="#">
	<span class="menu-text"></span>
</a>
<div class="sidebar responsive" id="sidebar">
	<script type="text/javascript">
		try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
	</script>

	<div class="sidebar-shortcuts" id="sidebar-shortcuts">

	</div><!-- #sidebar-shortcuts -->

	<?php

	$menuItems = $this->appContext->navigation->get();

	\themes\siteadmin\ViewHelper::sidenav_output( $menuItems, "nav nav-list" );

	?>

<!--	<div class="sidebar-collapse" id="sidebar-collapse">-->
<!--		<i class="icon-collapse fa fa-angle-double-left" data-icon1="fa fa-angle-double-left" data-icon2="fa fa-angle-double-right"></i>-->
<!--	</div>-->

<!--	<div class="text-center" style="font-size: 10px; margin-top: 10px;">-->
<!--		<span class="sa-powered-text">Powered By</span>-->
<!--		<img src="/themes/siteadmin/assets/images/sa.png" height="15" />-->
<!--        <span class="sa-powered-text">Site Administrator</span>-->
<!--	</div>-->


	<script type="text/javascript">
		try{
		    ace.settings.check('sidebar' , 'collapsed')
		}
		catch(e){

        }

        if ( $('#sidebar').hasClass('menu-min') )
        {
            $('.sa-powered-text').hide();
        }
        else
        {
            $('.sa-powered-text').show();
        }

        // $('#sidebar-collapse').click( function() {
        //     $('.sa-powered-text').toggle();
        // });

	</script>
</div>
