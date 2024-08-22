<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
    <div class="module <?=$module['installed'] ? 'installed' : ''?> <?=$module['update'] ? 'update' : ''?>">
        <div class="row">
            <div class="col-xs-12 col-md-4">

                <div class="row">

                    <div class="col-xs-12 col-md-12  text-center">
                        <div class="image">
                            <?php
                            if ($module['extra']['store']['main-picture']) {
                                echo '<img width="42px" src="'.\sacore\application\app::get()->getRouter()->generate('sa_module_details_photo', ['file' => $module['extra']['store']['main-picture']]).'?version='.$module['version'].'&module='.$module['name'].'" /><br />';
                            } else {
                                ?>
                                <i class="fa fa-image fa-3x"></i> <br />
                                <?php
                            }
    ?>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-12  text-center">
                        <?php if ($module['update']) { ?>
                            <a class="btn btn-xs btn-primary" href="<?= \sacore\application\app::get()->getRouter()->generate('sa_module_update').'?module='.$module['name'].'&version='.$module['version']; ?>">Update</a>
                        <?php } elseif ($module['installed']) { ?>

                        <?php } else { ?>
                            <a class="btn btn-xs btn-primary" href="<?= \sacore\application\app::get()->getRouter()->generate('sa_module_buy').'?module='.$module['name'].'&version='.$module['version']; ?>"><?=$module['extra']['store']['price'] > 0 ? 'Buy $'.$module['extra']['store']['price'] : 'Install' ?></a>
                        <?php } ?>
                    </div>
                </div>


            </div>

            <div class="col-xs-12 col-md-8">

                <div class="row">

                    <div class="col-xs-12 col-md-12 name">

                        <div class="row">
                            <div class="col-xs-12">
                                <a href="<?= \sacore\application\app::get()->getRouter()->generate('sa_module_details').'?module='.$module['name'].'&version='.$module['version']; ?>"><?=! empty($module['extra']['store']['name']) ? $module['extra']['store']['name'] : $module['name']?></a>
                            </div>
                        </div>
                        <div class="row vendor">
                            <div class="col-xs-12">
                                <?=$module['name']?>
                            </div>
                        </div>
                        <div class="row version">
                            <div class="col-xs-12">
                                <?='Version: '.$module['version']?>
                            </div>
                        </div>
                        <?php
                        if ($module['version'] != $module['installed_version']) {
                            ?>
                            <div class="row version">
                                <div class="col-xs-12">
                                    <?= 'Installed Version: '.$module['installed_version'] ?>
                                </div>
                            </div>
                            <?php
                        }

    ?>
                    </div>


                </div>

                <div class="row">

                    <div class="col-xs-12 col-md-12 name">

                        <?php
    if (! empty($module['frozen_updates'])) {
        ?>
                            <div class="row frozen_updates">
                                <div class="col-xs-12">
                                    <i class="fa fa-exclamation-triangle"></i> The installed version of this module is not compatible with later versions of this module.
                                </div>
                            </div>
                            <?php
    }
    ?>

                    </div>

                </div>

            </div>


        </div>
    </div>
</div>