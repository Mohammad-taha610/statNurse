@extends('master')
@section('site-container')

@asset::/member/profile/css/stylesheet.css

@view::_member_profile_header_nav

<div class="profile-email-numbers">
    <h1>Email Addresses</h1>

    <?php if(count($emails) > 0) { ?>

        <?php if(empty($primary_email)) { ?>
            <div class="alert alert-info">You haven't set a primary email number yet!</div>
        <?php } ?>

        <div class="text-right">
            <a class="btn btn-primary" href="@url('member_createemail')">Add email</a>
        </div>

        <div class="list-view">
            <?php foreach($emails as $email) { ?>
                <div class="list-view-item email">
                    <div class="row">
                        <div class="col-sm-8">
                            <p class="street"><?= $email['email'] ?></p>

                            <div class="list-item-labels">
                                <?php if($email['is_primary']) { ?>
                                    <label class="label label-primary">Primary</label>
                                <?php } ?>

                                <?php if(!$email['is_active']) { ?>
                                    <label class="label label-danger">Inactive</label>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <a href="@url('member_editemail', {'id':$email['id']})" class="btn btn-default btn-sm">Edit</a>
                            <a href="@url('member_deleteemail', {'id':$email['id']})" class="btn btn-danger btn-sm">X</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="text-center">
            <h4>You haven't added any emails yet.</h4>
            <a class="btn btn-primary" href="@url('member_createemail')">Add email</a>
        </div>
    <?php } ?>
</div>

@view::_member_profile_footer