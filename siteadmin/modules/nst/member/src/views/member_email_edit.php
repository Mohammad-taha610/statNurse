@extends('master')
@section('site-container')

@asset::/member/profile/css/stylesheet.css

<div class="profile-email-edit">
    <h1>Manage Email</h1>

    <div class="form-group text-right">
        <a href="@url('member_email_addresses')" class="btn btn-primary">My Email Addresses</a>
    </div>

    <form role="form" method="post" action="<?= $postRoute ?>">

        <!-- email -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" name="email" class="form-control" value="<?= $email ?>" title="Email">
        </div>
        <!-- end email -->

        <!-- type -->
        <div class="form-group">
            <p>What type of email is this?</p>

            <label>
                <input type="radio" name="type" value="personal" <?= $type == 'personal' ? 'checked' : '' ?>> Personal
            </label>

            <label>
                <input type="radio" name="type" value="work" <?= $type == 'work' ? 'checked' : '' ?>> Work
            </label>

            <label>
                <input type="radio" name="type" value="secondary" <?= $type == 'secondary' ? 'checked' : '' ?>> Secondary
            </label>

            <label>
                <input type="radio" name="type" value="other" <?= $type == 'other' ? 'checked' : '' ?>> Other
            </label>
        </div>
        <!-- end type -->

        <!-- is primary -->
        <div class="form-group">
            <label for="is_primary">
                <input type="checkbox" name="is_primary" value="1" title="Primary Email" <?= $is_primary ? 'checked' : '' ?>> This is my primary email.
            </label>
        </div>
        <!-- end is primary -->

        <!-- is active -->
        <div class="form-group">
            <label for="is_active">
                <input type="checkbox" name="is_active" value="1" title="Activate" <?= $is_active ? 'checked' : '' ?>> Activate this email
            </label>
        </div>
        <!-- end is active -->

        <div class="form-actions">
            <div class="row">
                <div class="col-md-offset-3 col-md-9">
                    <button class="btn btn-info" type="submit">
                        <i class="fa fa-save bigger-110"></i> Save
                    </button>

                    <button class="btn" type="reset">
                        <i class="fa fa-undo bigger-110"></i> Reset
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

@view::_member_profile_footer
@show