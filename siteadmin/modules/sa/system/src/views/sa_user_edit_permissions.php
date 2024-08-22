<?php if ($id && $permissions['system']['system_manage_permissions'] || $cur_user_type == \sa\system\saUser::TYPE_SUPER_USER || $cur_user_type== \sa\system\saUser::TYPE_DEVELOPER) { ?>
    <div id="edit-permissions" class="tab-pane">
        <div class="form-group">
            <h4 class="header blue bolder smaller">Permissions</h4>
        </div>

        <div class="row">
            <?php foreach($availablePermissions as $module => $module_permissions) {

                if (count($module_permissions)==0)
                    continue;
                ?>

                <div class="col-md-3 col-sm-12 col-xs-12">
                    <h4 class="blue"><?=ucwords($module)?></h4>
                    <div class="well" style="">
                        <?php
                        if ( is_array($module_permissions) && count($module_permissions)>0 ) {
                            foreach ($module_permissions as $key => $name) { ?>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" value="1" <?= $permissions[$module][$key] ? 'checked' : '' ?>  name="permissions[<?=$module?>][<?=$key?>]"> <?= ucwords($name) ?>
                                    </label>
                                </div>
                            <?php }
                        } else { ?>
                            <div>Permissions have not been setup for this module.</div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>