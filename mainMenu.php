<?php
if (!defined('__ROOT__'))
	define('__ROOT__',dirname(__FILE__));
require_once (__ROOT__.'/vlg_util.php');
if (!defined('DBCONNECT'))
	require_once (__ROOT__.'/db_connect.php');
//require_once(__ROOT__.'/vlg_header.php'); 
/* */
echo sprintf("<link  rel=\"stylesheet\" type=\"text/css\" href=\"css/mainMenu.css\">");
function getCat($mysqli){
    $sqlStr="SELECT * FROM private_sector.menu";
    $res=qSQL($sqlStr);
    //Создаем масив где ключ массива является ID меню
    $cat = array();
    while($row = $res->fetch_assoc()){
        $cat[$row['id']] = $row;
    }
    return $cat;
}
//Функция построения дерева из массива от Tommy Lacroix
function getTree($dataset) {
    $tree = array();
    foreach ($dataset as $id => &$node) {   
    //Если нет вложений
        if (!$node['parent']){
            $tree[$id] = &$node;
        }else{
        //Если есть потомки то перебераем массив
            $dataset[$node['parent']]['childs'][$id] = &$node;
        }
    }
    return $tree;
}
//Получаем подготовленный массив с данными
$cat  = getCat($mysqli);
//Создаем древовидное меню
$tree = getTree($cat);



//$sqlStr="SELECT * FROM private_sector.menu";
//$result=qSQL($sqlStr);
//$menuArray=array();
//while($row=mysql_fetch_array($result)){
//	$pr=(int)$row["parent"];
//	$title=$row["title"];
//	$id=(int)$row["id"];
//	if (!isset($menuArray[$pr]))
//		$menuArray[$pr]=array();
//	$menuArray[$pr][]= array("id"=>$id,"title"=>$title);
//}
echo  "<pre>";
print_r($tree);
echo  "</pre>";
echo '<div id="container">';
echo" <nav>";

//foreach ($menuArray as  )
echo "</div> </nav>";
?>
