<?php #Version SA3 1.0.1
ini_set('max_input_vars', 3000);

$reqUri = $_SERVER['REQUEST_URI'];
if (strpos($reqUri, '/executive') === 0 ||
    strpos($reqUri, '/_wdt') === 0 ||
    strpos($reqUri, '/_profiler') === 0 ||
    strpos($reqUri, '/build') === 0) {
    $sitePath = dirname(__DIR__, 1);
    $_SERVER['SCRIPT_FILENAME'] = $sitePath.'/app/public/index.php';
    $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require("../app/public/index.php");
} else {
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      header("Access-Control-Allow-Origin: *"); 
      header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
      header("Access-Control-Allow-Headers: Content-Type, Authorization, Client-Identifier"); 
      header("Content-type: application/json; charset=utf-8"); 
      header("Access-Control-Request-Headers: Content-Type, Authorization, Client-Identifier"); 
      die();
  }
  require("../siteadmin/vendor/nursestat/sacore/src/application/bootstrap.php");
}
