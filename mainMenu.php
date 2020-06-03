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
    //������� ����� ��� ���� ������� �������� ID ����
    $cat = array();
    while($row = mysql_fetch_array($res)){ //$res->fetch_assoc()){
//	print_r($row);
        $cat[(int)$row["id"]] = $row;
    }
    return $cat;
}
//������� ���������� ������ �� ������� �� Tommy Lacroix
function getTree($dataset) {
    $tree = array();
    foreach ($dataset as $id => &$node) {   
    //���� ��� ��������
        if (!$node['parent']){
            $tree[$id] = &$node;
        }else{
        //���� ���� ������� �� ���������� ������
            $dataset[$node['parent']]['childs'][$id] = &$node;
        }
    }
    return $tree;
}
//�������� �������������� ������ � �������
$cat  = getCat();
//������� ����������� ����
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
//Шаблон для вывода меню в виде дерева
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
	* Рекурсивно считываем наш шаблон
	**/
	function showCat($data){
	    $string = '';
	    foreach($data as $item){
	        $string .= tplMenu($item);
	    }
	    return $string;
	}
	//Получаем HTML разметку
	$cat_menu=showCat($tree);
//	$cat_menu = iconv('UTF-8','CP1251',$cat_menu);
	//Выводим на экран
echo '<div id="container">';
echo" <nav>";
//print_r($cat);

	echo '<ul>'. $cat_menu .'</ul>';
//foreach ($menuArray as  )
echo "</div> </nav>";
?>
