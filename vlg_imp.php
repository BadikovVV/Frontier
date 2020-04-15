<?php
//
// SET global sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'
// SET sql_mode = 'STRICT_ALL_TABLES,ERROR_FOR_DIVIZION_BY_ZERO' режим STRICT_ALL_TABLES - не разрешает даты '0000-00-00'
//
// импорт объектов ГТС
require_once 'vlg_imp_excel.php';

function choosefileload(){
    echo "
        Укажите файл: <input type='file' name='faudit'> 
        <input type='submit' value='Загрузить'>";
    //importMTCLTC("c:/Web/htdocs/ps/cs/buffer/Audit_zayavok_BIG.xls"); // загрузка МЦТЭТ/ЛТЦ
    //importATS("c:/Web/htdocs/ps/cs/buffer/Объекты_АТС.xls"); // загрузка объектов АТС
    //importRequest("/var/www/html/cs/buffer/Audit_zayavok20171128.xls");
    //exit();
    echo $_SERVER['DOCUMENT_ROOT'];
    if (@$_FILES['faudit']) { // Файл
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
        //prot("importRequest $targetFile");
        //$fileParts = pathinfo($change_fname);
        $test = move_uploaded_file($tempFile, $targetFile);
        if (@$test == TRUE){
            echo "<br><i style='color: green'>Файл ($targetFile) успешно скопирован на сервер</i>";
            //importATS($targetFile); 
            return $targetFile;
        }
        //exit();
    }
    return false;
}
function vlg_imp($mode){
    global $mySQLSchema,$mysqli;
    set_time_limit(0);
    switch($mode){
// добавлено для загрузки СПАРКа        
    case "sparkData":
        echo "<b>Загрузка  данных из СПАРКа</b>";
        //exit();
        echo "<form name='saddress' method='post' action='./?c=6&action=sparkData' enctype='multipart/form-data'>";
        if($loadFile=choosefileload()){
               importSPARK($loadFile);
        }
    break;
//        
    case "smraddload": // Загрузка (дополнение) СМР
        echo "<b>Загрузка (дополнение) СМР</b>";
        //exit();
        echo "<form name='saddress' method='post' action='./?c=7&action=smraddload' enctype='multipart/form-data'>";
        if($loadFile=choosefileload()){
            importSMR($loadFile);
        }
    break;
    case "refcommatload": // Загрузка справочника ОС
        echo "<b>Загрузка справочника ОС</b>";
        //exit();
        echo "<form name='saddress' method='post' action='./?c=7&action=refcommatload' enctype='multipart/form-data'>";
        if($loadFile=choosefileload()){
            importRefComMatLoad($loadFile);
        }
    break;
    case "ats":
        echo "<b>Загрузка объектов ГТС</b>
            <form name='saddress' method='post' action='./?c=6&action=imp_ats' enctype='multipart/form-data'>
            Укажите файл: <input type='file' name='faudit'> 
            <input type='submit' value='Загрузить'>";
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
            //prot("importRequest $targetFile");
            //$fileParts = pathinfo($change_fname);
            $test = move_uploaded_file($tempFile, $targetFile);
            if (@$test == TRUE){
                echo "<br><i style='color: green'>Файл ($targetFile) успешно скопирован на сервер</i>";
                //echo "<br><i style='color: green'>Файл успешно скопирован на сервер</i>";
                importATS($targetFile); 
                //importSPDPort($targetFile); 
            }
            //exit();
        }
        break;
    case "spd":
        echo "<b>Загрузка объектов ГТС</b>
            <form name='saddress' method='post' action='./?c=6&action=imp_ats' enctype='multipart/form-data'>
            Укажите файл: <input type='file' name='faudit'> 
            <input type='submit' value='Загрузить'>";
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
            //prot("importRequest $targetFile");
            //$fileParts = pathinfo($change_fname);
            $test = move_uploaded_file($tempFile, $targetFile);
            if (@$test == TRUE){
                echo "<br><i style='color: green'>Файл ($targetFile) успешно скопирован на сервер</i>";
                //echo "<br><i style='color: green'>Файл успешно скопирован на сервер</i>";
                //importATS($targetFile); 
                importSPDPort($targetFile); 
            }
            //exit();
        }
        break;
    case "dist_box":
        echo "<b>Загрузка РШ</b>
            <form name='saddress' method='post' action='./?c=6&action=imp_dist_box' enctype='multipart/form-data'>
            Укажите файл: <input type='file' name='faudit'> 
            <input type='submit' value='Загрузить'>";
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
            //prot("importRequest $targetFile");
            //$fileParts = pathinfo($change_fname);
            $test = move_uploaded_file($tempFile, $targetFile);
            if (@$test == TRUE){
                echo "<br><i style='color: green'>Файл ($targetFile) успешно скопирован на сервер</i>";
                importDistBox($targetFile); 
                //importUser($targetFile); 
            }
            //exit();
        }
        break;
    case "test":
        //d($_SERVER['DOCUMENT_ROOT'].'/../private/goto.txt');
        //
        $result = SQL("select * from ".$mySQLSchema.".ps_olayers where oid<15");
        while( $row = $result->fetch_assoc()){
            d($row['oname']." ".$row['odesc']);
        }
        mysqli_free_result($result); // Освобождаем используемую память 
        //
        $result = SQL("select * from ".$mySQLSchema.".ps_olayers where oid<15");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        foreach($rows as $row){
            d($row['oname']." ".$row['odesc']);
        }
        mysqli_free_result($result); // Освобождаем используемую память 
        //
        break;
    }
}
////////////////////////////////////////////////////////////////////////////////          
//
/*function vlg_imp_new_b2b($user_id){
    set_time_limit(0);
    echo "<b>Загрузка новых заявок B2B</b><hr>";
    echo "<p><form name='saddress' method='post' action='./?c=6&action=imp_new_b2b' enctype='multipart/form-data'>";
    echo "Укажите файл Аудита заявок АРМ: <input type='file' name='faudit'> ";
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
        d(@$_FILES['faudit']);
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
        $test = move_uploaded_file($tempFile, $targetFile);
        if (@$test == TRUE){
            echo "<br><i style='color: green'>Файл заявок ($targetFile) успешно скопирован на сервер.</i>";
            //echo "<br><i style='color: green'>Файл Аудита успешно скопирован на сервер...</i>";
            importRequest($targetFile);
        } else {
            echo "<br><i style='color: red'>Ошибка, файл заявок ($targetFile) не был скопирован на сервер!</i>";
            exit();
        }
        arm_buffer_stat(); // статистика по таблице ps_arm_buffer
        // Заполняем координаты
        $set_mctet=' ';
        if (isset($_POST["mctet"]) and trim($_POST["mctet"])!='выберите...')
            $set_mctet = " and ues_arm LIKE '%" . $_POST["mctet"] . "%'";
        // Копирование заявок построчное, т.к. необходимо проверить каждую на наличие в основной таблице и создать доп. атрибуты для новых записей
        echo "<br>Загружено заявок из Файла: <b>" . rSQL("SELECT count(*) cnt FROM ps_arm_buffer")["cnt"] . "</b><br>";
        //
        qSQL("update ps_arm_buffer set cs='B2B'");
        $result_taskl = qSQL("SELECT bufer_id, arm_id, device_address, technology, service FROM ps_arm_buffer where 1=1" . $set_mctet . 
                " and cs='B2B' and status_name<>'Тест'");
        echo "<br>Заявок для загрузки в БД после фильтрации: <b>" . mysql_num_rows($result_taskl) . "</b><br>";
        //
        //exit();
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
                $result_update = qSQL("UPDATE ps_list psl,ps_arm_buffer pab
                        SET psl.dateinbegin=pab.dateinbegin,psl.latlng=pab.latlng,psl.ues_arm=pab.ues_arm, 
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
                            psl.date_close_worder_iptv_kurs=pab.date_close_worder_iptv_kurs, psl.contact_phone=pab.contact_phone, 
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
                echo "<br><b style='color: orandge'>$i</b> - запись " . $row_taskl["arm_id"] . " (" . $row_taskl["bufer_id"] . ") ОБНОВЛЕНА ";
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
                    promt,date_open_worder_kurs,date_open_worder_iptv_kurs,worder_iptv_kurs,date_close_worder_iptv_kurs,contact_phone,
                    contact_person,fiz,ur,close_worder_installator,task_num_non_tvp,vip,cost_tp_spd,cost_tp_iptv,fio_create_dog,
                    card_number_access,card_number_access_iptv,cs,date_bind_card_onyma_spd,date_bind_card_onyma_iptv,
                    date_act_card_onyma_spd,date_act_card_onyma_iptv,end_time_install_wfm,area_wfm,create_rss,cpo,services,promo_action,
                    source_inf,source_inf_other,flag_migration,reason_rejection,nls,delivery 
                    FROM ps_arm_buffer WHERE ps_arm_buffer.bufer_id='" . $row_taskl["bufer_id"] . "';");
                $insert_count = @$insert_count + 1;
                $cord_id = mysql_insert_id();
                echo "<br><b style='color: green'>$i</b> - запись " . $row_taskl["arm_id"] . " (" . $row_taskl["bufer_id"] . ") 
                    <b style='color: green;'>ДОБАВЛЕНА (новый ID: $cord_id)</b> дублей найдено = " . mysql_num_rows($result_check);
            } else
                echo "<br><b style='color: red'>Для заявки " . $row_taskl["arm_id"] . " (" . $row_taskl["bufer_id"] . ") нет событий при загрузке</b>";
            // Создадим доп информацию для этой записи, если доп. информация отсутствует
            if (!@$row_check["list_id"] or @ $row_check["list_id"] == 0)
                $row_check["list_id"] = $cord_id;
            $result_check2 = qSQL("SELECT list_id, arm_id, technology, service, status_name FROM ps_list where arm_id='" . $row_taskl["arm_id"] . 
                    "' and technology='" . $row_taskl["technology"] . "' and service='" . $row_taskl["service"] . "'");
            $row_check2 = mysql_fetch_array($result_check2);
            $result_sdop = qSQl("SELECT * FROM ps_list_dop where list_id='" . $row_check2["list_id"] . "' and arm_id='" . $row_check2["arm_id"] . "'");
            if (mysql_num_rows($result_sdop) == 0) {
                // Заполним статус:
                $result_slist = qSQL("SELECT pas.ps_status set_status FROM ps_list pls, ps_arm_status pas where pls.list_id='" . $row_check2["list_id"] . 
                        "' and pls.status_name=pas.status_name");
                if($row_slist = mysql_fetch_array($result_slist)){
                    $row_slist=$row_slist['set_status'];
                } else { // такой статус АРМ не найден
                    $row_slist=10;
                }
                $result_cdop = qSQL("INSERT INTO ps_list_dop (lid,list_id,status,arm_id,
                        comment,file_smeta,zatrat_smr,dev_summ,shkaf_42u,shassi_olt,kol_ports,spd,
                        difficult_mc,difficult_rs,difficult_abl,difficult_abv,
                        install,month_pay,pon_flag,formatted_address,place_id,location_type,
                        claster_id,tpid,service_id) 
                    values
                        (NULL,'" . $row_check2["list_id"] . "','" . $row_slist . "','" . $row_check2['arm_id'] . "', 
                        '', 0, 0, 0, 0, 0, 0, 0, 
                        0, 0, 0, 0, 
                        0, 0, 0, '', '', '', 
                        0, -1, NULL)");
                // открываем маршрутизацию заявки
                //qSQL("INSERT INTO callpath(id,lp_id,status,uid,`group`,checkdate,object_type,comment)
                //    VALUES(NULL,'".$cord_id."','". $row_slist ."',".$user_id.",5,'20180101',1,'')");
            }
            // Получим Координаты GOOGLE и заполним их
            $i++;
        }
        // отправляем уведомление в ТБ (ugroup=5)
        if($insert_count>0 or $update_count>0){
            eMail(rSQL("SELECT email FROM ps_users where ugroup=5")["email"],"Частный сектор",
                "В БД \"Частного сектора\" загружено ".$insert_count." заявок\n".
                "обновлено ".$update_count." заявок\n".
                "Вам необходимо обработать эти заявки\n".
                "");
        }
    }
}*/

?>