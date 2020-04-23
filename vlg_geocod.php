<?php
define('__ROOT__',dirname(__FILE__));
require_once (__ROOT__.'/vlg_util.php');
function geocod($adrStr){
//header("Content-Type: application/xml; charset=UTF-8");
// РЅР° РІС…РѕРґ СЃС‚РѕСЂРѕРєСѓ СЃ Р°РґСЂРµСЃРѕРј, РЅР° РІС‹С…РѕРґ - РјР°СЃСЃРёРІ  РёР· С€РёСЂРѕС‚С‹ Рё РґРѕР»РіРѕС‚С‹)
	$rezStr="";
        //$adrStr=urlencode(iconv('CP1251', 'UTF-8', $searchaddress)
	$rezStr=getUrl("search.maps.sputnik.ru/search/addr?format=xml&q=".$adrStr);
//        .urlencode(iconv('CP1251','UTF-8',$adrStr)));
	$findFull=False;
	$fnd=False;
	$lat=0.0;
	$lon=0.0;
	$xmlData= new SimpleXMLElement($rezStr);
	$fnd= isset($xmlData->result->address)&!empty($xmlData->result->address );
	if ($fnd){
		foreach($xmlData->result->address as $address){
			if ((string)$address->features->properties->full_match=='true'){
			// РЅР°Р№РґРµРЅРѕ РїРѕР»РЅРѕРµ СЃРѕРІРїР°РґРµРЅРёРµ ( РЅСѓ РїРѕ РєСЂР°Р№РЅРµР№ РјРµСЂРµ РїРѕ РјРЅРµРЅРёСЋ РіРµРѕРґРµРєРѕРґРµСЂР°
				$lat=(string)$address->features->geometry->lat;
				$lon=(string)$address->features->geometry->lon;
				$findFull=True;
				break;
	                }
		}
		if(!$findFull){
		// РЅРµ РЅР°Р№РґРµРЅРѕ С‚РѕС‡РЅРѕРµ СЃРѕРІРїР°РґРµРЅРёРµ 
			$address=$xmlData->result->address[0];
			$lat=(string)$address->features->geometry->lat;
	                $lon=(string)$address->features->geometry->lon;
		}
	}
	else{
	// РµСЃР»Рё РЅРёС„РёРіР° РЅРµ РЅР°Р№РґРµРЅРѕ, С‚Рѕ  lat Рё lon  ==0.0
		$lat = "0.0";
		$lon = "0.0";
	}
	return  array("lat"=>floatval($lat),"lon"=>floatval($lon)); 
}
// TODO: подключиться к таблице tmp_sparkData и у тех у кого установлены координаты
// в -1.0 , -1.0  определить координату и обновить данные
// 
function getSparkC()
{
    $sqlStr = "SELECT regnum, adrStr FROM private_sector.sparkData
            where shirota=<0.0 ";
    $rows = qSQL($sqlStr);
    $okRec=0;//к-во записей с определеными координатами
    $badRec=0; // к-во записей для которых координаты не определены
    error_log(date(DATE_RFC822)." Проставляем координаты для организаций из СПАРКа");
        while ($row = mysql_fetch_array($rows)) {
            $coords= getCoord($row["adrStr"]);
            $sqlStr = "update sparkData  
            set 
            sparkData.shirota =".$coords['lat']
            ." ,sparkData.dolgota =". $coords['lon']
            . "where  where sparkData.regnum ="
            . $row['regnum'];
            SQL($sqlStr)->commit();
            if ($coords['lat'] == 0.0) {
                $badRec++;
            } 
            else {
                $okRec++;
            }
    }
    error_log("Найдены координаты для ".$okRec." адресов");
    error_log("Не найдены координаты для ".$badRec." адресов");
    error_log(date(DATE_RFC822)." Завершено назначение координат для организаций из СПАРКа");
        
}
//$z = geocod('[etnf');
//$z = geocod('403027,%20 Р’РѕР»РіРѕРіСЂР°РґСЃРєР°СЏ%20 РѕР±Р».,%20 Р“РѕСЂРѕРґРёС‰РµРЅСЃРєРёР№%20 СЂР°Р№РѕРЅ,%20 РїРѕСЃ.%20 РЎР°РґС‹%20 РџСЂРёРґРѕРЅСЊСЏ');
//$z = geocod('Р’РѕР»РіРѕРіСЂР°Рґ,%20СѓР».Р”Р·РµСЂР¶РёРЅСЃРєРѕРіРѕ,Рґ.28');

