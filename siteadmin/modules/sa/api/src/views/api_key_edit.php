<?php

use sacore\application\app;

?>
@extends('master')

@section('site-container')
<form method="post"
      action="<?= app::get()->getRouter()->generate('api_v1_key_mgmt_save', ['id' => $id]) ?>"
      class="form-horizontal">
    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <label for="form-field-client_id" class="col-sm-2 control-label no-padding-right">Client Id: </label>
                <div class="col-sm-10">
                    <input class="col-xs-12 col-sm-10" type="text" id="form-field-client_id" placeholder="Client Id"
                           value="<?= $apiKey['client_id'] ?>" name="client_id">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">Platform: </label>
                <div class="col-sm-10">
                    <input class="col-xs-12 col-sm-10" type="text" id="form-field-platform" placeholder="Platform"
                           value="<?= $apiKey['platform'] ?>" name="platform">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">Is Active: </label>
                <div class="col-sm-10">
                    <select class="col-xs-12 col-sm-10" id="form-field-is_active" name="is_active">
                        <option <?= $apiKey['is_active'] == 0 ? 'selected' : '' ?> value="0">No</option>
                        <option <?= $apiKey['is_active'] == 1 ? 'selected' : '' ?> value="1">Yes</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">Type: </label>
                <div class="col-sm-10">
                    <select class="col-xs-12 col-sm-10" id="form-field-type" name="type">
                        <option <?= $apiKey['type'] == 'm' ? 'selected' : '' ?> value="m">Member Restricted</option>
                        <option <?= $apiKey['type'] == 'f' ? 'selected' : '' ?> value="f">Full Access</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">Entity Scope: </label>
                <div class="col-sm-10">
                    <select size="20" class="col-xs-12 col-sm-10" id="form-field-entity-scope" name="entityScope[]"
                            multiple="multiple">
                        <?php foreach ($entity_scope_options as $option) { ?>
                            <option <?= !empty($apiKey['entity_scope']) && in_array($option, $apiKey['entity_scope']) ? 'selected' : '' ?>
                                    value="<?= $option ?>" name=""><?= $option ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Form buttons -->
    <div class="row">
        <div class="col-xs-12">
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
        </div>
    </div>
</form>

@show