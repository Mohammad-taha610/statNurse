<?php
use sacore\application\modRequest;
use sa\member\ViewHelper;

$sidebarLinks = modRequest::request('member.profile_sidebar_links');
$sidebarWidgets = modRequest::request('member.profile_sidebar_widgets');
$viewHelper = new ViewHelper();
?>
<div class="profile-sidebar">
    <nav id="profile-sidebar-nav" class="profile-nav" role="navigation">
        <?= $viewHelper->walkRecursiveNav($sidebarLinks) ?>
    </nav>

    <div class="sidebar-widgets">
        <?php
        if(is_array($sidebarWidgets)) {
            foreach($sidebarWidgets as $widget) {
                echo sprintf('<div class="sidebar-widget">%s</div>', $widget);
            }
        }
        ?>
    </div>
</div>