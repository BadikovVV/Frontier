<?php
// выгрузка отчета по заявкам (на основе фильтра reestr.php),
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/html; charset=windows-1251"); //charset=utf8");
chdir("/var/www/html/");
include "db_connect.php";
mysql_select_db("private_sector") or die("Could not select database");
mysql_query("SET NAMES 'cp1251'");
mysql_query("SET CHARACTER SET 'cp1251'");
$file_path = "/var/www/html/cs/";
require_once "PHPExcel/Classes/PHPExcel.php";
require_once 'vlg_util.php';
require_once "func.inc.php";

$udate2 = date("Y-m-d");
$udate = date("d.m.Y");
//$udate_time2 = date("d.m.Y H:i:s");
$udate_time3 = date("d.m.Y H-i-s");
//$udate_time = date("Y-m-d H:i:s");
////////////////////////////////////////////////////////////////////////////////
//
class CExcel{
    var $excel;
    var $aSheet;
    var $filename;
    var $cRow;
    var $cCol;
    var $cellStyle;
//
    function __construct($filename)
    {
        $this->filename = $filename;
        $this->excel = new PHPExcel();
        $this->excel->setActiveSheetIndex(0);
        $this->aSheet = $this->excel->getActiveSheet();
        $this->cCol=0;
        $this->cRow=1;
        $this->cellStyle=false;
    }
// изменение ячейки ($col, $row)
    function scVal($col, $row, $type, $val) {
        //d($val);
        $this->cCol=$col;
        $this->cRow=$row;
        $this->setCellVal($this->cCol, $this->cRow, $type, $val);
        return true;
    }
// выгрузка массива значений в $row-строку Excel, начиная с $col
    function sACVal($col, $row, $type, $arrVal) {
        //d($val);
        $this->cCol=$col;
        $this->cRow=$row;
        foreach ($arrVal as &$value){
            $this->setCellVal($this->cCol, $this->cRow, $type, $value);
            $this->cCol++;
        }
        return true;
    }
// изменение ячейки ($this->cCol++, $this->cRow)
    function sncVal($type, $val) {
        $this->cCol++;
        $this->setCellVal($this->cCol, $this->cRow, $type, $val);
        return true;
    }
// изменение ячейки ($col, $row)
    function setCellVal($col, $row, $type, $val) {
        if($this->cellStyle)   $this->aSheet->getStyleByColumnAndRow($col, $row)->applyFromArray($this->cellStyle);
        switch ($type) {
            case 'trimConvString':
                $this->aSheet->setCellValueByColumnAndRow($col,$row,iconv('CP1251', 'UTF-8',trim($val)));
                break;
            case 'String':
                $this->aSheet->setCellValueByColumnAndRow($col,$row,$val);
                break;
            case 'Number':
                break;
            case 'Date':
                //$val = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                //$this->aSheet->getStyleByColumnAndRow($col,$row)->getAlignment()->
                //setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                break;
        }
        return true;
    }    
// сохранение объекта PHPExcel в файл
    function save() {
        //d($this->filename);
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save($this->filename);
        return true;
    }
}
// ^^^ Class CExcel ^^^
////////////////////////////////////////////////////////////////////////////////
if ($ip != "10.147.1.19" or ! @$_GET["its_server"]) {
    if (@$_COOKIE['rtcomug'][0] and @ $_COOKIE['rtcomug'][0] != '' and @ $_COOKIE['rtcomug'][1] and @ $_COOKIE['rtcomug'][1] != '' and @ $_GET['action'] != "logout") {// Если в куках логин и пароль
        $result_users = qSQL("SELECT * FROM ps_users WHERE login='" . $_COOKIE['rtcomug'][0] . "'");
        if (mysql_num_rows($result_users) == 1) {
            $row_users = mysql_fetch_array($result_users);
            if ($row_users["pass"] == $_COOKIE['rtcomug'][1]) {
                if ($row_users["status"] == "1") {
                    define("LOGINED", "TRUE", TRUE);  // Активный пользователь
                    /*$q_group = "SELECT * FROM ggroup WHERE id='" . $row_users["ugroup"] . "';";
                    $result_group = mysql_query($q_group) or die("Query failed. gruop");
                    $row_group = mysql_fetch_array($result_group);*/
                }
            }
            //$result_group = qSQL("SELECT * FROM ggroup WHERE id='" . $row_users["ugroup"] . "'");
            //$row_group = mysql_fetch_array($result_group);
        }
    }
} else {
    define("LOGINED", "TRUE", TRUE);
    //$result_users = qSQL("SELECT * FROM ps_users WHERE id='1'");
    //$row_users = mysql_fetch_array($result_users);
    //$result_group = qSQL("SELECT * FROM ggroup WHERE id='" . $row_users["ugroup"] . "'");
    //$row_group = mysql_fetch_array($result_group);
}
//d($_REQUEST);d("<br>");
if (defined("LOGINED") == TRUE) { // DEFINE TRUE
    function hash_user($length = 14, $base = 36) {
        list($usec, $sec) = explode(' ', microtime());
        srand((float) $sec + ((float) $usec * 100000));
        $result = '';
        while (strlen($result) < $length)
            $result .= base_convert(mt_rand(), 10, $base);
        if (strlen($result) > $length)
            $result = substr($result, 0, $length);
        return $result;
    }
    //if ($_REQUEST["func"])
    //    $_POST["func"] = $_REQUEST["func"];
    //else
    //    $_POST["func"] = "1";
    //d($_REQUEST);d("<br>");
    //exit();
    //if ($_POST["submit"]=="Отчет (Excel) по заявкам") $_POST["func"]="1";
    //if ($_POST["submit"]=="Отчет (Excel) по физ.объёму заявок") $_POST["func"]="phys";
    switch ($_REQUEST["func"]) {
////////////////////////////////////////////////////////////////////////////////
        case "1": // Выгрузка заявок Excel
            //$uploaddir = "./uploads/downloaders/csv/"; // права для www-data
            //$fname = hash_user().".xls";
            $xls= new CExcel("./uploads/downloaders/csv/" . hash_user() . ".xls");
            $xls->aSheet->setTitle(iconv('CP1251', 'UTF-8', 'Заявки'));
            $xls->excel->getDefaultStyle()->getFont()->setName('Arial');
            $xls->excel->getDefaultStyle()->getFont()->setSize(12);
            $xls->excel->getDefaultStyle()->getAlignment()->setWrapText(true);
            // описание стиля для заголовка
            $style_header = array(
                // Шрифт
                'font'=>array('bold'=>false, 'name'=>'Times New Roman', 'size'=>10, 'color'=>array('rgb'=>'001664')),
                // Выравнивание
                'alignment' => array(
                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                ),
                // Заполнение цветом
                'fill' => array(
                    'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                    'color'=>array('rgb'=>'AAEEFF')
                ),
                'borders'=>array(
                    //внешняя рамка
                    'outline' => array(
                        'style'=>PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb'=>'006464')
                    ),
                    //внутренняя
                    'allborders'=>array(
                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb'=>'006464')
                    )
                )
            );
//                'allborders'=>array(
//                    'bottom'=>array(
//                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
//                        'color' => array('rgb'=>'006464')
//                    )
//                )
            
            $xls->aSheet->getStyle('a1:ag2')->applyFromArray($style_header);
            $style_header['fill']['color']['rgb']='AAFFEE';
            $xls->aSheet->getStyle('ah1:fd2')->applyFromArray($style_header);
            //$xls->aSheet->getColumnDimension('A')->setWidth(3); // зададим ширину столбцов (в символьных единицах)
            //$xls->aSheet->mergeCells('A1:E1');
            //$xls->aSheet->getRowDimension('1')->setRowHeight(20);
//            //$aSheet->mergeCells('R1C3:R2C3'); 
            $xls->aSheet->mergeCells('A1:A2'); 
            $xls->aSheet->mergeCells('B1:B2'); 
            //$aSheet->mergeCells('R1C3:R2C3'); 
            $xls->aSheet->mergeCells('C1:C2'); 
            $xls->aSheet->mergeCells('D1:D2'); 
            $xls->aSheet->mergeCells('E1:E2'); 
            $xls->aSheet->mergeCells('F1:F2'); 
            $xls->aSheet->mergeCells('G1:G2'); 
            $xls->aSheet->mergeCells('H1:H2');
            $xls->aSheet->mergeCells('I1:I2');  
            $xls->sACVal(0,1,'trimConvString',['Номер заявки (АРМ)','Проект','УЭС (АРМ)','ЛТЦ','Технология','Статус','НЛС','ФИО','Контактный номер']);
            //
            $xls->aSheet->mergeCells('J1:N1');
            $xls->scVal(9,1,'trimConvString','Адрес');
            $xls->sACVal(9,2,'trimConvString',['Населенный пункт', 'Улица', 'Дом', 'Корпус', 'Квартира']);
            //
            $xls->aSheet->mergeCells('o1:o2');  $xls->scVal(14,1,'trimConvString','Услуги');
            //
            $xls->aSheet->mergeCells('p1:r1');
            $xls->scVal(15,1,'trimConvString','Инстал.платёж');
            $xls->sACVal(15,2,'trimConvString',['Рассрочка (мес.)', 'Cумма', 'Гарантии']);
            //
            $xls->aSheet->mergeCells('s1:t1');
            $xls->scVal(18,1,'trimConvString','Ежемес.платёж');
            $xls->sACVal(18,2,'trimConvString',['Тариф', 'Cумма']);
            //
            $xls->aSheet->mergeCells('u1:v1');
            $xls->scVal(20,1,'trimConvString','ONT терминал');
            $xls->sACVal(20,2,'trimConvString',['Покупка', 'Аренда']);
            //
            $xls->aSheet->mergeCells('w1:x1');
            $xls->scVal(22,1,'trimConvString','Роутер');
            $xls->sACVal(22,2,'trimConvString',['Покупка', 'Аренда']);
            //
            $xls->aSheet->mergeCells('y1:aa1');
            $xls->scVal(24,1,'trimConvString','Приставки');
            $xls->sACVal(24,2,'trimConvString',['Кол-во','Покупка', 'Аренда']);
            //
            //$xls->aSheet->mergeCells('ab1:ae1');
            //$xls->scVal(27,1,'trimConvString','Аб.ввод');
            //$xls->sACVal(27,2,'trimConvString',['Фактические затраты','Готовность', 'Планируемая дата установки', 'Дата подключения']);
            $xls->aSheet->mergeCells('ab1:ab2');  $xls->scVal(27,1,'trimConvString','Дат.посл.контакта');
            
            $xls->aSheet->mergeCells('ac1:af1');
            $xls->scVal(28,1,'trimConvString','Этапы строительства');
            $xls->sACVal(28,2,'trimConvString',['Станция','МС','РС', 'Аб.участок']);
            //
            $xls->aSheet->mergeCells('ag1:ag2');  $xls->scVal(32,1,'trimConvString','Комментарий');
            //$xls->cellStyle=$style_header;
            $xls->cellStyle=false; // стиль для данных
            // !!! продолжение (в ширину) таблицы
            $nextcolumn=32+1;
            //d(1);
            // Добавляем заголовки для полей с данными из АРМ в полном составе
            $cursor = SQL("SELECT * FROM dictionary where `table`='ps_list' order by serial");
            $ps_list_fields=[];
            $h = 0;
            while($cursor->assoc()){
                $ps_list_fields[1][$h]=$cursor->r["fname"];
                $ps_list_fields[2][$h]=$cursor->r["full_name"];
                $h++;
            }
            $cursor->free();
            //
            for($psli = 3; $psli < count($ps_list_fields[2]); $psli++) {
                $xls->aSheet->mergeCellsByColumnAndRow($nextcolumn+$psli-3,1,$nextcolumn+$psli-3,2); 
                $xls->scVal($nextcolumn+$psli-3,1,'trimConvString',$ps_list_fields[2][$psli]);
            }
            //
            $h = 1;
            $cursor=SQL($_REQUEST['reestr_query']);
            //d($_REQUEST);
            while ($cursor->assoc()) {
                $xls->scVal(0,$h+2,'trimConvString',$cursor->r["arm_id"]);
                $xls->sncVal('trimConvString',$cursor->r["project_id"]);
                $xls->sncVal('trimConvString',$cursor->r["ues_arm"]);
                $xls->sncVal('trimConvString',$cursor->r["ltc"]);
                $xls->sncVal('trimConvString',$cursor->r["tp_name"]);
                $xls->sncVal('trimConvString',$cursor->r["int_status_name"]);
                $xls->sncVal('trimConvString',$cursor->r["nls"]);
                $xls->sncVal('trimConvString',$cursor->r["client_fio"]);
                $xls->sncVal('trimConvString',$cursor->r["contact_phone"]);
                $xls->sncVal('trimConvString',$cursor->r["settlement"]);
                $xls->sncVal('trimConvString',$cursor->r["ul"]);
                $xls->sncVal('trimConvString',$cursor->r["home"]);
                $xls->sncVal('trimConvString',$cursor->r["corp"]);
                $xls->sncVal('trimConvString',$cursor->r["room"]);
                $xls->sncVal('trimConvString',$cursor->r["service"]);
                $xls->sncVal('trimConvString',$cursor->r["deferredpay"]);
                $xls->sncVal('trimConvString',$cursor->r["install"]);
                $xls->sncVal('trimConvString',$cursor->r["guarantee"]);
                $xls->sncVal('trimConvString',$cursor->r["tariffname"]);
                $xls->sncVal('trimConvString',$cursor->r["month_pay"]);
                $xls->sncVal('trimConvString',$cursor->r["ontfullpay"]);
                $xls->sncVal('trimConvString',$cursor->r["ontlease"]);
                $xls->sncVal('trimConvString',$cursor->r["routefullpay"]);
                $xls->sncVal('trimConvString',$cursor->r["routelease"]);
                $xls->sncVal('trimConvString',$cursor->r["attachnum"]);
                $xls->sncVal('trimConvString',$cursor->r["attachfullpay"]);
                $xls->sncVal('trimConvString',$cursor->r["attachlease"]);
                $xls->sncVal('trimConvString',explode(" ",$cursor->r["finishdate"])[0]);
                ////////////////////////////////////////////////////////////////
                //
                $cursorWork=SQL("SELECT wp.*,wp.cnaid,cna.cnaname,wp.bid,b.bname
                    FROM workpath wp 
                    left join cn_area cna on wp.cnaid=cna.cnaid
                    left join builder b on wp.bid=b.bid
                    where object_type=2 and lp_id=".$cursor->r["lid"] ."
                    order by wp.cnaid");
                $workpathBegin='';
                $workpath20='';
                $workpath30='';
                $workpathToEnd='';
                while ($cursorWork->assoc()) {
                    $workpathtext=$cursorWork->r["bname"].chr(10).
                            $cursorWork->r["startdateplan"].chr(10).
                            $cursorWork->r["startdate"].chr(10).
                            $cursorWork->r["targetdate"].chr(10).
                            $cursorWork->r["finishdate"].chr(10).
                            $cursorWork->r["capacity"].chr(10).
                            $cursorWork->r["realcost"].chr(10).
                            $cursorWork->r["comment"];
                    switch($cursorWork->r["cnaid"]){
                        case 10:
                        case 15:
                            $workpathBegin.=$workpathtext;
                        break;
                        case 20:
                            $workpath20.=$workpathtext;
                        break;
                        case 30:
                            $workpath30.=$workpathtext;
                        break;
                        case 40:
                        case 45:
                        case 60:
                            $workpathToEnd.=$workpathtext;
                        break;
                    }
                } // ^^ while ^^
                $cursorWork->free();
//                $xls->sncVal('trimConvString',$workpathBegin);
//                $xls->sncVal('trimConvString',$workpath20);
//                $xls->sncVal('trimConvString',$workpath30);
//                $xls->sncVal('trimConvString',$workpathToEnd);
                $xls->scVal(28,$h+2,'trimConvString',$workpathBegin);
                $xls->scVal(29,$h+2,'trimConvString',$workpath20);
                $xls->scVal(30,$h+2,'trimConvString',$workpath30);
                $xls->scVal(31,$h+2,'trimConvString',$workpathToEnd);
                ////////////////////////////////////////////////////////////////
                //
                $xls->sncVal('trimConvString',$cursor->r["comment"].chr(10).$cursor->r["coment_tvp_ota"].chr(10).$cursor->r["coment_tvp_spd"].chr(10).$cursor->r["prompt"]);
                //
                for($psli = 3; $psli < count($ps_list_fields[1]); $psli++) {
                    $xls->scVal($nextcolumn+$psli-3,$h+2,'trimConvString',$cursor->r[$ps_list_fields[1][$psli]]);
                }
                //
                $h++;
                //if($h>2100) break;
            } // ^^ while ^^
            $cursor->free();
            //////////////////////////////////////////////////////////////////// 
            //d(2);
            $xls->save();
            //$file_path = $uploaddir . $fname;
            // начинаем непосредственно выгрузку на место пользователя
            header("Content-Disposition: attachment; filename=\"Выгрузка заявок " . $udate_time3 . ".xls\""); // имя файла котрое будет при сохранение
            header("Content-Type: application/force-download"); // вызвать загрузку
            header("Content-type: application/octet-stream"); // это тоже для скачики второй вариант
            header("Content-length: " . filesize($xls->filename)); // ещё нужно размер указать
            echo file_get_contents($xls->filename);
        break; // case "1"
        ////////////////////////////////////////////////////////////////////////
        // Выгрузка заявок Excel. Физический объём
        case "10": 
            //$uploaddir = "./uploads/downloaders/csv/"; // права для www-data
            //$fname = hash_user().".xls";
            $xls= new CExcel("./uploads/downloaders/csv/" . hash_user() . ".xls");
            $xls->aSheet->setTitle(iconv('CP1251', 'UTF-8', 'Заявки. Физический объём'));
            $xls->excel->getDefaultStyle()->getFont()->setName('Arial');
            $xls->excel->getDefaultStyle()->getFont()->setSize(12);
            $xls->excel->getDefaultStyle()->getAlignment()->setWrapText(true);
            // описание стиля для заголовка
            $style_header = array(
                // Шрифт
                'font'=>array('bold'=>false, 'name'=>'Times New Roman', 'size'=>10, 'color'=>array('rgb'=>'001664')),
                // Выравнивание
                'alignment' => array(
                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                ),
                // Заполнение цветом
                'fill' => array(
                    'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                    'color'=>array('rgb'=>'AAEEFF')
                ),
                'borders'=>array(
                    //внешняя рамка
                    'outline' => array(
                        'style'=>PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb'=>'006464')
                    ),
                    //внутренняя
                    'allborders'=>array(
                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb'=>'006464')
                    )
                )
            );
//                'allborders'=>array(
//                    'bottom'=>array(
//                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
//                        'color' => array('rgb'=>'006464')
//                    )
//                )
            $xls->aSheet->getStyle('a1:ag2')->applyFromArray($style_header);
            $style_header['fill']['color']['rgb']='AAFFEE';
            $xls->aSheet->getStyle('ah1:fd2')->applyFromArray($style_header);
            //$xls->aSheet->getColumnDimension('A')->setWidth(3); // зададим ширину столбцов (в символьных единицах)
            //$xls->aSheet->mergeCells('A1:E1');
            //$xls->aSheet->getRowDimension('1')->setRowHeight(20);
//            //$aSheet->mergeCells('R1C3:R2C3'); 
            $xls->aSheet->mergeCells('A1:A2'); 
            $xls->aSheet->mergeCells('B1:B2'); 
            //$aSheet->mergeCells('R1C3:R2C3'); 
            $xls->aSheet->mergeCells('C1:C2'); 
            $xls->aSheet->mergeCells('D1:D2'); 
            $xls->aSheet->mergeCells('E1:E2'); 
            $xls->aSheet->mergeCells('F1:F2'); 
            $xls->aSheet->mergeCells('G1:G2'); 
            $xls->aSheet->mergeCells('H1:H2');
            $xls->aSheet->mergeCells('I1:I2');  
            $xls->sACVal(0,1,'trimConvString',['Номер заявки (АРМ)','Проект','УЭС (АРМ)','ЛТЦ','Технология','Статус','НЛС','ФИО','Контактный номер']);
            //
            $xls->aSheet->mergeCells('J1:N1');
            $xls->scVal(9,1,'trimConvString','Адрес');
            $xls->sACVal(9,2,'trimConvString',['Населенный пункт', 'Улица', 'Дом', 'Корпус', 'Квартира']);
            //
            $xls->aSheet->mergeCells('o1:o2');  $xls->scVal(14,1,'trimConvString','Услуги');
            //
            $xls->aSheet->mergeCells('p1:r1');
            $xls->scVal(15,1,'trimConvString','Инстал.платёж');
            $xls->sACVal(15,2,'trimConvString',['Рассрочка (мес.)', 'Cумма', 'Гарантии']);
            //
            $xls->aSheet->mergeCells('s1:t1');
            $xls->scVal(18,1,'trimConvString','Ежемес.платёж');
            $xls->sACVal(18,2,'trimConvString',['Тариф', 'Cумма']);
            //
            $xls->aSheet->mergeCells('u1:v1');
            $xls->scVal(20,1,'trimConvString','ONT терминал');
            $xls->sACVal(20,2,'trimConvString',['Покупка', 'Аренда']);
            //
            $xls->aSheet->mergeCells('w1:x1');
            $xls->scVal(22,1,'trimConvString','Роутер');
            $xls->sACVal(22,2,'trimConvString',['Покупка', 'Аренда']);
            //
            $xls->aSheet->mergeCells('y1:aa1');
            $xls->scVal(24,1,'trimConvString','Приставки');
            $xls->sACVal(24,2,'trimConvString',['Кол-во','Покупка', 'Аренда']);
            //
            //$xls->aSheet->mergeCells('ab1:ae1');
            //$xls->scVal(27,1,'trimConvString','Аб.ввод');
            //$xls->sACVal(27,2,'trimConvString',['Фактические затраты','Готовность', 'Планируемая дата установки', 'Дата подключения']);
            $xls->aSheet->mergeCells('ab1:ab2');  $xls->scVal(27,1,'trimConvString','Дат.посл.контакта');
            
            $xls->aSheet->mergeCells('ac1:af1');
            $xls->scVal(28,1,'trimConvString','Этапы строительства');
            $xls->sACVal(28,2,'trimConvString',['Станция','МС','РС', 'Аб.участок']);
            //
            $xls->aSheet->mergeCells('ag1:ag2');  $xls->scVal(32,1,'trimConvString','Комментарий');
            //$xls->sACVal(32,1,'trimConvString',['Участок','Подвид затрат','Владелец','Прокладка','Исполнитель','Имя/емк.','(Кол-во","* Размер','* Уд.стоимость','* Kисп)','= Стоимость','Единица','Примечание']);
            // !!! продолжение (в ширину) таблицы
            $nextcolumn=32+1;
            $xls->sACVal($nextcolumn,1,'trimConvString',['Участок','Подвид затрат','Владелец','Прокладка','Исполнитель',
                'Наим.вида','Имя объекта','Емкость X','Емкость Y','Кол-во','Размер','Уд.стоимость','Kисп','Стоимость','Единица','Примечание']);
            $xls->cellStyle=false; // стиль для данных
            //d(1);
            $h = 1;
            $cursor=SQL($_REQUEST['reestr_query']);
            //d($_REQUEST);
            while ($cursor->assoc()) {
                $xls->scVal(0,$h+2,'trimConvString',$cursor->r["arm_id"]);
                $xls->sncVal('trimConvString',$cursor->r["project_id"]);
                $xls->sncVal('trimConvString',$cursor->r["ues_arm"]);
                $xls->sncVal('trimConvString',$cursor->r["ltc"]);
                $xls->sncVal('trimConvString',$cursor->r["tp_name"]);
                $xls->sncVal('trimConvString',$cursor->r["int_status_name"]);
                $xls->sncVal('trimConvString',$cursor->r["nls"]);
                $xls->sncVal('trimConvString',$cursor->r["client_fio"]);
                $xls->sncVal('trimConvString',$cursor->r["contact_phone"]);
                $xls->sncVal('trimConvString',$cursor->r["settlement"]);
                $xls->sncVal('trimConvString',$cursor->r["ul"]);
                $xls->sncVal('trimConvString',$cursor->r["home"]);
                $xls->sncVal('trimConvString',$cursor->r["corp"]);
                $xls->sncVal('trimConvString',$cursor->r["room"]);
                $xls->sncVal('trimConvString',$cursor->r["service"]);
                $xls->sncVal('trimConvString',$cursor->r["deferredpay"]);
                $xls->sncVal('trimConvString',$cursor->r["install"]);
                $xls->sncVal('trimConvString',$cursor->r["guarantee"]);
                $xls->sncVal('trimConvString',$cursor->r["tariffname"]);
                $xls->sncVal('trimConvString',$cursor->r["month_pay"]);
                $xls->sncVal('trimConvString',$cursor->r["ontfullpay"]);
                $xls->sncVal('trimConvString',$cursor->r["ontlease"]);
                $xls->sncVal('trimConvString',$cursor->r["routefullpay"]);
                $xls->sncVal('trimConvString',$cursor->r["routelease"]);
                $xls->sncVal('trimConvString',$cursor->r["attachnum"]);
                $xls->sncVal('trimConvString',$cursor->r["attachfullpay"]);
                $xls->sncVal('trimConvString',$cursor->r["attachlease"]);
                $xls->sncVal('trimConvString',explode(" ",$cursor->r["finishdate"])[0]);
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString',$cursor->r["comment"].chr(10).$cursor->r["coment_tvp_ota"].chr(10).$cursor->r["coment_tvp_spd"].chr(10).$cursor->r["prompt"]);
                //
                $physnum=0;
//                $cursorLid=SQL("SELECT ccmid,ccm.cnaid,cna.cnaname,ccm.subeid,sube.ename subename,ccm.oid,o.oname ooname,ccm.cxid,cx.cxname,
//		    ccm.technology,tp.name tpname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
//                    ccm.ceid,ce.cename,ccm.bid,b.bname,ccm.rcmid,rcm.name rcmname,pld.arm_id,ccm.lid,
//                    max(ccm.ccmname) ccmname,sum(ccm.amount) ccmamount,sum(ccmlen) ccmlen,max(ccm.price) ccmprice,max(ccm.comment) comment,
//                    max(rcm.price) rcmprice,max(rcm.unit) rcmunit,max(rcm.capacity1) rcmcapacity1,max(rcm.capacity2) rcmcapacity2
//                FROM call_com_mat ccm 
//                left join ref_com_mat rcm using(rcmid)
//                left join cn_eq_type cet using(cetid) 
//                left join ps_list_dop pld on ccm.stype=1 and pld.lid=ccm.lid
//                left join sign_envir se on ccm.seid=se.seid
//                left join cn_area cna on ccm.cnaid=cna.cnaid
//                left join cn_envir ce on ccm.ceid=ce.ceid
//                left join builder b on ccm.bid=b.bid
//                left join subexpense sube on ccm.subeid=sube.seid
//                left join owner o on ccm.oid=o.oid
//                left join complexity cx on ccm.cxid=cx.cxid
//                left join ps_teh_podkl tp on ccm.technology=tp.id
//                where ccm.stype=1 and ccm.lid=".$cursor->r["lid"] ." 
//                group by ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
//                    ccm.ceid,ce.cename,ccm.rcmid,rcm.name,pld.arm_id,ccm.lid,ccmid
//		order by ccm.cnaid,ccm.seid,rcm.cetid,ccm.ceid,ccm.rcmid,ccm.lid");
                $cursorLid=SQL(com_object_query("1","=". $cursor->r["lid"]));
                //$all_expense=0.0;
                while ($cursorLid->assoc()) {
                    $xls->scVal($nextcolumn,$h+2,'trimConvString',$cursorLid->r["cnaname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["subename"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["ooname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["cename"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["bname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["ccmname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmcapacity1"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmcapacity2"]);
                    $xls->sncVal('trimConvString', ((!empty($cursorLid->r["ccmamount"])) ? round($cursorLid->r["ccmamount"]) : "1") );
                    $xls->sncVal('trimConvString', ((!empty($cursorLid->r["ccmlen"])) ? $cursorLid->r["ccmlen"] : "1.0") );
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmprice"]);
                    $xls->sncVal('trimConvString', (($cursorLid->r["bid"]==1)? "0.7" : "1.0") );
                    $expense=round($cursorLid->r["rcmprice"] 
                                * (($cursorLid->r["ccmlen"]!=0)? $cursorLid->r["ccmlen"] : 1.0) 
                                * (($cursorLid->r["ccmamount"]!=0)? $cursorLid->r["ccmamount"] : 1.0) 
                                * (($cursorLid->r["bid"]==1)? 0.7 : 1.0),2); // если "хозспособ", то *0.7
                    //$all_expense+=$expense;
                    $xls->sncVal('trimConvString',$expense ."");
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmunit"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["comment"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["comment"]." / ".$cursorLid->r["rcmmgroup"]." / ".$cursorLid->r["rcmpgroup"]);
                    if($physnum>0){
                        for($pncol=0;$pncol<32;$pncol++)
                            $xls->aSheet->setCellValueByColumnAndRow($pncol, $h+2, $xls->aSheet->getCellByColumnAndRow($pncol, $h+2-1)->getValue());
                    }
                    $physnum++;
                    $h++;
                    //d($physnum." ".$h);
                    
                } // ^^ while ^^
                
                $cursorLid->free();
                //d($physnum." ".$h);
                //break;
                //
                if($physnum==0) $h++; // физ.объёма не было
                //if($h>2100) break;
            } // ^^ while ^^
            $cursor->free();
            //////////////////////////////////////////////////////////////////// 
            //d(2);
            $xls->save();
            //$file_path = $uploaddir . $fname;
            // начинаем непосредственно выгрузку на место пользователя
            header("Content-Disposition: attachment; filename=\"Выгрузка заявок " . $udate_time3 . ".xls\""); // имя файла котрое будет при сохранение
            header("Content-Type: application/force-download"); // вызвать загрузку
            header("Content-type: application/octet-stream"); // это тоже для скачики второй вариант
            header("Content-length: " . filesize($xls->filename)); // ещё нужно размер указать
            echo file_get_contents($xls->filename);
        break; // case "10"
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        // Выгрузка заявок Excel. Отчет по опросу абонентов
        case "15": 
            //$uploaddir = "./uploads/downloaders/csv/"; // права для www-data
            //$fname = hash_user().".xls";
            $xls= new CExcel("./uploads/downloaders/csv/" . hash_user() . ".xls");
            $xls->aSheet->setTitle(iconv('CP1251', 'UTF-8', 'Заявки. Физический объём'));
            $xls->excel->getDefaultStyle()->getFont()->setName('Arial');
            $xls->excel->getDefaultStyle()->getFont()->setSize(12);
            $xls->excel->getDefaultStyle()->getAlignment()->setWrapText(true);
            // описание стиля для заголовка
            $style_header = array(
                // Шрифт
                'font'=>array('bold'=>false, 'name'=>'Times New Roman', 'size'=>10, 'color'=>array('rgb'=>'001664')),
                // Выравнивание
                'alignment' => array(
                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                ),
                // Заполнение цветом
                'fill' => array(
                    'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                    'color'=>array('rgb'=>'AAEEFF')
                ),
                'borders'=>array(
                    //внешняя рамка
                    'outline' => array(
                        'style'=>PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb'=>'006464')
                    ),
                    //внутренняя
                    'allborders'=>array(
                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb'=>'006464')
                    )
                )
            );
//                'allborders'=>array(
//                    'bottom'=>array(
//                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
//                        'color' => array('rgb'=>'006464')
//                    )
//                )
            $xls->aSheet->getStyle('a1:ag2')->applyFromArray($style_header);
            $style_header['fill']['color']['rgb']='AAFFEE';
            $xls->aSheet->getStyle('p1:bf2')->applyFromArray($style_header);
            $style_header['fill']['color']['rgb']='FFEEEE';
            $xls->aSheet->getStyle('bg1:bh2')->applyFromArray($style_header);
            //$xls->aSheet->getColumnDimension('A')->setWidth(3); // зададим ширину столбцов (в символьных единицах)
            //$xls->aSheet->mergeCells('A1:E1');
            //$xls->aSheet->getRowDimension('1')->setRowHeight(20);
//            //$aSheet->mergeCells('R1C3:R2C3'); 
            $xls->aSheet->mergeCells('A1:A2'); 
            $xls->aSheet->mergeCells('B1:B2'); 
            //$aSheet->mergeCells('R1C3:R2C3'); 
            $xls->aSheet->mergeCells('C1:C2'); 
            $xls->aSheet->mergeCells('D1:D2'); 
            $xls->aSheet->mergeCells('E1:E2'); 
            $xls->aSheet->mergeCells('F1:F2'); 
            $xls->aSheet->mergeCells('G1:G2'); 
            $xls->aSheet->mergeCells('H1:H2');
            $xls->aSheet->mergeCells('I1:I2');  
            $xls->sACVal(0,1,'trimConvString',['Номер заявки (АРМ)','Проект','УЭС (АРМ)','ЛТЦ','Технология','Статус','НЛС','ФИО','Контактный номер']);
            //
            $xls->aSheet->mergeCells('J1:N1');
            $xls->scVal(9,1,'trimConvString','Адрес');
            $xls->sACVal(9,2,'trimConvString',['Населенный пункт', 'Улица', 'Дом', 'Корпус', 'Квартира']);
            //
            $xls->aSheet->mergeCells('o1:o2');  $xls->scVal(14,1,'trimConvString','Услуги');
            //
            //$xls->aSheet->mergeCells('p1:r1');
            //$xls->scVal(15,1,'trimConvString','Инстал.платёж');
            //$xls->sACVal(15,2,'trimConvString',['Рассрочка (мес.)', 'Cумма', 'Гарантии']);
            //
            //$xls->aSheet->mergeCells('s1:t1');
            //$xls->scVal(18,1,'trimConvString','Ежемес.платёж');
            //$xls->sACVal(18,2,'trimConvString',['Тариф', 'Cумма']);
            //
            //$xls->aSheet->mergeCells('u1:v1');
            //$xls->scVal(20,1,'trimConvString','ONT терминал');
            //$xls->sACVal(20,2,'trimConvString',['Покупка', 'Аренда']);
            //
            //$xls->aSheet->mergeCells('w1:x1');
            //$xls->scVal(22,1,'trimConvString','Роутер');
            //$xls->sACVal(22,2,'trimConvString',['Покупка', 'Аренда']);
            //
            //$xls->aSheet->mergeCells('y1:aa1');
            //$xls->scVal(24,1,'trimConvString','Приставки');
            //$xls->sACVal(24,2,'trimConvString',['Кол-во','Покупка', 'Аренда']);
            //
            //$xls->aSheet->mergeCells('ab1:ab2');  $xls->scVal(27,1,'trimConvString','Дат.посл.контакта');
            //$xls->aSheet->mergeCells('ac1:af1');
            //$xls->scVal(28,1,'trimConvString','Этапы строительства');
            //$xls->sACVal(28,2,'trimConvString',['Станция','МС','РС', 'Аб.участок']);
            //
            $xls->aSheet->mergeCells('p1:p2');  $xls->scVal(15,1,'trimConvString','Примечание');
            //$xls->sACVal(32,1,'trimConvString',['Участок','Подвид затрат','Владелец','Прокладка','Исполнитель','Имя/емк.','(Кол-во","* Размер','* Уд.стоимость','* Kисп)','= Стоимость','Единица','Примечание']);
            // !!! продолжение (в ширину) таблицы
            $nextcolumn=15+1;
            //$xls->sACVal($nextcolumn,1,'trimConvString',['Участок','Подвид затрат','Владелец','Прокладка','Исполнитель',
            //    'Наим.вида','Имя объекта','Емкость X','Емкость Y','Кол-во','Размер','Уд.стоимость','Kисп','Стоимость','Единица','Примечание']);
            $xls->cellStyle=false; // стиль для данных
            //
            $nextcolumn2=0;
            //
            $xls->aSheet->mergeCells('q1:s1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString','Услуги');
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString','существующие, не меняем');
            $xls->sncVal('trimConvString','по которым меняем технологию');
            $xls->sncVal('trimConvString','Новые (подключаем)');                
            $nextcolumn2+=3;
            //
            $xls->aSheet->mergeCells('t1:w1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString','Инсталляционный платеж');
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString','сумма');
            $xls->sncVal('trimConvString','месяцы');
            $xls->sncVal('trimConvString','ежемесячно');                
            $xls->sncVal('trimConvString','Договор');                
            $nextcolumn2+=4;
            // Выбор тарифного плана
            $xls->aSheet->mergeCells('x1:z1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString','Тарифный план');
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString','Наименование');
            $xls->sncVal('trimConvString',"ежемесячно");
            $xls->sncVal('trimConvString',"ежемесячно2");
            $nextcolumn2+=3;
            // ONT
            $xls->aSheet->mergeCells('aa1:ad1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"ONT");
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString',"тип");
            $xls->sncVal('trimConvString',"стоимость");
            $xls->sncVal('trimConvString',"аренда");
            $xls->sncVal('trimConvString',"количество");
            $nextcolumn2+=4;
            // роутер
            $xls->aSheet->mergeCells('ae1:ah1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"роутер");
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString',"тип");
            $xls->sncVal('trimConvString',"стоимость");
            $xls->sncVal('trimConvString',"аренда");
            $xls->sncVal('trimConvString',"количество");
            $nextcolumn2+=4;
            // Приставка
            $xls->aSheet->mergeCells('ai1:ak1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString','Приставка');            
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString','количество');
            $xls->sncVal('trimConvString',"стоимость");
            $xls->sncVal('trimConvString',"аренда");
            $nextcolumn2+=3;
            // SIM-карты (MVNO)
            $xls->aSheet->mergeCells('al1:am1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"SIM-карты");
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString',"тариф");
            $xls->sncVal('trimConvString',"количество");
            $nextcolumn2+=2;
            // Видеонаблюдение
//            $xls->aSheet->mergeCells('an1:ao1');
//            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"Видеонаблюдение");
//            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString',"внутр.камера");
//            $xls->sncVal('trimConvString',"внеш.камера");
            $xls->aSheet->mergeCells('an1:aq1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"Внутр.видеокамера");
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString',"Кол-во");
            $xls->sncVal('trimConvString',"Сумма");
            $xls->sncVal('trimConvString',"Рассрочка");
            $xls->sncVal('trimConvString',"Монтаж");
            $nextcolumn2+=4;
            $xls->aSheet->mergeCells('ar1:av1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"Внеш.видеокамера");
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString',"Кол-во");
            $xls->sncVal('trimConvString',"Сумма");
            $xls->sncVal('trimConvString',"Рассрочка");
            $xls->sncVal('trimConvString',"Монтаж");
            $xls->sncVal('trimConvString',"POE инжектор");
            $nextcolumn2+=5;
            // Умный дом
            $xls->aSheet->mergeCells('aw1:bb1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"Умный дом");
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString',"Баз. покупка");
            $xls->sncVal('trimConvString',"Рассрочка");
            $xls->sncVal('trimConvString',"Расш. покупка");
            $xls->sncVal('trimConvString',"Рассрочка");
            $xls->sncVal('trimConvString',"Монт.дат. внут.");
            $xls->sncVal('trimConvString',"Монт.дат. внеш.");
            $nextcolumn2+=6;
            // запрос по абонентскому вводу
            $xls->aSheet->mergeCells('bc1:bd1');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"дата орг.ввода");
            $xls->scVal($nextcolumn+$nextcolumn2,2,'trimConvString','План');
            $xls->sncVal('trimConvString','Факт');
            $nextcolumn2+=2;
            $xls->aSheet->mergeCells('be1:be2');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"План.дата подключения");
            $xls->aSheet->mergeCells('bf1:bf2');
            $xls->scVal($nextcolumn+$nextcolumn2+1,1,'trimConvString',"Последний контакт");
            $nextcolumn2+=2;
            $xls->aSheet->mergeCells('bg1:bg2');
            $xls->scVal($nextcolumn+$nextcolumn2,1,'trimConvString',"Оператор");
            $xls->aSheet->mergeCells('bh1:bh2');
            $xls->scVal($nextcolumn+$nextcolumn2+1,1,'trimConvString',"Дата редак.");
            $nextcolumn2+=2;
            //
            $h = 1;
            $cursor=SQL($_REQUEST['reestr_query']);
            //d($_REQUEST);
            while ($cursor->assoc()) {
                $xls->scVal(0,$h+2,'trimConvString',$cursor->r["arm_id"]);
                $xls->sncVal('trimConvString',$cursor->r["project_id"]);
                $xls->sncVal('trimConvString',$cursor->r["ues_arm"]);
                $xls->sncVal('trimConvString',$cursor->r["ltc"]);
                $xls->sncVal('trimConvString',$cursor->r["tp_name"]);
                $xls->sncVal('trimConvString',$cursor->r["int_status_name"]);
                $xls->sncVal('trimConvString',$cursor->r["nls"]);
                $xls->sncVal('trimConvString',$cursor->r["client_fio"]);
                $xls->sncVal('trimConvString',$cursor->r["contact_phone"]);
                $xls->sncVal('trimConvString',$cursor->r["settlement"]);
                $xls->sncVal('trimConvString',$cursor->r["ul"]);
                $xls->sncVal('trimConvString',$cursor->r["home"]);
                $xls->sncVal('trimConvString',$cursor->r["corp"]);
                $xls->sncVal('trimConvString',$cursor->r["room"]);
                $xls->sncVal('trimConvString',$cursor->r["service"]);
//                $xls->sncVal('trimConvString',$cursor->r["deferredpay"]);
//                $xls->sncVal('trimConvString',$cursor->r["install"]);
//                $xls->sncVal('trimConvString',$cursor->r["guarantee"]);
//                $xls->sncVal('trimConvString',$cursor->r["tariffname"]);
//                $xls->sncVal('trimConvString',$cursor->r["month_pay"]);
//                $xls->sncVal('trimConvString',$cursor->r["ontfullpay"]);
//                $xls->sncVal('trimConvString',$cursor->r["ontlease"]);
//                $xls->sncVal('trimConvString',$cursor->r["routefullpay"]);
//                $xls->sncVal('trimConvString',$cursor->r["routelease"]);
//                $xls->sncVal('trimConvString',$cursor->r["attachnum"]);
//                $xls->sncVal('trimConvString',$cursor->r["attachfullpay"]);
//                $xls->sncVal('trimConvString',$cursor->r["attachlease"]);
//                $xls->sncVal('trimConvString',explode(" ",$cursor->r["finishdate"])[0]);
                /*$xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');
                $xls->sncVal('trimConvString','');*/
                //$xls->sncVal('trimConvString',$cursor->r["comment"].chr(10).$cursor->r["coment_tvp_ota"].chr(10).$cursor->r["coment_tvp_spd"].chr(10).$cursor->r["prompt"]);
                $xls->sncVal('trimConvString',$cursor->r["comment"]);
                //
                /*$physnum=0;
                $cursorLid=SQL(com_object_query("1","=". $cursor->r["lid"]));
                //$all_expense=0.0;
                while ($cursorLid->assoc()) {
                    $xls->scVal($nextcolumn,$h+2,'trimConvString',$cursorLid->r["cnaname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["subename"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["ooname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["cename"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["bname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["ccmname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmcapacity1"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmcapacity2"]);
                    $xls->sncVal('trimConvString', ((!empty($cursorLid->r["ccmamount"])) ? round($cursorLid->r["ccmamount"]) : "1") );
                    $xls->sncVal('trimConvString', ((!empty($cursorLid->r["ccmlen"])) ? $cursorLid->r["ccmlen"] : "1.0") );
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmprice"]);
                    $xls->sncVal('trimConvString', (($cursorLid->r["bid"]==1)? "0.7" : "1.0") );
                    $expense=round($cursorLid->r["rcmprice"] 
                                * (($cursorLid->r["ccmlen"]!=0)? $cursorLid->r["ccmlen"] : 1.0) 
                                * (($cursorLid->r["ccmamount"]!=0)? $cursorLid->r["ccmamount"] : 1.0) 
                                * (($cursorLid->r["bid"]==1)? 0.7 : 1.0),2); // если "хозспособ", то *0.7
                    //$all_expense+=$expense;
                    $xls->sncVal('trimConvString',$expense ."");
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmunit"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["comment"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["comment"]." / ".$cursorLid->r["rcmmgroup"]." / ".$cursorLid->r["rcmpgroup"]);
                    if($physnum>0){
                        for($pncol=0;$pncol<32;$pncol++)
                            $xls->aSheet->setCellValueByColumnAndRow($pncol, $h+2, $xls->aSheet->getCellByColumnAndRow($pncol, $h+2-1)->getValue());
                    }
                    $physnum++;
                    $h++;
                    //d($physnum." ".$h);
                } // ^^ while ^^
                $cursorLid->free();*/
                //d($physnum." ".$h);
                //break;
                //

    /*            $call_row_dop=rSQL("SELECT t.formulation tformulation,t.month_pay tmonth_pay,t.month_pay2 tmonth_pay2,
                        ig.igname igname,ig.main_sum igmain_sum,ig.month_pay igmonth_pay,ig.defferedpay igdefferedpay,
                        ld.* 
                    FROM ps_list_dop ld left join tariff t on ld.tariff_id=t.id 
                        left join installg ig on ld.installg_id=ig.id  where lid=".$_REQUEST["lid"]);
                $call_row_status=rSQL("SELECT * FROM ps_status where id=".$call_row_dop["status"]);
                $call_row=rSQL("SELECT * FROM ps_list where list_id=".$call_row_dop["list_id"]);*/
                $old_call_service=rSQL("SELECT GROUP_CONCAT(s.sname SEPARATOR ',') scs "
                        . "FROM call_service cs left join service s on cs.service_id=s.id where cstype=1 and lid=".$cursor->r["lid"]." group by lid")['scs'];
                $new_tech_call_service=rSQL("SELECT GROUP_CONCAT(s.sname SEPARATOR ',') scs "
                        . "FROM call_service cs left join service s on cs.service_id=s.id where cstype=2 and lid=".$cursor->r["lid"]." group by lid")['scs'];
                $new_call_service=rSQL("SELECT GROUP_CONCAT(s.sname SEPARATOR ',') scs "
                        . "FROM call_service cs left join service s on cs.service_id=s.id where cstype=3 and lid=".$cursor->r["lid"]." group by lid")['scs'];
                // запрос итогов по объектам связи заявки (испол. в нескольких местах)
                $com_object_query="SELECT ccm.ccmid,ccm.rcmid,rcm.cetid,ccm.price,ccm.lease,ccm.amount,tariff_id,ccm.month_pay,ccm.defferedpay,ccm.first_pay 
                    FROM call_com_mat ccm left join ref_com_mat rcm using(rcmid) 
                    where ccm.stype=1 and ccm.lid=".$cursor->r["lid"] ."
                    order by ccm.ccmid ";
                $cursorCCA=SQL($com_object_query);
                $call_com_array=[];
                $call_com_rcmid=[];
                $call_attach_array=[];
                $call_attach_array_i=0;
                $call_attach_pay=[0.0,0.0];
                while ($cursorCCA->assoc()) {
                    //if($cursorCCA->r["cetid"]==920) $call_attach_array[$cursorCCA->r["ccmid"]]=array($cursorCCA->r["rcmid"],$cursorCCA->r["price"],$cursorCCA->r["lease"],$cursorCCA->r["amount"],$cursorCCA->r["cetid"]);
                    if($cursorCCA->r["cetid"]==920){ 
                        $call_attach_array[$call_attach_array_i]=
                                array($cursorCCA->r["rcmid"],$cursorCCA->r["price"],$cursorCCA->r["lease"],$cursorCCA->r["amount"],$cursorCCA->r["cetid"]);
                        $call_attach_pay[0]+=$cursorCCA->r["price"];
                        $call_attach_pay[1]+=$cursorCCA->r["lease"];
                        $call_attach_array_i++;
                    }
                    $call_com_array[$cursorCCA->r["rcmid"]]=array($cursorCCA->r["ccmid"],$cursorCCA->r["price"],$cursorCCA->r["lease"],
                        $cursorCCA->r["amount"],$cursorCCA->r["cetid"],$cursorCCA->r["tariff_id"],
                        $cursorCCA->r["month_pay"],$cursorCCA->r["defferedpay"],$cursorCCA->r["first_pay"]);
                    $call_com_rcmid[$cursorCCA->r["cetid"]]=$cursorCCA->r["rcmid"];
                }
                for($ai=$call_attach_array_i;$ai<4;$ai++){
                        $call_attach_array[$ai]=-1;
                }
                $cursorCCA->free();
                //
                $nextcolumn2=0;
                //
                $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',$old_call_service);
                $xls->sncVal('trimConvString',$new_tech_call_service);
                $xls->sncVal('trimConvString',$new_call_service);                
                $nextcolumn2+=3;
                //
                $installg=rSQL("SELECT * FROM installg where id='".$cursor->r["installg_id"] ."' ");
                if($installg['main_sum']){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',$installg['main_sum']);
                    $xls->sncVal('trimConvString',$installg['month_pay']);
                    $xls->sncVal('trimConvString',$installg['defferedpay']);
                    $xls->sncVal('trimConvString',$cursor->r["guarantee"]);
                }else{
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString','0');
                    $xls->sncVal('trimConvString','0');
                    $xls->sncVal('trimConvString','0');
                    $xls->sncVal('trimConvString','нет');                    
                }
                $nextcolumn2+=4;
                // Выбор тарифного плана
                if($cursor->r["tariff_id"] and $cursor->r["tformulation"]){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',$cursor->r["tformulation"]);
                    $xls->sncVal('trimConvString',$cursor->r["tmonth_pay"]);
                    $xls->sncVal('trimConvString',$cursor->r["tmonth_pay2"]);
                }else{
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',$cursor->r["tariffname"]);
                    $xls->sncVal('trimConvString',$cursor->r["month_pay"]);
                    $xls->sncVal('trimConvString','0');
                }
                $nextcolumn2+=3;
                // ONT
                if($call_com_array[112] and $call_com_array[112][1]==6200){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"С Wi-Fi");
                    $xls->sncVal('trimConvString',"6200");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"1");
                } elseif($call_com_array[112] and $call_com_array[112][2]==150){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"С Wi-Fi");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"150");
                    $xls->sncVal('trimConvString',"1");
                } elseif($call_com_array[112] and $call_com_array[112][2]==1){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"С Wi-Fi");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"1");
                    $xls->sncVal('trimConvString',"1");
                } elseif($call_com_array[113] and $call_com_array[113][1]==2100){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"Без Wi-Fi");
                    $xls->sncVal('trimConvString',"2100");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"1");
                } elseif($call_com_array[113] and $call_com_array[113][2]==100){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"Без Wi-Fi");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"100");
                    $xls->sncVal('trimConvString',"1");
                }else{
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"нет");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"0");
                }
                $nextcolumn2+=4;
                // роутер
                if($call_com_array[114] and $call_com_array[114][1]==1900){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"роутер");
                    $xls->sncVal('trimConvString',"1900");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"1");
                } elseif($call_com_array[114] and $call_com_array[114][2]==50){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"роутер");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"50");
                    $xls->sncVal('trimConvString',"1");
                } elseif($call_com_array[114] and $call_com_array[114][2]==1){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"роутер");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"1");
                    $xls->sncVal('trimConvString',"1");
                } elseif($call_com_array[114] and $call_com_array[114][2]==0){
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"в тарифе");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"1");
                }else{
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"нет");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"0");
                    $xls->sncVal('trimConvString',"0");
                }
                $nextcolumn2+=4;
                // Приставка
                $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',$call_attach_array_i);
                $xls->sncVal('trimConvString',$call_attach_pay[0]);
                $xls->sncVal('trimConvString',$call_attach_pay[1]);
                $nextcolumn2+=3;
                // SIM-карты (MVNO)
                if($call_com_array[$call_com_rcmid[930]][5]){ // есть SIM-карта
                    $tariff=rSQL("select * from tariff where tech_id=40 and id=".$call_com_array[$call_com_rcmid[930]][5] ." ");
                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',$tariff["formulation"]);
                    $xls->sncVal('trimConvString',"".$call_com_array[$call_com_rcmid[930]][3]);
                }
                $nextcolumn2+=2;
                // Видеонаблюдение
                $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"".(($call_com_array[115]) ? $call_com_array[115][3] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[115]) ? $call_com_array[115][1] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[115]) ? $call_com_array[115][6] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[122]) ? $call_com_array[122][3] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[116]) ? $call_com_array[116][3] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[116]) ? $call_com_array[116][1] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[116]) ? $call_com_array[116][6] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[123]) ? $call_com_array[123][3] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[124]) ? $call_com_array[124][1] : 0));
                $nextcolumn2+=9;
                // Умный дом
                $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"".(($call_com_array[120]) ? $call_com_array[120][1] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[120]) ? $call_com_array[120][6] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[121]) ? $call_com_array[121][1] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[121]) ? $call_com_array[121][6] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[117]) ? $call_com_array[117][3] : 0));
                $xls->sncVal('trimConvString',"".(($call_com_array[118]) ? $call_com_array[118][3] : 0));
//                if($call_com_array[117]){
//                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"Базовый");
//                } elseif($call_com_array[118]){
//                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"Расширенный");
//                } else{
//                    $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',"нет");
//                } 
//                $xls->sncVal('trimConvString',"".$call_com_array[$call_com_rcmid[950]][3]);
                $nextcolumn2+=6;
                // запрос по абонентскому вводу
//                $workpath=rSQL("select * from workpath where object_type=2 and cnaid=45 and lp_id=".$cursor->r["lid"] ." ");
//                $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',explode(" ",$workpath["targetdate"])[0]);
//                $xls->sncVal('trimConvString',explode(" ",$workpath["finishdate"])[0]);
//                $xls->sncVal('trimConvString',explode(" ",$cursor->r["targetdate"])[0]);
//                $xls->sncVal('trimConvString',explode(" ",$cursor->r["finishdate"])[0]);
                $workpath=rSQL("select DATE_FORMAT(targetdate, '%d.%m.%Y') targetdate,DATE_FORMAT(finishdate, '%d.%m.%Y') finishdate "
                        . "from workpath where object_type=2 and cnaid=45 and lp_id=".$cursor->r["lid"] ." ");
                $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',$workpath["targetdate"]);
                $xls->sncVal('trimConvString',$workpath["finishdate"]);
                $xls->sncVal('trimConvString',$cursor->r["targetdate_c"]);
                $xls->sncVal('trimConvString',$cursor->r["finishdate_c"]);
                $nextcolumn2+=4;
                //
                $xls->scVal($nextcolumn+$nextcolumn2,$h+2,'trimConvString',rSQL("SELECT fio FROM ps_users where uid=".$cursor->r["uid"])["fio"]);
                $xls->sncVal('trimConvString',explode(" ",$cursor->r["dateedit"])[0]);
                $nextcolumn2+=2;
                //
                $h++;
                //
            } // ^^ while ^^
            $cursor->free();
            //////////////////////////////////////////////////////////////////// 
            //d(2);
            $xls->save();
            //$file_path = $uploaddir . $fname;
            // начинаем непосредственно выгрузку на место пользователя
            header("Content-Disposition: attachment; filename=\"Выгрузка заявок " . $udate_time3 . ".xls\""); // имя файла котрое будет при сохранение
            header("Content-Type: application/force-download"); // вызвать загрузку
            header("Content-type: application/octet-stream"); // это тоже для скачики второй вариант
            header("Content-length: " . filesize($xls->filename)); // ещё нужно размер указать
            echo file_get_contents($xls->filename);
        break; // case "15"
        ////////////////////////////////////////////////////////////////////////
        // Выгрузка отчета по проектам. Физический объём
        case "11": 
            require_once "vlg_project_query.php"; // !!! без лишних пробелов !!!
            //$uploaddir = "./uploads/downloaders/csv/"; // права для www-data
            //$fname = hash_user().".xls";
            $xls= new CExcel("./uploads/downloaders/csv/" . hash_user() . ".xls");
            $xls->aSheet->setTitle(iconv('CP1251', 'UTF-8', 'Заявки. Физический объём'));
            $xls->excel->getDefaultStyle()->getFont()->setName('Arial');
            $xls->excel->getDefaultStyle()->getFont()->setSize(12);
            $xls->excel->getDefaultStyle()->getAlignment()->setWrapText(true);
            // описание стиля для заголовка
            $style_header = array(
                // Шрифт
                'font'=>array('bold'=>false, 'name'=>'Times New Roman', 'size'=>10, 'color'=>array('rgb'=>'001664')),
                // Выравнивание
                'alignment' => array(
                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                ),
                // Заполнение цветом
                'fill' => array(
                    'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                    'color'=>array('rgb'=>'AAEEFF')
                ),
                'borders'=>array(
                    //внешняя рамка
                    'outline' => array(
                        'style'=>PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb'=>'006464')
                    ),
                    //внутренняя
                    'allborders'=>array(
                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb'=>'006464')
                    )
                )
            );
//                'allborders'=>array(
//                    'bottom'=>array(
//                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
//                        'color' => array('rgb'=>'006464')
//                    )
//                )
            $xls->aSheet->getStyle('a1:z1')->applyFromArray($style_header);
            $xls->aSheet->getStyle('j2:t2')->applyFromArray($style_header);
            $style_header['fill']['color']['rgb']='AAFFEE';
            $xls->aSheet->getStyle('aa1:az1')->applyFromArray($style_header);
            $xls->sACVal(0,1,'trimConvString',['Наименование','Статус','Технология','Расходы на СМР (план)','Расходы на СМР (факт)',
                'Мин.(план)кол.абонентов','Расходы на СМР на абонента (план)','Кол-во заявок (факт)','в т.ч. заявок ЮЛ(факт)',
                'Кол-во заявок (факт) отдельно по статусам','','','','','','','','','','',
                'Кол-во портов(план)','Инстал.платёж','Ежемес.платёж','Время набора абонентов',
                'Срок окупаемости','Примечание']);
            $xls->sACVal(9,2,'trimConvString',['Группировка заявок','Определение ТВП (ОКВ)','Согласование с клиентом','Согласование ТЭО','Выделение инвестиций',
                'В реализации у подрядчика','В реализации хозспособом','К Включению','Подключено',
                'Отклонено','В архив']);
            $xls->aSheet->mergeCells('j1:t1');
            //
            // !!! продолжение (в ширину) таблицы
            $nextcolumn=26+1;
            $xls->sACVal($nextcolumn,1,'trimConvString',['Участок','Подвид затрат','Владелец','Прокладка','Исполнитель',
                'Наим.вида','Имя объекта','Емкость X','Емкость Y','Кол-во','Размер','Уд.стоимость','Kисп','Стоимость','Единица','Примечание']);
            $xls->cellStyle=false; // стиль для данных
            //d(1);
            $h = 1;
            $cursor=SQL($_REQUEST['reestr_query']);
            //d($_REQUEST);
            while ($cursor->assoc()) {
                //
                ////////////////////////////////////////////////////////////////
                // статистика статусов заявок проекта 
                $status_stat[10]=0;
                $status_stat[15]=0;
                $status_stat[20]=0;
                $status_stat[25]=0;
                $status_stat[30]=0;
                $status_stat[40]=0;
                $status_stat[45]=0;
                $status_stat[50]=0;
                $status_stat[55]=0;
                $status_stat[60]=0;
                $status_stat[65]=0;
                $cursorLid=SQL(call_status_stat($cursor->r["project_id"]));
                $str_status_stat=""; // ФЛ
                $str_status_stat2=""; // ЮЛ
                while ($cursorLid->assoc()) {
                    if($cursorLid->r["cs"]==1){
                        $str_status_stat.=$cursorLid->r["statname"]." ".$cursorLid->r["callnum"]."/";
                        $status_stat[$cursorLid->r["status"]]=$cursorLid->r["callnum"];
                    } else {
                        $str_status_stat2.="".$cursorLid->r["statname"]." ".$cursorLid->r["callnum"]."/";
                    }
                } // ^^ while ^^
                $cursorLid->free();
                $str_status_stat.="";
                $str_status_stat2.="";
                // статистика статусов заявок проекта               
                ////////////////////////////////////////////////////////////////
                //
                $xls->scVal(0,$h+2,'trimConvString',$cursor->r["project_name"]);
                $xls->sncVal('trimConvString',$cursor->r["statname"]);
                $xls->sncVal('trimConvString',$cursor->r["techname"]);
                $xls->sncVal('trimConvString','zatrat_smr_plan');
                $xls->sncVal('trimConvString',$cursor->r["zatrat_smr"]);
                $xls->sncVal('trimConvString',$cursor->r["deficient"]);
                $xls->sncVal('trimConvString','zatrat_smr_plan/deficient');
                
                $xls->sncVal('trimConvString',$cursor->r["callnum"]);
                $xls->sncVal('trimConvString',"0");
                
                $xls->sncVal('trimConvString',$status_stat[10]);
                $xls->sncVal('trimConvString',$status_stat[15]);
                $xls->sncVal('trimConvString',$status_stat[20]);
                $xls->sncVal('trimConvString',$status_stat[25]);
                $xls->sncVal('trimConvString',$status_stat[30]);
                $xls->sncVal('trimConvString',$status_stat[40]);
                $xls->sncVal('trimConvString',$status_stat[45]);
                $xls->sncVal('trimConvString',$status_stat[50]);
                $xls->sncVal('trimConvString',$status_stat[55]);
                $xls->sncVal('trimConvString',$status_stat[60]);
                $xls->sncVal('trimConvString',$status_stat[65]);
                
                $xls->sncVal('trimConvString',$cursor->r["port_num"]);
                $xls->sncVal('trimConvString',$cursor->r["install"]);
                $xls->sncVal('trimConvString',$cursor->r["month_pay"]);
                $xls->sncVal('trimConvString',$cursor->r["setting"]);
                $xls->sncVal('trimConvString',$cursor->r["payback"]);
                $xls->sncVal('trimConvString',$cursor->r["comment"]);
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
                    //d($physnum." ".$h);
                } // ^^ while ^^
                $cursorLid->free();
                //
                ////////////////////////////////////////////////////////////////
                // суммируем собственный физ.объём самого проекта 
                $physnum=0;
                $cursorLid=SQL(com_object_query("2","=". $cursor->r["project_id"]));
                while ($cursorLid->assoc()) {
                    $xls->scVal($nextcolumn,$h+2,'trimConvString',$cursorLid->r["cnaname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["subename"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["ooname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["cename"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["bname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["ccmname"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmcapacity1"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmcapacity2"]);
                    $xls->sncVal('trimConvString', ((!empty($cursorLid->r["ccmamount"])) ? round($cursorLid->r["ccmamount"]) : "1") );
                    $xls->sncVal('trimConvString', ((!empty($cursorLid->r["ccmlen"])) ? $cursorLid->r["ccmlen"] : "1.0") );
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmprice"]);
                    $xls->sncVal('trimConvString', (($cursorLid->r["bid"]==1)? "0.7" : "1.0") );
                    $expense=round($cursorLid->r["rcmprice"] 
                                * (($cursorLid->r["ccmlen"]!=0)? $cursorLid->r["ccmlen"] : 1.0) 
                                * (($cursorLid->r["ccmamount"]!=0)? $cursorLid->r["ccmamount"] : 1.0) 
                                * (($cursorLid->r["bid"]==1)? 0.7 : 1.0),2); // если "хозспособ", то *0.7
                    $all_expense+=$expense;
                    $xls->sncVal('trimConvString',$expense ."");
                    $xls->sncVal('trimConvString',$cursorLid->r["rcmunit"]);
                    $xls->sncVal('trimConvString',$cursorLid->r["comment"]);
                    if($physnum>0){
                        for($pncol=0;$pncol<32;$pncol++)
                            $xls->aSheet->setCellValueByColumnAndRow($pncol, $h+2, $xls->aSheet->getCellByColumnAndRow($pncol, $h+2-1)->getValue());
                    }
                    $physnum++;
                    $h++;
                    //d($physnum." ".$h);
                    
                } // ^^ while ^^
                $cursorLid->free();
                $xls->scVal(3,$h+2-$physnum,'trimConvString',$all_expense);     
                $xls->scVal(6,$h+2-$physnum,'trimConvString', round(($cursor->r["deficient"]!=0 ? $all_expense / $cursor->r["deficient"] : '0.0'),2) );     
                //d($physnum." ".$h);
                //break;
                //
                if($physnum==0) $h++; // физ.объёма не было
                //if($h>2100) break;
            } // ^^ while ^^
            $cursor->free();
            //////////////////////////////////////////////////////////////////// 
            //d(2);
            $xls->save();
            //$file_path = $uploaddir . $fname;
            // начинаем непосредственно выгрузку на место пользователя
            header("Content-Disposition: attachment; filename=\"Выгрузка заявок " . $udate_time3 . ".xls\""); // имя файла котрое будет при сохранение
            header("Content-Type: application/force-download"); // вызвать загрузку
            header("Content-type: application/octet-stream"); // это тоже для скачики второй вариант
            header("Content-length: " . filesize($xls->filename)); // ещё нужно размер указать
            echo file_get_contents($xls->filename);
        break; // case "11"
        ////////////////////////////////////////////////////////////////////////
        case "2": // Выгрузка заявок CSV
// создать файл:
            $uploaddir = "./uploads/downloaders/csv/"; // права для www-data
            $fname = hash_user() . ".csv";
            $fp = fopen($uploaddir . $fname, "w+");
            flock($fp, LOCK_EX);
            $caption = "Номер заявки в АРМ; № проекта;УЭС (АРМ);ЛТЦ;Технология;Статус;НЛС;ФИО;Контактный номер;".
                    "Населенный пункт;Улица;Дом;Корпус;Квартира;Услуги;".
                    "Рассрочка (мес.);Инстал.платёж;Гарант.инст.платежа;Тариф;Ежемес.платёж;".
                    "ONT: полная оплата;аренда;Роутер: полная оплата;аренда;".
                    "Приставки: кол-во;инст.сумма;сумма аренды;".
                    "Фактические затраты на ввод;Состояние ввода до ДХ абонента;".
                    "Планируемая дата установки;Дата подключения;Комментарий ТБ";
            // Добавляем поля АРМ в полном составе:
            //$result_armfields = qSQL("SELECT * FROM dictionary where `table`='ps_list' order by serial");
            //while ($row_armfields = mysql_fetch_array($result_armfields))
            //    $caption = $caption . ";" . $row_armfields["full_name"];
            fwrite($fp, $caption);
            $h = 1;
            $cursor=SQL($_REQUEST['reestr_query']);
            while ($cursor->assoc()) {
                $new_string = "\n\"".$cursor->r["arm_id"] ."\";\"".$cursor->r["project_id"] ."\";\"".$cursor->r["ues_arm"] ."\";\""
                        .$cursor->r["ltc"] ."\";\"" .$cursor->r["tp_name"]. "\";\"" .$cursor->r["int_status_name"] ."\";\"".$cursor->r["nls"] ."\";\""
                        .$cursor->r["client_fio"] ."\";\"".$cursor->r["contact_phone"] ."\";\""
                        .$cursor->r["settlement"] ."\";\"".$cursor->r["ul"] ."\";\"".$cursor->r["home"] ."\";\"".$cursor->r["corp"] ."\";\""
                        .$cursor->r["room"] ."\";\"".$cursor->r["service"] ."\";\"".$cursor->r["deferredpay"] ."\";\""
                        .$cursor->r["install"] ."\";\"".$cursor->r["guarantee"] ."\";\""
                        .$cursor->r["tariffname"] ."\";\"".$cursor->r["month_pay"] ."\";\""
                        .$cursor->r["ontfullpay"] ."\";\"".$cursor->r["ontlease"] ."\";\""
                        .$cursor->r["routefullpay"] ."\";\"".$cursor->r["routelease"] ."\";\""
                        .$cursor->r["attachnum"] ."\";\"".$cursor->r["attachfullpay"] ."\";\""
                        .$cursor->r["attachlease"] ."\";\"".$cursor->r["realcost"] ."\";\""
                        .$cursor->r["substatus"] ."\";\"".explode(" ",$cursor->r["targetdate"])[0] ."\";\"".explode(" ",$cursor->r["finishdate"])[0] ."\";\""
                        .$cursor->r["prompt"] ."\"";
                fwrite($fp, $new_string);
                $new_string = '';
                $h++;
            } // ^^ while ^^
            $cursor->free();
            flock($fp, LOCK_UN);
            fclose($fp);
            $file_path = "./uploads/downloaders/csv/" . $fname;
            // начинаем непосредственно выгрузку на место пользователя
            header("Content-Disposition: attachment; filename=\"Выгрузка заявок " . $udate_time3 . ".csv\""); // имя файла котрое будет при сохранение
            header("Content-Type: application/force-download"); // вызвать загрузку
            header("Content-type: application/octet-stream"); // это тоже для скачики второй вариант
            header("Content-length: " . filesize($file_path)); // ещё нужно размер указать
            echo file_get_contents($file_path);

        break; // case "1"
        case "3": // старая версия Выгрузка заявок
// создать файл:
            $uploaddir = "./uploads/downloaders/csv/"; // права для www-data
            $fname = hash_user() . ".csv";
            $fp = fopen($uploaddir . $fname, "w+");
            flock($fp, LOCK_EX);
            //$caption = "SID;Номер заявки в АРМ; Адрес заявки;Статус;Кластер;Проект;АРМ Статус;Дата обращения;Технология (Услуга);Затраты Оборудование;Затраты СМР;Файл схемы;Инсталляционный платеж;Ежемесяцный платеж;Адрес в Google;Уникальный номер в Google;Точность определения координат;Координаты;Тех.возможность;Требуется МС;Требуется РС;Требуется Абон. линия;Требуется Абон. ввод;Необходимость установки Шакафа 42U;Необходимость установки шасси OLT;Требуемое число портов;Необходимость установки оборудования СПД;Наличие PON;Комментарий ТБ";
            $caption = "SID;Номер заявки в АРМ; Адрес заявки;Статус;Кластер;Проект;АРМ Статус;Дата обращения;Технология (Услуга);Затраты Оборудование;Затраты СМР;".
                    "Файл схемы;Инсталляционный платеж;Ежемесяцный платеж;Адрес в Google;Уникальный номер в Google;Точность определения координат;Координаты;".
                    "Тех.возможность;Фактические затраты на ввод;Состояние ввода до ДХ абонента;Планируемая дата установки;Дата подключения;".
                    "Необходимость установки Шкафа 42U;Необходимость установки шасси OLT;Требуемое число портов;".
                    "Необходимость установки оборудования СПД;Наличие PON;Комментарий ТБ";
            // Добавляем поля АРМ в полном составе:
            $result_armfields = qSQL("SELECT * FROM dictionary where `table`='ps_list' order by serial");
            while ($row_armfields = mysql_fetch_array($result_armfields))
                $caption = $caption . ";" . $row_armfields["full_name"];
            fwrite($fp, $caption);
            //if (!@$_POST["cid"] or count(@$_POST["cid"]) < 1) die("<br><b style='color: red;'>Ошибка. Ни одна заявка не была выбрана.</b>");
            //$kk = 0;
            $h = 1;
            $result_reestr_query=qSQL($_REQUEST['reestr_query']);
            //while ($kk < count($_POST["cid"])) {
            while ($row_result_reestr_query = mysql_fetch_array($result_reestr_query)) {
//echo "<br>".$k." = ".$_POST["cid"][$k];
//date_format(add_date,'%d.%m.%Y %H:%i:%s')
                //$result_cids = qSQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date "
                //        . "FROM ps_list psl, ps_list_dop psld WHERE psl.list_id=psld.list_id and psl.arm_id='" . $_POST["cid"][$kk] . "'");
                //$result_cids = qSQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
                //    FROM ps_list psl, ps_list_dop psld WHERE psl.list_id=psld.list_id and psl.arm_id='".$row_result_reestr_query["arm_id"].
                //        "' and psld.tpid='".$row_result_reestr_query["tpid"]."'");
                if(empty($row_result_reestr_query["project_id"])) $project_name='';
                else $project_name=$row_result_reestr_query["project_id"].". ".
                        rSQL("select project_name from ps_project where project_id=".$row_result_reestr_query["project_id"])["project_name"];
                $result_cids = qSQL("SELECT psl.*, psld.*,date_format(psl.dateinbegin,'%d.%m.%Y') as insert_date 
                    FROM ps_list psl, ps_list_dop psld WHERE psl.list_id=psld.list_id and psld.lid=".$row_result_reestr_query["lid"]."");
                while ($row_cids = mysql_fetch_array($result_cids)) { // Перебор заявок с одним arm_id	
// Поиск Кластера по координатам (Если не найден, то сохранить в новом поле: claster_id)
//echo "<br>ARM_ID = ".$_POST["cid"][$kk]."</b>";	
//echo " - latlng = ".$row_cids["latlng"];
//echo " - claster_id = ".$row_cids["claster_id"];
                    /*if (@$row_cids["latlng"] != '' and @ $row_cids["claster_id"] == '0') { // Если есть координаты
//********************************************************************************************	CLASTER
                        list ($ulat_x, $ulng_y) = explode(":", $row_cids["latlng"]);
                        $finded[0]['x'] = $ulat_x;
                        $finded[0]['y'] = $ulng_y;
                        $result_claster = qSQL("SELECT claster_id FROM ps_claster group by claster_id");
                        while ($row_claster = mysql_fetch_array($result_claster)) {
//echo "<br> - Поиск координаты latlng = ".$row_cids["latlng"]." в Кластере № ".$row_claster["claster_id"];
// Заполнение массива вершин многоугольника
                            $result_19 = qSQL("SELECT * FROM ps_claster where claster_id='" . $row_claster["claster_id"] . "'");
                            $claser_array = array();
                            $k = 1;
                            while ($row_19 = mysql_fetch_array($result_19)) {
                                $claser_array[$k][0] = $row_19["lat"];
                                $claser_array[$k][1] = $row_19["lng"];
                                $k++;
                            }
                            $arPolygon = $claser_array; // Собрали массив вершин кластера
// Запускаем поиск
                            $polygon->set_polygon($arPolygon);
                            $result_polygon_calc = $polygon->calc([
                                'x' => $finded[0]['x'],
                                'y' => $finded[0]['y'],
                            ]);
//echo "<br>result_polygon_calc = ".$result_polygon_calc;
                            if ($result_polygon_calc == 1 or $result_polygon_calc == -1) {
                                $claster_num = $row_claster["claster_id"];
                                // Сохраним найденный кластер в ps_list_dop.claster_id
                                $result_update1 = qSQL("UPDATE ps_list_dop SET claster_id='" . $row_claster["claster_id"] . "' WHERE list_id='" . $row_cids["list_id"] . "'");
                                break;
                            } //else		//echo " - не нашли...";
                        }
//********************************************************************************************	/CLASTER
                    } // Если есть координаты
                    else */ 
                    $claster_num = $row_cids["claster_id"];
                    if ($row_cids["claster_id"]=='' or $row_cids["claster_id"]=='0'){
                        $claster_num = "Не определен";
                    } else {
                        $claster_num .= ". ".$row_result_reestr_query["clusname"];
                    }
                    $result_ps_status = qSQL("SELECT name FROM ps_status WHERE id='" . $row_cids["status"] . "'");
                    $row_ps_status = mysql_fetch_array($result_ps_status);
                    $ps_status_name = $row_ps_status["name"];
                    $row_cids["zatrat_smr"] = number_format($row_cids["zatrat_smr"], 2, '.', ' ');
                    $row_cids["dev_summ"] = number_format($row_cids["dev_summ"], 2, '.', ' ');
                    $row_cids["install"] = number_format($row_cids["install"], 2, '.', ' ');
                    $row_cids["month_pay"] = number_format($row_cids["month_pay"], 2, '.', ' ');

                    $result_fo = qSQL("SELECT * FROM files where otype=2 and oid=".$row_cids["lid"] ."");
                    if (mysql_num_rows($result_fo) > 0)
                        $shema_files = "x";
                    else
                        $shema_files = '';
/*                    if (@$row_cids["difficult_mc"] == 1)
                        $print_difficult_mc = "Да";
                    else
                        $print_difficult_mc = "Нет";*/
                    if (@$row_cids["difficult_rs"] == 1)
                        $print_difficult_rs = "Да";
                    else
                        $print_difficult_rs = "Нет";
                    if (@$row_cids["difficult_abl"] == 1)
                        $print_difficult_abl = "Да";
                    else
                        $print_difficult_abl = "Нет";
                    if (@$row_cids["difficult_abv"] == 1)
                        $print_difficult_abv = "Да";
                    else
                        $print_difficult_abv = "Нет";
                    if (@$row_cids["shkaf_42u"] == 1)
                        $shkaf_42u = "Да";
                    else
                        $shkaf_42u = "Нет";
                    if (@$row_cids["shassi_olt"] == 1)
                        $shassi_olt = "Да";
                    else
                        $shassi_olt = "Нет";
                    $kol_ports = @$row_cids["kol_ports"];
                    if (@$row_cids["spd"] == 1)
                        $spd = "Да";
                    else
                        $spd = "Нет";
                    if (@$row_cids["pon_flag"] == 1)
                        $pon = "Да";
                    else
                        $pon = "Нет";
                    /* if (@$row_cids["teh_vojm"]==1) $row_cids["teh_vojm"] = "есть";
                      elseif (@$row_cids["teh_vojm"]=='0') $row_cids["teh_vojm"] = "нет";
                      elseif (@$row_cids["teh_vojm"]=='' or !@$row_cids["teh_vojm"]) $row_cids["teh_vojm"] = "неизвестно";
                     */
                    $row_cids["point_address"] = ins_text(@$row_cids["point_address"]);

                    /* $len_prom = $row_cids["promt"];
                      $lchar_prom = substr($row_cids["promt"],-1); // Последний символ в строке
                      if ($lchar_prom=='"')	$row_cids["promt"] = substr($row_cids["promt"],0,($len_prom-1));
                     */
//$row_cids["all_lastmil"] = str_replace(".", ",", @$row_cids["all_lastmil"]);
//if ($row_dev["price"]!=0)	$row_dev["price"] = str_replace(".", ",", $row_dev["price"]);
//SID;Номер заявки в АРМ; Адрес заявки;Статус;Кластер;АРМ Статус;Дата обращения;Технология (Услуга); (".@$row_cid["service"].")
                    /*$new_string = "\n\"" . $row_cids["list_id"] . "\";\"" . $row_cids["arm_id"] . "\";\"" . $row_cids["device_address"] . "\";\"" . $ps_status_name . "\";\""
                            . @$claster_num . "\";\"" .$project_name. "\";\"" . $row_cids["status_name"] . "\";\"" . $row_cids["date_talking"]
                            . "\";\"" . @$row_cids["technology"] . "(" . @$row_cids["service"] . ")\";\"" . $row_cids["zatrat_smr"] . "\";\"" . $row_cids["dev_summ"] . "\";\"" . @$shema_files . "\";\"" . $row_cids["install"] . "\";\"" . $row_cids["month_pay"]
                            . "\";\""
                            . $row_cids["formatted_address"] . "\";\"" . $row_cids["place_id"] . "\";\"" . $row_cids["location_type"]
                            . "\";\"" . $row_cids["latlng"] . "\";\""
                            . $row_cids["available_tvp"] . "\";\"" . $print_difficult_mc . "\";\"" . $print_difficult_rs
                            . "\";\"" . $print_difficult_abl . "\";\"" . $print_difficult_abv
                            . "\";\"" . $shkaf_42u . "\";\"" . $shassi_olt . "\";\"" . $kol_ports . "\";\"" . $spd . "\";\""
                            . $pon . "\";\"" . @$row_cids["comment"] . "\"";*/
                    $new_string = "\n\"" . $row_cids["list_id"] . "\";\"" . $row_cids["arm_id"] . "\";\"" . $row_cids["device_address"] . "\";\"" . $ps_status_name . "\";\""
                            . @$claster_num . "\";\"" .$project_name. "\";\"" . $row_cids["status_name"] . "\";\"" . $row_cids["date_talking"]
                            . "\";\"" . @$row_cids["technology"] . "(" . @$row_cids["service"] . ")\";\"" . $row_cids["zatrat_smr"] . "\";\"" . $row_cids["dev_summ"] . "\";\"" . @$shema_files . "\";\"" . $row_cids["install"] . "\";\"" . $row_cids["month_pay"]
                            . "\";\""
                            . $row_cids["formatted_address"] . "\";\"" . $row_cids["place_id"] . "\";\"" . $row_cids["location_type"]
                            . "\";\"" . $row_cids["latlng"] . "\";\""
                            . $row_cids["available_tvp"] . "\";\"" . $row_cids["realcost"] . "\";\"" . $row_cids["substatus"]
                            . "\";\"" . explode(" ",$row_cids["targetdate"])[0] . "\";\"" . explode(" ",$row_cids["finishdate"])[0]
                            . "\";\"" . $shkaf_42u . "\";\"" . $shassi_olt . "\";\"" . $kol_ports . "\";\"" . $spd . "\";\""
                            . $pon . "\";\"" . @$row_cids["comment"] . "\"";
// Добавляем значение полей из АРМ-отчета:
                    //$result_arm_data = qSQL("SELECT * FROM ps_list where list_id='" . $row_cids["list_id"] . "'");
                    //$row_arm_data = mysql_fetch_array($result_arm_data);
                    $row_arm_data=rSQL("SELECT * FROM ps_list where list_id='".$row_cids["list_id"] ."'", MYSQLI_NUM);
                    for ($pl = 4; $pl <= 130; $pl++) {
                        if (@$row_arm_data[$pl] != '')
                            $row_arm_data[$pl] = str_replace(";", "", $row_arm_data[$pl]);
                        if (@$row_arm_data[$pl] != '')
                            $row_arm_data[$pl] = str_replace("\n", "", $row_arm_data[$pl]);
                        $new_string = $new_string . ";" . @$row_arm_data[$pl];
                    }
                    fwrite($fp, $new_string);
                    $new_string = '';
                } // ^^ while ($row_cids = mysql_fetch_array($result_cids)) { // Перебор заявок с одним arm_id ^^
                //$kk++;
                $h++;
            } // ^^ while ^^
            flock($fp, LOCK_UN);
            fclose($fp);
            $file_path = "./uploads/downloaders/csv/" . $fname;
            // начинаем непосредственно выгрузку на место пользователя
            header("Content-Disposition: attachment; filename=\"Выгрузка заявок " . $udate_time3 . ".csv\""); // имя файла котрое будет при сохранение
//header("Content-Disposition: attachment; filename=\"1.csv\""); // имя файла котрое будет при сохранение
            header("Content-Type: application/force-download"); // вызвать загрузку
            header("Content-type: application/octet-stream"); // это тоже для скачики второй вариант
            header("Content-length: " . filesize($file_path)); // ещё нужно размер указать
            echo file_get_contents($file_path);

            /* echo "
              <SCRIPT language=javascript type=\"text/javascript\">
              function wclose()
              {
              setTimeout('', 1000);
              window.close();
              }
              wclose();
              </SCRIPT>"; */

        break; // case "2"
    }
}
@mysql_close(@$link);
/*
  geometry – содержит следующую информацию:

  location – геокодированные значения широты и долготы. Как правило, в обычных поисках адресов это поле является наиболее важным.
  location_type – хранит дополнительные данные об указанном месте. В настоящее время поддерживаются следующие значения:

  "ROOFTOP" – указывает, что возвращаемый результат является точным геокодом, для которого имеется информация о месте с точным почтовым адресом.
  "RANGE_INTERPOLATED" – указывает, что возвращаемый результат содержит приближенное значение (обычно на дороге), полученное посредством интерполяции двух точных значений (например, перекрестков). Интерполированные результаты обычно возвращаются, если для почтового адреса недоступны геокоды номеров зданий.
  "GEOMETRIC_CENTER" – указывает на возвращение геометрического центра результата, например, ломаной линии (улицы) или многоугольника (района).
  "APPROXIMATE" – указывает на возвращение приближенного результата.
  viewport – содержит рекомендуемую область просмотра возвращаемого результата, которая указывается в виде двух пар значений (широта и долгота), обозначающих юго-западный и северо-восточный углы ограничивающего прямоугольника области просмотра. Как правило, область просмотра используется, чтобы очертить границы результата при его отображении пользователям.
  bounds (возвращается по желанию) – хранит ограничивающий прямоугольник, который может полностью содержать возвращаемый результат. Эти границы могут не соответствовать рекомендуемой области просмотра. (Например, в состав Москвы входит Зеленоград, который технически является частью города, но не должен отображаться в области просмотра.)
 */
?>