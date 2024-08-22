@extends('master')
<?php
?>
@section('site-container')
<div class="row">
    <div class="col-xs-12">
        <form id="profile-form" class="form-horizontal" method="POST" action="@url('sa_events_category_save')">
            <div class="tabbable">
                <ul class="nav nav-tabs padding-16">
                    <li class="active">
                        <a data-toggle="tab" href="#edit-basic">
                            <i class="blue fa fa-edit bigger-125"></i>
                            Basic Info
                        </a>
                    </li>
                    <li class="">
                        <a data-toggle="tab" href="#edit-permissions">
                            <i class="red fa fa-lock bigger-125"></i>
                            Permissions
                        </a>
                    </li>

                    <?php if ($other_tabs) {
                        foreach ($other_tabs as $tab) { ?>
                    <li class="">
                        <a data-toggle="tab" href="#<?= $tab['id'] ?>">
                            <i class="blue <?= $tab['icon'] ?> bigger-125"></i>
                            <?= $tab['name'] ?>
                        </a>
                    </li>
                    <?php
                        }
                    }
?>
                </ul>

                <div class="tab-content profile-edit-tab-content">
                    <div id="edit-basic" class="tab-pane active">
                        <div class="form-group">
                            <h4 class="header blue bolder smaller">Basic Info</h4>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group" style="margin-right: 0">
                                    <label for="category">Category Name</label>
                                    <input name="name" value="<?=$category->getName()?>" class="form-control" id="category">
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="form-group" style="margin-right: 0">
                                    <label for="description">Category Description</label>
                                    <textarea name="description" class="form-control" ><?=$category->getDescription()?></textarea>
                                </div>
                            </div>
                            <input type="hidden" value="<?= $category->getId() ?>" id="category_id" name="id"/>
                        </div>
                    </div>

                    <div id="edit-permissions" class="tab-pane">
                        <div class="form-group">
                            <h4 class="header blue bolder smaller">Permissions</h4>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12  col-xs-12">
                                <div class="form-group" style="margin-right: 0">
                                <label for="access_groups">What groups of members have access to this category?</label>
                                <select class="form-control" name="access_groups[]" multiple style="height: 250px">
                                    <option value="E" <?= in_array('E', $access_groups) || count($access_groups) == 0 ? 'selected="selected"' : ''?>>Everyone</option>
                                    <option value="M" <?= in_array('M', $access_groups) ? 'selected="selected"' : ''?>>Every Member</option>
                                    <option value="G" <?= in_array('G', $access_groups) ? 'selected="selected"' : ''?>>Every Guest (General public &amp; members who have not signed on)</option>
                                    <?php
                foreach ($groups as $group) {
                    ?>
                                        <option value="<?=$group['id']?>" <?= in_array($group['id'], $access_groups) ? 'selected="selected"' : ''?>><?=$group['name']?></option>
                                        <?php
                }
?>
                                </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($other_tabs as $tab) { ?>
                    <div id="<?= $tab['id'] ?>" class="tab-pane">
                        <div class="form-group">
                            <h4 class="header blue bolder smaller"><?= $tab['name'] ?></h4>
                        </div>
                        <?= $tab['html'] ?>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <div class="clearfix form-actions">
                <div class="col-md-offset-3 col-md-9">
                    <button class="btn btn-info" type="submit">
                        <i class="fa fa-save bigger-110"></i>
                        Save
                    </button>

                    &nbsp; &nbsp;
                    <button class="btn" type="reset">
                        <i class="fa fa-undo bigger-110"></i>
                        Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>