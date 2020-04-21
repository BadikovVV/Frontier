<?php
ini_set('display_errors', 'On');
error_reporting('E_ALL');
//header("Content-Type: text/html; charset=windows-1251"); //charset=utf8");
require_once "PHPExcel/Classes/PHPExcel.php";
//require_once "../Classes/PHPExcel.php";
require_once 'vlg_util.php';
// чтение с листа $sheet из ячейки ($col, $row) в формате $type
// String и Date УЖЕ в апострофах
function ceVal($sheet, $col, $row, $type) {
    $val = $sheet->getCellByColumnAndRow($col, $row)->getValue();
    switch ($type) {
        case 'String':
            //$val = is_null($val) ? 'NULL' : "'" . $val . "'";
            $val = is_null($val) ? "''" : "'" . $val . "'";
            break;
        case 'Number':
            //$val = is_null($val) ? 'NULL' : $val;
            $val = is_null($val) ? '0' : $val;
            break;
        case 'Date':
            //$val = is_null($val) ? 'NULL' : date("'Y-m-d'", PHPExcel_Shared_Date::ExcelToPHP($val));
            $val = is_null($val) ? "'0000-00-00'" : date("'Y-m-d'", PHPExcel_Shared_Date::ExcelToPHP($val));
            break;
    }
    return $val;
}
// чтение с листа $sheet из ячейки ($col, $row) в формате $type
// String и Date БЕЗ апострофов
function cVal($sheet, $col, $row, $type) {
    $val = $sheet->getCellByColumnAndRow($col, $row)->getValue();
    switch ($type) {
        case 'trimConvString':
            $val = is_null($val) ? "" : trim(iconv('UTF-8', 'CP1251', $val));
            break;
        case 'String':
            $val = is_null($val) ? "" : $val;
            break;
        case 'Number':
            //$val = is_null($val) ? 'NULL' : $val;
            $val = is_null($val) ? '0' : $val;
            break;
        case 'Date':
            //$val = is_null($val) ? 'NULL' : date("'Y-m-d'", PHPExcel_Shared_Date::ExcelToPHP($val));
            $val = is_null($val) ? "0000-00-00" : date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($val));
            break;
    }
    return $val;
}

// чтение c листа Excel $sheet строки $row начиная с колонки $startCol
// интепретируя форматы ячеек в соответствии с $typeArray
// и формирование строки для insert...values()
function rowVals($sheet, $startCol, $row, $typeArray) {
    $res = [];
    for ($i = 0; $i < sizeof($typeArray); $i++) {
        $val = $sheet->getCellByColumnAndRow($startCol + $i, $row)->getValue();
        list($typec,$lenc)=explode('.',$typeArray[$i]); // после точки max длина строки
//        switch ($typeArray[$i]) {
        switch ($typec) {
            case 'String':
                $val = iconv('UTF-8', 'CP1251', $val);
                if($lenc != ''){ // обрезаем строку до заданной длины
                    $val=substr($val,0,$lenc);
                }
                //$val = is_null($val) ? 'NULL' : "'" . $val . "'";
                $val = is_null($val) ? "''" : "'" . $val . "'";
                $res[] = $val;
                break;
            case 'Number':
                //$val = is_null($val) ? 'NULL' : $val;
                $val = is_null($val) ? '0' : $val;
                $res[] = $val;
                break;
            case 'Date':
                //$val = is_null($val) ? 'NULL' : date("'Y-m-d'", PHPExcel_Shared_Date::ExcelToPHP($val));
                $val = is_null($val) ? "'0000-00-00'" : date("'Y-m-d'", PHPExcel_Shared_Date::ExcelToPHP($val));
                $res[] = $val;
                break;
            case 'Skip':
                break;
        }
    }
    return implode(",", $res);
}
////////////////////////////////////////////////////////////////////////////////
// чтение с листа $sheet первой строки с названиями столбцов
// и сопоставление с dictionary.full_name where `table`='ps_list'
function readExcelHeader($sheet, $row) {
    $arDBTableColumn = array();
    $arExcelColumn = array();
    //
    $excelCol = 0;
    $rowExcelHeader = true;
    while ($rowExcelHeader) {
        if (cVal($sheet, $excelCol, $row, 'trimConvString') == '') {
            $rowExcelHeader = false;
        } else {
            $arExcelColumn[$excelCol] = cVal($sheet, $excelCol, $row, 'trimConvString');
            $excelCol++;
        }
    }
    //d($arExcelColumn);
    //
    $cursor = new CSQL("select d.*,cs.data_type from private_sector.dictionary d   
        left join information_schema.columns cs on d.`table`='ps_list' and cs.table_schema='private_sector' and 
            cs.table_name='ps_list' and d.fname=cs.column_name
        where d.`table`='ps_list' and cs.table_schema='private_sector' and cs.table_name='ps_list'
        order by d.serial,d.id");
    while ($cursor->assoc()) { //
        array_push($arDBTableColumn, 
            [$cursor->r["id"], $cursor->r["fname"], $cursor->r["full_name"], $cursor->r["serial"], $cursor->r["data_type"], -1]
        );
        //d([$cursor->r["id"], $cursor->r["fname"], $cursor->r["full_name"], $cursor->r["serial"], $cursor->r["data_type"], 0]);
    }
    $cursor->free();
    //
    foreach($arDBTableColumn as $key =>$value){
        $posInExcel=array_search($value[2], $arExcelColumn);
        if($posInExcel===FALSE) {
            
        } else {
            $arDBTableColumn[  $key  ][5] = $posInExcel;
            //d($arDBTableColumn[  $key  ]);
        }        
    }
    //
    
    return $arDBTableColumn;
}
//
////////////////////////////////////////////////////////////////////////////////
//
function insertFromExcelFields($arDBTableColumn) {
    $insQuery="INSERT INTO ps_arm_buffer(";
    foreach($arDBTableColumn as $key =>$value){
        if($value[5]>-1)
            $insQuery.=$value[1].",";
    }
    $insQuery=substr($insQuery,0,-1); // удаляем последнюю запятую
    return $insQuery. ") VALUES ";
}
///////////////////////////
// insert from excel
/////////////////////////
function insertFromExcel($arDBTableColumn,$sheet, $row) {
    $insQuery="(";
    foreach($arDBTableColumn as $key =>$value){
        //d($value);
        if($value[5]>-1){
            if($value[4]=="date"){
                $insQuery.="'".cVal($sheet, $value[5], $row, 'Date')."',";
            } else {
                $insQuery.="'". str_replace("'", " ",str_replace('"', " ",cVal($sheet, $value[5], $row, 'trimConvString'))) ."',";
            }
        }
    }
    $insQuery=substr($insQuery,0,-1); // удаляем последнюю запятую
    $insQuery.=")";
#    echo "<p>Sql str = $insQuery</p>";
    return $insQuery;
}
//
////////////////////////////////////////////////////////////////////////////////
// !!! не используется !!!
// импорт заявок из файла $fn в проект
function importProtoCall($user_id,$fn,$project_id) {
    //echo 1;
    try{
    $pExcel = PHPExcel_IOFactory::load($fn);
    //echo 2;
    $pExcel->setActiveSheetIndex(0);
    //echo 3;
    $aSheet = $pExcel->getActiveSheet();
    //echo 4;
    $highestRow = $aSheet->getHighestRow();
    //echo 5;
    $highestColumn = $aSheet->getHighestColumn();
    //echo 6;
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    //echo 7;
    //qSQL("TRUNCATE TABLE ps_arm_buffer;");
/*    
delete from ps_project_list where list_id in (SELECT list_id FROM private_sector.ps_list_dop where arm_id<10000)
delete from callpath where lp_id in (SELECT lid FROM private_sector.ps_list_dop where arm_id<10000)
delete FROM private_sector.ps_list_dop where arm_id<10000
delete FROM private_sector.ps_list where arm_id<10000
*/      
    } catch (Exception $e) {
        echo 'исключение: ',  $e->getMessage(), "\n";
    }
    $num_upd_rows=0;
    $num_ins_rows=0;
    for ($ri = 4; $ri <= $highestRow; $ri++) {
//    for ($ri = 4; $ri <= 6; $ri++) {
        //d($ri);
        $result_status = rSQL("SELECT pas.ps_status ps_status FROM ps_arm_status pas where pas.status_name='".
                cVal($aSheet, 5, $ri, 'trimConvString') ."'")["ps_status"];
        $result_service_id=rSQL("SELECT id FROM service where sname='".cVal($aSheet, 14, $ri, 'trimConvString') ."'")["id"];
        if(!$result_service_id)   $result_service_id="NULL";
        $technology="xPON";//cVal($aSheet, 4, $ri, 'trimConvString');
        $result_tech_id=rSQL("SELECT id FROM ps_teh_podkl where name='".$technology ."'")["id"];
        $arm_id=iconv('UTF-8', 'CP1251', ceVal($aSheet, 0, $ri, 'String'));
        $result_list_id=rSQL("SELECT list_id FROM ps_list where arm_id=".$arm_id ."")["list_id"];
        
        $project_id=iconv('UTF-8', 'CP1251', ceVal($aSheet, 1, $ri, 'String'));        
        $client_fio=str_ireplace("'", "", cVal($aSheet, 7, $ri, 'trimConvString'));
        $contact_phone=str_ireplace("'", "", cVal($aSheet, 8, $ri, 'trimConvString'));        
        $settlement=cVal($aSheet, 9, $ri, 'trimConvString');
        $ul=cVal($aSheet, 10, $ri, 'trimConvString');
        $home=cVal($aSheet, 11, $ri, 'trimConvString');
        $corp=cVal($aSheet, 12, $ri, 'trimConvString');
        $room=cVal($aSheet, 13, $ri, 'trimConvString');
        $guarantee=cVal($aSheet, 18, $ri, 'trimConvString');
        $comment=str_ireplace("'", "", cVal($aSheet, 48, $ri, 'trimConvString'));
        //echo "<br>$comment";
        //
        if(!empty(cVal($aSheet, 15, $ri, 'String'))){
            $install=2500.00;
            $deferredpay=0;
        }elseif(!empty(cVal($aSheet, 16, $ri, 'String'))){
            $install=2850.00;
            $deferredpay=3;
        }else{
            $install=2880.00;
            $deferredpay=6;
        }
        //
        if(!empty(cVal($aSheet, 19, $ri, 'String'))){
            $tariffname='Акция Легкая цена (моно ШПД)';
        }elseif(!empty(cVal($aSheet, 20, $ri, 'String'))){
            $tariffname='Акция Партнерский';
        }elseif(!empty(cVal($aSheet, 21, $ri, 'String'))){
            $tariffname='Акция Год кино';
        }else{
            $tariffname='Обычный Домашний интернет';
        }
        if(!empty(cVal($aSheet, 23, $ri, 'String')))  $month_pay=cVal($aSheet, 23, $ri, 'String');
        else   $month_pay=0;
        //
        if(!empty(cVal($aSheet, 24, $ri, 'String'))){
            $ontfullpay=2100;
            $ontlease=0;
        }elseif(!empty(cVal($aSheet, 25, $ri, 'String'))){
            $ontfullpay=0;
            $ontlease=100;
        }elseif(!empty(cVal($aSheet, 26, $ri, 'String'))){
            $ontfullpay=6200;
            $ontlease=0;
        }else{
            $ontfullpay=0;
            $ontlease=150;
        }
        //
        if(!empty(cVal($aSheet, 28, $ri, 'String'))){
            $routefullpay=0;
            $routelease=0;
        }elseif(!empty(cVal($aSheet, 29, $ri, 'String'))){
            $routefullpay=1900;
            $routelease=0;
        }else{
            $routefullpay=0;
            $routelease=50;
        }
        //
        if(!empty(cVal($aSheet, 31, $ri, 'String')))    $attachnum=cVal($aSheet, 31, $ri, 'String');
        else    $attachnum=0;
        if(!empty(cVal($aSheet, 32, $ri, 'String'))){
            $attachfullpay=3800;
            $attachlease=0;
        }else{
            $attachfullpay=0;
            $attachlease=100;
        }
        //        
        if($result_list_id){
        ////////////////////////////////////////////////////////////////////////
        // заявка с таким номером уже загружена
            SQL("START TRANSACTION");
            $result_update = SQL("UPDATE ps_list  SET client_fio='".$client_fio."',
                settlement='".$settlement."',ul='".$ul."',
                home='".$home."',corp='".$corp."',room='".$room."',
                status_name='".cVal($aSheet, 5, $ri, 'trimConvString')."',contact_phone='".$contact_phone."' 
                WHERE list_id='" . $result_list_id . "'");
            $result_update = SQL("UPDATE ps_list_dop  SET comment='".$comment."',
                zatrat_smr=zatrat_smr,dev_summ=dev_summ,
                install='".$install."',month_pay='".$month_pay."',pon_flag=pon_flag,
                uid='".$user_id."',dateedit=now(),guarantee='".$guarantee ."',tariffname='".$tariffname."',ontlease='".$ontlease."',routelease='".$routelease."',
                realcost=realcost,targetdate=targetdate,finishdate=finishdate,substatus=substatus,
                deferredpay='".$deferredpay."',ontfullpay='".$ontfullpay."',routefullpay='".$routefullpay."',
                attachnum='".$attachnum."',attachfullpay='".$attachfullpay."',attachlease='".$attachlease."' 
                WHERE list_id='" . $result_list_id . "'");
            //$buf=$result_update->affected_rows();
            //if($buf) $num_upd_rows++;
            $num_upd_rows++;
            SQL("COMMIT");
            
        }else{
        ////////////////////////////////////////////////////////////////////////
        // заявка с таким номером еще нет
            SQL("START TRANSACTION");
            $result_list=SQL("INSERT INTO ps_list (bufer_id,dateinbegin,latlng,ues_arm,
                ltc,arm_id,
                client_fio,region,post_index,
                settlement,ul,
                home,corp,room,
                status_name,service,count_service,
                tariff_plan,sales_channel,dop_schannel,technology,contact_phone,cs,
                device_address)
            values
                (0,now(),'',".iconv('UTF-8', 'CP1251', ceVal($aSheet, 2, $ri, 'String')).",".
                iconv('UTF-8', 'CP1251', ceVal($aSheet, 3, $ri, 'String')).",".$arm_id.",'".
                $client_fio."','ВОЛГОГРАДСКАЯ ОБЛАСТЬ',0,'".
                $settlement."','".$ul."','".$home."','".$corp."','".$room."','".
                cVal($aSheet, 5, $ri, 'trimConvString')."',".iconv('UTF-8', 'CP1251', ceVal($aSheet, 14, $ri, 'String')).",0,
                '','ТАН','','".$technology."','".$contact_phone."','3','".
                $settlement." ".$ul." ".$home." ".$corp." ".$room."')");
            $result_list=$result_list->insert_id();
            $result_list_dop = SQL("INSERT INTO ps_list_dop (lid,list_id,status,arm_id,
                comment,file_smeta,zatrat_smr,dev_summ,shkaf_42u,shassi_olt,kol_ports,spd,
                difficult_mc,difficult_rs,difficult_abl,difficult_abv,
                install,month_pay,pon_flag,formatted_address,place_id,location_type,
                claster_id,tpid,service_id,uid,dateedit,guarantee,
                tariffname,ontlease,routelease,
                realcost,targetdate,finishdate,substatus,deferredpay,ontfullpay,routefullpay,attachnum,attachfullpay,attachlease) 
            values
                (NULL,'".$result_list ."','".$result_status ."',".$arm_id .", 
                '".$comment."', 0, 0, 0, 0, 0, 0, 0, 
                0, 0, 0, 0, 
                ".$install.",'".$month_pay ."', 0, '', '', '', 
                0,'".$result_tech_id ."',".$result_service_id.",".$user_id .",now(),'".$guarantee."',
                '".$tariffname."',".$ontlease.",".$routelease.",
                0,'0000-00-00','0000-00-00',20,".$deferredpay.",".$ontfullpay.",".$routefullpay.",'".$attachnum."',".$attachfullpay.",".$attachlease.")");
            $result_list_dop=$result_list_dop->insert_id();
            SQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment)
                VALUES(NULL,'".$result_list_dop."','". $result_status ."',".$user_id.",5,'20180101',2,'')");
            SQL("insert into ps_project_list (project_list_id,project_id,list_id,dateinsert,user_id,delete_flag)
                VALUES(NULL,".$project_id .",".$result_list.",now(),".$user_id .",0)"); 
            $num_ins_rows++;
        SQL("COMMIT");
        }
    }
    echo "<br> строк в исходном файле $highestRow (загружаем с 4-й строки)";
    echo "<br>Добавлено $num_ins_rows заявок для проекта №".$project_id.".";
    echo "<br>Обновлено $num_upd_rows заявок для проекта №".$project_id.".<br>";
}
//
////////////////////////////////////////////////////////////////////////////////
//
/* после загрузки меняем ссылки на технологию и услуги:
update ps_list_dop ld 
        left join ps_list l using(list_id)
        left join ps_teh_podkl tp on l.technology=tp.name
        left join service s on l.service=s.sname 
    set ld.tpid=ifnull(tp.id,-1),ld.service_id=ifnull(s.id,-1)
*/
function vlg_imp_new($user_id,$cs){
    set_time_limit(0);
    //
    switch ($cs) {
    case 'CS':
        echo "<b>Загрузка новых заявок B2C</b><p><form name='saddress' method='post' action='./?c=6&action=imp_cs' enctype='multipart/form-data'>";
    break;
    case 'B2B':
        echo "<b>Загрузка новых заявок B2B</b><p><form name='saddress' method='post' action='./?c=6&action=imp_b2b' enctype='multipart/form-data'>";
    break;
    default:
        echo "<b>Загрузка новых заявок</b><p><form name='saddress' method='post' "
            . "action='./?c=6&action=prjcallload&project_id=".$_REQUEST["project_id"]."' enctype='multipart/form-data'>";
    break;
    }
    //
    echo "Укажите Excel-файл заявок: <input type='file' name='faudit'> ";
    //echo "<BR> Количество обрабатываемых строк: <input type='text' name='kol_string' value='все'> ";
    //echo "<BR> МЦТЭТ: <input type='text' name='mctet' value=''> ";
    echo "<BR> МЦТЭТ: ";
    if(isset($_REQUEST["mctet"]) and $_REQUEST["mctet"]!='')
        echo select('mctet', "SELECT name FROM ps_mctet",$_REQUEST["mctet"],"выберите...");
    else
        echo select('mctet', "SELECT name FROM ps_mctet","выберите...","выберите...");
    //echo "<P>Фильтры загрузки:<p>";
    //echo "<BR>Частный сектор = Да";
    //echo "<BR>Статус <> Тест";
    echo "<p><input type='submit' value='Загрузить'>";
    //importMTCLTC("c:/Web/htdocs/ps/cs/buffer/Audit_zayavok_BIG.xls"); // загрузка МЦТЭТ/ЛТЦ
    //importATS("c:/Web/htdocs/ps/cs/buffer/Объекты_АТС.xls"); // загрузка объектов АТС
    //importRequest("/var/www/html/cs/buffer/Audit_zayavok20171128.xls");
    //exit();
    echo $_SERVER['DOCUMENT_ROOT'];
    if (@$_FILES['faudit']) { // Файл аудита
        //d(@$_FILES['faudit']);
        //echo "<br>".$_FILES['order_scan']['tmp_name'][$i];
        // Поиск или создания каталога загрузки
        $targetFolder = $_SERVER['DOCUMENT_ROOT'] . "/cs/buffer"; // Relative to the root
        //$targetFolder = "/var/www/html/cs/buffer"; // Relative to the root
        //if (file_exists($targetFolder)==TRUE)	echo "<br>ЕСТЬ ДИРЕКТОРИЯ";	else	echo "<br>NONE ДИРЕКТОРИЯ";
        if (file_exists($targetFolder) == FALSE) { // Нет директории
            mkdir($targetFolder, 0777);
        } else { // Есть
            //$targetFolder = $targetFolder."/".$_GET["cid"];
        }
        $tempFile = $_FILES['faudit']['tmp_name'];
        //$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
        $targetPath = $targetFolder;
        // Проверка на наличие файла с тем же именем
        $targetFile = rtrim($targetPath, '/') . '/' . $_FILES['faudit']['name'];
        //$fileParts = pathinfo($change_fname);
#	echo "<p> tmpFile = ",$tempFile, " targetFile = ",$targetFile,"</p>";
	if (file_exists($tempFile)==FALSE)
	{
		echo " <p> File with fileName $tempFile doesn`t exists!!! </p>";
	}
        $test = move_uploaded_file($tempFile, $targetFile);
        if (@$test == TRUE){
            echo "<br><i style='color: green'>Файл заявок ($targetFile) успешно скопирован на сервер.</i>";
            switch ($cs) {
            case 'CS':
                importCall($targetFile,$cs);
            break;
            case 'B2B':
                importCall($targetFile,$cs);
            break;
            default:
                echo "<br><i style='color: red'>файл заявок ($targetFile) для проекта № $cs </i>";
                importProtoCall($user_id,$targetFile,$cs);
                exit();
            break;
            }
        } else {
            echo "<br><i style='color: red'>Ошибка, файл заявок ($targetFile) не был скопирован на сервер!</i>";
            exit();
        }
        //exit();
        arm_buffer_stat(); // статистика по таблице ps_arm_buffer
        //
        $set_mctet=' ';
        if (isset($_POST["mctet"]) and trim($_POST["mctet"])!='выберите...')
            $set_mctet = " and ues_arm LIKE '%" . $_POST["mctet"] . "%'";
        // Копирование заявок построчное, т.к. необходимо проверить каждую на наличие в основной таблице и создать доп. атрибуты для новых записей
        echo "<br>Загружено заявок из Файла: <b>" . rSQL("SELECT count(*) cnt FROM ps_arm_buffer")["cnt"] . "</b><br>";
        //
        // поле CS - источник загрузки 1-АРМ ФЛ частный сектор, 2- АРМ ЮЛ, 3-excel протозаявки, 4-ручной ввод
        switch ($cs) {
        case 'CS':
            qSQL("update ps_arm_buffer set cs='1' where cs='ДА'");
            $result_taskl = qSQL("SELECT bufer_id, arm_id, device_address, technology, service FROM ps_arm_buffer where 1=1" . $set_mctet . 
                    " and cs='1' and status_name<>'Тест'");
        break;
        case 'B2B':
            qSQL("update ps_arm_buffer set cs='2'");
            $result_taskl = qSQL("SELECT bufer_id, arm_id, device_address, technology, service FROM ps_arm_buffer where 1=1" . $set_mctet . 
                    " and status_name<>'Тест'");            
        break;
        default:

        break;
        }
        echo "<br>Заявок для загрузки в БД после фильтрации: <b>" . mysql_num_rows($result_taskl) . "</b><br>";
        //***************************************** aded 24-10-201
       //    exit();
        $i = 1;
        $k = 1;
        $update_count=0;
        $insert_count=0;
        while ($row_taskl = mysql_fetch_array($result_taskl)) {
            echo "arm_id='" . $row_taskl["arm_id"] . "' and technology='" . $row_taskl["technology"] . "' and service='" . $row_taskl["service"] . "';";
            $cord_id = '';
            $result_check = qSQL("SELECT list_id, arm_id, technology, service, status_name FROM ps_list where arm_id='" . $row_taskl["arm_id"] . 
                    "' and technology='" . $row_taskl["technology"] . "' and service='" . $row_taskl["service"] . "'");
            if (mysql_num_rows($result_check) == 1) { // Если в БД уже есть запись с этим id, технология одинакова и запись только одна
                $row_check = mysql_fetch_array($result_check);
                $cord_id = $row_check["list_id"];
                // ver.1
                //$result_update = qSQL("UPDATE ps_list psl,ps_arm_buffer pab
                //        SET psl.dateinbegin=pab.dateinbegin,psl.latlng=pab.latlng,psl.ues_arm=pab.ues_arm, 
                // ver.2 старые координаты СОХРАНЯЕМ
                $result_update = qSQL("UPDATE ps_list psl,ps_arm_buffer pab
                        SET psl.dateinbegin=pab.dateinbegin,psl.ues_arm=pab.ues_arm, 
                            psl.ltc=pab.ltc, psl.mpz_id=pab.mpz_id, psl.client_fio=pab.client_fio, psl.region=pab.region, 
                            psl.post_index=pab.post_index, psl.settlement=pab.settlement, psl.ul=pab.ul, psl.home=pab.home, 
                            psl.corp=pab.corp, psl.room=pab.room, psl.device_address=pab.device_address, 
                            psl.status_name=pab.status_name, psl.dateinstatus=pab.dateinstatus, psl.taskonapp=pab.taskonapp,  
                            psl.count_service=pab.count_service, psl.tariff_plan=pab.tariff_plan, psl.sales_channel=pab.sales_channel, 
                            psl.dop_schannel=pab.dop_schannel, psl.gts_sts=pab.gts_sts, psl.date_talking=pab.date_talking, 
                            psl.date_reg_app=pab.date_reg_app, psl.date_reg_podapp=pab.date_reg_podapp, 
                            psl.reg_worder_tvp=pab.reg_worder_tvp, psl.end_hand=pab.end_hand, psl.reason_clrefusal=pab.reason_clrefusal, 
                            psl.operator_end_app=pab.operator_end_app, psl.date_refusal_subapp=pab.date_refusal_subapp, 
                            psl.rejected=pab.rejected, psl.type_test_tvp=pab.type_test_tvp, psl.available_tvp=pab.available_tvp, 
                            psl.date_end_test_tvp=pab.date_end_test_tvp, psl.dur_test_tvp=pab.dur_test_tvp, 
                            psl.dur_test_tvpday=pab.dur_test_tvpday, psl.norm_dur_test_tvpday=pab.norm_dur_test_tvpday, 
                            psl.ex_ntime_test_tvp=pab.ex_ntime_test_tvp, psl.ex_ntime_test_tvpday=pab.ex_ntime_test_tvpday, 
                            psl.date_reg_worder_td=pab.date_reg_worder_td, psl.date_create_dog=pab.date_create_dog, 
                            psl.date_destination_td=pab.date_destination_td, psl.date_sogl_time_exit=pab.date_sogl_time_exit, 
                            psl.date_sogl_first=pab.date_sogl_first, psl.date_sogl=pab.date_sogl, 
                            psl.date_close_worder=pab.date_close_worder, psl.dur_connect=pab.dur_connect, 
                            psl.dur_connectday=pab.dur_connectday, psl.norm_dur_connectday=pab.norm_dur_connectday, 
                            psl.date_done_agent=pab.date_done_agent, psl.ex_norm_dur_connect=pab.ex_norm_dur_connect, 
                            psl.ex_norm_dur_connectday=pab.ex_norm_dur_connectday, psl.date_trans_agent=pab.date_trans_agent, 
                            psl.instalator=pab.instalator, psl.agent_instalator=pab.agent_instalator, 
                            psl.count_offset_time=pab.count_offset_time, psl.reason_offset_time=pab.reason_offset_time, 
                            psl.instreason_offset_time=pab.instreason_offset_time, 
                            psl.cancelled_available_tvp=pab.cancelled_available_tvp, psl.release_destin_td=pab.release_destin_td, 
                            psl.duration_rezerv=pab.duration_rezerv, psl.date_begin_dog_kurs=pab.date_begin_dog_kurs, 
                            psl.sert_ota=pab.sert_ota, psl.sert_ota_atc=pab.sert_ota_atc, psl.sert_ota_rejected=pab.sert_ota_rejected, 
                            psl.sert_internet=pab.sert_internet, psl.sert_internet_atc=pab.sert_internet_atc, 
                            psl.sert_internet_rejected=pab.sert_internet_rejected, psl.sert_iptv=pab.sert_iptv, 
                            psl.sert_iptv_atc=pab.sert_iptv_atc, psl.sert_iptv_rejected=pab.sert_iptv_rejected, 
                            psl.coment_tvp_ota=pab.coment_tvp_ota, psl.coment_tvp_spd=pab.coment_tvp_spd, 
                            psl.operator_begin_app=pab.operator_begin_app, psl.category_serv_pk=pab.category_serv_pk, 
                            psl.internet=pab.internet, psl.iptv=pab.iptv, psl.ota=pab.ota, psl.pp=pab.pp, psl.fly=pab.fly, 
                            psl.mvno=pab.mvno, psl.sim_count=pab.sim_count, psl.date_begin_dto=pab.date_begin_dto, 
                            psl.date_end_dto=pab.date_end_dto, psl.lc_onima=pab.lc_onima, 
                            psl.dur_dtoday=pab.dur_dtoday, 
                            psl.norm_time_dto=pab.norm_time_dto, psl.worder_kurs=pab.worder_kurs, 
                            psl.release_none_tvp=pab.release_none_tvp, psl.release_none_tvp_iptv=pab.release_none_tvp_iptv, 
                            psl.number_ota_spd=pab.number_ota_spd, psl.date_close_worder_kurs=pab.date_close_worder_kurs, 
                            psl.promt=pab.promt, psl.date_open_worder_kurs=pab.date_open_worder_kurs, 
                            psl.date_open_worder_iptv_kurs=pab.date_open_worder_iptv_kurs, psl.worder_iptv_kurs=pab.worder_iptv_kurs, 
                            psl.date_close_worder_iptv_kurs=pab.date_close_worder_iptv_kurs, 
                            psl.contact_phone=pab.contact_phone, psl.contact_phone2=pab.contact_phone2, 
                            psl.contact_person=pab.contact_person, psl.fiz=pab.fiz, psl.ur=pab.ur, 
                            psl.close_worder_installator=pab.close_worder_installator, psl.task_num_non_tvp=pab.task_num_non_tvp, 
                            psl.vip=pab.vip, psl.cost_tp_spd=pab.cost_tp_spd, psl.cost_tp_iptv=pab.cost_tp_iptv, 
                            psl.fio_create_dog=pab.fio_create_dog, psl.card_number_access=pab.card_number_access, 
                            psl.card_number_access_iptv=pab.card_number_access_iptv, psl.cs=pab.cs, 
                            psl.date_bind_card_onyma_spd=pab.date_bind_card_onyma_spd, 
                            psl.date_bind_card_onyma_iptv=pab.date_bind_card_onyma_iptv, 
                            psl.date_act_card_onyma_spd=pab.date_act_card_onyma_spd, 
                            psl.date_act_card_onyma_iptv=pab.date_act_card_onyma_iptv, 
                            psl.end_time_install_wfm=pab.end_time_install_wfm, psl.area_wfm=pab.area_wfm, 
                            psl.create_rss=pab.create_rss, psl.cpo=pab.cpo, psl.services=pab.services, 
                            psl.promo_action=pab.promo_action, psl.source_inf=pab.source_inf, 
                            psl.source_inf_other=pab.source_inf_other, psl.flag_migration=pab.flag_migration, 
                            psl.reason_rejection=pab.reason_rejection, psl.nls=pab.nls, psl.delivery=pab.delivery 
                        WHERE pab.bufer_id='" . $row_taskl["bufer_id"] . "' and psl.arm_id='" . $row_check["arm_id"] . "' and psl.technology='" . $row_check["technology"] . 
                            "' and psl.service='" . $row_check["service"] . "';");
                echo "<b style='color: orange'>$i</b> - запись " . $row_taskl["arm_id"] . " (" . $row_taskl["bufer_id"] . ") ОБНОВЛЕНА <br>";
                $update_count = @$update_count + 1;
            } elseif (mysql_num_rows($result_check) == 0) { // Если запись не найдена в БД (Новая запись), то добавим её как есть
                $result_copy = qSQL("INSERT INTO ps_list SELECT NULL,bufer_id,dateinbegin,latlng,ues_arm,ltc,arm_id,mpz_id,client_fio,
                    region,post_index,settlement,ul,home,corp,room,device_address,status_name,dateinstatus,taskonapp,service,count_service,
                    tariff_plan,sales_channel,dop_schannel,gts_sts,date_talking,date_reg_app,date_reg_podapp,reg_worder_tvp,end_hand,
                    reason_clrefusal,operator_end_app,date_refusal_subapp,rejected,type_test_tvp,available_tvp,date_end_test_tvp,
                    dur_test_tvp,dur_test_tvpday,norm_dur_test_tvpday,ex_ntime_test_tvp,ex_ntime_test_tvpday,technology,date_reg_worder_td,
                    date_create_dog,date_destination_td,date_sogl_time_exit,date_sogl_first,date_sogl,date_close_worder,dur_connect,
                    dur_connectday,norm_dur_connectday,date_done_agent,ex_norm_dur_connect,ex_norm_dur_connectday,date_trans_agent,
                    instalator,agent_instalator,count_offset_time,reason_offset_time,instreason_offset_time,cancelled_available_tvp,
                    release_destin_td,duration_rezerv,date_begin_dog_kurs,sert_ota,sert_ota_atc,sert_ota_rejected,sert_internet,
                    sert_internet_atc,sert_internet_rejected,sert_iptv,sert_iptv_atc,sert_iptv_rejected,coment_tvp_ota,coment_tvp_spd,
                    operator_begin_app,category_serv_pk,internet,iptv,ota,pp,fly,mvno,sim_count,date_begin_dto,date_end_dto,lc_onima,
                    dur_dtoday,norm_time_dto,worder_kurs,release_none_tvp,release_none_tvp_iptv,number_ota_spd,date_close_worder_kurs,
                    promt,date_open_worder_kurs,date_open_worder_iptv_kurs,worder_iptv_kurs,date_close_worder_iptv_kurs,
                    contact_phone, contact_phone2,
                    contact_person,fiz,ur,close_worder_installator,task_num_non_tvp,vip,cost_tp_spd,cost_tp_iptv,fio_create_dog,
                    card_number_access,card_number_access_iptv,cs,date_bind_card_onyma_spd,date_bind_card_onyma_iptv,
                    date_act_card_onyma_spd,date_act_card_onyma_iptv,end_time_install_wfm,area_wfm,create_rss,cpo,services,promo_action,
                    source_inf,source_inf_other,flag_migration,reason_rejection,nls,delivery 
                    FROM ps_arm_buffer WHERE ps_arm_buffer.bufer_id='" . $row_taskl["bufer_id"] . "';");
                $insert_count = @$insert_count + 1;
                $cord_id = mysql_insert_id();
                echo "<b style='color: green'>$i</b> - запись " . $row_taskl["arm_id"] . " (" . $row_taskl["bufer_id"] . ") 
                    <b style='color: green;'>ДОБАВЛЕНА (новый ID: $cord_id)</b> дублей найдено = " . mysql_num_rows($result_check) . "<br>";
            } else
                echo "<b style='color: red'>Для заявки " . $row_taskl["arm_id"] . " (" . $row_taskl["bufer_id"] . ") нет событий при загрузке</b><br>";
            // Создадим доп информацию для этой записи, если доп. информация отсутствует
            if (!@$row_check["list_id"] or @ $row_check["list_id"] == 0)
                $row_check["list_id"] = $cord_id;
            $result_check2 = qSQL("SELECT list_id, arm_id, technology, service, status_name FROM ps_list where arm_id='".$row_taskl["arm_id"] . 
                    "' and technology='".$row_taskl["technology"] ."' and service='".$row_taskl["service"] ."'");
            $row_check2 = mysql_fetch_array($result_check2);
            $result_sdop = qSQl("SELECT * FROM ps_list_dop where list_id='".$row_check2["list_id"] ."' and arm_id='".$row_check2["arm_id"] ."'");
            if (mysql_num_rows($result_sdop) == 0) {
                // Заполним статус:
                $result_slist = qSQL("SELECT pas.ps_status set_status FROM ps_list pls, ps_arm_status pas where pls.list_id='" . $row_check2["list_id"] . 
                        "' and pls.status_name=pas.status_name");
                if($row_slist = mysql_fetch_array($result_slist)){
                    $row_slist=$row_slist['set_status'];
                } else { // такой статус АРМ не найден
                    $row_slist=10;
                }
                $result_list_dop = SQL("INSERT INTO ps_list_dop (lid,list_id,status,arm_id,
                        comment,file_smeta,zatrat_smr,dev_summ,shkaf_42u,shassi_olt,kol_ports,spd,
                        difficult_mc,difficult_rs,difficult_abl,difficult_abv,
                        install,month_pay,pon_flag,formatted_address,place_id,location_type,
                        claster_id,tpid,service_id,uid,dateedit,guarantee,tariffname,ontlease,routelease,
                        realcost,targetdate,finishdate,substatus,deferredpay,ontfullpay,
                        routefullpay,attachnum,attachfullpay,attachlease,novelty,tariff_id,installg_id) 
                    values
                        (NULL,'" . $row_check2["list_id"] . "','" . $row_slist . "','" . $row_check2['arm_id'] . "', 
                        '', 0, 0, 0, 0, 0, 0, 0, 
                        0, 0, 0, 0, 
                        0, 0, 0, '', '', '', 
                        0, -1, NULL,".$user_id .",now(),'','',0,0,
                        0,'0000-00-00','0000-00-00',20,0,0,0,0,0,0,
                        0,-1,-1)");
                // открываем маршрутизацию заявки
                //if($cs=='B2B'){
                    
                //} else {
                    $result_list_dop=$result_list_dop->insert_id();
                    SQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment)
                        VALUES(NULL,'".$result_list_dop."','". $row_slist ."',".$user_id.",5,'20180101',2,'')")->commit();
                //}
            }
            // Получим Координаты GOOGLE и заполним их
/*            $result_tasks = qSQL("SELECT arm_id, device_address FROM ps_list where list_id='" . $cord_id . "' group by arm_id");
            $row_tasks = mysql_fetch_array($result_tasks);
            echo 'http://maps.google.com/maps/api/geocode/xml?address=' . urlencode(iconv('CP1251', 'UTF-8', $row_tasks["device_address"])) . '&language=ru';
            $string = getUrl('http://maps.google.com/maps/api/geocode/xml?address=' . urlencode(iconv('CP1251', 'UTF-8', $row_tasks["device_address"])) . '&language=ru');
            $xml = simplexml_load_string($string);
            //print_r($xml);
            $status = $xml->status;
            if ($status == "OK") {
                echo "<br>$i нашлись координаты для " . $row_tasks["device_address"];
                $lat = $xml->result->geometry->location->lat;
                $lng = $xml->result->geometry->location->lng;
                $formatted_address = $xml->result->formatted_address; // Форматированный адрес гуглом
                $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
                $place_id = $xml->result->place_id; // Уникальный идентификатор объекта в гугл
                $location_type = $xml->result->geometry->location_type; // Точность определения координаты. ROOFTOP - точный, с точным почтовым адресом.
                $_latlng = $lat . ":" . $lng;
                echo "<br>$i координаты = " . $_latlng;
                $result_update1 = qSQL("update ps_list set latlng='" . $_latlng . "' where arm_id='" . $row_tasks["arm_id"] . "'");
                if (@$location_type == 'ROOFTOP') {
                    $add_claster = 1;
                }
                $result_update2 = qSQL("update ps_list_dop set formatted_address='" . @$formatted_address . 
                    "', place_id='" . @$place_id . "', location_type='" . @$location_type . "' where arm_id='" . $row_tasks["arm_id"] . "'");
                $k++;
            } else
                echo "<br><i style='color: red'>$i координаты НЕ определены !!!</i>";*/
            $i++;
        }
        // простановка технологии/услуги в ps_list_dop для новых записей
        $result_update = SQL("update ps_list_dop ld 
            left join ps_list l using(list_id)
            left join ps_teh_podkl tp on l.technology=tp.name
            left join service s on l.service=s.sname 
            set ld.tpid=ifnull(tp.id,-1),ld.service_id=ifnull(s.id,-1)");
        if ($result_update == TRUE) {
            echo "<p><img src='./images/check.gif' align='absmiddle'><b style='color: green;'>для ".
                $result_update->affected_rows() ." новых записей изменены технология/услуги</b><p>";
        }
        $result_update->commit();
        // изменение статусов (при UPDATE) заявок в соответствии со статусом АРМ
        $result_update = SQL("update ps_list pl 
		inner join ps_list_dop pld on pl.list_id=pld.list_id
		left join ps_status s on pld.status=s.id   
		left join ps_arm_status ars on pl.status_name=ars.status_name
            set pld.status=ars.ps_status
            where ars.ps_status!=pld.status and ars.ps_status in (65,55,50)");
        if ($result_update == TRUE) {
            echo "<p><img src='./images/check.gif' align='absmiddle'><b style='color: green;'>для ".
                $result_update->affected_rows() ." СУЩЕСТВОВАВШИХ заявок обновлены статусы</b><p>";
        }
        $result_update->commit();
        //
        echo "<br>Загрузка завершена. Выполните поиск координат загруженных объектов.";
        // отправляем уведомление в ТБ (ugroup=5)
        if($insert_count>0 or $update_count>0){
            eMail(rSQL("SELECT email FROM ps_users where ugroup=5")["email"],"Частный сектор",
                "В БД \"Частного сектора\" загружено ".$insert_count." заявок\n".
                "обновлено ".$update_count." заявок\n".
                "Вам необходимо обработать эти заявки\n".
                "");
        }
    }
}
//
////////////////////////////////////////////////////////////////////////////////        
// статистика по таблице ps_arm_buffer
function arm_buffer_stat(){
    $cursor=SQL("select ues_arm,status_name,count(*) cnt FROM ps_arm_buffer group by ues_arm,status_name order by ues_arm,status_name");
    echo "<br><b>Статусы заявок в АРМ</b>";
    // стили таблицы
    echo "<style type=\"text/css\">
            td.prjleftcol { color:#025; background:#CDE; }
            td.prjheader { color:#037; background:#DEF; }
        </style>";
    echo "<br><table border='1' cellspacing='1' cellpadding='2'>";
    echo "<tr>
        <td align='center' class='prjheader'><b>ТУЭС</b></td>
        <td align='center' class='prjheader'><b>Статус</b></td>
        <td align='center' class='prjheader'><b>Количество</b></td>
        </tr>";
    //while ($ps_status=$ps_status_res->fetch_assoc()) {
    while ($cursor->assoc()) {
        echo "<tr>
            <td align='center'><b>".$cursor->r["ues_arm"]."</b></td>
            <td align='center'>".$cursor->r["status_name"]."</td>
            <td align='left'>".$cursor->r["cnt"]."</td>
            </tr>";
    }
    echo "</table>";
    $cursor->free();
}
////////////////////////////////////////////////////////////////////////////////
// импорт заявок из файла $fn в ps_arm_buffer
function importCall($fn,$cs) {
    //echo 1;
    try{
	    $objReader=PHPExcel_IOFactory::createReaderForFile($fn); #added by Badikov

	    $objReader->setReadDataOnly(true); #added by Badikov
	    $pExcel =  $objReader->load($fn);#PHPExcel_IOFactory::load($fn);
	    echo "<p> данные из Excel файла ",$fn," is loaded</p>";

    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    echo "<p> number of  Rows in file $fn  =$highestRow </p>";
    qSQL("TRUNCATE TABLE ps_arm_buffer;");
    } catch (Exception $e) {
        echo '<p> исключение: ',  $e->getMessage(), "</p>\n";
    }
    //
    $arDBTableColumn=readExcelHeader($aSheet, 1);
    $insertFields=insertFromExcelFields($arDBTableColumn);
    //exit();
    //echo $cs;
    for ($ri = 2; $ri <= $highestRow; $ri++) {
//    for ($ri = 2; $ri <= 1579; $ri++) {
        //d( trim(iconv('UTF-8', 'CP1251',$aSheet->getCellByColumnAndRow(0, $ri)->getValue())) );
        
        if( empty(trim(iconv('UTF-8', 'CP1251',$aSheet->getCellByColumnAndRow(0, $ri)->getValue()))) and
            empty(trim(iconv('UTF-8', 'CP1251',$aSheet->getCellByColumnAndRow(2, $ri)->getValue())))
          ){
            // считаем пустой строкой - заканчиваем загрузку
            break;
        }
        switch ($cs) {
        case 'CS':
            qSQL("INSERT INTO ps_arm_buffer(
                ues_arm,ltc,arm_id,mpz_id,client_fio,nls,
                region,post_index,settlement,ul,home,corp,room,
                device_address,status_name,dateinstatus,taskonapp,service,
                count_service,tariff_plan,sales_channel,dop_schannel,
                gts_sts,date_talking,date_reg_app,date_reg_podapp,reg_worder_tvp,end_hand,reason_clrefusal,
                operator_end_app,date_refusal_subapp,rejected,
                type_test_tvp,available_tvp,date_end_test_tvp,dur_test_tvp,dur_test_tvpday,
                norm_dur_test_tvpday,ex_ntime_test_tvp,ex_ntime_test_tvpday,technology,
                date_reg_worder_td,date_create_dog,date_destination_td,date_sogl_time_exit,
                date_sogl_first,date_sogl,date_close_worder,dur_connect,dur_connectday,
                norm_dur_connectday,date_done_agent,ex_norm_dur_connect,ex_norm_dur_connectday,
                date_trans_agent,instalator,agent_instalator,count_offset_time,
                reason_offset_time,instreason_offset_time,cancelled_available_tvp,release_destin_td,duration_rezerv,
                date_begin_dog_kurs,sert_ota,sert_ota_atc,sert_ota_rejected,
                sert_internet,sert_internet_atc,sert_internet_rejected,sert_iptv,sert_iptv_atc,
                sert_iptv_rejected,coment_tvp_ota,coment_tvp_spd,operator_begin_app,category_serv_pk,internet,
                iptv,ota,pp,fly,mvno,sim_count,date_begin_dto,date_end_dto,
                lc_onima,dur_dtoday,norm_time_dto,worder_kurs,release_none_tvp,
                release_none_tvp_iptv,number_ota_spd,date_close_worder_kurs,promt,
                date_open_worder_kurs,date_open_worder_iptv_kurs,worder_iptv_kurs,date_close_worder_iptv_kurs,            
                contact_phone,contact_phone2,contact_person,
                fiz,ur,close_worder_installator,task_num_non_tvp,vip,cost_tp_spd,
                cost_tp_iptv,fio_create_dog,   card_number_access,card_number_access_iptv,cs,delivery,
                date_bind_card_onyma_spd,date_bind_card_onyma_iptv,date_act_card_onyma_spd,date_act_card_onyma_iptv,end_time_install_wfm,
                area_wfm,create_rss,cpo,services,promo_action,source_inf,
                source_inf_other,flag_migration,reason_rejection)
            VALUES
            (" . rowVals($aSheet, 0, $ri, ["String", "String", "Number", "Number", "String.255","String", 
                "String", "Number", "String", "String", "String", "String", "String",
                "String", "String", "Date", "String", "String", 
                "Number", "String", "String","String", 
                "String", "Date", "Date", "Date", "Date", "String", "String",
                "String", "Date", "String", 
                "String", "String", "Date", "String", "Number",
                "Number", "String", "Number", "String", 
                "Date", "Date", "Date", "Date", 
                "Date", "Date", "Date", "String", "Number", 
                "Number", "Date", "String", "Number", 
                "Date", "String", "String", "Number", 
                "String", "String", "String", "Date", "Date",
                "Date", "String", "String", "String", 
                "String", "String", "String", "String", "String", 
                "String", "String", "String", "String", "String", "String",
                "String", "String", "String", "String", "String", "Number", "Date", "Date",
                "Number", "Number", "Number", "String", "String", 
                "String", "Number", "Date", "String", 
                "Date", "Date", "String", "Date", 
                "String", "String", "String", 
                "String", "String", "String", "String", "String", "Number", 
                "Number", "String", "Skip", "String", "String", "String", "String", 
                "Date", "Date", "Date", "Date", "Date",
                "String", "String", "String", "String", "String", "String", 
                "String","String", "String"]) .
                    ")");
        break;
        case 'B2B':
            /*qSQL("INSERT INTO ps_arm_buffer(ues_arm,ltc,arm_id,mpz_id,client_fio,
            nls,region,post_index,settlement,ul,
            home,corp,room,device_address,status_name,
            dateinstatus,taskonapp,service,count_service,tariff_plan,
            sales_channel,dop_schannel,gts_sts,date_talking,date_reg_app,
            date_reg_podapp,reg_worder_tvp,end_hand,reason_clrefusal,operator_end_app,
            date_refusal_subapp,rejected,type_test_tvp,available_tvp,date_end_test_tvp,
            dur_test_tvp,dur_test_tvpday,norm_dur_test_tvpday,ex_ntime_test_tvp,ex_ntime_test_tvpday,
            technology,
            
            coment_tvp_ota,coment_tvp_spd,
            
            promt,contact_phone,contact_person)
            VALUES
            (" . rowVals($aSheet, 0, $ri, [
                "String", "String", "Skip", "Number", "Number", "String.255",
                "String", "String", "Number", "String", "String", 
                "String", "String", "String", "String", "String", 
                "Date", "String", "String", "Number", "String", 
                "String", "String", "String", "Date", "Date", 
                "Date", "Date", "String", "String", "String", 
                "Date", "String", "String", "String", "Date", 
                "String", "Number", "Number", "String", "Number", 
                "String", 
                
                "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", 
                
                "String", "String", 
                
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip", 
                "Skip", "Skip", "Skip", "Skip", "Skip",
                
                "String", "String", "String"
                ]) .
                    ")");*/
                qSQL($insertFields. insertFromExcel($arDBTableColumn,$aSheet, $ri));

        break;
        default:
            echo "<br><i style='color: red'>файл заявок ($targetFile) для проекта № $cs </i>";
            importProtoCall($user_id,$targetFile,$cs);
            exit();
        break;
        }
        
        //prot("importRequest $insertDML");
//        if (!mysql_query($insertDML))
//            echo "<br>Excel row " . $ri . " Err.no." . mysql_errno() . ": " . mysql_error() . "\n";
    }
    //exit();
}
////////////////////////////////////////////////////////////////////////////////
// импорт заявок из файла $fn в ps_arm_buffer
/*function importRequestB2B($fn) {
    echo 1;
    try{
    $pExcel = PHPExcel_IOFactory::load($fn);
    echo 2;
    $pExcel->setActiveSheetIndex(0);
    echo 3;
    $aSheet = $pExcel->getActiveSheet();
    echo 4;
    $highestRow = $aSheet->getHighestRow();
    echo 5;
    $highestColumn = $aSheet->getHighestColumn();
    echo 6;
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    echo 7;
    qSQL("TRUNCATE TABLE ps_arm_buffer;");
    } catch (Exception $e) {
        echo 'исключение: ',  $e->getMessage(), "\n";
    }
    echo 8;
    for ($ri = 2; $ri <= $highestRow; $ri++) {
//    for ($ri = 2; $ri <= 1579; $ri++) {
        //d($ri);
        qSQL("INSERT INTO ps_arm_buffer(ues_arm,ltc,arm_id,mpz_id,client_fio,
        nls,
        region,post_index,settlement,ul,home,corp,room,device_address,status_name,dateinstatus,taskonapp,service,
        count_service,tariff_plan,sales_channel,dop_schannel,
        gts_sts,date_talking,date_reg_app,date_reg_podapp,reg_worder_tvp,end_hand,
        reason_clrefusal,operator_end_app,date_refusal_subapp,rejected,
        type_test_tvp,available_tvp,date_end_test_tvp,dur_test_tvp,dur_test_tvpday,
        norm_dur_test_tvpday,ex_ntime_test_tvp,ex_ntime_test_tvpday,technology,
        date_reg_worder_td,date_create_dog,date_destination_td,date_sogl_time_exit,
        date_sogl_first,date_sogl,date_close_worder,dur_connect,dur_connectday,
        norm_dur_connectday,date_done_agent,ex_norm_dur_connect,ex_norm_dur_connectday,
        date_trans_agent,instalator,   agent_instalator,count_offset_time,
        reason_offset_time,instreason_offset_time,cancelled_available_tvp,release_destin_td,
        duration_rezerv,date_begin_dog_kurs,sert_ota,sert_ota_atc,sert_ota_rejected,
        sert_internet,sert_internet_atc,sert_internet_rejected,sert_iptv,sert_iptv_atc,
        sert_iptv_rejected,coment_tvp_ota,coment_tvp_spd,operator_begin_app,
        category_serv_pk,internet,iptv,ota,pp,fly,mvno,sim_count,date_begin_dto,
        date_end_dto,lc_onima)
        VALUES
        (" . rowVals($aSheet, 0, $ri, ["String", "String", "Number", "Number", "String",
                    "String", "String", "Number", "String", "String", "String", "String", "String",
                    "String", "String", "Date", "String", "String", "Number", "String", "String",
                    "String", "String", "Date", "Date", "Date", "Date", "String", "String",
                    "String", "Date", "String", "String", "String", "Date", "String", "Number",
                    "Number", "String", "Number", "String", "Date", "Date", "Date", "Date", "Date",
                    "Date", "Date", "String", "Number", "Number", "Date", "String", "Number", "Date",
                    "String", "String", "Number", "String", "String", "String", "Date", "Date",
                    "Date", "String", "String", "String", "String", "String", "String", "String",
                    "String", "String", "String", "String", "String", "String", "String",
                    "String", "String", "String", "String", "String", "Number", "Date", "Date",
                    "Number"]) .
                ")");
        //prot("importRequest $insertDML");
//        if (!mysql_query($insertDML))
//            echo "<br>Excel row " . $ri . " Err.no." . mysql_errno() . ": " . mysql_error() . "\n";
    }
}*/
////////////////////////////////////////////////////////////////////////////////
// импорт МЦТЭТ/ТУЭС и ЛТЦ из файла заявок $fn в ps_mctet и ps_ltc
function importMTCLTC($fn) {
    $pExcel = PHPExcel_IOFactory::load($fn);
    //echo "<br>$fn";
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

    qSQL("TRUNCATE TABLE ps_mctet");
    qSQL("TRUNCATE TABLE ps_ltc");

    for ($ri = 2; $ri <= $highestRow; $ri++) {
        $lMCTET = iconv('UTF-8', 'CP1251', ceVal($aSheet, 0, $ri, 'String'));
        $lLTC = iconv('UTF-8', 'CP1251', ceVal($aSheet, 1, $ri, 'String'));
        //echo "<br>$lMCTET";
        //echo "<br>$lLTC";
        //
        if (strlen($lMCTET) > 2 and strlen($lLTC) > 2) { // т.е. не "''"
            $resultSQL = qSQL("SELECT * FROM ps_mctet where name=" . $lMCTET . "");
            if ($rowSQL = mysql_fetch_array($resultSQL)) {
                $idMCTET = $rowSQL["id"];
            } else {
                //while ($rowSQL = mysql_fetch_array($resultSQL)) {
                qSQL("INSERT INTO ps_mctet(name,coment,task)
                VALUES (" . $lMCTET . ",'',0)");
                $idMCTET = mysql_insert_id();
            }
            //
            $resultSQL = qSQL("SELECT * FROM ps_ltc where name=" . $lLTC . "");
            if ($rowSQL = mysql_fetch_array($resultSQL)) {
                
            } else {
                //while ($rowSQL = mysql_fetch_array($resultSQL)) {
                qSQL("INSERT INTO ps_ltc(name,abr,coment,address,mid)
                VALUES (" . $lLTC . ",'ЛТЦ','','',$idMCTET)");
            }
        }
    }
}

// импорт объектов ГТС
function importATS($fn) {
    //print_r("\n-- $fn --\n");
    $pExcel = PHPExcel_IOFactory::load($fn);
    echo "<br>$fn загрузка с 8-й строки";
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    qSQL("delete from com_obj where lay_type=102");
    //qSQL("delete from ps_olayers where oid>60696");
    for ($ri = 8; $ri <= $highestRow; $ri++) {
    //for ($ri = 8; $ri <= 100; $ri++) {
        $loname = iconv('UTF-8', 'CP1251', cVal($aSheet, 0, $ri, 'String'));
        $lotype = iconv('UTF-8', 'CP1251', str_replace("'", "", cVal($aSheet, 1, $ri, 'String')));
        if (strlen($loname) > 2 and strlen($lotype) > 2) { // т.е. не "''"        
            $col5_area=trim(iconv('UTF-8', 'CP1251', '' .cVal($aSheet, 5, $ri, 'String')));
            if(stristr($col5_area,'Волгоград') or stristr($col5_area,'Волжский')){
                $col5_area="";
            } else $col5_area=$col5_area.' район';
            $col6_settlement=trim(iconv('UTF-8', 'CP1251', '' .cVal($aSheet, 6, $ri, 'String')));
            $col8_street=trim(iconv('UTF-8', 'CP1251', '' .cVal($aSheet, 8, $ri, 'String')));
            if($col5_area==$col6_settlement) $loaddress=$col5_area;
            else $loaddress=$col5_area." ".$col6_settlement;
            if($col8_street==$col6_settlement) {}
            else $loaddress=$loaddress." ".$col8_street;
            // первая попытка найти адрес
            if(stristr($loaddress,'Волгоградская область')) $searchAddress=$loaddress;
            else $searchAddress='Волгоградская область '.$loaddress;
            //e($searchAddress);
            $lll=coordFix($searchAddress);
            if($lll==''){
                // вторая попытка найти адрес (без улицы и дома)
                if(stristr($loaddress,'Волгоградская область')) $searchAddress=$col5_area." ".$col6_settlement;
                else $searchAddress='Волгоградская область '.$col5_area." ".$col6_settlement;
                $lll=coordFix($searchAddress);                
            }else{
            }
            list ($llng, $llat) = explode(" ",$lll);
            qSQL("INSERT INTO ps_olayers(lay_type,otype,oname,oaddress,
                odesc,lat,lng,dateinbegin,dateend,uid,place_id)
            VALUES ('ud','" . $lotype . "','" . $loname . "','" . $loaddress .
                    "','".iconv('UTF-8', 'CP1251',cVal($aSheet, 10, $ri, 'String')."/".cVal($aSheet, 9, $ri, 'String')." ".
                            cVal($aSheet, 3, $ri, 'String')." ".cVal($aSheet, 2, $ri, 'String')."/".
                            cVal($aSheet, 12, $ri, 'String')." ".cVal($aSheet, 16, $ri, 'String').":".
                            cVal($aSheet, 17, $ri, 'String').":".cVal($aSheet, 18, $ri, 'String')."")
                    ."','" . 
                    $llat . "','" . $llng . "','2017-01-01','0000-00-00',1,'')");
            //$oid = mysql_insert_id();
        }
    }
}
// импорт СПД портов
function importSPDPort($fn) {
    exit();
    //print_r("\n-- $fn --\n");
    $pExcel = PHPExcel_IOFactory::load($fn);
    echo "<br>$fn загрузка с 5-й строки";
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    qSQL("delete from com_obj where lay_type=101");
    //qSQL("delete from com_obj where oid>60696");
    for ($ri = 5; $ri <= $highestRow ; $ri++) {
    //for ($ri = 8; $ri <= 100; $ri++) {
        $loname = iconv('UTF-8', 'CP1251', cVal($aSheet, 1, $ri, 'String'));
        $lotype = iconv('UTF-8', 'CP1251', str_replace("'", "", cVal($aSheet, 10, $ri, 'String')));
        if (strlen($loname) > 2 and strlen($lotype) > 2) { // т.е. не "''"        
            $col5_area=trim(iconv('UTF-8', 'CP1251', '' .cVal($aSheet, 6, $ri, 'String')));
            // первая попытка найти адрес
            if(stristr($col5_area,'Волгоградская область')) $searchAddress=$col5_area;
            else $searchAddress='Волгоградская область '.$col5_area;
            e($searchAddress);
            $lll=coordFix($searchAddress);
            if($lll==''){
                // вторая попытка найти адрес (без улицы и дома)
                //if(stristr($loaddress,'Волгоградская область')) $searchAddress=$col5_area." ".$col6_settlement;
                //else $searchAddress='Волгоградская область '.$col5_area." ".$col6_settlement;
                //$lll=coordFix($searchAddress);                
            }else{
            }
            list ($llng, $llat) = explode(" ",$lll);
            qSQL("INSERT INTO com_obj(lay_type,otype,oname,oaddress,
                odesc,lat,lng,dateinbegin,dateend,uid,place_id,mid)
            VALUES (101,'" . $lotype . "','" . $loname . "','" . $col5_area .
                "','".iconv('UTF-8', 'CP1251',
                        cVal($aSheet, 13, $ri, 'String').":".cVal($aSheet, 16, $ri, 'String')." ".
                        cVal($aSheet, 11, $ri, 'String')." ".cVal($aSheet, 4, $ri, 'String')."")
                ."','" . 
                $llat . "','" . $llng . "','2017-01-01','0000-00-00',900,'',0)");
//                    $llat . "','" . $llng . "','2017-01-01','0000-00-00',". $row_users['uid'] .",'')");
            //$oid = mysql_insert_id();
        }
    }
}
// импорт РШ
function importDistBox($fn) {
    $pExcel = PHPExcel_IOFactory::load($fn);
    echo "<br>$fn загрузка с 2-й строки";
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    qSQL("delete from com_obj where lay_type=105");
    for ($ri = 2; $ri <= $highestRow ; $ri++) {
    //for ($ri = 2; $ri <= 10; $ri++) {
        $loname = iconv('UTF-8', 'CP1251', cVal($aSheet, 2, $ri, 'String'));
        $lotype = iconv('UTF-8', 'CP1251', str_replace("'", "", cVal($aSheet, 0, $ri, 'String')));
        if (strlen($loname) > 2 and strlen($lotype) > 2) { // т.е. не "''"        
            $col5_area=trim(iconv('UTF-8', 'CP1251', '' .cVal($aSheet, 4, $ri, 'String')));
            // первая попытка найти адрес
            if(stristr($col5_area,'Волгоградская область')) $searchAddress=$col5_area;
            else $searchAddress='Волгоградская область '.$col5_area;
            e($searchAddress);
            $lll=coordFix($searchAddress);
            if($lll==''){
                // вторая попытка найти адрес (без улицы и дома)
                //if(stristr($loaddress,'Волгоградская область')) $searchAddress=$col5_area." ".$col6_settlement;
                //else $searchAddress='Волгоградская область '.$col5_area." ".$col6_settlement;
                //$lll=coordFix($searchAddress);                
            }else{
            }
            list ($llng, $llat) = explode(" ",$lll);
            qSQL("INSERT INTO com_obj(lay_type,otype,oname,oaddress,
                odesc,lat,lng,dateinbegin,dateend,uid,place_id,mid)
            VALUES (105,'" . $lotype . "','" . $loname . "','" . $col5_area .
                "','АТС ".iconv('UTF-8', 'CP1251',cVal($aSheet, 0, $ri, 'String')." ".cVal($aSheet, 1, $ri, 'String'))
                ." ёмк.:".iconv('UTF-8', 'CP1251',cVal($aSheet, 5, $ri, 'String')." r=".cVal($aSheet, 6, $ri, 'String'))
                ."м','" .$llat . "','" . $llng . "','2017-01-01','0000-00-00',900,'',0)");
//                    $llat . "','" . $llng . "','2017-01-01','0000-00-00',". $row_users['uid'] .",'')");
            //$oid = mysql_insert_id();
        }
    }
}
////////////////////////////////////////////////////////////////////////////////
// импорт СМР
function importSMR($fn) {
    echo "<br>$fn загрузка с 2-й строки";
    $pExcel = PHPExcel_IOFactory::load($fn);
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    //SQL("delete from ps_smet_calc")->commit();
    for ($ri = 2; $ri <= $highestRow ; $ri++) {
    //for ($ri = 2; $ri <= 10; $ri++) {
        $col0 = iconv('UTF-8', 'CP1251', cVal($aSheet, 0, $ri, 'String'));
        $col1 = iconv('UTF-8', 'CP1251',    preg_replace("/ {2,}/"," ",cVal($aSheet, 1, $ri, 'String'))     );
        $col2 = iconv('UTF-8', 'CP1251', cVal($aSheet, 2, $ri, 'String'));
        
        $strbuffer=preg_replace("/\s+/"," ",cVal($aSheet, 3, $ri, 'String'));
        $strbuffer=preg_replace("/ {2,}/"," ",$strbuffer);
        $col3 = iconv('UTF-8', 'CP1251',$strbuffer);
        
        $col4 = iconv('UTF-8', 'CP1251', cVal($aSheet, 4, $ri, 'String'));
        $col5 = iconv('UTF-8', 'CP1251', cVal($aSheet, 5, $ri, 'String'));
        $col6 = iconv('UTF-8', 'CP1251', cVal($aSheet, 6, $ri, 'String'));
        $col7 = iconv('UTF-8', 'CP1251', cVal($aSheet, 7, $ri, 'String'));
               
        SQL("INSERT INTO ps_smet_calc(mgroup,pgroup,name,ed,price,coment,datebegin)
        VALUES ('" . $col0 . "','" . $col1 . "','" . $col3 . "','" . $col5 . "','" . $col6 . "','" . $col4 . "',now())")->commit();
    }
    echo "<br>Загружено ". ($highestRow-1) ." строк";
}
////////////////////////////////////////////////////////////////////////////////
// Загрузка операторов
function importUser($fn) {
    echo "<br>$fn загрузка с 2-й строки";
    $pExcel = PHPExcel_IOFactory::load($fn);
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    SQL("delete from ps_users where uid>912")->commit();
    $numLoadRecord=0;
    for ($ri = 2; $ri <= $highestRow ; $ri++) {
    //for ($ri = 2; $ri <= 10; $ri++) {
        $c[0] = iconv('UTF-8', 'CP1251', cVal($aSheet, 0, $ri, 'String'));
        $c[1] = iconv('UTF-8', 'CP1251', cVal($aSheet, 1, $ri, 'String'));
        $c[2] = iconv('UTF-8', 'CP1251', cVal($aSheet, 2, $ri, 'String'));
        $c[3] = iconv('UTF-8', 'CP1251', cVal($aSheet, 3, $ri, 'String'));
        $c[4] = iconv('UTF-8', 'CP1251', cVal($aSheet, 4, $ri, 'String'));
        $c[5] = iconv('UTF-8', 'CP1251', cVal($aSheet, 5, $ri, 'String'));
        $c[6] = iconv('UTF-8', 'CP1251', cVal($aSheet, 6, $ri, 'String'));
        $c[7] = iconv('UTF-8', 'CP1251', cVal($aSheet, 7, $ri, 'String'));
        $c[8] = iconv('UTF-8', 'CP1251', cVal($aSheet, 8, $ri, 'String'));
        $c[0] = iconv('UTF-8', 'CP1251', cVal($aSheet, 0, $ri, 'String'));
        // дополнительный критерий выхода из цикла
        if(empty($c[0])) continue;
        //
        $min = 8; // минимальное количество символов
        $max = 8; // максимальное количество символов	
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
        echo $c[0]." ".$pwd2."<br>";
        //
        $min = 32; // минимальное количество символов
        $max = 32; // максимальное количество символов	
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
        $result_insert = qSQL("INSERT INTO ps_users (ugroup,login,pass,fio,name,surname,last_name,
                datebegin,email,ip,phone_work,phone,status,question,answer,hash,service,service2,rid)
                value('" . 6 . "', '" . $c[0] . "', '" . md5($pwd2) . "', '" . 
                $c[1] . "', '" . $c[3] . "', '" . $c[2] . "', '" . $c[4] . "', '2018-01-01', '" . $c[5] . "', '', '" . $c[6] . "', '" . 
                    "','" . 1 . "', '1', 'gzp', '" . $pwd3 . "', '" . "', '" . "', '" . 0 . "')");
            if ($result_insert == TRUE) {
                echo "<p><img src='./images/check.gif' align='absmiddle'> <b>Пользователь </b><b style='color: green;'>" . $_POST["fio"] . "</b> <b>успешно добален в БД.</b></b><br>";
            } else
            echo "<p><img src='./images/cross.gif' align='absmiddle'> <b>Пользователь </b><b style='color: red;'>" . $_POST["fio"] . "</b> <b>уже есть БД.</b></b><br>";
        //exit();
    }
    echo "<br>Загружено ".$numLoadRecord ." строк";
}
function importSPARK($fn){
    //
    //Процедура загрузки данных из СПАРКа
    // на вход - имя файла
    //
    echo "<br>Грузим файл $fn c данными СПАРКа";
    echo "<br> Старые данные будут удалены";
    $pExcel = PHPExcel_IOFactory::load($fn);
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    SQL("delete from private_sector.tmp_sparkData")->commit();
    $numLoadRecord=0;
    for($rowNum=7;$rowNum<$highestRow; $rowNum++){
        $name = iconv('UTF-8', 'CP1251', cVal($aSheet, 1, $rowNum, 'String'));
        $adrStr = iconv('UTF-8', 'CP1251', cVal($aSheet, 3, $rowNum, 'String'));
        $inn = iconv('UTF-8', 'CP1251', cVal($aSheet, 5, $rowNum, 'String'));
        $Activity = iconv('UTF-8', 'CP1251', cVal($aSheet, 8, $rowNum, 'String'));
        $PravForm = iconv('UTF-8', 'CP1251', cVal($aSheet, 10, $rowNum, 'String'));
        $viruchka = iconv('UTF-8', 'CP1251', cVal($aSheet, 14, $rowNum, 'Number'));
        $regnum = iconv('UTF-8', 'CP1251', cVal($aSheet, 2, $rowNum, 'String')).dechex(crc32($adrStr));
        $sqlStr = "INSERT INTO `private_sector`.`tmp_sparkData`
            (`INN`,
            `name`,
            `adrStr`,
            `Activity`,
            `PravForm`,
            `viruchka`,
            `shirota`,
            `dolgota`,
            `regnum`)
            VALUES
            ('".$inn."','".
            $name ."','".
            $adrStr ."','".
            $Activity ."','".
            $PravForm ."',".
            emty($viruchka)?0.0:$viruchka .",0.0,0.0,'".$regnum. "');" ; //широта и долгода по умолчанию =0.0
        SQL($sqlStr)->commit();
        $numLoadRecord++;
    }    
    echo "<br>Загружено ".$numLoadRecord ." строк";
}
////////////////////////////////////////////////////////////////////////////////
// Загрузка справочника ОС
function importRefComMatLoad($fn) {
    echo "<br>$fn загрузка с 27-й строки";
    $pExcel = PHPExcel_IOFactory::load($fn);
    $pExcel->setActiveSheetIndex(0);
    $aSheet = $pExcel->getActiveSheet();
    $highestRow = $aSheet->getHighestRow();
    $highestColumn = $aSheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    SQL("delete from ref_com_mat")->commit();
    $numLoadRecord=0;
    for ($ri = 27; $ri <= $highestRow ; $ri++) {
    //for ($ri = 2; $ri <= 10; $ri++) {
        $mgroup = iconv('UTF-8', 'CP1251',    preg_replace("/ {2,}/"," ",cVal($aSheet, 1, $ri, 'String'))     );
        $pgroup = iconv('UTF-8', 'CP1251',    preg_replace("/ {2,}/"," ",cVal($aSheet, 2, $ri, 'String'))     );
        $name = iconv('UTF-8', 'CP1251', cVal($aSheet, 3, $ri, 'String'));
        $cnaname = iconv('UTF-8', 'CP1251', cVal($aSheet, 4, $ri, 'String'));
        $exname = iconv('UTF-8', 'CP1251', cVal($aSheet, 6, $ri, 'String'));
        $subexname = iconv('UTF-8', 'CP1251', cVal($aSheet, 7, $ri, 'String'));
        $envname = iconv('UTF-8', 'CP1251', cVal($aSheet, 10, $ri, 'String'));
        $ownername = iconv('UTF-8', 'CP1251',    preg_replace("/ {2,}/"," ",cVal($aSheet, 11, $ri, 'String'))     );
        $techname = iconv('UTF-8', 'CP1251', cVal($aSheet, 12, $ri, 'String'));
        $buildname = iconv('UTF-8', 'CP1251', cVal($aSheet, 14, $ri, 'String'));
        $cxname = iconv('UTF-8', 'CP1251', cVal($aSheet, 15, $ri, 'String'));
        $unit = iconv('UTF-8', 'CP1251', cVal($aSheet, 16, $ri, 'String'));
        $capacity1 = iconv('UTF-8', 'CP1251', cVal($aSheet, 17, $ri, 'String'));
        $capacity2 = iconv('UTF-8', 'CP1251', cVal($aSheet, 18, $ri, 'String'));
        $price = iconv('UTF-8', 'CP1251', cVal($aSheet, 24, $ri, 'String'));
        $man_hours = 0.0;//iconv('UTF-8', 'CP1251', cVal($aSheet, 20, $ri, 'String'));

        // дополнительный критерий выхода из цикла
        if(empty($mgroup) or empty($price)) continue;
        
        $cnaid=rSQL("select cnaid FROM cn_area where trim(fullname) like trim('".$cnaname."')")["cnaid"];
        if(empty($cnaid)) $cnaid=-1;
        $eid=rSQL("select eid FROM expense where trim(comment) like trim('".$exname."')")["eid"];
        if(empty($eid)) $eid=-1;
        $subeid=rSQL("select seid FROM subexpense where upper(trim(comment)) like trim('".mb_strtoupper(trim($subexname),"CP1251") ."')")["seid"];
        //$subeid=rSQL("select seid FROM subexpense where trim(comment) like trim('".$subexname ."')")["seid"];
        if(empty($subeid)) $subeid=-1;
        $ceid=rSQL("select ceid FROM cn_envir where trim(cename) like trim('".$envname ."')")["ceid"];
        if(empty($ceid)) $ceid=-1;
        $technology=rSQL("select id FROM ps_teh_podkl where trim(name) like trim('".$techname ."')")["id"];
        if(empty($technology)) $technology=-1;
        
        SQL("INSERT INTO ref_com_mat(mgroup,pgroup,name,unit,material,
            price,comment,man_hours,machine_hour,
            datebegin,dateend,uid,dateedit,cetid,
            capacity1,cnaid,ceid,capacity2,seid,option1,option2,
            technology,eid,oid,oname,cxid,cxcomment,subeid,bid)
        VALUES ('".$mgroup ."','".$pgroup ."','".$name ."','".$unit ."',0.0,'".
            $price ."','','".$man_hours ."',0,NULL,NULL,900,now(),-1,'". 
            $capacity1 ."',".$cnaid .",".$ceid .",'".$capacity2 ."',-1,'','',". 
            "".$technology .",".$eid .",1,'',-1,'',".$subeid .",2)")->commit();
        $numLoadRecord++;
        //exit();
    }
    echo "<br>Загружено ".$numLoadRecord ." строк";
}
////////////////////////////////////////////////////////////////////////////////
// координаты СПД портов
// $lay_type=102 - АТС, 101 порд СПД, 105 - РШ
function obATSCoord($lay_type) {
    $h = 0;
/*
UPDATE com_obj co SET mid=
    (SELECT max(mc.id) FROM ps_mctet mc inner join ps_ltc ltc on mc.id=ltc.mid 
	where ltc.name like concat(SUBSTRING_INDEX(SUBSTRING_INDEX(co.oaddress,',',1),' ',1),'%') )
    WHERE co.lay_type=105 and co.mid is null
!!! так неправильно - concat('%',SUBSTRING_INDEX(SUBSTRING_INDEX(co.oaddress,',',1),' ',1),'%')
!!! Николаевский -> Новониколаевский


UPDATE com_obj SET oaddress=REPLACE(oaddress,' г.Волжский ','Волжский, ') WHERE oaddress like ' г.Волжский %'    
UPDATE com_obj SET oaddress=concat('Котельниковский, ',oaddress) where oaddress like '%Котельниково г,%'
*/
    //$result_cids = qSQL("SELECT * FROM com_obj WHERE trim(lat)='' or trim(lng)=''");
    //$result_cids = qSQL("SELECT * FROM com_obj WHERE lat=' ' or lng=' '");
    //$result_cids = qSQL("SELECT * FROM com_obj where lay_type='".$lay_type."' and place_id not in ('exact','number','near','manual') ");
    if($lay_type==106){
        $result_cids = qSQL("SELECT * FROM addrcache where lat='' or lat is null"); //limit 0,100000");
        while ($row_cids = mysql_fetch_array($result_cids)) {
            echo "<br>-----------------------<br><b>Абонент: ".$row_cids["comment"].
                " Поиск по Адресу: [".$row_cids["locality"] ." ".$row_cids["street"] ." ".$row_cids["building"] ."] в Yandex</b>";
                str_replace('обл. ВОЛГОГРАДСКАЯ', '', $row_cids["locality"]);
                $searchaddress=addressFormat(str_replace('обл. ВОЛГОГРАДСКАЯ', '', $row_cids["locality"]) ." ".$row_cids["street"] ." ".$row_cids["building"]);
                d($searchaddress);d("<br>");
                // yandex.ru
                $string = getUrl('https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)));
                //print_r($string);
                $xml = simplexml_load_string($string);
                $status = $xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found;
                $foundresults=$xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->results;
                echo "<br>Количество объектов: [" . $status . "/" .$foundresults . "]";
                if ($status > 0) {
                    $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
                    echo " Координаты объекта: [" . $cords . "]";
                    $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
                    //list ($lat, $lng) = explode(" ", $cords);
                    list ($lng, $lat) = explode(" ", $cords);
                    $formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // Форматированный адрес Yandex
                    $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
                    $post_index = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->postal_code; // Почтовый индекс
                    $place_id = 'YANDEX'; // Уникальный идентификатор объекта в Yandex
                    $precision = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision; // Точность определения координаты. exact - точный, near -Найден дом с номером, близким к запрошенному
                    echo "<br>Форматированный адрес Yandex: [" . $formatted_address . "] индекс: [" . $post_index . "] Точность: [" . $precision . "]";
                    $_latlng = $lat . ":" . $lng;
                    $result_update1 = qSQL("UPDATE addrcache SET lat='".$lat ."', lng='".$lng ."', exactness='".$precision . 
                        "' WHERE aid=".$row_cids["aid"] ."");
                } else { // google.com
                }
                $h++;
            //}
        }
    } else {
        $result_cids = qSQL("SELECT * FROM com_obj where lay_type='".$lay_type."' ");
        while ($row_cids = mysql_fetch_array($result_cids)) {
            echo "<br>-----------------------<br><b>Объект: ".$row_cids["oname"]." Поиск по Адресу: [" . $row_cids["oaddress"] . "] в Yandex</b>";
            //if (1 or trim($row_cids["latlng"]) == ''){
                $searchaddress=addressFormat($row_cids["oaddress"]);
                d($searchaddress);d("<br>");
                // yandex.ru
                $string = getUrl('https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)));
                //print_r($string);
                $xml = simplexml_load_string($string);
                $status = $xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found;
                $foundresults=$xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->results;
                echo "<br>Количество объектов: [" . $status . "/" .$foundresults . "]";
                if ($status > 0) {
                    $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
                    echo " Координаты объекта: [" . $cords . "]";
                    $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
                    //list ($lat, $lng) = explode(" ", $cords);
                    list ($lng, $lat) = explode(" ", $cords);
                    $formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // Форматированный адрес Yandex
                    $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
                    $post_index = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->postal_code; // Почтовый индекс
                    $place_id = 'YANDEX'; // Уникальный идентификатор объекта в Yandex
                    $precision = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision; // Точность определения координаты. exact - точный, near -Найден дом с номером, близким к запрошенному
                    echo "<br>Форматированный адрес Yandex: [" . $formatted_address . "] индекс: [" . $post_index . "] Точность: [" . $precision . "]";
                    $_latlng = $lat . ":" . $lng;
                    $result_update1 = qSQL("UPDATE com_obj SET lat='" . $lat . "', lng='" . $lng . "', place_id='" . $precision . 
                        "' WHERE oid='" . $row_cids["oid"] . "'");
                } else { // google.com
                }
                $h++;
            //}
        }
    }
}
////////////////////////////////////////////////////////////////////////////////
function callcoord(){
// Поиск координат заявок по адресу
    $cursor = SQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
        FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE trim(psl.latlng)='' order by psl.arm_id desc");
    //$cursor = SQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
    //    FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE trim(psl.latlng)='' and cs='2'");
    //$cursor = SQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
    //    FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE psl.arm_id in ('18233507')");
    //$cursor = SQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
    //    FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE psld.location_type not in ('exact','number','near','manual')");
    //$cursor = SQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
    //    FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE length(trim(psl.corp))<4 and trim(psl.corp)!=''");
    while ($cursor->assoc()) {
        $searchaddress=addressFormat6($cursor->r["region"],$cursor->r["ltc"],
                $cursor->r["settlement"],$cursor->r["ul"],$cursor->r["home"],$cursor->r["corp"]);
        //echo "".$cursor->r["list_id"]." ".$cursor->r["location_type"]." ".$searchaddress."<br>";
        list($_latlng, $post_index, $formatted_address, $precision)=getCoord($searchaddress);
        echo "<br>".$_latlng." ".$post_index." ".$formatted_address." ".$precision."<br>";

        SQL("UPDATE ps_list_dop SET formatted_address='" . $formatted_address . "', place_id='" . $place_id . 
            "', location_type='" . $precision . "' WHERE list_id='" . $cursor->r["list_id"] . "'")->commit();
        SQL("UPDATE ps_list SET latlng='" . $_latlng . "' WHERE list_id='" . $cursor->r["list_id"] . "'")->commit();
    }
    $cursor->free();
}
////////////////////////////////////////////////////////////////////////////////
// УСТАРЕЛО
function callcoord_old(){
// Поиск координат по адресу
        //echo "<script src=\"https://maps.googleapis.com/maps/api/js?key=AIzaSyCcECW3bzY2r-yyC8NEU1OSAAXNB-o-d7s&libraries=geometry&callback=initMap\" async defer></script><br>";
        $h = 0;
        //$result_cids = qSQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
        //    FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE trim(psl.latlng)=''");
        $result_cids = qSQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
            FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE psl.arm_id in ('17491227')");
        //$result_cids = qSQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
        //    FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE psld.location_type not in ('exact','number','near','manual')");
        //$result_cids = qSQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
        //    FROM ps_list psl left join ps_list_dop psld using(list_id) WHERE length(trim(psl.corp))<4 and trim(psl.corp)!=''");
        
        while ($row_cids = mysql_fetch_array($result_cids)) {
            $searchaddress=trim($row_cids["region"])." ||| ".explode(" ",trim($row_cids["ltc"]))[0] ." ".trim($row_cids["settlement"])." ||| ".trim($row_cids["ul"])." ||| ".trim($row_cids["home"]);
            if(empty(trim($row_cids["corp"]))){
                
            }else{
                $searchaddress.=" ||| ".trim($row_cids["corp"]);
            }               
            //echo "<br>-----------------------<br><b>ARM_ID: ".$row_cids["arm_id"]." Поиск по Адресу: [" . $row_cids["device_address"] . "] в Yandex</b>";
            echo "<br>-----------------------<br><b>ARM_ID: ".$row_cids["arm_id"]." Поиск по Адресу: [" . $searchaddress . "] в Yandex</b>";
            if (1 or trim($row_cids["latlng"]) == ''){
                // поиск по частям адреса из заявок АРМ
                $searchaddress=addressFormat2($searchaddress);
                d($searchaddress);//d("<br>");
                // yandex.ru
                $string = getUrl('https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)));
                //print_r($string);
                $xml = simplexml_load_string($string);
                $status=$xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found;
                $precision = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision; // Точность определения координаты. exact - точный, near -Найден дом с номером, близким к запрошенному
                
                //$foundresults=$xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->results;
                $foundresults=$status;
                        
                echo "<br>Количество объектов: [" . $foundresults . "]";
                /*$formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // Форматированный адрес Yandex
                $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
                echo "<br>Форматированный адрес Yandex: [" . $formatted_address . "]";
                if ($status > 1) { // повторный поиск - без дома
                    $searchaddress=addressFormat(trim($row_cids["region"])." ".explode(" ",trim($row_cids["ltc"]))[0] ." ".
                        trim($row_cids["settlement"])." ".trim($row_cids["ul"]));
                    d($searchaddress);d("<br>");
                    // yandex.ru
                    $string = getUrl('https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)));
                    //print_r($string);
                    $xml = simplexml_load_string($string);
                    $status = $xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found;
                    echo "<br>Количество объектов: [" . $status . "]";
                }*/
                $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
                echo "Координаты объекта: [" . $cords . "]";
                //if ($status > 0) {
                //if ($precision=='exact' or $precision=='number' or $precision=='near') {
                    $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
                    //list ($lat, $lng) = explode(" ", $cords);
                    list ($lng, $lat) = explode(" ", $cords);
                    $formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // Форматированный адрес Yandex
                    $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
                    $post_index = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->postal_code; // Почтовый индекс
                    $place_id = 'YANDEX'; // Уникальный идентификатор объекта в Yandex
                    echo "<br>Адрес Yandex: [" . $formatted_address . "] индекс: " . $post_index . " Точность: " . $precision . "";
                    // GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision // exact - Точное соответствие
                    $_latlng = $lat . ":" . $lng;
                    $result_update1 = qSQL("UPDATE ps_list_dop SET formatted_address='" . $formatted_address . 
                            "', place_id='" . $place_id . "', location_type='" . $precision . "' WHERE list_id='" . $row_cids["list_id"] . "'");
//                        if (@$NEW_device_address and $NEW_device_address != '')
//                            $adds = ", device_address='" . $NEW_device_address . "'";
                    if ($row_cids["post_index"] == '' or ! $row_cids["post_index"])
                        $adds = @$adds . ", post_index='" . ($post_index ? $post_index : 0) . "'";
//                        $result_update1 = qSQL("UPDATE ps_list SET latlng='" . $_latlng . "'" . @$adds . " WHERE list_id='" . $row_cids["list_id"] . "'");
                    $result_update1 = qSQL("UPDATE ps_list SET latlng='" . $_latlng . "' WHERE list_id='" . $row_cids["list_id"] . "'");
//                        $adds = '';
//                        $NEW_device_address = '';
                    for($i=1;$i<$foundresults;$i++){
                        echo "<br>вариант ". (1+$i) .": ".iconv('UTF-8', 'CP1251',$xml->GeoObjectCollection->featureMember[$i]->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted);
                    }
                //} else { // google.com
                    //$xml = simplexml_load_string(getUrl('http://maps.google.com/maps/api/geocode/xml?address=' .
                    //                urlencode(iconv('CP1251', 'UTF-8', $searchaddress)) . '&language=ru'));
                    //$status = $xml->status;
                        echo '<br>google.com<br>http://maps.google.com/maps/api/geocode/xml?address=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)) . '&language=ru';
                    $string = getUrl('http://maps.google.com/maps/api/geocode/xml?address=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)) . '&language=ru');

                    d($string);d("<br>");

                    $xml = simplexml_load_string($string);
                    //print_r($xml);
                    $status = $xml->status;
                    if ($status == "OK") {
                        echo "<br>нашлись координаты для " . $searchaddress;
                        $lat = $xml->result->geometry->location->lat;
                        $lng = $xml->result->geometry->location->lng;
                        $formatted_address = $xml->result->formatted_address; // Форматированный адрес гуглом
                        $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
                        $place_id = $xml->result->place_id; // Уникальный идентификатор объекта в гугл
                        $location_type = $xml->result->geometry->location_type; // Точность определения координаты. ROOFTOP - точный, с точным почтовым адресом.
                        $_latlng = $lat . ":" . $lng;
                        echo "<br>координаты = " . $_latlng;
                        /*$result_update1 = qSQL("update ps_list set latlng='" . $_latlng . "' where list_id='" . $row_cids["list_id"] . "'");
                        if (@$location_type == 'ROOFTOP') {
                            $add_claster = 1;
                        }
                        $result_update2 = qSQL("update ps_list_dop set formatted_address='" . @$formatted_address . 
                            "', place_id='" . @$place_id . "', location_type='" . @$location_type . "' where list_id='" . $row_cids["list_id"] . "'");*/
                        $k++;
                    } else
                        echo "<br><i style='color: red'>координаты НЕ определены !!!</i>";
                //}
                $h++;
            }
        }
}
?>