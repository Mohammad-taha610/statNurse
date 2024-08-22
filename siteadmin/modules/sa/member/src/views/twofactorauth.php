@extends('member_guest')
@section('site-container')
<div class="account-wrapper">
    <div class="account-body">
        <div class="page-header">
            <h1>Additional Authentication Required</h1>
        </div>
        <form action="<?=$url->make('member_two_factor_verify_user_input')?>" method="post">
            <div class="row" style="margin-top:25px">
                <div class="col-sm-12">
                    <label>As an added level of security, we require you to use an additional verification method.<br /><br />
                        Please select a verification method below:</label>
                </div>
            </div>
            <?php

            if ($google_auth) {
                ?>
                <div class="row" style="margin-top:25px">
                    <div class="col-sm-12">
                        <label style="font-weight:bold">Google Authenticator</label>

                        <div class="form-group">
                            <input type="radio" value="google_auth" name="phoneid" id="ga"
                                   placeholder="">
                            <label for="ga">Google Authenticator App</label>
                        </div>

                    </div>
                </div>
                <?php
            }


            if (count($mobile)>0) {
                ?>
                <div class="row" style="margin-top:25px">
                    <div class="col-sm-12">
                        <label style="font-weight:bold">Mobile Phone Numbers (Text Message)</label>
                        <?php
                        foreach ($mobile as $k => $p) {
                            ?>
                            <div class="form-group">
                                <input type="radio" value="<?= $p['id'] ?>" name="phoneid" id="number<?= $k ?>"
                                       placeholder="">
                                <label for="number<?= $k ?>"><?= \sacore\utilities\stringUtils::formatPhoneNumber($p['phone']) ?></label>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }

            if (count($other)>0) {
                ?>
                <div class="row" style="margin-top:25px">
                    <div class="col-sm-12">
                        <label style="font-weight:bold">Other Phone Numbers (Phone Call)</label>
                        <?php
                        foreach ($other as $k => $p) {
                            ?>
                            <div class="form-group">
                                <input type="radio" value="<?= $p['id'] ?>" name="phoneid" id="pnumber<?= $k ?>"
                                       placeholder="">
                                <label for="pnumber<?= $k ?>"><?= \sacore\utilities\stringUtils::formatPhoneNumber($p['phone']) ?></label>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="row">
                <div class="col-sm-12">
                    <input type="submit" class="btn btn-info" value="submit" />
                </div>
            </div>
        </form>
    </div>
</div>
@show