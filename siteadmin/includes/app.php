<?php
#Version SA3 1.0.1
/* DETERMINE SITEADMINS INSTALL PATH */
$path = str_replace('\\', '/', __DIR__);
$pathArray = explode('/', $path);
$path = '';
foreach($pathArray as $pathpart) {
    $path .= $pathpart.DIRECTORY_SEPARATOR;
    if ($pathpart=='siteadmin') break;
}

include($path.'vendor/nursestat/sacore/src/application/bootstrap.php');