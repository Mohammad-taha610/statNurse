@section('header')

<?php $assetfolder = '/themes/acetheme/assets'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?=!empty($page_name) ? $page_name : 'Site Administrator'?></title>
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
    <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-16x16.png" sizes="16x16">
    <meta name="msapplication-TileColor" content="#438eb9">
    <meta name="msapplication-TileImage" content="<?=$assetfolder?>/images/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <!-- basic styles -->
    <link rel="stylesheet" href="<?=$assetfolder?>/css/bootstrap.css">

    <link rel="stylesheet" href="<?=$assetfolder?>/css/jquery.growl.css">

    <!-- fonts -->
    <link rel="stylesheet" href="<?=$assetfolder?>/css/ace-fonts.css">

    <!-- ace styles -->
    <!-- <link rel="stylesheet" href="<?=$assetfolder?>/css/ace.css"> -->

    <link rel="stylesheet" href="<?=$assetfolder?>/css/stylesheet.css">
    <link rel="stylesheet" href="<?=$assetfolder?>/css/sa-print.css">

    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/v4-shims.js"></script>

    <script src="<?=$assetfolder?>/js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>

    <script src="<?=$assetfolder?>/js/bootstrap.min.js"></script>
    <script src="<?=$assetfolder?>/js/ace.min.js"></script>
    <script src="<?=$assetfolder?>/js/ace2.min.js"></script>
    <script src="<?=$assetfolder?>/js/bootbox.min.js"></script>
    <script src="<?=$assetfolder?>/js/jquery.growl.js"></script>
    <script src="<?=$assetfolder?>/js/jquery-ui.min.js"></script>
    <script src="<?=$assetfolder?>/js/custom.js"></script>

    <script src="<?=$assetfolder?>/ckeditor/ckeditor.js"></script>

    <!-- custom styles -->
    <link rel="stylesheet" href="/themes/nst/css/style.css">
    <link rel="stylesheet" href="/themes/nst/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="/themes/nst/css/nstStyles.css">
    <link rel="stylesheet" href="/themes/nst/assets/css/nstResponsive.css">

    <!-- header,header_bar,sidebar,titlebar,{views},sidebar_closediv,footer -->
</head>
<body>
@show

<script>
    window.onload = function(){
        var el = document.getElementById('loading-wrapper');
        el.style.display = 'none';
    };
</script>

@section('headerbar')

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
                        /** @var \sa\saFiles\saImage $image */
                        $image = \sacore\application\ioc::get('saImage', \sacore\application\app::get()->getConfiguration()->get('siteadmin_image_id')->getValue());
                        if ($image) {
                            $siteadmin_image_path = \sacore\application\app::get()->getRouter()->generate(
                                'files_browser_view_file', ['folder' => $image->getFolder(), 'file' => $image->getFilename()]
                            );
                            ?>
                            <img src="<?=$siteadmin_image_path?>" class="img-responsive sa-logo"/>

                            <?php
                        } else {
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
                //echo '<a class="safe-mode-link navbar-brand" href="'.\sacore\utilities\url::make('system_safemode').'"><small> | SAFE MODE</small></a>';
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

                <li class="light-blue hidden-xs navbar-header-hide-small">
                    <a href="#" title="File Browser" id="navbar-header-file-browser">
                        <i class="fa fa-upload"></i>
                    </a>
                </li>

                <?= \sacore\application\modRequest::request('sa.header') ?>
            </ul><!-- /.ace-nav -->
        </div><!-- /.navbar-header -->
    </div><!-- /.container -->
</div>

<script>
    try{ace.settings.check('main-container' , 'fixed')}catch(e){}

    $(document).ready(function() {
        $('#navbar-header-file-browser').click(function() {
            window.open("/siteadmin/files/browse?uploadOnly=1", "browser", "location=1,status=0,scrollbars=0,width=600,height=700");
        });
    });
</script>

<div class="main-container" id="main-container">
    @show

    @section('sidebar')
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
        //
        \themes\acetheme\ViewHelper::sidenav_output( $menuItems, "nav nav-list" );

        ?>

        <!--        <div class="sidebar-collapse" id="sidebar-collapse">-->
        <!--            <i class="icon-collapse fa fa-angle-double-left" data-icon1="fa fa-angle-double-left" data-icon2="fa fa-angle-double-right"></i>-->
        <!--        </div>-->

        <!--        <div class="text-center" style="font-size: 10px; margin-top: 10px;">-->
        <!--            <span class="sa-powered-text">Powered By</span>-->
        <!--            <img src="/themes/siteadmin/assets/images/sa.png" height="15" />-->
        <!--            <span class="sa-powered-text">Site Administrator</span>-->
        <!--        </div>-->


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

    @show

    @section('titlebar')
    <div class="main-content">
        <div class="breadcrumbs" id="breadcrumbs">
            <ul class="breadcrumb">
                <li>
                    <i class="fa fa-home home-icon"></i>
                    <a href="/siteadmin/dashboard">Home</a>
                </li>

                <?php
                // TODO : FIX THIS
                // $breadcrumbs = \sacore\utilities\breadcrumb::get()->getLinks();
                // foreach( $breadcrumbs as $i => $link )
                // {
                //     if ($i == 0) { continue; }
                //     if ($link['active'])
                //     {
                //         ?>
                //         <li class="active">
                    //             <?=$link['name']?>
                    //         </li>
                //         <?php
                //     }
                //     else
                //     {
                //         ?>
                //         <li>
                    //             <a href="<?=$link['link']?>"><?=$link['name']?></a>
                    //         </li>
                //         <?php
                //     }
                // }
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
                <h1>


                    @if (page_name)

                    @{{ ?page_name }}

                    @else

                    <?=\sacore\application\app::get()->getActiveRequest()->getRoute()->getDefault('name')?>

                    @endif

                </h1>
                <a href="#" onclick="window.history.go(-1); return false;"><i class="icon-search fa fa-search fa-arrow-circle-left"></i>  Back</a>
            </div>
            @view::notifications

            @show

            @section('site-container')
            No content provided
            @show


            @section('footer')
        </div>
    </div>
</body>
</html>

@show
