<?php
    header("Content-type: text/html; charset=windows-1251");
    session_start();
    $mode="select";
    if(isset($_REQUEST['mode']))
        $mode=$_REQUEST['mode'];
    $ques="select TRUE";
// !!! $_REQUEST['ques'] приходит в 'UTF-8', т.к. идёт через AJAX !!!   
    if(isset($_REQUEST['ques']))
        $ques=$_REQUEST['ques'];
    $result="empty";
//
    function prot($lVar) {
        $fpn='/var/www/html/cs/buffer/psProt.txt';
        $fp = fopen($fpn, 'a');  
        if($fp){
            fwrite($fp,$lVar); 
            fwrite($fp,"\n\r"); 
            fclose($fp);
        } else {
            //d("Не могу открыть файл протокола $fpn");
            //error_log(iconv('CP1251','UTF-8', "Не могу открыть файл протокола $fpn"));
            error_log("prot $lVar");
        }
    }
    //prot($ques);
    //
    $mySQLSchema='private_sector';
    $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/../private/goto.txt', 'r');
    $fpRes = fgets($fp,40);
    fclose($fp);
    // * * * mysql * * * //
    $link2 = mysql_connect("localhost","ps",$fpRes,$mySQLSchema);
    mysql_select_db($mySQLSchema) or $result="Could not select database";
    mysql_query("SET NAMES 'cp1251'",$link2);
    mysql_query("SET CHARACTER SET 'cp1251'",$link2);
    if($link2){
        switch($mode){
        case "select":
            //if(!$res=mysql_query("select count(*) cnt from ps_list",$link2)){
            if(!$res=mysql_query(iconv('UTF-8','CP1251',$ques),$link2)){
                $result=mysql_error($link2)."<br>";
            }
            if($row=mysql_fetch_array($res)){
                $result=$row[0];
            }
            echo "xmlHttp_responseText=\"".$result."\"";
        break;
        // возвращаем одну строку запроса в виде строки $key: $value<br>$key: $value...
        //
        case "select_t":
            $multires=[];
            if(!$res=mysql_query(iconv('UTF-8','CP1251',$ques),$link2)){
                $result=mysql_error($link2)."<br>";
            }
            if($row=mysql_fetch_array($res,MYSQL_ASSOC)){
                foreach ($row as $key => $value){
                    if(!empty($value)) $multires[]=$key.": <b>".$value."</b>";
                }
            }
            //echo "xmlHttp_responseText=[". iconv('UTF-8','CP1251',implode(',',$multires)) ."]";
            //echo urlencode ("xmlHttp_responseText=[". iconv('CP1251','UTF-8',implode(',',$multires)) ."]");
            echo "". implode('<br>',$multires) ."";
        break;        
        case "multiselect":
            $multires=[];
            if(!$res=mysql_query(iconv('UTF-8','CP1251',$ques),$link2)){
                $result=mysql_error($link2)."<br>";
            }
            while($row=mysql_fetch_array($res)){
                $multires[]=$row[0];
            }
            //echo "xmlHttp_responseText=[". iconv('UTF-8','CP1251',implode(',',$multires)) ."]";
            //echo urlencode ("xmlHttp_responseText=[". iconv('CP1251','UTF-8',implode(',',$multires)) ."]");
            echo "xmlHttp_responseText=[". implode(',',$multires) ."]";
        break;
        case "insert":
            if(!$res=mysql_query(iconv('UTF-8','CP1251',$ques),$link2)){
                $result=mysql_error($link2)."<br>";
            } else {
                $result=mysql_insert_id();
            }
            echo "xmlHttp_responseText=\"".$result."\"";
        break;
        case "update":
            if(!$res=mysql_query(iconv('UTF-8','CP1251',$ques),$link2)){
                $result=mysql_error($link2)."<br>";
            }
            //prot($result);
            echo "xmlHttp_responseText=\"".$result."\"";
        break;
        }
        mysql_close($link2);
    } else {
        echo "MySQL. <br>";
    }
?>




