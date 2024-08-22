@extends('master')
@section('site-container')

<div class="store_module_detail">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-3 col-ld-3">
            <div class="details-left">
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <div class="image">
                            <?php
                            if ($module['extra']['store']['main-picture']) {
                                echo '<img  src="'.\sacore\application\app::get()->getRouter()->generate('sa_module_details_photo', ['file' => $module['extra']['store']['main-picture']]).'?version='.$_REQUEST['version'], '?module='.$_REQUEST['module'].'" />';
                            } else {
                                ?>
                                <i class="fa fa-image fa-5x"></i>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="row buttonRow">
                    <div class="col-xs-12 text-center">

                        <?php if ($module['update']) { ?>
                            <button onclick="window.location.href='<?= \sacore\application\app::get()->getRouter()->generate('sa_module_update').'?module='.$module['name'].'&version='.$module['version_normalized']; ?>'" class="btn btn-pink">Update</button>
                        <?php } elseif ($module['installed']) { ?>
                            <button onclick="" class="btn btn-info">Installed</button>
                        <?php } else { ?>
                            <button onclick="window.location.href='<?= \sacore\application\app::get()->getRouter()->generate('sa_module_buy').'?module='.$module['name'].'&version='.$module['version_normalized']; ?>'" class="btn btn-info"><?=$module['extra']['store']['price'] > 0 ? 'Buy $'.$module['extra']['store']['price'] : 'Install' ?></button>
                        <?php }

                        if ($module['installed'] && $module['published']) { ?>
                            <button onclick="window.location.href='<?= \sacore\application\app::get()->getRouter()->generate('sa_module_uninstall').'?module='.$module['name'].'&version='.$module['version_normalized']; ?>'" class="btn btn-danger">Uninstall</button>
                        <?php }

                        ?>

                    </div>
                </div>
                <hr />
                <div class="row vendor">
                    <div class="col-xs-12">
                        <div class="">Module: <?=$module['extra']['store']['name']?></div>
                    </div>
                </div>
                <div class="row vendor">
                    <div class="col-xs-12">
                        Vendor: <?=$module['name']?>
                    </div>
                </div>
                <div class="row vendor">
                    <div class="col-xs-12">
                        Released: <?=\sacore\utilities\stringUtils::formatDate($module['time'])?>
                    </div>
                </div>
                <?php
                if (! empty($module['installed_version'])) {
                    ?>
                    <div class="row version">
                        <div class="col-xs-12">
                            Available Version: <?=$module['version_normalized']?>
                        </div>
                    </div>

                    <div class="row version">
                        <div class="col-xs-12">
                            <?= 'Installed Version: '.$module['installed_version'] ?>
                        </div>
                    </div>
                <?php
                } else {
                    ?>
                    <div class="row version">
                        <div class="col-xs-12">
                            Version: <?=$module['version_normalized']?>
                        </div>
                    </div>
                    <?php
                }
                            ?>
                <div class="row version">
                    <div class="col-xs-12">
                        Commit: <?=substr($module['dist']['reference'], -11, 11)?>
                    </div>
                </div>
                <div class="row version">
                    <div class="col-xs-12">
                        Other Versions: <a onclick="$('#versions').show(); $(this).hide()" href="#">Show</a>
                        <select id="versions" class="form-control saHidden">
                        <?php

                                    if (is_array($module['other_versions'])) {
                                        foreach ($module['other_versions'] as $version => $info) {
                                            echo '<option value="'.$version.'">'.$version.'</option>';
                                        }
                                    }

                            ?>
                        </select>
                    </div>
                </div>
                <div class="row version">
                    <div class="col-xs-12">
                        Requirements:
                        <?php
                            if (is_array($module['require'])) {
                                echo '<ul>';
                                foreach ($module['require'] as $package => $version) {
                                    echo '<li>'.$package.': '.$version.'</li>';
                                }
                                echo '</ul>';
                            }

                            ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-9 col-ld-9">

            <div class="row">
                <div class="col-xs-12">
                    <div class="storelabel">Description</div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="description"><?=empty($module['description']) ? 'No Description Available' : $module['description']?></div>
                </div>
            </div>

            <?php
            if (is_array($module['extra']['store']['pictures'])) {
                ?>

            <div class="row">
                <div class="col-xs-12">
                    <div class="storelabel">Screenshots</div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php

                            foreach ($module['extra']['store']['pictures'] as $pic) {
                                echo '<img data-toggle="modal" data-target="#myImageModal-'.preg_replace('/[^0-9a-zA-Z]/', '', $pic).'" height="140px" src="'.\sacore\utilities\url::make('sa_module_details_picture', $pic, '?version='.$_REQUEST['version'], '?module='.$_REQUEST['module']).'" />';
                            }
                ?>
                </div>
            </div>

            <?php } ?>

        </div>
    </div>

</div>

<?php
if (is_array($module['extra']['store']['pictures'])) {
    foreach ($module['extra']['store']['pictures'] as $pic) {
        ?>
        <div class="modal fade" id="myImageModal-<?= preg_replace('/[^0-9a-zA-Z]/', '', $pic) ?>" tabindex="-1"
             role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog storeImageModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="myModalLabel">Site Administrator</h4>
                    </div>
                    <div class="modal-body">
                        <img width="80%"
                             src="<?= \sacore\utilities\url::make('sa_module_details_picture', $pic, '?version='.$_REQUEST['version'], '?module='.$_REQUEST['module'])?>"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
}

                            ?>

<script>

    $('#versions').change( function() {

        window.location.href = '<?= \sacore\application\app::get()->getRouter()->generate('sa_module_details'); ?>?module=<?=$_REQUEST['module']?>&version='+$(this).val()


    })


</script>
@show