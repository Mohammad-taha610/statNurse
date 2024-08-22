<div class="account-wrapper">
    <div class="account-body">
        <form class="form account-form" method="POST"  action="<?php echo \sacore\utilities\url::make('member_login_post') ?>">
            <div class="form-group">
                <label for="login-username">Username</label>
                <input type="text" class="form-control" id="login-username" placeholder="Username" tabindex="1" name="username" value="<?=$username?>">
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" class="form-control" id="login-password" placeholder="Password" tabindex="2" name="password" value="<?=$password?>">
            </div>

            <div class="form-group col-xs-6">
                <button type="submit" class="btn btn-primary btn-block btn-lg" tabindex="3">
                    Sign In &nbsp; <i class="fa fa-play-circle"></i>
                </button>
            </div>
            <div class="account-footer col-xs-6">
                <a href="<?php echo \sacore\utilities\url::make('member_signup') ?>" class="">
                    <button class="btn btn-primary btn-block btn-lg">Sign Up</button></a>
            </div>
        </form>
    </div>
</div>