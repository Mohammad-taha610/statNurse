@extends('master')
@section('site-container')

<div class="row">
    <div class="col-sm-12 col-md-9 col-md-offset-2 text-center">
        <p class="lead">Site Administrator modules are updated regularly to provide new features and performance upgrades. These updates can help improve stability, security, and add new features.</p>
    </div>
</div>
<hr>
<div class="tabbable" id="settings-tabs">
    <ul class="nav nav-tabs padding-16">
        <li class="active">
            <a data-toggle="tab" href="#modules"><i class="blue fa fa-th-large bigger-125"></i>Modules <?= $info['siteadmin-module-updates'] > 0 ? '<span class="badge badge-success">'.$info['siteadmin-module-updates'].'</span>' : '' ?></a>
        </li>
         <li class="">
            <a data-toggle="tab" href="#themes"><i class="blue fa fa-picture-o bigger-125"></i>Themes <?= $info['siteadmin-theme-updates'] > 0 ? '<span class="badge badge-success">'.$info['siteadmin-theme-updates'].'</span>' : '' ?></a>
        </li>
        <li class="">
            <a data-toggle="tab" href="#apis"><i class="blue fa fa-plug bigger-125"></i>APIs <?= $info['siteadmin-api-updates'] > 0 ? '<span class="badge badge-success">'.$info['siteadmin-api-updates'].'</span>' : '' ?></a>
        </li>
        <li class="">
            <a data-toggle="tab" href="#system"><i class="blue fa fa-plug bigger-125"></i>System <?= $info['system_updates'] > 0 ? '<span class="badge badge-success">'.$info['system_updates'].'</span>' : '' ?></a>
        </li>
        <?php

        if ($info['installed_updates'] > 0 || $info['system_updates'] > 0) {
            ?>

            <li class="pull-right">
                <a href="<?= \sacore\application\app::get()->getRouter()->generate('sa_module_updateAll')?>" class=" updateallbtn btn btn-primary <?=($info['installed_updates'] > 0 || $info['system_updates'] > 0) ? '' : 'saHidden'?> ">Update All</a>
            </li>

            <li class="pull-right">
                <a href="#">System Updates - <?=$info['system_updates']?></a>
            </li>

            <li class="pull-right">
                <a href="#">Module Updates - <?=$info['installed_updates']?></a>
            </li>



            <?php
        }
            ?>
    </ul>
    <div class="tab-content profile-edit-tab-content" style="padding: 25px !important;">
            <div id="modules" class="tab-pane active">

				<?php

                    foreach ($modules as $type => $subtypes) {
                        ?>
                    <div class="row store">

                        <?php if ($type != 'none') { ?>
                            <div class="page-header">
                                <h1>
                                    <?=$type?>
                                </h1>
                            </div>

                            <?php
                        }

                            foreach ($subtypes as $module) {
                                echo $self->subview('store_box', ['module' => $module]);
                            }

                        ?>
                    </div>
                    <?php
                    }
            ?>

            </div>


            <div id="themes" class="tab-pane">

                <?php
            foreach ($themes as $type => $subtypes) {
                ?>
                    <div class="row store">
                        <?php if ($type != 'none') { ?>
                            <div class="page-header">
                                <h1>
                                    <?=$type?>
                                </h1>
                            </div>

                            <?php
                        }

                    foreach ($subtypes as $module) {
                        echo $self->subview('store_box', ['module' => $module]);
                    }

                ?>
                    </div>
                    <?php
            }
            ?>


            </div>

            <div id="apis" class="tab-pane">

                <?php
            foreach ($apis as $type => $subtypes) {
                ?>
                    <div class="row store">
                        <?php if ($type != 'none') { ?>
                            <div class="page-header">
                                <h1>
                                    <?=$type?>
                                </h1>
                            </div>

                            <?php
                        }

                    foreach ($subtypes as $module) {
                        echo $self->subview('store_box', ['module' => $module]);
                    }

                ?>
                    </div>
                    <?php
            }
            ?>


            </div>

        <div id="system" class="tab-pane">

            <?php
            foreach ($system as $type => $subtypes) {
                ?>
                <div class="row store">
                    <?php if ($type != 'none') { ?>
                        <div class="page-header">
                            <h1>
                                <?=$type?>
                            </h1>
                        </div>

                        <?php
                    }

                    foreach ($subtypes as $module) {
                        echo $self->subview('store_box', ['module' => $module]);
                    }

                ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
@show