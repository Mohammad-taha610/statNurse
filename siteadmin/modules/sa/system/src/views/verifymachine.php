<div class="animated fadeInDown">
    <div class="saSignInHeader"><?php
        $loginImageId = \sacore\application\app::get()->getConfiguration()->get('siteadmin_login_image_id')->getValue();

        /** @var \sa\saFiles\saImage $image */
        $image = \sacore\application\ioc::get('saImage', $loginImageId);

        if ($image) {
            $siteadmin_image_path = \sacore\utilities\url::make('files_browser_view_file', $image->getFolder(), $image->getFilename());
            ?><a href="/siteadmin"><img src="<?=$siteadmin_image_path?>" class="img-responsive padlogo"/></a><?php
        } else {
            $siteadmin_image_path = '/siteadmin/system/images/sa_login_logo.png';
            ?><a href="/siteadmin"><img src="<?=$siteadmin_image_path?>" class="img-responsive"  /></a><?php
        }

        ?></div>
    <div class="container">
        <div class="card card-container">

            <div id="error" class="error saHidden">
                <h3>Error</h3>
                <div id="error-message"></div>
            </div>

            <div class="send-well" style=" <?=$type == 'SMS' ? '' : 'display: none'?> ">

                <div>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <?php
                            $notify = new \sacore\utilities\notification();
                            $notify->showNotifications();
                            ?>
                            <div style="">
                                As an added level of security, we require <br />you to register the computer you are using. <br /> <br />
                                Please click "Send Now"<br /> to receive a text message with your security code.<br />

                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12 text-center">
                            <h3><?=$mobile?></h3>
                            <button type="button" class="btn btn-lg btn-primary btn-block btn-signin" id="btn-send-now">Send Now</button>
                            <button type="button" class="btn btn-lg btn-primary btn-block btn-signin saHidden" id="btn-sending" disabled>Sending ...</button>
                        </div>
                    </div>

                </div>
            </div>


            <div class="verify-well <?=$type == 'SMS' ? 'saHidden' : ''?> ">
                <div>

                    <div class="row">
                        <div class="col-md-12 text-center">

                            <div>

                                <?php if ($type == 'SMS') { ?>

                                    Please enter the security code text to you.

                                <?php } else if ($type == 'GA') { ?>

                                    As an added level of security,we require  <br />you to register the computer you are using. <br /><br />
                                    Please enter the<br /> Google Authenticator security code below:<br />


                                <?php } ?>

                            </div>

                        </div>
                    </div>


                    <div class="row" style="margin-top: 15px">
                        <div class="col-xs-12 col-md-12">

                            <input type="text" id="code" value="" class="form-control" />
                            <div class="text-muted" style="margin-bottom: 20px"></div>
                            <button type="button" class="btn btn-lg btn-primary btn-block btn-signin" id="btn-verify-now" >Verify Now</button>
                            <button type="button" class="btn btn-lg btn-primary btn-block saHidden btn-signin" id="btn-verifying-now" disabled>Verifying ...</button>
                        </div>
                    </div>

                    <div class="text-mute text-center" style="margin-top: 15px">
                        <?php
                        if ($type == 'GA') { ?>
                            If you do not have the IOS/Android app, please contact your administrator.
                        <?php } else { ?>
                            If the phone number listed is not valid, please contact your administrator.
                        <?php }?>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <div class="saSignInFooterCopyright">&copy; <?php echo date("Y");?> Site Administrator</div>
</div>


<script>

    $('#btn-send-now').click( function() {

        $('#error').hide();
        $('#btn-send-now').hide();
        $('#btn-sending').show();
        modRequest.request('sa.verify.send_code', null, null, function() {

            $('.send-well').fadeOut(500, function() {
                $('.verify-well').fadeIn(500);
            });

        });
    })

    $('#btn-verify-now').click( function() {
        $('#btn-verify-now').hide();
        $('#btn-verifying-now').show();
        $('#error').hide();
        modRequest.request('sa.verify.verify_code', null, { 'code': $('#code').val() }, function(data) {

            if (!data) {
                $('#error #error-message').text('The code you entered is invalid. Please try again.');
                $('#error').show();
                $('#btn-verify-now').show();
                $('#btn-verifying-now').hide();
            }
            else
            {
                location.reload();
            }

        });
    })

</script>
