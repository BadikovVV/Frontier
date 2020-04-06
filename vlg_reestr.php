<?php
// фильтр для заявок и таблица заявок
ini_set('display_errors', 'On');
error_reporting('E_ALL');
////////////////////////////////////////////////////////////////////////////////
function vlg_reestr($row_users,$user_LTC_list){
global $ubord;
$footer_text=""; // протокол работы для этой сессии PHP, будет показан в конце страницы
if (isset($_POST["search_arm_id"]) and $_POST["search_arm_id"] != '')
    $_GET["search_arm_id"] = $_POST["search_arm_id"];
//if(isset($_REQUEST["next_stage_status"]) and isset($_REQUEST["search_lid"]) and $_REQUEST["search_lid"]!=-1){
if(getReq("next_stage_status") and getReq("search_lid")!=-1){
// непосредственно меняем статус, закрываем этап (callpath), открываем новый этап (callpath)
    // пример getReq("next_stage_status") - "6. ТВ проработана / 7. Согласование ДФ"
    $next_stage_status2=explode(" / ",$_REQUEST["next_stage_status"]);
    $next_stage_status2_0=explode(". ",$next_stage_status2[0]);
    $next_stage_status2_1=explode(". ",$next_stage_status2[1]); // может быть NULL
    $next_stage_checkhour=rSQL("SELECT checkhour FROM ps_status where id=".$next_stage_status2_0[0]."")["checkhour"];
    //qSQL("set autocommit=0");
    //qSQL("begin");
    if(getReq("next_stage")==1){
    // групповое изменение статуса и передача заявок следующему исполнителю
        $next_stage_result=''; // групповое изменение статуса и передача заявок следующему исполнителю
        $rownum=0;
        $result_cid = qSQL(getReq("reestr_query"));
        while ($row_cid = mysql_fetch_array($result_cid)) {
            //$next_stage_result.=' '.$row_cid["lid"];
            SQL("UPDATE ps_list_dop SET status=". $next_stage_status2_0[0] ." WHERE lid='". $row_cid["lid"] ."'"); // меняем статус
            SQL("update callpath set shutdate=now() WHERE object_type=2 and lp_id='". $row_cid["lid"] ."' and shutdate is null"); // закрываем этап
            SQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment,issent,filename,recommend,nextcallpath,shutdate)
                VALUES(NULL,'". $row_cid["lid"] ."','". $next_stage_status2_0[0] ."',". 
                    $row_users['uid'] .",-1,DATE_ADD(now(),interval ".$next_stage_checkhour." hour),2,'".
                    getReq("next_stage_add")."',0,NULL,".$next_stage_status2_1[0].",NULL,NULL)")->commit();
            $next_stage_result.=$row_cid["arm_id"].',';
            if($rownum%6==5) $next_stage_result.="\r\n";
            else $next_stage_result.=' ';
            $rownum++;
        }
        eMail(rSQL("SELECT email FROM ps_users where uid=".$_REQUEST["next_stage_user_select"])["email"],"Частный сектор",
            "Изменился статус заявок \r\n".$next_stage_result."\r\n".
            "Новый статус '". $next_stage_status2_0[1] . "' \n".
            "Вам необходимо обработать эти заявки\n".
            "Дополнительное сообщение: '".$_REQUEST["next_stage_add"]."'\n");
    // ^^ групповое изменение статуса и передача заявок следующему исполнителю ^^
    } else {
        // изменение статуса ОДНОЙ и передача заявок следующему исполнителю
        SQL("UPDATE ps_list_dop SET status=". $next_stage_status2_0[0] ." WHERE lid='". $_REQUEST["search_lid"] ."'"); // меняем статус
        SQL("update callpath set shutdate=now() WHERE object_type=2 and lp_id='". $_REQUEST["search_lid"] ."' and shutdate is null"); // закрываем этап
        SQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment,issent,filename,recommend,nextcallpath,shutdate)
            VALUES(NULL,'". $_REQUEST["search_lid"] ."','". $next_stage_status2_0[0] ."',". 
                $row_users['uid'] .",-1,DATE_ADD(now(),interval ".$next_stage_checkhour." hour),2,'".
                getReq("next_stage_add")."',0,NULL,".$next_stage_status2_1[0].",NULL,NULL)")->commit();
    }
    //qSQL("commit");
    //qSQL("set autocommit=1");
}
////////////////////////////////////////////////////////////////////////////////
// удаление всех данных, кроме справочников
/*
truncate ps_list_dop;
truncate callpath;
truncate workpath;
truncate call_com_mat;
truncate map_obj
truncate ps_equip_cid;
truncate ps_smet_cid;
truncate files;
truncate ps_arm_buffer;
truncate ps_list;
truncate ps_project_list;
 * truncate ps_project;
 */ 
////////////////////////////////////////////////////////////////////////////////
// удаление записей, связанных с вариантом подключения
if(getReq("smr_delete")>0){
    $rownum=0;
    $ps_list_dop_smr_copy_row=rSQL("SELECT * FROM ps_list_dop where lid='".$_REQUEST["smr_delete"]."' ");
    $result_del=SQL("delete from ps_list_dop where lid='".$_REQUEST["smr_delete"]."' ");
    $rownum+=$result_del->affected_rows();
    SQL("delete from callpath WHERE object_type=2 and lp_id='".$_REQUEST["smr_delete"]."' ");
    $rownum+=$result_del->affected_rows();
    SQL("delete from ps_equip_cid where stype=1 and cid='".$_REQUEST["smr_delete"]."' ");
    $rownum+=$result_del->affected_rows();
    SQL("delete from ps_smet_cid where stype=1 and cid='".$_REQUEST["smr_delete"]."' ");
    $rownum+=$result_del->affected_rows();
    $result_del=SQL("DELETE FROM blobs WHERE bid=(select blob_id from files where otype=2 and oid=".$_REQUEST["smr_delete"] .")");
    $rownum+=$result_del->affected_rows();
    SQL("DELETE FROM files where otype=2 and oid=".$_REQUEST["smr_delete"] ."")->commit();
    $footer_text.="<br>Удалено ".$rownum." записей."; // протокол работы для этой сессии PHP, будет показан в конце страницы
}
////////////////////////////////////////////////////////////////////////////////
// групповое копирование расчета по заявке
if(getReq("smr_copy")>0){
    //qSQL("set autocommit=0");
    //qSQL("begin");
    $rownum=0;
    //$ps_list_dop_smr_copy_row=rSQL("SELECT * FROM ps_list_dop where lid='".$_REQUEST["smr_copy"]."' and tpid='".$_REQUEST["connvar"]."' ");
    $ps_list_dop_smr_copy_row=rSQL("SELECT * FROM ps_list_dop where lid='".$_REQUEST["smr_copy"]."' ");
    $cursor=SQL(getReq("reestr_query"));
    while ($cursor->assoc()) {
        if($cursor->r["arm_id"]!=$ps_list_dop_smr_copy_row["arm_id"] and $cursor->r["tpid"]==-1 and strlen($cursor->r["technology"])>0){
            if(rSQL("SELECT count(*) cnt FROM ps_list_dop where arm_id='".$cursor->r["arm_id"].
                    "' and tpid='".$ps_list_dop_smr_copy_row["tpid"]."'")["cnt"] == 0){
            // для текущей заявки нет такого варианта подключения
                // получаем из ps_list_dop текущей строки и строки прототипа
                $ps_list_dop_row=rSQL("SELECT * FROM ps_list_dop where lid='".$cursor->r["lid"]."' ");
                $result_ins=SQL("INSERT INTO ps_list_dop (list_id,status,arm_id,
                    comment,file_smeta,zatrat_smr,
                    dev_summ,shkaf_42u,shassi_olt,
                    kol_ports,spd,difficult_mc,
                    difficult_rs,difficult_abl,difficult_abv,
                    install,month_pay,pon_flag,
                    formatted_address,place_id,location_type,claster_id,tpid,service_id,
                    uid,dateedit,guarantee,tariffname,ontlease,routelease,
                    realcost,targetdate,finishdate,substatus,deferredpay,ontfullpay,routefullpay,attachnum,attachfullpay,attachlease)
                    VALUES
                    ('".$ps_list_dop_row["list_id"]."','".$ps_list_dop_row["status"]."','".$cursor->r["arm_id"].
                    "','".$ps_list_dop_row["comment"]."','".$ps_list_dop_smr_copy_row["file_smeta"]."','".$ps_list_dop_smr_copy_row["zatrat_smr"].
                    "','".$ps_list_dop_smr_copy_row["dev_summ"]."','".$ps_list_dop_smr_copy_row["shkaf_42u"]."','".$ps_list_dop_smr_copy_row["shassi_olt"].
                    "','".$ps_list_dop_smr_copy_row["kol_ports"]."','".$ps_list_dop_smr_copy_row["spd"]."','".$ps_list_dop_smr_copy_row["difficult_mc"].
                    "','".$ps_list_dop_smr_copy_row["difficult_rs"]."','".$ps_list_dop_smr_copy_row["difficult_abl"]."','".$ps_list_dop_smr_copy_row["difficult_abv"].
                    "','".$ps_list_dop_smr_copy_row["install"]."','".$ps_list_dop_smr_copy_row["month_pay"]."','".$ps_list_dop_smr_copy_row["pon_flag"].
                    "','".$ps_list_dop_row["formatted_address"]."','".$ps_list_dop_row["place_id"]."','".$ps_list_dop_row["location_type"].
                    "','".$ps_list_dop_row["claster_id"]."','".$ps_list_dop_smr_copy_row["tpid"]."','".$ps_list_dop_smr_copy_row["service_id"].
                    "',".$row_users['uid'].",now(),'".$ps_list_dop_row["guarantee"]."','".$ps_list_dop_smr_copy_row["tariffname"]."','".$ps_list_dop_row["ontlease"].
                    "','".$ps_list_dop_row["routelease"]."','".$ps_list_dop_row["realcost"]."','".$ps_list_dop_row["targetdate"].
                    "','".$ps_list_dop_row["finishdate"]."','".$ps_list_dop_row["substatus"]."','".$ps_list_dop_row["deferredpay"].
                    "','".$ps_list_dop_row["ontfullpay"]."','".$ps_list_dop_row["routefullpay"]."','".$ps_list_dop_row["attachnum"].
                    "','".$ps_list_dop_row["attachfullpay"]."','".$ps_list_dop_row["attachlease"]."')");
                $ps_list_dop_new_id = $result_ins->insert_id();
                $result_ins=SQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment,issent,filename,recommend,nextcallpath,shutdate)
                VALUES(NULL,'". $ps_list_dop_new_id ."','". $ps_list_dop_row["status"] ."',". 
                    $row_users['uid'] .",-1,DATE_ADD(now(),interval 240 hour),2,'',0,NULL,NULL,NULL,NULL)");
                $result_ins=SQL("INSERT INTO ps_equip_cid (cid,pid,price,kol,uid,dtime,onoff,comment,stype,map_obj) 
                        SELECT '".$ps_list_dop_new_id."',pid,price,kol,uid,dtime,onoff,comment,stype,map_obj FROM ps_equip_cid  
                        where stype=1 and cid='".$ps_list_dop_smr_copy_row["lid"]."' ");
                SQL("INSERT INTO ps_smet_cid (cid,pid,price,kol,uid,dtime,onoff,comment,stype,map_obj) 
                        SELECT '".$ps_list_dop_new_id."',pid,price,kol,uid,dtime,onoff,comment,stype,map_obj FROM ps_smet_cid 
                        where stype=1 and cid='".$ps_list_dop_smr_copy_row["lid"]."' ")->commit();
            }
        }
        $rownum++;
    }
    //qSQL("commit");
    //qSQL("set autocommit=1");
}
// ^^ групповое копирование расчета по заявке ^^
////////////////////////////////////////////////////////////////////////////////
// форма-шапка name='search'
//d($user_LTC_list);
echo "<form name='search' id='search_form_id' method='POST' action='./?c=2'>
    Мои заявки<input type=\"checkbox\" name=\"for_my_job\" ".
        ((isset($_REQUEST["for_my_job"]) and $_REQUEST["for_my_job"]=='on') ? "checked" : "")."> ";
//echo "МЦТЭТ " . str_replace('select name=',
//        "select onchange = '' name=",
//        select('mapfilter_mctet', "SELECT name FROM ps_mctet order by name",$_REQUEST["mapfilter_mctet"],"выберите...")) . " ";
echo "ЛТЦ " . str_replace('select name=',
        "select onchange = '' name=",
        select('mapfilter_ltc', "SELECT lname FROM ltc order by lname",$_REQUEST["mapfilter_ltc"],"выберите...")) . "
    Номер заявки в АРМ <input type='text' name='search_arm_id' value='".$_REQUEST["search_arm_id"]."'> 
    <input type='hidden' form='search_form_id' name='search_lid' value='-1'>
    <input type='hidden' form='search_form_id' name='reestr_query' value=''>
    Адрес <input type='text' name='mapfilter_address' value='".$_REQUEST["mapfilter_address"]."'>
    ";
// фильтр заявок
echo "<br><b> Статус </b>";
if(isset($_REQUEST["search_status"]) and $_REQUEST["search_status"]!=-1)
    echo select2('search_status', "select 'выберите...',-1 union SELECT concat(id,'. ',name),id FROM ps_status",
        rSQL("SELECT concat(id,'. ',name) rstr FROM ps_status where id=".$_REQUEST["search_status"])["rstr"]);
else
    echo select2('search_status', "select 'выберите...',-1 union SELECT concat(id,'. ',name),id FROM ps_status","выберите...");    
//
echo "
    <b> АРМ Статус </b>";
if(isset($_REQUEST["mapfilter_arm_status"]) and $_REQUEST["mapfilter_arm_status"]!=-1)
    echo select2('mapfilter_arm_status', "select 'выберите...',-1 union SELECT concat(as_id,'. ',status_name),as_id FROM ps_arm_status",
        rSQL("SELECT concat(as_id,'. ',status_name) rstr FROM ps_arm_status where as_id=".$_REQUEST["mapfilter_arm_status"])["rstr"]);
else
    echo select2('mapfilter_arm_status', "select 'выберите...',-1 union SELECT concat(as_id,'. ',status_name),as_id FROM ps_arm_status","выберите...");
//
echo "
    <br><b> Технология </b>";
if(isset($_REQUEST["search_conn_var"]) and $_REQUEST["search_conn_var"]!=-1)
    echo select2('search_conn_var', "select 'выберите...',-1 union SELECT concat(id,'. ',name),id FROM ps_teh_podkl",
        rSQL("SELECT concat(id,'. ',name) rstr FROM ps_teh_podkl where id=".$_REQUEST["search_conn_var"])["rstr"]);
else
    echo select2('search_conn_var', "select 'выберите...',-1 union SELECT concat(id,'. ',name),id FROM ps_teh_podkl","выберите...");
//
echo "
    <b> Услуга </b>";
if(isset($_REQUEST["search_service"]) and $_REQUEST["search_service"]!="выберите...")
    echo select('search_service', "SELECT distinct service FROM private_sector.ps_list order by service",$_REQUEST["search_service"],"выберите...");
else
    echo select('search_service', "SELECT distinct service FROM private_sector.ps_list order by service","выберите...","выберите...");
//
echo "
    <b> Проект </b>";
//if(isset($_REQUEST["project_id"]) and $_REQUEST["project_id"]!=-1)
//    echo select2('project_id', "select 'выберите...',-1 union SELECT concat(project_id,'. ',project_name),project_id FROM ps_project",
//        rSQL("SELECT concat(project_id,'. ',project_name) rstr FROM ps_project where project_id=".$_REQUEST["project_id"])["rstr"]);
//else
//    echo select2('project_id', "select 'выберите...',-1 union SELECT concat(project_id,'. ',project_name),project_id FROM ps_project","выберите...");
if(isset($_REQUEST["project_id"]) and $_REQUEST["project_id"]!=-1)
    echo select2('project_id', "select * from (select 'выберите...' project_name,-1 project_id,' ' pname  union 
        SELECT concat(project_id,'. ',project_name),project_id,project_name pname FROM ps_project prj) sprj
            order by pname",
        rSQL("SELECT concat(project_id,'. ',project_name) rstr FROM ps_project where project_id=".$_REQUEST["project_id"])["rstr"]);
else
    echo select2('project_id', "select * from (select 'выберите...' project_name,-1 project_id,' ' pname  union 
        SELECT concat(project_id,'. ',project_name),project_id,project_name pname FROM ps_project prj) sprj
            order by pname","выберите...");
//
echo "
    <b> Кластер </b>";
if(isset($_REQUEST["search_cluster"]) and $_REQUEST["search_cluster"]!=-1)
    echo select2('search_cluster', "select 'выберите...',-1 union SELECT concat(id,'. ',cname),id FROM cluster",
        rSQL("SELECT concat(id,'. ',cname) rstr FROM cluster where id=".$_REQUEST["search_cluster"])["rstr"]);
else
    echo select2('search_cluster', "select 'выберите...',-1 union SELECT concat(id,'. ',cname),id FROM cluster","выберите...");
//
echo "<input type='submit' value='Выбрать/обновить заявки'>
    </form>";
////////////////////////////////////////////////////////////////////////////////
popup_info_window(); // создаём пустое всплывающее информационное окно
// всплывающее окно с выбором нового статуса и сообщения
echo "<div id='next_stage_darkening' class='ps_popup_darkening'> 
        <div id='next_stage' class='ps_popup_main_window'> 
            <a class='ps_popup_close_button' title='Закрыть' 
                onclick='document.getElementById(\"next_stage_darkening\").style.display = \"none\";'>X</a>
            <b id='next_stage_status_select'></b> 
            <br><b>Выберите сотрудника</b> " . 
                str_replace('select name=',"select form='search_form_id' name=",
                    select2('next_stage_user_select', "select 'выберите...',-1 union SELECT concat(uid,'. ',fio),uid FROM ps_users","выберите...")) . "
            <br><b>Дополнительное сообщение</b> <input type='text' form='search_form_id' name='next_stage_add' size='60' value=''>
            <input type='hidden' form='search_form_id' name='next_stage' value='0'>
            <br><input type='submit' form='search_form_id' value='Выполнить' onclick=' 
                //document.search.next_stage.value=0; 
                document.search.search_lid.value=current_ps_list_dop; 
                return true; '>
    </div></div>";
// для группового изменения статуса document.search.next_stage.value=1
//                <br><input type='submit' form='search_form_id' value='Выполнить' onclick=' document.search.next_stage.value=1; return true; '>
//    </div></div>";
// ^^ всплывающее окно с выбором нового статуса и сообщения ^^
////////////////////////////////////////////////////////////////////////////////
// всплывающее окно с выбором варианта подключения
echo "<div id='new_conn_darkening' class='ps_popup_darkening'> 
        <div id='new_conn' class='ps_popup_main_window'> 
            <a class='ps_popup_close_button' title='Закрыть' 
                onclick='document.getElementById(\"new_conn_darkening\").style.display = \"none\";'>X</a>
            <form name='new_conn_form' method='POST' action='vlg_call.php?action=call_edit&sourcepage=reestr&lid=-1' >
                <b>Выберите технологию подключения</b> " . 
                    select2('new_conn_form_tech', "select 'выберите...',-1 union SELECT concat(id,' ',name),id FROM ps_teh_podkl","выберите...") . "
                <br><b>Выберите услугу (необязательно) </b> " . 
                    select2('new_conn_form_service', "select 'выберите...' sname,-1 id union SELECT sname,id FROM service order by sname","выберите...") . "
                <input type='hidden' name='new_conn_form_lid' value='-1'>
                <br><input type='submit' onclick=' document.getElementById(\"new_conn_darkening\").style.display = \"none\"; ' value='Выполнить'>
            </form>
    </div></div>";
// ^^ всплывающее окно с выбором варианта подключения ^^
// стили основной таблицы
echo "<style type=\"text/css\">
        .scroll-table { width: 100%; overflow: auto; height: 600px; position: relative; }
        .scroll-table table { border-collapse: collapse; }
        .scroll-table td { padding: 5px; }
        .scroll-table th { background: #eee; padding: 5px; }
        .scroll-table tr.fixed { position: relative;
                top: expression(this.parentElement.parentElement.parentElement.scrollTop)
        }
        .scroll-table>table tbody {/* <- added */
                height: 0px; overflow: auto; overflow-x: hidden;
        }
    </style>";
//echo "<link rel=\"stylesheet\" href=\"js/examples.css\" type=\"text/css\">";
// основная таблица
/*echo "<div id=\"container\" style=\"border: solid 1px black; margin-bottom: 8px; margin-top: 8px; padding: 2px;\">
    <div class=\"scroll-table\">
    <!--form method='POST' name='reestr' action='vlg_download.php' target='_blank'-->
    <table width='100%'  cellpadding=\"0\" cellspacing=\"0\" border=\"1\">
    <thead>
    <tr bgcolor='gray' class=\"fixed\" style='color: white'>
    <!--td width='' align='center' style='border-left-color: black'><b><small>SID</small></b></td-->
    <td width='' align='center' style='border-left-color: black'><b>АРМ №</b></td>
    <td width='' align='center'><b>Адрес</b></td>
    <td align='center'><b>Кластер</b></td>
    <td align='center'><b>АРМ Статус</b></td>
    <td align='center'><b>Дата обращения</b></td>
    <td align='center'><b>Технология (Услуга)</td>
    <td align='center'>Вариант<br>подключения</td>
    <td align='center'><b>Статус</b></td>
    <!--td align='center'>42U / OLT / Порты / СПД</td-->
    <!--td align='center'>МС / РС / АбЛ / АбВв</td-->
    <td align='center'><b>Рассрочка(мес.) / платёж(руб.) / гарантия</b></td>
    <td align='center'><b>Тариф / платёж(руб.)</b></td>
    <td align='center'><b>ONT: полная / аренда</b></td>
    <td align='center'><b>Роутер: полная / аренда</b></td>
    <td align='center'><b>Приствки: кол-во / полная / аренда</b></td>
    <td align='center'><b>Затраты Оборудование</b></td>
    <td align='center'><b>Затраты СМР</b></td>
    <!--td align='center'><b>История</b></td-->
    </tr>";*/
echo "<div id=\"container\" style=\"border: solid 1px black; margin-bottom: 8px; margin-top: 8px; padding: 2px;\">
    <div class=\"scroll-table\">
    <table width='100%'  cellpadding=\"0\" cellspacing=\"0\" border=\"1\">
    <thead>
    <tr bgcolor='gray' class=\"fixed\" style='color: white'>
    <td width='' align='center' style='border-left-color: black'><b>АРМ №</b></td>
    <td width='' align='center'><b>Имя-Фамилия</b></td>
    <td width='' align='center'><b>Адрес</b></td>
    <td align='center'><b>Проект</b></td>
    <td align='center'><b>АРМ Статус</b></td>
    <td align='center'><b>Дата обращения</b></td>
    <td align='center'><b>Технология (Услуга)</td>
    <td align='center'>Вариант<br>подключения</td>
    <td align='center'><b>Статус</b></td>
    <td align='center'>Посл. контакт</td>
    </tr>";
echo"</thead>";
?>
<script type="text/javascript">
//
xmlHttp=new XMLHttpRequest();
current_user_uid=<?php echo $row_users['uid']; ?>;
current_user_ugroup=<?php echo $row_users['ugroup']; ?>;
var current_ps_list_dop=false;
/*function onNewConnChangeClick(arm_id) {
    //alert("./?c=11&cid="+arm_id+"&connvar="+document.getElementsByName("select_conn_var_"+arm_id)[0].value);
    var lConnVar=document.getElementsByName("select_conn_var_"+arm_id)[0].value;
    if(lConnVar==-1){
        document.getElementsByName("select_conn_var_"+arm_id)[0].nextSibling.style.display='none';
    }else{
        document.getElementsByName("select_conn_var_"+arm_id)[0].nextSibling.style.display='inline-block';
    }
}*/
//a href='' - После клика по такой ссылке, будет просто перезагружена страница в браузере.
//a href='#' - После клика по такой ссылке, пользователя выбросит в начало страницы, без её перезагрузки.
/*function onArmIdClick(arm_id) {
    var lConnVar=document.getElementsByName("select_conn_var_"+arm_id)[0].value;
    if(lConnVar==-1){
        //alert("Выберите вариант подключения");
    }else{
        var myWindow=window.open("http://"+location.hostname+":"+location.port+location.pathname+"?c=11&cid="+arm_id+
                "&connvar="+document.getElementsByName("select_conn_var_"+arm_id)[0].value,"","height=600,width=800");
        //myWindow.location="http://"+location.hostname+":"+location.port+location.pathname+"?c=11&cid="+arm_id+
        //       "&connvar="+document.getElementsByName("select_conn_var_"+arm_id)[0].value;   
        //window.location="http://"+location.hostname+":"+location.port+location.pathname+"?c=11&cid="+arm_id+
        //        "&connvar="+document.getElementsByName("select_conn_var_"+arm_id)[0].value;
    }
}*/
// запрос подтверждения и копирование расчетов
// !!! УСТАРЕЛО см. ниже
/*function onArmIdRightClick(lid) {
    if (confirm("Скопировать расчеты по заявке \n"+
        "на все выбранные заявки?\n"+
        "(при условии, что по ним нет своего расчета)")) {
        //alert();
        //window.location="./?c=2&smr_copy="+arm_id+"&connvar="+connvar+"&reestr_query="+document.reestr.reestr_query.value;  
        addHidden(document.search,'smr_copy',lid);
        //addHidden(document.search,'connvar',connvar);
        document.search.reestr_query.value=document.reestr.reestr_query.value; // :) в форме reestr уже будет текст запроса
        document.search.submit();
    } else {
      
    }
}*/
// запрос подтверждения и копирование расчетов
function onArmIdRightClick2() {
    if (confirm("Скопировать расчеты по заявке \n"+
        "на все выбранные заявки?\n"+
        "(при условии, что по ним нет своего расчета)")) {
        //alert();
        //window.location="./?c=2&smr_copy="+arm_id+"&connvar="+connvar+"&reestr_query="+document.reestr.reestr_query.value;  
        addHidden(document.search,'smr_copy',current_ps_list_dop);
        //addHidden(document.search,'connvar',connvar);
        document.search.reestr_query.value=document.reestr.reestr_query.value; // :) в форме reestr уже будет текст запроса
        //document.search.action='./?c=11&cid="'+current_ps_list_dop+'"';
        //document.search.target = "_blank";
        document.search.submit();
    } else {
      
    }
}
// Удаление варианта
function onCallDelRightClick() {
    if (confirm("Удалить вариант расчета по заявке ?")) {
        addHidden(document.search,'smr_delete',current_ps_list_dop);
        document.search.submit();
    } else {
      
    }
}
// История заявки 
/*function onCallHistory_Back(sEvalRes) {
//function onCallHistory_Back(sEvalRes) {
    delete xmlHttp.psCallBackFunction; 
    var info_window_message="<b>История заявки </b><hr>";
//    alert(sEvalRes);
//    evalRes=JSON.parse(sEvalRes);
    var evalRes=eval(sEvalRes);
    for(var i=0;i<evalRes.length;i++){
        info_window_message+=evalRes[i]+'<hr>';
    }
    document.getElementById('info_window_message').innerHTML=info_window_message;
    document.getElementById("info_window_darkening").style.display = 'block';
}
function onCallHistory() {
    xmlHttp.psCallBackFunction="onCallHistory_Back";
    //nSQL("SELECT concat('\"',cp.dateedit,' ''',cs.name ,''' Цель:''',rec.name ,''' <br>Описание: ',\
    jSQL("multiselect","SELECT concat('\"',cp.dateedit,' ''',cs.name ,''' Цель:''',ifnull(rec.name, ''),''' <br>Описание: ',\
            cp.comment,'<br>',us.fio,' ',cp.checkdate,' ',ifnull(cp.shutdate,'открыт'),'\"') ch \
    FROM callpath cp \
        left join ps_status cs on cp.status=cs.id \
        left join ps_status rec on cp.recommend=rec.id \
        left join ps_users us on cp.uid=us.uid \
    where object_type=2 and lp_id='"+current_ps_list_dop+"' order by cp.id");
    return false;
}*/
function onCallHistory() {
    //xmlHttp.psCallBackFunction="onCallHistory_Back";
    //nSQL("SELECT concat('\"',cp.dateedit,' ''',cs.name ,''' Цель:''',rec.name ,''' <br>Описание: ',\
    SQL("multiselect","SELECT concat('\"Дата создания:<b>',cp.dateedit,'</b> <br>Статус:<b>''',cs.name ,'''</b> Цель:<b>''',ifnull(rec.name, ''),'''</b> <br>Описание: <b>',\
            cp.comment,'</b><br>Исполнитель:<b>',us.fio,'</b><br>Контр.срок:<b>',cp.checkdate,'</b>&nbsp&nbsp&nbsp&nbspДата закрытия:<b>',ifnull(cp.shutdate,'открыт'),'</b>\"') ch \
    FROM callpath cp \
        left join ps_status cs on cp.status=cs.id \
        left join ps_status rec on cp.recommend=rec.id \
        left join ps_users us on cp.uid=us.uid \
    where object_type=2 and lp_id='"+current_ps_list_dop+"' order by cp.id");
    //delete xmlHttp.psCallBackFunction; 
    var info_window_message="<b>История заявки </b><hr>";
    //var evalRes=eval(sEvalRes);
    for(var i=0;i<xmlHttp_responseText.length;i++){
        info_window_message+=xmlHttp_responseText[i]+'<hr>';
    }
    document.getElementById('info_window_message').innerHTML=info_window_message;
    document.getElementById("info_window_darkening").style.display = 'block';
    return false;
}
// изменение статуса заявки (call back функция)
function onNextStageCall_Back(sEvalRes) {
    delete xmlHttp.psCallBackFunction; 
    var evalRes=eval(sEvalRes);
    var next_stage_status_select='<select form="search_form_id" name="next_stage_status" >';
    for(var i=0;i<evalRes.length;i++){
        next_stage_status_select+='<option selected>'+evalRes[i][0]+'. '+evalRes[i][1]+' / '+evalRes[i][2]+'. '+evalRes[i][3]+'</option>';
    }
    next_stage_status_select+='</select>';
    document.getElementById('next_stage_status_select').innerHTML="Выберите новый статус / предложение "+next_stage_status_select; 
    document.getElementById("next_stage_darkening").style.display = 'block';
}
// изменение статуса заявки
// current_ps_list_dop - гобальная переменная содержит ps_list_dop.lid
// заполняется в oncontextmenu=' current_ps_list_dop=" . $prev_row_cid["lid"] . "; return true; '
function onNextStageCall() {
    xmlHttp.psCallBackFunction="onNextStageCall_Back";
    //alert(current_ps_list_dop+" "+current_user_uid);
    var testUGroup='';
    document.search.reestr_query.value=document.reestr.reestr_query.value; // :) в форме reestr уже будет текст запроса
    if(current_user_ugroup!=1) testUGroup=' and arb.group='+current_user_ugroup+' ';
    /*alert("SELECT concat('[',arb.targ_status,',''',ts.name,''',',arb.recommend,',''',rec.name,''',''',ifnull(arb.comment,''),''']') arbs \
        FROM arbor arb left join ps_status ts on arb.targ_status=ts.id \
        left join ps_status rec on arb.recommend=rec.id \
        where status in (SELECT status FROM ps_list_dop where lid='"+current_ps_list_dop+"') "+testUGroup+
        "order by arb.targ_status,arb.recommend");*/
    jSQL("multiselect","SELECT concat('[',arb.targ_status,',''',ts.name,''',',arb.recommend,',''',rec.name,''',''',ifnull(arb.comment,''),''']') arbs \
        FROM arbor arb left join ps_status ts on arb.targ_status=ts.id \
        left join ps_status rec on arb.recommend=rec.id \
        where status in (SELECT status FROM ps_list_dop where lid='"+current_ps_list_dop+"') "+testUGroup+
        "order by arb.targ_status,arb.recommend");
    return false;
}
//
////////////////////////////////////////////////////////////////////////////////
// 
function onAddCallToProject(call_project_id,thiselem,lLid) {
    thiselem.style.color = '#008';
    SQL("update","delete from ps_project_list where list_id in (select list_id from ps_list_dop where lid="+lLid+")");
    if(document.search.project_id.value==-1){
        if(call_project_id==-1){ 
            thiselem.textContent = '(проект не выбран)повторить';
        }else{
            thiselem.textContent = 'удалена из проекта';
        }
    } else {
        SQL("insert","insert into ps_project_list (project_id,list_id,user_id) \
                values ("+document.search.project_id.value+","+lLid+","+current_user_uid+")");
        thiselem.textContent = 'добавлена в проект';
    }
    return false;
}
//
////////////////////////////////////////////////////////////////////////////////
// групповое изменение статуса и передача заявок следующему исполнителю
function onNextStageGroup() {
    document.search.next_stage.value=1;
    onNextStageCall();
    return false;
}
</script>
<?php
echo "<tbody>";
$selectConnVarHTML=select2('select_conn_var_', "select 'выберите...',-1 union SELECT concat(id,' ',name),id FROM ps_teh_podkl","выберите...");
//
$status_for_my_job=rSQL("SELECT id FROM ps_status where ugroup=".$row_users["ugroup"])["id"]; // status доступные для работы данного пользователя
if(empty($status_for_my_job)) $status_for_my_job=-1;

if($row_users["ugroup"]!=1 and (isset($_REQUEST["for_my_job"]) or $_REQUEST["for_my_job"]=='on')){ // НЕ ИТ и выбираем только "Мои заявки"
    $test_for_my_job=" and pld.status=$status_for_my_job ";
} else
    $test_for_my_job=" "; // выбираем НЕ только "Мои заявки"
//
//if(isset($_REQUEST["mapfilter_mctet"]) and $_REQUEST["mapfilter_mctet"]!="выберите...")
//    $test_mctet=" and pl.ues_arm='".$_REQUEST["mapfilter_mctet"]."' ";
//else
    $test_mctet=" ";
//
//if(isset($_REQUEST["mapfilter_ltc"]) and $_REQUEST["mapfilter_ltc"]!="выберите...")
if(isset($_REQUEST["mapfilter_ltc"]))
    $test_ltc=" and pl.ltc like '%".$_REQUEST["mapfilter_ltc"]."%' ";
else
    $test_ltc=" ";
//
if(isset($_REQUEST["search_status"]) and $_REQUEST["search_status"]!=-1)
    $test_search_status=" and pld.status=".$_REQUEST["search_status"]." ";
else
    $test_search_status=" ";
//
if(isset($_REQUEST["project_id"]) and $_REQUEST["project_id"]!=-1)
    $test_search_project=" and prj.project_id=".$_REQUEST["project_id"]." ";
else
    $test_search_project=" ";
//
if(isset($_REQUEST["search_arm_id"]))
    $test_search_arm_id=" and pl.arm_id like '%".$_REQUEST["search_arm_id"]."%' ";
else
    $test_search_arm_id=" ";
//
if(isset($_REQUEST["search_cluster"]) and $_REQUEST["search_cluster"]!=-1)
    $test_search_cluster=" and pld.claster_id=".$_REQUEST["search_cluster"]." ";
else
    $test_search_cluster=" ";
//
if(isset($_REQUEST["mapfilter_address"]))
    $test_search_address=" and upper(pl.device_address) like upper('%".$_REQUEST["mapfilter_address"]."%') ";
else
    $test_search_address=" ";
//
if(isset($_REQUEST["mapfilter_arm_status"]) and $_REQUEST["mapfilter_arm_status"]!=-1)
    $test_search_arm_status=" and upper(pl.status_name) like upper('%" . 
        rSQL("SELECT status_name FROM ps_arm_status where as_id='".$_REQUEST["mapfilter_arm_status"]."'")["status_name"] . "%') ";
else
    $test_search_arm_status=" ";
//
if(isset($_REQUEST["search_conn_var"]) and $_REQUEST["search_conn_var"]!=-1)
    $test_search_conn_var=" and upper(pl.technology) like upper('%" . 
        rSQL("SELECT name FROM ps_teh_podkl where id='".$_REQUEST["search_conn_var"]."'")["name"] . "%') ";
else
    $test_search_conn_var=" ";
//
if(isset($_REQUEST["search_service"]) and $_REQUEST["search_service"]!="выберите...")
    $test_search_service=" and upper(pl.service) like upper('%" . $_REQUEST["search_service"] . "%') ";
else
    $test_search_service=" ";
////////////////////////////////////////////////////////////////////////////////
//
if(empty($user_LTC_list)){
    $test_user_LTC_list=" ";    
}else{
    $test_user_LTC_list=" and SUBSTRING_INDEX(pl.ltc,' ',1) in (".$user_LTC_list.") ";
}
////////////////////////////////////////////////////////////////////////////////
// Осноной запрос по заявкам
// mysql функция - ifnull(c.cname,'ни в одном кластере')
//$reestr_query="SELECT pl.*,pld.*,tp.name tp_name,s.name int_status_name,c.cname clusname,serv.sname service_name,prj.project_id project_id,
//        t.formulation tformulation,t.month_pay tmonth_pay,t.month_pay2 tmonth_pay2
//    FROM ps_list pl 
//    inner join ps_list_dop pld on pl.list_id=pld.list_id
//    left join ps_teh_podkl tp on pld.tpid=tp.id
//    left join service serv on pld.service_id=serv.id
//    left join (select list_id,project_id from ps_project_list where delete_flag=0) prj on pl.list_id=prj.list_id
//    left join ps_status s on pld.status=s.id 
//    left join cluster c on pld.claster_id=c.id 
//    left join tariff t on pld.tariff_id=t.id
//    WHERE 1=1  ".$test_user_LTC_list.$test_mctet.$test_search_status.$test_search_project.$test_for_my_job.$test_search_arm_id.$test_search_address.
//        $test_search_cluster.$test_search_arm_status.$test_search_conn_var.$test_search_service." 
//    ORDER by pl.arm_id,pl.list_id,pld.tpid";
$reestr_query="SELECT pl.*,pld.*,
        DATE_FORMAT(pld.targetdate, '%d.%m.%Y') targetdate_c,DATE_FORMAT(pld.finishdate, '%d.%m.%Y') finishdate_c,
        tp.name tp_name,s.name int_status_name,prj.project_name,c.cname clusname,serv.sname service_name,prj.project_id project_id,
        t.formulation tformulation,t.month_pay tmonth_pay,t.month_pay2 tmonth_pay2
    FROM ps_list pl 
    inner join ps_list_dop pld on pl.list_id=pld.list_id
    left join ps_teh_podkl tp on pld.tpid=tp.id
    left join service serv on pld.service_id=serv.id
    left join (select pl.list_id,pl.project_id,p.project_name from ps_project_list pl left join ps_project p using(project_id)) prj on pl.list_id=prj.list_id
    left join ps_status s on pld.status=s.id 
    left join cluster c on pld.claster_id=c.id 
    left join tariff t on pld.tariff_id=t.id
    WHERE 1=1  ".$test_user_LTC_list.$test_mctet.$test_ltc.$test_search_status.$test_search_project.$test_for_my_job.$test_search_arm_id.$test_search_address.
        $test_search_cluster.$test_search_arm_status.$test_search_conn_var.$test_search_service." 
    ORDER by pl.arm_id,pl.list_id,pld.tpid";
//    ORDER by pl.arm_id,pld.tpid";
//
//    GROUP by pl.arm_id,pld.tpid 
//    ORDER by pl.arm_id,pld.tpid";
//    
//    GROUP by pl.arm_id,pl.service,pld.tpid 
//    ORDER by pl.arm_id,pld.tpid";
//d($reestr_query);
$result_cid = qSQL($reestr_query);
//echo "<script>  var reestr_query=\"".$reestr_query."\"; </script> ";
// ^^ Осноной запрос по заявкам ^^
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
//mysql_data_seek($result_cid,0);
//$next_stage_result=''; // групповое изменение статуса и передача заявок следующему исполнителю
$rownum=0;
//$prev_row_cid=false;
$current_arm_id=-1;
$background_row_color="background: #DEF;";
while ($row_cid = mysql_fetch_array($result_cid)) {
    if($current_arm_id!=$row_cid["arm_id"]){
        if($background_row_color==" ") $background_row_color="background: #DEF;";
        else $background_row_color=" ";
        $current_arm_id=$row_cid["arm_id"];
    }
    echo "<tr style=\" ".$background_row_color." \">
        <td style=\"  \"><nobr><a href='vlg_call.php?action=call_edit&sourcepage=reestr&lid=".$row_cid["lid"]."' title='Карточка заявки'>".
            $row_cid["arm_id"] ." ". 
            (($row_cid["cs"]==2)? "ЮЛ" : "ФЛ") . "</a></nobr>
            <nobr><a href='vlg_call_prior.php?action=call_edit&sourcepage=reestr&lid=".$row_cid["lid"]."' title='Опрос по заявке'>Опрос по заявке</a></nobr>
        </td>
        <td style=\"  \">" . $row_cid["client_fio"] . "</td>
        <td style=\"  \">" . $row_cid["device_address"] . "</td>
        <!--td align='center' style=\"  \">" . ($row_cid["clusname"] ? ($row_cid["claster_id"] . "." . $row_cid["clusname"]) : "ни в одном кластере") . "</td-->";
    // Добавить эту заявку в проект
    if($row_cid["project_name"]){
        echo "<td align='center' style=\"  \">".$row_cid["project_id"] .".".$row_cid["project_name"] .
                "<br><span style='font-size: 0.8em; color: #F00; font-style: italic;' "
                . "onclick=' onAddCallToProject(".$row_cid["project_id"] .",this,".$row_cid["lid"]."); '>в выбранный проект</span> "
                . "</td>";
    } else {
        //if($_REQUEST["project_id"]==-1){
        //    echo "<td align='center' style=\"  \" title='Проект для добавления не выбран'>"."ни в одном проекте [+]</td>";            
        //} else {
        //    echo "<td align='center' style=\"  \">"."ни в одном проекте". 
        //        "<a href='reestr.php?action=call_add&sourcepage=reestr&lid=".$row_cid["lid"]."&project_id=".$_REQUEST["project_id"].
        //        "' title='Добавить эту заявку в проект'>[+]</a></td>";
        //
        echo "<td align='center' ".
                "title='Добавить эту заявку в проект'>"."ни в одном проекте "
                . "<br><span style='font-size: 0.8em; color: #0B0; font-style: italic;' "
                . "onclick=' onAddCallToProject(-1,this,".$row_cid["lid"]."); '>в выбранный проект</span> </td>";
    }
    //
    echo "<td align='center' style=\"  \">" . $row_cid["status_name"] . "</td>";
    //
    if($row_cid["date_talking"][0]=='0'){
        $date_parse_from_format_v = '';
    }else{
        $date_parse_from_format_v = (new DateTime($row_cid["date_talking"]))->format('d.m.Y');
    }
    echo "<td align='center' >" . $date_parse_from_format_v . "</td>";
    //
    echo "<td align='center' style=\"  \">" . @$row_cid["technology"] . " (" . @$row_cid["service"] . ")</td>";
    echo "<td align='center'>
            <a href='vlg_call.php?action=call_edit&sourcepage=reestr&lid=".$row_cid["lid"]."' title='Карточка заявки'
                oncontextmenu=' current_ps_list_dop=" . $row_cid["lid"] . "; return true; '
                contextmenu=\"connvarcontextmenu3\" ><b>".$row_cid["tp_name"].
                "". ($row_cid["service_name"] ? "(". $row_cid["service_name"] .")" : "") ."</b></a>";
//    echo "  <a href='' title='Добавить вариант подключения' 
//                onclick='
//                    document.new_conn_form.new_conn_form_lid.value=" . $row_cid["lid"] . ";
//                    document.getElementById(\"new_conn_darkening\").style.display = \"block\"; 
//                    return false;' 
//                oncontextmenu=' current_ps_list_dop=" . $row_cid["lid"] . "; return true; ' 
//                contextmenu=\"connvarcontextmenu\">[+]</a>
//        </td>";       
    echo "  <a href='' title='Добавить вариант подключения' 
                onclick=' ' 
                oncontextmenu=' current_ps_list_dop=" . $row_cid["lid"] . "; return true; ' 
                contextmenu=\"connvarcontextmenu\">[+]</a>
        </td>";       
    echo "<td align='center'>" . $row_cid["int_status_name"] . "</td>";
    /* версия 1
    echo "<td align='center'>".$row_cid["shkaf_42u"] ." / ".$row_cid["shassi_olt"] ." / ".$row_cid["kol_ports"] ." / ".
        $row_cid["spd"] ."</td>
        <td align='center'>".$row_cid["difficult_mc"] ." / ".$row_cid["difficult_rs"] ." / ".$row_cid["difficult_abl"] ." / ".
        $row_cid["difficult_abv"] ."</td>";
    */
    /* версия 2
    echo "<td align='center'>".$row_cid["deferredpay"] ." / ".$row_cid["install"] ." / ".
        (empty($row_cid["guarantee"]) ? "<i style='color: red'>нет</i>" : $row_cid["guarantee"])   . "</td>
        <td align='center'>" . $row_cid["tariffname"] ." / ".$row_cid["month_pay"] . "</td>                            
        <td align='center'>".$row_cid["ontfullpay"] ." / ".$row_cid["ontlease"] ."</td>
        <td align='center'>".$row_cid["routefullpay"] ." / ".$row_cid["routelease"] ."</td>";
    echo "<td align='center'>".$row_cid["attachnum"] ." / ".$row_cid["attachfullpay"] ." / ".$row_cid["attachlease"] ."</td>";
    echo "<td align='center'>" . @$row_cid["dev_summ"] . "</td>
        <td align='center'>" . @$row_cid["zatrat_smr"] . "</td>";
    */
    // версия 3
//    echo "<td align='center'>".explode(" ",$row_cid["finishdate"])[0] ."</td>
//        <td align='center'></td>";
//    echo "<td align='center'>".($row_cid["finishdate_c"]=='00.00.0000' ? ' ':$row_cid["finishdate_c"]) ."</td>";
//
//    d(date_parse_from_format('Y-m-d H:i:s', $row_cid["finishdate"]));
//    $date_parse_from_format_v = date_parse_from_format('Y-m-d H:i:s', $row_cid["finishdate"]);
//    if($date_parse_from_format_v['year']==0)
//        $date_parse_from_format_v = '';
//    else
//        $date_parse_from_format_v = $date_parse_from_format_v->format('d.m.Y');
//    echo "<td align='center'>".$date_parse_from_format_v ."</td>";
    if($row_cid["finishdate"][0]=='0'){
        $date_parse_from_format_v = '';
    }else{
        $date_parse_from_format_v = (new DateTime($row_cid["finishdate"]))->format('d.m.Y');
    }
    echo "<td align='center'>".$date_parse_from_format_v ."</td>";
//
    echo "</tr>";

    $rownum++;
} // ^^ while ($row_cid = mysql_fetch_array($result_cid)) ^^
echo "</tbody></table></div></div>";
////////////////////////////////////////////////////////////////////////////////
// статистика по проекту
/*if(isset($_REQUEST["project_id"]) and $_REQUEST["project_id"]!=-1){
    $project_statistics=rSQL("SELECT 
            sum(round(ifnull(lin.id/lin.id,0))) lincount,round(sum(ifnull(lin.price*mo.cosize,0)),2) linprice,
            sum(round(ifnull(eq.id/eq.id,0))) eqcount,round(sum(ifnull(eq.price*mo.cosize,0)),2) eqprice
        FROM map_obj mo
        left join ps_equip eq on mo.type=1 and mo.subtype=eq.id
        left join ps_smet_calc lin on mo.type=2 and mo.subtype=lin.id
        where mo.project_id=".$_REQUEST["project_id"]."");
    echo "<div id=\"project_statistics\">Проект '".
            $_REQUEST["project_id"].". ". 
            rSQL("SELECT project_name FROM ps_project WHERE project_id=".$_REQUEST["project_id"])[0] .
        "'. Затраты (по проекту): <span style='color: #A00; font-weight: 900; '>
        оборудование ".(empty($project_statistics["eqcount"]) ? 0 : $project_statistics["eqcount"])." шт. 
        на ".(empty($project_statistics["eqprice"]) ? 0 : $project_statistics["eqprice"])." руб.
        линий ".(empty($project_statistics["lincount"]) ? 0 : $project_statistics["lincount"])." шт. 
        на ".(empty($project_statistics["linprice"]) ? 0 : $project_statistics["linprice"])." руб. </span>";
    $project_statistics=rSQL("SELECT sum(pld.install)install,sum(pld.month_pay) month_pay,
            round(sum(ifnull(ec.sprice,0)),2) eqprice2,round(sum(ifnull(sc.sprice,0)),2) linprice2
        FROM ps_list_dop pld 
        left join (select list_id,project_id from ps_project_list where delete_flag=0) prl on pld.list_id=prl.list_id
        left join (select cid,sum(price*kol) sprice from ps_equip_cid where stype=1 group by cid) ec on ec.cid=pld.lid
        left join (select cid,sum(price*kol) sprice from ps_smet_cid where stype=1 group by cid) sc on sc.cid=pld.lid
        WHERE prl.project_id=".$_REQUEST["project_id"]."");
    echo "<br>Затраты (по заявкам): <span style='color: #A00; font-weight: 900; '>
        оборудование ".(empty($project_statistics["eqprice2"]) ? 0 : $project_statistics["eqprice2"])." руб. 
        линии связи ".(empty($project_statistics["linprice2"]) ? 0 : $project_statistics["linprice2"])." руб. </span>
        Доход(по заявкам): <span style='color: #0A0; font-weight: bold; '>инст.платёж ".
        (empty($project_statistics["install"]) ? 0 : $project_statistics["install"])." руб., ежемес.платёж ".
        (empty($project_statistics["month_pay"]) ? 0 : $project_statistics["month_pay"])." руб.</span></div>";
}*/
// ^^ статистика по проекту ^^
////////////////////////////////////////////////////////////////////////////////
// форма-подвал name='reestr' и контекстное меню id="connvarcontextmenu" по заявкам
echo "<form method='POST' name='reestr' action='vlg_download.php' target='_blank' accept-charset='windows-1251'>
    <input type='hidden' name='reestr_query' value=\"".$reestr_query."\">
    <input type='hidden' name='func' value=\"1\">"; 
//echo $ubord->havePrivilegeText("U900 U901 U2 U82","*",
//            "<input type='submit' value='Отчет (Excel) по заявкам'>"). 
//    $ubord->havePrivilegeText("U900 U901 U2 U82","*",
//            "<button name='phys_button' onclick=' document.reestr.func.value=\"10\"; submit(); '>Отчет (Excel) по физ.объёму заявок</button>"). 
//    $ubord->havePrivilegeText("U900 U901 U2 U82","*",
//            "<button name='phys_button' onclick=' document.reestr.func.value=\"15\"; submit(); '>Отчет (Excel) по опросу абонентов</button>"); 
echo "<input type='submit' value='Отчет (Excel) по заявкам'>". 
    "<button name='phys_button' onclick=' document.reestr.func.value=\"10\"; submit(); '>Отчет (Excel) по физ.объёму заявок</button>". 
    "<button name='phys_button' onclick=' document.reestr.func.value=\"15\"; submit(); '>Отчет (Excel) по опросу абонентов</button>"; 
echo "<button name='add_to_project_button' onclick=' return false; ' disabled=true>Добавить выбранные заявки в проект</button>
    <!--button name='next_stage_button' onclick='onNextStageButton(); return false;' disabled=true>Передать работу по заявкам далее</button-->
    </form>";
$footer_text.="<br>Количество выбранных записей ".$rownum;
// контекстное меню для колонки "вариант подключения"
//echo "<menu type=\"context\" id=\"connvarcontextmenu3\">
//	    <menuitem label=\"Скопировать расчет СМР во все выбранные заявки\" icon=\"images/143.gif\" 
//                onclick=' onArmIdRightClick2(); return false;'></menuitem>
//	    <menuitem label=\"Удалить вариант подключения\" icon=\"images/aff_cross.gif\" onclick=' onCallDelRightClick(); return false; '></menuitem>
//    </menu>";
echo "<menu type=\"context\" id=\"connvarcontextmenu3\">
	    <menuitem label=\"Скопировать расчет СМР во все выбранные заявки\" icon=\"images/143.gif\" 
                onclick=' '></menuitem>
	    <menuitem label=\"Удалить вариант подключения\" icon=\"images/aff_cross.gif\" onclick=' '></menuitem>
    </menu>";
// контекстные меню для колонки "вариант подключения" [+]
//echo "<menu type=\"context\" id=\"connvarcontextmenu\">
//	    <menuitem label=\"Изменить статус\" icon=\"images/next_small.bmp\" onclick=' onNextStageCall();' ></menuitem>
//	    <menuitem label=\"Изменить статус по всем выбранным заявкам\" icon=\"images/143.gif\" onclick='onNextStageGroup();'></menuitem>
//	    <!--menuitem label=\"Добавить выбранные заявки в проект\" icon=\"images/ok.gif\"></menuitem-->
//	    <menuitem label=\"Добавить вариант подключения\" icon=\"images/plus.gif\" onclick=' 
//                    document.new_conn_form.new_conn_form_lid.value=current_ps_list_dop;
//                    document.getElementById(\"new_conn_darkening\").style.display = \"block\"; 
//                    return false;'></menuitem>
//	    <menuitem label=\"История заявки\" icon=\"images/files/farh.jpg\" onclick='onCallHistory();'></menuitem>
//	    <menuitem label=\"Все данные по заявке в АРМ\" icon=\"images/i.gif\" onclick='onCallARM();'></menuitem>
//    </menu>";
echo "<menu type=\"context\" id=\"connvarcontextmenu\">
	    <menuitem label=\"Изменить статус\" icon=\"images/next_small.bmp\" onclick=' onNextStageCall();' ></menuitem>
	    <menuitem label=\"Изменить статус по всем выбранным заявкам\" icon=\"images/143.gif\" onclick=' '></menuitem>
	    <!--menuitem label=\"Добавить эту заявку в проект\" icon=\"images/ok.gif\"  onclick=' '></menuitem-->
	    <menuitem label=\"Добавить вариант подключения\" icon=\"images/plus.gif\" onclick=' '></menuitem>
	    <menuitem label=\"История заявки\" icon=\"images/files/farh.jpg\" onclick='onCallHistory();'></menuitem>
	    <menuitem label=\"Все данные по заявке в АРМ\" icon=\"images/i.gif\" onclick='onCallARM();'></menuitem>
    </menu>";
//echo "<menu type=\"context\" id=\"connvarcontextmenu2\">
//	    <menuitem label=\"Добавить вариант подключения\" icon=\"images/plus.gif\" onclick=' 
//                    document.new_conn_form.new_conn_form_lid.value=current_ps_list_dop;
//                    document.getElementById(\"new_conn_darkening\").style.display = \"block\"; 
//                    return false;'></menuitem>
//	    <menuitem label=\"Все данные по заявке в АРМ\" icon=\"images/i.gif\" onclick='onCallARM();'></menuitem>
//    </menu>";
echo "<menu type=\"context\" id=\"connvarcontextmenu2\">
	    <menuitem label=\"Добавить вариант подключения\" icon=\"images/plus.gif\" onclick=' '></menuitem>
	    <menuitem label=\"Все данные по заявке в АРМ\" icon=\"images/i.gif\" onclick='onCallARM();'></menuitem>
    </menu>";
$footer_text.="<br><b>Для копирования расчета на все выбранные заявки нажмите правую кнопку мыши на варианте подключения</b>";
echo "<br>".$footer_text;

/*
  echo "<tr><td align='center' style='border-left-color: black'>1</td>";
  for ($i=2;$i<=$rows_num;$i++)
  echo "<td align='center'>".$i."</td>";
  echo "</tr>";
 */
/*echo "<script type=\"text/javascript\">
    document.next_stage_form.reestr_query.value=document.reestr.reestr_query.value;
</script>";*/
$section_name = "gzp";
/*echo "<script type=\"text/javascript\">
    function checkAll(oForm, cbName, checked){
        for (var i=0; i < oForm[cbName].length; i++) 
            oForm[cbName][i].checked = checked;
    }
</script>";*/
}

