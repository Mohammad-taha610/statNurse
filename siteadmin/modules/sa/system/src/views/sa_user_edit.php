@extends('master')
@section('site-container')

@asset::/siteadmin/system/js/pwstrength.js
@asset::/siteadmin/system/js/bootstrap-multiselect.js
@asset::/siteadmin/system/css/bootstrap-multiselect.css

<div class="row">
    <div class="col-xs-12">
        <form  id="edit_form" class="form-horizontal" method="POST" action="@url('sa_sausers_save', {'id':$id})">
            <div class="tabbable">
                <ul class="nav nav-tabs padding-16">
                    <li class="active">
                        <a data-toggle="tab" href="#edit-basic">
                            <i class="green fa fa-edit bigger-125"></i>
                            Basic Information
                        </a>
                    </li>
                    <?php use sacore\application\app;

                    if ($id && $permissions['system']['system_manage_permissions'] || $cur_user_type == \sa\system\saUser::TYPE_SUPER_USER || $cur_user_type == \sa\system\saUser::TYPE_DEVELOPER) { ?>
                        <li>
                            <a data-toggle="tab" href="#edit-permissions">
                                <i class="red fa fa-bars bigger-125"></i>
                                Permissions
                            </a>
                        </li>
                    <?php } ?>
                    <li>
                        <a data-toggle="tab" href="#edit-approved-devices">
                            <i class="purple fa fa-desktop bigger-125"></i>
                            Approved Devices
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#edit-login-history">
                            <i class="blue fa fa-history bigger-125"></i>
                            Login History
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#edit-google-authenticator">
                            <i class="blue fa fa-google bigger-125"></i>
                            Google Authenticator
                        </a>
                    </li>

                    <?php if($user_type != \sa\system\saUser::TYPE_SUPER_USER && ($cur_user_type == \sa\system\saUser::TYPE_SUPER_USER || $cur_user_type == \sa\system\saUser::TYPE_DEVELOPER)) { ?>
                        <li>
                            <a data-toggle="tab" href="#edit-location-restrictions">
                                <i class="blue fa fa-globe bigger-125"></i>
                                Location Restrictions
                            </a>
                        </li>
                    <?php } ?>
                </ul>

                <div class="tab-content profile-edit-tab-content">
                    @view::sa_user_edit_basic
                    @view::sa_user_edit_permissions
                    @view::sa_user_edit_devices
                    @view::sa_user_edit_login_history
                    @view::sa_user_edit_authenticator
                    @view::sa_user_edit_location_restrictions









                    
                    @view::sa_tab_table
                </div>
            </div>

            <div class="clearfix form-actions">
                <div class="col-md-offset-3 col-md-9">
                    <button class="btn btn-info" type="submit">
                        <i class="fa fa-save bigger-110"></i>
                        Save
                    </button>

                    &nbsp; &nbsp;
                    <button class="btn" onclick="history.back()" >
                        <i class="fa fa-undo bigger-110"></i>
                        Back
                    </button>
                </div>
            </div>
            <input type="hidden" id="form-field-pass1" name="sa_member_id" value="<?=$sa_member_id?>">
        </form>
    </div>
</div>

<script>

    $('#btn-issue-ga-code').click( function() {
        modRequest.request('sa.verify.issue_ga_code', null, { id: <?=$id?> }, function(d) {

            $('#ga_information').html('Secret Key: <br /><br />'+ d.code +' <br /><br />\
                OR\
                <br /><br />\
                Scan the QR Code below:\
                <br />\
                <br />\
                <img src="'+ d.qr_image +'" />');
        });
    });

    $('#btn-change-password').click( function() {

        $(this).hide();
        $('#password_container').show();

        $('#password').attr('name', 'password');
        $('#confirm_password').attr('name', 'confirm_password');
    });

    $(document).ready( function() {

        var hash = window.location.hash.substring(1);
        if (hash!='') {
            $('a[href="#'+hash+'"]').click();
        }

        $('#allowed-login-locations').multiselect({
            buttonClass: 'btn btn-primary',
            buttonWidth: '200px'
        });

        $('#add-ip-allowed-location-btn').click(function() {
            var value = $('#add-ip-allowed-location-input').val();

            var html = '';
            html += '<option value="' + value + '" selected>' + value + '</option>';

            $('#allowed-login-locations').append(html);
            $('#allowed-login-locations').multiselect('rebuild');

            $('#add-ip-allowed-location-input').val('');
        });

    });

</script>
@show