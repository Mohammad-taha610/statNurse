@extends('master')
@section('site-container')
<?php use sacore\application\app;?>
<form>Select a template from the <b><?=app::get()->getConfiguration()->get('theme')->getValue();?></b> theme: <select name="template">

<?php

    // Show list of templates
    if ($handle = opendir(app::getAppPath().'/themes/'.app::get()->getConfiguration()->get('theme').'/views/vtemplates/')) {
        while (false !== ($entry = readdir($handle))) {
            if (strpos($entry,'.php')) {
                $templateName = explode('.',$entry)[0];
                echo '<option value="' . $templateName . '">' . $templateName . '</option>';
            }
        }
        closedir($handle);
    }


?>
    </select><br><input type="submit" value="Generate Sprite CSS" class="btn btn-primary">
    <input type="hidden" name="randomKey" value="<?=md5(uniqid()) ?>">
    <br><br>
    <div class="page-header">
        <h1>Current Sprite</h1> 
    </div>
    <div class="well">
        <?php if(!file_exists(app::get()->getConfiguration()->get('public_directory')->getValue() . '/build/combined/sprite.png')) { ?>
            <p>Sprite file not generated</p>
        <?php } else { ?>
            <img src="/build/combined/sprite.png?<?=rand(0,999999) ?>">    
        <?php } ?>
    </div>

</form>
@show
