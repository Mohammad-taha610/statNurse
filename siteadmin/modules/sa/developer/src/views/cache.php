@extends('master')
@section('site-container')
<h3>Please select a cache to flush.</h3>

<form action="" method="POST">
    <?php
    foreach ($caches as $cache) {
        ?>
        <div class="form-group">
            <div class="col-sm-10">
                <label>
                    <input type="checkbox" name="cache[]" value="<?=$cache?>" />
                    <?=$cache?>
                </label>
            </div>
        </div>
        <?php

    }
    ?>
    <div class="form-group">
        <div class="col-sm-10">
            <input type="submit" value="Submit" class="btn" />
        </div>
    </div>
</form>
@show