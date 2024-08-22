<?php
use sacore\application\modRequest;

$sidebarLinks = modRequest::request('member.profile_sidebar_links', array());
//$sidebarWidgets = modRequest::request('member.profile_sidebar_widgets');
?>

<!-- Still need to hook up the widgets to this -->
<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">
        <?php foreach ($sidebarLinks as $link) {
            // disable "Invoices" for now
            if ($link['label'] === 'Invoices')
                continue;
            ?>
            <li><a class="<?= $link['children'] ? 'has-arrow' : '' ?> ai-icon" href="<?= $link['link'] ? $link['link'] : 'javascript:void(0)'?>">
                    <i class="<?= $link['icon'] ?>"></i>
                    <span class="nav-text"><?= $link['label'] ?></span>
                </a>
                <?php if ($link['children']) { ?>
                    <ul aria-expanded="false">
                    <?php foreach ($link['children'] as $child) { ?>
                        <li><a href="<?= $child['link'] ?>"><?= $child['label'] ?></a></li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </li>
        <?php } ?>
        </ul>
        
        <div class="copyright">
            <p>© <?= date('Y') ?> <strong>NurseStat LLC.</strong> All Rights Reserved</p>
        </div>
    </div>
</div>