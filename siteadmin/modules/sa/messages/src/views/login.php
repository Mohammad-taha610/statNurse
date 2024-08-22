@extends('member_guest')
@section('site-container')
<div class="account-wrapper">
    <?php
    $notify = new \sacore\utilities\notification();

    $auth = \sacore\application\modRequest::request('auth.object');
    if ( !$auth->isHardLogin() && $auth->isAuthenticated() ) {
        $notify->addNotification('info', 'Login', 'As an added level of security, you are required to login again to access this page.');
    }

    $notify->showNotifications();
    ?>

    <div class="account-body">
        <h4>Please sign in to get access.</h4>
        <form class="form account-form" method="POST"  action="@url('member_login_post')">

            <div class="form-group">
                <label for="login-username">Username</label>
                <input type="text" class="form-control" id="login-username" placeholder="Username" tabindex="1" name="username" value="<?=$username?>">
            </div> <!-- /.form-group -->

            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" class="form-control" id="login-password" placeholder="Password" tabindex="2" name="password" value="<?=$password?>">
            </div> <!-- /.form-group -->

            <div class="form-group clearfix">
                <div class="pull-left">
                    <label class="checkbox-inline">
                        <input type="checkbox" class="" name="remember_me" value="1" tabindex="3"> <small>Remember me</small>
                    </label>
                </div>

                <div class="pull-right">
                    <small><a href="@url('member_reset')">Forgot Password?</a></small>
                </div>
            </div> <!-- /.form-group -->

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block btn-lg" tabindex="4">
                    Sign In &nbsp; <i class="fa fa-play-circle"></i>
                </button>
            </div> <!-- /.form-group -->

        </form>


    </div> <!-- /.account-body -->

    <?php if($enable_public_member_signup) { ?>
        <div class="account-footer">
            <p>
                Don't have an account? &nbsp;
                <a href="@url('member_signup')" class="">Create an Account!</a>
            </p>
        </div> <!-- /.account-footer -->
    <?php } ?>

</div>
@show