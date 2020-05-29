<?php
if (!defined('__ROOT__'))
	define('__ROOT__',dirname(__FILE__));
require_once (__ROOT__.'/vlg_util.php');
if (!defined('DBCONNECT'))
	require_once (__ROOT__.'/db_connect.php');
//require_once(__ROOT__.'/vlg_header.php'); 
/* */
echo sprintf("<link  rel=\"stylesheet\" type=\"text/css\" href=\"css/mainMenu.css\">");
$sqlStr="SELECT * FROM private_sector.menu";
$result=qSQL($sqlStr);
$menuArray=array();
while($row=mysql_fetch_array($result)){
	$pr=(int)$row["parent"];
	$title=$row["title"];
	$id=(int)$row["id"];
	if (!isset($menuArray[$pr]))
		$menuArray[$pr]=array();
	$menuArray[$pr][]= array("id"=>$id,"title"=>$title);
}
echo  "<pre>";
print_r($menuArray);
echo  "</pre>";
echo '<div id="container">';
echo" <nav>";

//foreach ($menuArray as  )
echo "</div> </nav>";
?>
