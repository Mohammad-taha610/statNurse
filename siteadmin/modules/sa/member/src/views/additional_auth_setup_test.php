@extends('member_guest')
@section('site-container')
<div class="">
    <div class="account-body">
        <div class="page-header">
            <h1>Additional Authentication Setup</h1>
        </div>
        <form action="<?=$url->make('member_additionalauthsetup_test_submit')?>" method="post">
            <div class="row" style="margin-top:25px">
                <div class="col-sm-12">
                    <label>Please test the additional authentication method:  <br /><br />

                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div>
                        Method:
                        <?php
                        if ($method=='ga') {
                            echo 'Google Authenticator';
                        }
                        else if ($method=='sms') {
                            echo 'Text Message';
                        }
                        else if ($method=='call') {
                            echo 'Phone Call';
                        }

                        ?>
                    </div>
                    <div>
                        <?php
                        if ($method=='ga') {
                            echo 'Please type the code from the Google Authenticator app below and click submit.';
                        }
                        else if ($method=='sms') {
                            echo 'You should be receiving a text message at '.$verification_data.' containing a 6 digit code.  Please enter the code below and click submit.';
                        }
                        else if ($method=='call') {
                            echo 'You should be receiving a phone call at '.$verification_data.'.  Please enter the 6 digit code below and click submit.';
                        }

                        ?>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:25px;">
                <div class="col-sm-12">
                    <label style="font-weight:bold">Verification Code</label>
                    <input type="tel" name="verification_code" placeholder="">
                </div>
            </div>


            <div class="row">
                <div class="col-sm-12">
                    <input type="hidden" name="verification_method" value="<?=$method?>">
                    <input type="hidden" name="verification_data" value="<?=$verification_data?>">

                    <input type="submit" class="btn btn-info" value="submit" />
                </div>
            </div>
        </form>
    </div>
</div>
@show