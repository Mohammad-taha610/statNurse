@extends('master')
@section('site-container')
<pre style="margin-top: 25px"><?=! empty($output) ? $output : 'Please select a module to generate code for.'?></pre>


<h3>Select Module</h3>

<form action="@url('sa_developer_code_generation_exec')" method="get">
    <input type="hidden" value="" name="entity" />
<?php
foreach ($modules as $module) {
    ?>
    <div class="form-group">
        <div class="col-sm-10">
            <label>
                <input type="radio" name="module" value="<?=$module['module']?>" />
                 <?=$module['module']?>
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