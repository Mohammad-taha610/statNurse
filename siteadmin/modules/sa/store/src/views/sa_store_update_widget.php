<div class="alert alert-block alert-<?=$type?> row">
    <div class="col-xs-12">
        <?=$message?>
        <?php if ($total_updates > 0) { ?>

        <a href="<?= \sacore\application\app::get()->getRouter()->generate('sa_store_check_for_updates') ?>" class="btn btn-xs btn-success pull-right"><i class="fa fa-check"></i> View Updates</a>

        <?php } ?>
    </div>
</div>
