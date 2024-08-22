<?php

namespace sa\store;

/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 6/16/2017
 * Time: 2:03 PM
 */
interface IPostComposerTask
{
    public function getType();

    public function getMinimumVersion();

    public function install();

    public function update();

    public function downgrade();
    // public function always();
}
