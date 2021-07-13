<?php
// �������� ������ �� ������� (�� ������ ������� reestr.php),
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/html; charset=windows-1251"); //charset=utf8");
include "db_connect.php";

$mysqli->select_db("private_sector") or die("Could not select database");
$mysqli->query("SET NAMES 'cp1251'");
$mysqli->query("SET CHARACTER SET 'cp1251'");
require_once "PHPExcel/Classes/PHPExcel.php";
require_once 'vlg_util.php';
require_once 'vlg_CExcel.php';
////////////////////////////////////////////////////////////////////////////////
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
    switch ($_REQUEST["func"]) {
////////////////////////////////////////////////////////////////////////////////
        case "1": // �������� �������� �� ������ �������
            // ������ �� ���� �������� ���    
            //d($_REQUEST);
            $area_query="
                SELECT '������' lay_type,psl.list_id,psl.latlng,case when psl.cs=1 then '��' else '��' end cs,psl.technology,psl.service,
                                    psl.client_fio oname, concat(psl.settlement,' ',psl.ul,' ',psl.home,' ',psl.corp) oaddress,
                    psl.contact_phone,ps_status.name status
                    FROM ps_list psl left join ps_list_dop psld on psl.list_id=psld.list_id join ltc 
                        left join ps_status on psld.status=ps_status.id
                    where ltc.lid=".$_REQUEST["ltc_id"]." and psl.ltc like concat('%',ltc.lname,'%')  
                union all
                select mot.mname lay_type,oid,concat(lat,':',lng),'','','',
                                    concat(co.oname,' / ',co.otype) oname, co.oaddress, '' contact_phone, '' status
                                    from com_obj co left join map_obj_type mot on co.lay_type=mot.id            
                union all            
                select mot.mname lay_type,mo.id,latlng,'','','',
                                    mo.oname, '' oaddress, '' contact_phone, '' status
                                    from map_obj mo left join map_obj_type mot on mo.type=mot.id
                    where mo.latlng<>'' and mo.project_id in (select project_id from ps_project where ltc=".$_REQUEST["ltc_id"].")
                union all
                select '���.��.' lay_type,
                    abonent.aid,concat(addrcache.lat,':',addrcache.lng),
                    case when abonent.atype=1 then '��' else '��' end ,
                    case when abonserv.tid=1 then 'xDSL' else 'FTTx' end ,'',
                    abonent.aname, concat(addrcache.locality,' ',addrcache.street,' ',addrcache.building,' ',addrcache.corp) oaddress,
                    abonent.contactphone contact_phone, '' status
                    from abonent join addrcache on abonent.address_id=addrcache.aid 
                        join abonserv on abonent.aid=abonserv.aid
                    where abonent.ltc=".$_REQUEST["ltc_id"]."
                ";
            // vvv ��������� �������������-������� vvv
            //d($area_query);
            if($_REQUEST['area_id'])   $row_map_obj=rSQL("select latlng from map_obj where id=".$_REQUEST['area_id']); // �� ������
            else if($_REQUEST['clustar_id'])   $row_map_obj=rSQL("select coord latlng from cluster where id=".$_REQUEST['clustar_id']); // �� ��������
            else if($_REQUEST['points'])   $row_map_obj["latlng"]=str_replace('_', ' ',$_REQUEST['points']); // �� �������
            else exit(); // ������� �� ������
            //d($_REQUEST['points']);
            //d($row_map_obj);
            $arPolygon = array();
            $row_map_obj_latlng=explode(" ",trim($row_map_obj["latlng"]));
            //d($row_map_obj_latlng);
            for($k=0;$k<count($row_map_obj_latlng);$k++){
                list($arPolygon[$k][0],$arPolygon[$k][1]) = explode(",",$row_map_obj_latlng[$k]);
            }
            $polygon = new Polygon();
            $polygon->set_polygon($arPolygon);
            // ^^^ ��������� �������������-������� ^^^
            //d(1);
            $xls= new CExcel("./uploads/downloaders/csv/" . hash_user() . ".xls");
            //d(2);
            $xls->aSheet->setTitle(iconv('CP1251', 'UTF-8', '������� � �������� �������'));
            $xls->excel->getDefaultStyle()->getFont()->setName('Arial');
            $xls->excel->getDefaultStyle()->getFont()->setSize(12);
            $xls->excel->getDefaultStyle()->getAlignment()->setWrapText(true);
            // �������� ����� ��� ���������
            $style_header = array(
                // �����
                'font'=>array('bold'=>false, 'name'=>'Times New Roman', 'size'=>10, 'color'=>array('rgb'=>'001664')),
                // ������������
                'alignment' => array(
                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                ),
                // ���������� ������
                'fill' => array(
                    'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                    'color'=>array('rgb'=>'AAEEFF')
                ),
                'borders'=>array(
                    //������� �����
                    'outline' => array(
                        'style'=>PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb'=>'006464')
                    ),
                    //����������
                    'allborders'=>array(
                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb'=>'006464')
                    )
                )
            );
            //d(3);
            $xls->aSheet->getStyle('a1:j1')->applyFromArray($style_header);
            $xls->sACVal(0,1,'trimConvString',['��� �������','�������������','����������','��� ��������','����������',
                                               '������','���','�����','���������� �����','������']);
            $xls->aSheet->getColumnDimension('A')->setWidth(10); // ������� ������ �������� (� ���������� ��������)
            $xls->aSheet->getColumnDimension('B')->setWidth(12); 
            $xls->aSheet->getColumnDimension('c')->setWidth(20); 
            $xls->aSheet->getColumnDimension('d')->setWidth(10); 
            $xls->aSheet->getColumnDimension('e')->setWidth(10); 
            $xls->aSheet->getColumnDimension('f')->setWidth(10);
            $xls->aSheet->getColumnDimension('g')->setWidth(40); 
            $xls->aSheet->getColumnDimension('h')->setWidth(40); 
            $xls->aSheet->getColumnDimension('i')->setWidth(15); 
            $xls->aSheet->getColumnDimension('j')->setWidth(20); 
            $xls->cellStyle=false; // ����� ��� ������
            $cursor=SQL($area_query);
            $h=1;
            while ($cursor->assoc()) {
                if ($cursor->r["latlng"] == '' or $cursor->r["latlng"] == ':'){
                // ���������� �� ��������
                } else {
                    $testclustercount++;
                    list ($ulat_x, $ulng_y) = explode(":", $cursor->r["latlng"]);
                    $result_polygon_calc = $polygon->calc([ 'x' => $ulat_x, 'y' => $ulng_y, ]);
                    if ($result_polygon_calc == 1 or $result_polygon_calc == -1) {
                        $xls->scVal(0,$h+1,'trimConvString',$cursor->r["lay_type"]);
                        $xls->sncVal('trimConvString',$cursor->r["list_id"]);
                        $xls->sncVal('trimConvString',$cursor->r["latlng"]);
                        $xls->sncVal('trimConvString',empty($cursor->r["cs"]) ? "-" : $cursor->r["cs"]);
                        $xls->sncVal('trimConvString',$cursor->r["technology"]);
                        $xls->sncVal('trimConvString',$cursor->r["service"]);
                        $xls->sncVal('trimConvString',$cursor->r["oname"]);
                        $xls->sncVal('trimConvString',$cursor->r["oaddress"]);
                        $xls->sncVal('trimConvString',$cursor->r["contact_phone"]);
                        $xls->sncVal('trimConvString',$cursor->r["status"]);
                        $h++;
                    }
                }
                ////////////////////////////////////////////////////////////////
                //if($h>2100) break;
            } // ^^ while ^^
            $cursor->free();
            //////////////////////////////////////////////////////////////////// 
            $xls->save();
            //$file_path = $uploaddir . $fname;
            // �������� ��������������� �������� �� ����� ������������
            header("Content-Disposition: attachment; filename=\"�������� ������ " . date("d.m.Y H-i-s") . ".xls\""); // ��� ����� ������ ����� ��� ����������
            header("Content-Type: application/force-download"); // ������� ��������
            header("Content-type: application/octet-stream"); // ��� ���� ��� ������� ������ �������
            header("Content-length: " . filesize($xls->filename)); // ��� ����� ������ �������
            echo file_get_contents($xls->filename);
        break; // case "1"
        ////////////////////////////////////////////////////////////////////////
        // ����� �� ����������� ������������� � ���������
        case "2": // 
            //d(1);
            $xls= new CExcel("./uploads/downloaders/csv/" . hash_user() . ".xls");
            //d(2);
            // �� �������� ��-�� �������� ����� excel
            //$xls->aSheet->setTitle(iconv('CP1251', 'UTF-8', '����� �� ����������� ������������� � ���������'));
            // � ��� ��������
            $xls->aSheet->setTitle(iconv('CP1251', 'UTF-8', '�����_��_�����������'));
            $xls->excel->getDefaultStyle()->getFont()->setName('Arial');
            $xls->excel->getDefaultStyle()->getFont()->setSize(12);
            $xls->excel->getDefaultStyle()->getAlignment()->setWrapText(true);
            // �������� ����� ��� ���������
            $style_header = array(
                // �����
                'font'=>array('bold'=>false, 'name'=>'Times New Roman', 'size'=>10, 'color'=>array('rgb'=>'001664')),
                // ������������
                'alignment' => array(
                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                ),
                // ���������� ������
                'fill' => array(
                    'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                    'color'=>array('rgb'=>'AAEEFF')
                ),
                'borders'=>array(
                    //������� �����
                    'outline' => array(
                        'style'=>PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb'=>'006464')
                    ),
                    //����������
                    'allborders'=>array(
                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb'=>'006464')
                    )
                )
            );
            //d(3);
            $xls->aSheet->getStyle('a1:e1')->applyFromArray($style_header);
            $xls->sACVal(0,1,'trimConvString',['���','�������������','���','����','IP �����']);
            $xls->aSheet->getColumnDimension('A')->setWidth(30); // ������� ������ �������� (� ���������� ��������)
            $xls->aSheet->getColumnDimension('B')->setWidth(15); 
            $xls->aSheet->getColumnDimension('c')->setWidth(20); 
            $xls->aSheet->getColumnDimension('d')->setWidth(20); 
            $xls->aSheet->getColumnDimension('e')->setWidth(15); 
            $xls->cellStyle=false; // ����� ��� ������
            $cursor=SQL("SELECT us.fio,oc.uid,ltc.lname,oc.dateinsert,oc.ipaddress 
                FROM occurrence oc left join ps_users us using(uid) left join ltc on us.ltc=ltc.lid 
                where otype=1 and uid!=779 and dateinsert>STR_TO_DATE('10.2.2018','%d.%m.%Y')
                order by oc.id");
            $h=1;
            while ($cursor->assoc()) {
                $xls->scVal(0,$h+1,'trimConvString',$cursor->r["fio"]);
                $xls->sncVal('trimConvString',$cursor->r["uid"]);
                $xls->sncVal('trimConvString',$cursor->r["lname"]);
                $xls->sncVal('trimConvString',$cursor->r["dateinsert"]);
                $xls->sncVal('trimConvString',$cursor->r["ipaddress"]);
                $h++;
            } // ^^ while ^^
            $cursor->free();
            //////////////////////////////////////////////////////////////////// 
            $xls->save();
            //$file_path = $uploaddir . $fname;
            // �������� ��������������� �������� �� ����� ������������
            header("Content-Disposition: attachment; filename=\"����� �� ����� � ������� " . date("d.m.Y H-i-s") . ".xls\""); // ��� ����� ������ ����� ��� ����������
            header("Content-Type: application/force-download"); // ������� ��������
            header("Content-type: application/octet-stream"); // ��� ���� ��� ������� ������ �������
            header("Content-length: " . filesize($xls->filename)); // ��� ����� ������ �������
            echo file_get_contents($xls->filename);
        break; // case "2"
        ////////////////////////////////////////////////////////////////////////
        case "3": // 
        break; // case "3"
    }
$mysqli->close(@$link);

?>
