@extends('master')
@section('site-container')

<h3>Please select an object to export. This process is limited to the first 100 objects in each table.</h3>

<form action="@url('sa_developer_object_export_zip')" method="POST">
    <?php
    foreach ($entities as $entity) {
        ?>
        <div class="form-group">
            <div class="col-sm-10">
                <label>
                    <input type="checkbox" name="entity[]" value="<?=$entity?>" />
                    <?=$entity?>
                </label>
            </div>
        </div>
        <?php

    }
    ?>
    <div class="form-group">
        <div class="col-sm-10">
            <input type="hidden" value="orm_entities" name="c" />
            <input type="submit" value="Submit" class="btn" />
        </div>
    </div>
</form>
@show