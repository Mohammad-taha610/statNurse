@extends('member_guest')
@section('site-container')
<div class="">
    <div class="account-body">
        <div class="page-header">
            <h1>Additional Authentication Setup</h1>
        </div>
        <form action="<?=$url->make('member_additionalauthsetup_test')?>" method="post">
            <div class="row" style="margin-top:25px">
                <div class="col-sm-12">
                    <label>Please setup an additional authentication method below:</label>
                </div>
            </div>


            <?php
            if ($usePhone) {
                ?>
                <div class="row" style="margin-top:25px">
                    <div class="col-sm-12">
                        <label style="font-weight:bold">
                            <input name="method_type" type="radio" value="ga" />
                            Google Authenticator
                        </label>

                        <label style="font-weight:bold">
                            <input name="method_type" type="radio" value="sms" />
                            Text Message
                        </label>

                        <label style="font-weight:bold">
                            <input name="method_type" type="radio" value="call" />
                            Phone Call
                        </label>
                    </div>
                </div>
                <?php
            }
            ?>


            <?php
            if ($usePhone) {
                ?>
                <div class="row phone data-collection" style="margin-top:25px; display: none">
                    <div class="col-sm-12">
                        <label style="font-weight:bold">Phone Number</label>
                        <input type="tel" name="call_phone" placeholder="859-422-0000">
                    </div>
                </div>
                <div class="row sms data-collection" style="margin-top:25px; display: none">
                    <div class="col-sm-12">
                        <label style="font-weight:bold">Mobile Number</label>
                        <input type="tel" name="sms_phone" placeholder="859-422-0000">
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="row google data-collection"  style=" <?=$usePhone ? 'display: none' : ''?>">
                <div class="col-md-12">
                    Go to the Google Play store or the Apple App Store to download the Google Authenticator App. Then scan or type in the barcode below:

                    <br />

                    <img style="margin: 25px 0 25px 0" src="<?=$qr_image?>" />

                    <br />

                    <?=$code?>

                    <input type="hidden" name="secret_code" value="<?=$code?>">

                </div>
            </div>


            <div class="row">
                <div class="col-sm-12">
                    <input type="submit" class="btn btn-info" value="submit" />
                </div>
            </div>
        </form>
    </div>
</div>

<script>

    $('input[name="method_type"]').change( function() {

        $('.data-collection').hide();

        if ( $(this).val()=='sms' ) {
            $('.sms').show();
        }
        else if ( $(this).val()=='call' ) {
            $('.phone').show();
        }
        else if ( $(this).val()=='ga' ) {
            $('.google').show();
        }

    })

</script>
@show