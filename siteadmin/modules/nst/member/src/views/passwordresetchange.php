@extends('member_guest')
@section('site-container')
<div class="container h-100">
    <div class="row justify-content-center h-100 align-items-center">
        <div class="account-wrapper">

            <div class="account-body">
                <div class="text-center mb-5">
                    <a href="index.html"><img src="/themes/nst/assets/images/white-logo.png" alt=""></a>
                </div>

                <div class="text-center">
                    <h2>Password Reset</h2>

                    <h5>You're almost there! Just create a password with 6 or more characters in it and enter it in twice below for verification.</h5>
                </div>

                <form class="form account-form" method="POST" action="@url('member_reset_change_post')">

                    <div class="form-group">
                        <label for="forgot-email" class="placeholder-hidden">Your Username</label>
                        <input type="text" class="form-control" id="forgot-email" placeholder="Your Username"  disabled="disabled" name="username" value="<?=$username?>">
                    </div>

                    <div class="form-group">
                        <label for="forgot-password1" class="placeholder-hidden">Your Password</label>
                        <input class="form-control" id="forgot-password1" placeholder="Your Password" type="password"  name="password"  autocapitalize="off" autofocus required>
                    </div>

                    <div class="form-group">
                        <label for="forgot-password2" class="placeholder-hidden">Confirm Password</label>
                        <input class="form-control" id="forgot-password2" placeholder="Confirm Password" type="password" name="password2" autocapitalize="off" autofocus required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block text-white btn-lg" tabindex="2">
                            Reset My Password Now&nbsp; <i class="fa fa-refresh"></i>
                        </button>
                    </div> <!-- /.form-group -->

                    <div class="form-group">
                        <a href="@url('member_login')"><i class="fa fa-angle-double-left"></i> &nbsp;Back to Login</a>
                    </div> <!-- /.form-group -->

                    <input type="hidden" value="<?=$i?>" name="i" />
                    <input type="hidden" value="<?=$k?>" name="k" />
                </form>

            </div> <!-- /.account-body -->

        </div>
    </div>
</div>
@endsection