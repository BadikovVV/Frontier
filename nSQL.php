<?php
// попытка использовать JSON - пока не идёт - не используйте win1251!!!
    //header("Content-type: text/html; charset=windows-1251");
    header("Content-type: text/html; charset=UTF-8");
    session_start();
// вывод сообщения в протокол
// необхлдимы права на соответствующую директорию
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
    $ques="select TRUE";
// !!! $_REQUEST['ques'] приходит в 'UTF-8', т.к. идёт через AJAX !!!
// сейчас iconv('UTF-8','CP1251',$ques) вызывается только в case "multiselect"     
    if(isset($_REQUEST['ques']))
        $ques=$_REQUEST['ques'];
    $result="empty";
    $mySQLSchema='private_sector';
    $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/../private/goto.txt', 'r');
    $fpRes = fgets($fp,40);
    fclose($fp);
    // * * * mysql * * * //
    $link2 = mysql_connect("localhost","ps",$fpRes,$mySQLSchema);
    mysql_select_db($mySQLSchema) or $result="Could not select database";
    //mysql_query("SET NAMES 'cp1251'",$link2);
    //mysql_query("SET CHARACTER SET 'cp1251'",$link2);
    //mysql_query("SET NAMES 'UTF-8'",$link2);
    //mysql_query("SET CHARACTER SET 'UTF-8'",$link2);
    if($link2){
            $multires=[];
            if(!$res=mysql_query($ques,$link2)){
                $result=mysql_error($link2)."<br>";
            }
            while($row=mysql_fetch_array($res)){
                $multires[]=$row;
            }
            //echo "xmlHttp_responseText=[". iconv('UTF-8','CP1251',implode(',',$multires)) ."]";
            //echo urlencode ("xmlHttp_responseText=[". iconv('CP1251','UTF-8',implode(',',$multires)) ."]");
            //prot($multires);
            //prot(json_encode($multires));
            echo iconv('UTF-8','CP1251',json_encode($multires));
        mysql_close($link2);
    } else {
        echo "MySQL. <br>";
    }
?>




