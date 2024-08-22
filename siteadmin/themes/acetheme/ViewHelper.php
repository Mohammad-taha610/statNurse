<?php
/**
 * Date: 2/25/2016
 *
 * File: ViewHelper.php
 */

namespace themes\acetheme;

use sacore\application\app;
use sacore\application\navItem;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

abstract class ViewHelper {
    public static function sidenav_output($menuItem, $class)
    {
        self::sidenav_recursive_output($menuItem['siteadmin_root'], $class);
    }

    public static function sidenav_recursive_output($menuItem, $class=null)
    {
        if(app::get()->getConfiguration()->get('hidden_sa_modules')){
            $hiddenModuleList = explode(',', strtolower(str_replace(' ', '', app::get()->getConfiguration()->get('hidden_sa_modules')->getValue())));
        } else {
            $hiddenModuleList = [];
        }

        echo '<ul class="'.$class.'">';

        /**
         * @var navItem $item
         */
        foreach ($menuItem->children as $k=>$item) {

            $hasSubmenu = count($item->children)>0 ? true : false;

            if (!$item->is_accessible && !empty($item->routeid) || in_array(strtolower($item->name), $hiddenModuleList))
                continue;

            try {
                $route_path = !empty($item->routeid) ? app::get()->getRouter()->generate($item->routeid) : $item->route;
            }
            catch(RouteNotFoundException $e) {

            }

            ?>

        <li class=" <?= ($item->is_current) ? 'active' : '' ?>  <?= $item->is_open ? 'active open' : '' ?> ">
            <a style="cursor:pointer" class="<?= $hasSubmenu ? 'dropdown-toggle' : '' ?>" href="<?= $route_path ?>" >
                <i class="<?= $item->icon ?>"></i>
                <span class="menu-text"> <?= $item->name ?> </span>

                <?php if ($hasSubmenu) { ?>
                    <b class="arrow fa fa-angle-down"></b>
                <?php } ?>
            </a>


            <?php

            self::sidenav_recursive_output($item, 'submenu');

            echo '</li>';

        }

        echo '</ul>';

    }
}
