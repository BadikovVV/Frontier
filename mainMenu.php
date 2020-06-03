<?php
if (!defined('__ROOT__'))
	define('__ROOT__',dirname(__FILE__));
require_once (__ROOT__.'/vlg_util.php');
if (!defined('DBCONNECT'))
	require_once (__ROOT__.'/db_connect.php');
//require_once(__ROOT__.'/vlg_header.php'); 
/* */
echo sprintf("<link  rel=\"stylesheet\" type=\"text/css\" href=\"css/mainMenu.css\">");
function getCat(){
    $sqlStr="SELECT * FROM private_sector.menu";
    $res=qSQL($sqlStr);
    //Создаем масив где ключ массива является ID меню
    $cat = array();
    while($row = mysql_fetch_array($res)){ //$res->fetch_assoc()){
//	print_r($row);
        $cat[(int)$row["id"]] = $row;
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
$cat  = getCat();
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
//РЁР°Р±Р»РѕРЅ РґР»СЏ РІС‹РІРѕРґР° РјРµРЅСЋ РІ РІРёРґРµ РґРµСЂРµРІР°
	function tplMenu($category){
	    $menu = '<li>
	        <a href="#" title="'. iconv("CP1251","UTF-8",$category['title']) .'">'.
	        iconv("CP1251","UTF-8",$category['title']).'</a>';
	        if(isset($category['childs'])){
	            $menu .= '<ul>'. showCat($category['childs']) .'</ul>';
	        }
	    $menu .= '</li>';
	    return $menu;
	}
	/**
	* Р РµРєСѓСЂСЃРёРІРЅРѕ СЃС‡РёС‚С‹РІР°РµРј РЅР°С€ С€Р°Р±Р»РѕРЅ
	**/
	function showCat($data){
	    $string = '';
	    foreach($data as $item){
	        $string .= tplMenu($item);
	    }
	    return $string;
	}
	//РџРѕР»СѓС‡Р°РµРј HTML СЂР°Р·РјРµС‚РєСѓ
	$cat_menu=showCat($tree);
//	$cat_menu = iconv('UTF-8','CP1251',$cat_menu);
	//Р’С‹РІРѕРґРёРј РЅР° СЌРєСЂР°РЅ
echo '<div id="container">';
echo" <nav>";
//print_r($cat);

	echo '<ul>'. $cat_menu .'</ul>';
//foreach ($menuArray as  )
echo "</div> </nav>";
?>
