<div class="col-xs-6 col-md-3 col-lg-2 widget-box">
    <div class="widget-header">
        <label>Site Admin Header Logo</label> <br />
        <div style="font-size: 10px; color: rgba(37, 37, 37, 1)">&nbsp;</div>
    </div>
    <div class="col-xs-6 col-md-3 col-lg-2 widget-main">
        <a href="#" data-container="image-holder"  data-input="siteadmin_image_id" class="btn btn-sm select-image">Select Image</a>

        <div class="image-holder">
            <?php if ($siteadmin_image_path) { ?><img src="<?= $siteadmin_image_path ?>" width="200" /><br /><?php } ?>
            <input type="hidden" name="siteadmin_image_id" value="<?= $siteadmin_image_id ?>"/>
        </div>
    </div>
</div>
<div class="col-xs-6 col-md-3 col-lg-2 widget-box">
    <div class="widget-header">
        <label>Site Admin Login Logo</label>  <br />
        <div style="font-size: 10px; color: rgba(37, 37, 37, 1)">&nbsp;</div>
    </div>
    <div class="col-xs-6 col-md-3 col-lg-2 widget-main">
        <a href="#" data-container="image-holder2" data-input="siteadmin_login_image_id" class="btn btn-sm select-image">Select Image</a>

        <div class="image-holder2">
            <?php if ($siteadmin_login_image_path) { ?><img src="<?= $siteadmin_login_image_path ?>" width="200" /><br /><?php } ?>
            <input type="hidden" name="siteadmin_login_image_id" value="<?= $siteadmin_login_image_id ?>"/>
        </div>
    </div>
</div>
<script>
    var img_container = null;
    var img_input = null;
    function fileBrowserSelectCallBack(object) {
        $('.' + img_container).html('<img src="' + object.filepath + '" width="200" /><br /><input type="hidden" name="' + img_input + '" value="' + object.id + '" />');
    }

    $('.select-image').click(function (e) {
        img_container = $(this).data('container');
        img_input = $(this).data('input');
        e.preventDefault();
        window.open("<?=\sacore\utilities\url::make('files_browse')?>?return=object&folder=siteadmin_theme_images", "browser", "location=1,status=0,scrollbars=0,width=600,height=700");
    });
</script>