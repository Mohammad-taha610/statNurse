@extends('master')
@section('site-container')

<?php
    if ($modules) {
        ?>
        <form method="POST">
            <table class="table table-responsive">
            <?php

            foreach ($modules as $module => $classes) {
                ?>
                <tr><td>
                    <h3 data-toggle="collapse" href="#<?=$collapse?>Table" style="cursor: pointer; display: inline-block;"><?=$module ?></h3>
                    <input type="text" name="modules[<?=$module ?>]" value="<?=$reasons[$module] ?>" placeholder="Why was this module extended?" class="form form-control" style="margin-top: 15px; float: right; width: 50%;" maxlength="255" />
                    <div class="collapse" id="<?=$collapse?>Table">
                        <table class="table table-responsive table-striped">
                            <?php
                                foreach ($classes as $class) {
                                    ?>
                                    <tr><td><?=$class['class'] ?></td><td><?=$class['file'] ?></tr>
                                    <?php
                                }
                ?>
                        </table>
                    </div>
                </td></tr>
                <?php
            }

        ?>
            </table>
            <button class="btn btn-primary" style="margin: auto; display: block;" ><i class="fa fa-save"></i> Save</button>
        </form>
        <?php
    } else {
        echo 'No extended modules detected.';
    }

?>
@show
