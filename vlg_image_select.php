<?php
if(isset($_GET['oid'])){
    $oid = (int)$_GET['oid'];
    if ( $oid > 0 ) {
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
            //$query = "SELECT bcontent FROM blobs WHERE otype=".$_GET['otype'] ." and oid=".$oid;
            $query="SELECT bcontent FROM blobs WHERE bid=(select blob_id from files where otype=".$_GET['otype'] ." and oid=".$oid .")";
            // Выполняем запрос и получаем файл
            $res = mysql_query($query);
            if ( mysql_num_rows( $res ) == 1 ) {
              $image = mysql_fetch_array($res);
              // Отсылаем браузеру заголовок, сообщающий о том, что сейчас будет передаваться файл изображения
              header("Content-type: image/*");
              // И  передаем сам файл
              echo $image['bcontent'];
            }
        }
    }
}
?>
