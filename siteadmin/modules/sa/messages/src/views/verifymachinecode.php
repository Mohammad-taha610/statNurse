@extends('member_guest')
@section('site-container')
<div class="account-wrapper">
    <div class="account-body">
        <div class="page-header">
            <h1>Verify Security Code</h1>
        </div>
        <form action="<?=$url->make('member_machineverifycodeverify')?>" method="post">
            <div class="row" style="margin-top:25px">
                <div class="col-sm-12">
                    <label>As an added level of security please enter the code you just received. <br /> Also you can enter a description of the computer your own to help you identify it for example "Home Computer".</label>
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
                    <div class="form-group">
                        <label for="Description">Description of Device</label>
                        <input type="text" name="description" class="form-control" id="Description" placeholder="">
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