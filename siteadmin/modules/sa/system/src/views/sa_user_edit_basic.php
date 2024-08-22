<div id="edit-basic" class="tab-pane active">
    <div class="form-group">
        <h4 class="header blue bolder smaller">Basic Information</h4>
    </div>

    <div class="row">
        <div class="col-md-6 col-sm-12  col-xs-12">
            <div class="row">
                <div class="col-md-4 col-sm-12  col-xs-12">
                    <div class="form-group" style="margin-right: 0">
                        <label for="name">First Name</label>
                        <input type="text" class="form-control" id="first_name" placeholder="First Name" name="first_name" value="<?=$first_name?>">
                        <span class="text-muted"></span>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12  col-xs-12">
                    <div class="form-group" style="margin-right: 0">
                        <label for="name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" placeholder="Last Name" name="last_name" value="<?=$last_name?>">
                        <span class="text-muted"></span>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12  col-xs-12">
                    <div class="form-group" style="margin-right: 0">
                        <label for="name">Is Active</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option <?=$is_active ? 'selected' : ''?> value="1">Yes</option>
                            <option <?=$is_active==false ? 'selected' : ''?> value="0">No</option>
                        </select>
                        <span class="text-muted"></span>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-right: 0;">
                <label for="user_type">Is Super User</label>
                <select class="form-control" id="user_type" name="user_type">
                    <option <?=$user_type==1 ? 'selected' : ''?> value="1">Yes</option>
                    <option <?=$user_type==0 ? 'selected' : ''?> value="0">No</option>
                </select>
            </div>

            <div class="form-group" style="margin-right: 0;">
                <label for="cell_number">Cell Number</label>
                <input type="text" class="form-control" id="cell_number" placeholder="Cell Number" name="cell_number" value="<?=$cell_number?>">
                <span class="text-muted"><?= \sacore\application\app::get()->getConfiguration()->get("sa_device_verify")->getValue() ? 'Required for two factor sms authentication.' : '' ?></span>
            </div>

            <div class="form-group" style="margin-right: 0;">
                <label for="cell_number">Email Address</label>
                <input type="text" class="form-control" id="email_address" placeholder="Email Address" name="email" value="<?= $email ?>">
                <span class="text-muted"></span>
            </div>

            <div class="form-group" style="margin-right: 0;">
                <label for="title">Username</label>
                <input type="text" class="form-control" id="username" placeholder="Username" name="username" value="<?=$username?>">
                <span class="text-muted"></span>
            </div>

            <div class="form-group" style="margin-right: 0;">
                <label for="name">User Group</label>
                <select class="form-control" id="user_group" name="user_group">
                    <option <?= !$user_group ? 'selected' : ''?> value="0">No Group</option>
                    <?php foreach ($groups as $group) { ?>
                        <option <?=$group['name'] == $user_group['name'] ? 'selected' : ''?> value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                    <?php } ?>
                </select>
                <span class="text-muted"></span>
            </div>
            
            <button class="btn btn-info <?= (!empty($password) || !empty($confirm_password) || $id==0) ? 'saHidden' : ''?>" id="btn-change-password" type="button">
                <i class="fa fa-save bigger-110"></i>
                Change Password
            </button>

            <div id="password_container" class="<?= (!empty($password) || !empty($confirm_password) || $id==0) ? '' : 'saHidden'?>">

                <div class="row">
                    <div class="col-md-8 col-sm-12  col-xs-12">
                        <div class="form-group" style="margin-right: 0;">
                            <label for="title">Password</label>
                            <input type="password" data-minlength="8" class="form-control" id="password" placeholder="Password" name="<?= (!empty($password) || !empty($confirm_password) || $id==0) ? 'password' : ''?>" value="<?=$password?>">
                            <span class="text-muted"></span>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12  col-xs-12">
                        <div class="form-group" style="margin-right: 0;">
                            <label for="title">Strength</label>
                            <input type="text" readonly class="form-control passwordstrength" value="">
                        </div>
                        <div class=""></div>
                    </div>
                </div>

                <div class="form-group" style="margin-right: 0;">
                    <label for="title">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" placeholder="Confirm Password" name="<?= (!empty($confirm_password) || !empty($password) || $id==0) ? 'confirm_password' : ''?>" value="<?=$confirm_password?>">
                    <span class="text-muted"></span>
                </div>
            </div>

        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">

        </div>

    </div>
</div>
