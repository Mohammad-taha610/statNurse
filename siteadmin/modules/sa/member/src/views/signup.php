<!--The old code used the member guest-->
@extends('member_guest')
<!--I was just matching the section for the SA portions, but I doubt that is the name of the section on this view-->
@section('site-container')
<div class="account-wrapper">

    <?php

    use sacore\application\app;

    ?>

    <div class="account-body">

        <div class="text-center">
            <h2>Get started with an Account</h2>

            <h5><!--You're almost there! Just create a password with 6 or more characters in it and enter it in twice below for verification.--></h5>
        </div>

        <form class="form account-form" method="POST" action="@url('member_signup_post')">

            <div class="form-group">
                <label for="first_name">First name</label>
                <input type="text" name="first_name" value="<?=$first_name?>" id="first_name" class="form-control" >
            </div>
            <div class="form-group">
                <label for="last_name">Last name</label>
                <input type="text" name="last_name" value="<?=$last_name?>" id="last_name" class="form-control" >
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" value="<?=$phone?>" id="phone" class="form-control" >
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" name="email" id="email" value="<?=$email?>" class="form-control" autocapitalize="off" >
                <p class="form-help"><small>This will be your login username</small></p>
            </div>
            <div class="form-group">
                <label for="email2">Confirm email address</label>
                <input type="email" name="email2" id="email2" value="<?=$email2?>" class="form-control" autocapitalize="off" >
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" value="<?=$password?>" id="password" class="form-control" autocapitalize="off"   title="Your password must be at least 6 characters long">
            </div>
            <div class="form-group">
                <label for="password2">Confirm password</label>
                <input type="password" name="password2" value="<?=$password2?>" id="password2" class="form-control" autocapitalize="off" >
            </div>
            <?php
            if(app::get()->getConfiguration()->get('signup_form_use_recaptcha')->getValue()) {
                $captchahtml = 'Recaptcha private and/or public keys are missing.  Please change the configuration.';
                if (!empty(app::get()->getConfiguration()->get('recaptcha_public')->getValue()) && !empty(app::get()->getConfiguration()->get('recaptcha_private')->getValue())) {
                    $captcha = new Captcha\Captcha();
                    $captcha->setPublicKey(app::get()->getConfiguration()->get('recaptcha_public'));
                    $captcha->setPrivateKey(app::get()->getConfiguration()->get('recaptcha_private'));
                    $captcha->setRemoteIp($_SERVER['REMOTE_ADDR']);
                    $captchahtml = $captcha->html();
                }
                echo $captchahtml;
            }?>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary btn-block btn-lg" tabindex="6">
                    Create My Account &nbsp; <i class="fa fa-play-circle"></i>
                </button>
            </div> <!-- /.form-group -->

            <div class="form-group">
                <a href="@url('member_login')"><i class="fa fa-angle-double-left"></i> &nbsp;Back to Login</a>
            </div> <!-- /.form-group -->

        </form>

    </div> <!-- /.account-body -->

</div>
@show