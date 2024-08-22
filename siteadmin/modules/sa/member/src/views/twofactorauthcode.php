@extends('member_guest')
@section('site-container')
<div class="account-wrapper">
    <div class="account-body">
        <div class="page-header">
            <h1>Additional Authentication Required</h1>
        </div>
        <form action="<?=$url->make('member_two_factor_verify_user_input_validate')?>" method="post">
            <div class="row" style="margin-top:25px">
                <div class="col-sm-12">
                    <label>
                        <?php if ($method=='google_auth') { ?>
                            As an added level of security please enter the code from the Google Authenticator App.
                        <?php } else { ?>
                            As an added level of security please enter the code you just received.
                        <?php } ?>
                    </label>
                </div>
            </div>
            <div class="row" style="margin-top:25px">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="code">Security Code</label>
                        <input type="password" name="code" class="form-control" id="code" placeholder="">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <input type="submit" class="btn btn-info" value="submit" />
                </div>
            </div>
        </form>
    </div>
</div>
@show