<div class="animated fadeInDown">
    <div class="saSignInHeader"><?php
    if ($siteadmin_login_image_id) {
        /** @var \sa\saFiles\saImage $image */
        $image = \sacore\application\ioc::get('saImage', $siteadmin_login_image_id);
        if ($image) {
            $siteadmin_image_path = \sacore\application\app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $image->getFolder(), 'file' =>$image->getFilename()]);
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
            <?php
            /*
            <!-- <img class="profile-img-card" src="//lh3.googleusercontent.com/-6V8xOA6M7BA/AAAAAAAAAAI/AAAAAAAAAAA/rzlHcD0KYwo/photo.jpg?sz=120" alt="" /> -->
            <!-- <img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" /> -->
            <p id="profile-name" class="profile-name-card"></p>
            */
            
            if ($error) { ?>
                <div class="error">
                    <h3>Authentication Error</h3>
                    <ul>
                        <li>Invalid username and password combination</li>
                    </ul>
                </div>
            <?php } ?>
            <form class="form-signin" id="saSignInForm" name="login" method="post" action="<?=$post_action?>">
                <div class="form-group text-center saSignInText">
                   To sign in, please enter your username and password.
                </div>
                <?php
                /*                
                <span id="reauth-email" class="reauth-email"></span>
                */
                ?>
                <input type="text" id="inputUsername"  name="username" class="form-control"  value="<?=$username?>" autocapitalize="off" placeholder="Username" required autofocus>
                <input type="password" id="inputPassword" name="password" class="form-control"  autocomplete="off" onClick="document.getElementById('inputPassword').value = ''; return false;" placeholder="Password" required>
                <?php
                /*
                <div id="remember" class="checkbox">
                    <label>
                        <input type="checkbox" value="remember-me"> Remember me
                    </label>
                </div>
                */
                ?>
                <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit">Sign in</button>
            </form>
            <?php /* <a href="#" class="forgot-password">Forgot my password?</a> */ ?>
        </div>
    </div>
    <div class="saSignInFooterCopyright">&copy; <?php echo date("Y");?> Site Administrator</div>
</div>

<script>
<?php
if ($siteadmin_login_bg) {
    /** @var \sa\saFiles\saImage $image */
    $image = \sacore\application\ioc::get('saImage', $siteadmin_login_bg);
    if ($image) {
        $siteadmin_image_path = \sacore\utilities\url::make('files_browser_view_file', $image->getFolder(), $image->getFilename());
        ?>
        var background = "#FFFFFF url('<?=$siteadmin_image_path ?>') no-repeat top center";
        $('body').addClass('custom-bg');
        <?php
    }
    else
    {
        ?>
        var background = "#FFFFFF url('/siteadmin/system/images/sa_login_background.jpg') no-repeat top center";
        <?php
    }
    ?>
    $('body').css('background',background);
    <?php
}
?>
</script>
