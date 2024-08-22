<?php if($user_type != \sa\system\saUser::TYPE_SUPER_USER && ($cur_user_type == \sa\system\saUser::TYPE_SUPER_USER || $cur_user_type == \sa\system\saUser::TYPE_DEVELOPER)) { ?>
    <div id="edit-location-restrictions" class="tab-pane">
        <div class="form-group">
            <h4 class="header blue bolder smaller">Location Restrictions</h4>

            <div class="row">
                <div class="col-xs-12">
                    <input type="checkbox" name="is_location_restricted" value="1" <?= $is_location_restricted ? 'checked' : '' ?> />&nbsp; Restrict Login by Location
                </div>
            </div>
            <br><br>
            <div class="row">
                <div class="col-xs-12">
                    <h4>Allowed Locations:</h4>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-xs-12">
                    <select class="form-control" id="allowed-login-locations" name="allowed_login_locations[]" multiple="multiple">
                        <?php foreach($unique_login_ips as $ip) { ?>
                            <option value="<?= $ip ?>" <?= in_array($ip, $allowed_login_locations) ? 'selected' : '' ?>><?= $ip ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <br><br>
            <div class="row">
                <div class="col-xs-12 col-lg-4">
                    <div class="input-group">
                        <input class="form-control" id="add-ip-allowed-location-input" type="text" />
                        <span class="input-group-btn">
                                                <button type="button" id="add-ip-allowed-location-btn" class="btn btn-primary">Add Location by IP</button>
                                            </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>