@extends('master')
@section('site-container')

@asset::/member/profile/css/stylesheet.css

<div class="profile-addresses">
    <h1>Addresses</h1>

    <?php if(count($addresses) > 0) { ?>

        <?php if(empty($primary_address)) { ?>
            <div class="alert alert-info">You haven't set a primary address yet!</div>
        <?php } ?>

        <div class="text-right">
            <a class="btn btn-primary" href="@url('member_createaddress')">Add Address</a>
        </div>

        <div class="list-view">
            <?php foreach($addresses as $address) { ?>
                <div class="list-view-item address">
                    <div class="row">
                        <div class="col-sm-8">
                            <p class="street"><?= sprintf('%s %s', $address['street_one'], $address['street_two']) ?></p>
                            <p class="location"><?= sprintf('%s, %s %s', $address['city'], $address['state'], $address['postal_code']) ?></p>
                            <div class="list-item-labels">
                                <?php if($address['is_primary']) { ?>
                                    <label class="label label-primary">Primary</label>
                                <?php } ?>

                                <?php if(!$address['is_active']) { ?>
                                    <label class="label label-danger">Inactive</label>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <a href="@url('member_editaddress', {'id':$address['id']})" class="btn btn-default btn-sm">Edit</a>
                            <a href="@url('member_deleteaddress', {'id':$address['id']})" class="btn btn-danger btn-sm">X</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="text-center">
            <h4>You haven't added any addresses yet.</h4>
            <a class="btn btn-primary" href="@url('member_createaddress')">Add Address</a>
        </div>
    <?php } ?>
</div>

@view::_member_profile_footer
@show