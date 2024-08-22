<?php

use sacore\application\app;

?>
@asset::/siteadmin/system/js/toolbar.js
@asset::/vendor/nursestat/sacore/assets/modal.js
@asset::/siteadmin/system/css/editor.css
@view::modal
<div class="main-container" id="main-container">
    <div id="toggleToolbarBox"><a href="javascript:void(0)" id="toggleToolbar" style="font-size:18px !important;" class="hide" title="Expand Toolbar"><img src="<?=$assetfolder?>/images/sa.png" height="45px" /></a></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-fixed-400" id="peToolbar">


                <div class="navbar navbar-default" id="navbar" style="position:relative;">
                    <script>
                        try{ace.settings.check('navbar' , 'fixed')}catch(e){}

                        var _siteadmin_url = "<?php echo app::get()->getConfiguration()->get('site_url')->getValue() . app::get()->getRouter()->generate('sa_pe_pages'); ?>";
                    </script>

                    <div class="navbar-container" id="navbar-container">
                        <div class="navbar-header pull-left">
                            <a href="<?= app::get()->getConfiguration()->get('site_url')->getValue() ?>/siteadmin" class="navbar-brand">
                                <small>
                                    <img src="<?=$assetfolder?>/images/sa.png" height="45px" />
                                    Site Administrator
                                </small>
                            </a><!-- /.brand -->
                        </div><!-- /.navbar-header -->

                        <div class="navbar-header pull-right" role="navigation">
                            <a href="#" id="toggleToolbarClose" class="display_icon white" style="font-size:14px !important;" title="Collapse Toolbar"><i class="fa  fa-caret-left"></i></a>
                            <a href="#" id="refreshFrame" class="display_icon white" style="font-size:14px !important;" title="Refresh Page"><i class="fa fa-undo"></i></a>
                            <a href="#" id="exitToolbar" class="display_icon white" style="font-size:14px !important;" title="Exit Toolbar"><i class="fa fa-remove"></i></a>
                        </div><!-- /.navbar-header -->
                    </div><!-- /.container -->
                </div>

                <div style="margin:10px 0 0 13px; ">
                    <div class="tabbable">
                        <ul id="tabs" class="nav nav-tabs">
                            <li class="active sticky" style="margin-left:10px;">
                                <a id="tab_selector_site" href="#site" data-toggle="tab" aria-expanded="true">
                                    Site
                                </a>
                            </li>

<!--                            <li class="">-->
<!--                                <a href="#messages" data-toggle="tab" aria-expanded="false">-->
<!--                                    Page-->
<!--                                    <!-- <span class="badge badge-danger">4</span> -->
<!--                                </a>-->
<!--                            </li>-->
<!--                            <li class="">-->
<!--                                <a href="#theme" data-toggle="tab" aria-expanded="false">-->
<!--                                    Theme-->
<!--                                    <!-- <span class="badge badge-danger">4</span> -->
<!--                                </a>-->
<!--                            </li>-->

<!--                            <li class="dropdown ">-->
<!--                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">-->
<!--                                    <i class="ace-icon fa fa-caret-down bigger-110 width-auto"></i>-->
<!--                                </a>-->
<!---->
<!--                                <ul class="dropdown-menu dropdown-info pull-right">-->
<!--                                    <li>-->
<!--                                        <a href="#dropdown1" data-toggle="tab">Manage Layouts</a>-->
<!--                                    </li>-->
<!---->
<!--                                    <li>-->
<!--                                        <a href="#dropdown2" data-toggle="tab">Create Page</a>-->
<!--                                    </li>-->
<!--                                </ul>-->
<!--                            </li>-->
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade active in sticky" id="site">
                                <div class="tab-pane-property">
                                    <strong>Site Name</strong><br />
                                    <a href="@url('sa_settings_modal')" data-samodal="true" data-samodal_type="frame" data-samodal_width="1024" aria-expanded="true" style="display:block;padding:2px;">
                                        <i class="blue ace-icon fa fa-pencil bigger-120 pull-right"></i><?= app::get()->getConfiguration()->get('site_name')->getValue() ?>
                                    </a>
                                </div>
<!--                                <div class="tab-pane-property">-->
<!--                                    <strong>Fav Icon</strong><br />-->
<!--                                    <a href="#home"  data-toggle="tab" aria-expanded="true" style="display:block;padding:2px;"><i class="blue ace-icon fa fa-pencil bigger-120" style="float:right;"></i>-</a>-->
<!--                                </div>-->

                                <div class="tab-pane-property">

                                    <div>
                                        <strong>Site Map</strong>
                                        <div class="pull-right pe_tree_loading"><i class="fa fa-circle-o-notch fa-spin"></i> </div>
                                    </div>

                                    <div class="page_tree_container">
                                        <div class="row pages_table_header">

                                        </div>

                                        <div class="tree_body">

                                        </div>

                                    </div>
                                </div>
                            </div>



                        </div>
                    </div>

                    <div id="display_views">
                        <h1>Display Formats</h1>
                        <a href="#" id="resize_phone" class="display_icon" title="Phone"><i class="fa fa-mobile"></i></a>
                        <a href="#" id="resize_tablet" class="display_icon" title="Tablet Vertical"><i class="fa fa-tablet"></i></a>
                        <a href="#" id="resize_tablet_hor" class="display_icon" title="Tablet Horizontal"><i class="fa fa-tablet fa-rotate-90"></i></a>
                        <a href="#" id="resize_desktop" class="display_icon display_icon_active" title="Desktop"><i class="fa fa-desktop"></i></a>
                    </div>

                </div>




            </div>
            <div class="col-xs-12 col-offset-400 hidden-md-down peToolbarSiteBackground" id="peToolbarSite">
                <iframe id="frame" name="frame" onLoad="toolbar.updateFrame(this);" src="<?= isset($_REQUEST['url']) ?  $_REQUEST['url'] : '/' ?>" style="display:block; width:100%; height:100vh; border-left:1px solid #dddddd; background-color: white;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
