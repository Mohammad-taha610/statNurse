@extends('member_guest')
@section('site-container')
<div class="container h-100">
    <div class="row justify-content-center h-100 align-items-center">
        <div class="account-wrapper">

            <div class="account-body">

                <div class="text-center mb-5">
                    <a href="index.html"><img src="/themes/nst/assets/images/white-logo.png" alt=""></a>
                </div>
                <?php
                $notification = new \sacore\utilities\notification();
                $notification->showNotifications();
                ?>

                <div class="text-left mb-5">
                    <h2>Password Reset</h2>

                    <h5>We'll email you instructions on how to reset your password. Please enter your username.</h5>
                </div>

                <form class="form account-form" method="POST" action="@url('member_reset_post')">

                    <div class="form-group">
                        <label for="forgot-email" class="placeholder-hidden">Your Username</label>
                        <input type="text" class="form-control" id="forgot-email" placeholder="Your Username" tabindex="1" name="username">
                    </div> <!-- /.form-group -->

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block text-white btn-lg" tabindex="2">
                            Reset Password &nbsp; <i class="fa fa-refresh"></i>
                        </button>
                    </div> <!-- /.form-group -->

                    <div class="form-group">
                        <a href="@url('member_login')"><i class="fa fa-angle-double-left"></i> &nbsp;Back to Login</a>
                    </div> <!-- /.form-group -->
                </form>

            </div> <!-- /.account-body -->

        </div>
    </div>
</div>
@endsection