@extends('master')
@section('site-container')

@asset::/member/profile/css/stylesheet.css

<div class="profile-phone-numbers">
    <h1>Phone Numbers</h1>

    <?php if(count($phone_numbers) > 0) { ?>

        <?php if(empty($primary_phone)) { ?>
            <div class="alert alert-info">You haven't set a primary phone number yet!</div>
        <?php } ?>

        <div class="text-right">
            <a class="btn btn-primary" href="@url('member_createphone')">Add Phone</a>
        </div>

        <div class="list-view">
            <?php foreach($phone_numbers as $phone_number) { ?>
                <div class="list-view-item phone">
                    <div class="row">
                        <div class="col-sm-8">
                            <p class="street"><?= $phone_number['phone'] ?></p>

                            <div class="list-item-labels">
                                <?php if($phone_number['is_primary']) { ?>
                                    <label class="label label-primary">Primary</label>
                                <?php } ?>

                                <?php if(!$phone_number['is_active']) { ?>
                                    <label class="label label-danger">Inactive</label>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <a href="@url('member_editphone', {'id':$phone_number['id']})" class="btn btn-default btn-sm">Edit</a>
                            <a href="@url('member_deletephone', {'id':$phone_number['id']})" class="btn btn-danger btn-sm">X</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="text-center">
            <h4>You haven't added any phone numbers yet.</h4>
            <a class="btn btn-primary" href="@url('member_createphone')">Add phone</a>
        </div>
    <?php } ?>
</div>

@view::_member_profile_footer
@show