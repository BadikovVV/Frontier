<?php
// 
// !!! ���������� ���� � ���������� http://10.147.2.125/?action=login !!!
//
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', '1'); 
###################################

require_once 'vlg_php_header.php';
//
    if(isset($_REQUEST["mapfilter_address"])){
        setcookie('mapfilter[address]', $_REQUEST["mapfilter_address"]);
    } elseif(isset($_COOKIE['mapfilter']['address'])){
        $_REQUEST["mapfilter_address"]=$_COOKIE['mapfilter']['address'];
    } else {
        setcookie ('mapfilter[address]', '');
        $_REQUEST["mapfilter_address"]='';
    }
//
    if(isset($_REQUEST["mapfilter_project"])){
        setcookie('mapfilter[project]', $_REQUEST["mapfilter_project"]);
    } elseif(isset($_COOKIE['mapfilter']['project'])){
        $_REQUEST["mapfilter_project"]=$_COOKIE['mapfilter']['project'];
    } else {
        setcookie ('mapfilter[project]', "������� ������");
        $_REQUEST["mapfilter_project"]="������� ������";
    }
//
    if(isset($_REQUEST["mapfilter_mctet"])){
        setcookie('mapfilter[mctet]', $_REQUEST["mapfilter_mctet"]);
    } elseif(isset($_COOKIE['mapfilter']['mctet'])){
        $_REQUEST["mapfilter_mctet"]=$_COOKIE['mapfilter']['mctet'];
    } else {
        setcookie ('mapfilter[mctet]', "��������...");
        $_REQUEST["mapfilter_mctet"]="��������...";
    }
//
    if(isset($_REQUEST["mapfilter_arm_status"])){
        setcookie('mapfilter[search_arm_status]', $_REQUEST["mapfilter_arm_status"]);
    } elseif(isset($_COOKIE['mapfilter']['search_arm_status'])){
        $_REQUEST["mapfilter_arm_status"]=$_COOKIE['mapfilter']['search_arm_status'];
    } else {
        setcookie ('mapfilter[search_arm_status]', "-1");
        $_REQUEST["mapfilter_arm_status"]="-1";
    }
//
    if(isset($_REQUEST["project_id"])){
        setcookie('project[id]', $_REQUEST["project_id"]);
    } elseif(isset($_COOKIE['project']['id'])){
        $_REQUEST["project_id"]=$_COOKIE['project']['id'];
    } else {
        setcookie ('project[id]', "-1");
        $_REQUEST["project_id"]="-1";
    }
// �.�. ����� "require_once 'vlg_reestr.php';" � �.�.
    syncReqCook("mapfilter","ltc","��������...");
//

require_once 'vlg_util_ps.php';
require_once 'func.inc.php';
require_once 'func_date.inc.php';
require_once 'vlg_header.php'; // ����� ������ HTML ��������
require_once 'vlg_imp.php';
require_once 'vlg_reestr.php';
require_once 'vlg_edit_cluster.php';
?>

<TR><TD colspan='2'>
<table border='0' cellspacing='0' cellpadding='0' width='98%' height='100%' align='center'>
<tr><td valign='top' style='PADDING-LEFT: 35px;'>
<?php

if (!defined("LOGINED")) $_GET["c"] = "4";
if (!isset($_GET["c"])) $_GET["c"] = "0";
////////////////////////////////////////////////////////////////////////////////
    switch($_GET["c"]) {
////////////////////////////////////////////////////////////////////////////////
    case "99": // �������� ������
        echo file_get_contents('help.html');
    break;
////////////////////////////////////////////////////////////////////////////////
    case "0": // ������� ��������
        showStatusFrontier();
        showMapStatus();
    break;
////////////////////////////////////////////////////////////////////////////////
    case "1": // �������� �����
        //vlg_map($row_users); // ����� ���������� � vlg_header.php
        break;
////////////////////////////////////////////////////////////////////////////////
    case "2": // ������
        //d($_REQUEST);d("<br>");
        vlg_reestr($row_users,$user_LTC_list);        
        break;
////////////////////////////////////////////////////////////////////////////////
    case "3": // ������ (���� �������, ������ ����� �������� ���������� � vlg_header.php)
        echo $ubord->havePrivilegeText("G2 U900 U918 U1082","*","<a href='vlg_report.php?func=2'>[����� �� ����� � �������]</a>").
            "
            <hr>
            ";        
    break;
////////////////////////////////////////////////////////////////////////////////
    case "4": // ����� �����������
        if (isset($_COOKIE['rtcomug'])) {
            if (@$_COOKIE['rtcomug'][0] and @ $_COOKIE['rtcomug'][0] != '' and @ $_COOKIE['rtcomug'][2] == 1) {
                $saved_name = $_COOKIE['rtcomug'][0];
                $save_check = " checked";
            }
        }
        echo "<form name='welcome' method='POST' action='./?action=login'>
        <br><br><br><br><br><br><br><br>
        <table border=1 cellSpacing=0 cellPadding=0 align='center' width='90%' bordercolor='lightskyblue' 
               bordercolordark='white' bordercolorlight='lightskyblue' bgcolor='#FBFBFB'>
            <tr><td>
            <br><br><br><br>
            <h3 align='center'>���������� �������. ������������� ������ ��� \"����������\"</h2>
            <table border='0' cellSpacing=0 cellPadding=0 align='center'>
            <tr><td align='right' style='PADDING-RIGHT: 5px;'><b>������������:</b></td>
                <td><input type='text' name='login' size='45' value='".@$saved_name ."'></td></tr>
            <tr><td align='right' style='PADDING-RIGHT: 5px;'><b>������:</b></td><td><input type='password' name='pass' size='45'></td></tr>
            <!--tr><td><input type='checkbox' name='saveme' value='1'".@$save_check ."> ��������� ����</td>
                <td align='center'><a href='./reg.php'>�����������</a> &nbsp;&nbsp;&nbsp;
                <a href='mailto:Denis_Sakhnov@south.rt.ru'>���������</a></td></tr-->
            <tr><td colspan='2'><br><input type='submit' value='�����'></td><td colspan='2' align='center'>
                <a href='mailto:Denis_Sakhnov@south.rt.ru?subject=�������������� ������&Body=����� ������������ ������ �� ���� � �������. '>������������ ������</a></td></tr>
            </table>
            </form>
            <br><br>
            </td></tr>
        </table>
        <br><br>";
        echo "<br><center>" . @$message . "</center>";
        echo "<br><br><br><br><br><br><br>";
        break;
////////////////////////////////////////////////////////////////////////////////
    case "5": // ���������� ����������
        echo "<a href='./?c=5&action=clusterstat'>[����������]</a> 
            <a href='./?c=5&action=clustermap'>[����� �������������� ���������]</a> 
            <a href='./?c=5&action=clustermember'>[�������������� ������]</a>
            <hr>
            ";
        switch ($_REQUEST["action"]) {
            case "clusterstat":
                clusterStat();
            break;
            case "clustermap":
                //vlg_cluster(); // ������� ���������� (���� ��������)
                //vlg_add_cluster($row_users["uid"]); // ������� (�������������� �� �����)
                vlg_edit_cluster($row_users["uid"]);                
            break;
            case "clustermember": // ����� �������� �� �����������
                clusterMember();
            break;
            default:
            break;
        }
    break;
////////////////////////////////////////////////////////////////////////////////
    case "6": // �������� ������ � ����������� ��������� ��������
        echo "<a href='./?c=6&action=imp_cs'>[�������� ������ B2C/</a>
            <a href='./?c=6&action=imp_b2b'>�������� ������ B2B /</a>
            <a href='./?c=6&action=callcoord'>������ ��������� ������]</a>";
        echo $ubord->havePrivilegeText("G1","*","<a href='./?c=6&action=imp_ats'>[�������� ��� /</a>
            <a href='./?c=6&action=imp_spd'>�������� ��� /</a>
            <a href='./?c=6&action=imp_dist_box'>�������� ��]</a>
            <a href='./?c=6&action=atscoord'>[������ ��������� ���</a>
            <a href='./?c=6&action=spdportcoord'>... ��� ������,</a>
            <a href='./?c=6&action=distboxcoord'>... ��]</a>
            <a href='./?c=6&action=dslcoord'>[xDSL ���.��������� </a>
            <a href='./?c=6&action=xlmload'>XLM load </a>
            <a href='./?c=6&action=osmload'>... OSM load]</a>
            <a href='./?c=6&action=sparkData'>... �������� ������ �� ������]</a>").
            "<hr>
            ";
        switch (@$_GET["action"]) {
        ////////////////////////////////////////////////////////////////////////
        case "imp_cs":
            vlg_imp_new($row_users["uid"],'CS');
        break;
        ////////////////////////////////////////////////////////////////////////
        case "imp_b2b":
            //vlg_imp_new_b2b($row_users["uid"]);
            vlg_imp_new($row_users["uid"],'B2B');
        break;
        ////////////////////////////////////////////////////////////////////////
        // ���������� �� vlg_project.php
        case "prjcallload":
            //vlg_imp_new_b2b($row_users["uid"]);
            vlg_imp_new($row_users["uid"],$_REQUEST["project_id"]);
        break;
        ////////////////////////////////////////////////////////////////////////
        case "callcoord": // ������ ��������� ������
            callcoord();
        break;
        ////////////////////////////////////////////////////////////////////////
        case "imp_ats":
            vlg_imp("ats");
            //vlg_imp("test2");
        break;
        ////////////////////////////////////////////////////////////////////////
        case "imp_spd":
            vlg_imp("spd");
        break;
        ////////////////////////////////////////////////////////////////////////
        case "imp_dist_box":
            vlg_imp("dist_box");
        break;
        ////////////////////////////////////////////////////////////////////////
        case "atscoord":
            obATSCoord(102);
        break;
        ////////////////////////////////////////////////////////////////////////
        case "spdportcoord":
            obATSCoord(101);
        break;
        ////////////////////////////////////////////////////////////////////////
        case "distboxcoord":
            obATSCoord(105);
        break;
        ////////////////////////////////////////////////////////////////////////
        case "dslcoord":
            obATSCoord(106);
        break;
        ////////////////////////////////////////////////////////////////////////
        case "xlmload":
            $string = getUrl('http://10.147.2.124:8080/firw4/testXMLAnswer');
/*$string = "
<?xml version='1.0' encoding='utf-8' ?>
<table>
<tr><td>�. ���������� ���. �������������</td><td>��.</td><td>�������</td></tr>
<tr><td>�. �������� �. ������������ ���. �������������</td><td>��.</td><td>�������</td></tr>
<tr><td>�. �������� �. ������������ ���. �������������</td><td>��.</td><td>�������</td></tr>
<tr><td>�. ���������� ���. �������������</td><td>��.</td><td>������</td></tr>
<tr><td>�. ���������� ���. �������������</td><td>��.</td><td>�������</td></tr>
<tr><td>�. ���������� ���. �������������</td><td>��.</td><td>�������������</td></tr>
<tr><td>�. ���������� ���. �������������</td><td>��.</td><td>�����������</td></tr>
<tr><td>�. ���������� ���. �������������</td><td>��.</td><td>������������</td></tr>
</table>";*/
            
            //print_r(iconv('CP1251','UTF-8', $string));
            print_r(iconv('UTF-8','CP1251', $string));
            echo "<br>";
            //$sxe = simplexml_load_string(iconv('CP1251','UTF-8', $string));
            $sxe = simplexml_load_string(iconv('UTF-8','CP1251', $string));
            print_r($sxe);
            echo "<br>";
//            $sxe = simplexml_load_file("addrdsl.xml");
//            print_r($sxe);
            foreach($sxe->tr as $tr)
            {
                echo "<br>". $tr->td, ";";
                echo $tr->td[0], ";";
            }
            //print_r($sxe);
            foreach($sxe->children() as $a => $b) {
                //if($a=='tr'){
                    echo $a."---".$b;
            }
        break;
        ////////////////////////////////////////////////////////////////////////
        case "osmload":
            require_once 'vlg_osmload.php';
        break;
        case "sparkData":
            vlg_imp("sparkData");
        break;
        }    
    break;
////////////////////////////////////////////////////////////////////////////////
    case "7": // ������ ����������
        //echo $ubord->havePrivilegeText("U900","*","<a href='./?c=7&action=adduser'>[�������� ������������]</a> 
        echo $ubord->havePrivilegeText("U900 U908 U918 U1082 U1115","*","<a href='./?c=7&action=adduser'>[�������� ������������]</a> 
            <a href='./?c=7&action=userlist'>[������ �������������]</a>
            <a href='./?c=7&action=usergroup'>[������ �������������]</a>"
            ).
            "<a href='./?c=7&action=gstatus'>[������� ������]</a> 
            <a href='./?c=7&action=newDeviceList'>[�������������� ������������]</a> 
            <a href='./?c=7&action=smredit'>[�������������� ���]</a> 
            <a href='./?c=7&action=smraddload'>[�������� (����������) ���]</a> 
            <!--a href='./?c=7&action=settings'>[��������� �������]</a-->
            <a href='./?c=7&action=refcommatload'>[�������� ����������� ��]</a>
            <hr>
            ";
        switch (@$_GET["action"]) {
////////////////////////////////////////////////////////////////////////////////
            case "smraddload": // �������� (����������) ���
                vlg_imp("smraddload");
            break;
            case "smrdelload": // �������� (������) ���
                //vlg_imp("smrdelload");
            break;
            case "refcommatload": // �������� ����������� ��
                vlg_imp("refcommatload");
            break;
            ////////////////////////////////////////////////////////////////////
            case "gstatus": // ������� �������
                showStatusFrontier();
                showMapStatus();
                showStatusRoute();
            break;
////////////////////////////////////////////////////////////////////////////////
            case "adduser": // �������� ������ ������������
                if($_REQUEST["do"] == "adduser") {
                    $result_t1=rSQL("SELECT * FROM ps_users WHERE login='".$_POST["login"] .
                            "' or (name='".$_POST["name"] ."' and surname='".$_POST["surname"] ."' and last_name='".$_POST["last_name"] ."')");
                    if(!$result_t1) {
                        if(isset($_POST["login"]) and !empty(trim($_POST["login"])))   $login=$_POST["login"];
                        else   list($login, ) = explode("@", $_POST["email"]);
                        $result_insert = SQL("INSERT INTO ps_users (ugroup,login,pass,fio,name,surname,last_name,
                            datebegin,email,ip,phone_work,phone,status,question,answer,hash,service,service2,rid,ltc)
                            value('".$_POST["ggroup"] ."', '".trim($login) ."', '".md5(trim($_POST["pass"])) ."', '". 
                                $_POST["fio"] ."', '".$_POST["name"] ."', '".$_POST["surname"] ."', '".$_POST["last_name"] ."', '". 
                                $udate_time ."', '".$_POST["email"] ."', '', '".$_POST["phone_work"] ."', '" . 
                                $_POST["phone"] . "','" . $_POST["status"] . "', '1', 'gzp', '" . $_POST["hash"] . "', '" . 
                                $_POST["service"] . "', '" . implode(",",$_POST["service2"]) ."', '0',".$_POST["ltc"].")")->commit();
                        if ($result_insert == TRUE) {
                            echo "<br><img src='./images/check.gif' align='absmiddle'> <b style='color: green;'>������������ \"".$_POST["fio"] ."\" ������� ������� � ��.</b><br>";
                        }
                    } else
                        echo "<br><img src='./images/cross.gif' align='absmiddle'> ".
                            "<b style='color: red;'>������������ (���-\"".$_POST["fio"] ."\", �����-\"".$_POST["login"] ."\") ��� ���� ��</b><br>";
                }
                //echo "<form method='POST' target='result' action='./?c=7&action=adduser&do=adduser'>";
                echo "<form method='POST' action='./?c=7&action=adduser&do=adduser'>";
                $min = 8; // ����������� ���������� ��������
                $max = 8; // ������������ ���������� ��������	
                $pwd2 = "";
                for ($i = 0; $i < rand($min, $max); $i++) {
                    $num = rand(48, 122);
                    if (($num > 97 && $num < 122))
                        $pwd2.=chr($num);
                    else if (($num > 65 && $num < 90))
                        $pwd2.=chr($num);
                    else if (($num > 48 && $num < 57))
                        $pwd2.=chr($num);
                    else if ($num == 95)
                        $pwd2.=chr($num);
                    else
                        $i--;
                }
                $min = 32; // ����������� ���������� ��������
                $max = 32; // ������������ ���������� ��������	
                $pwd3 = "";
                for ($i = 0; $i < rand($min, $max); $i++) {
                    $num = rand(48, 122);
                    if (($num > 97 && $num < 122))
                        $pwd3.=chr($num);
                    else if (($num > 65 && $num < 90))
                        $pwd3.=chr($num);
                    else if (($num > 48 && $num < 57))
                        $pwd3.=chr($num);
                    else if ($num == 95)
                        $pwd3.=chr($num);
                    else
                        $i--;
                }
                echo "<b>�������� ������ ������������</b><hr>";
                echo "<table width='80%'>
                    <tr><td align='right' width='10%'>�����</td>
                        <!--td>������������� (��� �� @ � e-mail)</td-->
                        <td><input name='login' type='text' size='35'></td>
                    </tr>
                    <tr><td align='right'>������</td><td><input name='pass' type='text' size='35' value='".$pwd2 ."'></td></tr>
                    <tr><td align='right'>������� �.�.</td><td><input name='fio' type='text' size='35'></td></tr>
                    <tr><td align='right'>�������</td><td><input name='surname' type='text' size='35'></td></tr>
                    <tr><td align='right'>���</td><td><input name='name' type='text' size='35'></td></tr>
                    <tr><td align='right'>��������</td><td><input name='last_name' type='text' size='35'></td></tr>
                    <tr><td align='right'>Email</td><td><input name='email' type='text' size='35'></td></tr>
                    <tr><td align='right'>������� �������</td><td><input name='phone_work' type='text' size='35'></td></tr>
                    <tr><td align='right'>������ �������</td><td><input name='phone' type='text' size='35'></td></tr>";
                $sel_user_ltc_list=new CSelect("select '��������...',-1 union SELECT concat(id,'. ',name),id FROM ggroup", 
                            "ggroup", "", "", "");
                echo "<tr><td align='right'>������ �������������</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>";
                $sel_user_ltc_list=new CSelect("select '��������...',-1 union SELECT concat(id,'. ',name),id FROM ps_mctet", 
                            "service", "", "", "");
                echo "<tr><td align='right'>���� ��������</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>";
                $sel_user_ltc_list=new CSelect("select '��������...',-1 union SELECT concat(lid,'. ',lname),lid FROM ltc", 
                            "ltc", "", "", "");
                echo "<tr><td align='right'>��� ��������</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>";
                $sel_user_ltc_list=new CSelect("select '��������...',-1 union SELECT concat(lid,'. ',lname),lid FROM ltc", 
                            "service2[]", "", "", "multiple='multiple' width: 300px size='6'");
                echo "<tr><td align='right'>���</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>
                    <tr><td align='right'>������</td><td>
                        <select name='status'>
                            <option value='1' selected>�����.</option>
                            <option value='0'>�������.</option>
                            <option value='2'>����</option></select></td></tr>
                    </table>
                    ";
                echo "<input type='hidden' name='hash' value='" . $pwd3 . "'>";
                echo "<br><br><input type='submit' value='��������'>";
                echo "</form><br>";
            break;
////////////////////////////////////////////////////////////////////////////////
            case "userlist": // ������ ������������� � �� ��������������
                if($_REQUEST["save"]=="true" and $_REQUEST["uid"]) {
                    // ���������� ����� �������������� ������������
                    $result_update = SQL("UPDATE ps_users SET ugroup='" . $_POST["ugroup"] . "', `status`='" . 
                        $_POST["status"] . "', service2='" . implode(",",$_POST["service2"]) . "', service='" . 
                        @$_POST["service"] . "', fio='".$_POST["fio"] ."', surname='".$_POST["surname"] ."', name='".$_POST["name"] ."',
                            last_name='".$_POST["last_name"] ."', email='".$_POST["email"] ."', 
                            phone_work='".$_POST["phone_work"] ."',phone='".$_POST["phone"]."',ltc=".$_POST["ltc"]." 
                        WHERE uid='".$_POST["uid"] ."'")->commit();
                    if (@$result_update == TRUE) {
                        echo "<p><img src='./images/check.gif' align='absmiddle'> <b style='color: green;'>������ ������������ ��������</b><p>";
                    }
                } elseif($_REQUEST["delete"]=="true" and $_REQUEST["uid"]) {
                    // �������� ������������
                    $cursor=SQL("delete from ps_users WHERE uid='".$_REQUEST["uid"] ."'");
                    $result_update=$cursor->affected_rows();
                    $cursor->commit();
                    if ($result_update) {
                        echo "<p><img src='./images/check.gif' align='absmiddle'>
                            <b style='color: green;'>������� ".$result_update." ���.</b><p>";
                    }
                } elseif($_REQUEST["edit"]=="true" and $_REQUEST["uid"]) {
                    // �������������� ������������
                    $uRow=rSQL("SELECT u.*,g.name gname FROM ps_users u left join ggroup g on u.ugroup=g.id WHERE u.uid='".$_REQUEST["uid"] ."'");
                    echo "<form method='POST' action='./?c=7&action=userlist&save=true'>";
                    echo "<b>�������������� ������������</b><hr>";
                    echo "<table width='80%'>
                        <tr><td align='right'>������� �.�.</td><td><input name='fio' type='text' size='35' value='".$uRow["fio"] ."'></td></tr>
                        <tr><td align='right'>�������</td><td><input name='surname' type='text' size='35' value='".$uRow["surname"] ."'></td></tr>
                        <tr><td align='right'>���</td><td><input name='name' type='text' size='35' value='".$uRow["name"] ."'></td></tr>
                        <tr><td align='right'>��������</td><td><input name='last_name' type='text' size='35' value='".$uRow["last_name"] ."'></td></tr>
                        <tr><td align='right'>Email</td><td><input name='email' type='text' size='35' value='".$uRow["email"] ."'></td></tr>
                        <tr><td align='right'>������� �������</td><td><input name='phone_work' type='text' size='35' value='".$uRow["phone_work"] ."'></td></tr>
                        <tr><td align='right'>������ �������</td><td><input name='phone' type='text' size='35' value='".$uRow["phone"] ."'></td></tr>";
                    //
                    $sel_user_ltc_list=new CSelect("select '��������...',-1 union SELECT concat(id,'. ',name),id FROM ggroup", 
                                "ugroup", $uRow["ugroup"], "", "");
                    echo "<tr><td align='right'>������ �������������</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>";
                    //
                    $sel_user_ltc_list=new CSelect("select '��������...',-1 union SELECT concat(id,'. ',name),id FROM ps_mctet", 
                                "service", $uRow["service"], "", "");
                    echo "<tr><td align='right'>���� ��������</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>";
                    //
                    $sel_user_ltc_list=new CSelect("select '��������...',-1 union SELECT concat(lid,'. ',lname),lid FROM ltc", 
                                "ltc", $uRow["ltc"], "", "");
                    echo "<tr><td align='right'>��� ��������</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>";
                    //
                    $sel_user_ltc_list=new CCheckBoxList("SELECT concat(lid,'. ',lname),lid FROM ltc order by lname", 
                            "service2[]", $uRow["service2"], "", "style='width: 100%; overflow: auto; height: 80px; position: relative;'");
                    echo "<tr><td align='right'>������������� ���</td><td>".$sel_user_ltc_list->htmlel ."</td></tr>";
                    //
                    echo "<tr><td align='right'>������</td><td>
                            <select name='status'>
                                <option value='1' selected>�����.</option>
                                <option value='0'>�������.</option>
                                <option value='2'>����</option></select></td></tr>
                        </table>
                        ";
                    echo "<input type='hidden' name='uid' value='".$_REQUEST["uid"] ."'>";
                    echo "<br><br><input type='submit' value='���������'>";
                    echo "</form><br>";
                } elseif($_REQUEST["parsme"]){
                    if($_REQUEST["p"]){
                        SQL("update ps_users set pass='".md5($_REQUEST["p"])."' where uid=".$_REQUEST["parsme"])->commit();
                    }else{
                        //    
                        $min = 8; // ����������� ���������� ��������
                        $max = 8; // ������������ ���������� ��������	
                        $pwd2 = "";
                        for ($i = 0; $i < rand($min, $max); $i++) {
                            $num = rand(48, 122);
                            if (($num > 97 && $num < 122))
                                $pwd2.=chr($num);
                            else if (($num > 65 && $num < 90))
                                $pwd2.=chr($num);
                            else if (($num > 48 && $num < 57))
                                $pwd2.=chr($num);
                            else if ($num == 95)
                                $pwd2.=chr($num);
                            else
                                $i--;
                        }
                        //
                        echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                        echo "<legend>&nbsp;<b style='color: #006;'>parsme</b>&nbsp;</legend>";
                        echo "<form name='form_parsme' method='post' style='' "
                                ."action='./?c=7&action=userlist&parsme=".$_REQUEST["parsme"]."'>
                            parsme:<input type='text' name='p' size='30' value='".$pwd2 ."'><br>
                            <input type='submit' onclick=' ps(document.form_parsme); return true; ' value='��������� ���������'>
                        </form></fieldset>";
                    }
                }
                echo "<b>������ �������������:</b><hr>";
                // ����� �������
                echo "<style type=\"text/css\">
                        tr.blockuser  { background:#FDD; }
                        td.prjleftcol { color:#025; background:#EEF; }
                        td.prjheader { color:#037; background:#DEF; }
                        td.prjtype1 { color:#800; }
                        td.prjtype2 { color:#080; }
                        td.prjtype3 { color:#008; }
                    </style>";
                echo "<table border='1' cellspacing='0' bordercolor='black' bordercolordark='white' width='100%'>";
                $cursor=SQL("SELECT u.*,g.name gname,t.name tname,ltc.lname ltcname,l.lname lname 
                    FROM ps_users u left join ggroup g on u.ugroup=g.id 
                        left join ps_mctet t on u.service=t.id 
                        left join ltc on u.ltc=ltc.lid 
                        left join ltc l on u.service2=l.lid 
                    order by fio");
                echo "<tr>
                    <td align='right' class='prjheader'><b>�</b></td>
                    <td align='left' class='prjheader'><b>�����</b></td>
                    <td class='prjheader'><b>&nbsp</b></td>
                    <td align='left' class='prjheader'><b>������� �.�.</b></td>
                    <td align='center' class='prjheader'><b>������ ���</b></td>
                    <td align='center' class='prjheader'><b>Email</b></td>
                    <td align='center' class='prjheader'><b>������� �������</b></td>
                    <td align='center' class='prjheader'><b>���. �������</b></td>
                    <td align='center' class='prjheader'><b>������</b></td>
                    <td align='center' class='prjheader'><b>������</b></td>
                    <td align='center' class='prjheader'><b>����</b></td>
                    <td align='center' class='prjheader'><b>��� ��������</b></td>
                    <td align='center' class='prjheader'><b>������������� ���</b></td>
                    <td class='prjheader'><b>&nbsp</b></td>
                    <td class='prjheader'><b>&nbsp</b></td>
                    </tr>";
                while ($cursor->assoc()) {
                    //d($cursor->r);
                    if(empty(trim($cursor->r["service2"]))){
                        $user_LTC_list='';
                    }else{
                        // <td align='center' class=''>".$cursor->r["service2"] ."</td>
                        $user_LTC_list=rSQL("SELECT group_concat(lname SEPARATOR ' ') gcl "
                            . "FROM ltc where lid in (".$cursor->r["service2"] .")")["gcl"];
                    }
                    echo "<tr ".($cursor->r["status"]==2 ? "class='blockuser'" : " ") .">
                        <td align='right' class=''>".$cursor->r["uid"] ."</td>
                        <td align='left' class=''>".$cursor->r["login"] ."</td>
                        <td align='center' class=''>
                            <a href='./?c=7&action=userlist&parsme=".$cursor->r["uid"]."'>
                                <img src='./images/jc_refresh.gif' align='absmiddle' title=''></a></td>
                        <td align='left' class='prjleftcol'>".$cursor->r["fio"] ."</td>
                        <td align='center' class=''>".$cursor->r["surname"] ." ".$cursor->r["name"] ." ".$cursor->r["last_name"] ."</td>
                        <td align='center' class=''>".$cursor->r["email"] ."</td>
                        <td align='center' class=''>".$cursor->r["phone_work"] ."</td>
                        <td align='center' class=''>".$cursor->r["phone"] ."</td>
                        <td align='center' class=''>".$cursor->r["gname"] ."</td>
                        <td align='center' class=''>".
                            ($cursor->r["status"]==1 ? "�����." : ($cursor->r["status"]==2 ? "����" : "�������."))
                            ."</td>
                        <td align='center' class=''>".$cursor->r["tname"] ."</td>
                        <td align='center' class=''>".$cursor->r["ltcname"] ."</td>
                        <td align='center' class=''>".$user_LTC_list ."</td>
                        <td align='center'><a href='./?c=7&action=userlist&edit=true&uid=".$cursor->r["uid"] ."' title='�������������'>
                            <img src='./images/edit.gif' align='absmiddle'></a></td>
                        <td align='center' title='�������' onclick=' 
                                if(confirm(\"������� ������������ ?\"))
                                    window.location = \"./?c=7&action=userlist&delete=true&uid=".$cursor->r["uid"] ."\" '>
                            <img src='./images/del.gif' align='absmiddle'></td>
                        </tr>";
                }
                echo "</table>";
                $cursor->free();
            break;
////////////////////////////////////////////////////////////////////////////////
            case "smredit": // �������������� ���
                require_once 'vlg_smredit.php';
            break;
////////////////////////////////////////////////////////////////////////////////
            case "newDeviceList": // ������������
                require_once 'vlg_devicedit.php';
            break;
////////////////////////////////////////////////////////////////////////////////
            default:
            break;
        }
        break;
    //*****************************************************************************************************************
    default: // �� ���������
    break;
    //*****************************************************************************************************************
    } // end of switch (@$_GET["c"])
    ?>
    <br><br>
    </td></tr>
    <tr><td colspan='2' height='20' background='./images/top_bg.jpg' align='center' style='color: lightgray;'>���� powered </td></tr>
    </table>
    </TD></TR>
<?php
include "footer.php";
?>


