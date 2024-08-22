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
										<a href="/member/login"><img class="img-fluid" src="/themes/nst/assets/images/white-logo.png" alt=""></a>
									</div>
                                    <form id="loginForm" method="POST">
                                        <?php
                                        $notification = new \sacore\utilities\notification();
                                        $notification->showNotifications();
                                        ?>
                                        <div class="form-group">
                                            <label class="mb-1 text-white"><strong>Username</strong></label>
                                            <input id="login-username-input" type="text" name="username" class="form-control" placeholder="hello@example.com">
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1 text-white"><strong>Password</strong></label>
                                            <input id="login-password-input" type="password" name="password" class="form-control" placeholder="********">
                                        </div>
                                        <div class="form-row d-flex justify-content-between mt-4 mb-2">
                                            <div class="form-group">
                                               <div class="custom-control custom-checkbox ml-1 text-white">
													<input type="checkbox" class="custom-control-input" id="basic_checkbox_1">
													<label class="custom-control-label" for="basic_checkbox_1">Remember my preference</label>
												</div>
                                            </div>
                                            <div class="form-group">
                                                <a class="text-white" href="<?=\sacore\application\app::get()->getRouter()->generate('member_reset')?>">Forgot Password?</a>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn bg-white text-primary btn-block">Sign In</button>
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

</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            const form = document.createElement('form');
            document.body.appendChild(form);

            // Set the form action and method
            form.method = 'POST'; // Update with your form method

            const username = document.getElementById('login-username-input').value;
            const password = document.getElementById('login-password-input').value;

            const usernameInput = document.createElement('input');
            usernameInput.type = 'hidden';
            usernameInput.name = 'username';
            usernameInput.value = username;
            form.appendChild(usernameInput);

            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = password;
            form.appendChild(passwordInput);

            // Submit the new form
            modRequest.request('member.get_member_type', {}, {
                username: document.getElementById('login-username-input').value,
            }, function (response) {
                if (response.memberType === 'Executive') {
                    form.action = '/executive/login';
                }
                else {
                    form.action = "@url('member_login_post')";
                }
                form.submit();
            });
        });
    });
</script>
</html>
