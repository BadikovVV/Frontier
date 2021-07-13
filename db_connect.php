<?php
ini_set('display_errors','On');
error_reporting('E_ALL');
/* Соединение, выбор БД */
list($msec,$sec)=explode(chr(32),microtime());
$headtime=$sec+$msec;
$mySQLSchema='private_sector';
$fp = fopen($_SERVER['DOCUMENT_ROOT'].'/../private/goto.txt', 'r');
$fpRes = fgets($fp,40);
fclose($fp);
// * * * mysql * * * //
/*try{
        $link = mysql_connect("localhost","ps",$fpRes) ;
        mysql_select_db($mySQLSchema) or die ("Could not select database");
        mysql_query("SET NAMES 'cp1251'");
        mysql_query("SET CHARACTER SET 'cp1251'");
}catch (Exception $e)
{
	echo ("\n We have a bug". $e->getMessage()."\n");
}
*/
/*mysql_select_db($mySQLSchema) or die ("Could not select database");
mysql_query("SET NAMES 'cp1251'");
mysql_query("SET CHARACTER SET 'cp1251'");
*/
//mysql_query("SET NAMES 'UTF8'");
//mysql_query("SET CHARACTER SET 'UTF8'");
$gzp_path = "http://10.147.2.125/";
$file_path = "/var/www/html/";
// * * * mysqli * * * //
global $mysqli;
$mysqli = new mysqli('localhost','ps',trim($fpRes),$mySQLSchema);
if ($mysqli->connect_error) {
    error_log('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    echo 'Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error." passwd =  ". $fpRes." ,";
    exit("\nmysqli connect error");
}
else
	define('DBCONNECT',1);
$mysqli->autocommit(FALSE);
$mysqli->set_charset("cp1251");
//
list($msec,$sec)=explode(chr(32),microtime());
?>
