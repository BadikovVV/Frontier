<script>
////////////////////////////////////////////////////////////////////////////////
// общий код со спецификой 
////////////////////////////////////////////////////////////////////////////////
// Все данные по заявке из АРМ
// current_ps_list_dop - гобальная переменная содержит ps_list_dop.lid
/*function onCallARM_Back(evalRes) {
    delete xmlHttp.psCallBackFunction; 
    var info_window_message="<b>Все данные по заявке из АРМ </b><hr>";
    //alert(evalRes);
    info_window_message+=evalRes;

    document.getElementById('info_window_message').innerHTML=info_window_message;
    document.getElementById("info_window_darkening").style.display = 'block';
}
function onCallARM() {
    xmlHttp.psCallBackFunction="onCallARM_Back";
    //alert(current_ps_list_dop);
    jSQL("select_t","SELECT pl.*,pld.* FROM ps_list pl inner join ps_list_dop pld on pl.list_id=pld.list_id where pld.lid='"+current_ps_list_dop+"' ");
    return false;
}*/
function onCallARM() {
    //xmlHttp.psCallBackFunction="onCallARM_Back";
    //alert(current_ps_list_dop);
    SQL("select_t","SELECT pl.*,pld.* FROM ps_list pl inner join ps_list_dop pld on pl.list_id=pld.list_id where pld.lid='"+current_ps_list_dop+"' ");
    //delete xmlHttp.psCallBackFunction; 
    var info_window_message="<b>Все данные по заявке из АРМ </b><hr>";
    //alert(evalRes);
    info_window_message+=xmlHttp.responseText;
    document.getElementById('info_window_message').innerHTML=info_window_message;
    document.getElementById("info_window_darkening").style.display = 'block';
    return false;
}
</script>
<?php
////////////////////////////////////////////////////////////////////////////////
//
    function showStatusFrontier(){
        $cursor=SQL("SELECT s.*,g.name gname FROM ps_status s left join ggroup g on s.ugroup=g.id order by s.id");
        echo "<b>Статусы заявок в данной ИС</b>";
        // стили таблицы
        echo "<style type=\"text/css\">
                td.prjleftcol { color:#025; background:#CDE; }
                td.prjheader { color:#037; background:#DEF; }
            </style>";
        echo "<br><table border='1' cellspacing='1' cellpadding='2'>";
        echo "<tr>
            <td align='center' class='prjleftcol'><b>Номер</b></td>
            <td align='center' class='prjheader'><b>Наименование</b></td>
            <td align='center' class='prjheader'><b>Комментарии</b></td>
            <td align='center' class='prjheader'><b>Исполнитель</b></td>
            <td align='center' class='prjheader'><b>Контрольный срок</b></td>
            </tr>";
        //while ($ps_status=$ps_status_res->fetch_assoc()) {
        while ($cursor->assoc()) {
            echo "<tr>
                <td align='center' class='prjleftcol'><b>".$cursor->r["id"]."</b></td>
                <td align='left'>".$cursor->r["name"]."</td>
                <td align='left'>".$cursor->r["info"]."</td>
                <td align='center'>".$cursor->r["gname"]."</td>
                <td align='center'>". ($cursor->r["checkhour"]=='9999999' ? 'неограничено' : $cursor->r["checkhour"]/24 .' дн. ' ) ."</td>
                </tr>";
        }
        echo "</table>";
        $cursor->free();
    }
//
////////////////////////////////////////////////////////////////////////////////
//
    function showMapStatus(){
        $cursor=SQL("SELECT ast.*,st.id,st.name FROM ps_arm_status ast left join ps_status st on ast.ps_status=st.id");
        echo "<hr><b>Отображение статуса 'АРМ -> данная ИС'</b>";
        // стили таблицы
        echo "<style type=\"text/css\">
                td.prjleftcol { color:#025; background:#CDE; }
                td.prjheader { color:#037; background:#DEF; }
            </style>";
        echo "<br><table border='1' cellspacing='1' cellpadding='2'>";
        echo "<tr>
            <td align='center' class='prjleftcol'><b>Номер (АРМ)</b></td>
            <td align='center' class='prjheader'><b>Статус (АРМ)</b></td>
            <td align='center' class='prjheader'><b>Номер</b></td>
            <td align='center' class='prjheader'><b>Статус</b></td>
            </tr>";
        while ($cursor->assoc()) {
            echo "<tr>
                <td align='center' class='prjleftcol'><b>".$cursor->r["as_id"]."</b></td>
                <td align='left'>".$cursor->r["status_name"]."</td>
                <td align='center'>".$cursor->r["id"]."</td>
                <td align='left'>".$cursor->r["name"]."</td>
                </tr>";
        }
        echo "</table>";
        $cursor->free();
    }
//
////////////////////////////////////////////////////////////////////////////////
//
    function showStatusRoute(){
        $cursor=SQL("SELECT a.*,st1.name st1,st2.name st2,st3.name st3,g.name gname FROM arbor a left join ps_status st1 on a.status=st1.id
            left join ps_status st2 on a.targ_status=st2.id left join ps_status st3 on a.recommend=st3.id
            left join ggroup g on a.group=g.id order by a.status,a.targ_status");
        echo "<hr><b>Изменение статусов в данной ИС</b>";
        // стили таблицы
        echo "<style type=\"text/css\">
                td.prjleftcol { color:#025; background:#CDE; }
                td.prjheader { color:#037; background:#DEF; }
            </style>";
        echo "<br><table border='1' cellspacing='1' cellpadding='2'>";
        echo "<tr>
            <td align='center' class='prjleftcol'><b>Номер</b></td>
            <td align='center' class='prjheader'><b>Статус</b></td>
            <td align='center' class='prjheader'><b>Цель</b></td>
            <td align='center' class='prjheader'><b>Конечная цель</b></td>
            <td align='center' class='prjheader'><b>Исполнитель</b></td>
            </tr>";
        while ($cursor->assoc()) {
            echo "<tr>
                <td align='center' class='prjleftcol'><b>".$cursor->r["status"]."</b></td>
                <td align='left'>".$cursor->r["st1"]."</td>
                <td align='left'>".$cursor->r["st2"]."</td>
                <td align='center'>".$cursor->r["st3"]."</td>
                <td align='center'>".$cursor->r["gname"]."</td>
                </tr>";
        }
        echo "</table>";
        $cursor->free();
    }
////////////////////////////////////////////////////////////////////////////////
//
class CBord{
    var $user_id,$user_group,$user_role;
//
    function __construct($user_id,$user_group,$user_role)
    {
        $this->user_id=$user_id;
        $this->user_group=$user_group;
        $this->user_role=$user_role;
    }
//
    function havePrivilege($subjectBord,$actionBord){
        $privres=false;
        //echo "<!-- $subjectBord,$actionBord,".  "G".$this->user_group  ." -->";
        if(empty($subjectBord) or $subjectBord=="*") $privres=true;
        elseif(strpos($subjectBord, "!G")!==false){ 
            if(strpos($subjectBord, "!G".$this->user_group )!==false) $privres=false;
            else $privres=true;
        }
        elseif(strpos($subjectBord, "G".$this->user_group )!==false) $privres=true;
        elseif(strpos($subjectBord, "U".$this->user_id )!==false) $privres=true;
        
        return $privres;
    }    
//
    function havePrivilegeText($subjectBord,$actionBord,$outhtml){
        //echo "<!-- $subjectBord,$actionBord,$outhtml -->";
        if($this->havePrivilege($subjectBord,$actionBord)) return $outhtml;
        else return " ";
    }
//
    function button($option,$content){
        echo "<button ".$option." ".$this->mayIdo("button") .">".$content."</button>";
        return " ";
    }
    //mayIam("button",row_user["uid"],row_user["ugroup"],row_user["rid"])
    function mayIdo($html_obj){

        switch($html_obj){
        case "button":
            if($this->user_role=='8'){
                return " disabled=true ";
            }
        break;
        }
        return " ";
    }    
}
?>