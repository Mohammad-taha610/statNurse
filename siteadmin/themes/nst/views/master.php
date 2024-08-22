
<?php $assetfolder = '/themes/acetheme/assets'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NurseStat LLC - Medical Staffing Agency</title>
    <meta name="description" content="overview &amp; stats">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="NOINDEX,NOFOLLOW,NOARCHIVE,NOSNIPPET">

    <script src="https://unpkg.com/vue-select@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/vue-select@latest/dist/vue-select.css">

    <link rel="apple-touch-icon" sizes="57x57" href="<?=$assetfolder?>/images/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?=$assetfolder?>/images/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?=$assetfolder?>/images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?=$assetfolder?>/images/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?=$assetfolder?>/images/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?=$assetfolder?>/images/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?=$assetfolder?>/images/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?=$assetfolder?>/images/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$assetfolder?>/images/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon.png" sizes="32x32">
    <meta name="msapplication-TileColor" content="#438eb9">
    <meta name="msapplication-TileImage" content="<?=$assetfolder?>/images/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">


    <link rel="stylesheet" href="<?=$assetfolder?>/css/jquery.growl.css">

    <link rel="stylesheet" href="<?=$assetfolder?>/css/ace-fonts.css">


    <link rel="stylesheet" href="<?=$assetfolder?>/css/stylesheet.css">
    <link rel="stylesheet" href="<?=$assetfolder?>/css/sa-print.css">

    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/v4-shims.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/luxon@3.2.1/build/global/luxon.min.js"></script>
    <script src="<?=$assetfolder?>/js/jquery.js"></script>
    <script src="/themes/nst/assets/js/vue.js"></script>

    <!-- <script src="$assetfolder/js/bootstrap.min.js"></script> -->
    <script src="<?=$assetfolder?>/js/ace.min.js"></script>
    <script src="<?=$assetfolder?>/js/ace2.min.js"></script>
    <script src="<?=$assetfolder?>/js/bootbox.min.js"></script>
    <script src="<?=$assetfolder?>/js/jquery.growl.js"></script>
    <script src="<?=$assetfolder?>/js/jquery-ui.min.js"></script>
    <script src="<?=$assetfolder?>/js/custom.js"></script>

    <script src="<?=$assetfolder?>/ckeditor/ckeditor.js"></script>


    <link rel="icon" type="image/png" sizes="16x16" href="/themes/nst/assets/images/favicon.png">
    <link href="/themes/nst/assets/vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="/themes/nst/assets/vendor/chartist/css/chartist.min.css">
    <link href="/themes/nst/assets/vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
	<link href="https://cdn.lineicons.com/2.0/LineIcons.css" rel="stylesheet">
	<link href="/themes/nst/assets/vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
    <!-- <link href="/themes/nst/assets/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet"> -->
    <link href="/themes/nst/assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">

    <!-- Vuetify -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <link href="/themes/nst/assets/css/vuetify.min.css" rel="stylesheet">

    <!-- custom styles -->
    <link rel="stylesheet" href="/themes/nst/assets/css/style.css">
    <link rel="stylesheet" href="/themes/nst/assets/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="/themes/nst/assets/css/nstStyles.css">
    <link rel="stylesheet" href="/themes/nst/assets/css/nstResponsive.css">

    <!-- header,header_bar,sidebar,titlebar,{views},sidebar_closediv,footer -->
    <script src="/themes/nst/js/nst_loading_vue.js"></script>
</head>
<body>

    @view::_member_profile_header_nav
    
    @view::_profile_sidebar

    <div id="vue-wrapper">
        <div class="content-body">
            <?php
            $notify = new \sacore\utilities\notification();
            $notify->showNotifications();
            ?>
            @yield('site-container')
        </div>
    </div>
    
    @view::_member_profile_footer

    <script>
        var EventBus = new Vue({});
        window.onload = function() {
            window.vue_wrapper = new Vue({
                el: '#vue-wrapper',
                vuetify: new Vuetify({
                    theme: {
                        themes: {
                            light: {
                                primary: '#ee4037',
                                secondary: '#8BC740',
                                danger: '#FF6746',
                                success: '#1BD084',
                                info: '#48A9F8',
                                warning: '#FE8024',
                                light: '#F4F5F9',
                                dark: '#B1B1B1',
                                blue: '#5e72e4',
                                indigo: '#6610f2',
                                purple: '#6f42c1',
                                pink: '#e83e8c',
                                red: '#EE3232',
                                orange: '#ff9900',
                                yellow: '#FFFA6F',
                                green: '#297F00',
                                teal: '#20c997',
                                cyan: '#3065D0',
                                white: '#fff',
                                gray: '#6c757d',
                                gray_dark: '#343a40'
                            }
                        }
                    }
                }),
                data: function() {
                    return {

                    }
                },
                mounted: function() {

                },
                methods: {

                }
            });
        }
    </script>
    <!-- Required vendors -->
    <script src="/themes/nst/assets/vendor/global/global.min.js"></script>
	<script src="/themes/nst/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
	<script src="/themes/nst/assets/vendor/chart.js/Chart.bundle.min.js"></script>
    <script src="/themes/nst/assets/js/custom.min.js"></script>
	<script src="/themes/nst/assets/js/deznav-init.js"></script>
	<script src="/themes/nst/assets/vendor/owl-carousel/owl.carousel.js"></script>
		
    <script src="/themes/nst/assets/vendor/peity/jquery.peity.min.js"></script>

	<script src="/themes/nst/assets/js/dashboard/dashboard-1.js"></script>

    <!-- Vuetify -->
    <script src="/themes/nst/assets/js/vuetify.js"></script>

</body>
</html>
