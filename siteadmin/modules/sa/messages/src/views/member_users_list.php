@extends('master')
@section('site-container')

@asset::/member/profile/css/stylesheet.css

@view::_member_profile_header_nav


<div class="profile-users">
    <h1>Accounts</h1>

    <?php if(count($users) > 0) { ?>

        <div class="text-right">
            <a class="btn btn-primary" href="@url('member_createusers')">Add User</a>
        </div>

        <div class="list-view">
            <?php foreach($users as $user) { ?>
                <div class="list-view-item user">
                    <div class="row">
                        <div class="col-sm-8">
                            <p class="name"><?= sprintf('%s %s', $user['first_name'], $user['last_name']) ?></p>
                            <p class="username"><strong>Username: </strong><?= $user['username'] ?></p>

                            <div class="list-item-labels">
                                <?php if($user['is_primary']) { ?>
                                    <label class="label label-primary">Primary</label>
                                <?php } ?>

                                <?php if(!$user['is_active']) { ?>
                                    <label class="label label-danger">Inactive</label>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <a href="@url('member_editusernames', {'id':$user['id']})" class="btn btn-default btn-sm">Edit</a>
                            <a href="@url('member_deleteusernames', {'id':$user['id']})" class="btn btn-danger btn-sm">X</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="text-center">
            <h4>You haven't added any users yet.</h4>
            <a class="btn btn-primary" href="@url('member_createusers')">Add user</a>
        </div>
    <?php } ?>
</div>

@view::_member_profile_footer
@show