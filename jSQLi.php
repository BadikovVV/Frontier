<?php
// AJAX сурвер по доступу к MySQL через библиотеку mysqli
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
    $link2 = new mysqli('localhost','ps',$fpRes,$mySQLSchema);
    if ($link2->connect_error) {
        error_log('Connect Error (' . $link2->connect_errno . ') ' . $link2->connect_error);
        exit("mysqli connect error");
    }
    $link2->autocommit(FALSE);
    $link2->set_charset("cp1251");
    /*$link2 = mysql_connect("localhost","ps",$fpRes,$mySQLSchema);
    mysql_select_db($mySQLSchema) or $result="Could not select database";
    mysql_query("SET NAMES 'cp1251'",$link2);
    mysql_query("SET CHARACTER SET 'cp1251'",$link2);*/
    if($link2){
        switch($mode){
        case "select":
            if(!$res=$link2->query(iconv('UTF-8','CP1251',$ques),MYSQLI_STORE_RESULT)){
                $result=$link2->error."<br>";
            }
            if($row=$res->fetch_row()){
                $result=$row[0];
            }
            $res->free();
            echo "xmlHttp_responseText=\"".$result."\"";
        break;
        // возвращаем одну строку запроса в виде строки $key: $value<br>$key: $value...
        //
        case "select_t":
            $multires=[];
            if(!$res=$link2->query(iconv('UTF-8','CP1251',$ques),MYSQLI_STORE_RESULT)){
                $result=$link2->error."<br>";
            }
            if($row=$res->fetch_assoc()){
                foreach ($row as $key => $value){
                    if(!empty($value)) $multires[]=$key.": <b>".$value."</b>";
                }
            }
            $res->free();
            //echo "xmlHttp_responseText=[". iconv('UTF-8','CP1251',implode(',',$multires)) ."]";
            //echo urlencode ("xmlHttp_responseText=[". iconv('CP1251','UTF-8',implode(',',$multires)) ."]");
            echo "". implode('<br>',$multires) ."";
        break;        
        case "multiselect":
            $multires=[];
            if(!$res=$link2->query(iconv('UTF-8','CP1251',$ques),MYSQLI_STORE_RESULT)){
                $result=$link2->error."<br>";
                exit();
            }
            while($row=$res->fetch_row()){
                $multires[]=$row[0];
            }
            $res->free();
            //echo "xmlHttp_responseText=[". iconv('UTF-8','CP1251',implode(',',$multires)) ."]";
            //echo urlencode ("xmlHttp_responseText=[". iconv('CP1251','UTF-8',implode(',',$multires)) ."]");
            //prot("xmlHttp_responseText=[". implode(',',$multires) ."]");
            echo "xmlHttp_responseText=[". implode(',',$multires) ."]";
        break;
        case "jsonselect":
            $multires=[];
            if(!$res=$link2->query(iconv('UTF-8','CP1251',$ques),MYSQLI_STORE_RESULT)){
                $result=$link2->error."<br>";
                exit();
            }
            while($row=$res->fetch_row()){
                $multires[]=$row[0];
            }
            $res->free();
            //echo "xmlHttp_responseText=[". iconv('UTF-8','CP1251',implode(',',$multires)) ."]";
            //echo urlencode ("xmlHttp_responseText=[". iconv('CP1251','UTF-8',implode(',',$multires)) ."]");
            //prot("xmlHttp_responseText=[". implode(',',$multires) ."]");
            echo "[". implode(',',$multires) ."]";
        break;
        case "insert":
            if(!$res=$link2->query(iconv('UTF-8','CP1251',$ques),MYSQLI_STORE_RESULT)){
                $result=$link2->error."<br>";
            } else {
                $result=$link2->insert_id;
                $link2->commit();
                $res->free();
            }
            echo "xmlHttp_responseText=\"".$result."\"";
        break;
        case "update":
            if(!$res=$link2->query(iconv('UTF-8','CP1251',$ques),MYSQLI_STORE_RESULT)){
                $result=$link2->error."<br>";
            }else{
                $link2->commit();
                $res->free();
            }
            //prot($result);
            echo "xmlHttp_responseText=\"".$result."\"";
        break;
        }
        $link2->close();
    } else {
        echo "MySQL. <br>";
    }
?>




