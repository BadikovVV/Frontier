<?php
require_once 'vlg_php_header.php';
//
/*    if(isset($_REQUEST["mapfilter_address"])){
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
        setcookie ('mapfilter[project]', "Создать проект");
        $_REQUEST["mapfilter_project"]="Создать проект";
    }
//
    if(isset($_REQUEST["mapfilter_mctet"])){
        setcookie('mapfilter[mctet]', $_REQUEST["mapfilter_mctet"]);
    } elseif(isset($_COOKIE['mapfilter']['mctet'])){
        $_REQUEST["mapfilter_mctet"]=$_COOKIE['mapfilter']['mctet'];
    } else {
        setcookie ('mapfilter[mctet]', "выберите...");
        $_REQUEST["mapfilter_mctet"]="выберите...";
    }*/
    syncReqCook("mapfilter","address","");
    syncReqCook("mapfilter","project","Создать проект");
    syncReqCook("mapfilter","mctet","выберите...");
//
    if(isset($_REQUEST["project_type"])){
        setcookie('project[type]', $_REQUEST["project_type"]);
    } elseif(isset($_COOKIE['project']['type'])){
        $_REQUEST["project_type"]=$_COOKIE['project']['type'];
    } else {
        setcookie ('project[type]', "выберите...");
        $_REQUEST["project_type"]="выберите...";
    }
//
    if(isset($_REQUEST["project_subtype"])){
        setcookie('project[subtype]', $_REQUEST["project_subtype"]);
    } elseif(isset($_COOKIE['project']['subtype'])){
        $_REQUEST["project_subtype"]=$_COOKIE['project']['subtype'];
    } else {
        setcookie ('project[subtype]', "выберите...");
        $_REQUEST["project_subtype"]="выберите...";
    }
//

require_once 'func.inc.php';
require_once 'func_date.inc.php';
require_once 'vlg_util_ps.php';
require_once "vlg_project_query.php";
global $ubord;
require_once 'vlg_header.php'; // здесь начало HTML страницы
if (!defined("LOGINED")) {
    "<a href='index.php?c=4'><b>Вам необходимо авторизоваться - пройдите по этой ссылке</b></a>";
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
// изменение статуса заявки (call back функция)
/*function onNextStageCall_Back(sEvalRes) {
    delete xmlHttp.psCallBackFunction; 
    var evalRes=eval(sEvalRes);
    var next_stage_status_select='<select form="search_form_id" name="next_stage_status" >';
    for(var i=0;i<evalRes.length;i++){
        next_stage_status_select+='<option selected>'+evalRes[i][0]+'. '+evalRes[i][1]+' / '+evalRes[i][2]+'. '+evalRes[i][3]+'</option>';
    }
    next_stage_status_select+='</select>';
    document.getElementById('next_stage_status_select').innerHTML="Выберите новый статус / предложение "+next_stage_status_select; 
    document.getElementById("next_stage_darkening").style.display = 'block';
}*/
// изменение статуса заявки
// current_ps_list_dop - гобальная переменная содержит ps_list_dop.lid
// заполняется в oncontextmenu=' current_ps_list_dop=" . $prev_row_cid["lid"] . "; return true; '
/*function onNextStageCall(project_id) {
    xmlHttp.psCallBackFunction="onNextStageCall_Back";
    //alert(current_ps_list_dop+" "+current_user_uid);
    var testUGroup='';
    //document.search.reestr_query.value=document.reestr.reestr_query.value; // :) в форме reestr уже будет текст запроса
    if(current_user_ugroup!=1) testUGroup=' and arb.group='+current_user_ugroup+' ';
    sjSQL("multiselect","SELECT concat('[',arb.targ_status,',''',ts.name,''',',arb.recommend,',''',rec.name,''',''',ifnull(arb.comment,''),''']') arbs \
        FROM arbor arb left join ps_status ts on arb.targ_status=ts.id \
        left join ps_status rec on arb.recommend=rec.id \
        where status in (SELECT status FROM ps_project where project_id='"+project_id+"') "+testUGroup+
        "order by arb.targ_status,arb.recommend");
    return false;
}*/
function onNextStageCall(project_id) {
    //xmlHttp.psCallBackFunction="onNextStageCall_Back";
    var testUGroup='';
    if(current_user_ugroup!=1) testUGroup=' and arb.group='+current_user_ugroup+' ';
    SQL("multiselect","SELECT concat('[',arb.targ_status,',''',ts.name,''',',arb.recommend,',''',rec.name,''',''',ifnull(arb.comment,''),''']') arbs \
        FROM arbor arb left join ps_status ts on arb.targ_status=ts.id \
        left join ps_status rec on arb.recommend=rec.id \
        where status in (SELECT status FROM ps_project where project_id='"+project_id+"') "+testUGroup+
        "order by arb.targ_status,arb.recommend");
    //delete xmlHttp.psCallBackFunction; 
    //var evalRes=xmlHttp_responseText;
    var next_stage_status_select='<select name="next_stage_status" >';
    for(var i=0;i<xmlHttp_responseText.length;i++){
        next_stage_status_select+='<option selected>'+xmlHttp_responseText[i][0]+'. '+xmlHttp_responseText[i][1]+' / '+
                xmlHttp_responseText[i][2]+'. '+xmlHttp_responseText[i][3]+'</option>';
    }
    next_stage_status_select+='</select>';
    document.getElementById('next_stage_status_select').innerHTML="Выберите новый статус / предложение "+next_stage_status_select; 
    document.form_next_stage.next_stage_project.value=project_id;
    document.getElementById("next_stage_darkening").style.display = 'block';
    return false;
}
// групповое изменение статуса и передача заявок следующему исполнителю
function onNextStageGroup(project_id) {
    //document.search.next_stage.value=1;
    onNextStageCall(project_id);
    return false;
}
//
function onFileClick(imgSrc) {
    document.getElementById('info_window_message').innerHTML="<img src=\""+imgSrc+"\" alt=\"Пример кода\">";
    document.getElementById("info_window_darkening").style.display = 'block';
}
</script>
<?php
// всплывающее окно с выбором нового статуса и сообщения
echo "<div id='next_stage_darkening' class='ps_popup_darkening'> 
        <div id='next_stage' class='ps_popup_main_window'> 
            <a class='ps_popup_close_button' title='Закрыть' 
                onclick='document.getElementById(\"next_stage_darkening\").style.display = \"none\";'>X</a>
            <form name='form_next_stage' method='post' style='' "
                        . "action='vlg_project.php?action=status_project'>
            <b id='next_stage_status_select'></b> 
            <br><b>Выберите сотрудника</b> " . 
                    select2('next_stage_user_select', "select 'выберите...',-1 union SELECT concat(uid,'. ',fio),uid FROM ps_users","выберите...") . "
            <br><b>Дополнительное сообщение</b> <input type='text' name='next_stage_add' size='60' value=''>
            <input type='hidden' name='next_stage_project' value='0'>
            <br><input type='submit' value='Выполнить' onclick=' 
                return true; '></form>
    </div></div>";
// ^^ всплывающее окно с выбором нового статуса и сообщения ^^
////////////////////////////////////////////////////////////////////////////////
//
//$main_project_query="SELECT tech.name techname,tech.wallpayback wallpayback,pl.callnum,s.name statname,prj.* FROM ps_project prj 
//                left join ps_teh_podkl tech on prj.technology=tech.id
//                left join ps_status s on prj.status=s.id
//                left join (select project_id,count(list_id) callnum from ps_project_list group by project_id) pl using(project_id)";
//$main_project_query="SELECT if(isnull(parp.par_name),prj.project_name, concat('+-',prj.project_name)) fullname,
//        tech.name techname,tech.wallpayback wallpayback,pl.callnum,s.name statname,prj.* 
//	FROM ps_project prj 
//                left join (select project_id par_id,project_name par_name from ps_project) parp on prj.parent=parp.par_id
//                left join ps_teh_podkl tech on prj.technology=tech.id
//                left join ps_status s on prj.status=s.id
//                left join (select project_id,count(list_id) callnum from ps_project_list group by project_id) pl using(project_id)
//	order by if(isnull(parp.par_name),prj.project_name, concat(parp.par_name,' ',prj.project_name))";
//$main_project_query="SELECT if(isnull(parp.par_name),prj.project_name, concat('+-',prj.project_name)) fullname,
//        tech.name techname,tech.wallpayback wallpayback,pl.callnum,pl.b2bcallnum,s.name statname,prj.* 
//	FROM ps_project prj 
//                left join (select project_id par_id,project_name par_name from ps_project) parp on prj.parent=parp.par_id
//                left join ps_teh_podkl tech on prj.technology=tech.id
//                left join ps_status s on prj.status=s.id
//                left join (select prl.project_id,sum(pls.cs-1) b2bcallnum,count(prl.list_id) callnum 
//                    from ps_project_list prl left join ps_list pls on pls.list_id=prl.list_id
//                    group by prl.project_id) pl using(project_id)
//	order by if(isnull(parp.par_name),prj.project_name, concat(parp.par_name,' ',prj.project_name))";
//
//function projectStatistics2(){
function projectStatistics2($headerrow_cnaid="",$headerrow_cetid="",$headerrow_ceid="",$phyStatBody=[]){
    global $ubord;
// денежная статистика по проекту v.2
        $cursor=SQL("select prj.project_name,prj.project_id,prj.zatrat_smr,prj.dev_summ,prj.install,prj.month_pay,prj.deficient,prj.sname, 
            ifnull(onprj.lincount,'-') lincount,ifnull(onprj.linprice,'-') linprice,ifnull(onprj.linmanual,'-') linmanual,
            ifnull(onprj.eqcount,'-') eqcount,ifnull(onprj.eqprice,'-') eqprice,ifnull(onprj.eqmanual,'-') eqmanual,
            ifnull(oncall.callcnt,'-') callcnt,ifnull(oncall.install,'-') oncall_install,ifnull(oncall.month_pay,'-') oncall_month_pay,
            ifnull(oncall_zatrat_smr,'-') oncall_zatrat_smr,ifnull(oncall_dev_summ,'-') oncall_dev_summ,
            ifnull(ontinstall,'-') ontinstall,ifnull(ontlease,'-') ontlease,
            ifnull(routeinstall,'-') routeinstall,ifnull(routelease,'-') routelease,
            ifnull(attachinstall,'-') attachinstall,ifnull(attachlease,'-') attachlease,
            ifnull(oncall.eqprice2,'-') eqprice2,ifnull(oncall.linprice2,'-') linprice2,
            ifnull(mmstat.minstatus,'-') minstatus,ifnull(mmstat.maxstatus,'-') maxstatus
            from
            (select project_name,project_id,zatrat_smr,dev_summ,install,month_pay,deficient,status,s.name sname 
                from ps_project p left join ps_status s on p.status=s.id 
            union 
            select 'без проекта',-1,0.00,0.00,0.00,0.00,0,-1,''
            ) prj
            left join 
            (SELECT mo.project_id project_id,
                sum(round(ifnull(lin.id/lin.id,0))) lincount,
                round(sum(ifnull(lin.price*mo.cosize,0)),2) linprice,round(sum(ifnull(lin.price*mo.manual_size,0)),2) linmanual,
                sum(round(ifnull(eq.id/eq.id,0))) eqcount,
                round(sum(ifnull(eq.price*mo.cosize,0)),2) eqprice,round(sum(ifnull(eq.price*mo.manual_size,0)),2) eqmanual
                FROM map_obj mo
                left join ps_equip eq on mo.type=1 and mo.subtype=eq.id
                left join ps_smet_calc lin on mo.type=2 and mo.subtype=lin.id
                group by mo.project_id) onprj
            on prj.project_id=onprj.project_id   
            left join        
            (SELECT ifnull(prl.project_id,-1) project_id,count(pld.lid) callcnt,
                sum(pld.install)install,sum(pld.month_pay) month_pay,
                sum(pld.zatrat_smr) oncall_zatrat_smr,sum(pld.dev_summ) oncall_dev_summ,
                sum(pld.ontfullpay) ontinstall,sum(pld.ontlease) ontlease,
                sum(pld.routefullpay) routeinstall,sum(pld.routelease) routelease,
                sum(pld.attachfullpay) attachinstall,sum(pld.attachlease) attachlease,
                round(sum(ifnull(ec.sprice,0)),2) eqprice2,round(sum(ifnull(sc.sprice,0)),2) linprice2
                FROM ps_list_dop pld 
                left join (select list_id,project_id from ps_project_list where delete_flag=0) prl on pld.list_id=prl.list_id
                left join (select eqcid.cid cid,sum(eqcid.kol*eq.price) sprice 
                    from ps_equip_cid eqcid left join ps_equip eq on eqcid.pid=eq.id 
                    where eqcid.stype=1 group by eqcid.cid) ec on ec.cid=pld.lid
                left join (select smcid.cid,sum(smcid.kol*sm.price) sprice 
                    from ps_smet_cid smcid left join ps_smet_calc sm on smcid.pid=sm.id 
                    where smcid.stype=1 group by smcid.cid) sc on sc.cid=pld.lid
                group by prl.project_id) oncall     
            on prj.project_id=oncall.project_id 
            left join 
            (select project_id,min(callcnt*100000+status) minstatus,max(callcnt*100000+status) maxstatus from(
                SELECT ifnull(prl.project_id,-1) project_id,count(pld.lid) callcnt,status
                FROM ps_list_dop pld 
                left join (select list_id,project_id from ps_project_list where delete_flag=0) prl on pld.list_id=prl.list_id
                group by prl.project_id,status
                ) minmaxstat group by project_id) mmstat
            on prj.project_id=mmstat.project_id
            order by prj.project_id");
        //print_r($phyStatBody);
        // стили основной таблицы
        echo "<style type=\"text/css\">
                td.prjleftcol { color:#330; background:#FFFF63; text-align: left; }
                td.merchantheader { color:#330; background:#B7FFB7; text-align: center; }
                td.prjheader { color:#037; background:#DEF; }
            </style>";
        echo "<br><table width='100%' border='1' cellspacing='1' cellpadding='2'>";
        echo "<tr><td class='leftcol' rowspan=3>Проект</td>$headerrow_cnaid<td class='merchantheader' colspan=22 rowspan=2>Коммерческий блок</td></tr>";
        echo "<tr>$headerrow_cetid</tr>";
        echo "<tr>$headerrow_ceid";
        echo "
            <!--td align='center' class='prjleftcol'><b>Проект</b></td-->
            <td align='center' class='merchantheader'><b>СМР (доп.)</b></td>
            <td align='center' class='merchantheader'><b>Обор.(доп.)</b></td>
            <td align='center' class='merchantheader'><b>СМР</b></td>
            <td align='center' class='merchantheader'><b>Оборудование</b></td>
            <td align='center' class='merchantheader'><b>Количество заявок </b></td>
            <td align='center' class='merchantheader'><b>СМР (абон. доп.)</b></td>
            <td align='center' class='merchantheader'><b>Обор. (абон. доп.)</b></td>
            <td align='center' class='merchantheader'><b>СМР (абон.)</b></td>
            <td align='center' class='merchantheader'><b>Обор. (абон.)</b></td>
            <td align='center' class='merchantheader'><b>Cтатус проекта</b></td>
            <td align='center' class='merchantheader'><b>Инстал.(недост.)</b></td>
            <td align='center' class='merchantheader'><b>Ежемес.(недост.)</b></td>
            <td align='center' class='merchantheader'><b>Инстал.(абон.)</b></td>
            <td align='center' class='merchantheader'><b>Ежемес.(абон.)</b></td>
            <td align='center' class='merchantheader'><b>Инстал.(ONT)</b></td>
            <td align='center' class='merchantheader'><b>Ежемес.(ONT)</b></td>
            <td align='center' class='merchantheader'><b>Инстал.(Route)</b></td>
            <td align='center' class='merchantheader'><b>Ежемес.(Route)</b></td>
            <td align='center' class='merchantheader'><b>Инстал.(прист.)</b></td>
            <td align='center' class='merchantheader'><b>Ежемес.(прист.)</b></td>
            <td align='center' class='merchantheader'><b>Cтатус заявок редк./част.</b></td>
            </tr>";
        $prj_serial=1;
        while ($cursor->assoc()) {
            if($cursor->r["project_id"]==-1){
                continue;
                echo "<tr><td align='center' color=blue><b>".$cursor->r["project_name"]."</b></td>";
            } else {
            echo "<tr>
                <td align='center' class='prjleftcol'><a href='vlg_project.php?action=edit_project&project_id=" . 
                    $cursor->r["project_id"] . "' title='Редактировать'><b>".$cursor->r["project_name"]."</b></a></td>".
                    $phyStatBody[$cursor->r["project_id"]];
                $prj_serial++;
            }
            echo "
                <td align='center'>".number_format($cursor->r["zatrat_smr"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["dev_summ"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["linmanual"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["eqmanual"], 0, ',', ' ' ) ."</td>
                <td align='center'>".$cursor->r["callcnt"]."</td>
                <td align='center'>".number_format($cursor->r["oncall_zatrat_smr"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["oncall_dev_summ"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["linprice2"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["eqprice2"], 0, ',', ' ' ) ."</td>
                <td align='center'><a href='' onclick='onNextStageGroup(\"".$cursor->r["project_id"] ."\");return false;' title='Изменить статус'>".$cursor->r["sname"]."</a></td>
                <td align='center'>".number_format($cursor->r["install"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["month_pay"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["oncall_install"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["oncall_month_pay"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["ontinstall"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["ontlease"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["routeinstall"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["routelease"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["attachinstall"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["attachlease"], 0, ',', ' ' ) ."</td>
                <td align='center'>".$cursor->r["minstatus"] % 100000 ."/".$cursor->r["maxstatus"] % 100000 ."</td>
                </tr>";
        }
        echo "</table>";
        $cursor->free();
        ////////////////////////////////////////////////////////////////////////
        // форма "Отчет (Excel) по проектам"
        echo "<br><form method='POST' name='reestr' action='vlg_download.php' target='_blank'>
            <input type='hidden' name='reestr_query' value=\"".project_stat() ."\">
            <input type='hidden' name='func' value=\"11\">". 
            $ubord->havePrivilegeText("U900 U901 U2 U82","*","<input type='submit' value='Отчет (Excel) по проектам'>"). 
            //"<input type='submit' value='Отчет (Excel) по проектам'>". 
            "</form>";

}
// ^^ денежная статистика по проекту v.2 ^^
////////////////////////////////////////////////////////////////////////////////
// vv статистика по проектам v.3 vv
function projectStatistics3(){
    global $ubord;
        $cursor=SQL(project_stat());
        /*$cursor=SQL("SELECT tech.name techname,pl.callnum,s.name statname,prj.* FROM ps_project prj 
                left join ps_teh_podkl tech on prj.technology=tech.id
                left join ps_status s on prj.status=s.id
                left join (select project_id,count(list_id) callnum from ps_project_list group by project_id) pl using(project_id)");*/
        //print_r($phyStatBody);
        // стили основной таблицы
        echo "<style type=\"text/css\">
                td.prjleftcol { color:#330; background:#FFFF63; text-align: left; }
                td.merchantheader { color:#330; background:#B7FFB7; text-align: center; }
                td.prjheader { color:#037; background:#DEF; }
            </style>";
        echo "<br><table width='100%' border='1' cellspacing='1' cellpadding='2'>";
        echo "<tr>";
        echo "
            <td align='center' class='prjleftcol'><b>Наименование</b></td>
            <td align='center' class='prjleftcol'><b>X</b></td>
            <td align='center' class='merchantheader'>Статус</td>
            <td align='center' class='merchantheader'>Технология</td>
            <td align='center' class='merchantheader'>Расходы на СМР (план)</td>
            <td align='center' class='merchantheader'>Расходы на СМР (факт)</td>
            <td align='center' class='merchantheader'>Мин.(план)кол.абонентов</td>
            <td align='center' class='merchantheader'>Расходы на СМР на абонента (план)</td>
            <td align='center' class='merchantheader' title='Кол-во заявок + кол-во в подпроектах(факт). Разбивка по статусам(только ФЛ)'>Кол-во заявок (факт)</td>
            <td align='center' class='merchantheader' title='в т.ч. заявок: проект + подпроекты(факт). Разбивка по статусам(только ЮЛ)'>в т.ч. заявок ЮЛ(факт)</td>
            <td align='center' class='merchantheader'>Кол-во портов(план)</td>
            <td align='center' class='merchantheader'>Инстал.платёж</td>
            <td align='center' class='merchantheader'>Ежемес.платёж</td>
            <td align='center' class='merchantheader'>Время набора абонентов</td>
            <td align='center' class='merchantheader'>Срок окупаемости</td>
            <td align='center' class='merchantheader'>Примечание</td>
            </tr>";
        while ($cursor->assoc()) {
            if($cursor->r["project_id"]==-1){
                continue;
                echo "<tr><td align='center' color=blue><b>".$cursor->r["project_name"]."</b><br>".$cursor->r["ltcname"]."</td>";
            } else {
                echo "<tr>
                    <td align='center' class='prjleftcol'>
                        <a href='vlg_project.php?action=edit_project&project_id=" . 
                            $cursor->r["project_id"] . "' title='Редактировать'><b>".$cursor->r[/*"project_name"*/"fullname"]."</b></a><br>".$cursor->r["ltcname"]."
                    </td>
                    <td align='center' class='prjleftcol' title='Удалить' 
                        onclick=' if(confirm(\"Удалить Проект со ВСЕМИ связанными с ним объектами ?\"))
                            window.location = \"./?c=7&action=edit_project&project_id=" . $cursor->r["project_id"] . 
                                "&delete_project=true \"; '><b>X</b>
                    </td>                    
                    ".
                    $phyStatBody[$cursor->r["project_id"]];
            }
            ////////////////////////////////////////////////////////////////
            // получаем итоговые цифры проекта с учетом подпроектов
//            $subprojres=rSQL("select count(list_id) callnum from ps_project_list where project_id in (select project_id from ps_project where parent=".
//                    $cursor->r["project_id"]."  and project_id!=parent)");
            $subprojres=rSQL("select count(prl.list_id) callnum,sum(pls.cs-1) b2bcallnum 
                        from ps_project_list prl
                        left join ps_list pls on pls.list_id=prl.list_id
                where prl.project_id in (select project_id from ps_project where parent=".
                                    $cursor->r["project_id"]." and project_id!=parent)");
            //
            ////////////////////////////////////////////////////////////////
            // статистика статусов заявок проекта               
//            $cursorLid=SQL("select project_id,count(list_id) callnum,ld.status,s.name statname
//                from ps_project_list pl 
//                    left join ps_list_dop ld using(list_id)  
//                    left join ps_status s on ld.status=s.id  
//                where project_id=".$cursor->r["project_id"] ." group by project_id,ld.status,s.name order by project_id,ld.status ");
//            $cursorLid=SQL("select project_id,pls.cs,count(pl.list_id) callnum,ld.status,s.name statname
//                from ps_project_list pl 
//                    left join ps_list_dop ld using(list_id)  
//                    inner join ps_list pls on pls.list_id=ld.list_id
//                    left join ps_status s on ld.status=s.id  
//                where project_id=".$cursor->r["project_id"] ." group by project_id,pls.cs,ld.status,s.name 
//                order by project_id,ld.status ");
            $cursorLid=SQL(call_status_stat($cursor->r["project_id"]));
            
            $str_status_stat="<p style='font-size: 0.7em;'>";
            $str_status_stat2="<p style='font-size: 0.7em;'>";
            while ($cursorLid->assoc()) {
                if($cursorLid->r["cs"]==1){
                    $str_status_stat.=$cursorLid->r["statname"]." ".$cursorLid->r["callnum"]."/";
                } else {
                    $str_status_stat2.="".$cursorLid->r["statname"]." ".$cursorLid->r["callnum"]."/";
                }
            } // ^^ while ^^
            $cursorLid->free();
            $str_status_stat.="</p>";
            $str_status_stat2.="</p>";
            //
            ////////////////////////////////////////////////////////////////
            // суммируем собственный физ.объём заявок проекта               
            $cursorLid=SQL(com_object_query("1"," in (select lid from ps_list_dop pld where list_id in ".
                    "(select list_id from ps_project_list where project_id=".$cursor->r["project_id"] ."))"));
            $all_expense=0.0;
            while ($cursorLid->assoc()) {
                $expense=round($cursorLid->r["rcmprice"] 
                            * (($cursorLid->r["ccmlen"]!=0)? $cursorLid->r["ccmlen"] : 1.0) 
                            * (($cursorLid->r["ccmamount"]!=0)? $cursorLid->r["ccmamount"] : 1.0) 
                            * (($cursorLid->r["bid"]==1)? 0.7 : 1.0),2); // если "хозспособ", то *0.7
                $all_expense+=$expense;
            } // ^^ while ^^
            $cursorLid->free();
            //
            ////////////////////////////////////////////////////////////////
            // суммируем собственный физ.объём самого проекта 
            $cursorLid=SQL(com_object_query("2","=". $cursor->r["project_id"]));
            while ($cursorLid->assoc()) {
                $expense=round($cursorLid->r["rcmprice"] 
                            * (($cursorLid->r["ccmlen"]!=0)? $cursorLid->r["ccmlen"] : 1.0) 
                            * (($cursorLid->r["ccmamount"]!=0)? $cursorLid->r["ccmamount"] : 1.0) 
                            * (($cursorLid->r["bid"]==1)? 0.7 : 1.0),2); // если "хозспособ", то *0.7
                $all_expense+=$expense;
            } // ^^ while ^^
            $cursorLid->free();
            echo "
                <td align='center'><a href='' onclick='onNextStageGroup(\"".
                    $cursor->r["project_id"] ."\");return false;' title='Изменить статус'>".$cursor->r["statname"]."</a>
                </td>
                <td align='center'>".$cursor->r["techname"] ."</td>
                <td align='center'>".number_format($all_expense, 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["zatrat_smr"], 0, ',', ' ' ) ."</td>
                <td align='center'>".$cursor->r["deficient"] ."</td>
                <td align='center'>".number_format(round(($cursor->r["deficient"]!=0 ? $all_expense / $cursor->r["deficient"] : '0.0'),2) , 0, ',', ' ' ) .
                    " (".number_format($cursor->r["wallpayback"], 0, ',', '' ) .")</td>
                <td align='center'>".
                    ($cursor->r["callnum"] ? $cursor->r["callnum"] : '0') .
                    ($subprojres["callnum"]>0 ? "+".$subprojres["callnum"]."" : '') ."<br>".
                    $str_status_stat."
                </td>
                <td align='center'>".
                    ($cursor->r["b2bcallnum"] ? "".$cursor->r["b2bcallnum"] ."" : '0').
                    ($subprojres["b2bcallnum"]>0 ? "+".$subprojres["b2bcallnum"]."" : '') ."<br>".
                    $str_status_stat2."
                </td>
                <td align='center'>".$cursor->r["port_num"] ."</td>
                <td align='center'>".number_format($cursor->r["install"], 0, ',', ' ' ) ."</td>
                <td align='center'>".number_format($cursor->r["month_pay"], 0, ',', ' ' ) ."</td>
                <td align='center'>".$cursor->r["setting"] ."</td>
                <td align='center'>".$cursor->r["payback"] ."</td>
                <td align='center'>".$cursor->r["comment"] ."</td>
                </tr>";
        }
        echo "</table>";
        $cursor->free();
        ////////////////////////////////////////////////////////////////////////
        // форма "Отчет (Excel) по проектам"
        echo "<br><form method='POST' name='reestr' action='vlg_download.php' target='_blank'>
            <input type='hidden' name='reestr_query' value=\"".project_stat() ."\">
            <input type='hidden' name='func' value=\"11\">". 
            $ubord->havePrivilegeText("U900 U901 U2 U82","*","<input type='submit' value='Отчет (Excel) по проектам'>"). 
            //"<input type='submit' value='Отчет (Excel) по проектам'>". 
            "</form>";
}
// ^^ статистика по проектам v.3 ^^
////////////////////////////////////////////////////////////////////////////////
function projectStatisticsPhy2(){
// физическая статистика по проекту v.2
    $phyStatArray=[]; // массив измерений (шапка и боковина таблицы)
    $cursor=SQL("SELECT project_id,project_name FROM ps_project order by project_id");
    $phyStatLIds=[];
    $phyStatBody=[]; // тело таблицы
    while ($cursor->assoc()) {
        $phyStatBody[$cursor->r["project_id"]]="";
        //array_push($phyStatLIds,$cursor->r["project_id"]);
        $phyStatLIds[$cursor->r["project_id"]]=$cursor->r["project_name"];
    }
    $cursor->free();
    // 
    $queryText="SELECT ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
            ccm.ceid,ce.cename,ccm.bid,ccm.rcmid,rcm.name rcmname,prj.project_name,ccm.lid,
            max(ccm.ccmname) ccmname,sum(ccm.amount) ccmamount,sum(ccmlen) ccmlen,max(ccm.price) ccmprice,
            max(rcm.price) rcmprice,max(rcm.unit) rcmunit,max(rcm.capacity1) rcmcapacity1,max(rcm.capacity2) rcmcapacity2
        FROM call_com_mat ccm 
        left join ref_com_mat rcm using(rcmid)
        left join cn_eq_type cet using(cetid) 
        left join ps_project prj on ccm.stype=2 and prj.project_id=ccm.lid
        left join sign_envir se on ccm.seid=se.seid
        left join cn_area cna on ccm.cnaid=cna.cnaid
        left join cn_envir ce on ccm.ceid=ce.ceid
        where ccm.stype=2 
        group by ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,ccm.ceid,ce.cename,ccm.rcmid,rcm.name,prj.project_name,ccm.lid ";
    // запрос для формирования шапки
    $cursor=SQL($queryText." order by ccm.cnaid,ccm.seid,rcm.cetid,ccm.ceid,ccm.rcmid,ccm.lid");
    // стили основной таблицы
    echo "<style type=\"text/css\">
            td.header { color:#037; background:#DEF; text-align: center; }
            td.leftcol { color:#330; background:#FFFF63; text-align: left; }
            td.copper { color:#500; background:#FFF; text-align: center; }
            td.optics { color:#050; background:#FFF; text-align: center; }
            td.radio { color:#005; background:#FFF; text-align: center; }
        </style>";
    while ($cursor->assoc()) {
        // формируем текст ячейки
        $celltext="";
//        if(!empty($cursor->r["rcmname"])) $celltext.=$cursor->r["rcmname"]." ";
//        if(!empty($cursor->r["ccmname"])) $celltext.=$cursor->r["ccmname"]." ";
//        if(!empty($cursor->r["ccmamount"])) $celltext.= round($cursor->r["ccmamount"]) ."шт ";
//        if(!empty($cursor->r["ccmlen"])) $celltext.= round($cursor->r["ccmlen"]) ."м ";
//        if(!empty($cursor->r["rcmcapacity1"])) $celltext.= " ёмк ". round($cursor->r["rcmcapacity1"]) ." ";
//        if(!empty($cursor->r["rcmcapacity2"])) $celltext.= "x ". round($cursor->r["rcmcapacity2"]) ."м ";
        if(!empty($cursor->r["rcmname"])) $celltext.=$cursor->r["rcmname"]." ";
        if(!empty($cursor->r["ccmname"])) $celltext.=$cursor->r["ccmname"]." ";
        if(!empty($celltext)) $celltext=trim($celltext).":<b> ";
        if(!empty($cursor->r["ccmamount"])) $celltext.= round($cursor->r["ccmamount"]) ."шт ";
        if(!empty($cursor->r["ccmlen"])) $celltext.= round($cursor->r["ccmlen"]) ."м ";
        if(!empty($cursor->r["rcmcapacity1"])) $celltext.= "</b> ёмк <b>". round($cursor->r["rcmcapacity1"]) ."";
        if(!empty($cursor->r["rcmcapacity2"])) $celltext.= "x". round($cursor->r["rcmcapacity2"]) ."";
        if(!empty($celltext)) $celltext.="</b>";
        // формируем массив для заголовков
//        if(empty($cursor->r["cnaid"])) $cnaid=-1; else $cnaid=$cursor->r["cnaid"];
//        if(empty($cursor->r["seid"])) $seid=-1; else $seid=$cursor->r["seid"];
//        if(empty($cursor->r["cetid"])) $cetid=-1; else $cetid=$cursor->r["cetid"];
//        if(empty($cursor->r["ceid"])) $ceid=-1; else $ceid=$cursor->r["ceid"];
//        if(empty($cursor->r["rcmid"])) $rcmid=-1; else $rcmid=$cursor->r["rcmid"];
//        if(empty($cursor->r["lid"])) $lid=-1; else $lid=$cursor->r["lid"];
//        $phyStatArray[$cnaid][$seid][$cetid][$ceid][$rcmid][$lid]=$celltext;
        if(empty($cursor->r["cnaname"])) $cnaid=-1; else $cnaid=$cursor->r["cnaname"];
        if(empty($cursor->r["seid"])) $seid=-1; else $seid=$cursor->r["seid"];
        if(empty($cursor->r["cetname"])) $cetid=-1; else $cetid=$cursor->r["cetname"];
        if(empty($cursor->r["cename"])) $ceid=-1; else $ceid=$cursor->r["cename"];
        if(empty($cursor->r["rcmid"])) $rcmid=-1; else $rcmid=$cursor->r["rcmid"];
        if(empty($cursor->r["lid"])) $lid=-1; else $lid=$cursor->r["lid"];
        
        if($cursor->r["bid"]==1) $celltext="<td style='background:#FED;'>".$celltext."</td>";
        else $celltext="<td style='background:#FFF;'>".$celltext."</td>";
        
        
        $phyStatArray[$cnaid][$seid][$cetid][$ceid][$rcmid][$lid]=$celltext;
    }
    $cursor->free();
    //
    $headerrow_cnaid="";
    $headerrow_cetid="";
    $headerrow_ceid="";
    foreach($phyStatArray as $kcnaid => $vcnaid){
        $colcnt_cnaid=0;
        foreach($vcnaid as $kseid => $vseid){
            foreach($vseid as $kcetid => $vcetid){
                $colcnt_cetid=0;
                foreach($vcetid as $kceid => $vceid){
                    $colcnt_ceid=0;
                    foreach($vceid as $krcmid => $vrcmid){
                        //foreach($vrcmid as $klid => $vlid){
                        foreach($phyStatLIds as $klid => $vproject_name){
                        
                            if($phyStatArray[$kcnaid][$kseid][$kcetid][$kceid][$krcmid][$klid])
                                //$phyStatBody[$klid].="<td>".$phyStatArray[$kcnaid][$kseid][$kcetid][$kceid][$krcmid][$klid] ."</td>";
                                $phyStatBody[$klid].=$phyStatArray[$kcnaid][$kseid][$kcetid][$kceid][$krcmid][$klid];
                            else
                                $phyStatBody[$klid].="<td> </td>";
                        }
                        $colcnt_cnaid++;
                        $colcnt_cetid++;
                        $colcnt_ceid++;
                    }
                    if($kceid==-1)   $headerrow_ceid.="<td class='header' colspan=$colcnt_ceid></td>";
                    else   $headerrow_ceid.="<td class='header' colspan=$colcnt_ceid>$kceid</td>";
                }
                if($kcetid==-1)   $headerrow_cetid.="<td class='header' colspan=$colcnt_cetid></td>";
                else   $headerrow_cetid.="<td class='header' colspan=$colcnt_cetid>$kcetid</td>";
            }
        }
        if($kcnaid==-1)   $headerrow_cnaid.="<td class='header' colspan=$colcnt_cnaid></td>";
        else   $headerrow_cnaid.="<td class='header' colspan=$colcnt_cnaid>$kcnaid</td>";
    }
/*    echo "<br><table width='100%' border='1' cellspacing='1' cellpadding='2'>";
    echo "<tr><td class='leftcol' rowspan=3>Проект</td>$headerrow_cnaid</tr>";
    echo "<tr>$headerrow_cetid</tr>";
    echo "<tr>$headerrow_ceid</tr>";
    // формирование тела таблицы
    foreach($phyStatLIds as $klid => $vproject_name){
        echo "<tr><td class='leftcol'>$klid.$vproject_name</td>$phyStatBody[$klid]</tr>";
    }
    echo "</table>";
*/    
    projectStatistics2($headerrow_cnaid,$headerrow_cetid,$headerrow_ceid,$phyStatBody);
}
// ^^ физическая статистика по проекту v.2 ^^
////////////////////////////////////////////////////////////////////////////////
// график окупаемости по проекту
function projectPaybackSchedule($project_id){
    $horizon=60; // рассматриваемое количество месяцев
    $income=array_fill(0, $horizon, 0.0);
    $charge=array_fill(0, $horizon, 0.0);
    // просматриваем заявки
    //echo "<!-- ".$project_id." -->";
    $project_row_pb=rSQL("SELECT * FROM ps_project where project_id=".$project_id);
    $cursor=SQL("SELECT 
        pld.install install,pld.month_pay month_pay,
        pld.zatrat_smr zatrat_smr,pld.dev_summ dev_summ,
        pld.ontfullpay ontfullpay,pld.ontlease ontlease,
        pld.routefullpay routeinstall,pld.routelease routelease,
        pld.attachfullpay attachinstall,pld.attachlease attachlease,
        round(ifnull(ec.sprice,0),2) eqprice2,round(ifnull(sc.sprice,0),2) linprice2
        FROM ps_list_dop pld 
        right join (select list_id from ps_project_list where project_id=".$project_id." and delete_flag=0) prl on pld.list_id=prl.list_id
        left join (select eqcid.cid cid,sum(eqcid.kol*eq.price) sprice 
            from ps_equip_cid eqcid left join ps_equip eq on eqcid.pid=eq.id 
            where eqcid.stype=1 group by eqcid.cid) ec on ec.cid=pld.lid
        left join (select smcid.cid,sum(smcid.kol*sm.price) sprice 
            from ps_smet_cid smcid left join ps_smet_calc sm on smcid.pid=sm.id 
            where smcid.stype=1 group by smcid.cid) sc on sc.cid=pld.lid
        ");
    while ($cursor->assoc()) { // Перебор заявок
        $income[0]+=$cursor->r["install"]+$cursor->r["ontfullpay"]+$cursor->r["routeinstall"]+$cursor->r["attachinstall"];
        $charge[0]+=$cursor->r["zatrat_smr"]+$cursor->r["dev_summ"]+$cursor->r["eqprice2"]+$cursor->r["linprice2"];
        
        for($i=0;$i<$horizon;$i++){
            $income[$i]+=$cursor->r["month_pay"]+$cursor->r["ontlease"]+$cursor->r["routelease"]+$cursor->r["attachlease"];            
        }
    }
    $cursor->free();
    
    
    $charge[0]+=$project_row_pb["zatrat_smr"]+$project_row_pb["dev_summ"];
    
    //echo "<!-- "."2"." -->";
    //echo "<!-- ".$income[0]." ".$charge[0]." -->";
    // формируем представление графика окупаемости
    // стили таблицы
//    echo "<style type=\"text/css\">
//        td.prjleftcol { color:#025; background:#EEF; }
//        td.prjheader { color:#037; background:#DEF; }
//        td.prjtype1 { color:#800; }
//        td.prjtype2 { color:#080; }
//        td.prjtype3 { color:#008; }
//        </style>";
//    echo "<br><table border='1' cellspacing='1' cellpadding='2'>";
//    echo "<tr>
//        <td align='center' class='prjheader'><b>Месяц</b></td>
//        <td align='center' class='prjheader'><b>Расходы</b></td>
//        <td align='center' class='prjheader'><b>Доходы</b></td>
//        <td align='center' class='prjheader'><b>Баланс</b></td>
//        </tr>";
//    $balance=0;
//    for($i=0;$i<$horizon;$i++){
//        $balance+=$income[$i]-$charge[$i];
//        echo "<tr>
//            <td align='center' class=''>".$i ."</td>
//            <td align='center' class=''>".$charge[$i] ."</td>
//            <td align='center' class=''>".$income[$i] ."</td>
//            <td align='center' class=''>".$balance ."</td>
//            </tr>";
//    }
//    echo "</table>";
//    $cursor->free();     
    echo "<script>
        var horizon= $horizon ; // рассматриваемое количество месяцев
        var income=[". implode(',',$income) ."];
        var charge=[". implode(',',$charge) ."];
        </script>";
}
// ^^ статистика по проекту v.1 ^^
////////////////////////////////////////////////////////////////////////////////
// Работа над проектами
        switch ($_REQUEST["action"]) {
////////////////////////////////////////////////////////////////////////////////
// редактирование проекта
        case "edit_project":
            $project_row=rSQL("SELECT p.*,s.name sname FROM ps_project p left join ps_status s on p.status=s.id where project_id=".$_REQUEST["project_id"]);
            // форма добавления СМР
            if($_REQUEST["settypesmr"]=='true'){
                echo "<form name='map_obj_line_form' method='post' "
                        . "action='vlg_project.php?action=edit_project'>".
                        "<input type='hidden' name='project_id' value='" . $_REQUEST["project_id"] . "'>".
                        "<input type='hidden' name='map_obj_type' value='2'>".
			"<input type='hidden' name='add_to_project_button' value='cansel'>".
                        "<b>Тип СМР </b>".
                        "<br>Наимен.(обозн.):<input type='text' name='map_obj_equip_name' size='16' value=''>".
                        "&nbsp Размер(кол-во):<input type='text' name='map_obj_equip_size' size='16' value='1'> км<hr><table>";
                
                if($_REQUEST["project_type"]=='выберите...') $_REQUEST["project_type"]='';
                if($_REQUEST["project_subtype"]=='выберите...') $_REQUEST["project_subtype"]='';
                
                
		echo "<tr><td style='color: #000;background: #f8f0f0' onclick='map_obj_line_form.submit();'><b style='color: #900;'>Отмена</b></td></tr>";
			$map_obj_cursor=SQL("SELECT id,name,price,ed FROM ps_smet_calc 
                    where mgroup='" . $_REQUEST["project_type"] . "' and pgroup='" . $_REQUEST["project_subtype"] . "'");
                while ($map_obj_cursor->assoc()) {
                    echo "<tr><td style='color: #000;background: #f0f0f0'
                        onclick='map_obj_line_form.add_to_project_button.value=".$map_obj_cursor->r["id"]."; map_obj_line_form.submit();'>
                        <b style='color: #009;'>".$map_obj_cursor->r["name"]."</b><br>".$map_obj_cursor->r["price"].
                        " руб. за ".$map_obj_cursor->r["ed"]."</td></tr>";
                }
                $map_obj_cursor->free();
                echo '</table></form>';
            } else if($_REQUEST["settypeequip"]=='true'){
            // форма добавления оборудования
                echo "<form name='map_obj_equip_form' method='post'"
                        . "action='vlg_project.php?action=edit_project'>".
                        "<table border='1' cellspacing='1' cellpadding='2'><tr><td>".
			"<input type='hidden' name='project_id' value='" . $_REQUEST["project_id"] . "'>".
                        "<input type='hidden' name='map_obj_type' value='1'>".
			"<input type='hidden' name='add_to_project_button' value='cansel'>".
                        "<b>Тип оборудования </b>".
                        "<br>Наимен.(обозн.):<input type='text' name='map_obj_equip_name' size='16' value=''>".
                        "&nbsp Кол-во(размер):<input type='text' name='map_obj_equip_size' size='16' value='1'> шт</td></tr>";
		echo "<tr><td style='color: #000;background: #f8f0f0' onclick='map_obj_equip_form.submit();'><b style='color: #900;'>Отмена</b></td></tr>";
                $map_obj_cursor=SQL("SELECT id,name,price,ed FROM ps_equip");
                while ($map_obj_cursor->assoc()) {
                    echo "<tr><td style='color: #000;background: #f0f0f0;'
			onclick='map_obj_equip_form.add_to_project_button.value=".$map_obj_cursor->r["id"]."; map_obj_equip_form.submit();'>
			<b style='color: #009;'>".$map_obj_cursor->r["name"]."</b><br>".$map_obj_cursor->r["price"].
                        " руб. за ".$map_obj_cursor->r["ed"]."</td></tr>";
                }
                $map_obj_cursor->free();
                echo '</table></form>';
            } else if($_REQUEST["addcommobject"]=='true'){
            // форма добавления объекта связи
                $sel_cnaname=new CSelect("SELECT 'выберите...',-1 union select cnaname,cnaid FROM cn_area", "map_obj_cn_area", -1, "comm_network_area");
                $sel_cename=new CSelect("SELECT 'выберите...',-1 union select cename,ceid FROM cn_envir", "map_obj_cn_envir", -1, "comm_network_envir");
                //echo "<!-- ".$cnaname->htmlel ." -->";
                echo "<form name='comm_obj_form' method='post'"
                        . "action='vlg_project.php?action=edit_project'>".
                        "<table border='1' cellspacing='1' cellpadding='2'><tr><td colspan=8 >".
			"<input type='hidden' name='project_id' value='" . $_REQUEST["project_id"] . "'>".
                        "<input type='hidden' name='map_obj_type' value='4'>".
			"<input type='hidden' name='add_to_project_button' value='cansel'>".
                        "<b>Тип оборудования </b>".
                        "<br>Наимен.(обозн.):<input type='text' name='map_obj_equip_name' size='16' value=''>".
                        "&nbsp Кол-во:<input type='text' name='map_obj_equip_amount' size='16' value='1'> шт".
                        "&nbsp Размер:<input type='text' name='map_obj_equip_size' size='16' value='1'> м".
                        "<br>Участок:".$sel_cnaname->htmlel ."&nbsp Прокладка:".$sel_cename->htmlel."</td></tr>";
		echo "<tr><td colspan=8 style='color: #000;background: #f8f0f0' onclick='comm_obj_form.submit();'>
                        <b style='color: #900;'>Отмена</b></td></tr>";
                echo "<tr>
                    <td align='center' class='prjheader'><b>Участок</b></td>
                    <td align='center' class='prjheader'><b>Сигнал</b></td>
                    <td align='center' class='prjheader'><b>Тип объекта</b></td>
                    <td align='center' class='prjheader'><b>Прокладка</b></td>
                    <td align='center' class='prjheader'><b>Имя/емк.</b></td>
                    <td align='center' class='prjheader'><b>Уд.стоимость</b></td>
                    <td align='center' class='prjheader'><b>Единица</b></td>
                    <td align='center' class='prjheader'><b>Примечание</b></td>
                    </tr>";
                $cursor=SQL("SELECT rcm.cetid,cet.cetname,rcm.rcmid,rcm.name rcmname,rcm.comment,
                        rcm.price rcmprice,rcm.unit rcmunit,rcm.capacity1 rcmcapacity1,rcm.capacity2 rcmcapacity2,
                        rcm.cnaid,cna.cnaname,rcm.seid,se.sename,rcm.ceid,ce.cename
                    FROM ref_com_mat rcm 
                    left join cn_eq_type cet using(cetid) 
                    left join sign_envir se on rcm.seid=se.seid
                    left join cn_area cna on rcm.cnaid=cna.cnaid
                    left join cn_envir ce on rcm.ceid=ce.ceid
                    order by rcm.cetid,rcm.name");
                while ($cursor->assoc()) {
                    echo "<tr>
                        <td align='center' class=''>".$cursor->r["cnaname"]."</td>
                        <td align='center' class=''>".$cursor->r["sename"]."</td>
                        <td align='center' class=''>".$cursor->r["cetname"]."</td>
                        <td align='center' class=''>".$cursor->r["cename"]."</td>
                        ";
                    $celltext="";
                    if(!empty($cursor->r["rcmname"])) $celltext.=$cursor->r["rcmname"]." ";
                    if(!empty($celltext)) $celltext=trim($celltext);
                    if(!empty($cursor->r["rcmcapacity1"])) $celltext.= " / ". round($cursor->r["rcmcapacity1"]) ."";
                    if(!empty($cursor->r["rcmcapacity2"])) $celltext.= "x". round($cursor->r["rcmcapacity2"]) ."";
                    echo"<td align='center' style='color: #000;background: #f0f0f0;'
			onclick='comm_obj_form.add_to_project_button.value=".$cursor->r["rcmid"]."; comm_obj_form.submit();'>
			<b style='color: #009;'>".$celltext."</b></td>
                        <td align='center' class=''>".$cursor->r["rcmprice"]." руб.</td>
                        <td align='center' class=''>".$cursor->r["rcmunit"]."</td>
                        <td align='center' class=''>".$cursor->r["comment"]."</td>
                        </tr>";
                }
                $cursor->free();
                echo '</table></form>';
            ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////             
            } else if($_REQUEST["addcommobject_cot"]=='true'){
            // форма добавления объекта связи (map_obj_type=4)
                echo "<form name='comm_obj_form' id='comm_obj_form_id' method='post' action='vlg_project.php?action=edit_project'>".
			"<input type='hidden' name='project_id' value='" . $_REQUEST["project_id"] . "'>".
                        "<input type='hidden' name='map_obj_type' value='4'>".
			"<input type='hidden' name='add_to_project_button' value='cansel'>".
			"<input type='hidden' name='add_object_id' value='cansel'>".
                        "<table border='1' cellspacing='1' cellpadding='2'><tr><td id='sel_for_level_cont' colspan=9 >".
                        "<span class='emtextblue' >Помощь в выборе oбъекта</span><br>";
		echo "<tr><td colspan=3 style='color: #000;background: #f8f0f0' onclick='comm_obj_form.submit();'>
                        <span class='emtextred'>Отмена</span></td>
                    <td id='sel_for_level_new' colspan=3 style='color: #000;background: #f0f8f0' 
                        onclick=' addHidden(document.comm_obj_form, \"addcommobject_cot\", true); document.comm_obj_form.submit(); '>
                        <span class='emtextgreen'>Начать снова</span></td>
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
////////////////////////////////////////////////////////////////////////////////             
            ////////////////////////////////////////////////////////////////////
            } else if($_REQUEST["edit_map_obj"]=='true'){
            // форма редактирования СМР и оборудования
                $map_obj_row=rSQL("SELECT * FROM map_obj where id=".$_REQUEST["map_obj_id"]);
                echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>Редактирование СМР и оборудования</b>&nbsp;</legend>";
                echo "<form name='form_map_obj' method='post' style='' "
                        . "action='vlg_project.php?action=edit_project&project_id=" . $_REQUEST["project_id"] 
                        . "&save_map_obj=true&map_obj_id=" . $_REQUEST["map_obj_id"] . "'>
                    Наименование(обозначение):(".$map_obj_row["id"] .")<input type='text' name='oname' size='40' value='".$map_obj_row["oname"] ."'><br>
                    Примечание:<input type='text' name='comment' size='80' value='".$map_obj_row["comment"] ."'><br>
                    Количество/размер:<input type='text' name='manual_size' size='20' value='".$map_obj_row["manual_size"] ."'><br><br>
                    <input type='submit' value='Сохранить изменения'> 
                    <input type='button' onclick=' window.location = \"vlg_project.php?action=edit_project&project_id=" 
                        . $_REQUEST["project_id"] . "\"; ' value='Вернуться'/>        
                </form></fieldset>";
            } else if($_REQUEST["edit_comm_obj"]=='true'){
            // форма редактирования объекта связи
                $cursor=rSQL("SELECT ccmid,ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.bid,ccm.rcmid,rcm.name rcmname,prj.project_name,ccm.lid,
                    ccm.ccmname ccmname,ccm.amount ccmamount,ccmlen ccmlen,ccm.price ccmprice,ccm.comment,
                    rcm.price rcmprice,rcm.unit rcmunit,rcm.capacity1 rcmcapacity1,rcm.capacity2 rcmcapacity2
                FROM call_com_mat ccm 
                left join ref_com_mat rcm using(rcmid)
                left join cn_eq_type cet using(cetid) 
                left join ps_project prj on ccm.stype=2 and prj.project_id=ccm.lid
                left join sign_envir se on ccm.seid=se.seid
                left join cn_area cna on ccm.cnaid=cna.cnaid
                left join cn_envir ce on ccm.ceid=ce.ceid
                where ccm.stype=2 and ccmid=".$_REQUEST["map_obj_id"]);
                $sel_cnaname=new CSelect("SELECT 'выберите...',-1 union select cnaname,cnaid FROM cn_area", "map_obj_cn_area", $cursor["cnaid"], "comm_network_area");
                $sel_cename=new CSelect("SELECT 'выберите...',-1 union select cename,ceid FROM cn_envir", "map_obj_cn_envir", $cursor["ceid"], "comm_network_envir");
                $sel_builder=new CSelect("SELECT 'выберите...',-1 union select bname,bid FROM builder", "map_obj_builder", $cursor["bid"], "comm_network_builder");
                echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>Редактирование объекта связи</b>&nbsp;</legend>";
                echo "<form name='comm_obj_form' method='post' style='' "
                        . "action='vlg_project.php?action=edit_project&project_id=".$_REQUEST["project_id"] 
                        . "&save_comm_obj=true&map_obj_id=".$_REQUEST["map_obj_id"] . "'>
                    Наименование(обозначение):(".$cursor["ccmid"] .")<input type='text' name='ccmname' size='40' value='".$cursor["ccmname"] ."'><br>
                    Примечание:<input type='text' name='comment' size='80' value='".$cursor["comment"] ."'><br>
                    Кол-во:<input type='text' name='map_obj_equip_amount' size='16' value='".$cursor["ccmamount"] ."'> шт<br>
                    Размер:<input type='text' name='map_obj_equip_size' size='16' value='".$cursor["ccmlen"] ."'> м<br>
                    <br>Участок:".$sel_cnaname->htmlel ."&nbsp Прокладка:".$sel_cename->htmlel."&nbsp Строитель:".$sel_builder->htmlel."<br><br>
                    <input type='submit' value='Сохранить изменения'> 
                    <input type='button' onclick=' window.location = \"vlg_project.php?action=edit_project&project_id=" 
                        . $_REQUEST["project_id"] . "\"; ' value='Вернуться'/>        
                </form></fieldset>";
            } else {
            // редактирование самого проекта
                // HTML-форма выбор типа работ проекта
                $project_type_HTML="<b>[Тип]/[подтип] работ проекта</b><table>";
                $project_type_cursor=SQL("SELECT concat('<tr><td onclick=\' setTypeSubtype(\\\\\"',mgroup,'\\\\\",\\\\\"',pgroup,'\\\\\");  \' style=\'color: #000;background: #eee\'><b style=\'color: #009;\'>',
                    mgroup,'</b><br>',pgroup,'</td></tr>') sc
                    FROM ps_smet_calc group by mgroup,pgroup order by mgroup,pgroup");
		$project_type_HTML.= "<tr><td style='color: #000;background: #f8f0f0' onclick=' setTypeSubtype(\\\"cansel\\\",\\\"cansel\\\"); '><b style='color: #900;'>Отмена</b></td></tr>";
                while ($project_type_cursor->assoc()) {
                    $project_type_HTML.=$project_type_cursor->r["sc"];
                }
                $project_type_cursor->free();
                $project_type_HTML.='</table>';
                /*  вернуться назад
                <!-- вариант с кнопкой -->
                <input type="button" onclick="history.back();" value="Назад"/>
                <!-- вариант ссылкой -->
                <a onclick="javascript:history.back(); return false;">Назад</a>
                */
                ////////////////////////////////////////////////////////////////
                // форма редактирования самого проекта
                echo "<table><tr><td >";
                echo "<fieldset style='padding: 20px; border-color: darkgray;'>";
                echo "<legend>&nbsp;<b style='color: #006;'>Редактирование проекта</b>&nbsp;</legend>";
                echo "<form name='new_project' method='post' enctype='multipart/form-data' style='' "
                        . "action='vlg_project.php?action=save_project&project_id=" . $_REQUEST["project_id"] . "'>
                    <nobr>Наименование проекта:<input type='text' name='project_name' size='40' value='".$project_row["project_name"] ."'>&nbsp</nobr>
                    <nobr>Статус проекта:<b>
                        <!--input type='text' name='status' size='40' value='".$project_row["status"] ."'-->
                            \"".$project_row["sname"] ."\"
                        </b></nobr><br>";
                // Головной проект
                $sel_parent=new CSelect("SELECT 'выберите...',-1 union (select project_name,project_id FROM ps_project order by project_name)", 
                        "parent_project_id", $project_row["parent"], "parent_project_id");
                echo "Головной проект:".$sel_parent->htmlel ."<br>";
                //
                
                echo "Технология:";
                if($project_row["technology"]!=-1)
                    echo select2('technology', "select 'выберите...',-1 union SELECT concat(id,'. ',name),id FROM ps_teh_podkl",
                            rSQL("SELECT concat(id,'. ',name) rstr FROM ps_teh_podkl where id='".$project_row["technology"] ."'")["rstr"]);
                else
                    echo select2('technology', "select 'выберите...',-1 union SELECT concat(id,'. ',name),id FROM ps_teh_podkl","выберите...");
                echo "<br>
                    <nobr>Расходы на СМР (факт):<input type='text' name='zatrat_smr' size='40' value='".$project_row["zatrat_smr"] ."'>&nbsp</nobr>
                    <!--nobr>Расходы на оборудование (факт):<input type='text' name='dev_summ' size='40' value='".$project_row["dev_summ"] ."'></nobr-->
                    <nobr>Расходы на СМР (план):<input type='text' name='zatrat_smr_plan' size='40' value='zatrat_smr_plan' disabled></nobr><br>
                    <nobr>Кол-во портов(план):<input type='text' name='port_num' size='10' value='".$project_row["port_num"] ."'></nobr><br>
                    <!--nobr onclick='onTypeButtonClick(3);return false;'>Тип/подтип работ:[".
                        $project_row["project_type"] ."]/[".$project_row["project_subtype"] ."]</nobr--> 
                    <nobr>Мин.кол.абонентов:<input type='text' name='deficient' size='16' value='".$project_row["deficient"] ."'>шт&nbsp</nobr>
                    <nobr>Инстал.платёж:<input type='text' name='install' size='16' value='".$project_row["install"] ."'>руб.&nbsp</nobr>
                    <nobr>Ежемес.платёж:<input type='text' name='month_pay' size='16' value='".$project_row["month_pay"] ."'>руб</nobr><br>
                    <nobr>Время набора абонентов:<input type='text' name='setting' size='16' value='".$project_row["setting"] ."'>мес&nbsp</nobr>
                    <nobr>Срок окупаемости:<input type='text' name='payback' size='16' value='".$project_row["payback"] ."'>мес<br></nobr><br>
                    <div style='display:inline-block; float: right'>Примечание:<textarea name='comment' rows='4' cols='100'>".
                        $project_row["comment"] ."</textarea></div><br>
                    <img src='./images/save.gif' align='absmiddle'> Загрузка граф.схемы(выберите файл)
                        <input type='file' name='drawing[]' id='drawing' multiple> 
                    <!--a href=' ' onclick='onFileClick(\"uploads/" . "13" . "/" . "snimok.JPG" . "\"); return false; ' >
                        <img src='./images/save2.gif' align='absmiddle'> </a-->
                    <a href='vlg_image.php?otype=1&oid=".$_REQUEST["project_id"] ."' target='_blank'>
                        <img src='./images/search.gif' align='absmiddle'> Показать схему</a>&nbsp&nbsp&nbsp       
                    <img src='./images/aff_cross.gif' align='absmiddle'> Удалить схему<input type='checkbox' name='delschema' value='true'><br><br>                            
                    <input type='submit' value='Сохранить изменения' ".$ubord->mayIdo("button")
                        ." > <input type='button' onclick=' window.location = \"vlg_project.php\"; ' value='Вернуться'/>        
                </form></fieldset>";
                echo "</td><td >";
                ////////////////////////////////////////////////////////////////
                // vvv сводка по заявкам проекта vvv
                $cursor=SQL("select case when pl.cs='ДА' then 'ЧС' else pl.cs end cs,pl.technology,pl.service,count(*) cnt 
                    FROM ps_list_dop pld left join ps_list pl using(list_id)
                    left join (select list_id,project_id from ps_project_list) prl on pld.list_id=prl.list_id
                    where project_id=".$_REQUEST["project_id"]."
                    group by pl.cs,pl.technology,pl.service order by pl.cs,pl.technology,pl.service");
                // стили таблицы
                echo "<style type=\"text/css\">
                        td.prjleftcol { color:#025; background:#EEF; }
                        td.prjheader { color:#037; background:#DEF; }
                        td.prjtype1 { color:#800; }
                        td.prjtype2 { color:#080; }
                        td.prjtype3 { color:#008; }
                    </style>";
                echo "<br><table border='1' cellspacing='1' cellpadding='2'>";
                echo "<tr>
                    <td align='center' class='prjheader'><b>Тип абонента</b></td>
                    <td align='center' class='prjheader'><b>Технология</b></td>
                    <td align='center' class='prjheader'><b>Услуга</b></td>
                    <td align='center' class='prjheader'><b>Количество</b></td>
                    </tr>";
                while ($cursor->assoc()) {
                    echo "<tr>
                        <td align='center' class=''>".(($cursor->r["cs"]==1) ? "ФЛ" : "ЮЛ") ."</td>
                        <td align='center' class=''>".$cursor->r["technology"]."</td>
                        <td align='center' class=''>".$cursor->r["service"]."</td>
                        <td align='center' class=''>".$cursor->r["cnt"]."</td>
                        </tr>";
                }
                echo "</table>";
                $cursor->free();             
                // ^^^ сводка по заявкам проекта ^^^
                echo "<canvas id=\"paybackSchedule\" style=\" width:3px; height:3px \"></canvas>";
                echo "<br><a href='' onclick=' paybackSchedule(); return false; '>График окупаемости</a>";
                //projectPaybackSchedule($_REQUEST["project_id"]);
                //
                echo "<br><form name='tocallEditform' method='post' action='vlg_call.php?action=call_edit&sourcepage=project&project_id=" . 
                        $_REQUEST["project_id"] . "' title='Карточка заявки'>
                    <input type='text' name='arm_id' value=''>
                    <input type='submit' value='Перейти к заявке'>
                    </form>";
                echo "<br><a href='./?c=6&action=prjcallload&project_id=".$_REQUEST["project_id"]."'>[Загрузка заявок по данному проекту]</a>";
                echo "</td></tr></table>";
                projectPaybackSchedule($_REQUEST["project_id"]);        
                ////////////////////////////////////////////////////////////////
                // vv выполнить до запросов для отображения измененных данных vv
                // добавление в таблицу СМР и оборудования
                if(isset($_REQUEST["add_to_project_button"])){
                    if($_REQUEST["add_to_project_button"]=='cansel'){

                    }else{
                        switch($_REQUEST["map_obj_type"]){
                        case 1:
                        case 2:
                        case 3:
                            SQL("INSERT INTO map_obj(id,oname,type,latlng,subtype,uid,dateedit,comment,project_id,morphe,manual_size)
                                VALUES (NULL,'". $_REQUEST["map_obj_equip_name"] ."',".$_REQUEST["map_obj_type"].",'',".
                                    $_REQUEST["add_to_project_button"].",'". $row_users["uid"] ."',now(),'','". 
                                    $_REQUEST["project_id"] ."',1,'". $_REQUEST["map_obj_equip_size"] ."')") -> commit();
                        break;
                        case 4:
                            $arr_ref_com_mat=explode("__",$_REQUEST["map_obj_ref_com_mat"]);
                            if(isset($_REQUEST["map_obj_cn_area"])) true;
                            else $_REQUEST["map_obj_cn_area"]="0__-1";
                            if(count($arr_ref_com_mat)>1){
                                SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,dateedit,comment,map_obj,ccmname,
                                        technology,bid,eid,oid,oname,cxid,cxcomment,subeid,capacity1,capacity2)
                                    VALUES (2,".$_REQUEST["project_id"] .",".$_REQUEST["add_to_project_button"] .",".
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
                            SQL("INSERT INTO call_com_mat (stype,lid,rcmid,cnaid,seid,ceid,amount,ccmlen,price,uid,comment,map_obj,ccmname)
                                VALUES (2,".$_REQUEST["project_id"] .",".$_REQUEST["add_to_project_button"] .",".
                                    $_REQUEST["map_obj_cn_area"] .",20,".$_REQUEST["map_obj_cn_envir"] .",'". 
                                    $_REQUEST["map_obj_equip_amount"] ."','".$_REQUEST["map_obj_equip_size"] ."',0.0,'". 
                                    $row_users["uid"] ."','',NULL,'".$_REQUEST["map_obj_equip_name"] ."')") -> commit();
                            }
                        break;
                        }
                    }
                }
                // сохранение в таблице СМР и оборудования
                if($_REQUEST["save_map_obj"]=='true'){
                    SQL("update map_obj set oname='".$_REQUEST["oname"]."',comment='".$_REQUEST["comment"].
                            "',manual_size='".$_REQUEST["manual_size"]."'
                        where id=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // сохранение в таблице объектов связи
                if($_REQUEST["save_comm_obj"]=='true'){
                    SQL("update call_com_mat set ccmname='".$_REQUEST["ccmname"]."',comment='".$_REQUEST["comment"].
                            "',amount='".$_REQUEST["map_obj_equip_amount"]."',ccmlen='".$_REQUEST["map_obj_equip_size"].
                            "',cnaid=".$_REQUEST["map_obj_cn_area"].",ceid=".$_REQUEST["map_obj_cn_envir"].",bid=".$_REQUEST["map_obj_builder"]."
                        where ccmid=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // удаление из таблицы СМР и оборудования
                if($_REQUEST["delete_map_obj"]=='true'){
                    SQL("delete from map_obj where id=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // удаление из таблицы объектов связи
                if($_REQUEST["delete_comm_obj"]=='true'){
                    SQL("delete from call_com_mat where ccmid=".$_REQUEST["map_obj_id"]) -> commit();
                }
                // ^^ выполнить до запросов для отображения измененных данных ^^
                ////////////////////////////////////////////////////////////////
                // редактирование объектов связи
                echo "<div style='float: left; display:inline-block;color: #006;'><b>Редактирование объектов связи</b></div>";
                echo "<div style='display:inline-block; float: right'>";
                echo "<button name='add_line_to_project_button' onclick=' window.location=\"vlg_project.php?action=edit_project&project_id=".
                    $_REQUEST["project_id"] ."&addcommobject=true\"; '>Добавить oбъект</button>";
                echo "<button name='add_line_to_project_button' onclick=' window.location=\"vlg_project.php?action=edit_project&project_id=".
                    $_REQUEST["project_id"] ."&addcommobject_cot=true&cot_vertex=1\"; '>Помощь в выборе oбъекта</button>";
                echo "</div>";
                // запрос и html таблица объектов связи
                $cursor=SQL("SELECT ccmid,ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.bid,b.bname,ccm.rcmid,rcm.name rcmname,prj.project_name,ccm.lid,
                    max(ccm.ccmname) ccmname,sum(ccm.amount) ccmamount,sum(ccmlen) ccmlen,max(ccm.price) ccmprice,max(ccm.comment) comment,
                    max(rcm.price) rcmprice,max(rcm.unit) rcmunit,max(rcm.capacity1) rcmcapacity1,max(rcm.capacity2) rcmcapacity2
                FROM call_com_mat ccm 
                left join ref_com_mat rcm using(rcmid)
                left join cn_eq_type cet using(cetid) 
                left join ps_project prj on ccm.stype=2 and prj.project_id=ccm.lid
                left join sign_envir se on ccm.seid=se.seid
                left join cn_area cna on ccm.cnaid=cna.cnaid
                left join cn_envir ce on ccm.ceid=ce.ceid
                left join builder b on ccm.bid=b.bid
                where ccm.stype=2 and ccm.lid=".$_REQUEST["project_id"] ." 
                group by ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.rcmid,rcm.name,prj.project_name,ccm.lid,ccmid
		order by ccm.cnaid,ccm.seid,rcm.cetid,ccm.ceid,ccm.rcmid,ccm.lid");
                // стили таблицы
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
                    <td align='center' class='prjheader'><b>Участок</b></td>
                    <td align='center' class='prjheader'><b>Сигнал</b></td>
                    <td align='center' class='prjheader'><b>Тип объекта</b></td>
                    <td align='center' class='prjheader'><b>Прокладка</b></td>
                    <td align='center' class='prjheader'><b>Строитель</b></td>
                    <td align='center' class='prjheader'><b>Имя/емк.</b></td>
                    <td align='center' class='prjheader'><b>Кол-во/размер</b></td>
                    <td align='center' class='prjheader'><b>*уд.стоим.=стоимость</b></td>
                    <td align='center' class='prjheader'><b>Единица</b></td>
                    <td align='center' class='prjheader'><b>Примечание</b></td>
                    </tr>";
                while ($cursor->assoc()) {
                    echo "<tr>
                        <td align='center' class='prjleftcol'>
                            <a href='vlg_project.php?action=edit_project&project_id=".$_REQUEST["project_id"] . 
                            "&edit_comm_obj=true&map_obj_id=".$cursor->r["ccmid"] ."' title='Редактировать'>
                            <img src='./images/edit.gif' align='absmiddle'></a></td>
                        <td align='center'  title='Удалить' onclick=' if(confirm(\"Удалить объект связи?\"))
                                    window.location = \"vlg_project.php?action=edit_project&project_id=".$_REQUEST["project_id"] . 
                            "&delete_comm_obj=true&map_obj_id=".$cursor->r["ccmid"] ."\"; '><b>X</b></td>";
                    echo "<td align='center' class=''>".$cursor->r["cnaname"]."</td>
                        <td align='center' class=''>".$cursor->r["sename"]."</td>
                        <td align='center' class=''>".$cursor->r["cetname"]."</td>
                        <td align='center' class=''>".$cursor->r["cename"]."</td>
                        <td align='center' class=''>".$cursor->r["bname"]."</td>";
                    $celltext="";
                    if(!empty($cursor->r["rcmname"])) $celltext.=$cursor->r["rcmname"]." ";
                    if(!empty($cursor->r["ccmname"])) $celltext.=$cursor->r["ccmname"]." ";
                    if(!empty($celltext)) $celltext=trim($celltext);
                    if(!empty($cursor->r["rcmcapacity1"])) $celltext.= " / ". round($cursor->r["rcmcapacity1"]) ."";
                    if(!empty($cursor->r["rcmcapacity2"])) $celltext.= "x". round($cursor->r["rcmcapacity2"]) ."";
                    echo"<td align='center' class=''>".$celltext."</td>";
                    $celltext="<b>";
                    if(!empty($cursor->r["ccmamount"])) $celltext.= round($cursor->r["ccmamount"]) ." шт ";
                    if(!empty($cursor->r["ccmlen"])) $celltext.= round($cursor->r["ccmlen"]) ." м ";
                    if(!empty($celltext)) $celltext.="</b>";
                    echo "<td align='center' class=''>".$celltext."</td>
                        <td align='center' class=''>"."* ".$cursor->r["rcmprice"]." = <b>".
                            $cursor->r["rcmprice"] * (($cursor->r["ccmlen"]!=0)? $cursor->r["ccmlen"] : 1.0) 
                                * (($cursor->r["ccmamount"]!=0)? $cursor->r["ccmamount"] : 1.0) ."</b> руб.</td>
                        <td align='center' class=''>".$cursor->r["rcmunit"]."</td>
                        <td align='center' class=''>".$cursor->r["comment"]."</td>
                        </tr>";
                }
                echo "</table>";
                $cursor->free();
                ////////////////////////////////////////////////////////////////
                // Редактирование СМР и оборудования
                /*echo "<br><div style='float: left; display:inline-block;color: #006;'><b>Редактирование СМР и оборудования</b></div>
                    <div style='display:inline-block; float: right'>";
                //$ubord->button("name='add_line_to_project_button' onclick='onTypeButtonClick(2);return false;'","Добавить СМР в проект");
                //$ubord->button("name='add_line_to_project_button' onclick='onTypeButtonClick(1);return false;'","Добавить оборудование в проект");
                //$ubord->button("name='add_line_to_project_button' onclick=' window.location=\"vlg_project.php?action=edit_project&project_id=". 
                //    $_REQUEST["project_id"] ."&settypesmr=true&project_type=\"+document.getElementById('project_type').innerHTML+\" \"; '","Добавить СМР в проект");
                $ubord->button("name='add_line_to_project_button' onclick=' toAddSMR(". $_REQUEST["project_id"] ."); '","Добавить СМР в проект");
                $ubord->button("name='add_line_to_project_button' onclick=' window.location=\"vlg_project.php?action=edit_project&project_id=". 
                    $_REQUEST["project_id"] ."&settypeequip=true\"; '","Добавить оборудование в проект");
                echo "</div>";
                //echo "<nobr onclick='onTypeButtonClick(3);return false;'>Тип/подтип работ: [<div id='project_type'>".
                //        $_REQUEST["project_type"] ."</div>]/[<div id='project_subtype'>".$_REQUEST["project_subtype"] ."</div>]</nobr>";
                echo "<br><b onclick='onTypeButtonClick(3);return false;' style='color: #006;'>
                    Тип работ: <b id='project_type' style='color: #060;'>".$_REQUEST["project_type"]."</b> 
                    Подтип: <b id='project_subtype' style='color: #060;'>".$_REQUEST["project_subtype"]."</b></b>";
                // запрос и html таблица СМР и оборудования
                $cursor=SQL("SELECT mo.*,
                        case when type=1 then eq.price when type=2 then line.price when type=3 then area.price else null end objpr,
                        case when type=1 then eq.pgroup when type=2 then line.pgroup when type=3 then area.pgroup else null end objgr,
                        case when type=1 then eq.name when type=2 then line.name when type=3 then area.name else null end objname
                    FROM map_obj mo 
                        left join ps_equip eq on mo.type=1 and mo.subtype=eq.id 
                        left join ps_smet_calc line on mo.type=2 and mo.subtype=line.id 
                        left join ps_smet_calc area on mo.type=3 and mo.subtype=area.id where mo.project_id=".$_REQUEST["project_id"].
                    " order by mo.type,mo.id");
                // стили таблицы
                echo "<style type=\"text/css\">
                        td.prjleftcol { color:#025; background:#EEF; }
                        td.prjheader { color:#037; background:#DEF; }
                        td.prjtype1 { color:#800; }
                        td.prjtype2 { color:#080; }
                        td.prjtype3 { color:#008; }
                    </style>";
                echo "<br><table width='100%' border='1' cellspacing='1' cellpadding='2'>";
                echo "<tr>
                    <td align='center' class='prjleftcol'><b>Наименование</b></td>
                    <td align='center' class='prjheader'><b></b></td>
                    <td align='center' class='prjheader'><b>Тип (группа)</b></td>
                    <td align='center' class='prjheader'><b>Тип</b></td>
                    <td align='center' class='prjheader'><b>Размер/ручн.</b></td>
                    <td align='center' class='prjheader'><b>*уд.стоим.=стоимость</b></td>
                    <td align='center' class='prjheader'><b>Примечание</b></td>
                    </tr>";
                while ($cursor->assoc()) {
                    if($cursor->r["type"]==3){ // объект-район в проекте
                        echo "<tr>
                            <td align='center' class='prjleftcol'>
                                <a href='vlg_project.php?action=edit_project&project_id=" . $cursor->r["project_id"] . 
                                "&edit_map_obj=true&map_obj_id=" . $cursor->r["id"] . "' title='Редактировать'>(".
                                $cursor->r["id"].")<b>".$cursor->r["oname"]."</b></a></td>
                            <td align='center'><b> </b></td>";
                    }else{
                        echo "<tr>
                            <td align='center' class='prjleftcol'>
                                <a href='vlg_project.php?action=edit_project&project_id=" . $cursor->r["project_id"] . 
                                "&edit_map_obj=true&map_obj_id=" . $cursor->r["id"] . "' title='Редактировать'>(".
                                $cursor->r["id"].")<b>".$cursor->r["oname"]."</b></a></td>
                            <td align='center'  title='Удалить' onclick=' if(confirm(\"Удалить СМР/оборудование?\"))
                                        window.location = \"vlg_project.php?action=edit_project&project_id=" . $cursor->r["project_id"] . 
                                "&delete_map_obj=true&map_obj_id=" . $cursor->r["id"] . "\"; '><b>X</b></td>";
                    }
                    echo "<td align='center' class='prjtype".$cursor->r["type"]."'>".$cursor->r["objgr"]."</td>
                        <td align='center' class='prjtype".$cursor->r["type"]."'>".$cursor->r["objname"]."</td>
                        <td align='center' class='prjtype".$cursor->r["type"]."'>".$cursor->r["cosize"]."/".$cursor->r["manual_size"] ."</td>
                        <td align='center' class='prjtype".$cursor->r["type"]."'>*".$cursor->r["objpr"]."=". $cursor->r["objpr"]*$cursor->r["manual_size"] ."</td>    
                        <td align='center' class='prjtype".$cursor->r["type"]."'>".$cursor->r["comment"]."</td>
                        </tr>";
                }
                echo "</table>";
                $cursor->free();*/
            }
        break;
        ////////////////////////////////////////////////////////////////////////
        // сохранить изменения по проекту 
        case "save_project":
            SQL("UPDATE ps_project SET
                project_name = '".$_REQUEST["project_name"]."',user_id = '".$row_users["uid"]."',
                zatrat_smr = '".$_REQUEST["zatrat_smr"]."',
                dev_summ = '".$_REQUEST["dev_summ"]."',payback = '".($_REQUEST["payback"] ? $_REQUEST["payback"] : 0) ."',
                comment = '".$_REQUEST["comment"]."',install = '".$_REQUEST["install"]."',
                month_pay = '".$_REQUEST["month_pay"]."',deficient = '".$_REQUEST["deficient"]."',
                project_type = '',project_subtype = '',parent=".$_REQUEST["parent_project_id"].",
                technology = '".$_REQUEST["technology"]."',service_id = 0,port_num = '".$_REQUEST["port_num"]."',
                setting = '". ($_REQUEST["setting"] ? $_REQUEST["setting"] : 0) ."',dateinsert=now()
                WHERE project_id = " . $_REQUEST["project_id"] . "") -> commit();
//                project_type = '".$_REQUEST["project_type"]."',project_subtype = '".$_REQUEST["project_subtype"]."',
            // удаление графической схемы
            if($_REQUEST["delschema"]=='true'){
                $result_del=SQL("DELETE FROM blobs WHERE bid=(select blob_id from files where otype=1 and oid=".$_REQUEST["project_id"] .")");
                $result_del=$result_del->affected_rows();
                SQL("DELETE FROM files where otype=1 and oid=".$_REQUEST["project_id"] ."")->commit();    
            }
            // загрузка графической схемы (возможно несколько файлов)
            if($_FILES['drawing']) { // СХЕМА
                for ($i = 0; $i < count($_FILES['drawing']); $i++) {
                    if ($_FILES['drawing']['tmp_name'][$i] == '')
                        continue;
                    // Поиск или создания каталога загрузки
                    // /var/www/html/uploads/
                    //$targetFolder = $file_path . "uploads/" . $_REQUEST["project_id"]; // Relative to the root
                    //echo "<br>" . $targetFolder;
                    //if (file_exists($targetFolder) == FALSE) { // Нет директории
                    //    mkdir($targetFolder, 0777);
                    //}
                    $tempFile = $_FILES['drawing']['tmp_name'][$i];
                    // Читаем содержимое файла
                    $image = file_get_contents( $tempFile );
                    // Экранируем специальные символы в содержимом файла
                    $image = $mysqli->real_escape_string( $image );
                    // Формируем запрос на добавление файла в базу данных
                    $result_id=SQL("INSERT INTO blobs (bcontent,uid) VALUES('".$image ."',".$row_users['uid'] .")");
                    $result_id=$result_id->insert_id();
                    SQL("INSERT INTO files values(NULL,'проект','схема','','".$_FILES['drawing']['name'][$i] ."',now(),'".
                        $row_users["uid"] ."',NULL,1,".$_REQUEST["project_id"] .",".$result_id .")")->commit();                    
                    //$targetPath = $targetFolder;
                    // Проверка на наличие файла с тем же именем
                    /*for ($g = 1; $g < 500; $g++) {
                        if ($g == 1)
                            $change_fname = $_FILES['drawing']['name'][$i];
                        $result_ftest = qSQL("SELECT * FROM ps_files WHERE section='" . $_REQUEST["project_id"] . "' and file_name='" . $change_fname . "'");
                        if ($result_ftest->num_rows >= 1) {
                            // Переименовываем файл, текущее имя файла не допустимо
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
            ////////////////////////////////////////////////////////////////////
            //projectStatistics2(); // статистика по проекту
            projectStatistics3(); // статистика по проекту
        break;
        /////////////////////////////////////////////////////////////////////////
        // изменение статуса проекта
        case "status_project":
            $next_stage_status2=explode(" / ",$_REQUEST["next_stage_status"]);
            $next_stage_status2_0=explode(". ",$next_stage_status2[0]);
            $next_stage_status2_1=explode(". ",$next_stage_status2[1]); // может быть NULL
            $next_stage_checkhour=rSQL("SELECT checkhour FROM ps_status where id=".$next_stage_status2_0[0]."")["checkhour"];
            /*qSQL("set autocommit=0");
            qSQL("begin");
            if(getReq("next_stage")==1){
            // групповое изменение статуса и передача заявок следующему исполнителю
                $next_stage_result=''; // групповое изменение статуса и передача заявок следующему исполнителю
                $rownum=0;
                $result_cid = qSQL(getReq("reestr_query"));
                while ($row_cid = $result_cid->fetch_array()) {
                    //$next_stage_result.=' '.$row_cid["lid"];
                    qSQL("UPDATE ps_list_dop SET status=". $next_stage_status2_0[0] ." WHERE lid='". $row_cid["lid"] ."'"); // меняем статус
                    qSQL("update callpath set shutdate=now() WHERE object_type=2 and lp_id='". $row_cid["lid"] ."' and shutdate is null"); // закрываем этап
                    qSQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment,issent,filename,recommend,nextcallpath,shutdate)
                        VALUES(NULL,'". $row_cid["lid"] ."','". $next_stage_status2_0[0] ."',". 
                            $row_users['uid'] .",-1,DATE_ADD(now(),interval ".$next_stage_checkhour." hour),2,'".getReq("next_stage_add")."',0,NULL,".$next_stage_status2_1[0].",NULL,NULL)");
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
                qSQL("UPDATE ps_list_dop SET status=". $next_stage_status2_0[0] ." WHERE lid='". $_REQUEST["search_lid"] ."'"); // меняем статус
                qSQL("update callpath set shutdate=now() WHERE object_type=2 and lp_id='". $_REQUEST["search_lid"] ."' and shutdate is null"); // закрываем этап
                qSQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment,issent,filename,recommend,nextcallpath,shutdate)
                    VALUES(NULL,'". $_REQUEST["search_lid"] ."','". $next_stage_status2_0[0] ."',". 
                        $row_users['uid'] .",-1,DATE_ADD(now(),interval ".$next_stage_checkhour." hour),2,'".getReq("next_stage_add")."',0,NULL,".$next_stage_status2_1[0].",NULL,NULL)");
            }
            qSQL("commit");  */          
            $cursor=SQL("UPDATE ps_list_dop pl left join ps_project_list prj on pl.list_id=prj.list_id
                SET pl.status='".$next_stage_status2_0[0] ."' where prj.project_id='".$_REQUEST["next_stage_project"] ."'"); 
            $cursor->sql("UPDATE ps_project SET status = '".$next_stage_status2_0[0] ."',dateinsert=now()
                WHERE project_id = " . $_REQUEST["next_stage_project"] . "");
            $cursor->sql("update callpath set shutdate=now() WHERE object_type=3 and lp_id='". 
                    $_REQUEST["next_stage_project"] ."' and shutdate is null"); // закрываем этап           
            $cursor->sql("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment,issent,filename,recommend,nextcallpath,shutdate)
                    VALUES(NULL,'". $_REQUEST["next_stage_project"] ."','". $next_stage_status2_0[0] ."',". 
                        $row_users['uid'] .",".$row_users['ugroup'].",DATE_ADD(now(),interval ".$next_stage_checkhour." hour),3,'".
                        getReq("next_stage_add")."',0,NULL,".$next_stage_status2_1[0].",NULL,NULL)");
            $cursor->commit();
            //projectStatistics2(); // статистика по проекту
            projectStatistics3(); // статистика по проекту
        break;
        ////////////////////////////////////////////////////////////////////////
        default :
            //projectStatisticsPhy2(); // статистика по проекту
            //projectStatistics2();
            projectStatistics3(); // статистика по проекту
        break;
        }        
////////////////////////////////////////////////////////////////////////////////
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
            // лист дерева   
                $('#sel_for_level_cont').append(
                        "<br>Наимен.(обозн.):<input type='text' name='map_obj_equip_name' size='16' value=''>"+
                        "&nbsp Кол-во:<input type='text' name='map_obj_equip_amount' size='16' value='1'> шт"+
                        "&nbsp Размер:<input type='text' name='map_obj_equip_size' size='16' value='1'> км"+
                        "<br>Емкость Х:<input type='text' name='map_obj_capacity1' size='16' value='1'> "+
                        "&nbsp Емкость Y:<input type='text' name='map_obj_capacity2' size='16' value='1'> "+
                        "<br>Примечание:<input type='text' name='comment' size='80' value=''>"
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
                $('#sel_for_level_add').html("<span class='emtextgreen'>Добавить объект</span>");
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
            // продолжаем
                SQL("select","select fid from forest where pid="+forestid+" and childtype="+child[0]+" and childid="+child[1]);
                //alert(xmlHttp_responseText);
                //var cursor=SQL("jsonselect","select concat('[',fid,',',pid,',',childtype,',',childid,',',type,']') ro from forest where pid="+xmlHttp_responseText);
                
                var newSelect=onCOTLevelChangeSel(xmlHttp_responseText);
                if(newSelect==-1){ // ветвь обломлена
                    alert("Недопустимая комбинация значений!");
                } else {
                    //$(thiselem).prop('disabled',true); // не передаётся через $_REQUEST
                    $(thiselem).prop('readonly',true);
                    $('#sel_for_level_cont').append(newSelect);
                    
        //            $('[name="sel_for_level'+forestid+'"]').after(onCOTLevelChangeSel(xmlHttp_responseText));
                }
            }
        }        
    }
    function onCOTLevelChangeSel(forestPid){
        var cursor=SQL("jsonselect","SELECT concat('[',f.fid,',',f.pid,',',f.childtype,',',f.childid,',',f.type,',\"', "+
                "    case when f.childtype=1 then 'технология' \
                        when f.childtype=2 then 'вид затрат' \
                        when f.childtype=4 then 'подвид затрат' \
                        when f.childtype=3 then 'участок' \
                        when f.childtype=7 then 'тип прокладки' \
                        when f.childtype=9 then 'объект связи' \
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
        // ветвление продолжается
            //alert('map_obj_'+preload_dictable[cursor[0][2]][0]);
            //var after_sel_for_level='<select name="sel_for_level';
            var after_sel_for_level='<span class=\'emtextgreen\'> '+cursor[0][5]+'</span>'+'<select name="'+'map_obj_'+preload_dictable[cursor[0][2]][0];
//            if(forestPid==-1)
//                after_sel_for_level+='0'+'"  onchange="onCOTLevelChange(this,'+forestPid+');" >';
//            else
//                after_sel_for_level+=''+forestPid+'"  onchange="onCOTLevelChange(this,'+forestPid+');" >';
            after_sel_for_level+='"  onchange="onCOTLevelChange(this,'+forestPid+');" >';            
            //<span class='emtextgreen'></span>
            //after_sel_for_level+=' <option value="-1" selected style="color: #f00;">('+cursor[0][5]+'...)не выбран</option>';
            after_sel_for_level+=' <option value="-1" selected style="color: #f00;">'+'не выбран</option>';
            cursor.forEach(function(item, i, cursor) {
              //alert( i + ": " + item + " (массив:" + cursor + ")" );
              //after_sel_for_level+='<option value="'+item[2]+'__'+item[3]+'__'+item[4]+'"  >('+item[5]+') '+item[6]+'</option>';
              after_sel_for_level+='<option value="'+item[2]+'__'+item[3]+'__'+item[4]+'"  > '+item[6]+'</option>';
            });
            after_sel_for_level+='</select>';

            return after_sel_for_level;
        } else {
        // ветвь обломлена
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
            alert("Не выбран объект связи");
        }
    }
    //
    ////////////////////////////////////////////////////////////////////////////
    // выбор типа работ проекта или типа оборудования или СМР
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
    // установка текущих типа/подтипа работ
    function setTypeSubtype(mgroup,pgroup){
				if(mgroup=='cansel'){
				}else{
					document.getElementById('project_type').innerHTML=mgroup;
					document.getElementById('project_subtype').innerHTML=pgroup;
				}
        document.getElementById("info_window_darkening").style.display = 'none';
    }
    // формирование ссылки для добавления СМР
    function toAddSMR(project_id){
        window.location="vlg_project.php?action=edit_project&project_id="+project_id+
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
    // рисуем диаграмму для графика окупаемости
        function paybackSchedule(){
            var canvas = document.getElementById('paybackSchedule');
            canvas.style.width=300;
            canvas.style.height=300;
            var context = canvas.getContext('2d');
            context.scale(1,1);
            canvas.height = 300;
            canvas.width = 300;
            //horizon=; // рассматриваемое количество месяцев
            //income=[];
            //charge=[];
            maxIncome=Math.max.apply(null, income);
            maxCharge=Math.max.apply(null, charge);
            maxBalance=Math.max(maxIncome,maxCharge);
            balance=0.0;
            for($i=0;$i<horizon;$i++){
                //alert(income[$i]+" "+maxIncome+" "+charge[$i]+" "+maxCharge);
                context.fillStyle = "rgba(100, 0, 0, 0.5)";
                context.fillRect($i*5, 150-(charge[$i]*100.0/maxCharge), 4, (charge[$i]*100.0/maxCharge));
                context.fillStyle = "rgba(0, 0, 100, 0.5)";
                context.fillRect($i*5, 150, 4, (income[$i]*100.0/maxIncome));
                context.fillStyle = "rgba(0, 100, 0, 0.5)";
                balance+=income[$i]-charge[$i];
                if(balance<0)   context.fillRect($i*5, 150+(balance*100.0/maxIncome), 4, -(balance*100.0/maxIncome));                
                else   context.fillRect($i*5, 150, 4, (balance*100.0/maxIncome));                
                
            }
            // рисуем осевые линии
            context.fillStyle = "black"; 
            context.lineWidth = 1; 
            context.beginPath(); 
            context.moveTo(0,150); 
            context.lineTo(300,150); 
            //context.lineTo(490,460); 
            context.stroke();
            context.strokeStyle = "rgba(150, 150, 0, 1)"; 
            for(var i=0; i<6; i++) { 
              //context.fillText((5-i)*20 + "",4, i*40+60); 
              context.beginPath(); 
              context.moveTo(i*60,5); 
              context.lineTo(i*60,295); 
              context.stroke(); 
            }            
            // рисуем текст и вертикальные линии
//            context.fillStyle = "black"; 
//            for(var i=0; i<6; i++) { 
//              context.fillText((5-i)*20 + "",4, i*40+60); 
//              context.beginPath(); 
//              context.moveTo(25,i*40+60); 
//              context.lineTo(30,i*40+60); 
//              context.stroke(); 
//            }

//            var labels = ["JAN","FEB","MAR","APR","MAY"]; 
//            // выводим текст
//            for(var i=0; i<5; i++) { 
//              c.fillText(labels[i], 50+ i*100, 475); 
//            }
            //context.fillStyle = "rgb(0, 0, 100)";
            //context.font = '12px "Arial"';
            //context.fillText("200 руб.", 50, 150);
        };
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
