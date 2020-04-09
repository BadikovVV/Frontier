<?php
define('__ROOT__',dirname(__FILE__));
require_once (__ROOT__.'/vlg_util.php');
function geocod($adrStr){
//header("Content-Type: application/xml; charset=UTF-8");
// на вход стороку с адресом, на выход - массив  из широты и долготы)
	$rezStr="";
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
			// найдено полное совпадение ( ну по крайней мере по мнению геодекодера
				$lat=(string)$address->features->geometry->lat;
				$lon=(string)$address->features->geometry->lon;
				$findFull=True;
				break;
	                }
		}
		if(!$findFull){
		// не найдено точное совпадение 
			$address=$xmlData->result->address[0];
			$lat=(string)$address->features->geometry->lat;
	                $lon=(string)$address->features->geometry->lon;
		}
	}
	else{
	// если нифига не найдено, то  lat и lon  ==0.0
		$lat = "0.0";
		$lon = "0.0";
	}
	return  array("lat"=>floatval($lat),"lon"=>floatval($lon)); 
}

//$z = geocod('[etnf');
//$z = geocod('403027,%20 Волгоградская%20 обл.,%20 Городищенский%20 район,%20 пос.%20 Сады%20 Придонья');
//$z = geocod('Волгоград,%20ул.Дзержинского,д.28');

