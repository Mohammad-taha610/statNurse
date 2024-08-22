<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>NurseStat LLC | Medical Staffing Agency</title>
    <link rel="icon" type="image/png" sizes="16x16" href="/themes/nst/assets/images/favicon.png">
    <link href="/themes/nst/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="/themes/acetheme/assets/css/jquery.growl.css">
    <script src="/themes/acetheme/assets/js/jquery.js"></script>
    <script src="/themes/acetheme/assets/js/jquery.growl.js"></script>

    <style>
        span.error {
            color: #ff4f4f;
            display: none;
        }
    </style>
</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <div class="text-center mb-3">
                                        <a href="/member/register"><img class="img-fluid" src="/themes/nst/assets/images/white-logo.png" alt=""></a>
                                    </div>
                                    <form method="POST" action="@url('member_register_post')">
                                        <?php
                                        $notification = new \sacore\utilities\notification();
                                        $notification->showNotifications();
                                        ?>
                                        <div class="row">
                                            <div class="form-group col-xl-6">
                                                <label class="mb-1 text-white" for="first_name"><strong>First Name</strong></label>
                                                <input id="register-first_name-input" type="text" name="first_name" class="form-control" placeholder="John" required>
                                                <span class="error" id="register-first_name-error">This field is required</span>
                                            </div>
                                            <div class="form-group col-xl-6">
                                                <label class="mb-1 text-white" for="last_name"><strong>Last Name</strong></label>
                                                <input id="register-last_name-input" type="text" name="last_name" class="form-control" placeholder="Doe" required>
                                                <span class="error" id="register-last_name-error">This field is required</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-xl-6">
                                                <label class="mb-1 text-white" for="email"><strong>Email</strong></label>
                                                <input id="register-email-input" type="email" name="email" class="form-control" placeholder="hello@example.com" required>
                                                <span class="error" id="register-email-error">This field is required</span>
                                            </div>
                                            <div class="form-group col-xl-6">
                                                <label class="mb-1 text-white" for="phone"><strong>Mobile Phone</strong></label>
                                                <input id="register-phone-input" type="phone" name="phone" class="form-control" placeholder="123-456-7890">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1 text-white" for="password"><strong>Password</strong></label>
                                            <input id="register-password-input" type="password" name="password" class="form-control" placeholder="********" autocomplete="new-password" required>
                                            <span class="error" id="register-password-error">This field is required</span>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1 text-white"><strong>Repeat Password</strong></label>
                                            <input id="register-password-input" type="password" name="password2" class="form-control" placeholder="********" autocomplete="new-password" required>
                                            <span class="error" id="register-password2-error">This field is required</span>
                                        </div>
                                        <div class="form-row d-flex justify-content-between mt-4 mb-2">
                                            <div class="ml-1 form-group">
                                                <a class="text-primary" href="<?= \sacore\application\app::get()->getRouter()->generate('member_login') ?>">Sign In</a>
                                            </div>
                                            <div class="mr-2 form-group">
                                                <a class="text-white" href="<?= \sacore\application\app::get()->getRouter()->generate('member_reset') ?>">Forgot Password?</a>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn bg-white text-primary btn-block">Register</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/themes/nst/assets/vendor/global/global.min.js"></script>
    <script src="/themes/nst/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="/themes/nst/assets/js/custom.min.js"></script>
    <script src="/themes/nst/assets/js/deznav-init.js"></script>
    <script src="/themes/nst/assets/js/jquery.validate.min.js"></script>

    <script>
        $(document).ready(function() {
            /**
             * Validate the form
             */
            $("form").validate({
                errorElement: "span"
            });
        })
    </script>

</body>

</html>