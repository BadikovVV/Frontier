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
    //������� ����� ��� ���� ������� �������� ID ����
    $cat = array();
    while($row = $res->fetch_assoc()){
        $cat[$row['id']] = $row;
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
$cat  = getCat($mysqli);
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
echo  "<pre>";
print_r($tree);
echo  "</pre>";
echo '<div id="container">';
echo" <nav>";

//foreach ($menuArray as  )
echo "</div> </nav>";
?>
