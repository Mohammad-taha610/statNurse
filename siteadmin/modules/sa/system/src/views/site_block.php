<?php
//$assetfolder = '/assets';
$assetfolder = '/themes/siteadmin/assets';
?>
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Site Administrator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="msapplication-TileColor" content="#438eb9">
    <meta name="msapplication-TileImage" content="<?=$assetfolder?>/images/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" sizes="57x57" href="<?=$assetfolder?>/images/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?=$assetfolder?>/images/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?=$assetfolder?>/images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?=$assetfolder?>/images/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?=$assetfolder?>/images/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?=$assetfolder?>/images/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?=$assetfolder?>/images/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?=$assetfolder?>/images/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?=$assetfolder?>/images/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="<?=$assetfolder?>/images/manifest.json">
    <link rel="stylesheet" href="<?=$assetfolder?>/css/bootstrap.css">
    <link rel="stylesheet" href="<?=$assetfolder?>/css/font-awesome.min.css">
    <script src="<?=$assetfolder?>/js/jquery.js"></script>
    <script src="<?=$assetfolder?>/js/bootstrap.min.js"></script>
    <script>
        if (window!= top) {top.location.href=location.href;}
    </script>
</head>
<body>

<div class="container">

    <div class="row">

        <div class="col-md-push-2 col-md-8">

            <div class="panel panel-default" style="margin-top: 35px">
                <div class="panel-heading">
                    <h3 class="panel-title">Site Blocked</h3>
                </div>
                <div class="panel-body">


                    <?php
                    $notify = new \sacore\utilities\notification();
                    $notify->showNotifications();
                    ?>

                    <div class="account-body">
                        <h4></h4>
                        <form class="form account-form" method="POST"  action="@url('site_block_login')">

                            <div class="text-center">
                                <span class="fa-stack fa-lg" style="font-size: 50px;">
                                  <i class="fa fa-circle fa-stack-2x" style="color: #bbbbbb"  > </i>
                                  <i class="fa fa-lock fa-inverse fa-stack-1x"></i>
                                </span>
                                <p>The site you are attempting to access is password protected.</p>
                            </div> <!-- /.form-group -->

                            <div class="form-group">
                                <label for="login-password">Password</label>
                                <input type="password" class="form-control" id="login-password" placeholder="Password" tabindex="2" name="password" value="<?=$password?>">
                            </div> <!-- /.form-group -->

                            <div class="form-group">
                                <input type="hidden" name="return_uri" value="<?= $return_uri ?>">

                                <button type="submit" class="btn btn-primary btn-block btn-lg" tabindex="4">
                                    Submit &nbsp; <i class="fa fa-play-circle"></i>
                                </button>
                            </div> <!-- /.form-group -->

                        </form>


                    </div> <!-- /.account-body -->


                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>