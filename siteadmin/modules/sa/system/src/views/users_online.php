<div class="row">
    <div class="col-md-12 col-lg-9">
        <div class="infobox infobox-red">
            <div class="infobox-icon-sa3">
                <i class="ace-icon fa fa-users"></i>
            </div>
            <div class="infobox-data" title="Computers that have the site open in the last 1 minute.">
                <span class="infobox-data-number"><?=$users_count ? $users_count : '0' ?></span>
                <div class="infobox-content">User<?=$users_count == 1 ? '' : 's' ?> online.</div>
            </div>
        </div>

        <div class="infobox infobox-blue">
            <div class="infobox-icon-sa3">
                <i class="ace-icon fa fa-users"></i>
            </div>
            <div class="infobox-data" title="Computers that have the site open and have moved the mouse or changed pages in the last 1 minute.">
                <span class="infobox-data-number"><?=$active_users_count ? $active_users_count : '0' ?></span>
                <div class="infobox-content">Active user<?=$active_users_count == 1 ? '' : 's' ?> online.</div>
            </div>
        </div>
    </div>
</div>