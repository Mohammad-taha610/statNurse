@view::header
@asset::/siteadmin/system/css/login_recaptcha_error.css

<div class="container">
    <?php if($site_img) { ?>
        <div class="row">
            <div class="col-xs-12 text-center">
                <img src="<?= \sacore\utilities\url::make('files_browser_view_file_by_id', $site_img) ?>">
            </div>
        </div>
    <?php } ?>
    <div class="row">
        <div class="col-xs-12 text-center">
            <h1>Login Error</h1>
            <p>You have unsuccessfully attempted to login more than 3 times. Please contact the site administrator.</p>
        </div>
    </div>
</div>
