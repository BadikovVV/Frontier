<?php 
$ip = getenv ("REMOTE_ADDR"); // �������� ip-����� ������������
/* ����������, ����� �� */
INCLUDE "db_connect.php";
$udate2 = date ("Y-m-d");
$udate3 = date ("d.m.y");
$udate_time3 = date ("d.m.Y H-i-s");
if (@$_GET["fid"] and !@$_GET["unlink"] and @$_GET["unlink"]!=1) {
    $query_sfile = "SELECT *,date_format(add_date,'%d.%m.%Y %H-%i-%s') fdate FROM ps_files WHERE id='".@$_GET["fid"]."';";
	$result_sfile = mysql_query($query_sfile) or die ("Query failed. sfile");
	$row_sfile = mysql_fetch_array($result_sfile);
    $out_file_name = @$row_sfile["file_name"];
    if ($row_sfile["sub_section"]=="vigruzka_all" or $row_sfile["sub_section"]=="vigruzka_all_arh" or 
            $row_sfile["sub_section"]=="vigruzka_timing" or $row_sfile["sub_section"]=="vigruzka_all_time_out" or 
            $row_sfile["sub_section"]=="vigruzka_proj_cif")
    {
        $file_path = "/var/www/html/uploads/downloaders/csv/".@$out_file_name;
        if (file_exists($file_path)==FALSE) // �� ����� ���� � ��������. �������� ��� ������� ����� � ��������� windows-2151 (�������� �� ������ �������)
        {
            // ������������ � �������� ����� ����
            $out_file_name = iconv("windows-1251","UTF-8",@$row_sfile["file_name"]); // �������� ��������� � utf-8 ��� ��������� � ����� ����� ������� PHP
            $file_path = "/var/www/html/uploads/downloaders/csv/".@$out_file_name;
        }
        $filedate = $row_sfile['fdate'];
    } else {
        $file_path = "/var/www/html/uploads/".$row_sfile["section"]."/".@$out_file_name;
        if (file_exists($file_path)==FALSE) // �� ����� ���� � ��������. �������� ��� ������� ����� � ��������� windows-2151 (�������� �� ������ �������)
        {
            // ������������ � �������� ����� ����
            $out_file_name = iconv("windows-1251","UTF-8",@$row_sfile["file_name"]); // �������� ��������� � utf-8 ��� ��������� � ����� ����� ������� PHP
            $file_path = "/var/www/html/uploads/".$row_sfile["section"]."/".@$out_file_name;
        }
    }
if ($row_sfile["sub_section"]=="argus")
    $file_path = "/var/www/html/uploads/loaders/csv/".@$out_file_name;	
if ($row_sfile["sub_section"]=="vigruzka_all")
    header("Content-Disposition: attachment; filename=\"�������� ������ ".$filedate.".csv\""); // ��� ����� ������ ����� ��� ����������
        elseif ($row_sfile["sub_section"]=="vigruzka_all_arh")
        header("Content-Disposition: attachment; filename=\"�������� �������� ������ ".$filedate.".csv\""); // ��� ����� ������ ����� ��� ����������
            elseif ($row_sfile["sub_section"]=="vigruzka_timing")
            header("Content-Disposition: attachment; filename=\"�������� ������ � �������� ����������� �������� ".$filedate.".csv\""); // ��� ����� ������ ����� ��� ����������
                elseif ($row_sfile["sub_section"]=="vigruzka_all_time_out")
                header("Content-Disposition: attachment; filename=\"�������� ������ � ����� ��������� ".$filedate.".csv\""); // ��� ����� ������ ����� ��� ����������
                    elseif ($row_sfile["sub_section"]=="vigruzka_proj_cif")
                    header("Content-Disposition: attachment; filename=\"�������� ������ ������������ ".$filedate.".csv\""); // ��� ����� ������ ����� ��� ����������
                        elseif ($row_sfile["sub_section"]=="argus")
                        header("Content-Disposition: attachment; filename=\"�������� �� ����� ".$row_sfile['fdate'].".csv\""); // ��� ����� ������ ����� ��� ����������
                        else	header("Content-Disposition: attachment; filename=\"".$row_sfile["file_name"]."\""); // ��� ����� ������ ����� ��� ����������
header("Content-Type: application/force-download" ); // ������� ��������
header("Content-type: application/octet-stream"); // ��� ���� ��� ������� ������ �������
header('Content-type: text/html; charset=windows-1251');
header("Content-length: ".filesize($file_path)); // ��� ����� ������ �������
echo file_get_contents($file_path);
/*echo "<SCRIPT language=javascript type=\"text/javascript\">
    function wclose()
    {	
        setTimeout('', 1000);	
        window.close();
    }
    wclose();
    </SCRIPT>";
} elseif(@$_GET["unlink"]==1 and @$_GET["fid"]) {
    // �������� �����\
    if ($row_users_test["access"]=="oks_all")	{
        echo "<SCRIPT language=javascript type=\"text/javascript\">
        function redir()
        {	setTimeout('location.replace(\"./index.php\")', 3500);	}
        redir();
        </SCRIPT>";
        die("<center><br><br><br><br><h1>��������� � �������!</h1></center>");
    // ��������� ������� ����� � ����������
        $query_fdel = "SELECT * FROM gfiles_tmp WHERE id='".$_GET["fid"]."';";
        $result_fdel = mysql_query($query_fdel) or die ("Query failed. fdel");
        $row_fdel  = mysql_fetch_array($result_fdel);
        $file_path = "/var/www/html/uploads/".$row_fdel["section"]."/".@$row_fdel["file_name"];
    // �������� �����, ���� �� ����
    //unlink ($file_path);
    //echo $file_path;
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
            if (mysql_num_rows($result_fdel)==1)
                    {
                    $query_dell_file = "DELETE FROM gfiles_tmp WHERE id='".@$row_fdel["id"]."';";
                    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                            $result_dell_file = mysql_query($query_dell_file) or die ("Query failed Dell from File_List");
                    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                    }
    echo "<SCRIPT language=javascript type=\"text/javascript\">
        function wclose()
        {	
                setTimeout('', 1000);	
                window.close();
        }
        wclose();
        </SCRIPT>";
    }*/
}
@mysql_close(@$link);
?>
