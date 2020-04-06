<!--meta http-equiv="Content-Type" content="text/html; charset=utf-8"-->
<?php
function transform_date (&$tdate,$too)
{
	if ($too=="to_db")
		{
			list($tmp_day,$tmp_mouns,$tmp_year) = explode(".", $tdate);
			//$yyxx = "20"; //первый две цифры года
			$cngd_date = $tmp_year."-".$tmp_mouns."-".$tmp_day;
		}
	if ($too=="from_db")
		{
			list($tmp_year,$tmp_mouns,$tmp_day) = explode("-", $tdate);
			//$tmp_year = substr($tmp_year,-2);
			$cngd_date = $tmp_day.".".$tmp_mouns.".".$tmp_year;
		}
	if ($too=="to_db_time")
		{
			list($tmp_date,$tmp_time) = explode(" ", $tdate);
			list($tmp_day,$tmp_mouns,$tmp_year) = explode(".", $tmp_date);
			//$yyxx = "20"; //первый две цифры года
			$cngd_date = $tmp_year."-".$tmp_mouns."-".$tmp_day." ".$tmp_time;
		}
	if ($too=="from_db_time")
		{
			list($tmp_date,$tmp_time) = explode(" ", $tdate);
			list($tmp_year,$tmp_mouns,$tmp_day) = explode("-", $tmp_date);
			//$tmp_year = substr($tmp_year,-2);
			$cngd_date = $tmp_day.".".$tmp_mouns.".".$tmp_year." ".$tmp_time;
		}
		
		return $cngd_date;
}
function antihack (&$per,$id,$ntable)
{
	$per = str_replace("'", "", $per);
	$per = str_replace("\"", "", $per);
	$per = str_replace("\\", "", $per);
	$per = str_replace("../", "", $per);
	$per = str_replace("..\\", "", $per);
	$per = str_replace("..", "", $per);
	if (!@$per or @$per=='')	$per = FALSE;
	if (@$id=="id")
			if (!@$per or is_numeric(@$per)!=TRUE) $per = FALSE;
	if (@$id=="id" and @$ntable and @$ntable!='')
		{
			$q_tst = "SELECT id FROM ".$ntable." WHERE id='".$per."';";
				$result_tst = mysql_query($q_tst) or die ("Query failed Select tst");
			if (mysql_num_row($result_tst)==0)	$per = FALSE;

		}
	return $per;
}
?>