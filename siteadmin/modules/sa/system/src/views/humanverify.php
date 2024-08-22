@view::header
<div class="page-content">
    <div class="page-header">
        <h1>Verify Login</h1>
    </div>


    <script type="text/javascript">
        var RecaptchaOptions = {
            theme : 'clean'
        };
    </script>

    <div style="width:439px; margin:auto; padding-top:50px">
        <?php
        $notify = new \sacore\utilities\notification();
        $notify->showNotifications();
        ?>
        We have detected 4 or more invalid login attempts from this computer. Please fill in the code below. We do this to make sure someone isn't attempting to hack your account. Sorry for any inconvenience.
        <br /><br />
        <form action="<?="@url('sa_humanverifypost')"?>" method="post">
            <?php
            echo $recaptchaHTML;;
            ?>
            <br/>
            <input type="submit"  class="btn btn-info" value="submit" />
        </form>
    </div>

</div>
@view::footer