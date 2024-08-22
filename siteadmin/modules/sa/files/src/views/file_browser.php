<!--This should not be master but master_nonav_notitlebar isn't currently a template -->
@extends('master')
@section('site-container')

@asset::/assets/files/js/jstree.min.js
@asset::/assets/files/js/browser.js
@asset::/assets/files/css/browser.css
@asset::/assets/files/css/style.min.css

<input type="hidden" id="return" value="<?=$return?>" />
<input type="hidden" id="folder" value="<?=$folder?>" />
<input type="hidden" id="prependpath" value="<?=$prependpath?>" />

<div class="widget-box widget-color-green2">
    <div class="widget-header">
        <h4 class="widget-title lighter smaller">Browse</h4>

        <div class="pull-right headeruploadtoolbar">
            <div id="delete" class="delete" style="display: none;">
                <strong><span id="files-selected"></span></strong>
                <button id="delete-btn" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
            </div>
            
            <div id="progress" class="progress" style="display: none">
                <div class="progress-bar progress-bar-success"></div>
            </div>

            <div id="buttons_container">
                <button class="btn btn-primary btn-xs show_search"><i class="fa fa-search "></i></button>
                <button class="btn btn-primary btn-xs show_create_folder"><i class="fa fa-plus "></i><i class="fa fa-folder "></i></button>
            </div>
            <div id="create_folder_container" style="display: none">
                <input placeholder="Folder Name" id="new_folder_name" type="text"  />
                <button class="btn btn-primary btn-xs save_folder"><i class="fa fa-save "></i></button>
            </div>
            <div id="search_container" style="display: none">
                <input placeholder="Search" id="search_name" type="text"  />
                <button class="btn btn-primary btn-xs search_now"><i class="fa fa-search "></i></button>
                <button class="btn btn-primary btn-xs close_search "><i class="fa fa-times "></i></button>
            </div>

            <select id="folder_selection">

                <?php
                foreach($folder_list as $f) {

                    ?>

                    <option <?=$folder==$f ? 'selected="selected"' : ''?> value="<?=$f?>"><?=$f?></option>

                    <?php
                }

                ?>
            </select>
            <button class="btn btn-primary btn-xs uploadaliasbutton" style="float: right"><i class="fa fa-upload "></i> Upload</button>
            <input type="file" name="file" id="uploadfilebutton" />
        </div>


    </div>

    <div class="widget-body">
        <div class="widget-main padding-8">
            <div id="no-files-msg" style="display: none;">There are no files in this folder</div>
            <div id="file-tree"></div>
        </div>

    </div>
</div>

<div class="widget-box widget-color-green2" style="display:none;" id="fileinfo">
    <div class="widget-header">
        <h4 class="widget-title lighter smaller">File Preview</h4>
    </div>

    <div class="widget-body fileinfobox">
        <div class="widget-main padding-8 clearfix">

            <div class="pull-left">
                <div id="preview"></div>
                <div id="clipboard" class="text-center">
                    <button class="btn btn-success btn-xs">Copy Link</button>
                </div>
                <div id="clipboard-success" class="green text-center" style="display: none;">
                    <i class="fa fa-check"></i>&nbsp;Copied!
                </div>
            </div>
            <div class="pull-left">
                <div id="filename">
                    File Name
                </div>
                <div id="filesize">
                    File Size
                </div>
                <div id="filepath">
                    File Path
                </div>
                <div class="clearfix"></div>
                <div id="warning">
                    <div class="pull-left">
                        <i class="fa fa-exclamation-triangle warning"></i>
                    </div>
                    <div class="pull-left">
                        The file you are selecting is above the <br /> maximum recommended size of 400KB.
                        This file could impact load time.  <br />Please consider using a smaller file.
                    </div>
                    <div style="clear: both"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="actions">
    <button id="close" class="btn btn-prev">
        Close
    </button>
    <?php if(!$uploadOnly) { ?>
        <button id="select" class="btn btn-success btn-next">
            OK
            <i class="ace-icon fa fa-arrow-right icon-on-right"></i>
        </button>
    <?php } ?>
</div>

<script>
    <?php 
        $requiresSSL = \sacore\application\app::get()->getConfiguration()->get('require_ssl')->getValue();
        $site_url_key =  $requiresSSL == true ? 'secure_site_url' : 'site_url';
    ?>
    
    var siteUrl = '<?= \sacore\application\app::get()->getConfiguration()->get($site_url_key)->getValue() ?>';
</script>
@show