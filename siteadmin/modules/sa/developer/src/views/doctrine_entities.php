
@extends('master')
@section('site-container')
@view::doctrine
<form action="@url('sa_developer_doctrine_execute')" method="get">
    <input type="hidden" value="" name="entity" />
<?php
foreach ($entities as $entity) {
    ?>
    <div class="form-group">
        <div class="col-sm-10">
            <label>
                <input type="radio" name="entity" value="<?=$entity?>" />
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