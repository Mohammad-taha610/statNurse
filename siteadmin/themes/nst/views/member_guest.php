
<?php $assetfolder = '/themes/acetheme/assets'; ?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <title><?=!empty($page_name) ? $page_name : 'NurseStat LLC - Medical Staffing Agency'?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="NOINDEX,NOFOLLOW,NOARCHIVE,NOSNIPPET">

    <link rel="icon" type="image/png" sizes="16x16" href="/themes/nst/assets/images/favicon.png">

    <script src="https://unpkg.com/vue-select@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/vue-select@latest/dist/vue-select.css">
    <meta name="msapplication-TileColor" content="#438eb9">
    <meta name="msapplication-TileImage" content="<?=$assetfolder?>/images/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">


    <link rel="stylesheet" href="<?=$assetfolder?>/css/jquery.growl.css">

    <link rel="stylesheet" href="<?=$assetfolder?>/css/ace-fonts.css">


    <link rel="stylesheet" href="<?=$assetfolder?>/css/stylesheet.css">
    <link rel="stylesheet" href="<?=$assetfolder?>/css/sa-print.css">

    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/v4-shims.js"></script>

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

    <!-- custom styles -->
    <link rel="stylesheet" href="/themes/nst/assets/css/style.css">
    <link rel="stylesheet" href="/themes/nst/assets/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="/themes/nst/assets/css/nstStyles.css">
    <link rel="stylesheet" href="/themes/nst/assets/css/nstResponsive.css">

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

    <!-- header,header_bar,sidebar,titlebar,{views},sidebar_closediv,footer -->
</head>
<body class="h-100">

<div class="app">
    @yield('site-container')
</div>

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

<!-- Datatable -->
<!-- <script src="/themes/nst/assets/vendor/datatables/js/jquery.dataTables.min.js"></script> -->
<!-- <script src="/themes/nst/assets/js/plugins-init/datatables.init.js"></script> -->

<script>
    window.onload = function () {
        window.vue_wrapper = new Vue({
            el: '.app',
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

<script>
    function carouselReview(){
        /*  testimonial one function by = owl.carousel.js */
        /*  testimonial one function by = owl.carousel.js */
        jQuery('.testimonial-one').owlCarousel({
            loop:true,
            autoplay:true,
            margin:15,
            nav:false,
            dots: false,
            left:true,
            navText: ['', ''],
            responsive:{
                0:{
                    items:1
                },
                800:{
                    items:2
                },
                991:{
                    items:2
                },

                1200:{
                    items:2
                },
                1600:{
                    items:2
                }
            }
        })
    }

    jQuery(window).on('load',function(){
        setTimeout(function(){
            carouselReview();
        }, 1000);
    });
</script>
</body>
</html>
