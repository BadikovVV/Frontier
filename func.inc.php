<?php /* Created on: 23.04.2013 */ ?>
<?php
// Вывод ссылки на информацию о пользователя в системе
//function usr_card($uid,$back=0)
//{
//$query_uid = "SELECT * FROM gusers WHERE id='".$uid."' ;";
//	$result_uid = mysql_query($query_uid) or die ("<img src='./images/error.jpg' align='absmiddle'> <b style='color: red;'>Ошибка!</b><p> ".mysql_errno().": ".mysql_error());
//		$row_uid = mysql_fetch_array($result_uid);
//if (@$back==1)	$add_back = "&back=1";	else	$add_back = '';
//if (@$back==2)	$add_style = " style='font-weight: normal;'";	else	$add_style = '';
//$result = "<a".@$add_style." href='./window.php?action=uinfo".@$add_back."&uid=".$uid."'  onclick=\"return openNewWindow(this)\" title='Информация'><img src='./images/mini_card.gif' align='absmiddle'> ".$row_uid["fio"]."</a>";
//
//return $result; 
//}
// Вырезаем пробелы в начале и в конце строки и заменяем зяпятую на точку. Для Чисел с плавающей точкой.
function ins_num($num)
{
$num = str_replace(' ', '', $num);
$num = str_replace(',', '.', $num);
$result = trim($num);

return $result; 
}
function ins_num_xls($num)
{
$num = str_replace(' ', '', $num);
$num = str_replace('.', ',', $num);
$result = trim($num);

return $result; 
}

function ins_text($utext) {
    $apost_c = substr_count($utext, '"');
    if (($apost_c > 0) and ( $apost_c & 1)) {
        $utext = str_replace("\"", '', $utext);
    }
    $utext = str_replace(";", ",", $utext);
    $utext = str_replace("\r\n\r\n", '', $utext);
    $utext = str_replace("\r\n", ' ', $utext);
    $utext = str_replace("\r", " ", $utext);
    $utext = str_replace("\n", " ", $utext);
    $utext = str_replace("'", "\"", $utext);
    $result = trim($utext);

    return $result;
}

function date_morec($udate)
{
// Формат входящей даты 00.00.0000
	if ($udate!='' and $udate!="00.00.0000")
		{
			$query_psmr = "SELECT STR_TO_DATE('".$udate."', '%d.%m.%Y') > CURDATE();";
				$result_psmr = mysql_query($query_psmr) or die ("Query failed: psmr");
					$row_psmr = mysql_fetch_array($result_psmr);
			$result = $row_psmr[0];	
		}
return $result; 
}
// 1 > 2 Да? (1) : Нет? (0) 
function date_more2($udate1,$udate2,$andravro="0")
{
// Формат входящей даты 00.00.0000
	if ($udate1!='' and $udate1!="00.00.0000" and $udate2!='' and $udate2!="00.00.0000")
		{
			if (@$andravro == 1)	$ravno = "=";
			$query_psmr = "SELECT STR_TO_DATE('".$udate1."', '%d.%m.%Y') >".@$ravno." STR_TO_DATE('".$udate2."', '%d.%m.%Y');";
				$result_psmr = mysql_query($query_psmr) or die ("Query failed: psmr");
				
					$row_psmr = mysql_fetch_array($result_psmr);
			$result = $row_psmr[0];	
		}
return @$result; 
}
function zatrat_500($zatrati)
{
	if (($zatrati * 1.18)>=500)
		$result = 1;	else	$result = 0;
return $result; 
}
function howmore($date_start, $date_end){
if (@$date_start and @$date_start!="0000-00-00" and @$date_start!=0 and @$date_end and @$date_end!="0000-00-00" and @$date_end!=0)
{
list ($uy1,$um1,$ud1) = explode ("-",$date_start);
list ($uy2,$um2,$ud2) = explode ("-",$date_end);
  $time1 = mktime(0, 0, 0, $um1, $ud1, $uy1 );
  $time2 = mktime(0, 0, 0, $um2, $ud2, $uy2 );
  if ($time2 >= $time1)	$delta = $time2 - $time1;	else	$delta = 1;
  	if ($delta!=1)	$result = max(round($delta/(24*60*60)), 1);
		else	$result = 1;
}	else	$result = 0;

    return $result;
} 
//
function fname_test($usfile,$usection)
{
for ($g=1;$g<500;$g++)
{
        if ($g == 1)	{	$change_fname = $usfile;	$result = $change_fname;	}
        $query_ftest = "SELECT * FROM gfiles_tmp WHERE section='".$usection."' and file_name='".$change_fname."';";
                $result_ftest = mysql_query($query_ftest) or die ("Query failed Sel from ftest");
                        if (mysql_num_rows($result_ftest)>=1)
                                {
                                        // Переименовываем файл, текущее имя файла не допустимо
                                        $fmime = substr(strrchr($usfile,'.'), 1);
                                        list($fname_alone,) = explode ($fmime,$usfile);
                                        $fname_alone = substr($fname_alone, 0, -1);
                                        $change_fname = $fname_alone."_".$g.".".$fmime;
                                        $result = $change_fname;
                                }	else	break;
}
return $result; 
}
//
function ret_date($get_status,$get_cid)
{
switch ($get_status)
	{
	case "0":
	//----- 0
	$query_0e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_0 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND atype =  'add_card' );";
			$result_0e = mysql_query($query_0e) or die ("Query failed:0e");
				$row_0e = mysql_fetch_array($result_0e);
		$date_enter = $row_0e['de_0'];

	break;
	case "1":
	//----- В проработке
	$query_1e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_1 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND (atype =  'send_work' or atype =  'get_return') );";
		//$query_1e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_1 FROM ghistory WHERE bck_id='".$get_cid."' and atype='send_agreement';"; // Согласование ДФ
			$result_1e = mysql_query($query_1e) or die ("Query failed: 1e");
				$row_1e = mysql_fetch_array($result_1e);
		$date_enter = $row_1e['de_1'];

	break;
	case "8":
	//----- Отклонено
	$query_8e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_8 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND (atype =  'get_return' or `status`=8) );";
			$result_8e = mysql_query($query_8e) or die ("Query failed: 8e");
				$row_8e = mysql_fetch_array($result_8e);
		$date_enter = $row_8e['de_8'];

	break;
	case "7":
		//----- Согласование ДФ
		$query_7e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_7 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND (atype =  'send_agreement' or `status`=7) );";
		//$query_7e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_7 FROM ghistory WHERE bck_id='".$get_cid."' and atype='send_agreement';"; // Согласование ДФ
			$result_7e = mysql_query($query_7e) or die ("Query failed: 7e");
				$row_7e = mysql_fetch_array($result_7e);
		$date_enter = $row_7e['de_7'];
	break;
	case "6":
		//----- ТВ проработана
		$query_6e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_6 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."' AND (atype =  'get_tvcomplete' or `status`=6) );";
		//$query_6e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_6 FROM ghistory WHERE bck_id='".$get_cid."' and atype='get_tvcomplete';"; // При наличии статуса Расчет конечных затрат
			$result_6e = mysql_query($query_6e) or die ("Query failed: 6e");
				$row_6e = mysql_fetch_array($result_6e);
		$date_enter = $row_6e['de_6'];
	break;
	case "5":
		//----- Выделение инвестиций
		$query_5e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_5 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."' AND (atype =  'send_done1' or atype =  'get_kp' or `status`=5));";
		//$query_5e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_5 FROM ghistory WHERE bck_id='".$get_cid."' and atype='send_done1';"; 
			$result_5e = mysql_query($query_5e) or die ("Query failed: 5e");
				$row_5e = mysql_fetch_array($result_5e);
		$date_enter = $row_5e['de_5'];
			break;
	case "9":
		//----- В реализации
		$query_9e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_9 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND (atype =  'to_release' or `status`=9) );";
		//$query_9e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_9 FROM ghistory WHERE bck_id='".$get_cid."' and atype='to_release';";
			$result_9e = mysql_query($query_9e) or die ("Query failed: 9e");
				$row_9e = mysql_fetch_array($result_9e);
		$date_enter = $row_9e['de_9'];
	break;
	case "11":
		//----- К включению
		$query_11e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_11 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND (atype =  'to_on' or `status`=11) );";
		//$query_11e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_11 FROM ghistory WHERE bck_id='".$get_cid."' and atype='to_on';";
			$result_11e = mysql_query($query_11e) or die ("Query failed: 11e");
				$row_11e = mysql_fetch_array($result_11e);
		$date_enter = $row_11e['de_11'];
	break;
	case "2":
		//----- Подключено
		$query_2e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_2 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND (atype =  'on' or `status`=2) );";
		//$query_2e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_2 FROM ghistory WHERE bck_id='".$get_cid."' and atype='on';";
			$result_2e = mysql_query($query_2e) or die ("Query failed: 2e");
				$row_2e = mysql_fetch_array($result_2e);
		$date_enter = $row_2e['de_2'];
	break;
	case "10":
		//----- Архив
		$query_10e = "SELECT date_format(dtime,'%d.%m.%Y %H:%i:%s') de_10 FROM ghistory WHERE id = (SELECT MAX(id) FROM ghistory WHERE bck_id =  '".$get_cid."'  AND (atype =  'arh' or `status`=10) );";
		//$query_10e = "SELECT max(id) end_id,date_format(dtime,'%d.%m.%Y %H:%i:%s') de_10 FROM ghistory WHERE bck_id='".$get_cid."' and atype='arh';";
			$result_10e = mysql_query($query_10e) or die ("Query failed: 10e");
				$row_10e = mysql_fetch_array($result_10e);
		$date_enter = $row_10e['de_10'];
	break;
}
//***********************

return $date_enter; 
}
//
function get_duration ($date_from, $date_till) { //Формат дат - dd.mm.yyyy
	
	if (!@$date_till or $date_till=='' or $date_till=='00.00.0000') {
		$date_till = date ("d.m.Y");
	}
	$diff = (strtotime($date_till) - strtotime($date_from)) / 86400;

	return $diff;
    }
//
function get_count_work_day($countDay,$dateStart){	// Пересчет количества рабочих дней ($countDay) в количеств календарных дней от даты ($dateStart)
	$timing = $countDay; 
	$good_day = 0;
	$cur_date = $dateStart;
	
	
	for ($h=1;$h<=$timing;$h++)
	{
		$query_bdate = "SELECT DAYOFWEEK(str_to_date('".$cur_date."','%d.%m.%Y %H:%i:%s') + INTERVAL 1 DAY);";
		$result_bdate = mysql_query($query_bdate) or die ("Query failed: bdate");
			$row_bdate = mysql_fetch_array($result_bdate);								
		
		if ($row_bdate[0] == 7 or $row_bdate[0] == 1) $timing = $timing + 1; else $good_day = $good_day + 1;
		
		$query_bdate1 = "SELECT date_format(str_to_date('".$cur_date."','%d.%m.%Y %H:%i:%s') + INTERVAL 1 DAY,'%d.%m.%Y %H:%i:%s');";
		$result_bdate1 = mysql_query($query_bdate1) or die ("Query failed: bdate1");
			$row_bdate1 = mysql_fetch_array($result_bdate1);
		$cur_date = $row_bdate1[0];
		if ($good_day == $countDay) break;
	}									
	$result = $timing;
	
	return $result;
}	
//	
function get_date_work_day($countDay,$dateStart){	// Определение даты выполнения путем добавления рабочих дней
    $timing = $countDay; // Контрольный срок / Выгрузка из таблицы !!!
    $good_day = 0;
    $cur_date = $dateStart;
    for ($h=1;$h<=$timing;$h++)
    {
            $query_bdate = "SELECT DAYOFWEEK(str_to_date('".$cur_date."','%d.%m.%Y %H:%i:%s') + INTERVAL 1 DAY);";
            $result_bdate = mysql_query($query_bdate) or die ("Query failed: bdate");
                    $row_bdate = mysql_fetch_array($result_bdate);								

            if ($row_bdate[0] == 7 or $row_bdate[0] == 1) $timing = $timing + 1; else $good_day = $good_day + 1;

            $query_bdate1 = "SELECT date_format(str_to_date('".$cur_date."','%d.%m.%Y %H:%i:%s') + INTERVAL 1 DAY,'%d.%m.%Y %H:%i:%s');";
            $result_bdate1 = mysql_query($query_bdate1) or die ("Query failed: bdate1");
                    $row_bdate1 = mysql_fetch_array($result_bdate1);
            $cur_date = $row_bdate1[0];
            if ($good_day == $countDay) break;
    }									
    $query_bdate2 = "SELECT date_format(str_to_date('".$dateStart."','%d.%m.%Y %H:%i:%s') + INTERVAL ".$timing." DAY, '%d.%m.%Y %H:%i:%s') as sdate;";
                    $result_bdate2 = mysql_query($query_bdate2) or die ("Query failed: bdate2");
                            $row_bdate2 = mysql_fetch_array($result_bdate2);
    $result = $row_bdate2["sdate"];

    return $result;
}
//	
function get_work_day($countDay,$dateStart){	// Определение даты после которой начинается просрочка
	$timing = $countDay; // Контрольный срок / Выгрузка из таблицы !!!
	$good_day = 0;
	$cur_date = $dateStart;
	
	
	for ($h=1;$h<=$timing;$h++)
	{
		$query_bdate = "SELECT DAYOFWEEK(str_to_date('".$cur_date."','%d.%m.%Y %H:%i:%s') - INTERVAL 1 DAY);";
		$result_bdate = mysql_query($query_bdate) or die ("Query failed: bdate");
			$row_bdate = mysql_fetch_array($result_bdate);								
		
		if ($row_bdate[0] == 7 or $row_bdate[0] == 1) $timing = $timing + 1; else $good_day = $good_day + 1;
		
		$query_bdate1 = "SELECT date_format(str_to_date('".$cur_date."','%d.%m.%Y %H:%i:%s') - INTERVAL 1 DAY,'%d.%m.%Y %H:%i:%s');";
		$result_bdate1 = mysql_query($query_bdate1) or die ("Query failed: bdate1");
			$row_bdate1 = mysql_fetch_array($result_bdate1);
		$cur_date = $row_bdate1[0];
		if ($good_day == $countDay) break;
	}									
	$query_bdate2 = "SELECT date_format(str_to_date('".$dateStart."','%d.%m.%Y %H:%i:%s') - INTERVAL ".$timing." DAY, '%d.%m.%Y %H:%i:%s') as sdate;";
			$result_bdate2 = mysql_query($query_bdate2) or die ("Query failed: bdate2");
				$row_bdate2 = mysql_fetch_array($result_bdate2);
	$result = $row_bdate2["sdate"];
	
	return $result;
	
}


function retIdTimeOut($getId,$getDate) // Просрочка по ИД заявке. Формат: №ИД, На дату, Статус, Кол-во рабочих дней в этом статусе
{
	//Длительность СМР с земельными работами 30 раб. дней (38 календарных)
	$dltSmrWithEarthWork = 38;
	$dltSmrWithEarthWorkWD = 30;
	//Длительность СМР без земельных работ 15 раб. дней (21 календарных)
	$dltSmrWithoutEarthWork = 21;
	$dltSmrWithoutEarthWorkWD = 15;
	// Длительности нахождения в статусах (в раб.днях)
	$tvProrabotano = 14;
	$vProrabotke = 1;
	$soglasovanieDf = 1;
	$kVliucheniiu = 2;
	$videlenieInvest = 3;
	$peredachaShemiPodriadchiku = 1;
		
	if ($getDate == '' or $getDate == '0000-00-00' or $getDate == '00.00.0000') $getDate = date ("d.m.Y H:i:s");
	
	$query_getStatus = "SELECT *, date_format(date_shem,'%d.%m.%Y %H:%i:%s') dshem, date_format(date_pltb,'%d.%m.%Y %H:%i:%s') as pl_date FROM bck WHERE id =  '".$getId."';";
		$result_getStatus = mysql_query($query_getStatus) or die ("Query failed: getStatus");
			$row_getStatus = mysql_fetch_array($result_getStatus);
	
	$dateIn = ret_date($row_getStatus["status"],$getId);
	
	switch ($row_getStatus["status"])
	{
		default:
			$result = "";
		
		break;	
		
		case "1":
			
			if ($row_getStatus["project_name"]==18)
			{
				$diff = get_duration($getDate,$row_getStatus["pl_date"]);
				
			} 	else 
				{
					$dayToTimeOut = $vProrabotke;
					$dateControl = get_date_work_day($dayToTimeOut,$dateIn);
					$diff = get_duration($getDate,$dateControl);
				}
			
			if ($diff < 0) $result = "Просрочено"; else $result = "";
			
		break;
		
		case "2":
			$result = '';
		break;
		/*
		case "5":
			
			$dayToTimeOut = $videlenieInvest;
			$dateControl = get_date_work_day($dayToTimeOut,$dateIn);
			$diff = get_duration($getDate,$dateControl);
			
			if ($diff < 0) $result = "Просрочено"; else $result = "";
			
		break;
		*/
		case "6":
			
			$dayToTimeOut = $tvProrabotano;
			$dateControl = get_date_work_day($dayToTimeOut,$dateIn);
			$diff = get_duration($getDate,$dateControl);
			
			if ($diff < 0) $result = "Просрочено"; else $result = "";
			
		break;
		
		case "7":
			
			$dayToTimeOut = $soglasovanieDf;
			$dateControl = get_date_work_day($dayToTimeOut,$dateIn);
			$diff = get_duration($getDate,$dateControl);
			
			if ($diff < 0) $result = "Просрочено"; else $result = "";
			
		break;
		
		case "8":
			$result = '';
		break;
		
		case "9": //?
			$netShemi = 0;
			if ($row_getStatus["earth_work"]=='1') $dayToTimeOut = $dltSmrWithEarthWorkWD; 
				elseif ($row_getStatus["earth_work"]=='2') $dayToTimeOut = $dltSmrWithoutEarthWorkWD;
					else $dayToTimeOut = $row_getStatus["osrok_smr"];
					
			if ($row_getStatus["sposob_rab"]=="podriad") {
				if(@$row_getStatus["dshem"] and $row_getStatus["dshem"]!='' and $row_getStatus["dshem"]!='00.00.0000 00:00:00') 
					$dateInRealise = $row_getStatus["dshem"];
					else { $netShemi = 1; $dayToTimeOut = $peredachaShemiPodriadchiku; $dateInRealise = $dateIn;}
			} else $dateInRealise = $dateIn;
				
			$dateControl = get_date_work_day($dayToTimeOut,$dateInRealise);
			$diff = get_duration($getDate,$dateControl);
			
			if ($diff < 0) 
				if ($netShemi==0) $result = "Просрочено"; 
				else $result = "Просрочено (Схема не передана)"; 
			else $result = "";
		break;
		
		case "10":
			$result = '';
		break;
		
		case "11":
			
			$dayToTimeOut = $kVliucheniiu;
			$dateControl = get_date_work_day($dayToTimeOut,$dateIn);
			$diff = get_duration($getDate,$dateControl);
			
			if ($diff < 0) $result = "Просрочено"; else $result = "";
			
		break;
		
		case "12":
			$result = '';
		break;
		
		case "13":
			$result = '';
		break;

	}
return $result;	
}
//Функция: определение вхождения точки в Полигон
function fPointInsidePolygon($aPolygon = "", $aPoint = "")
{
  $_PolygonSize = count($aPolygon);

  if ($_PolygonSize <= 1):
    $result   = false;
  else:

    $_intersections_num = 0;

    $_prev      = $_PolygonSize - 1;
    $_prev_under= $aPolygon[$_prev][cY] < $aPoint[cY];

    for ($i = 0; $i < $_PolygonSize; ++$i):

      $_cur_under   = $aPolygon[$i][cY] < $aPoint[cY];

      $a[cX]    = $aPolygon[$_prev][cX] - $aPoint[cX];
      $a[cY]    = $aPolygon[$_prev][cY] - $aPoint[cY];

      $b[cX]    = $aPolygon[$i][cX]  - $aPoint[cX];
      $b[cY]    = $aPolygon[$i][cY]  - $aPoint[cY];

      $t = ($a[cX]*($b[cY] - $a[cY]) - $a[cY]*($b[cX] - $a[cX]));

      if (($_cur_under == true) and (!$_prev_under == true)):

        if ($t > 0):
          $_intersections_num++;
        endif;

      endif;

      if ((!$_cur_under == true) and ($_prev_under == true)):

        if ($t < 0):
          $_intersections_num++;
        endif;

      endif;

      $_prev        = $i;
      $_prev_under  = $_cur_under;

    endfor;

    $result = !($_intersections_num & 1) == 0;
  endif;
  return $result;
}
//Формирование массива [индекс][X][Y]
function fSetArrayXY(&$aArray,$aX,$aY,$aNewArray = false)
{
  if($aNewArray == true):
    $aArray     = array();
  endif;
  
  //Количество элементов в массиве
  $_Count =   count($aArray);

  //Группа параметров:
  $aArray[$_Count][cX] = $aX;
  $aArray[$_Count][cY] = $aY;
}  
?>
