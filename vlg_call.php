<?php
ini_set('display_errors', 'On');
error_reporting('E_ALL');
//define("INDEX", "TRUE", TRUE);
define("DEBUG_PS", TRUE, TRUE);
//define("DEBUG_MAIL_PS", "Viacheslav.Badikov@south.rt.ru", TRUE);
define("DEBUG_MAIL_PS", "Denis_Sakhnov@south.rt.ru", TRUE);
header("Content-Type: text/html; charset=windows-1251"); //charset=utf8");
//define("WP_MEMORY_LIMIT", "128M");
//$locate_glob = $_SERVER['REQUEST_URI'];
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
    if(isset($_REQUEST["project_type"])){
        setcookie('project[type]', $_REQUEST["project_type"]);
    } elseif(isset($_COOKIE['project']['type'])){
        $_REQUEST["project_type"]=$_COOKIE['project']['type'];
    } else {
        setcookie ('project[type]', "��������...");
        $_REQUEST["project_type"]="��������...";
    }
//
    if(isset($_REQUEST["project_subtype"])){
        setcookie('project[subtype]', $_REQUEST["project_subtype"]);
    } elseif(isset($_COOKIE['project']['subtype'])){
        $_REQUEST["project_subtype"]=$_COOKIE['project']['subtype'];
    } else {
        setcookie ('project[subtype]', "��������...");
        $_REQUEST["project_subtype"]="��������...";
    }
//
INCLUDE "db_connect.php";
require_once 'vlg_util.php';
require_once 'func.inc.php';
require_once 'func_date.inc.php';
require_once 'vlg_util_ps.php';
require_once 'vlg_header.php'; // ����� ������ HTML ��������
if (!defined("LOGINED")) {
    "<a href='index.php?c=4'><b>��� ���������� �������������� - �������� �� ���� ������</b></a>";
    exit();
}
?>
<TR><TD colspan='2'>
<table border='0' cellspacing='0' cellpadding='0' width='98%' height='100%' align='center'>
<tr><td valign='top' style='PADDING-LEFT: 35px;'>
<script src="js/jquery.min.js"></script>
<script>
xmlHttp=new XMLHttpRequest();
current_user_uid=<?php echo $row_users['uid']; ?>;
current_user_ugroup=<?php echo $row_users['ugroup']; ?>;
preload_dictable=false;
</script>
<?php
////////////////////////////////////////////////////////////////////////////////
//
/*function forest_get_level($fid){
    $par_cursor=rSQL("select * from forest where fid=".$fid);
    //$level_arr=forest_get_child($fid,$par_cursor["parenttype"],$par_cursor["parentid"]);
    //$level_arr=forest_get_child($fid,$par_cursor["childtype"],$par_cursor["childid"]);
    // ���� ���������� ������
    $level_arr=forest_get_child($fid);
    // ���� �������� ������
    $cot_vertex=forest_get_vertex($par_cursor["childtype"],$par_cursor["childid"]);
    $level_arr[0]=array($par_cursor["pid"],$par_cursor["childtype"],$cot_vertex["ctable"],$cot_vertex["id"],
        $cot_vertex["pname"],$cot_vertex["comment"],$cot_vertex["ctableid"]);
    //
    return $level_arr;
}
//
////////////////////////////////////////////////////////////////////////////////
//
function forest_get_vertex($vtype,$vid){
    $cot_vertex=false;
    switch($vtype){
        case 2: // expense
            $cot_vertex=rSQL("select '��� ������' ctable,eid id,ename pname,comment,".$vtype." ctableid from expense where eid=".$vid);
        break;
        case 3: // cn_area
            $cot_vertex=rSQL("select '�������' ctable,cnaid id,cnaname pname,fullname comment,".$vtype." ctableid  from cn_area where cnaid=".$vid);
        break;
        case 7: // cn_envir
            $cot_vertex=rSQL("select '��� ���������' ctable,ceid id,cename pname,comment,".$vtype." ctableid from cn_envir where ceid=".$vid);
        break;
        case 9: // ref_com_mat
            $cot_vertex=rSQL("select '������ �����' ctable,rcmid id,mgroup pname,comment,".$vtype." ctableid from ref_com_mat where rcmid=".$vid);
        break;
    }          
    return $cot_vertex;
}
//
////////////////////////////////////////////////////////////////////////////////
//
//function forest_get_child($pid,$par_type,$par_id){
function forest_get_child($pid){
    $par_cursor=SQL("select * from forest where pid=".$pid);
    $i=1;
    $childs=[];
    while ($par_cursor->assoc()) {
        $cot_vertex=forest_get_vertex($par_cursor->r["childtype"],$par_cursor->r["childid"]);
        $childs[$i]=array($par_cursor->r["fid"],$par_cursor->r["childtype"],$cot_vertex["ctable"],$cot_vertex["id"],
            $cot_vertex["pname"],$cot_vertex["comment"],$cot_vertex["ctableid"]);
        $i++;
    }
    $par_cursor->free();
    //drec($childs);
    if($i==1) return false;
    else return $childs;
}*/
//
////////////////////////////////////////////////////////////////////////////////
// ����������� ���� � ������� ������ ������� � ���������
echo "<div id='next_stage_darkening' class='ps_popup_darkening'> 
        <div id='next_stage' class='ps_popup_main_window'> 
            <a class='ps_popup_close_button' title='�������' 
                onclick='document.getElementById(\"next_stage_darkening\").style.display = \"none\";'>X</a>
            <form name='form_next_stage' method='post' style='' "
                        . "action='vlg_call.php?action=status_project&sourcepage=".$_REQUEST["sourcepage"]."'>
            <b id='next_stage_status_select'></b> 
            <br><b>�������� ����������</b> " . 
                    select2('next_stage_user_select', "select '��������...',-1 union SELECT concat(uid,'. ',fio),uid FROM ps_users","��������...") . "
            <br><b>�������������� ���������</b> <input type='text' name='next_stage_add' size='60' value=''>
            <input type='hidden' name='next_stage_project' value='0'>
            <br><input type='submit' value='���������' onclick=' 
                return true; '></form>
    </div></div>";
// ^^ ����������� ���� � ������� ������ ������� � ��������� ^^
//////////////////////////////////////////////////////////////////////////////// 
// ���������� ����� ������ (������ �������� �����������) ps_list_dop
if(getReq("lid")==-1){
    if(rSQL("select count(*) count FROM ps_list_dop where list_id=(select list_id FROM ps_list_dop where lid='".$_REQUEST['new_conn_form_lid'].
        "') and tpid='".$_REQUEST['new_conn_form_tech']."' and service_id='".$_REQUEST['new_conn_form_service']."'")['count']==0)
    {
        $old_conn_status=rSQL("select status FROM ps_list_dop where lid='".$_REQUEST['new_conn_form_lid']."'")['status'];
        //echo "INSERT INTO ps_list_dop (list_id,status,arm_id,tpid,service_id,formatted_address,place_id,location_type,claster_id) 
        //    select list_id,'".$old_conn_status."', arm_id,'".$_REQUEST['new_conn_form_tech']."','".
        //        $_REQUEST['new_conn_form_service']."',formatted_address,place_id,location_type,claster_id 
        //        FROM ps_list_dop where lid='".$_REQUEST['new_conn_form_lid']."'";
        $result_insert = SQL("INSERT INTO ps_list_dop (list_id,status,arm_id,tpid,service_id,formatted_address,place_id,location_type,claster_id,
            uid,dateedit,guarantee,tariffname,ontlease,routelease,
            realcost,targetdate,finishdate,substatus,deferredpay,ontfullpay,routefullpay,attachnum,attachfullpay,attachlease) 
            select list_id,'".$old_conn_status."', arm_id,'".$_REQUEST['new_conn_form_tech']."','".
                $_REQUEST['new_conn_form_service']."',formatted_address,place_id,location_type,claster_id,
                ". $row_users['uid'] .",now(),guarantee,tariffname,ontlease,routelease,
                realcost,targetdate,finishdate,substatus,deferredpay,ontfullpay,routefullpay,attachnum,attachfullpay,attachlease 
                FROM ps_list_dop where lid='".$_REQUEST['new_conn_form_lid']."'");
        $_REQUEST["lid"] = $result_insert->insert_id();
        //echo $_REQUEST["lid"];
        SQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment,issent,filename,recommend,nextcallpath,shutdate)
            VALUES(NULL,'". $_REQUEST["lid"] ."','".$old_conn_status."',". $row_users['uid'] .
                ",-1,DATE_ADD(now(),interval 240 hour),2,'',0,NULL,6,NULL,NULL)")
            ->commit();
    }else{
        echo "����� ������� ��� ����! �������� � ���! <br>";
        $_REQUEST["lid"] = rSQL("select lid FROM ps_list_dop where list_id=(select list_id FROM ps_list_dop where lid='".$_REQUEST['new_conn_form_lid'].
            "') and tpid='".$_REQUEST['new_conn_form_tech']."' and service_id='".$_REQUEST['new_conn_form_service']."'")['lid'];
    }
}
////////////////////////////////////////////////////////////////////////////////
// ������ ��� �������
        switch ($_REQUEST["action"]) {
////////////////////////////////////////////////////////////////////////////////
// ��������� ���������
        case "save_call":
            $list_id=rSQL("SELECT list_id FROM ps_list_dop where lid=".$_REQUEST["lid"])["list_id"];
/*            SQL("UPDATE ps_list_dop SET
                zatrat_smr = '".$_REQUEST["zatrat_smr"]."', dev_summ = '".$_REQUEST["dev_summ"]."',
                comment = '".$_REQUEST["comment"]."',
                difficult_mc = '".$_REQUEST["difficult_mc"]."',shkaf_42u = '".$_REQUEST["shkaf_42u"]."',
                shassi_olt = '".$_REQUEST["shassi_olt"]."',
                kol_ports = '".$_REQUEST["kol_ports"]."',install = '".$_REQUEST["install"]."',
                pon_flag = '".$_REQUEST["pon_flag"]."', spd = '".$_REQUEST["spd"]."',
                difficult_rs = '".$_REQUEST["difficult_rs"]."',difficult_abl = '".$_REQUEST["difficult_abl"]."',
                difficult_abv = '".$_REQUEST["difficult_abv"]."',
                month_pay = '".$_REQUEST["month_pay"]."',*/
/*            SQL("UPDATE ps_list_dop SET
                zatrat_smr = '".$_REQUEST["zatrat_smr"]."', dev_summ = '".$_REQUEST["dev_summ"]."',
                comment = '".$_REQUEST["comment"]."',
                install = '".$_REQUEST["install"]."',
                month_pay = '".$_REQUEST["month_pay"]."',
                tpid = '".$_REQUEST["technology"]."',service_id = '".$_REQUEST["service_id"]."',
                uid = ".$row_users['uid'].",dateedit = now(),
                guarantee = '".$_REQUEST["guarantee"]."',tariffname = '".$_REQUEST["tariffname"]."',
                ontlease = '".(empty($_REQUEST["ontlease"]) ? 0 : $_REQUEST["ontlease"]) ."',
                routelease = '".(empty($_REQUEST["routelease"]) ? 0 : $_REQUEST["routelease"]) ."',
                realcost = '".(empty($_REQUEST["realcost"]) ? 0 : $_REQUEST["realcost"]) ."',
                targetdate = STR_TO_DATE('".(empty($_REQUEST["targetdate"]) ? "0000-00-00" : $_REQUEST["targetdate"]) ."','%Y-%m-%d'),
                finishdate = STR_TO_DATE('".(empty($_REQUEST["finishdate"]) ? "0000-00-00" : $_REQUEST["finishdate"]) ."','%Y-%m-%d'),
                substatus = '".(empty($_REQUEST["substatus"]) ? 20 : $_REQUEST["substatus"]) ."',
                deferredpay = '".(empty($_REQUEST["deferredpay"]) ? 0 : $_REQUEST["deferredpay"]) ."',
                ontfullpay = '".(empty($_REQUEST["ontfullpay"]) ? 0 : $_REQUEST["ontfullpay"]) ."',
                routefullpay = '".(empty($_REQUEST["routefullpay"]) ? 0 : $_REQUEST["routefullpay"]) ."',
                attachnum = '".(empty($_REQUEST["attachnum"]) ? 0 : $_REQUEST["attachnum"]) ."',
                attachfullpay = '".(empty($_REQUEST["attachfullpay"]) ? 0 : $_REQUEST["attachfullpay"]) ."',
                attachlease = '".(empty($_REQUEST["attachlease"]) ? 0 : $_REQUEST["attachlease"]) ."'
                WHERE lid = " . $_REQUEST["lid"] . "");*/
            SQL("UPDATE ps_list_dop SET
                zatrat_smr = '".$_REQUEST["zatrat_smr"]."', dev_summ = '".$_REQUEST["dev_summ"]."',
                comment = '".$_REQUEST["comment"]."',
                tpid = '".$_REQUEST["technology"]."',service_id = '".$_REQUEST["service_id"]."',
                uid = ".$row_users['uid'].",dateedit = now(),
                finishdate = STR_TO_DATE('".(empty($_REQUEST["finishdate"]) ? "0000-00-00" : $_REQUEST["finishdate"]) ."','%Y-%m-%d')
                WHERE lid = " . $_REQUEST["lid"] . "");
            SQL("UPDATE ps_list SET
                client_fio = '".$_REQUEST["client_fio"]."', contact_phone = '".$_REQUEST["contact_phone"]."'
                WHERE list_id = " . $list_id . "") -> commit();
            // �������� ����������� �����
            if($_REQUEST["delschema"]=='true'){
                $result_del=SQL("DELETE FROM blobs WHERE bid=(select blob_id from files where otype=2 and oid=".$_REQUEST["lid"] .")");
                $result_del=$result_del->affected_rows();
                SQL("DELETE FROM files where otype=2 and oid=".$_REQUEST["lid"] ."")->commit();    
            }
            // �������� ����������� ����� (�������� ��������� ������)
            if($_FILES['drawing']) { // �����
                for ($i = 0; $i < count($_FILES['drawing']); $i++) {
                    if ($_FILES['drawing']['tmp_name'][$i] == '')
                        continue;
                    // ����� ��� �������� �������� ��������
                    // /var/www/html/uploads/
                    //$targetFolder = $file_path . "uploads/" . $_REQUEST["project_id"]; // Relative to the root
                    //echo "<br>" . $targetFolder;
                    //if (file_exists($targetFolder) == FALSE) { // ��� ����������
                    //    mkdir($targetFolder, 0777);
                    //}
                    $tempFile = $_FILES['drawing']['tmp_name'][$i];
                    // ������ ���������� �����
                    $image = file_get_contents( $tempFile );
                    // ���������� ����������� ������� � ���������� �����
                    $image = $mysqli->escape_string( $image );
                    // ��������� ������ �� ���������� ����� � ���� ������
                    //echo "<!--INSERT INTO blobs (bcontent,uid) VALUES('',".$row_users['uid'] .")-->";
                    $result_id=SQL("INSERT INTO blobs (bcontent,uid) VALUES('".$image ."',".$row_users['uid'] .")");
                    $result_id=$result_id->insert_id();
                    SQL("INSERT INTO files values(NULL,'������','�����','','".$_FILES['drawing']['name'][$i] ."',now(),'".
                        $row_users["uid"] ."',NULL,2,".$_REQUEST["lid"] .",".$result_id .")")->commit();                    
                    //$targetPath = $targetFolder;
                    // �������� �� ������� ����� � ��� �� ������
                    /*for ($g = 1; $g < 500; $g++) {
                        if ($g == 1)
                            $change_fname = $_FILES['drawing']['name'][$i];
                        $result_ftest = qSQL("SELECT * FROM ps_files WHERE section='" . $_REQUEST["project_id"] . "' and file_name='" . $change_fname . "'");
                        if ($result_ftest->num_rows >= 1) {
                            // ��������������� ����, ������� ��� ����� �� ���������
                            $fmime = substr(strrchr($_FILES['drawing']['name'][$i], '.'), 1);
                            list($fname_alone, ) = explode($fmime, $_FILES['drawing']['name'][$i]);
                            $fname_alone = substr($fname_alone, 0, -1);
                            $change_fname = $fname_alone . "_" . $g . "." . $fmime;
                        } else
                            break;
                    }
                    $targetFile = rtrim($targetPath, '/') . '/' . $change_fname;
                    $fileParts = pathinfo($change_fname);
                    $test = move_uploaded_file($tempFile, $targetFile);
                    if ($test != FALSE) {
                        $result_insert_file = qSQL("INSERT INTO ps_files values(NULL,'" . $_REQUEST["project_id"] . "','otuio_shema','-','" . $change_fname . 
                                "','" . $udate2 . "','" . $row_users["uid"] . "',NULL)");
                        $otuio_shema_id = $mysqli->insert_id();
                    }*/
                }
            }
////////////////////////////////////////////////////////////////////////////////
// �������������� ������
        case "call_edit":
            if($_REQUEST["arm_id"]){
            // !!! �������� ����� ���� ����, ����� ������ ������ !!!
                $_REQUEST["lid"]=rSQL("SELECT min(lid) lid FROM ps_list_dop where arm_id=".$_REQUEST["arm_id"])["lid"];
            }
            $call_row_dop=rSQL("SELECT * FROM ps_list_dop where lid=".$_REQUEST["lid"]);
            $call_row_status=rSQL("SELECT * FROM ps_status where id=".$call_row_dop["status"]);
            $call_row=rSQL("SELECT * FROM ps_list where list_id=".$call_row_dop["list_id"]);
            // ������ ������ �� �������� ����� ������ (�����. � ���������� ������)
            $com_object_query="SELECT ccmid,ccm.cnaid,cna.cnaname,ccm.subeid,sube.ename subename,ccm.oid,o.oname ooname,ccm.cxid,cx.cxname,
		    ccm.technology,tp.name tpname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.bid,b.bname,ccm.rcmid,rcm.name rcmname,pld.arm_id,ccm.lid,
                    max(ccm.ccmname) ccmname,
                    sum(ccm.amount) ccmamount,sum(ccmlen) ccmlen,
                    sum(ccm.capacity1) ccmcapacity1,sum(ccm.capacity2) ccmcapacity2,
                    max(ccm.price) ccmprice,max(ccm.comment) comment,
                    max(rcm.price) rcmprice,max(rcm.unit) rcmunit,max(rcm.capacity1) rcmcapacity1,max(rcm.capacity2) rcmcapacity2
                FROM call_com_mat ccm 
                left join ref_com_mat rcm using(rcmid)
                left join cn_eq_type cet using(cetid) 
                left join ps_list_dop pld on ccm.stype=1 and pld.lid=ccm.lid
                left join sign_envir se on ccm.seid=se.seid
                left join cn_area cna on ccm.cnaid=cna.cnaid
                left join cn_envir ce on ccm.ceid=ce.ceid
                left join builder b on ccm.bid=b.bid
                left join subexpense sube on ccm.subeid=sube.seid
                left join owner o on ccm.oid=o.oid
                left join complexity cx on ccm.cxid=cx.cxid
                left join ps_teh_podkl tp on ccm.technology=tp.id
                where ccm.stype=1 and ccm.lid=".$_REQUEST["lid"] ." 
                group by ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.rcmid,rcm.name,pld.arm_id,ccm.lid,ccmid
		order by ccm.cnaid,ccm.seid,rcm.cetid,ccm.ceid,ccm.rcmid,ccm.lid
                ";
            ////////////////////////////////////////////////////////////////////
            // ����������� ���� � ������� ������
            echo "<div id='tariff_selection_darkening' class='ps_popup_darkening'> 
                    <div id='tariff_selection' class='ps_popup_main_window'> 
                        <a class='ps_popup_close_button' title='�������' 
                           onclick='document.getElementById(\"tariff_selection_darkening\").style.display = \"none\";'>X</a>
                        <b>�������� ����� (��������� ����������: " . 
                            rSQL("SELECT name FROM private_sector.ps_teh_podkl where id=".$call_row_dop["tpid"])["name"] . ")</b>
                        <table style='text-align: center;'><tr><td>������</td><td>���������������, ���.</td><td>�����������, ���.)</td></tr>";
                        $tariffCursor=qSQL("SELECT t.*,s.sname FROM tariff t left join service s on t.service_id=s.id where tech_id=".$call_row_dop["tpid"]);
                        while ($rowTariffCursor = $tariffCursor->fetch_array(MYSQL_ASSOC)) {
                            echo "<tr><td>".$rowTariffCursor["sname"]."</td><td>".
                            $rowTariffCursor["install"]."</td><td><a class=\"tariff_selection_close\" style='cursor: pointer;' 
                                onclick=\"document.new_project.month_pay.value=".$rowTariffCursor["month_pay"].";\">".
                            $rowTariffCursor["month_pay"]."</a></td></tr>";
                        }
            echo "</table></div></div>";
            // ^^ ����������� ���� � ������� ������ ^^
            ////////////////////////////////////////////////////////////////////
            // ����� ���������� ��� (map_obj_type=2)
            // !!! ��������
            if($_REQUEST["settypesmr"]=='true'){
//                echo "<form name='ps_smet_cid_form' method='post' "
//                        . "action='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."'>".
//                        "<input type='hidden' name='lid' value='" . $_REQUEST["lid"] . "'>".
//                        "<input type='hidden' name='map_obj_type' value='2'>".
//			"<input type='hidden' name='add_to_project_button' value='cansel'>".
//                        "<b>��� ��� </b>".
//                        "<br>����������:<input type='text' name='comment' size='16' value=''>".
//                        "&nbsp ������(���-��):<input type='text' name='ps_size' size='16' value='1'> ��<hr><table>";
//                
//                if($_REQUEST["project_type"]=='��������...') $_REQUEST["project_type"]='';
//                if($_REQUEST["project_subtype"]=='��������...') $_REQUEST["project_subtype"]='';
//                
//                echo "<tr><td style='color: #000;background: #f8f0f0' onclick='ps_smet_cid_form.submit();'><b style='color: #900;'>������</b></td></tr>";
//                $map_obj_cursor=SQL("SELECT id,name,price,ed FROM ps_smet_calc 
//                    where mgroup='" . $_REQUEST["project_type"] . "' and pgroup='" . $_REQUEST["project_subtype"] . "'");
//                while ($map_obj_cursor->assoc()) {
//                    echo "<tr><td style='color: #000;background: #f0f0f0'
//			onclick='ps_smet_cid_form.add_to_project_button.value=".$map_obj_cursor->r["id"]."; ps_smet_cid_form.submit();'>
//			<b style='color: #009;'>".$map_obj_cursor->r["name"]."</b><br>".$map_obj_cursor->r["price"].
//                        " ���. �� ".$map_obj_cursor->r["ed"]."</td></tr>";
//                }
//                $map_obj_cursor->free();
//                echo '</table></form>';
            } else if($_REQUEST["settypeequip"]=='true'){
            // ����� ���������� ������������ (map_obj_type=1)
            // !!! ��������
//                echo "<form name='ps_equip_cid_form' method='post'"
//                        . "action='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."'>".
//                        "<table border='1' cellspacing='1' cellpadding='2'><tr><td>".
//                        "<input type='hidden' name='lid' value='" . $_REQUEST["lid"] . "'>".
//                        "<input type='hidden' name='map_obj_type' value='1'>".
//			"<input type='hidden' name='add_to_project_button' value='cansel'>".
//                        "<b>��� ������������ </b>".
//                        "<br>����������:<input type='text' name='comment' size='16' value=''>".
//                        "&nbsp ���-��(������):<input type='text' name='ps_size' size='16' value='1'> ��</td></tr>";
//		echo "<tr><td style='color: #000;background: #f8f0f0' onclick='ps_equip_cid_form.submit();'><b style='color: #900;'>������</b></td></tr>";
//                $map_obj_cursor=SQL("SELECT id,name,price,ed FROM ps_equip");
//                while ($map_obj_cursor->assoc()) {
//                    echo "<tr><td style='color: #000;background: #f0f0f0;'
//			onclick='ps_equip_cid_form.add_to_project_button.value=".$map_obj_cursor->r["id"]."; ps_equip_cid_form.submit();'>
//			<b style='color: #009;'>".$map_obj_cursor->r["name"]."</b><br>".$map_obj_cursor->r["price"].
//                        " ���. �� ".$map_obj_cursor->r["ed"]."</td></tr>";
//                }
//                $map_obj_cursor->free();
//                echo '</table></form>';
            // ^^^ !!! �������� ^^^
            ////////////////////////////////////////////////////////////////////
            } else if($_REQUEST["addcommobject"]=='true'){
            // ������������� ����� ���������� ������� ����� (map_obj_type=4)
                $sel_cnaname=new CSelect("SELECT '��������...',-1 union select cnaname,cnaid FROM cn_area", 
                    "map_obj_cn_area", 
                    (($_REQUEST["map_obj_cn_area"]) ? $_REQUEST["map_obj_cn_area"] : 40), 
                    "comm_network_area");
                $sel_cename=new CSelect("SELECT '��������...',-1 union select cename,ceid FROM cn_envir", 
                    "map_obj_cn_envir", 
                    (($_REQUEST["map_obj_cn_envir"]) ? $_REQUEST["map_obj_cn_envir"] : -1), 
                    "comm_network_envir");
                $sel_subename=new CSelect("SELECT '---��������...' fsename,-1 fseid 
                        union SELECT concat(e.ename,'/',se.ename) fsename,eid*100000+seid fseid
                                FROM subexpense se left join expense e using(eid) 
                        union SELECT concat(e.ename,'/...') fsename,eid*100000 fseid FROM expense e 
                        order by fsename", 
                    "map_obj_subexpense", 
                    (($_REQUEST["map_obj_subexpense"]) ? $_REQUEST["map_obj_subexpense"] : -1), 
                    "comm_network_subexpense");
                $sel_builder=new CSelect("SELECT '��������...',-1 union select bname,bid FROM builder", "map_obj_builder", 2, "comm_network_builder");
                $sel_owner=new CSelect("SELECT '��������...',-1 union select oname,oid FROM owner", "map_obj_owner", 1, "comm_network_owner");
                $sel_complexity=new CSelect("SELECT '��������...',-1 union select cxname,cxid FROM complexity", "map_obj_complexity", 1, "comm_network_complexity");
                $sel_ps_teh_podkl=new CSelect("SELECT '��������...',-1 union select name,id FROM ps_teh_podkl", 
                        "map_obj_ps_teh_podkl", 
                        (($_REQUEST["map_obj_ps_teh_podkl"]) ? $_REQUEST["map_obj_ps_teh_podkl"] : 3), 
                        "comm_network_ps_teh_podkl");
                //echo "<!-- ".$cnaname->htmlel ." -->";
                echo "<form name='comm_obj_form' method='post' action='vlg_call.php?action=call_edit'>".
                        "<table border='1' cellspacing='1' cellpadding='2'><tr><td colspan=9 >".
			"<input type='hidden' name='lid' value='" . $_REQUEST["lid"] . "'>".
                        "<input type='hidden' name='map_obj_type' value='4'>".
			"<input type='hidden' name='add_to_project_button' value='cansel'>".
                        "<b>��� ������� ����� </b>".
                        "<br>������.(�����.):<input type='text' name='map_obj_equip_name' size='16' value=''>".
                        "&nbsp ���-��:<input type='text' name='map_obj_equip_amount' size='16' value='1'> ��".
                        "&nbsp ������:<input type='text' name='map_obj_equip_size' size='16' value='1'> ��".
                        "<br>������� �:<input type='text' name='map_obj_capacity1' size='16' value='1'> ".
                        "&nbsp ������� Y:<input type='text' name='map_obj_capacity2' size='16' value='1'> ".
                        "<br>����������:<input type='text' name='comment' size='80' value=''>".
                        "<br>�������:".$sel_cnaname->htmlel .
                        "&nbsp <b style='color: #090;'>���/������ ������:</b>".$sel_subename->htmlel .
                        "&nbsp <b style='color: #090;'>���������:</b>".$sel_cename->htmlel .
                        "<br>�����������:".$sel_builder->htmlel ."&nbsp ����������:".$sel_ps_teh_podkl->htmlel .
                        "<br> ��������:".$sel_owner->htmlel."������.<input type='text' name='map_obj_owner_add' size='40' value=''>".
                        "<br>��������� ������:".$sel_complexity->htmlel."������.<input type='text' name='map_obj_complexity_add' size='40' value=''>".
                        "</td></tr>
                        ";
		echo "<tr><td colspan=8 style='color: #000;background: #f8f0f0' onclick='comm_obj_form.submit();'>
                        <b style='color: #900;'>������</b></td>
                    <td colspan=8 style='color: #000;background: #f0f8f0' onclick=' addHidden(document.comm_obj_form, \"addcommobject\", true); comm_obj_form.submit(); '>
                        <b style='color: #090;'>����������� ����������</b></td></tr>";
                echo "<tr>
                    <td align='center' class='prjheader'><b>�������</b></td>
                    <td align='center' class='prjheader'><b>������ ������</b></td>
                    <td align='center' class='prjheader'><b>��������</b></td>
                    <td align='center' class='prjheader'><b>���������</b></td>
                    <td align='center' class='prjheader'><b>�����������</b></td>
                    <td align='center' class='prjheader'><b>���/���.</b></td>
                    <td align='center' class='prjheader'><b>��.���������</b></td>
                    <td align='center' class='prjheader'><b>�������</b></td>
                    <td align='center' class='prjheader'><b>����������</b></td>
                    </tr>";
                $where=" where 1=1 ";
//                if($_REQUEST["map_obj_subexpense"] and $_REQUEST["map_obj_subexpense"]!=-1){
//                    $where.=" and rcm.subeid=".$_REQUEST["map_obj_subexpense"]." ";
//                }
                if($_REQUEST["map_obj_subexpense"] and $_REQUEST["map_obj_subexpense"]!=-1){
                    
                    $where.=" and rcm.eid=".floor($_REQUEST["map_obj_subexpense"]/100000) . " ";
                    if($_REQUEST["map_obj_subexpense"]%100000 > 0) $where.=" and rcm.subeid=".$_REQUEST["map_obj_subexpense"]%100000 ." ";
                    
                }
                if($_REQUEST["map_obj_cn_envir"] and $_REQUEST["map_obj_cn_envir"]!=-1){
                    $where.=" and rcm.ceid=".$_REQUEST["map_obj_cn_envir"]." ";
                }
                //d(ref_com_object_query($where," order by exp.ename,sube.ename,ce.cename,rcm.name "));
                $cursor=SQL(ref_com_object_query($where," order by exp.ename,sube.ename,ce.cename,rcm.name "));
                while ($cursor->assoc()) {
                    
                    echo "<tr>
                        <td align='center' class=''>".$cursor->r["cnaname"]."</td>
                        <td align='center' class=''>".$cursor->r["ename"]."/".$cursor->r["subename"]."</td>
                        <td align='center' class=''>".$cursor->r["ooname"]."</td>
                        <td align='center' class=''>".$cursor->r["cename"]."</td>
                        <td align='center' class=''>".$cursor->r["bname"]."</td>
                        ";
                    $celltext="";
                    if(!empty($cursor->r["rcmname"])) $celltext.=$cursor->r["rcmname"]." ";
                    if(!empty($celltext)) $celltext=trim($celltext); else $celltext=$cursor->r["mgroup"];
                    if(!empty(trim($cursor->r["rcmcapacity1"]))) $celltext.= " / ". $cursor->r["rcmcapacity1"] ."";
                    if(!empty($cursor->r["rcmcapacity2"])) $celltext.= "x". $cursor->r["rcmcapacity2"] ."";
                    echo"<td align='center' style='color: #000;background: #f0f0f0;'
			onclick='comm_obj_form.add_to_project_button.value=".$cursor->r["rcmid"]."; comm_obj_form.submit();'>
			<b style='color: #009;' title=''>". (empty($celltext) ? "��.����." : $celltext )."</b></td>
                        <td align='center' class=''>".$cursor->r["rcmprice"]." ���.</td>
                        <td align='center' class=''>".$cursor->r["rcmunit"]."</td>".
                        "<td align='left' class=''>".
                            (empty(trim($cursor->r["comment"])) ? "" : "[".$cursor->r["comment"]."]") 
                        ."".$cursor->r["mgroup"].
                        (empty(trim($cursor->r["pgroup"])) ? "" : (" / ".$cursor->r["pgroup"]) ) ."</td>".
                        "</tr>";
                }
                $cursor->free();
                echo '</table></form>';
            ////////////////////////////////////////////////////////////////////
            } else if($_REQUEST["addcommobject_cot"]=='true'){
            // ����� ���������� ������� ����� (map_obj_type=4)
                echo "<form name='comm_obj_form' id='comm_obj_form_id' method='post' action='vlg_call.php?action=call_edit'>".
			"<input type='hidden' name='lid' value='" . $_REQUEST["lid"] . "'>".
                        "<input type='hidden' name='map_obj_type' value='4'>".
			"<input type='hidden' name='add_to_project_button' value='cansel'>".
			"<input type='hidden' name='add_object_id' value='cansel'>".
                        "<table border='1' cellspacing='1' cellpadding='2'><tr><td id='sel_for_level_cont' colspan=9 >".
                        "<span class='emtextblue' >������ � ������ o������</span><br>";
//                $level_arr=forest_get_level($_REQUEST["cot_vertex"]);
//                $sel_for_level[0]=array("������",-1);
//                for($i=1; $i<count($level_arr); $i++){
//                    $sel_for_level[$i]=array("(".$level_arr[$i][2].")".$level_arr[$i][4] , $level_arr[$i][6] ."__".$level_arr[$i][3] ."__1");
//                }
//                echo "<br>"."(".$level_arr[0][2].")".$level_arr[0][4].": ".(new CSelect($sel_for_level, "sel_for_level1", -1,"",
//                        " onchange='onCOTLevelChange(this,".$_REQUEST["cot_vertex"].");' "))->htmlel ."</td></tr>";
		echo "<tr><td colspan=3 style='color: #000;background: #f8f0f0' onclick='comm_obj_form.submit();'>
                        <span class='emtextred'>������</span></td>
                    <td id='sel_for_level_new' colspan=3 style='color: #000;background: #f0f8f0' 
                        onclick=' addHidden(document.comm_obj_form, \"addcommobject_cot\", true); document.comm_obj_form.submit(); '>
                        <span class='emtextgreen'>������ �����</span></td>
                    <td id='sel_for_level_add' colspan=3 style='color: #000;background: #f0f8f0' onclick='  '>
                        <span class='emtextgreen'></span></td></tr>";
                echo '</table></form>';
                //
                echo "<script>            
                    $(document).ready(function(){
                        preLoadDB();
                        $('#sel_for_level_cont').append(onCOTLevelChangeSel(-1));
                    });
                </script>";
            ////////////////////////////////////////////////////////////////////
            } else if($_REQUEST["addworkpath"]=='true'){
            // ����� ���������� ����� ��� (map_obj_type=5)
                $sel_cnaname=new CSelect("SELECT '��������...',-1 union select cnaname,cnaid FROM cn_area", "map_obj_cn_area", -1, "comm_network_area");
                $sel_builder=new CSelect("SELECT '��������...',-1 union select bname,bid FROM builder", "map_obj_builder", -1, "comm_network_builder");
                echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>���������� ����� ���</b>&nbsp;</legend>";
                echo "<form name='comm_obj_form' method='post' action='vlg_call.php?action=call_edit'>".
                    "<input type='hidden' name='lid' value='" . $_REQUEST["lid"] . "'>".
                    "<input type='hidden' name='map_obj_type' value='5'>".
                    "<input type='hidden' name='add_to_project_button' value='submit'>".
                    "<br>�������:".$sel_cnaname->htmlel .
                    "<br>���� ������ (����):<input type='date' name='startdateplan' size='12' value='2018-01-01'>
                    &nbsp; ���� ������ (����):<input type='date' name='startdate' size='12' value='2018-01-01'>
                    <br>���� ��������� (����):<input type='date' name='targetdate' size='12' value='2018-01-01'>
                    &nbsp; ���� ��������� (����):<input type='date' name='finishdate' size='12' value='2018-01-01'>
                    <br>������� (...):<input type='text' name='capacity' size='20' value='0'>
                    <br>��������� (����):<input type='text' name='realcost' size='20' value='0'>
                    <br>�����������:".$sel_builder->htmlel .
                    "<br>����������:<input type='text' name='comment' size='80' value=''><br>
                    <input type='submit' value='��������� ���������'> 
                    <input type='button' onclick=' window.location = \"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"].
                        "&lid=".$_REQUEST["lid"] ."\"; ' value='���������'/>        
                </form></fieldset>";
            } else if($_REQUEST["edit_map_obj"]){
            // ����� �������������� ��� � ������������
            // !!! ��������
                echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>�������������� ��� � ������������</b>&nbsp;</legend>";
                echo "<form name='form_map_obj' method='post' style='' "
                        . "action='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". $_REQUEST["lid"] 
                        . "&save_map_obj=".$_REQUEST["edit_map_obj"]."&map_obj_id=" . $_REQUEST["map_obj_id"] . "'>";
                if($_REQUEST["edit_map_obj"]==1){
                    $map_obj_row=rSQL("SELECT * FROM ps_equip_cid where stype=1 and id=".$_REQUEST["map_obj_id"]);
                } else {
                    $map_obj_row=rSQL("SELECT * FROM ps_smet_cid where stype=1 and id=".$_REQUEST["map_obj_id"]);
                }
                echo "����������:<input type='text' name='comment' size='80' value='".$map_obj_row["comment"] ."'><br>
                    ����������/������:<input type='text' name='kol' size='20' value='".$map_obj_row["kol"] ."'><br><br>
                    <input type='submit' value='��������� ���������'> 
                    <input type='button' onclick=' window.location = \"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=" 
                        . $_REQUEST["lid"] . "\"; ' value='���������'/>        
                </form></fieldset>";
            // ^^^ !!! �������� ^^^
            } else if($_REQUEST["edit_workpath"]=='true'){
            // ����� �������������� ����� ��� 
                $cursor=rSQL("SELECT wp.*,wp.cnaid,cna.cnaname,wp.bid,b.bname
                    FROM workpath wp 
                    left join cn_area cna on wp.cnaid=cna.cnaid
                    left join builder b on wp.bid=b.bid
                    where object_type=2 and lp_id=".$_REQUEST["lid"] ." and wid=".$_REQUEST["map_obj_id"]);
                $sel_cnaname=new CSelect("SELECT '��������...',-1 union select cnaname,cnaid FROM cn_area", "map_obj_cn_area", $cursor["cnaid"], "comm_network_area");
                $sel_builder=new CSelect("SELECT '��������...',-1 union select bname,bid FROM builder", "map_obj_builder", $cursor["bid"], "comm_network_builder");
                echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>�������������� ����� ���</b>&nbsp;</legend>";
                echo "<form name='comm_obj_form' method='post' style='' "
                        . "action='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". $_REQUEST["lid"] 
                        . "&save_workpath=true&map_obj_id=" . $_REQUEST["map_obj_id"] . "'>
                    �������:".$sel_cnaname->htmlel .
                    "<br>���� ������ (����):<input type='date' name='startdateplan' size='12' value='".explode(" ",$cursor["startdateplan"])[0] ."'>
                    &nbsp; ���� ������ (����):<input type='date' name='startdate' size='12' value='".explode(" ",$cursor["startdate"])[0] ."'>
                    <br>���� ��������� (����):<input type='date' name='targetdate' size='12' value='".explode(" ",$cursor["targetdate"])[0] ."'>
                    &nbsp; ���� ��������� (����):<input type='date' name='finishdate' size='12' value='".explode(" ",$cursor["finishdate"])[0] ."'>                    
                    <br>�����������:".$sel_builder->htmlel .
                    "<br>�������:<input type='text' name='capacity' size='16' value='".$cursor["capacity"] ."'><br>
                    C��������(����):<input type='text' name='realcost' size='40' value='".$cursor["realcost"] ."'><br>
                    ����������:<input type='text' name='comment' size='80' value='".$cursor["comment"] ."'><br>
                    <br>
                    <input type='submit' value='��������� ���������'> 
                    <input type='button' onclick=' window.location = \"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=" 
                        . $_REQUEST["lid"] . "\"; ' value='���������'/>        
                </form></fieldset>";
            } else if($_REQUEST["edit_comm_obj"]=='true'){
            // ����� �������������� ������� �����
                $cursor=rSQL("SELECT ccmid,rcm.mgroup,rcm.pgroup,ccm.cnaid,cna.cnaname,ccm.subeid,sube.ename subename,
                    ccm.oid,o.oname ooname,ccm.oname,
                    ccm.cxid,cx.cxname,ccm.cxcomment,
		    ccm.technology,tp.name tpname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.bid,b.bname,ccm.rcmid,rcm.name rcmname,pld.arm_id,ccm.lid,
                    max(ccm.ccmname) ccmname,
                    sum(ccm.amount) ccmamount,sum(ccmlen) ccmlen,
                    sum(ccm.capacity1) ccmcapacity1,sum(ccm.capacity2) ccmcapacity2,
                    max(ccm.price) ccmprice,max(ccm.comment) comment,
                    max(rcm.price) rcmprice,max(rcm.unit) rcmunit,max(rcm.capacity1) rcmcapacity1,max(rcm.capacity2) rcmcapacity2
                FROM call_com_mat ccm 
                left join ref_com_mat rcm using(rcmid)
                left join cn_eq_type cet using(cetid) 
                left join ps_list_dop pld on ccm.stype=1 and pld.lid=ccm.lid
                left join sign_envir se on ccm.seid=se.seid
                left join cn_area cna on ccm.cnaid=cna.cnaid
                left join cn_envir ce on ccm.ceid=ce.ceid
                left join builder b on ccm.bid=b.bid
                left join subexpense sube on ccm.subeid=sube.seid
                left join owner o on ccm.oid=o.oid
                left join complexity cx on ccm.cxid=cx.cxid
                left join ps_teh_podkl tp on ccm.technology=tp.id
                where ccm.stype=1 and ccmid=".$_REQUEST["map_obj_id"]);
                $sel_cnaname=new CSelect("SELECT '��������...',-1 union select cnaname,cnaid FROM cn_area", "map_obj_cn_area", $cursor["cnaid"], "comm_network_area");
                $sel_cename=new CSelect("SELECT '��������...',-1 union select cename,ceid FROM cn_envir", "map_obj_cn_envir", $cursor["ceid"], "comm_network_envir");
                $sel_builder=new CSelect("SELECT '��������...',-1 union select bname,bid FROM builder", "map_obj_builder", $cursor["bid"], "comm_network_builder");
                $sel_subename=new CSelect("SELECT '��������...',-1 union select ename,seid FROM subexpense", "map_obj_subexpense", $cursor["subeid"], "comm_network_subexpense");
                $sel_owner=new CSelect("SELECT '��������...',-1 union select oname,oid FROM owner", "map_obj_owner", $cursor["oid"], "comm_network_owner");
                $sel_complexity=new CSelect("SELECT '��������...',-1 union select cxname,cxid FROM complexity", "map_obj_complexity", $cursor["cxid"], "comm_network_complexity");
                $sel_ps_teh_podkl=new CSelect("SELECT '��������...',-1 union select name,id FROM ps_teh_podkl", "map_obj_ps_teh_podkl", $cursor["technology"], "comm_network_ps_teh_podkl");
                echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>�������������� ������� �����</b>&nbsp;</legend>";
                echo "<form name='comm_obj_form' method='post' style='' "
                        . "action='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". $_REQUEST["lid"] 
                        . "&save_comm_obj=true&map_obj_id=" . $_REQUEST["map_obj_id"] . "'>
                    ������������(�����������):(".$cursor["ccmid"] .")<input type='text' name='ccmname' size='40' value='".$cursor["ccmname"] ."'><br>"
                    ."[".$cursor["mgroup"]." / ".$cursor["pgroup"]."]<br>
                    ����������:<input type='text' name='comment' size='80' value='".$cursor["comment"] ."'><br>
                    ���-��:<input type='text' name='map_obj_equip_amount' size='16' value='".$cursor["ccmamount"] ."'> �� &nbsp 
                    ������:<input type='text' name='map_obj_equip_size' size='16' value='".$cursor["ccmlen"] ."'> ��<br>
                    ������� �:<input type='text' name='map_obj_capacity1' size='16' value='".$cursor["ccmcapacity1"] ."'> &nbsp 
                    ������� Y:<input type='text' name='map_obj_capacity2' size='16' value='".$cursor["ccmcapacity2"] ."'> 
                    <br>�������:".$sel_cnaname->htmlel ."&nbsp ��� ������:".$sel_subename->htmlel."&nbsp ���������:".$sel_cename->htmlel.
                    "<br>�����������:".$sel_builder->htmlel ."&nbsp ����������:".$sel_ps_teh_podkl->htmlel .
                    "<br> ��������:".$sel_owner->htmlel."������.<input type='text' name='map_obj_owner_add' size='40' value='".$cursor["oname"] ."'>".
                    "<br>��������� ������:".$sel_complexity->htmlel."������.<input type='text' name='map_obj_complexity_add' size='40' value='".$cursor["cxcomment"] ."'>".
                    "<br><br>
                    <input type='submit' value='��������� ���������'> 
                    <input type='button' onclick=' window.location = \"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=" 
                        . $_REQUEST["lid"] . "\"; ' value='���������'/>        
                </form></fieldset>";
            } else {
            // �������������� ������
                // HTML-����� ����� ���� �����
                $project_type_HTML="<b>[���]/[������] �����</b><table>";
                $project_type_cursor=SQL("SELECT concat('<tr><td onclick=\' setTypeSubtype(\\\\\"',mgroup,'\\\\\",\\\\\"',pgroup,'\\\\\");  \' style=\'color: #000;background: #eee\'><b style=\'color: #009;\'>',
                    mgroup,'</b><br>',pgroup,'</td></tr>') sc
                    FROM ps_smet_calc group by mgroup,pgroup order by mgroup,pgroup");
		$project_type_HTML.= "<tr><td style='color: #000;background: #f8f0f0' onclick=' setTypeSubtype(\\\"cansel\\\",\\\"cansel\\\"); '><b style='color: #900;'>������</b></td></tr>";
                while ($project_type_cursor->assoc()) {
                    $project_type_HTML.=$project_type_cursor->r["sc"];
                }
                $project_type_cursor->free();
                $project_type_HTML.='</table>';
                ////////////////////////////////////////////////////////////////
                // ����� �������������� ������
                echo "<fieldset style='padding: 20px; width: 90%;border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>�������������� ������</b>&nbsp;</legend>";
                echo "<form name='new_project' method='post' enctype='multipart/form-data' style='' action='vlg_call.php?action=save_call&sourcepage=".$_REQUEST["sourcepage"]."&lid=". 
                        $_REQUEST["lid"] . "'>
                    <nobr>�����:".$call_row_dop["arm_id"] ."</nobr>
                    <nobr>������:<b>".$call_row_status["name"] ."</b></nobr><br>
                    <nobr>���<input type='text' name='client_fio' size='60' value='".$call_row["client_fio"] ."'>&nbsp</nobr>
                    <nobr>�������<input type='text' name='contact_phone' size='60' value='".$call_row["contact_phone"] ."'>&nbsp</nobr>
                    <nobr>�����: ".$call_row["settlement"] ." ".$call_row["ul"] ." ".$call_row["home"] ."</nobr><br>";
                echo "���������� ";
                if($call_row_dop["tpid"]!=-1)
                    echo select2('technology', "select '��������...',-1 union SELECT concat(id,'. ',name),id FROM ps_teh_podkl",
                        rSQL("SELECT concat(id,'. ',name) rstr FROM ps_teh_podkl where id='".$call_row_dop["tpid"] ."'")["rstr"]);
                else
                    echo select2('technology', "select '��������...',-1 union SELECT concat(id,'. ',name),id FROM ps_teh_podkl","��������...");
                echo " ������ ";
                if($call_row_dop["service_id"]!=-1)
                    echo select2('service_id', "select '��������...',-1 union SELECT concat(id,'. ',sname),id FROM service",
                        rSQL("SELECT concat(id,'. ',sname) rstr FROM service where id='".$call_row_dop["service_id"] ."'")["rstr"]);
                else
                    echo select2('service_id', "select '��������...',-1 union SELECT concat(id,'. ',sname),id FROM service","��������...");
                /*echo "<br>
                    <!--div style='display:inline-block; padding:2px; border:solid 1px darkblue; margin-top: 6px;'>
                    ������������� ��������� ����� 42 U:<input type='text' name='shkaf_42u' size='6' value='".$call_row_dop["shkaf_42u"] ."'>
                    ������������� ��������� ����� OLT:<input type='text' name='shassi_olt' size='6' value='".$call_row_dop["shassi_olt"] ."'>
                    ��������� ����� ������:<input type='text' name='kol_ports' size='6' value='".$call_row_dop["kol_ports"] ."'>
                    ������������� ��������� ������������ ���:<input type='text' name='spd' size='6' value='".$call_row_dop["spd"] ."'>
                    <br>��������� ��<input type='text' name='difficult_mc' size='6' value='".$call_row_dop["difficult_mc"] ."'>
                    ��������� ��<input type='text' name='difficult_rs' size='12' value='".$call_row_dop["difficult_rs"] ."'>
                    ��������� ����.�����<input type='text' name='difficult_abl' size='10' value='".$call_row_dop["difficult_abl"] ."'>
                    ��������� ����.����<input type='text' name='difficult_abv' size='3' value='".$call_row_dop["difficult_abv"] ."'>
                    ������� ������� PON � ��������:<input type='text' name='pon_flag' size='3' value='".$call_row_dop["pon_flag"] ."'>
                    </div-->
                    <br><div style='display:inline-block; padding:2px; margin-top: 6px;'>
                        ���������<input type='text' name='deferredpay' size='6' value='".$call_row_dop["deferredpay"] ."'>���. "
                        . "������.�����<input type='text' name='install' size='16' value='".$call_row_dop["install"] ."'>���. "
                        . "������� �� ����.�����<input type='text' name='guarantee' size='20' value='".$call_row_dop["guarantee"] ."'></div>
                    <br><div style='display:inline-block; padding:2px; margin-top: 6px;'>
                        �����<input type='text' name='tariffname' size='30' value='".$call_row_dop["tariffname"] ."'> "
                        . "������.�����<input type='text' name='month_pay' size='16' value='".$call_row_dop["month_pay"] ."'>��� 
                            <a href=\"javascript:void(0)\" onclick=\"document.getElementById('tariff_selection_darkening').style.display = 'block';\">����� ������</a></div>
                    <br><div style='display:inline-block; padding:2px; margin-top: 6px;'>
                        ONT: �������<input type='text' name='ontfullpay' size='12' value='".$call_row_dop["ontfullpay"] ."'>���. 
                        ������<input type='text' name='ontlease' size='12' value='".$call_row_dop["ontlease"] ."'>���. 
                    </div>
                    <br><div style='display:inline-block; padding:2px; margin-top: 6px;'>
                        ������: ������� <input type='text' name='routefullpay' size='12' value='".$call_row_dop["routefullpay"] ."'>���. 
                        ������<input type='text' name='routelease' size='12' value='".$call_row_dop["routelease"] ."'>���. 
                    </div>
                    <br><div style='display:inline-block; padding:2px; margin-top: 6px;'>
                        ���������: ���-�� <input type='text' name='attachnum' size='3' value='".$call_row_dop["attachnum"] ."'>��.
                        �������<input type='text' name='attachfullpay' size='12' value='".$call_row_dop["attachfullpay"] ."'>���. 
                        ������<input type='text' name='attachlease' size='10' value='".$call_row_dop["attachlease"] ."'>���. 
                    </div>";*/
                    /*<br>����.����. �� ����:<input type='text' name='realcost' size='12' value='".$call_row_dop["realcost"] ."'>���.
                    <br>��������� ����� �� �� ��������:<input type='text' name='substatus' size='12' value='".$call_row_dop["substatus"] ."'>".*/
                    /*<br>����������� ���� ���������:<input type='date' name='targetdate' size='12' value='".explode(" ",$call_row_dop["targetdate"])[0] ."'>
                    <br>���� �����������:<input type='date' name='finishdate' size='12' value='".explode(" ",$call_row_dop["finishdate"])[0] ."'>*/
                echo "<br>���� ���������� �������� � ���������:<input type='date' name='finishdate' size='12' value='".explode(" ",$call_row_dop["finishdate"])[0] ."'>".
                    "<br>";
                // ������ ������ �� �������� ����� ������
                $cursor=SQL($com_object_query);
                $all_expense=0.0;
                while ($cursor->assoc()) {
                    $expense=round($cursor->r["rcmprice"] 
                                * (($cursor->r["ccmlen"]!=0)? $cursor->r["ccmlen"] : 1.0) 
                                * (($cursor->r["ccmamount"]!=0)? $cursor->r["ccmamount"] : 1.0) 
                                * (($cursor->r["bid"]==1)? 0.7 : 1.0),2); // ���� "���������", �� *0.7
                    $all_expense+=$expense;
                }
                $cursor->free();
                //
                echo "<nobr>������� �� ��� (����):<input type='text' name='zatrat_smr' size='40' value='".$call_row_dop["zatrat_smr"] ."'>&nbsp</nobr>
                    <nobr>������� �� ��� (����):<input type='text' name='zatrat_smr_plan' size='40' value='".$all_expense ." ���.' disabled></nobr>
                    <!--nobr>������� �� ������������ (����):<input type='text' name='dev_summ' size='40' value='".$call_row_dop["dev_summ"] ."'></nobr-->
                    <br>
                    <div style='display:inline-block; '>����������:<textarea name='comment' rows='4' cols='100'>".
                        $call_row_dop["comment"] ."</textarea></div><br>
                    <div style='display:inline-block; padding:2px; border:solid 1px darkblue; margin-top: 6px;'>
                    <img src='./images/save.gif' align='absmiddle'> �������� ����.�����(�������� ����)
                        <input type='file' name='drawing[]' id='drawing' multiple> 
                    <a href='vlg_image.php?otype=2&oid=".$_REQUEST["lid"] ."' target='_blank'>
                        <img src='./images/search.gif' align='absmiddle'> �������� �����</a>&nbsp&nbsp&nbsp       
                    <img src='./images/aff_cross.gif' align='absmiddle'> ������� �����<input type='checkbox' name='delschema' value='true'>
                    </div><br>                            
                    <br><div style='display:inline-block; margin-top: 10px;'>
                        <input type='submit' value='��������� ���������' ".$ubord->mayIdo("button") ." >
                    </div>";
                if($_REQUEST["sourcepage"]=="project"){
                    //echo "<input type='button' onclick=' window.location = \"vlg_project.php?action=edit_project&project_id=".
                    //    $_REQUEST["project_id"]."\"; ' value='���������'/>";
                    // �������� ��� !!! ��� �������� �������
                    echo "<input type='button' onclick=' window.location = \"vlg_project.php\"; ' value='���������'/>";
                }else{
                    echo "<input type='button' onclick=' window.location = \"index.php?c=2\"; ' value='���������'/>";
                }
                echo "</form></fieldset>";
                ////////////////////////////////////////////////////////////////
                // vv ��������� �� �������� ��� ����������� ���������� ������ vv
                // ���������� � ������� ��� � ������������
                if(isset($_REQUEST["add_to_project_button"])){
                    if($_REQUEST["add_to_project_button"]=='cansel'){

                    }else{
                        switch($_REQUEST["map_obj_type"]){
                        case 1:
                            SQL("INSERT INTO ps_equip_cid values (NULL,'".$_REQUEST["lid"]."', '".
                                $_REQUEST["add_to_project_button"]."',1, '".$_REQUEST["ps_size"]."', '".$row_users["uid"]."','".
                                $udate_time . "',0,'".$_REQUEST["comment"] ."',1,NULL)")->commit();
                        break;
                        case 2:
                            SQL("INSERT INTO ps_smet_cid values (NULL,'".$_REQUEST["lid"]."', '".
                                $_REQUEST["add_to_project_button"]."',0, '".$_REQUEST["ps_size"]."', '".
                                $row_users["uid"]."',now(),0,'".$_REQUEST["comment"] ."',1,NULL)")->commit();
                        break;
                        case 4:
                            $arr_ref_com_mat=explode("__",$_REQUEST["map_obj_ref_com_mat"]);
                            if(isset($_REQUEST["map_obj_cn_area"])) true;
                            else $_REQUEST["map_obj_cn_area"]="0__-1";
                            if(count($arr_ref_com_mat)>1){
                                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                                        technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2)
                                    VALUES (1,".$_REQUEST["lid"] .",".$_REQUEST["add_to_project_button"] .",".
                                        explode("__",$_REQUEST["map_obj_cn_area"])[1] .",NULL,".
                                        (($_REQUEST["map_obj_cn_envir"]) ? explode("__",$_REQUEST["map_obj_cn_envir"])[1] : "NULL") .",'". 
                                        $_REQUEST["map_obj_equip_amount"] ."','".$_REQUEST["map_obj_equip_size"] ."',0.0,'". 
                                        $row_users["uid"] ."',now(),'".$_REQUEST["comment"] ."',NULL,'".$_REQUEST["map_obj_equip_name"] ."','".
                                        explode("__",$_REQUEST["map_obj_ps_teh_podkl"])[1] ."','".$_REQUEST["map_obj_builder"] ."',NULL,'".
                                        $_REQUEST["map_obj_owner"] ."','".$_REQUEST["map_obj_owner_add"] ."','".
                                        $_REQUEST["map_obj_complexity"] ."','".$_REQUEST["map_obj_complexity_add"] ."','".
                                        $_REQUEST["map_obj_subexpense"] ."','".
                                        $_REQUEST["map_obj_capacity1"] ."','".
                                        $_REQUEST["map_obj_capacity2"] ."')") -> commit();
                                
                            }else{
                            SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2)
                                VALUES (1,".$_REQUEST["lid"] .",".$_REQUEST["add_to_project_button"] .",".
                                    $_REQUEST["map_obj_cn_area"] .",NULL,".$_REQUEST["map_obj_cn_envir"] .",'". 
                                    $_REQUEST["map_obj_equip_amount"] ."','".$_REQUEST["map_obj_equip_size"] ."',0.0,'". 
                                    $row_users["uid"] ."',now(),'".$_REQUEST["comment"] ."',NULL,'".$_REQUEST["map_obj_equip_name"] ."','".
                                    $_REQUEST["map_obj_ps_teh_podkl"] ."','".$_REQUEST["map_obj_builder"] ."',NULL,'".
                                    $_REQUEST["map_obj_owner"] ."','".$_REQUEST["map_obj_owner_add"] ."','".
                                    $_REQUEST["map_obj_complexity"] ."','".$_REQUEST["map_obj_complexity_add"] ."','".
                                    $_REQUEST["map_obj_subexpense"] ."','".
                                    $_REQUEST["map_obj_capacity1"] ."','".
                                    $_REQUEST["map_obj_capacity2"] ."')") -> commit();
                            }
                        break;                    
                        case 5:
                            if(rSQL("SELECT count(*) cnt FROM workpath where object_type=2 and lp_id=".$_REQUEST["lid"] .
                                " and cnaid=".$_REQUEST["map_obj_cn_area"] ."")["cnt"]==0){
                                SQL("INSERT INTO workpath (lp_id,object_type,cnaid,realcost,targetdate,finishdate,startdate,
                                        capacity,comment,status,uid,dateedit,bid,realcost2,startdateplan)
                                    VALUES (".$_REQUEST["lid"] .",2,".
                                        $_REQUEST["map_obj_cn_area"] .",".$_REQUEST["realcost"] .
                                        ",STR_TO_DATE('".(empty($_REQUEST["targetdate"]) ? "0000-00-00" : $_REQUEST["targetdate"]) ."','%Y-%m-%d'),".
                                        "STR_TO_DATE('".(empty($_REQUEST["finishdate"]) ? "0000-00-00" : $_REQUEST["finishdate"]) ."','%Y-%m-%d'),".
                                        "STR_TO_DATE('".(empty($_REQUEST["startdate"]) ? "0000-00-00" : $_REQUEST["startdate"]) ."','%Y-%m-%d'),".
                                        $_REQUEST["capacity"] .",'".$_REQUEST["comment"] ."',0,'".
                                        $row_users["uid"] ."',now(),'".$_REQUEST["map_obj_builder"] ."',0,".
                                        "STR_TO_DATE('".(empty($_REQUEST["startdateplan"]) ? "0000-00-00" : $_REQUEST["startdateplan"]) ."','%Y-%m-%d'))") -> commit();
                            }else{
                            // ����� ����/������� ��� ����
                            }
                        break;                    
                        }
                    }
                }
                // ���������� � ������� ��� � ������������
                if($_REQUEST["save_map_obj"]){ // 2-��� � 1-������������
                    if($_REQUEST["save_map_obj"]==1){
                        SQL("UPDATE ps_equip_cid SET kol='".$_REQUEST["kol"]."',comment='".$_REQUEST["comment"].
                            "' WHERE stype=1 and id=".$_REQUEST["map_obj_id"]." ");
                    } else {
                        SQL("UPDATE ps_smet_cid SET kol='".$_REQUEST["kol"]."',comment='".$_REQUEST["comment"].
                            "' WHERE stype=1 and id=".$_REQUEST["map_obj_id"]." ");
                    }
                }
                // ���������� � ������� �������� �����
                if($_REQUEST["save_comm_obj"]=='true'){
                    SQL("update call_com_mat set ccmname='".$_REQUEST["ccmname"]."',comment='".$_REQUEST["comment"].
                            "',amount='".$_REQUEST["map_obj_equip_amount"]."',ccmlen='".$_REQUEST["map_obj_equip_size"].
                            "',cnaid=".$_REQUEST["map_obj_cn_area"].",ceid=".$_REQUEST["map_obj_cn_envir"].
                            ",subeid=".$_REQUEST["map_obj_subexpense"].
                            ",oid=".$_REQUEST["map_obj_owner"].",oname='".$_REQUEST["map_obj_owner_add"].
                            "',cxid=".$_REQUEST["map_obj_complexity"].",cxcomment='".$_REQUEST["map_obj_complexity_add"].
                            "',technology=".$_REQUEST["map_obj_ps_teh_podkl"].
                            ",capacity1='".$_REQUEST["map_obj_capacity1"].
                            "',capacity2='".$_REQUEST["map_obj_capacity2"].
                            "',bid=".$_REQUEST["map_obj_builder"]."
                        where ccmid=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // ���������� � ������� ������ ���
                if($_REQUEST["save_workpath"]=='true'){
                    SQL("update workpath set comment='".$_REQUEST["comment"]."',
                            realcost='".$_REQUEST["realcost"]."',
                            capacity='".$_REQUEST["capacity"]."',
                            cnaid=".$_REQUEST["map_obj_cn_area"].",
                            bid=".$_REQUEST["map_obj_builder"].",
                            targetdate=STR_TO_DATE('".(empty($_REQUEST["targetdate"]) ? "0000-00-00" : $_REQUEST["targetdate"]) ."','%Y-%m-%d'),   
                            finishdate=STR_TO_DATE('".(empty($_REQUEST["finishdate"]) ? "0000-00-00" : $_REQUEST["finishdate"]) ."','%Y-%m-%d'),   
                            startdate=STR_TO_DATE('".(empty($_REQUEST["startdate"]) ? "0000-00-00" : $_REQUEST["startdate"]) ."','%Y-%m-%d'),   
                            startdateplan=STR_TO_DATE('".(empty($_REQUEST["startdateplan"]) ? "0000-00-00" : $_REQUEST["startdateplan"]) ."','%Y-%m-%d'),   
                            uid='".$row_users["uid"] ."',
                            dateedit=now()
                        where wid=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // �������� �� ������� ��� � ������������
                if($_REQUEST["delete_map_obj"]){ // 2-��� � 1-������������
                    //SQL("delete from map_obj where id=".$_REQUEST["map_obj_id"]) -> commit();
                    if($_REQUEST["delete_map_obj"]==1){
                        SQL("delete from ps_equip_cid where stype=1 and id=".$_REQUEST["map_obj_id"]) -> commit();
                    } else {
                        SQL("delete from ps_smet_cid where stype=1 and id=".$_REQUEST["map_obj_id"]) -> commit();
                    }
                }
                // �������� �� ������� �������� �����
                if($_REQUEST["delete_comm_obj"]=='true'){
                    SQL("delete from call_com_mat where ccmid=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // �������� �� ������� ������ ���
                if($_REQUEST["delete_workpath"]=='true'){
                    SQL("delete from workpath where wid=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // ^^ ��������� �� �������� ��� ����������� ���������� ������ ^^
                ////////////////////////////////////////////////////////////////
                // vvv �������������� �������� ����� vvv
                echo "<div style='float: left; display:inline-block;font-size: 14px; color: #00d;'><b>�������������� �������� �����</b></div>";
                echo "<div style='display:inline-block; float: right'>";
                echo "<button name='add_line_to_project_button' onclick=' window.location=\"vlg_call.php?action=call_edit&sourcepage=".
                        $_REQUEST["sourcepage"] ."&lid=".$_REQUEST["lid"] ."&addcommobject=true\"; '>�������� o�����</button>";
                echo "<button name='add_line_to_project_button' onclick=' window.location=\"vlg_call.php?action=call_edit&sourcepage=".
                        $_REQUEST["sourcepage"] ."&lid=".$_REQUEST["lid"] ."&addcommobject_cot=true&cot_vertex=1\"; '>������ � ������ o������</button>";
                echo "</div>";
                // ������ � html ������� �������� �����
                $cursor=SQL($com_object_query);
                // ����� �������
                echo "<style type=\"text/css\">
                        td.prjleftcol { color:#025; background:#EEF; }
                        td.prjheader { color:#037; background:#DEF; }
                        td.prjtype1 { color:#800; }
                        td.prjtype2 { color:#080; }
                    </style>";
                echo "<br><table width='100%' border='1' cellspacing='1' cellpadding='2'>";
                echo "<tr>
                    <td align='center' class='prjheader'><b></b></td>
                    <td align='center' class='prjheader'><b></b></td>
                    <td align='center' class='prjheader'><b>�������</b></td>
                    <td align='center' class='prjheader'><b>������ ������</b></td>
                    <td align='center' class='prjheader'><b>��������</b></td>
                    <td align='center' class='prjheader'><b>���������</b></td>
                    <td align='center' class='prjheader'><b>�����������</b></td>
                    <td align='center' class='prjheader'><b>���/���.</b></td>
                    <td align='center' class='prjheader'><b>���-��/������</b></td>
                    <td align='center' class='prjheader'><b>*��.�����*K���=���������</b></td>
                    <td align='center' class='prjheader'><b>�������</b></td>
                    <td align='center' class='prjheader'><b>����������</b></td>
                    </tr>";
                $all_expense=0.0;
                while ($cursor->assoc()) {
                    echo "<tr>
                        <td align='center' class='prjleftcol'>
                            <a href='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". 
                        $_REQUEST["lid"] ."&edit_comm_obj=true&map_obj_id=".$cursor->r["ccmid"] ."' title='�������������'>
                            <img src='./images/edit.gif' align='absmiddle'></a></td>
                        <td align='center'  title='�������' onclick=' if(confirm(\"������� ������ ����� [id".$cursor->r["ccmid"]."]?\"))
                                    window.location = \"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". 
                        $_REQUEST["lid"] ."&delete_comm_obj=true&map_obj_id=".$cursor->r["ccmid"] ."\"; '><b>X</b></td>";
                    echo "<td align='center' class=''>".$cursor->r["cnaname"]."</td>
                        <td align='center' class=''>".$cursor->r["subename"]."</td>
                        <td align='center' class=''>".$cursor->r["ooname"]."</td>
                        <td align='center' class=''>".$cursor->r["cename"]."</td>
                        <td align='center' class=''>".$cursor->r["bname"]."</td>";
                    $celltext="";
                    if(!empty($cursor->r["rcmname"])) $celltext.=$cursor->r["rcmname"]." ";
                    if(!empty($cursor->r["ccmname"])) $celltext.=$cursor->r["ccmname"]." ";
                    if(!empty($celltext)) $celltext=trim($celltext);
                    if(!empty($cursor->r["rcmcapacity1"])) $celltext.= " / ". $cursor->r["rcmcapacity1"] ."";
                    if(!empty($cursor->r["rcmcapacity2"])) $celltext.= "x". $cursor->r["rcmcapacity2"] ."";
                    echo"<td align='center' class=''>".$celltext."</td>";
                    $celltext="<b>";
                    if(!empty($cursor->r["ccmamount"])) $celltext.= round($cursor->r["ccmamount"]) ." �� ";
                    if(!empty($cursor->r["ccmlen"])) $celltext.= $cursor->r["ccmlen"] ." �� ";
                    if(!empty($celltext)) $celltext.="</b>";
                    $expense=round($cursor->r["rcmprice"] 
                                * (($cursor->r["ccmlen"]!=0)? $cursor->r["ccmlen"] : 1.0) 
                                * (($cursor->r["ccmamount"]!=0)? $cursor->r["ccmamount"] : 1.0) 
                                * (($cursor->r["bid"]==1)? 0.7 : 1.0),2); // ���� "���������", �� *0.7
                    $all_expense+=$expense;
                    echo "<td align='center' class=''>".$celltext."</td>
                        <td align='center' class=''>"."* ".$cursor->r["rcmprice"].
                                (($cursor->r["bid"]==1)? " * 0.7" : "")
                                ." = <b>".$expense."</b> ���.</td>
                        <td align='center' class=''>".$cursor->r["rcmunit"]."</td>
                        <td align='center' class=''>".$cursor->r["comment"]."</td>
                        </tr>";
                }
                echo "</table><div align='right'><nobr>����.����. �� ���.������ ".$all_expense ." ���.</nobr></div>";
                $cursor->free();
                // ^^^ �������������� �������� ����� ^^^
                ////////////////////////////////////////////////////////////////
                // vvv ����� ��� vvv
                echo "<br><div style='float: left; display:inline-block; font-size: 14px; color: #00d;'><b>����� ���</b></div>";
                echo "<div style='display:inline-block; float: right'>";
                echo "<button name='add_line_to_project_button' onclick=' window.location=\"vlg_call.php?action=call_edit&sourcepage=".
                        $_REQUEST["sourcepage"] ."&lid=".$_REQUEST["lid"] ."&addworkpath=true\"; '>�������� ���� ���</button>";
                echo "</div>";
                // ������ � html ������� ������ ���
                $cursor=SQL("SELECT wp.*,wp.cnaid,cna.cnaname,wp.bid,b.bname
                    FROM workpath wp 
                    left join cn_area cna on wp.cnaid=cna.cnaid
                    left join builder b on wp.bid=b.bid
                    where object_type=2 and lp_id=".$_REQUEST["lid"] ." 
                    order by wp.cnaid");
                // ����� �������
                echo "<style type=\"text/css\">
                        td.prjleftcol { color:#025; background:#EEF; }
                        td.prjheader { color:#037; background:#DEF; }
                        td.prjtype1 { color:#800; }
                        td.prjtype2 { color:#080; }
                    </style>";
                echo "<br><table width='100%' border='1' cellspacing='1' cellpadding='2'>";
                echo "<tr>
                    <td align='center' class='prjheader'><b></b></td>
                    <td align='center' class='prjheader'><b></b></td>
                    <td align='center' class='prjheader'><b>�������</b></td>
                    <td align='center' class='prjheader'><b>������(����)</b></td>
                    <td align='center' class='prjheader'><b>������(����)</b></td>
                    <td align='center' class='prjheader'><b>�����(����)</b></td>
                    <td align='center' class='prjheader'><b>�����(����)</b></td>
                    <td align='center' class='prjheader'><b>�����������</b></td>
                    <td align='center' class='prjheader'><b>�������</b></td>
                    <td align='center' class='prjheader'><b>C��������(����)</b></td>
                    <td align='center' class='prjheader'><b>����������</b></td>
                    </tr>";
                while ($cursor->assoc()) {
                    echo "<tr>
                        <td align='center' class='prjleftcol'>
                            <a href='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". 
                        $_REQUEST["lid"] ."&edit_workpath=true&map_obj_id=".$cursor->r["wid"] ."' title='�������������'>
                            <img src='./images/edit.gif' align='absmiddle'></a></td>
                        <td align='center'  title='�������' onclick=' if(confirm(\"������� ���� ��� [".$cursor->r["cnaname"]."]?\"))
                                    window.location = \"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". 
                        $_REQUEST["lid"] ."&delete_workpath=true&map_obj_id=".$cursor->r["wid"] ."\"; '><b>X</b></td>";
                    echo "<td align='center' class=''>".$cursor->r["cnaname"]."</td>
                        <td align='center' class=''>".$cursor->r["startdateplan"]."</td>
                        <td align='center' class=''>".$cursor->r["startdate"]."</td>
                        <td align='center' class=''>".$cursor->r["targetdate"]."</td>
                        <td align='center' class=''>".$cursor->r["finishdate"]."</td>
                        <td align='center' class=''>".$cursor->r["bname"]."</td>
                        <td align='center' class=''>".$cursor->r["capacity"]."</td>
                        <td align='center' class=''>".$cursor->r["realcost"]."</td>
                        <td align='center' class=''>".$cursor->r["comment"]."</td>
                        </tr>";
                }
                echo "</table>";
                $cursor->free();
                // ^^^ ����� ��� ^^^
                ////////////////////////////////////////////////////////////////
                // vvv �������������� ��� � ������������ vvv
                /*echo "<br><div style='float: left; display:inline-block;font-size: 14px; color: #00d;'>
                    <b>�������������� ��� � ������������</b></div><div style='display:inline-block; float: right'>";
                $ubord->button("name='add_line_to_project_button' onclick=' toAddSMR(". $_REQUEST["lid"] ."); '","�������� ���");
                $ubord->button("name='add_line_to_project_button' onclick=' window.location=\"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". 
                        $_REQUEST["lid"] ."&settypeequip=true\"; '","�������� ������������");
                echo "</div>";
                //echo "<nobr onclick='onTypeButtonClick(3);return false;'>���/������ �����: [<div id='project_type'>".
                //        $_REQUEST["project_type"] ."</div>]/[<div id='project_subtype'>".$_REQUEST["project_subtype"] ."</div>]</nobr>";
                echo "<br><b onclick='onTypeButtonClick(3);return false;' style='color: #006;'>
                    ��� �����: <b id='project_type' style='color: #060;'>".$_REQUEST["project_type"]."</b> 
                    ������: <b id='project_subtype' style='color: #060;'>".$_REQUEST["project_subtype"]."</b></b>";
                // ������ � html ������� ��� � ������������
                $cursor=SQL("select * from
                    (SELECT s.*,2 map_obj_type,sc.pgroup objgr,sc.name objname,sc.ed ed ,sc.price refprice 
                        FROM ps_smet_cid s left join ps_smet_calc sc on s.pid=sc.id where s.stype=1 and s.cid=".$_REQUEST["lid"]." 
                    union
                    SELECT s.*,1 map_obj_type,sc.pgroup objgr,sc.name objname,sc.ed ed ,sc.price refprice 
                        FROM ps_equip_cid s left join ps_equip sc on s.pid=sc.id where s.stype=1 and s.cid=".$_REQUEST["lid"]."  ) seq
                    order by map_obj_type,objgr,objname");
                // ����� �������
                echo "<style type=\"text/css\">
                        td.prjleftcol { color:#025; background:#EEF; }
                        td.prjheader { color:#037; background:#DEF; }
                        td.prjtype1 { color:#800; }
                        td.prjtype2 { color:#080; }
                    </style>";
                echo "<br><table width='100%' border='1' cellspacing='1' cellpadding='2'>";
                echo "<tr>
                    <td align='center' class='prjheader'><b></b></td>
                    <td align='center' class='prjheader'><b></b></td>
                    <td align='center' class='prjheader'><b>��� (������)</b></td>
                    <td align='center' class='prjheader'><b>���</b></td>
                    <td align='center' class='prjheader'><b>������/����.</b></td>
                    <td align='center' class='prjheader'><b>*��.�����.=���������</b></td>
                    <td align='center' class='prjheader'><b>����������</b></td>
                    </tr>";
                while ($cursor->assoc()) {
                    echo "<tr>
                        <td align='center' class='prjleftcol'>
                            <a href='vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=" . $_REQUEST["lid"] . 
                            "&edit_map_obj=".$cursor->r["map_obj_type"] ."&map_obj_id=" . $cursor->r["id"] . "' title='�������������'>
                            <img src='./images/edit.gif' align='absmiddle'></a></td>
                        <td align='center'  title='�������' onclick=' if(confirm(\"������� ���/������������ [id".$cursor->r["id"]."]?\"))
                                    window.location = \"vlg_call.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=" . $_REQUEST["lid"] . 
                            "&delete_map_obj=".$cursor->r["map_obj_type"] ."&map_obj_id=" . $cursor->r["id"] . "\"; '><b>X</b></td>";
                    echo "<td align='center' class='prjtype".$cursor->r["map_obj_type"]."'>".$cursor->r["objgr"]."</td>
                        <td align='center' class='prjtype".$cursor->r["map_obj_type"]."'>".$cursor->r["objname"]."</td>
                        <td align='center' class='prjtype".$cursor->r["map_obj_type"]."'>".$cursor->r["kol"] ."</td>
                        <td align='center' class='prjtype".$cursor->r["map_obj_type"]."'>*".$cursor->r["refprice"]."=". $cursor->r["kol"]*$cursor->r["refprice"] ."</td>    
                        <td align='center' class='prjtype".$cursor->r["map_obj_type"]."'>".$cursor->r["comment"]."</td>
                        </tr>";
                }
                echo "</table>";
                $cursor->free();*/
                // ^^^ �������������� ��� � ������������ ^^^
                ////////////////////////////////////////////////////////////////
            }
        break;
////////////////////////////////////////////////////////////////////////////////
        default :
        break;
        }
?>
    <script>
    ////////////////////////////////////////////////////////////////////////////
    //
    function preLoadDB(){
        var cursor=SQL("jsonselect","SELECT concat('[',id,',\"',dbname,'\",\"',tname,'\"]') FROM dictable ORDER BY id");
        preload_dictable=[];
        cursor.forEach(function(item, i, cursor) {
          preload_dictable[item[0]]=[item[1],item[2]];
        });       
        //alert(preload_dictable);
    }
    //
    ////////////////////////////////////////////////////////////////////////////
    //
    
    //
    ////////////////////////////////////////////////////////////////////////////
    //
    function onCOTLevelChange(thiselem,forestid){
        //var selvalue=document.comm_obj_form.sel_for_level.value;
        var selvalue=thiselem.value;
        if(selvalue==-1){
            
        } else {
            var child=selvalue.split("__");
            //alert(forestid+'->'+child[0]+'.'+child[1]+'.'+child[2]);
            if(child[2]==3){
            // ���� ������   
                $('#sel_for_level_cont').append(
                        "<br>������.(�����.):<input type='text' name='map_obj_equip_name' size='16' value=''>"+
                        "&nbsp ���-��:<input type='text' name='map_obj_equip_amount' size='16' value='1'> ��"+
                        "&nbsp ������:<input type='text' name='map_obj_equip_size' size='16' value='1'> ��"+
                        "<br>������� �:<input type='text' name='map_obj_capacity1' size='16' value='1'> "+
                        "&nbsp ������� Y:<input type='text' name='map_obj_capacity2' size='16' value='1'> "+
                        "<br>����������:<input type='text' name='comment' size='80' value=''>"
                );
        
        
//                SQL("insert","INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,\
//                        technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2)\
//                    VALUES (1,"+$_REQUEST["lid"] +","+$_REQUEST["add_to_project_button"] +","+
//                        $_REQUEST["map_obj_cn_area"] +",NULL,"+$_REQUEST["map_obj_cn_envir"] +",'"+ 
//                        $_REQUEST["map_obj_equip_amount"] +"','"+$_REQUEST["map_obj_equip_size"] +"',0+0,'"+ 
//                        $row_users["uid"] +"',now(),'"+$_REQUEST["comment"] +"',NULL,'"+$_REQUEST["map_obj_equip_name"] +"','"+
//                        $_REQUEST["map_obj_ps_teh_podkl"] +"','"+$_REQUEST["map_obj_builder"] +"',NULL,'"+
//                        $_REQUEST["map_obj_owner"] +"','"+$_REQUEST["map_obj_owner_add"] +"','"+
//                        $_REQUEST["map_obj_complexity"] +"','"+$_REQUEST["map_obj_complexity_add"] +"','"+
//                        $_REQUEST["map_obj_subexpense"] +"','"+
//                        $_REQUEST["map_obj_capacity1"] +"','"+
//                        $_REQUEST["map_obj_capacity2"] +"')") -> commit();
        
        
        
                document.comm_obj_form.add_object_id.value=child[1];
                $('#sel_for_level_add').html("<span class='emtextgreen'>�������� ������</span>");
                $('#sel_for_level_add').click(  function() {onCOTSubmit();}  );
                /*
                $('#sel_for_level_submit').click(
                    function() {
                        onCOTSubmit();
                        return true;
                    }
                );*/
                //$('#sel_for_level_submit').prop('onclick','comm_obj_form.add_to_project_button.value='+child[1]+'; comm_obj_form.submit();');
            } else {
            // ����������
                SQL("select","select fid from forest where pid="+forestid+" and childtype="+child[0]+" and childid="+child[1]);
                //alert(xmlHttp_responseText);
                //var cursor=SQL("jsonselect","select concat('[',fid,',',pid,',',childtype,',',childid,',',type,']') ro from forest where pid="+xmlHttp_responseText);
                
                var newSelect=onCOTLevelChangeSel(xmlHttp_responseText);
                if(newSelect==-1){ // ����� ���������
                    alert("������������ ���������� ��������!");
                } else {
                    //$(thiselem).prop('disabled',true); // �� ��������� ����� $_REQUEST
                    $(thiselem).prop('readonly',true);
                    $('#sel_for_level_cont').append(newSelect);
                    
        //            $('[name="sel_for_level'+forestid+'"]').after(onCOTLevelChangeSel(xmlHttp_responseText));
                }
            }
        }        
    }
    function onCOTLevelChangeSel(forestPid){
        var cursor=SQL("jsonselect","SELECT concat('[',f.fid,',',f.pid,',',f.childtype,',',f.childid,',',f.type,',\"', "+
                "    case when f.childtype=1 then '����������' \
                        when f.childtype=2 then '��� ������' \
                        when f.childtype=4 then '������ ������' \
                        when f.childtype=3 then '�������' \
                        when f.childtype=7 then '��� ���������' \
                        when f.childtype=9 then '������ �����' \
                    end , \
                '\",\"', "+
                "    case when f.childtype=1 then tp.name \
                        when f.childtype=2 then e.comment \
                        when f.childtype=3 then ca.fullname \
                        when f.childtype=4 then se.comment \
                        when f.childtype=7 then ce.cename \
                        when f.childtype=9 then rcm.mgroup "+ 
//                "    case when f.childtype=1 then tp.name \
//                        when f.childtype=2 then e.ename \
//                        when f.childtype=3 then ca.cnaname \
//                        when f.childtype=4 then se.ename \
//                        when f.childtype=7 then ce.cename \
//                        when f.childtype=9 then rcm.mgroup "+ 
                    "end, '\"]') ro "+
            "FROM forest f \
            left join ps_teh_podkl tp on f.childtype=1 and f.childid=tp.id \
            left join expense e on f.childtype=2 and f.childid=e.eid \
            left join subexpense se on f.childtype=4 and f.childid=se.seid \
            left join cn_area ca on f.childtype=3 and f.childid=ca.cnaid \
            left join cn_envir ce on f.childtype=7 and f.childid=ce.ceid \
            left join ref_com_mat rcm on f.childtype=9 and f.childid=rcm.rcmid \
            where f.pid="+forestPid);
        //alert(cursor[1]);
        if(cursor.length>0){
        // ��������� ������������
            //alert('map_obj_'+preload_dictable[cursor[0][2]][0]);
            //var after_sel_for_level='<select name="sel_for_level';
            var after_sel_for_level='<span class=\'emtextgreen\'> '+cursor[0][5]+'</span>'+'<select name="'+'map_obj_'+preload_dictable[cursor[0][2]][0];
//            if(forestPid==-1)
//                after_sel_for_level+='0'+'"  onchange="onCOTLevelChange(this,'+forestPid+');" >';
//            else
//                after_sel_for_level+=''+forestPid+'"  onchange="onCOTLevelChange(this,'+forestPid+');" >';
            after_sel_for_level+='"  onchange="onCOTLevelChange(this,'+forestPid+');" >';            
            //<span class='emtextgreen'></span>
            //after_sel_for_level+=' <option value="-1" selected style="color: #f00;">('+cursor[0][5]+'...)�� ������</option>';
            after_sel_for_level+=' <option value="-1" selected style="color: #f00;">'+'�� ������</option>';
            cursor.forEach(function(item, i, cursor) {
              //alert( i + ": " + item + " (������:" + cursor + ")" );
              //after_sel_for_level+='<option value="'+item[2]+'__'+item[3]+'__'+item[4]+'"  >('+item[5]+') '+item[6]+'</option>';
              after_sel_for_level+='<option value="'+item[2]+'__'+item[3]+'__'+item[4]+'"  > '+item[6]+'</option>';
            });
            after_sel_for_level+='</select>';

            return after_sel_for_level;
        } else {
        // ����� ���������
            return -1;
        }
    }    
    //
    ////////////////////////////////////////////////////////////////////////////
    //
    function onCOTSubmit(){
        if(document.comm_obj_form.map_obj_ref_com_mat.value!=-1){
            document.comm_obj_form.add_to_project_button.value=document.comm_obj_form.add_object_id.value;
            document.comm_obj_form.submit();
        } else {
            alert("�� ������ ������ �����");
        }
    }
    //
    ////////////////////////////////////////////////////////////////////////////
    //
    /*function onRefComMatRefresh(){
                alert( "SELECT rcm.mgroup,rcm.pgroup,rcm.cetid,cet.cetname,rcm.rcmid,rcm.name rcmname,rcm.comment,\
                        rcm.price rcmprice,rcm.unit rcmunit,rcm.capacity1 rcmcapacity1,rcm.capacity2 rcmcapacity2,\
                        rcm.cnaid,cna.cnaname,rcm.seid,se.sename,rcm.ceid,ce.cename,\
                        rcm.eid,exp.ename ename,rcm.subeid,sube.ename subename,rcm.oid,o.oname ooname,rcm.cxid,cx.cxname,\
                        rcm.technology,tp.name tpname,rcm.bid,b.bname\
                    FROM ref_com_mat rcm \
                    left join cn_eq_type cet using(cetid) \
                    left join sign_envir se on rcm.seid=se.seid\
                    left join cn_area cna on rcm.cnaid=cna.cnaid\
                    left join cn_envir ce on rcm.ceid=ce.ceid\
                    left join builder b on rcm.bid=b.bid\
                    left join expense exp on rcm.eid=exp.eid\
                    left join subexpense sube on rcm.subeid=sube.seid\
                    left join owner o on rcm.oid=o.oid\
                    left join complexity cx on rcm.cxid=cx.cxid\
                    left join ps_teh_podkl tp on rcm.technology=tp.id "+
                " where subeid="+document.comm_obj_form.map_obj_subexpense.value+
                " order by exp.ename,sube.ename,ce.cename,rcm.name ");
    }*/
    // ����� ���� ����� ��� ���� ������������ ��� ���
    function onTypeButtonClick(type) {
        switch(type){
/*        case 1:
            document.getElementById('info_window_message').innerHTML="<?php echo $map_obj_equip_HTML; ?>";
        break;
        case 2:
            document.getElementById('info_window_message').innerHTML="<?php echo $map_obj_line_HTML; ?>";
        break;*/
        case 3:
            document.getElementById('info_window_message').innerHTML="<?php echo $project_type_HTML; ?>";
        break;
        }
        document.getElementById("info_window_darkening").style.display = 'block';
        return false;
    }
    // ��������� ������� ����/������� �����
    function setTypeSubtype(mgroup,pgroup){
        if(mgroup=='cansel'){
        }else{
                document.getElementById('project_type').innerHTML=mgroup;
                document.getElementById('project_subtype').innerHTML=pgroup;
        }
        document.getElementById("info_window_darkening").style.display = 'none';
    }
    // ������������ ������ ��� ���������� ���
    function toAddSMR(lid){
        window.location="vlg_call.php?action=call_edit&sourcepage=<?php echo $_REQUEST["sourcepage"]; ?>&lid="+lid+
            "&settypesmr=true&project_type="+document.getElementById('project_type').innerHTML+"&project_subtype="+document.getElementById('project_subtype').innerHTML+" ";
    }
    /*
    function onSetTypePoint(map_obj_subtype){
        document.getElementById("info_window_darkening").style.display = 'none';
        alert(map_obj_id+" "+document.map_obj_equip_form.map_obj_equip_size.value);
        window.open("vlg_project.php?action=edit_project&project_id="+<------?php echo $_REQUEST["project_id"]; ?>+
            "&map_obj_type=1&map_obj_subtype="+map_obj_subtype+"&map_obj_equip_size="+document.map_obj_equip_form.map_obj_equip_size.value,"_self");
    }
    */
    </script>
    <br><br>
    </td></tr>
    <tr><td colspan='2' height='20' background='images/top_bg.jpg' align='center' style='color: lightgray;'></td></tr>
    </table>
    </TD></TR>
<?php
echo "<br><b></b>
    <br>";
include "footer.php";
?>        
