<?php
ini_set('display_errors', 'On');
error_reporting('E_ALL');
setlocale(LC_ALL, "ru_RU.CP1251");
header("Content-Type: text/html; charset=windows-1251"); //charset=utf8");
define("DEBUG_PS", TRUE, TRUE);
define("DEBUG_MAIL_PS", "Denis_Sakhnov@south.rt.ru", TRUE);
//define("WP_MEMORY_LIMIT", "128M");
//$locate_glob = $_SERVER['REQUEST_URI'];

require_once 'db_connect.php';
require_once 'vlg_util.php';
?>
