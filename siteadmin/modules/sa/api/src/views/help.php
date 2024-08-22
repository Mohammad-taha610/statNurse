<?php

/** @var \sacore\application\route $route */
foreach($routes as $route) {
    ?>
    <div>
        <h3><?=$route['name']?></h3>
        <div><b>Endpoint:</b> <?=$route['route']?></div>
        <div><b>Action:</b> <?=$route['action']?></div>
        <div><b>Description:</b> <br /> <?=nl2br($route['description'])?></div>
    </div>
    <?php
}