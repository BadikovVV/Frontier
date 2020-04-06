<?php
//
////////////////////////////////////////////////////////////////////////////////
// Показываем АТС, РШ, порты СПД
    if($_COOKIE['mapfilter']['UD']==1 or $_COOKIE['mapfilter']['DB']==1 or $_COOKIE['mapfilter']['SPDPort']==1) {
        $astDBspd="";
        if($_COOKIE['mapfilter']['UD']==1) $astDBspd.=" 102, ";
        if($_COOKIE['mapfilter']['DB']==1) $astDBspd.=" 105, ";
        if($_COOKIE['mapfilter']['SPDPort']==1) $astDBspd.=" 101, ";
        $astDBspd.=" -99999";
        // показывать ли только заблудившиеся объекты
        // !!! поле которое можно было бы использовать (place_id) не заполняется !!!
        //if($_COOKIE['mapfilter']['stray']==1){
            //$testStrayCall=" and place_id not in ('exact','number','near','manual') ";
        //} else {
            $testStrayCall=" ";
        //}
        // версия 1
        //echo "/*SELECT co.* FROM com_obj co inner join ps_mctet mc on co.mid=mc.id 
        //    where mc.name='".$_REQUEST["mapfilter_mctet"]."' and lay_type in (".$astDBspd .")
        //    order by concat(lat,lng)*/";
        // версия 2
        //$cursor=new CSQL("SELECT co.* FROM com_obj co inner join ps_mctet mc on co.mid=mc.id 
        //    where mc.name='".$_REQUEST["mapfilter_mctet"]."' ".$testStrayCall." and lay_type in (".$astDBspd .")
        //    order by concat(lat,lng)");
            //where lay_type=101 and mc.name='".$_REQUEST["mapfilter_mctet"]."' order by concat(lat,lng)");
        // версия 3 (ЛТЦ)
        //$cursor=new CSQL("SELECT co.* FROM com_obj co inner join ltc on co.mid=ltc.tid 
        //    where ltc.lid=".$mapfilter_ltc_id." ".$testStrayCall." and lay_type in (".$astDBspd .")
        //    order by concat(co.lat,co.lng)");
        // версия 4 (ЛТЦ)
        $cursor=new CSQL("SELECT co.* FROM com_obj co 
            where ltc=".$mapfilter_ltc_id." ".$testStrayCall." and lay_type in (".$astDBspd .")
            order by concat(co.lat,co.lng)");
       //
        $prevLat=0; $prevLng=0;
        $level = 1; $x=$level; $y=-$level;
        while ($cursor->assoc()) {
            if($cursor->r["lat"] and $cursor->r["lng"]){
                str_replace('"', "", $cursor->r["odesc"]);
                if($cursor->r["lat"]==$prevLat and $cursor->r["lng"]==$prevLng){
                // у маркера такие же координаты как у предыдущего
                    echo "lMarker = L.marker([" . ($y*0.00002+$prevLat) . ", " . ($x*0.00003+$prevLng) . "], 
                        {mapobj_type: ".$cursor->r["lay_type"] .", icon: ".mapObjIcon($cursor->r["lay_type"]) .",oid:".$cursor->r["oid"]."}).addTo(mymap);
                        lMarker.bindPopup(\"<b>" . 
                            str_replace('"', "",
                                $cursor->r["oname"] ."</b>" . 
                                $cursor->r["otype"] ."<br>" .
                                $cursor->r["odesc"] ."<br>" . 
                                $cursor->r["oaddress"]) . 
                            "\");
                        ";
                    if($x==$level and $y<($level)) $y++;
                    else{
                        if($x>(-$level) and $y==$level) $x--;
                        else{
                            if($x==-$level and $y>(-$level)) $y--;
                            else{
                                if($x<($level-1) and $y==-$level) $x++;
                                else{ 
                                    $level++;$x=$level;$y=-$level; 
                                }
                            }
                        }
                    }
                }else{
                    echo "lMarker = L.marker([" . $cursor->r["lat"] . ", " . $cursor->r["lng"] . "], 
                        {mapobj_type: ".$cursor->r["lay_type"] .", icon: ".mapObjIcon($cursor->r["lay_type"]) .",oid:".$cursor->r["oid"]."}).addTo(mymap);
                        lMarker.bindPopup(\"<b>" . 
                            str_replace('"', "",
                                $cursor->r["oname"] ."</b> " . 
                                $cursor->r["otype"] ."<br>" .
                                $cursor->r["odesc"] ."<br>" . 
                                $cursor->r["oaddress"]) . 
                            "\");
                        ";
                    $prevLat=$cursor->r["lat"];
                    $prevLng=$cursor->r["lng"];
                    $level = 1; $x=$level; $y=-$level;
                }
                echo "lMarker.on('click', onUDClick);";
            }
        }
        $cursor->free();
    }
//
////////////////////////////////////////////////////////////////////////////////
// Показываем кластеры
/*
// кластеры, построенные автоматически
    $cursor=new CSQL("SELECT d.*, 
	ifnull(SUBSTRING_INDEX(SUBSTRING_INDEX(latlng, ':', 1),':',-1),ac.lat) lat, 
	ifnull(SUBSTR(latlng, LOCATE(':', latlng)+1),ac.lng) lng 
	FROM dynclust d 
	left join abonent ab on d.otype=4 and d.oid=ab.aid 
	left join addrcache ac on ab.address_id=ac.aid 
	left join ps_list psl on d.otype=1 and d.oid=psl.list_id ");
    while ($cursor->assoc()) {
        if(empty($cursor->r["lat"])){
            
        } else {
            if($cursor->r["mtype"]==1){
                echo"cMarker=L.circleMarker([".$cursor->r["lat"].",".$cursor->r["lng"]."],  
                    {radius: ".sqrt($cursor->r["amount"]).", weight: 1, color: '#F00', fill: false, fillOpacity: '0.2'}); cMarker.addTo(mymap);
                    cMarker.bindPopup(\"<b>".$cursor->r["cid"]."/".$cursor->r["amount"]." шт</b>\");";
            } else {
                echo"cMarker=L.circleMarker([".$cursor->r["lat"].",".$cursor->r["lng"]."],  
                    {radius: 1, weight: 1, color: '#00F'}); cMarker.addTo(mymap);
                    cMarker.bindPopup(\"<b>".$cursor->r["otype"]."/".$cursor->r["oid"]."</b>\");";            
            }
        }
    }
    $cursor->free(); 
*/
// кластеры, нарисованные вручную
$testCluster=' where 1=2 ';
    if (isset($_COOKIE['mapfilter']['cluster']) and $_COOKIE['mapfilter']['cluster']==1) {
        $testCluster.=' or level=1 ';
    }
    if (isset($_COOKIE['mapfilter']['cluster2']) and $_COOKIE['mapfilter']['cluster2']==1) {
        $testCluster.=' or level=2 ';
    }
    $cursor=new CSQL("SELECT * FROM cluster ".$testCluster);
    $i = 1;
    // создаём группу маркеров кластеров и показываем/скрываем маркеры при переходе mymap.getZoom() через 14
    // вешаем на событие окончания зуммирования mymap.on('zoomend', ...
    echo "var ClusterMarkers = new L.FeatureGroup();";
    //
    while ($cursor->assoc()) {
        $latlngs=explode(" ",trim($cursor->r["coord"]));
        echo "lCluster = L.polygon([[".implode("],[",$latlngs)."]], {color: 'red'}).addTo(mymap);
            lCluster.on('click', onClusterClick);
            ";
        echo "lMarker=L.marker([" . explode(",",$latlngs[0])[0] . ", " . explode(",",$latlngs[0])[1] . "], {mapobj_type: 100, icon: clustericon, cluster_id: '".
                $cursor->r["id"]."',cluster_name: '".$cursor->r["cname"]."'});
            lMarker.on('click', onClusterClick);
            lMarker.on('contextmenu', onClusterRightClick);
            ClusterMarkers.addLayer(lMarker);
            "; 
        $i++;
    }
    $cursor->free(); 
//
////////////////////////////////////////////////////////////////////////////////
// Показываем проекты
    $cursor=new CSQL("SELECT * FROM ps_project");
    while ($cursor->assoc()) {
        // !!! для вложенных курсоров требуется буферизированный результат
        // MYSQLI_STORE_RESULT – вернет буферизированный результат, значение по умолчанию
        // MYSQLI_USE_RESULT – небуферизированный
        // $this->link->query($this->query,MYSQLI_STORE_RESULT);
        $cursor2=new CSQL("SELECT pl.latlng latlng,prjl.project_id project_id,prj.project_name project_name
            FROM ps_list pl 
            left join ps_project_list prjl on pl.list_id=prjl.list_id
            left join ps_project prj on prjl.project_id=prj.project_id
            where pl.latlng<>':' and prjl.project_id=".$cursor->r["project_id"]);
        $i = 0;
        $lat=0.0;
        $lng=0.0;
        while ($cursor2->assoc()) {
            $latlngs=explode(":",trim($cursor2->r["latlng"]));
            $lat+=$latlngs[0];
            $lng+=$latlngs[1];       
            $i++;
        }
        $cursor2->free();
        //echo " ".$i." ".$lat." ".$lng."<br>";
        if($i>0){
            $lat=$lat/$i;    
            $lng=$lng/$i;    
            echo "lMarker=L.marker([" . $lat . ", " . $lng . "], {mapobj_type: 104, icon: projecticon, project_id: '".
                    $cursor->r["project_id"]."', project_name: '".
                    $cursor->r["project_name"]."'}).addTo(mymap);
                lMarker.on('click', onProjectClick);
                ";  
            // рисование линий от заявки к проекту
            /*
            $cursor2->data_seek(0);
            while ($cursor2->assoc()) {
                $latlngs=explode(":",trim($cursor2->r["latlng"]));
                echo "lPolyline=L.polyline([[". $latlngs[0] .",".$latlngs[1] ."],[".($lat+3.0*$latlngs[0])/4.0 .",".($lng+3.0*$latlngs[1])/4.0 ."]], {
                        dashArray: '1,6', 
                        weight: 1, 
                        color: '#aa0'
                        }).addTo(mymap);
                    ";
            }*/
            if($project_as_map_center==$cursor->r["project_id"]){
            // проект только что сменился сдвигаем окно на него
                echo "mymap.panTo(new L.LatLng(".$lat .", ".$lng ."));
                    ";
            }
        }
    }
    $cursor->free();
//
////////////////////////////////////////////////////////////////////////////////
// Показываем существующих абонентов (xDSL, FTTx)
    if($_COOKIE['mapfilter']['existabon']==1 and $ubord->havePrivilege("!G7","*")){
        //$testStrayCall=" and pld.location_type not in ('exact','number','near','manual') ";
        // ver 1
//        $cursor=new CSQL("select abonent.*,abonserv.tid abonservtech,addrcache.aid address_id,
//                addrcache.lat,addrcache.lng, ps_status.name sname, 
//                addrcache.district_type, addrcache.district, addrcache.locality, 
//                addrcache.street_type, addrcache.street, addrcache.building, addrcache.corp, addrcache.flat
//            from abonent join addrcache on abonent.address_id=addrcache.aid 
//                join ps_status on abonent.status=ps_status.id
//                join abonserv on abonent.aid=abonserv.aid
//            where addrcache.lat!=999 and abonent.ltc=".$mapfilter_ltc_id);
        // ver 2
//        if($_COOKIE['mapfilter']['restrict_existabon']==1){
//            $restrict_existabon=
//                " and ".$mapscale .">=14 ".
//                " and addrcache.lat between ".$mapsouthlat." and ".$mapnorthlat.
//                " and addrcache.lng between ".$mapwestlng."  and ".$mapeastlng." ";
//        }else{
//            $restrict_existabon=" and ".$mapscale .">=10 ";
//        }
        // ver 3 $_COOKIE['mapfilter']['restrict_existabon']===1
            
            $restrict_existabon=
                " and ".$mapscale .">=14 ".
                " and addrcache.lat between ".$mapsouthlat." and ".$mapnorthlat.
                " and addrcache.lng between ".$mapwestlng."  and ".$mapeastlng." ";
        // ver.1
        // номер квартиры берётся из addrcache
//        $cursor=new CSQL("select abonent.*,abonserv.tid abonservtech,addrcache.aid address_id,
//                addrcache.lat,addrcache.lng, ps_status.name sname, 
//                addrcache.district_type, addrcache.district, addrcache.locality, 
//                addrcache.street_type, addrcache.street, addrcache.building, addrcache.corp, addrcache.flat
        // номер квартиры берётся из abonent, а не addrcache
        $cursor=new CSQL("select abonent.*,abonserv.tid abonservtech,addrcache.aid address_id,
                addrcache.lat,addrcache.lng, ps_status.name sname, 
                addrcache.district_type, addrcache.district, addrcache.locality, 
                addrcache.street_type, addrcache.street, addrcache.building, addrcache.corp
            from abonent join addrcache on abonent.address_id=addrcache.aid 
                join ps_status on abonent.status=ps_status.id
                join abonserv on abonent.aid=abonserv.aid
            where ("
                .(($_COOKIE['mapfilter']['b2c']==1) ? " abonent.atype=1 or " : " ") 
                .(($_COOKIE['mapfilter']['b2b']==1) ? " abonent.atype=2 or " : " ") 
                ." 1=2 "
                .") "
                ." and abonserv.tid in (1,2) ".$restrict_existabon
                ." and abonent.ltc=".$mapfilter_ltc_id
            ." order by addrcache.lat,addrcache.lng ");
        // ver.2
//        $cursor=new CSQL("select abonent.*,abonserv.tid abonservtech,addrcache.aid address_id,
//                addrcache.lat,addrcache.lng, ps_status.name sname, 
//                addrcache.district_type, addrcache.district, addrcache.locality, 
//                addrcache.street_type, addrcache.street, addrcache.building, addrcache.corp, addrcache.flat
//            from abonent join addrcache on abonent.address_id=addrcache.aid 
//                join ps_status on abonent.status=ps_status.id
//                join abonserv on abonent.aid=abonserv.aid
//            where 1=1 ".$restrict_existabon.
//                " and abonent.ltc=".$mapfilter_ltc_id." 
//            order by addrcache.lat,addrcache.lng LIMIT 5000");
        $prevLat=0; $prevLng=0;
        $level = 1; $x=$level; $y=-$level; 
        $textLine1=""; // основной вариант
        $textLineAll=""; // вариант для заявок с одними координатами
        $numSamePlaceObject=0; // количество заявок с одними координатами
        $prevaid==-1;
        while ($cursor->assoc()) {
            if($cursor->r["lat"] and $cursor->r["lng"]){
                //echo $cursor->r["lat"]."------".$cursor->r["lng"];
                str_replace('"', "", $cursor->r["aname"]);
                if($cursor->r["atype"]==1 and $cursor->r["abonservtech"]==1) $existicon="exist_4_green_icon";
                elseif($cursor->r["atype"]==1 and $cursor->r["abonservtech"]==2) $existicon="exist_3_green_icon";
                elseif($cursor->r["atype"]==2 and $cursor->r["abonservtech"]==2) $existicon="exist_3_yellow_icon";
                else $existicon="exist_4_yellow_icon";
                // vvv сдвиг для объектов с одинаковыми координатами vvv
                if($cursor->r["lat"]==$prevLat and $cursor->r["lng"]==$prevLng){
                // у маркера такие же координаты как у предыдущего
                    $cursor->r["lat"]=$y*0.00002+$prevLat;
                    $cursor->r["lng"]=$x*0.00003+$prevLng;
                    if($x==$level and $y<($level)) $y++;
                    else{
                        if($x>(-$level) and $y==$level) $x--;
                        else{
                            if($x==-$level and $y>(-$level)) $y--;
                            else{
                                if($x<($level-1) and $y==-$level) $x++;
                                else{ 
                                    $level++;$x=$level;$y=-$level; 
                                }
                            }
                        }
                    }
                    $numSamePlaceObject++;
                }else{
                    if($numSamePlaceObject>0){ // вариант для заявок с одними координатами
                        echo str_replace("title: 'группа абонентов'", "title: '".($numSamePlaceObject+1) ." абонентa(ов)'", $textLineAll);
                        $numSamePlaceObject=0;
                    } else {
                        echo $textLine1;
                    }
                    $textLine1=""; // сброс
                    $textLineAll="";  // сброс
                    $prevaid=$cursor->r["aid"]; // сброс
                    $prevLat=$cursor->r["lat"]; // сброс
                    $prevLng=$cursor->r["lng"]; // сброс
                    $level = 1; $x=$level; $y=-$level; // сброс
                }
                // ^^^ сдвиг для объектов с одинаковыми координатами ^^^
                $textLine1="lMarker=L.marker([" . trim(($cursor->r["lat"]+0.00003).",".($cursor->r["lng"]+0.000045)) . 
                    "], {mapobj_type: 106, icon: ".$existicon.",list_id:".$cursor->r["aid"].",zIndexOffset: 100}).addTo(mymap);
                    ";
                $textLine1.="lMarker.on('click', onAbonentClick);";
                $textLine1.="abonArr[".$cursor->r["aid"].
                    "]={aname:\"". $cursor->r["aname"] ." ".$cursor->r["contactphone"] .
                    "\",device_address:\"".
                        str_replace('"', "",
                            $cursor->r["address_id"] .". ".$cursor->r["district"] ." ".$cursor->r["district_type"] .
                            "<br>".$cursor->r["locality"] ."<br>".$cursor->r["street_type"] ." ".$cursor->r["street"] .
                            " ".$cursor->r["building"] ." ".$cursor->r["corp"] ." ".$cursor->r["flat"]
                        ) .
                    "\",int_status_name:\"". $cursor->r["sname"] ."\"};
                ";
                // вариант для заявок с одними координатами
                if(empty($textLineAll)){ 
                    $textLineAll="lMarker=L.marker([" . trim(($cursor->r["lat"]+0.00003).",".($cursor->r["lng"]+0.000045)) . 
                        "], {mapobj_type: 126, icon: exist_all_icon, list_id:".$cursor->r["aid"].",zIndexOffset: 100,title: 'группа абонентов'}).addTo(mymap);
                        ";
                    $textLineAll.="lMarker.on('click', onAbonentClick); abonArr[".$prevaid ."]=new Array(); ";
                }
//                $textLineAll.="abonArr[".$prevaid ."]=new Array(); ".
//                    "abonArr[".$prevaid ."][".$numSamePlaceObject."]={aname:\"". $cursor->r["aname"] ." ".$cursor->r["contactphone"] .
                $textLineAll.=" ".
                    "abonArr[".$prevaid ."].push({aid: ".$cursor->r["aid"] .", aname:\"". $cursor->r["aname"] ." ".$cursor->r["contactphone"] .
                    "\",device_address:\"".
                        str_replace('"', "",$cursor->r["district"] ." ".$cursor->r["district_type"] .
                            " ".$cursor->r["locality"] ." ".$cursor->r["street_type"] ." ".$cursor->r["street"] .
                            " ".$cursor->r["building"] ." ".$cursor->r["corp"] ." ".$cursor->r["flat"]
                        ) .
                    "\",int_status_name:\"". $cursor->r["sname"] ."\",atype:\"". $cursor->r["atype"] ."\",abonservtech:\"". $cursor->r["abonservtech"] ."\"});
                ";
            }
        }
        $cursor->free();
        echo $textLine1;
    }
//
////////////////////////////////////////////////////////////////////////////////
// Показываем map_obj
    if ($mapfilter_ltc_id and $mapfilter_ltc_id>0) {
        $cursor=new CSQL("SELECT m.cssid,m.width mwidth,m.height mheight, m.a_size,m.color mcolor,mo.*,
            eq.name eqname,lin.name linname FROM map_obj mo 
            left join morphe m on mo.morphe=m.id 
            left join ps_equip eq on mo.type=1 and mo.subtype=eq.id
            left join ps_smet_calc lin on mo.type=2 and mo.subtype=lin.id
            where mo.latlng<>'' and project_id in (select project_id from ps_project where ltc=".$mapfilter_ltc_id.") 
            order by latlng");
        while ($cursor->assoc()) {
            str_replace('"', "", $cursor->r["comment"]);
            $latlngs=trim($cursor->r["latlng"]);
            switch($cursor->r["type"]){
            case 1:
                //list($prevLat,$prevLng)=explode(",",$row_map_obj["latlng"]);
                echo "lIcon=new L.DivIcon({className: '".$cursor->r["cssid"]."', html: '<div><nobr style=\"color: ".$cursor->r["color"]."; \">". 
                    $cursor->r["id"]."&nbsp;&nbsp;&nbsp;".
                        (rtrim($cursor->r["cosize"],'0')==1 and rtrim(rtrim($cursor->r["manual_size"],'0'))==1 ?
                        rtrim(rtrim($cursor->r["cosize"],'0'),'.')."(".
                        rtrim(rtrim($cursor->r["manual_size"],'0'),'.').") шт " : "")
                        .
                        $cursor->r["oname"]."</nobr></div>', iconSize: new L.Point(16, 16) });";
                echo "lMarker=L.marker([" . $latlngs . "], {
                        mapobj_type: 1,
                        mapobj_id: ".$cursor->r["id"] .", 
                        icon: lIcon,
                        project_id: ".$cursor->r["project_id"].",
                        mocolor: '".$cursor->r["color"]."',
                        manual_size: '".$cursor->r["manual_size"]."',
                        oname: '".$cursor->r["oname"]."'
                        }).addTo(mymap);
                    lMarker.on('click', onProjectObjectClick);
                    ";
            break;
            case 2:
                echo "lPoly=L.polyline([[".implode("],[",explode(" ",$latlngs))."]], {
                        dashArray: '15,6,2,6', 
                        weight: 1, 
                        color: '".$cursor->r["color"]."', 
                        mapobj_id: ".$cursor->r["id"].", 
                        project_id: ".$cursor->r["project_id"].",
                        mocolor: '".$cursor->r["color"]."',
                        oname: '".$cursor->r["oname"]."',
                        manual_size: '".$cursor->r["manual_size"]."',
                        cssid: '".$cursor->r["cssid"]."'    
                        }).addTo(mymap);
                    ";
                echo "lIcon=new L.DivIcon({className: '".$cursor->r["cssid"]."', html: '<div><nobr style=\"color: ".$cursor->r["color"]."; \">". 
                    $cursor->r["id"]."&nbsp;&nbsp;&nbsp;".rtrim(rtrim( round($cursor->r["cosize"],2) ,'0'),'.').
                        ($cursor->r["manual_size"]==0 ? "" : "(".rtrim(rtrim($cursor->r["manual_size"],'0'),'.').")")." км ".
                        $cursor->r["oname"]."</nobr></div>', iconSize: new L.Point(14, 14)  });";
                echo "L.marker([" 
                    . 
                    (((float)(explode(",",explode(" ",$latlngs)[0])[0])+(float)(explode(",",explode(" ",$latlngs)[1])[0]))/2) 
                    .",".
                    (((float)(explode(",",explode(" ",$latlngs)[0])[1])+(float)(explode(",",explode(" ",$latlngs)[1])[1]))/2) 
                    . 
                    "], {
                        mapobj_type: 2,
                        ppolyline: lPoly, 
                        mapobj_id: ".$cursor->r["id"].", 
                        icon: lIcon, 
                        project_id: ".$cursor->r["project_id"]."
                        }).addTo(mymap)
                    .on('click', onProjectObjectClick);
                    ";
            break;
            case 3:
                echo "lPoly=new L.polygon([[".implode("],[",explode(" ",$latlngs))."]], {
                        dashArray: '3,3', 
                        weight: 1, 
                        color: '".$cursor->r["color"]."', 
                        mapobj_id: ".$cursor->r["id"].", 
                        project_id: ".$cursor->r["project_id"].",
                        mocolor: '".$cursor->r["color"]."',
                        oname: '".$cursor->r["oname"]."',
                        manual_size: '".$cursor->r["manual_size"]."',
                        cssid: '".$cursor->r["cssid"]."'    
                        }).addTo(mymap);
                        
                    ";
                // ver 1
                /*echo "lIcon=new L.DivIcon({className: '".$cursor->r["cssid"]."', html: '<div><nobr style=\"color: ".$cursor->r["color"]."; \">". 
                    $cursor->r["id"]."&nbsp;&nbsp;&nbsp;".rtrim(rtrim( round($cursor->r["cosize"],2) ,'0'),'.').
                        ($cursor->r["manual_size"]==0 ? "" : "(".rtrim(rtrim($cursor->r["manual_size"],'0'),'.').")")." км ".
                        $cursor->r["oname"]."</nobr></div>', iconSize: new L.Point(14, 14)  });";
                echo "L.marker([" 
                    . 
                    (((float)(explode(",",explode(" ",$latlngs)[0])[0])+(float)(explode(",",explode(" ",$latlngs)[1])[0]))/2) 
                    .",".
                    (((float)(explode(",",explode(" ",$latlngs)[0])[1])+(float)(explode(",",explode(" ",$latlngs)[1])[1]))/2) 
                    . 
                    "], {
                        mapobj_type: 3,
                        ppolyline: lPoly, 
                        mapobj_id: ".$cursor->r["id"].", 
                        icon: lIcon, 
                        project_id: ".$cursor->r["project_id"]."
                        }).addTo(mymap)
                    .on('click', onProjectObjectClick);
                    ";*/
                // ver 2
                /*echo "lIcon=new L.DivIcon({className: '".$cursor->r["cssid"]."', html: '<div><nobr style=\"color: black; \">". 
                    $cursor->r["id"]."&nbsp;&nbsp;&nbsp;".rtrim(rtrim( round($cursor->r["cosize"],2) ,'0'),'.').
                        ($cursor->r["manual_size"]==0 ? "" : "(".rtrim(rtrim($cursor->r["manual_size"],'0'),'.').")")." км ".
                        $cursor->r["oname"]."</nobr></div>', iconSize: new L.Point(14, 14)  });";*/
                echo "L.marker([" 
                    . 
                    (((float)(explode(",",explode(" ",$latlngs)[0])[0])+(float)(explode(",",explode(" ",$latlngs)[1])[0]))/2) 
                    .",".
                    (((float)(explode(",",explode(" ",$latlngs)[0])[1])+(float)(explode(",",explode(" ",$latlngs)[1])[1]))/2) 
                    . 
                    "], {
                        mapobj_type: 3,
                        ppolyline: lPoly, 
                        mapobj_id: ".$cursor->r["id"].", 
                        icon: clustericon, 
                        project_id: ".$cursor->r["project_id"]."
                        }).addTo(mymap)
                    .on('click', onProjectObjectClick).on('contextmenu', onProjectObjectRightClick);
                    ";
            break;
            }
        }
        $cursor->free();
    }
//
////////////////////////////////////////////////////////////////////////////////
// показываем заявки на карте
//
// выбор иконки для заявки
    function callIcon($status,$cs){
        switch($cs){
        case 1:
            switch($status){
            case 10:
            case 15:
                return "blueicon";
            break;
            case 55:
                return "greenicon";
            break;
            default:
                if($status>55) return "grayicon";
                else return "cyanicon";                
            }
        break;
        case 2:
            switch($status){
            case 10:
            case 15:
                return "redicon";
            break;
            case 55:
                return "yellowicon";
            break;
            default:
                if($status>55) return "grayicon";
                else return "orangeicon";
            }
        break;
        case 3:
            return "greenicon";
        break;
        case 5: // существующие абоненты
            return "existicon";
        break;
        default:
            return "grayicon";
        }
    } 
    // выбор иконки для объекта
    function mapObjIcon($lay_type){
        switch($lay_type){
        case 101:
            return "spdporticon";
        break;
        case 102:
            return "udicon";
        break;
        case 105:
            return "dist_box";
        break;
        }
    }     
    //
    //echo "SELECT id FROM ps_status where ugroup=".$row_users["ugroup"];
    $status_for_my_job=rSQL("SELECT id FROM ps_status where ugroup=".$row_users["ugroup"])["id"]; // status доступные для работы данного пользователя
    if(empty($status_for_my_job)) $status_for_my_job=-1;
    $test_for_my_job=""; // выбираем НЕ только "Мои заявки"
//    if($row_users["ugroup"]!=1 and (isset($_REQUEST["for_my_job"]) or $_REQUEST["for_my_job"]=='on')){ // НЕ ИТ и выбираем только "Мои заявки"
    if($row_users["ugroup"]!=1 and (isset($_COOKIE['mapfilter']['for_my_job']) and $_COOKIE['mapfilter']['for_my_job']==1)){ // НЕ ИТ и выбираем только "Мои заявки"
        $test_for_my_job=" and pld.status=$status_for_my_job ";
    }
    // показывать ли только заявки ВООБЩЕ
    if($_COOKIE['mapfilter']['call']==1 and $ubord->havePrivilege("!G7","*")){
        $testCall=" ";
    } else {
        $testCall=" and 1=2 ";
    }
    // показывать ли только заблудившиеся заявки
    if($_COOKIE['mapfilter']['stray']==1){
        $testStrayCall=" and pld.location_type not in ('exact','number','near','manual') ";
    } else {
        $testStrayCall=" ";
    }
    // показывать только просроченные заявки
    if($_REQUEST['overdue_call_min']>0){
        $testOverdueCall=" and DATEDIFF(NOW(),cp.checkdate) between ".$_REQUEST['overdue_call_min'] ." and ".$_REQUEST['overdue_call_max'] ." ";
    } else {
        $testOverdueCall=" ";
    }
    //
    if(empty($user_LTC_list)){
        $test_user_LTC_list=" ";    
    }else{
        $test_user_LTC_list=" and SUBSTRING_INDEX(l.ltc,' ',1) in (".$user_LTC_list.") ";
    }
    //
//    $cursor=new CSQL("SELECT l.arm_id,pld.lid lid,l.device_address,l.latlng,l.list_id,pl.project_id,
//            pld.status,s.name int_status_name,pn.project_name project_name,l.cs cs 
//        FROM ps_list l 
//        left join (select list_id,project_id from ps_project_list where delete_flag=0) pl on l.list_id=pl.list_id
//        left join ps_project pn on pn.project_id=pl.project_id
//        inner join ps_list_dop pld on l.list_id=pld.list_id
//        left join ps_status s on pld.status=s.id
//        where ues_arm='".$_REQUEST["mapfilter_mctet"]."' ".$test_user_LTC_list.$testStrayCall." and l.latlng<>'' ".$test_for_my_job.
//        " group by l.arm_id,l.list_id order by l.latlng");
    //overdue_call
    $cursor=new CSQL("SELECT l.arm_id,pld.lid lid,l.device_address,l.latlng,l.list_id,pl.project_id,
            pld.status,s.name int_status_name,pn.project_name project_name,l.cs cs,
            cp.checkdate cp_checkdate, cp.dateedit cp_dateedit
        FROM ps_list l 
        left join (select list_id,project_id from ps_project_list where delete_flag=0) pl on l.list_id=pl.list_id
        left join ps_project pn on pn.project_id=pl.project_id
        inner join ps_list_dop pld on l.list_id=pld.list_id
        left join ps_status s on pld.status=s.id
        left join callpath cp on cp.object_type=2 and cp.lp_id=pld.list_id and cp.nextcallpath is null
        where l.ltc like '%".$_REQUEST["mapfilter_ltc"]."%' ".
            $testCall.$test_user_LTC_list.$testStrayCall.
            " and (".
            (($_COOKIE['mapfilter']['b2c']==1) ? " l.cs=1 or " : " ").
            (($_COOKIE['mapfilter']['b2b']==1) ? " l.cs=2 or " : " ").             
            " 1=2 )".
            " and l.latlng<>'' and trim(l.latlng)<>':' ".
            $test_for_my_job.$testOverdueCall.
        " group by l.arm_id,l.list_id order by l.latlng");
//        where ues_arm='".$_REQUEST["mapfilter_mctet"]."' ".$test_user_LTC_list.$testStrayCall." and l.latlng<>'' ".$test_for_my_job.$testOverdueCall.
//        " group by l.arm_id,l.list_id order by l.latlng");
    
    
    
    //$i = 1;
    $prevLat=0; $prevLng=0;
    $level = 1; $x=$level; $y=-$level;    
    while ($cursor->assoc()) {
        list ($lat, $lng) = explode(":", $cursor->r["latlng"]);
        //echo "alert('" . $lat . " " . (float)$lat . "');";
        $lat=(float)$lat;
        $lng=(float)$lng;
        if($_COOKIE['mapfilter']['clanalysis']==1){ // кластерный анализ заявок
            echo "lMarker=L.marker([" . $lat . ", " . $lng . "], {mapobj_type: 103, icon: ".
                callIcon($cursor->r["status"],$cursor->r["cs"]) .",list_id:".$cursor->r["list_id"]."});";
            /*if($cursor->r["cs"]=='2') echo "markers.addLayer(lMarker); // для кластеризации маркеров";
            else*/ echo "markers2.addLayer(lMarker); "; // для кластеризации маркеров
            echo "lMarker.on('click', onCallClick);";
        } else {
        if($lat==$prevLat and $lng==$prevLng){
        // у маркера такие же координаты как у предыдущего
            echo "lMarker=L.marker([" . ($y*0.00002+$prevLat) . ", " . ($x*0.00003+$prevLng) . 
                "], {mapobj_type: 103, icon: ".
                    callIcon($cursor->r["status"],$cursor->r["cs"]) .",list_id:".$cursor->r["list_id"].",zIndexOffset: 100}).addTo(mymap);
                lMarker.on('click', onCallClick);
                lMarker.on('contextmenu', onCallRightClick);";
            echo "callArr[".$cursor->r["list_id"].
                    "]={project_id:\"".($cursor->r["project_id"] ? $cursor->r["project_id"] : "new").
                    "\",lid:\"".$cursor->r["lid"].
                    "\",project_name:\"".$cursor->r["project_name"].
                    "\",arm_id:\"".$cursor->r["arm_id"].
                    "\",device_address:\"".addslashes($cursor->r["device_address"]).
                    "\",status:\"".$cursor->r["status"].
                    "\",int_status_name:\"".$cursor->r["int_status_name"]."\"};
                ";
            if($cursor->r["project_id"]){
                echo "lMarker=L.circleMarker([" . $lat . ", " . $lng . "], {mapobj_type: 104, radius: 9, dashArray: '0,10,9,49', weight: 1, color: '#FF00FF', fillOpacity: 0}).addTo(mymap);
                callArr[" . $cursor->r["list_id"] . "].circleMarker=lMarker;";
            }            
            if($x==$level and $y<($level)) $y++;
            else{
                if($x>(-$level) and $y==$level) $x--;
                else{
                    if($x==-$level and $y>(-$level)) $y--;
                    else{
                        if($x<($level-1) and $y==-$level) $x++;
                        else{ 
                            $level++;$x=$level;$y=-$level; 
                        }
                    }
                }
            }
        }else{
            echo "lMarker=L.marker([" . $lat . ", " . $lng . "], {mapobj_type: 103, icon: ".
                    callIcon($cursor->r["status"],$cursor->r["cs"]) .",list_id:".$cursor->r["list_id"]."}).addTo(mymap);
                lMarker.on('click', onCallClick);
                lMarker.on('contextmenu', onCallRightClick);";
            echo "callArr[".$cursor->r["list_id"].
                    "]={project_id:\"".($cursor->r["project_id"] ? $cursor->r["project_id"] : "new").
                    "\",lid:\"".$cursor->r["lid"].
                    "\",project_name:\"".$cursor->r["project_name"].
                    "\",arm_id:\"".$cursor->r["arm_id"].
                    "\",device_address:\"".addslashes($cursor->r["device_address"]).
                    "\",circleMarker:\"false".
                    "\",status:\"".$cursor->r["status"].
                    "\",int_status_name:\"".$cursor->r["int_status_name"]."\"};
                ";
            if($cursor->r["project_id"]){
/*
                echo "lMarker=L.circleMarker([" . ($lat+0.001) . ", " . $lng . "], 
                        {mapobj_type: 104, radius: 5, weight: 2, color: '#F00', fillOpacity: 0}).addTo(mymap);";
                echo "lMarker=L.circleMarker([" . ($lat+0.001) . ", " . $lng . "], 
                        {mapobj_type: 104, radius: 3, weight: 2, color: '#0F0', fillOpacity: 0}).addTo(mymap);";
                echo "lMarker=L.circleMarker([" . ($lat+0.001) . ", " . $lng . "], 
                        {mapobj_type: 104, radius: 7, dashArray: '7,8,7,59', weight: 2, color: '#00F', fillOpacity: 0}).addTo(mymap);";
*/
                //echo "lMarker=L.circleMarker([" . ($lat+0.002) . ", " . $lng . "], 
                //        {mapobj_type: 103, icon: ".callIcon($cursor->r["status"],$cursor->r["cs"]) .", radius: 4, dashArray: '4,2', weight: 3, color: '#060', fillOpacity: 0,list_id:".$cursor->r["list_id"]."}).addTo(mymap);
                //        lMarker.on('click', onCallClick);";
                //echo "lMarker=L.circleMarker([" . ($lat+0.002) . ", " . $lng . "], 
                //        {mapobj_type: 104, radius: 7, dashArray: '6,5,6,5,6,5,6,50', weight: 3, color: '#F0F', fillOpacity: 0}).addTo(mymap);";

                echo "lMarker=L.circleMarker([" . $lat . ", " . $lng . "], {mapobj_type: 104, radius: 9, dashArray: '0,10,9,49', weight: 1, color: '#FF00FF', fillOpacity: 0}).addTo(mymap);
                callArr[" . $cursor->r["list_id"] . "].circleMarker=lMarker;";
            }
            $prevLat=$lat;
            $prevLng=$lng;
            $level = 1; $x=$level; $y=-$level;
        }
        }
    }
?>        
