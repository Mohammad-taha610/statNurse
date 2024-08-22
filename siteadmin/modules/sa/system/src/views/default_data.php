@extends('master')
@section('site-container')
<h3>Please select at least one module to import.</h3>

<form action="@url('sa_default_data_import')" method="POST">
    <?php
    foreach($modules as $module) {

        ?>
        <div class="form-group">
            <div class="col-sm-10">
                <label>
                    <input type="checkbox" name="module[]" value="<?=$module['module']?>" />
                    <?=$module['module']?>
                </label>
            </div>
        </div>
        <?php

    }
    ?>
    <div class="form-group">
        <div class="col-sm-10">
            <input type="submit" value="Import" class="btn" />
        </div>
    </div>
</form>
@show