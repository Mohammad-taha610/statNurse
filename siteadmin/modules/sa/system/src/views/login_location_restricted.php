<div class="animated fadeInDown">
    <div class="saSignInHeader"><?php
        $loginImageId = \sacore\application\app::get()->getConfiguration()->get('siteadmin_login_image_id')->getValue();

        if (!empty($loginImageId)) {
            /** @var \sa\saFiles\saImage $image */
            $image = \sacore\application\ioc::get('saImage', $loginImageId);
            if ($image) {
                $siteadmin_image_path = \sacore\utilities\url::make('files_browser_view_file', $image->getFolder(), $image->getFilename());
                ?><a href="/siteadmin"><img src="<?=$siteadmin_image_path?>" class="img-responsive padlogo"/></a><?php
            }
            else
            {
                $siteadmin_image_path = '/siteadmin/system/images/sa_login_logo.png';
                ?><a href="/siteadmin"><img src="<?=$siteadmin_image_path?>" class="img-responsive"  /></a><?php
            }
        }
        else
        {
            $siteadmin_image_path = '/siteadmin/system/images/sa_login_logo.png';
            ?><a href="/siteadmin"><img src="<?=$siteadmin_image_path?>" class="img-responsive"  /></a><?php
        }
        ?></div>
    <div class="container">
        <div class="card card-container">

            <div id="error" class="error">
                <h3>Login Location Not Authorized</h3>
                <div id="error-message"></div>
            </div>

            <div class="send-well">

                <div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <?php
                            $notify = new \sacore\utilities\notification();
                            $notify->showNotifications();
                            ?>
                            <div style="">
                                As an added level of security, you are not authorized to log into Site Administrator from this location.<br />
                                Please request access from a Super User or contact an administrator for assistance.<br /><br />
                                <a href="<?= \sacore\utilities\url::make('sa_logoff') ?>" class="btn btn-lg btn-primary btn-block btn-signin" id="btn-send-now">Return to Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="saSignInFooterCopyright">&copy; <?php echo date("Y");?> Site Administrator</div>
</div>
