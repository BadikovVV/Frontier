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
<script>
xmlHttp=new XMLHttpRequest();
current_user_uid=<?php echo $row_users['uid']; ?>;
current_user_ugroup=<?php echo $row_users['ugroup']; ?>;
</script>
<?php
////////////////////////////////////////////////////////////////////////////////
if(getReq("lid")==-1){
    exit();
}
////////////////////////////////////////////////////////////////////////////////
// ������ ��� �������
    switch ($_REQUEST["action"]) {
////////////////////////////////////////////////////////////////////////////////
// ��������� ���������
        case "save_call":
            $list_id=rSQL("SELECT list_id FROM ps_list_dop where lid=".$_REQUEST["lid"])["list_id"];
            /*SQL("UPDATE ps_list_dop SET
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
                WHERE lid = " . $_REQUEST["lid"] . "");
            SQL("UPDATE ps_list SET
                client_fio = '".$_REQUEST["client_fio"]."', contact_phone = '".$_REQUEST["contact_phone"]."'
                WHERE list_id = " . $list_id . "") -> commit();*/    
            
            SQL("UPDATE ps_list_dop SET
                comment = '".$_REQUEST["comment"]."',
                novelty = '".((isset($_REQUEST["novelty"]) and $_REQUEST["novelty"]=='on') ? 1 : 0) ."',
                tariff_id = '".((isset($_REQUEST["tariff_manual"]) and $_REQUEST["tariff_manual"]=='on') ?  "-1" : ("".$_REQUEST["tariff_id"])) ."',
                
                installg_id = '".$_REQUEST["install_radio"]."',
                targetdate = STR_TO_DATE('".(empty($_REQUEST["targetdate"]) ? "0000-00-00" : $_REQUEST["targetdate"]) ."','%Y-%m-%d'),
                finishdate = STR_TO_DATE('".(empty($_REQUEST["finishdate"]) ? "0000-00-00" : $_REQUEST["finishdate"]) ."','%Y-%m-%d'),
                
                uid = ".$row_users['uid'].",dateedit = now(),
                guarantee = '".$_REQUEST["guarantee"]."',
                tariffname = '".$_REQUEST["tariffname"]."',
                month_pay = '".$_REQUEST["month_pay"]."' 
                WHERE lid = " . $_REQUEST["lid"] . "");
            SQL("UPDATE ps_list SET
                client_fio = '".$_REQUEST["client_fio"]."', contact_phone = '".$_REQUEST["contact_phone"]."'
                WHERE list_id = " . $list_id . "") -> commit();
            ////////////////////////////////////////////////////////////////////
            // ����� ������ (����������)
            //if(count($_REQUEST["new_call_service"])>0){
            SQL("delete from call_service where cstype=3 and lid=".$_REQUEST["lid"] ." ")->commit();
            foreach ($_REQUEST["new_call_service"] as $value) {
                SQL("insert into call_service (lid,service_id,cstype) values (".$_REQUEST["lid"] .",".$value .",3)")->commit();
            }
            // ����� ������ (����������)
            ////////////////////////////////////////////////////////////////////
            // ������, �� ������� ������ ���������� �����������
            SQL("delete from call_service where cstype=2 and lid=".$_REQUEST["lid"] ." ")->commit();
            foreach ($_REQUEST["new_tech_call_service"] as $value) {
                SQL("insert into call_service (lid,service_id,cstype) values (".$_REQUEST["lid"] .",".$value .",2)")->commit();
            }
            // ������, �� ������� ������ ���������� �����������
            ////////////////////////////////////////////////////////////////////
            // ������ ������������, �� ������, �� ���������� � ����� ����� �����������
            SQL("delete from call_service where cstype=1 and lid=".$_REQUEST["lid"] ." ")->commit();
            foreach ($_REQUEST["old_call_service"] as $value) {
                SQL("insert into call_service (lid,service_id,cstype) values (".$_REQUEST["lid"] .",".$value .",1)")->commit();
            }
            // ������ ������������, �� ������, �� ���������� � ����� ����� �����������
            ////////////////////////////////////////////////////////////////////
            // ONT(��������)
            $ont_radio=explode('_',getReq("ont_radio"));
            SQL("delete from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                    " and rcmid in (select rcmid from ref_com_mat where cetid=900)") -> commit();
            if($ont_radio[0]==0){
                //SQL("delete from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                //    " and rcmid in (select rcmid from ref_com_mat where cetid=900)") -> commit();
            }else{
                //if(rSQL("select ccmid from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                //        " and rcmid=".$ont_rcmid ." and subeid=18 and price=".$ont_price ." and lease=".$ont_lease ."")["ccmid"]){
                //} else {
                    SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                        technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                    VALUES (1,".$_REQUEST["lid"] .",".$ont_radio[0] .",60,NULL,NULL,'1','0',".$ont_radio[1] .",'". 
                        $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,".$ont_radio[2] .")") -> commit();
                //}
            }
            // ONT(��������)
            ////////////////////////////////////////////////////////////////////
            // ������
            $route_radio=explode('_',$_REQUEST["route_radio"]);
            SQL("delete from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                    " and rcmid in (select rcmid from ref_com_mat where cetid=910)") -> commit();
            if($route_radio[0]==0){
            }else{
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,
                    lease,month_pay,defferedpay,first_pay)
                VALUES (1,".$_REQUEST["lid"] .",".$route_radio[0] .",60,NULL,NULL,'1','0',".$route_radio[1] .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,".
                    $route_radio[2] .",".$route_radio[3] .",".$route_radio[4] .",".$route_radio[5] .")") -> commit();
            }
            // ������
            ////////////////////////////////////////////////////////////////////
            // ���������
            $attach_radio_num=$_REQUEST["attach_radio_num"];
            SQL("delete from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                    " and rcmid in (select rcmid from ref_com_mat where cetid=920)") -> commit();
            if($attach_radio_num>0){
                $attach_radio=explode('_',$_REQUEST["attach_radio1"]);
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                VALUES (1,".$_REQUEST["lid"] .",".$attach_radio[0] .",60,NULL,NULL,'1','0',".$attach_radio[1] .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,".$attach_radio[2] .")") -> commit();
                if($attach_radio_num>1){
                    $attach_radio=explode('_',$_REQUEST["attach_radio2"]);
                    SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                        technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                    VALUES (1,".$_REQUEST["lid"] .",".$attach_radio[0] .",60,NULL,NULL,'1','0',".$attach_radio[1] .",'". 
                        $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,".$attach_radio[2] .")") -> commit();
                    if($attach_radio_num>2){
                        $attach_radio=explode('_',$_REQUEST["attach_radio3"]);
                        SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                            technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                        VALUES (1,".$_REQUEST["lid"] .",".$attach_radio[0] .",60,NULL,NULL,'1','0',".$attach_radio[1] .",'". 
                            $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,".$attach_radio[2] .")") -> commit();
                        if($attach_radio_num>3){
                            $attach_radio=explode('_',$_REQUEST["attach_radio4"]);
                            SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                                technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                            VALUES (1,".$_REQUEST["lid"] .",".$attach_radio[0] .",60,NULL,NULL,'1','0',".$attach_radio[1] .",'". 
                                $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,".$attach_radio[2] .")") -> commit();
                        }
                    }
                }
            }
            // ���������
            ////////////////////////////////////////////////////////////////////
            // SIM
            $sim_radio_num=$_REQUEST["sim_radio_num"];
            SQL("delete from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                    " and rcmid in (select rcmid from ref_com_mat where cetid=930)") -> commit();
            if($sim_radio_num>0){
//                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
//                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease,tariff_id)
//                VALUES (1,".$_REQUEST["lid"] .",".getReq('sim_tariff_radio') .",60,NULL,NULL,'".$sim_radio_num ."','0',". "0" .",'". 
//                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .",'".
//                    "".$_REQUEST["mvno_tariff_id"] ."')") -> commit();
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease,tariff_id)
                VALUES (1,".$_REQUEST["lid"] .",108,60,NULL,NULL,'".$sim_radio_num ."','0',". "0" .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .",'".
                    "".$_REQUEST["mvno_tariff_id"] ."')") -> commit();
            }
            // SIM
            ////////////////////////////////////////////////////////////////////
            // ��������������� 
            $video_int_num= getReq("video_int_num");
            $video_ext_num= getReq("video_ext_num");
            $video_ext_poe= getReq("video_ext_poe");
            $video_int_mont_num= getReq("video_int_mont_num");
            $video_ext_mont_num= getReq("video_ext_mont_num");
            SQL("delete from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                    " and rcmid in (select rcmid from ref_com_mat where cetid=940)") -> commit();
            if($_REQUEST["video_int"] and $video_int_num>0){
                $video_int=explode('_',$_REQUEST["video_int"]);
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease,month_pay,defferedpay,first_pay)
                VALUES (1,".$_REQUEST["lid"] .",115,60,NULL,NULL,'".$video_int_num ."','0',".($video_int[1] ? $video_int[1] : 0) .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .",".$video_int[2] .",24,0)") -> commit();
            }
            if($_REQUEST["video_ext"] and $video_ext_num>0){
                $video_ext=explode('_',$_REQUEST["video_ext"]);
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease,month_pay,defferedpay,first_pay)
                VALUES (1,".$_REQUEST["lid"] .",116,60,NULL,NULL,'".$video_ext_num ."','0',". ($video_ext[1] ? $video_ext[1] : 0) .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .",".$video_ext[2] .",24,". (4*$video_ext[2]) .")") -> commit();
            }
            if($_REQUEST["video_int"] and $video_int_num>0 and $video_int_mont_num>0){
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                VALUES (1,".$_REQUEST["lid"] .",122,60,NULL,NULL,'".$video_int_mont_num ."','0',". "350" .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .")") -> commit();
            }
            if($_REQUEST["video_ext"] and $video_ext_num>0 and $video_ext_mont_num>0){
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                VALUES (1,".$_REQUEST["lid"] .",123,60,NULL,NULL,'".$video_ext_mont_num ."','0',". "1500" .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .")") -> commit();
            }
            if($_REQUEST["video_ext"] and $video_ext_num>0 and $video_ext_poe=='on'){
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                VALUES (1,".$_REQUEST["lid"] .",124,60,NULL,NULL,'". "1" ."','0',". "950" .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .")") -> commit();
            }
            // ��������������� 
            ////////////////////////////////////////////////////////////////////
            // ����� ��� 
            //$smart_house_type=getReq("smart_house_type");
            $smart_house=explode('_',$_REQUEST["smart_house_type"]);
            $smart_house_int_num=getReq("smart_house_int_num");
            $smart_house_out_num=getReq("smart_house_out_num");
            SQL("delete from call_com_mat where stype=1 and lid=".$_REQUEST["lid"] .
                    " and rcmid in (select rcmid from ref_com_mat where cetid in (950,952))") -> commit();
            if($smart_house[0]!='0'){
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease,month_pay,defferedpay,first_pay)
                VALUES (1,".$_REQUEST["lid"] .",".$smart_house[0] .",60,NULL,NULL,'0','0',". $smart_house[1] .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .",".$smart_house[2] .",".$smart_house[3] .",". $smart_house[4] .")") -> commit();
            }
            if($smart_house[0]!='0' and $smart_house_int_num>0){
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                VALUES (1,".$_REQUEST["lid"] .",117,60,NULL,NULL,'".$smart_house_int_num ."','0',". "0" .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .")") -> commit();
            }
            if($smart_house[0]!='0' and $smart_house_out_num>0){
                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                    technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2,lease)
                VALUES (1,".$_REQUEST["lid"] .",118,60,NULL,NULL,'".$smart_house_out_num ."','0',". "0" .",'". 
                    $row_users["uid"] ."',now(),'',NULL,'',NULL,NULL,NULL,NULL,'',NULL,'',18,NULL,NULL,". "0" .")") -> commit();
            }
            // ����� ��� 
            ////////////////////////////////////////////////////////////////////
            // ����������� ������������ �����
            if(rSQL("select count(wid) cnav from workpath where object_type=2 and cnaid=45 and lp_id=".$_REQUEST["lid"] ."")["cnav"]>0){
                SQL("update workpath set 
                        targetdate=STR_TO_DATE('".(empty($_REQUEST["targetdate_av"]) ? "0000-00-00" : $_REQUEST["targetdate_av"]) ."','%Y-%m-%d'),
                        finishdate=STR_TO_DATE('".(empty($_REQUEST["finishdate_av"]) ? "0000-00-00" : $_REQUEST["finishdate_av"]) ."','%Y-%m-%d') 
                    where object_type=2 and cnaid=45 and lp_id=".$_REQUEST["lid"]) -> commit();                
            }else{
                SQL("INSERT INTO workpath (lp_id,object_type,cnaid,realcost,
                        targetdate,finishdate,
                        startdate,capacity,comment,status,uid,dateedit,bid,realcost2,startdateplan)
                    VALUES (".$_REQUEST["lid"] .",2,45,0,
                        STR_TO_DATE('".(empty($_REQUEST["targetdate_av"]) ? "0000-00-00" : $_REQUEST["targetdate_av"]) ."','%Y-%m-%d'),".
                        "STR_TO_DATE('".(empty($_REQUEST["finishdate_av"]) ? "0000-00-00" : $_REQUEST["finishdate_av"]) ."','%Y-%m-%d'),".
                        "'0000-00-00',0,'',0,'".$row_users["uid"] ."',now(),'2',0,'0000-00-00')") -> commit();
            }
            // ����������� ������������ �����
////////////////////////////////////////////////////////////////////////////////
// �������������� ������
        case "call_edit":
            if($_REQUEST["arm_id"]){
            // !!! �������� ����� ���� ����, ����� ������ ������ !!!
                $_REQUEST["lid"]=rSQL("SELECT min(lid) lid FROM ps_list_dop where arm_id=".$_REQUEST["arm_id"])["lid"];
            }
            $call_row_dop=rSQL("SELECT t.formulation tformulation,t.month_pay tmonth_pay,t.month_pay2 tmonth_pay2,
                    ig.igname igname,ig.main_sum igmain_sum,ig.month_pay igmonth_pay,ig.defferedpay igdefferedpay,
                    ld.* 
                FROM ps_list_dop ld left join tariff t on ld.tariff_id=t.id 
                    left join installg ig on ld.installg_id=ig.id  where lid=".$_REQUEST["lid"]);
            $call_row_status=rSQL("SELECT * FROM ps_status where id=".$call_row_dop["status"]);
            $call_row=rSQL("SELECT * FROM ps_list where list_id=".$call_row_dop["list_id"]);
            $call_project=rSQL("SELECT * FROM ps_project where project_id in (select project_id from ps_project_list where list_id=".$call_row_dop["list_id"].")");
            $old_call_service=rSQL("SELECT GROUP_CONCAT(service_id SEPARATOR ',') scs "
                    . "FROM call_service where cstype=1 and lid=".$_REQUEST["lid"]." group by lid")['scs'];
            $new_tech_call_service=rSQL("SELECT GROUP_CONCAT(service_id SEPARATOR ',') scs "
                    . "FROM call_service where cstype=2 and lid=".$_REQUEST["lid"]." group by lid")['scs'];
            $new_call_service=rSQL("SELECT GROUP_CONCAT(service_id SEPARATOR ',') scs "
                    . "FROM call_service where cstype=3 and lid=".$_REQUEST["lid"]." group by lid")['scs'];
            // ������ ������ �� �������� ����� ������ (�����. � ���������� ������)
            $com_object_query="SELECT ccm.ccmid,ccm.rcmid,rcm.cetid,ccm.price,ccm.lease,ccm.amount,ccm.month_pay,ccm.defferedpay,ccm.first_pay,
                    ccm.tariff_id,t.formulation tformulation,t.month_pay tmonth_pay,t.month_pay2 tmonth_pay2 
                FROM call_com_mat ccm left join ref_com_mat rcm using(rcmid)  left join tariff t on ccm.tariff_id=t.id 
                where ccm.stype=1 and ccm.lid=".$_REQUEST["lid"] ."
                order by ccm.ccmid "; // ������� ����� ��� ���������
            $cursor=SQL($com_object_query);
            $call_com_array=[];
            $call_com_rcmid=[];
            $call_attach_array=[];
            $call_attach_array_i=0;
            while ($cursor->assoc()) {
                if($cursor->r["cetid"]==920){ 
                    $call_attach_array[$call_attach_array_i]=
                            array($cursor->r["rcmid"],$cursor->r["price"],$cursor->r["lease"],$cursor->r["amount"],$cursor->r["cetid"]);
                    $call_attach_array_i++;
                }
                $call_com_array[$cursor->r["rcmid"]]=array($cursor->r["ccmid"],$cursor->r["price"],$cursor->r["lease"],$cursor->r["amount"],
                    $cursor->r["cetid"],$cursor->r["month_pay"],$cursor->r["defferedpay"],$cursor->r["first_pay"],
                    $cursor->r["tariff_id"],$cursor->r["tformulation"],$cursor->r["tmonth_pay"],$cursor->r["tmonth_pay2"]);
                $call_com_rcmid[$cursor->r["cetid"]]=$cursor->r["rcmid"];
            }
            for($ai=$call_attach_array_i;$ai<4;$ai++){
                    $call_attach_array[$ai]=-1;
            }
            $cursor->free();
            // �������� ����� �� ������
            // ["price","lease","amount","month_pay","defferedpay","first_pay"]
            $call_total_array=array(0.0,0.0,0.0,0.0,0.0,0.0);                            
            ////////////////////////////////////////////////////////////////////
            // ����������� ���� � ������� ������ �� ����������� ����� �����������
            echo "<div id='tariff_selection_darkening' class='ps_popup_darkening'> 
                    <div id='tariff_selection' class='ps_popup_main_window'> 
                        <a class='ps_popup_close_button' title='�������' 
                           onclick='document.getElementById(\"tariff_selection_darkening\").style.display = \"none\";'>X</a>
                        <b>�������� ����� (��������� ����������: " . 
                            rSQL("SELECT name FROM ps_teh_podkl where id=".$call_row_dop["tpid"])["name"] . ")</b>
                        <table style='text-align: center;'><tr><td>��������</td><td>������</td><td>����� 1-�� �������, ���.</td><td>����� 2-�� �������, ���.)</td></tr>";
                        $tariffCursor=qSQL("SELECT t.*,s.sname FROM tariff t left join service s on t.service_id=s.id where tech_id=".$call_row_dop["tpid"].
                                " union SELECT -1,-1,3,0.0,0.0,900,NULL,'������� ��',999,'������� ��',0.0,0,1,'������� ��'");
                        //$tariffCursor=qSQL("SELECT t.*,s.sname FROM tariff t left join service s on t.service_id=s.id where tech_id=3");
                        while ($rowTariffCursor = $tariffCursor->fetch_array( MYSQL_ASSOC )) {
                            echo "<tr><td>".$rowTariffCursor["speed"]." ��/�</td><td>".$rowTariffCursor["sname"].
                                    "</td><td style='background-color: #FDD; '><a class=\"tariff_selection_close\" style='cursor: pointer;' 
                                onclick=\"
                                    document.new_project.tariffnameB.value='".$rowTariffCursor["formulation"]."';
                                    document.new_project.tariff_id.value='".$rowTariffCursor["id"]."';
                                    document.new_project.month_payB.value=".$rowTariffCursor["month_pay"].";
                                    document.new_project.month_pay2B.value=".$rowTariffCursor["month_pay2"].";
                                    document.getElementById('tariff_selection_darkening').style.display ='none';    
                                    \">".
                            $rowTariffCursor["month_pay"]."</a></td>
                            <td>". (($rowTariffCursor["month_pay2"]=='0') ? '-' : $rowTariffCursor["month_pay2"]) ."</td>
                            <td>".$rowTariffCursor["formulation"]."</td></tr>";
                        }
            echo "</table></div></div>";
            // ^^ ����������� ���� � ������� ������ ^^
            ////////////////////////////////////////////////////////////////////
            // ����������� ���� � ������� ������ �� ����������� MVNO
            echo "<div id='mvno_tariff_selection_darkening' class='ps_popup_darkening'> 
                    <div id='mvno_tariff_selection' class='ps_popup_main_window'> 
                        <a class='ps_popup_close_button' title='�������' 
                           onclick='document.getElementById(\"mvno_tariff_selection_darkening\").style.display = \"none\";'>X</a>
                        <b>�������� ����� MVNO</b>
                        <table style='text-align: center;'><tr><td>������</td><td>����� 1-�� �������, ���.</td><td>����� 2-�� �������, ���.)</td></tr>";
                        $tariffCursor=qSQL("SELECT t.*,s.sname FROM tariff t left join service s on t.service_id=s.id where tech_id=40 ");
                        while ($rowTariffCursor = $tariffCursor->fetch_array(MYSQL_ASSOC )) {
                            echo "<tr><td>".$rowTariffCursor["sname"].
                                    "</td><td><a class=\"tariff_selection_close\" style='cursor: pointer;' 
                                onclick=\"
                                    document.new_project.mvno_tariffnameB.value='".$rowTariffCursor["formulation"]."';
                                    document.new_project.mvno_tariff_id.value='".$rowTariffCursor["id"]."';
                                    document.new_project.mvno_month_payB.value=".$rowTariffCursor["month_pay"].";
                                    document.new_project.mvno_month_pay2B.value=".$rowTariffCursor["month_pay2"].";
                                    document.getElementById('mvno_tariff_selection_darkening').style.display ='none';
                                    \">".
                            $rowTariffCursor["month_pay"]."</a></td>
                            <td>". (($rowTariffCursor["month_pay2"]=='0') ? '-' : $rowTariffCursor["month_pay2"]) ."</td>
                            <td>".$rowTariffCursor["formulation"]."</td></tr>";
                        }
            echo "</table></div></div>";
            // ^^ ����������� ���� � ������� ������ ^^
            ////////////////////////////////////////////////////////////////////
            if($_REQUEST["addcommobject"]=='true'){
            // ����� ���������� ������� ����� (map_obj_type=4)
            /*    $sel_cnaname=new CSelect("SELECT '��������...',-1 union select cnaname,cnaid FROM cn_area", 
                    "map_obj_cn_area", 
                    (($_REQUEST["map_obj_cn_area"]) ? $_REQUEST["map_obj_cn_area"] : 40), 
                    "comm_network_area");
                $sel_cename=new CSelect("SELECT '��������...',-1 union select cename,ceid FROM cn_envir", 
                    "map_obj_cn_envir", 
                    (($_REQUEST["map_obj_cn_envir"]) ? $_REQUEST["map_obj_cn_envir"] : 150), 
                    "comm_network_envir");
                $sel_subename=new CSelect("SELECT '��������...',-1 union (SELECT concat(se.ename,'/',e.ename) fsename,seid "
                        . " FROM subexpense se left join expense e using(eid) order by e.ename,se.ename)", 
                    "map_obj_subexpense", 
                    (($_REQUEST["map_obj_subexpense"]) ? $_REQUEST["map_obj_subexpense"] : 4), 
                    "comm_network_subexpense");
                $sel_builder=new CSelect("SELECT '��������...',-1 union select bname,bid FROM builder", "map_obj_builder", 2, "comm_network_builder");
                $sel_owner=new CSelect("SELECT '��������...',-1 union select oname,oid FROM owner", "map_obj_owner", 1, "comm_network_owner");
                $sel_complexity=new CSelect("SELECT '��������...',-1 union select cxname,cxid FROM complexity", "map_obj_complexity", 1, "comm_network_complexity");
                $sel_ps_teh_podkl=new CSelect("SELECT '��������...',-1 union select name,id FROM ps_teh_podkl", 
                        "map_obj_ps_teh_podkl", 
                        (($_REQUEST["map_obj_ps_teh_podkl"]) ? $_REQUEST["map_obj_ps_teh_podkl"] : 3), 
                        "comm_network_ps_teh_podkl");
                //echo "<!-- ".$cnaname->htmlel ." -->";
                echo "<form name='comm_obj_form' method='post' action='vlg_call_prior.php?action=call_edit'>".
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
                if($_REQUEST["map_obj_subexpense"] and $_REQUEST["map_obj_subexpense"]!=-1){
                    $where.=" and rcm.subeid=".$_REQUEST["map_obj_subexpense"]." ";
                }
                if($_REQUEST["map_obj_cn_envir"] and $_REQUEST["map_obj_cn_envir"]!=-1){
                    $where.=" and rcm.ceid=".$_REQUEST["map_obj_cn_envir"]." ";
                }
                //d(ref_com_object_query($where," order by exp.ename,sube.ename,ce.cename,rcm.name "));
                $cursor=SQL(ref_com_object_query($where," order by exp.ename,sube.ename,ce.cename,rcm.name "));
                while ($cursor->assoc()) {
                    echo "<tr>
                        <td align='center' class=''>".$cursor->r["cnaname"]."</td>
                        <td align='center' class=''>".$cursor->r["subename"]."</td>
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
                echo '</table></form>';*/
            } else if($_REQUEST["edit_comm_obj"]=='true'){
            // ����� �������������� ������� �����
                /*$cursor=rSQL("SELECT ccmid,rcm.mgroup,rcm.pgroup,ccm.cnaid,cna.cnaname,ccm.subeid,sube.ename subename,
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
                        . "action='vlg_call_prior.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=". $_REQUEST["lid"] 
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
                    <input type='button' onclick=' window.location = \"vlg_call_prior.php?action=call_edit&sourcepage=".$_REQUEST["sourcepage"]."&lid=" 
                        . $_REQUEST["lid"] . "\"; ' value='���������'/>        
                </form></fieldset>";*/
            } else {
            ////////////////////////////////////////////////////////////////////
            // �������������� ������
                ////////////////////////////////////////////////////////////////
                // ����� �������������� ������
                //echo "<fieldset style='padding: 20px; width: 90%;border-color: darkgray;'>";
                //echo "<legend>&nbsp;<b style='color: #006;'>�������������� ������</b>&nbsp;</legend>";
                echo "<form name='new_project' method='post' enctype='multipart/form-data' style='' ". 
                        "action='vlg_call_prior.php?action=save_call&sourcepage=".$_REQUEST["sourcepage"]."&lid=". 
                        $_REQUEST["lid"] . "'>";
                echo "<table>";
                echo "<td>�����:".$call_row_dop["arm_id"] ." ������: '<b>".$call_row_status["name"] ." </b>'
                    ���<input type='text' name='client_fio' size='60' value='".$call_row["client_fio"] ."'>&nbsp
                    ������������ ������� (��������)<input type=\"checkbox\" name=\"novelty\" ".((isset($call_row_dop["novelty"]) and $call_row_dop["novelty"]==0) ? "" : "checked") ."><br> ";
                echo "�������:<input type='text' name='contact_phone' size='20' value='".$call_row["contact_phone"] ."'>&nbsp
                        ";
                if($call_row_dop["service_id"]!=-1)
                    echo " ������ (���) '<b>".rSQL("SELECT sname FROM service where id='".$call_row_dop["service_id"] ."'")["sname"] . "</b>' &nbsp;";
                else
                    echo " ������ (���) <b>�� ����������</b> &nbsp;";
                if($call_row_dop["tpid"]!=-1)
                    echo " ���������� '<b>".rSQL("SELECT name FROM ps_teh_podkl where id='".$call_row_dop["tpid"] ."'")["name"] . "</b>' &nbsp;";
                else
                    echo " ���������� <b>�� ����������</b> &nbsp;";
                
                //$sel_call_service_list1=new CSelect("SELECT concat(id,'. ',sname),id FROM service", 
                //            "new_call_service[]", $new_call_service, "", "multiple='multiple' width: 300px size='10'");
                //$sel_call_service_list2=new CSelect("SELECT concat(id,'. ',sname),id FROM service  where id in (2,3,5,9)", 
                //            "new_tech_call_service[]", $new_tech_call_service, "", "multiple='multiple' width: 300px size='10'");
                //$sel_call_service_list3=new CSelect("SELECT concat(id,'. ',sname),id FROM service where id=9", 
                //            "old_call_service[]", $old_call_service, "", "multiple='multiple' width: 300px size='10'");
                $sel_call_service_list1=new CCheckBoxList("SELECT concat(id,'. ',sname),id FROM service", 
                            "new_call_service[]", $new_call_service, "", "style='width: 100%; overflow: auto; height: 120px; position: relative;'");
                $sel_call_service_list2=new CCheckBoxList("SELECT concat(id,'. ',sname),id FROM service  where id in (2,3,5,9)", 
                            "new_tech_call_service[]", $new_tech_call_service, "", "style='width: 100%; overflow: auto; height: 120px; position: relative;'");
                $sel_call_service_list3=new CCheckBoxList("SELECT concat(id,'. ',sname),id FROM service where id=9", 
                            "old_call_service[]", $old_call_service, "", "style='width: 100%; overflow: auto; height: 120px; position: relative;'");
                echo "<br>�����:<input type='text' name='contact_phone' size='80' value='".
                        $call_row["settlement"] ." ".$call_row["ul"] ." ".$call_row["home"] ." ".$call_row["corp"] ." ".$call_row["room"] ."' disabled>&nbsp<br>
                    <textarea name='comment' rows=4 cols=80 disabled>".
                        "���������� �� ���: ".$call_row["promt"] .chr(10).
                        /*"����.��� ���: ".$call_row["coment_tvp_ota"] .chr(10).
                        "����.��� ���: " .$call_row["coment_tvp_spd"] .*/
                        "</textarea>";
/*                echo "</td><td><table><tr>
                    <td>����� ������ (����������):</td><td>".$sel_call_service_list1->htmlel ."</td>
                    <td>������, �� ������� ������ ���������� �����������:</td><td>".$sel_call_service_list2->htmlel ."</td>
                    <td>������ ������������, �� ������, �� ���������� � ����� ����� �����������:</td><td>".$sel_call_service_list3->htmlel ."</td>
                    </tr></table></td></tr>";*/
                echo "</td><td><table><tr>
                    <td><fieldset style='padding: 4px; border-color: darkgray;'>
                        <legend style='font-size: 0.8em;'>����� ������ (����������)</legend>
                        ".$sel_call_service_list1->htmlel ."</fieldset></td>
                    <td><fieldset style='padding: 4px; border-color: darkgray;'>
                        <legend style='font-size: 0.8em;'>������, �� ������� ������ ���������� �����������</legend>
                        ".$sel_call_service_list2->htmlel ."</fieldset></td>
                    <td><fieldset style='padding: 4px; border-color: darkgray;'>
                        <legend style='font-size: 0.8em;'>������ ������������, �� ������, �� ���������� � ����� ����� �����������</legend>
                        ".$sel_call_service_list3->htmlel ."</fieldset></td>
                    </tr></table></td></tr>";
                //,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,//
                // ��������������� ������
//                $install_radio=new CRadio("select case when defferedpay=0 then concat(main_sum,' ���. ')
//                        else concat(main_sum,' ���. ',defferedpay,' ���. �� ',month_pay,' ���.') end fo,id 
//                        FROM installg order by main_sum,month_pay", "install_radio", 1);  
                echo "<tr><td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>��������������� ������</b>
                    ". (($call_project["install"]) ? "(�� ������� ".$call_project["install"]." ���.)" : "") ."
                    </b>&nbsp;</legend>".
                    (new CRadio("select case when defferedpay=0 then concat(main_sum,' ���. ')
				else concat(main_sum,' ���. ',defferedpay,' ���. �� ',month_pay,' ���.(�����.',
                        month_pay*defferedpay-main_sum,'-',round((month_pay*defferedpay-main_sum)*100/defferedpay/main_sum,2),'%/���)') end fo,id,main_sum 
                        FROM installg order by main_sum,month_pay", "install_radio", $call_row_dop["installg_id"],true))->htmlel .
                    "<br>������� �� ����.�����<input type='text' name='guarantee' size='20' value='".$call_row_dop["guarantee"] ."'>
                    </fieldset></td>";
                echo "<td>";
                if($call_row_dop["installg_id"]>0){
                    $installg=rSQL("select main_sum,defferedpay,month_pay FROM installg where id=".$call_row_dop["installg_id"]);
                    if($installg['defferedpay']>0){
                        $call_total_array[3]+=$installg['month_pay'];
                        $call_total_array[4]+=$installg['defferedpay'];
                    }else{
                        $call_total_array[0]+=$installg['main_sum'];
                    }
                }
                //
                ////////////////////////////////////////////////////////////////
                // ����� ��������� �����
                //echo (new CRadio("SELECT concat(t.formulation,'/',s.sname),t.id "
                //        . "FROM tariff t left join service s on t.service_id=s.id where tech_id=3", "tariff_radio", 1))->htmlel;
                echo "<fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>����� ��������� �����</b>&nbsp;</legend>";
//                echo "  ����� (���.)<input type=\"checkbox\" name=\"tariff_manual\" 
//                            onclick=\"onTariffManualClick();\" 
//                            ".((isset($call_row_dop["tariff_id"]) and $call_row_dop["tariff_id"]==-1) ? "checked" : "") .">
//                        <input type='text' name='tariffname' size='30' value='".$call_row_dop["tariffname"] ."' ".
//                            ((isset($call_row_dop["tariff_id"]) and $call_row_dop["tariff_id"]==-1) ? "" : "disabled") .">
//                        ������.�����<input type='text' name='month_pay' size='16' value='".$call_row_dop["month_pay"] ."' ".
//                            ((isset($call_row_dop["tariff_id"]) and $call_row_dop["tariff_id"]==-1) ? "" : "disabled") .">��� ";
//                echo "  <div id='tariff_choose_ref' style='display: block;'><a href=\"javascript:void(0)\" 
//                            onclick=\"document.getElementById('tariff_selection_darkening').style.display = 'block';\">
//                            ����� ������</a><br>
//                        <input type='hidden' name='tariff_id' value='".$call_row_dop["tariff_id"] ."'>
//                        �����<input type='text' name='tariffnameB' size='30' value='".$call_row_dop["tformulation"] ."' readonly>
//                        ������.�����<input type='text' name='month_payB' size='16' value='".$call_row_dop["tmonth_pay"] ."' readonly>��� 
//                        ������.����� (2-� ���/������)<input type='text' name='month_pay2B' size='16' value='".$call_row_dop["tmonth_pay2"] ."' readonly>��� </div>";
                if(isset($call_row_dop["tariff_id"]) and $call_row_dop["tariff_id"]==-1){
                // '������' �����
//                    echo "  ����� (���.)<input type=\"checkbox\" name=\"tariff_manual\" 
//                                onclick=\"onTariffManualClick();\" checked>
//                            <input type='text' name='tariffname' size='30' value='".$call_row_dop["tariffname"] ."' >
//                            ������.�����<input type='text' name='month_pay' size='16' value='".$call_row_dop["month_pay"] ."' >��� ";
//                    echo "  <div id='tariff_choose_ref' style='display: none;'>
//                                <a style='font-weight:bold; font-size: 1.3em;' href=\"javascript:void(0)\" 
//                                onclick=\"document.getElementById('tariff_selection_darkening').style.display = 'block';\">
//                                ����� ������</a><br>
//                                <input type='hidden' name='tariff_id' value='-1'>
//                                �����<input type='text' name='tariffnameB' size='30' value='' readonly>
//                                ������.�����<input type='text' name='month_payB' size='16' value='' readonly>��� 
//                                ������.����� (2-� ���/������)<input type='text' name='month_pay2B' size='16' value='' readonly>��� 
//                            </div>";                    
                    
                    echo "  <div id='tariff_choose_ref' style='display: block;'>
                                <a style='font-weight:bold; font-size: 1.3em;' href=\"javascript:void(0)\" 
                                onclick=\"document.getElementById('tariff_selection_darkening').style.display = 'block';\">
                                ����� ������</a><br>
                                <input type='hidden' name='tariff_id' value='-1'>
                                �����<input type='text' name='tariffnameB' size='30' value='' readonly>
                                ������.�����<input type='text' name='month_payB' size='16' value='' readonly>��� 
                                ������.����� (2-� ���/������)<input type='text' name='month_pay2B' size='16' value='' readonly>��� 
                            </div>";
                    echo "  ����� (���.)<input type=\"checkbox\" name=\"tariff_manual\" 
                                onclick=\"onTariffManualClick();\" ".($call_row_dop["tariffname"] ? "checked" : "").">
                            <input type='text' name='tariffname' size='30' value='".$call_row_dop["tariffname"] ."' >
                            ������.�����<input type='text' name='month_pay' size='16' value='".$call_row_dop["month_pay"] ."' >��� ";
                    $call_total_array[3]+=$call_row_dop["month_pay"];
                } else {
                    echo "  <div id='tariff_choose_ref' style='display: block;'>
                            <a style='font-weight:bold; font-size: 1.3em;' href=\"javascript:void(0)\" 
                                onclick=\"document.getElementById('tariff_selection_darkening').style.display = 'block';\">
                                ����� ������</a><br>
                                <input type='hidden' name='tariff_id' value='".$call_row_dop["tariff_id"] ."'>
                                �����<input type='text' name='tariffnameB' size='30' value='".$call_row_dop["tformulation"] ."' readonly>
                                ������.�����<input type='text' name='month_payB' size='16' value='".$call_row_dop["tmonth_pay"] ."' readonly>��� 
                                ������.����� (2-� ���/������)<input type='text' name='month_pay2B' size='16' value='".$call_row_dop["tmonth_pay2"] ."' readonly>��� 
                            </div>";
                    $call_total_array[3]+=$call_row_dop["tmonth_pay"];
                    echo "  ����� (���.)<input type=\"checkbox\" name=\"tariff_manual\" 
                                onclick=\"onTariffManualClick();\" >
                            <input type='text' name='tariffname' size='30' value='".$call_row_dop["tariffname"] ."' disabled>
                            ������.�����<input type='text' name='month_pay' size='16' value='".$call_row_dop["month_pay"] ."' disabled>��� ";
                }
                
                echo "</fieldset></td></tr>";
                // ����� ��������� �����
                ////////////////////////////////////////////////////////////////
                // ONT(��������)
                echo "<tr><td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>ONT(��������)</b>&nbsp;</legend>
                    <table>
                    <tr><td></td><td><input type=radio name='ont_radio' value='0' checked></td><td>�������� �� ����� - 0 ���.</td></tr>
                    <tr style='color: #00a;'><td>� ���������� Wi-Fi ��������, 4 ����� ��� ��-���������</td><td>
                        <input type=radio name='ont_radio' value='112_6200_0' ".
                        (($call_com_array[112] and $call_com_array[112][1]==6200) ? "checked" : "") ."></td><td>������� - 6200 ���.</td></tr>
                    <tr style='color: #00a;'><td>� ���������� Wi-Fi ��������, 4 ����� ��� ��-���������</td><td><input type=radio name='ont_radio' value='112_0_150' ".
                        (($call_com_array[112] and $call_com_array[112][2]==150) ? "checked" : "") ."></td><td>������ - 150 ���. � �����</td></tr>
                    <tr style='color: #00a;'><td>� ���������� Wi-Fi ��������, 4 ����� ��� ��-���������</td><td><input type=radio name='ont_radio' value='112_0_1' ".
                        (($call_com_array[112] and $call_com_array[112][2]==1) ? "checked" : "") ."></td><td>������ - 1 ���. � �����</td></tr>
                    <tr style='color: #a00;'><td>��� Wi-Fi, ���� ��� ������, ���� ��� Wi-Fi �������</td>
                        <td><input type=radio name='ont_radio' value='113_2100_0' ".
                        (($call_com_array[113] and $call_com_array[113][1]==2100) ? "checked" : "") ."></td><td>������� - 2100 ���.</td></tr>
                    <tr style='color: #a00;'><td>��� Wi-Fi, ���� ��� ������, ���� ��� Wi-Fi �������</td><td><input type=radio name='ont_radio' value='113_0_100' ".
                        (($call_com_array[113] and $call_com_array[113][2]==100) ? "checked" : "") ."></td><td>������ - 100 ���. � �����</td></tr>
                    <tr style='color: #a00;'><td>��� Wi-Fi, ���� ��� ������, ���� ��� Wi-Fi �������</td><td><input type=radio name='ont_radio' value='113_0_1' ".
                        (($call_com_array[113] and $call_com_array[113][2]==1) ? "checked" : "") ."></td><td>������ - 1 ���. � �����</td></tr>
                    </table>
                    </fieldset></td>";
                $call_total_array[0]+=$call_com_array[112][1];
                $call_total_array[1]+=$call_com_array[112][2];
                $call_total_array[0]+=$call_com_array[113][1];
                $call_total_array[1]+=$call_com_array[113][2];
                // ������
                echo "<td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>������</b>&nbsp;</legend>
                    <input type=radio name='route_radio' value='114_1900_0_0_0_0' ".
                        (($call_com_array[114] and $call_com_array[114][1]==1900) ? "checked" : "") .">������� - 1900 ���.
                    <input type=radio name='route_radio' value='114_0_50_0_0_0' ".
                        (($call_com_array[114] and $call_com_array[114][2]==50) ? "checked" : "") .">������ - 50 ���. � �����<br>
                    <input type=radio name='route_radio' value='0' ".
                        (($call_com_array[114]) ? "" : "checked") .">�� ����� (����) - 0 ���.
                    <input type=radio name='route_radio' value='114_0_0_0_0_0' ".
                        (($call_com_array[114] and $call_com_array[114][1]==0 and $call_com_array[114][2]==0) ? "checked" : "") .">������ � ��������� ������<br>
                    <input type=radio name='route_radio' value='114_0_1_0_0_0' ".
                        (($call_com_array[114] and $call_com_array[114][1]==1) ? "checked" : "") .">������ - 1 ���. � �����<br>
                    <input type=radio name='route_radio' value='114_0_0_100_36_100' ".
                        (($call_com_array[114] and $call_com_array[114][5]==100) ? "checked" : "") .">��������� �� 36 ���. 100 ��� � �����<br>
                    </fieldset></td></tr>";
                $call_total_array[0]+=$call_com_array[114][1];
                $call_total_array[1]+=$call_com_array[114][2];
                $call_total_array[3]+=$call_com_array[114][5];
                // ���������
                /*$attach_radio_num=count($call_attach_array);
                echo "<tr><td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>���������</b>&nbsp;</legend>
                    ����������<input name='attach_radio_num' type=number min=0 max=4 value=".count($call_attach_array) ."
                        onchange=\"onAttachRadioNumChange();\"><br>
                    1-� <input type=radio name='attach_radio1' value='119_3800_0' ".(($call_com_array[119] and $call_com_array[119][1]==3800) ? "checked" : "") .">������� - 3800 ���.
                    <input type=radio name='attach_radio1' value='119_0_100' ".(($call_com_array[119] and $call_com_array[119][2]==100) ? "checked" : "") .">������ - 100 ���. � �����
                    <input type=radio name='attach_radio1' value='119_0_1' ".(($call_com_array[119] and $call_com_array[119][2]==1) ? "checked" : "") .">������ - 1 ���. � �����<br>
                    2-� <input type=radio name='attach_radio2' value='119_3800_0' checked disabled>������� - 3800 ���.
                    <input type=radio name='attach_radio2' value='119_0_100' disabled>������ - 100 ���. � �����<br>
                    3-� <input type=radio name='attach_radio3' value='119_3800_0' checked disabled>������� - 3800 ���.
                    <input type=radio name='attach_radio3' value='119_0_100' disabled>������ - 100 ���. � �����<br>
                    4-� <input type=radio name='attach_radio4' value='119_3800_0' checked disabled>������� - 3800 ���.
                    <input type=radio name='attach_radio4' value='119_0_100' disabled>������ - 100 ���. � �����<br>
                    </fieldset></td>";*/
                echo "<tr><td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>���������</b>&nbsp;</legend>
                    ����������<input name='attach_radio_num' type=number min=0 max=4 value=".$call_attach_array_i ."
                        onchange=\"onAttachRadioNumChange();\"><br>";
                for($ai=0;$ai<4;$ai++){
                    if($call_attach_array[$ai]==-1){
                        echo "".($ai+1)."-� <input type=radio name='attach_radio".($ai+1)."' value='119_3800_0' disabled>������� - 3800 ���.
                            <input type=radio name='attach_radio".($ai+1)."' value='119_0_100' disabled>������ - 100 ���. � �����";
                        if($ai<2){
                            echo "<input type=radio name='attach_radio".($ai+1)."' value='119_0_1' disabled>������ - 1 ���. � �����";
                        }
                    }else{
                        echo "".($ai+1)."-� <input type=radio name='attach_radio".($ai+1)."' value='119_3800_0' ".(($call_attach_array[$ai][1]==3800) ? "checked" : "") .">������� - 3800 ���.
                            <input type=radio name='attach_radio".($ai+1)."' value='119_0_100' ".(($call_attach_array[$ai][2]==100) ? "checked" : "") .">������ - 100 ���. � �����";
                        if($ai<2){
                            echo "<input type=radio name='attach_radio".($ai+1)."' value='119_0_1' ".(($call_attach_array[$ai][2]==1) ? "checked" : "") .">������ - 1 ���. � �����";
                        }
                        $call_total_array[0]+=$call_attach_array[$ai][1];
                        $call_total_array[1]+=$call_attach_array[$ai][2];
                    }
                    echo "<br>";
                }
                echo "</fieldset></td>";
                // SIM-����� (MVNO)
                /*echo "<td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>SIM-����� (MVNO)</b>&nbsp;</legend>
                    ����������:<input name='sim_radio_num' type=number min=0 max=50 value=".
                        (($call_com_array[$call_com_rcmid[930]][3]) ? ($call_com_array[$call_com_rcmid[930]][3]) : 0) ."><br>
                    
                    <a href=\"javascript:void(0)\" 
                        onclick=\"document.getElementById('mvno_tariff_selection_darkening').style.display = 'block';\">
                        ����� ������</a><br>
                    �����:
                    <input type=radio name='sim_tariff_radio' value='108' ".(($call_com_array[108]) ? "checked" : "") .">S
                    <input type=radio name='sim_tariff_radio' value='109' ".(($call_com_array[109]) ? "checked" : "") .">M
                    <input type=radio name='sim_tariff_radio' value='110' ".(($call_com_array[110]) ? "checked" : "") .">L
                    <input type=radio name='sim_tariff_radio' value='111' ".(($call_com_array[111]) ? "checked" : "") .">XL
                    </fieldset></td></tr>";*/
                echo "<td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>SIM-����� (MVNO)</b>&nbsp;</legend>
                    ����������:<input name='sim_radio_num' type=number min=0 max=50 value=".
                        (($call_com_array[108][3]) ? ($call_com_array[108][3]) : 0) ."><br>
                    <a href=\"javascript:void(0)\" 
                        onclick=\"document.getElementById('mvno_tariff_selection_darkening').style.display = 'block';\">
                        ����� ������</a><br>
                    <input type='hidden' name='mvno_tariff_id' value='".$call_com_array[108][8] ."'>
                    �����<input type='text' name='mvno_tariffnameB' size='30' value='".$call_com_array[108][9] ."' readonly>
                    ������.�����<input type='text' name='mvno_month_payB' size='16' value='".$call_com_array[108][10] ."' readonly>��� 
                    ������.����� (2-� ���/������)<input type='text' name='mvno_month_pay2B' size='16' value='".$call_com_array[108][11] ."' readonly>��� 
                    </fieldset></td></tr>";
                    //$call_total_array[2]+=$call_com_array[108][3];
                    //$call_total_array[3]+=$call_com_array[108][3]*$call_com_array[108][10];
                // ���������������
                echo "<tr><td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>���������������</b>&nbsp;</legend>                    
                    <table>
                    <tr style='color: #00a;'><td>
                    ���������� ���������� �����<input name='video_int_num' type=number min=0 max=10 style='width: 4em;' value=".
                            (($call_com_array[115]) ? $call_com_array[115][3] : 0) ."></td>
                        <td><input type=radio name='video_int' value='115_4990_0' ".
                            (($call_com_array[115] and $call_com_array[115][1]==4990) ? "checked" : "") .">������� - 4990 ���.</td>
                        <td><input type=radio name='video_int' value='115_0_300' ".
                            (($call_com_array[115] and $call_com_array[115][5]==300) ? "checked" : "") .">��������� - 300 ���. �� 24 ���. </td> 
                        <td>������ (350 ���/��)<input name='video_int_mont_num' type=number min=0 max=10 style='width: 4em;' value=".
                            (($call_com_array[122]) ? $call_com_array[122][3] : 0) .">��</td>
                    </tr>
                    <tr style='color: #a00;'><td>
                    ���������� ������� �����<input name='video_ext_num' type=number min=0 max=10 style='width: 4em;' value=".
                            (($call_com_array[116]) ? $call_com_array[116][3] : 0) ."></td>
                        <td><input type=radio name='video_ext' value='116_5990_0' ".
                            (($call_com_array[116] and $call_com_array[116][1]==5990) ? "checked" : "") .">������� - 5990 ���.</td>
                        <td><input type=radio name='video_ext' value='116_0_300' ".
                            (($call_com_array[116] and $call_com_array[116][5]==300) ? "checked" : "") .">��������� - 300 ���. �� 24 ���., ������ ����� 1200 ���.</td>
                        <td>������ (1500 ���/��)<input name='video_ext_mont_num' type=number min=0 max=10 style='width: 4em;' value=".
                            (($call_com_array[123]) ? $call_com_array[123][3] : 0) .">��</td>
                        <td>POE �������� (950 ���/��)<input type='checkbox' name='video_ext_poe' ".
                            (($call_com_array[124]) ? 'checked' : ' ') .">
                    </tr>
                    </table>
                    </fieldset></td>";
                    $call_total_array[0]+=$call_com_array[115][3]*$call_com_array[115][1];
                    $call_total_array[2]+=$call_com_array[115][3];
                    $call_total_array[3]+=$call_com_array[115][3]*$call_com_array[115][5];
                    $call_total_array[0]+=$call_com_array[116][3]*$call_com_array[116][1];
                    $call_total_array[2]+=$call_com_array[116][3];
                    $call_total_array[3]+=$call_com_array[116][3]*$call_com_array[116][5];                
                    $call_total_array[0]+=$call_com_array[124][1];
                // ����� ���
                echo "<td><fieldset style='padding: 4px; border-color: darkgray;'>
                    <legend>&nbsp;<b style='color: #006;'>����� ���</b>&nbsp;</legend>
                    <table>
                    <tr style='color: #000;'><td>
                    ���<input type=radio name='smart_house_type' value='0' checked></td>
                    </tr>
                    <tr style='color: #a00;'><td>
                    ������� (��������, ��������): <input type=radio name='smart_house_type' value='120_11590_0_0_0' ".
                        (($call_com_array[120] and $call_com_array[120][1]==11590) ? "checked" : "")
                        ." title=''>������� (11590 ���), 
                        <input type=radio name='smart_house_type' value='120_0_600_24_1300' ".
                        (($call_com_array[120] and $call_com_array[120][1]==0) ? "checked" : "") .">��������� (1-� ������ 1300, ����������� - 600 ��� �� 24 ���)</td>
                    </tr>
                    <tr style='color: #00a;'><td>
                    ����������� (��������, ��������, ���, ��������): <input type=radio name='smart_house_type' value='121_16990_0_0_0' ".
                        (($call_com_array[121] and $call_com_array[121][1]==16990) ? "checked" : "")
                        ." title=''>������� (16990 ���), 
                        <input type=radio name='smart_house_type' value='121_0_900_24_1300' ".
                        (($call_com_array[121] and $call_com_array[121][1]==0) ? "checked" : "") .">��������� (1-� ������ 1300, ����������� - 900 ��� �� 24 ���)</td>
                    </tr>
                    <tr style='color: #000;'><td>  
                    ������ �������������� ��������: ���������� (150 ���/��)<input name='smart_house_int_num' type=number min=0 max=10 style='width: 6em;' value=".
                        (($call_com_array[117]) ? $call_com_array[117][3] : 0) .">��, &nbsp;
                    ������� (300 ���/��)<input name='smart_house_out_num' type=number min=0 max=10 style='width: 6em;' value=".
                        (($call_com_array[118]) ? $call_com_array[118][3] : 0) .">��</td>
                    </tr>
                    </table>
                    </fieldset></td></tr>";
                    $call_total_array[0]+=$call_com_array[120][1];
                    $call_total_array[0]+=$call_com_array[121][1];
                    $call_total_array[0]+=$call_com_array[117][3]*150+$call_com_array[118][3]*300;
                // ������ �� ������������ �����
                $workpath=rSQL("select * from workpath where object_type=2 and cnaid=45 and lp_id=".$_REQUEST["lid"] ." ");
                echo "<tr><td><p style='color: #00a;'>����������� ���� ����������� �����:<input type='date' name='targetdate_av' size='12' value='".
                        explode(" ",$workpath["targetdate"])[0] ."'></p>
                    ����������� ���� ����������� �����:<input type='date' name='finishdate_av' size='12' value='".explode(" ",$workpath["finishdate"])[0] ."'><br>
                    ����������� ���� �����������:<input type='date' name='targetdate' size='12' value='".explode(" ",$call_row_dop["targetdate"])[0] ."'><br>
                    <p style='color: #00a;'>���� ���������� �������� � ���������:<input type='date' name='finishdate' size='12' value='".
                        explode(" ",$call_row_dop["finishdate"])[0] ."'></p>";
                echo "<p style='color: #800;'>
                    �����: ����.����� ".$call_total_array[0] ." ���,
                    ������ ".$call_total_array[1] ." ���,
                    ������. ".$call_total_array[3] ." ���,
                    ��������� ".$call_total_array[4] ." ���,
                    ����.����� ".$call_total_array[5] ." ���.
                    </p></td>";
                // ������ ������ �� �������� ����� ������
//                $cursor=SQL($com_object_query);
//                $all_expense=0.0;
//                while ($cursor->assoc()) {
//                    $expense=round($cursor->r["rcmprice"] 
//                                * (($cursor->r["ccmlen"]!=0)? $cursor->r["ccmlen"] : 1.0) 
//                                * (($cursor->r["ccmamount"]!=0)? $cursor->r["ccmamount"] : 1.0) 
//                                * (($cursor->r["bid"]==1)? 0.7 : 1.0),2); // ���� "���������", �� *0.7
//                    $all_expense+=$expense;
//                }
//                $cursor->free();
                //
                echo "<td>
                    ���������� �� ������:<br><textarea name='comment' rows=4 cols=80>".$call_row_dop["comment"] ."</textarea><br>
                    <div style='display:inline-block; padding:2px; border:solid 1px darkblue; margin-top: 6px;'>
                    <img src='./images/save.gif' align='absmiddle'> �������� ����.�����(�������� ����)
                        <input type='file' name='drawing[]' id='drawing' multiple> 
                    <a href='vlg_image.php?otype=2&oid=".$_REQUEST["lid"] ."' target='_blank'>
                        <img src='./images/search.gif' align='absmiddle'> �������� �����</a>&nbsp&nbsp&nbsp       
                    <img src='./images/aff_cross.gif' align='absmiddle'> ������� �����<input type='checkbox' name='delschema' value='true'>
                    </div></td></tr>";
                echo "</table>";
                echo "<br><div style='display:inline-block; margin-top: 10px;'>
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
                //echo "</form></fieldset>";
                ////////////////////////////////////////////////////////////////
                // vv ��������� �� �������� ��� ����������� ���������� ������ vv
                // ���������� � ������� ��� � ������������
                // ^^ ��������� �� �������� ��� ����������� ���������� ������ ^^
                ////////////////////////////////////////////////////////////////
                // vvv �������������� �������� ����� vvv
                // ^^^ �������������� �������� ����� ^^^
                ////////////////////////////////////////////////////////////////
            }
        break;
////////////////////////////////////////////////////////////////////////////////
        default :
        break;
        }
?>
    <script>
    //
    function onTariffManualClick(){
        if(document.new_project.tariff_manual.checked==true){
            //document.new_project.tariff_manual.checked=false;
            document.new_project.tariffname.disabled=false;
            document.new_project.month_pay.disabled=false;
            document.getElementById('tariff_choose_ref').style.display='none';
        } else {
            //document.new_project.tariff_manual.checked=true;
            document.new_project.tariffname.disabled=true;
            document.new_project.month_pay.disabled=true;
            document.getElementById('tariff_choose_ref').style.display='block';
        }
    }
    //
    function onAttachRadioNumChange(){
        var num=document.new_project.attach_radio_num.value;
        //alert(num);
            document.new_project.attach_radio4[0].disabled=true;
            document.new_project.attach_radio4[1].disabled=true;
            document.new_project.attach_radio3[0].disabled=true;
            document.new_project.attach_radio3[1].disabled=true;
            document.new_project.attach_radio2[0].disabled=true;
            document.new_project.attach_radio2[1].disabled=true;
            document.new_project.attach_radio2[2].disabled=true;
            document.new_project.attach_radio1[0].disabled=true;
            document.new_project.attach_radio1[1].disabled=true;
            document.new_project.attach_radio1[2].disabled=true;
        if(num>0){
            document.new_project.attach_radio1[0].disabled=false;
            document.new_project.attach_radio1[1].disabled=false;
            document.new_project.attach_radio1[2].disabled=false;
            if(num>1){
                document.new_project.attach_radio2[0].disabled=false;
                document.new_project.attach_radio2[1].disabled=false;
                document.new_project.attach_radio2[2].disabled=false;
                if(num>2){
                    document.new_project.attach_radio3[0].disabled=false;
                    document.new_project.attach_radio3[1].disabled=false;
                    if(num>3){
                        document.new_project.attach_radio4[0].disabled=false;
                        document.new_project.attach_radio4[1].disabled=false;
                    }
                }
            }
        }
    }
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
