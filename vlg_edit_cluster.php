<?php
// статистика по кластерам
function clusterStat(){
    echo "<b>Статистика по кластерам</b>";
    // стили основной таблицы
    echo "<style type=\"text/css\">
            td.clusterheader { color:#037; background:#DEF; }
        </style>";
    echo "<p><table border='1' cellspacing='1' cellpadding='2' class='sort-table'><tr>
        <td align='center' class='clusterheader'><b>Уровень кластера</b></td>
        <td align='center' class='clusterheader'><b>Наименование кластера</b></td>
        <td align='center' class='clusterheader'><b>Количество заявок в кластере</b></td>
        <td align='center' class='clusterheader'><b>Заявки</b></td></tr>";
// Вывод кластеров
    $result_claster = qSQL("SELECT * FROM cluster order by level,id");
    while ($row_claster = $result_claster->fetch_array()) {
        $result_point=qSQL("SELECT psl.arm_id, psl.list_id, psld.claster_id
                    FROM ps_list psl left join ps_list_dop psld on psl.list_id=psld.list_id
                    WHERE latlng<>'' and psld.claster_id=".$row_claster["id"]);
        $k2 = 0;
        $arrID_ARM = array();
        while ($row_point = $result_point->fetch_array()) {
            $k2++;
            array_push($arrID_ARM, $row_point["arm_id"]);
        }
        if(empty($row_claster["comment"])) $claster_comment='';
        else $claster_comment='('.$row_claster["comment"].')';
        echo "<tr><td>" . $row_claster["level"] . "</td><td>" . $row_claster["id"] . ". <b>" . $row_claster["cname"] . "</b> " . $claster_comment . "</td><td>" . $k2 . "</td><td>";
        for ($y = 0; $y < count($arrID_ARM); $y++) {
            //echo "<a href='./?c=11&cid=" . $arrID_ARM[$y] . "' target='_blank'>" . $arrID_ARM[$y] . "</a>";
            echo $arrID_ARM[$y];
            if ($y < (count($arrID_ARM) - 1))
                echo ", ";
        }
        echo "</td></tr>";
    }
    echo "</table>";
}
////////////////////////////////////////////////////////////////////////////////
// Поиск Кластера по координатам
function clusterMember(){ 
    $clusters=array();
    $arPolygon = array();
    // order by level desc !!! т.е. приоритет по уровню у более мелких кластеров
    $cursor_cluster = qSQL("SELECT id,coord FROM cluster order by level");
    $i=0;
    while ($row_cluster = $cursor_cluster->fetch_array()) {
        $clusters[$i][0]=$row_cluster["id"];
        $clusters[$i][1]=$row_cluster["coord"];
        // Заполнение массива вершин многоугольника
        $clusterlatlngs=explode(" ",trim($row_cluster["coord"]));
        for($k=0;$k<count($clusterlatlngs);$k++){
            list($arPolygon[$i][$k][0],$arPolygon[$i][$k][1]) = explode(",",$clusterlatlngs[$k]);
        }
        $i++;
    }
    //INCLUDE "classes.php";
    $polygon = new Polygon();
    $result_cids = qSQL("SELECT psl.list_id,psl.latlng,psld.claster_id
        FROM ps_list psl, ps_list_dop psld WHERE psl.list_id=psld.list_id");
    $clustermembercount=0;
    $testclustercount=0;
    $nolatlngcount=0;
    while ($row_cids = $result_cids->fetch_array()) { // Перебор заявок с одним arm_id	
        if ($row_cids["latlng"] == ''){
            $nolatlngcount++; // координаты не известны
        } else {
            if($row_cids["claster_id"] == '0') { 
            // есть координаты, принадлежность неизвестна
                $testclustercount++;
                list ($ulat_x, $ulng_y) = explode(":", $row_cids["latlng"]);
                $finded[0]['x'] = $ulat_x;
                $finded[0]['y'] = $ulng_y;
                // перебираем ВСЕ кластеры
                //$cursor_cluster = qSQL("SELECT id,coord FROM cluster order by level desc");
                //while ($row_cluster = $cursor_cluster->fetch_array()) {
                for($i=0;$i<count($clusters);$i++){
                    // Запускаем поиск
                    $polygon->set_polygon($arPolygon[$i]);
                    $result_polygon_calc = $polygon->calc([
                        'x' => $finded[0]['x'],
                        'y' => $finded[0]['y'],
                    ]);
                    if ($result_polygon_calc == 1 or $result_polygon_calc == -1) {
                        // Сохраним найденный кластер в ps_list_dop.claster_id
                        //qSQL("UPDATE ps_list_dop SET claster_id='" . $row_cluster["id"] . "' WHERE list_id='" . $row_cids["list_id"] . "'");
                        qSQL("UPDATE ps_list_dop SET claster_id='" . $clusters[$i][0] . "' WHERE list_id='" . $row_cids["list_id"] . "'");
                        $clustermembercount++;
                        break;
                    } //else		//echo " - не нашли...";
                }
            }
        }
    }
    echo "Заявок с неизвестными координатами ".$nolatlngcount." шт<br>";
    echo "Проверено заявок с известными координатами ".$testclustercount." шт<br>";
    echo "Заявок с найденными кластерами ".$clustermembercount." шт<br>";
}
// создание и редактирование кластеров
function vlg_edit_cluster($user_id) {
    //d(implode(" ",$_REQUEST));
    if(isset($_POST["coord"]) and $_POST["coord"]=="Удалить"){
        qSQL("delete from cluster where id=".$_POST["claster_id"]);
        //qSQL("delete from ps_claster where claster_id=".$_POST["claster_id"]);
        //d("Удалён " . $_POST["cname"] . " (id:".$_POST["claster_id"].")");
    }elseif(isset($_POST["claster_id"]) and $_POST["claster_id"]=='new'){ // сохранение нового кластера
        qSQL("INSERT INTO cluster(cname,comment,ltc_id,flag_pon,flag_cuprum,flag_optica,user_id,cstatus,coord,level)
            VALUES('" . $_POST["cname"] . "','" . $_POST["comment"] . "',1," . 
                ($_POST["flag_pon"] ? 1 : 0) . "," . ($_POST["flag_cuprum"] ? 1 : 0) . "," . ($_POST["flag_optica"] ? 1 : 0) . ",'" . 
                $user_id . "',1,'" . $_POST["coord"] . "'," . $_POST["cluster_level"] . ")");
        $claster_id = $mysqli->insert_id();
        /*$coorArray = explode(" ", $_POST["coord"]);
        foreach ($coorArray as $coorEl) {
            $coor = explode(",", $coorEl);
            $queryDML = "INSERT INTO ps_claster(claster_id,lat,lng)VALUES(" . $claster_id . "," . $coor[0] . "," . $coor[1] .")";
            $resultDML = $mysqli->query($queryDML);
        }*/
    } elseif(isset($_POST["coord"]) and strlen($_POST["coord"])>8){ 
    // сохранение кластера и есть что сохранять
        //d($_REQUEST);
        //d("s ".$_POST["claster_id"]." ".$_POST["coord"]);
        qSQL("UPDATE cluster SET cname='" . $_POST["cname"] . "',
            comment='" . $_POST["comment"] . "',
            ltc_id=1,
            flag_pon=" . ((isset($_POST["flag_pon"]) and $_POST["flag_pon"]==1) ? 1 : 0) . ",
            flag_cuprum=" . ((isset($_POST["flag_cuprum"]) and $_POST["flag_cuprum"]==1) ? 1 : 0) . ",
            flag_optica=" . ((isset($_POST["flag_optica"]) and $_POST["flag_optica"]==1) ? 1 : 0) . ",
            user_id=".$user_id.",
            cstatus=1,
            coord='".$_POST["coord"]."',
            level=".$_POST["cluster_level"]."    
            WHERE id=".$_POST["claster_id"]);
        /*qSQL("delete from ps_claster where claster_id=".$_POST["claster_id"]);
        $coorArray = explode(" ", $_POST["coord"]);
        foreach ($coorArray as $coorEl) {
            $coor = explode(",", $coorEl);
            $queryDML = "INSERT INTO ps_claster(claster_id,lat,lng)VALUES(" .$_POST["claster_id"] . "," . $coor[0] . "," . $coor[1] .")";
            $resultDML = $mysqli->query($queryDML);
        }*/
    }
    // получаем из cookie масштаб и центр карты
    if (isset($_COOKIE['mapscale'])) $mapscale=$_COOKIE["mapscale"];
    else $mapscale='16';
    if (isset($_COOKIE['mapcenterlng'])) $mapcenterlng=$_COOKIE["mapcenterlng"];
    else $mapcenterlng=44.51728;
    if (isset($_COOKIE['mapcenterlat'])) $mapcenterlat=$_COOKIE["mapcenterlat"];
    else $mapcenterlat=48.70982;
    ?>
    <div style="display:inline-block; padding:2px; border:solid 1px darkblue; ">
        Кластера I <input type='checkbox' name='mapfilter_cluster' value='true' 
            onchange = 'if (this.checked) document.cookie = "mapfilter[cluster]=1"; else document.cookie = "mapfilter[cluster]=0";'
            <?php echo ((isset($_COOKIE['mapfilter']['cluster']) and $_COOKIE['mapfilter']['cluster']==1) ? " checked " : " "); ?>>
        Кластера II <input type='checkbox' name='mapfilter_cluster2' value='true' 
            onchange = 'if (this.checked) document.cookie = "mapfilter[cluster2]=1"; else document.cookie = "mapfilter[cluster2]=0";'
            <?php echo ((isset($_COOKIE['mapfilter']['cluster2']) and $_COOKIE['mapfilter']['cluster2']==1) ? " checked " : " "); ?>>
    </div>
    <div id="map" style="width: 100%; height: 600px; border: solid 1px darkblue;"></div>
    <script>
        var map=false;
        var infoWindow=false;
        var ruler=false;
        var mapLabel=false;
        var newPolygon=false;
        var activePolygon=false;
        // Левая кнопка - выбрать кластер
        function onMapClick(event) {
            infoWindow.setContent("("+event.latLng.lat()+","+event.latLng.lng()+") "+
                "<br><button name='addClusterButton' onclick='onAddClusterButtonClick("+event.latLng.lat()+","+event.latLng.lng()+")'>Создать новый кластер</button >"+
                "<br><button name='addRulerButton' onclick='onAddRulerButtonClick("+event.latLng.lat()+","+event.latLng.lng()+")'>Линейка</button >");
//            infoWindow.setContent("("+event.latLng.lat()+","+event.latLng.lng()+") ");
            infoWindow.setPosition(event.latLng);
            infoWindow.open(map);
        };
        // Левая кнопка - выбрать кластер
        function onClusterClick(event) {
            if(newPolygon){
                if(this!=newPolygon){
                    infoWindow.setContent("Завершите работу с новым кластером!");
                }else{
                    document.addCluster.claster_id.value = 'new';
                    var lBound = this.getPath().getArray();
                    var res='';
                    for(var i=0;i<lBound.length; i++){
                        res+=lBound[i].lat()+','+lBound[i].lng()+' ';
                    }
                    document.addCluster.coord.value = res;
                    infoWindow.setContent("("+this.claster_id+") "+document.addCluster.cname.value+"<br>"+document.addCluster.comment.value+
                        "<br><button name='saveClusterButton' onclick='onSaveClusterButtonClick()'>Сохранить изменения</button>");
                }
            } else {
                if(activePolygon){
                    activePolygon.setOptions({fillOpacity: 0.15, fillColor: '#FF0000'});
                }
                activePolygon=this;
                this.setOptions({fillOpacity: 0.3, fillColor: '#FFDD00', editable: true});
                document.addCluster.claster_id.value = this.claster_id;
                document.addCluster.cname.value = this.cname;
                document.addCluster.cluster_level.value = this.cluster_level;
                document.addCluster.comment.value = this.comment;
                document.addCluster.flag_pon.checked = (this.flag_pon==1 ? true : false);
                document.addCluster.flag_cuprum.checked = (this.flag_cuprum==1 ? true : false);
                document.addCluster.flag_optica.checked = (this.flag_optica==1 ? true : false);
                var lBound = this.getPath().getArray();
                var res='';
                for(var i=0;i<lBound.length; i++){
                    res+=lBound[i].lat()+','+lBound[i].lng()+' ';
                }
                document.addCluster.coord.value = res;
                infoWindow.setContent("("+this.claster_id+") "+this.cname+"<br>"+this.comment+
                    "<br><button name='saveClusterButton' onclick='onSaveClusterButtonClick()'>Сохранить изменения</button>"+
                    "<br><button name='deleteClusterButton' onclick='deleteCluster(\""+this.cname+"\","+this.claster_id+")'>Удалить кластер</button>");
            }
            infoWindow.setPosition(event.latLng);
            infoWindow.open(map);
        };
        // Правая кнопка - удаление кластера
//        function onClusterRightClick(event) {
//            document.addCluster.claster_id.value = this.claster_id;
//            document.addCluster.coord.value = "Удалить";
//            document.addCluster.cname.value = this.cname;
//            if(confirm("Удалить "+this.cname+" (id:"+this.claster_id+")")){
//                document.addCluster.submit();
//            }
//        };
        // Правая кнопка - удаление кластера
        function deleteCluster(lCName,lClaster_id) {
            
            if(confirm("Удалить "+lCName+" (id:"+lClaster_id+")")){
                document.addCluster.coord.value = "Удалить";
                document.addCluster.submit();
            }
        };
        // Создание/удаление ломаной-линейки
        function onAddRulerButtonClick(latC,lngC) {
            //var latC=map.getCenter().lat();
            //var lngC=map.getCenter().lng();
            if(ruler){ // удаляем линейку
                ruler.setMap(null);
                //document.getElementsByName('addRulerButton')[0].innerHTML="Линейка";
                //ruler=false;
            }//else{
                ruler = new google.maps.Polyline({
                    path: [{lat: latC, lng: lngC},{lat: latC, lng: lngC+0.01}
                        //,{lat: latC+0.01, lng: lngC+0.01}
                    ],
                    strokeColor: '#0000FF',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    editable: true
                });
                ruler.setMap(map);
                //mapLabel = new Label({ map: map });
                //mapLabel.bindTo('position', ruler, 'position');
                ruler.addListener('mouseup', onRulerMouseUp);
                //document.getElementsByName('addRulerButton')[0].innerHTML="Удалить линейку";
            //}
            infoWindow.close();
        };
        // Линейка - измерение и показ расстояния
        function onRulerMouseUp(event) {
            //mapLabel.set("text","длина "+google.maps.geometry.spherical.computeLength(ruler.getPath().getArray()).toFixed(1)+" м");
            infoWindow.setContent("длина "+google.maps.geometry.spherical.computeLength(ruler.getPath().getArray()).toFixed(1)+" м");
            infoWindow.setPosition(event.latLng);
            infoWindow.open(map);
        };
        // Создание нового класера
        function onAddClusterButtonClick(latC,lngC) {
            //var latC=map.getCenter().lat();
            //var lngC=map.getCenter().lng();
            if(!newPolygon){
            }else{
                //alert("Уже добавлен!")
                newPolygon.setMap(null);
            }
            newPolygon = new google.maps.Polygon({
                paths: [
                    {lat: latC, lng: lngC},
                    {lat: latC, lng: lngC+0.01},
                    {lat: latC+0.01, lng: lngC+0.01},
                    {lat: latC+0.01, lng: lngC}
                ],
                strokeColor: '#00FF00',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#00FF00',
                fillOpacity: 0.3,
                claster_id: 'new',
                cname: 'Имя нового кластера',
                comment: '',
                ltc_id: 1,
                flag_pon: 0,
                flag_cuprum: 0,
                flag_optica: 0,
                cstatus: 1,
                coord: '[]',
                cluster_level: 1,
                cluster_parent: '',
                editable: true
            });
            newPolygon.setMap(map);
    
            //document.getElementsByName('addClusterButton')[0].innerHTML="Пересоздать новый кластер";
            //document.addCluster.сlusterAct.disabled = false;
            document.addCluster.cname.value = newPolygon.cname;
            newPolygon.addListener('click', onClusterClick);
            infoWindow.close();
        };
        // Кнопка формы с типом submit - сохранение в первую очередь нового и
        // если такого нет, то выделенного кластера
        function onSubmit() {
            var res='';
            var lBound=false;
            if(newPolygon){
                document.addCluster.claster_id.value='new';
                lBound = newPolygon.getPath().getArray();
                for(var i=0;i<lBound.length; i++){
                    res+=lBound[i].lat()+','+lBound[i].lng()+' ';
                }
            }else if(activePolygon){
                document.addCluster.claster_id.value = activePolygon.claster_id;
                lBound = activePolygon.getPath().getArray();
                for(var i=0;i<lBound.length; i++){
                    res+=lBound[i].lat()+','+lBound[i].lng()+' ';
                }
            }
            document.addCluster.coord.value = res;
        };
        // Кнопка формы с типом submit - сохранение в первую очередь нового и
        // если такого нет, то выделенного кластера
        function onSaveClusterButtonClick() {
            document.addCluster.submit();
        };
        // Создание карты и объектов
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: <?php echo $mapcenterlat; ?>, lng: <?php echo $mapcenterlng; ?>},
                zoom: <?php echo $mapscale; ?>
            });
            map.addListener('center_changed', function() {
                // 3 seconds after the center of the map has changed, pan back to the marker.
                //window.setTimeout(
                //    function(){ map.panTo(marker.getPosition());},
                //    3000);
                var date = new Date(new Date().getTime() + 600 * 1000); // 600 sec
                document.cookie = "mapscale="+map.getZoom().toString()+"; path=/; expires=" + date.toUTCString();
                document.cookie = "mapcenterlng="+map.getCenter().lng().toString()+"; path=/; expires=" + date.toUTCString();
                document.cookie = "mapcenterlat="+map.getCenter().lat().toString()+"; path=/; expires=" + date.toUTCString();
            });

            //geocoder = new google.maps.Geocoder();
            //создаем инфоокно
            infoWindow = new google.maps.InfoWindow({
                content: '<div class="content">Какой-то контент</div>'
            });
            map.addListener('click', onMapClick);
            var pBound = [];
            var lPolygon ;
    <?php
    // добавляем кластеры из cluster для редактирования
    $testCluster=' where 1=2 ';
    if (isset($_COOKIE['mapfilter']['cluster']) and $_COOKIE['mapfilter']['cluster']==1) {
        $testCluster.=' or level=1 ';
    }
    if (isset($_COOKIE['mapfilter']['cluster2']) and $_COOKIE['mapfilter']['cluster2']==1) {
        $testCluster.=' or level=2 ';
    }
    $result_claster = qSQL("SELECT * FROM cluster ".$testCluster." order by level,id");
    $i = 1;
    while ($row_claster = $result_claster->fetch_array()) {
        $pBound=str_ireplace(",", "##", trim($row_claster["coord"]));
        $pBound=str_ireplace(" ", "},{lat: ", $pBound);
        $pBound="[{lat: ".str_ireplace("##", ", lng: ", $pBound)."}]";
        /*echo "pBound = [";
        $result_laln = qSQL("SELECT * FROM ps_claster where claster_id=" . $row_claster["id"] . " order by id");
        $isFirstVertex=TRUE;
        while ($row_laln = $result_laln->fetch_array()) {
            if($isFirstVertex) $isFirstVertex=FALSE;
            else echo ",";
            echo "{lat: " . $row_laln["lat"] . ", lng: " . $row_laln["lng"] . "}";
        }
        echo "];";*/
        echo "lPolygon=new google.maps.Polygon({
            paths: ".$pBound.",
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.15,
            claster_id: '".$row_claster["id"]."',
            cname: '".$row_claster["cname"]."',
            comment: '".$row_claster["comment"]."',
            ltc_id: '".$row_claster["ltc_id"]."',
            flag_pon: '".$row_claster["flag_pon"]."',
            flag_cuprum: '".$row_claster["flag_cuprum"]."',
            flag_optica: '".$row_claster["flag_optica"]."',
            cstatus: '".$row_claster["cstatus"]."',
            coord: '".$row_claster["coord"]."',
            cluster_level: '".$row_claster["level"]."',
            cluster_parent: '".$row_claster["parent"]."',
            editable: false
        });
        lPolygon.setMap(map);
        //polygons.push(lPolygon);
        
        // !!! - так неправильно google.maps.event.addListener(lPolygon,'click',onClusterClick(lPolygon));
        // т.к. onClusterClick(lPolygon) вызывается сразу
        // так можно - google.maps.event.addListener(lPolygon,'click',onClusterClick);
        // для передачи параметров - google.maps.event.addListener(lPolygon,'click',function(){onClusterClick(lPolygon)});
        
        lPolygon.addListener('click', onClusterClick);
        //lPolygon.addListener('rightclick', onClusterRightClick);

        ";
        $i++;
    }
    // добавляем заявки из ps_list для ориентировки при редактировании кластера
    /*$result_tasks = qSQL("SELECT arm_id, device_address,latlng, list_id FROM ps_list where latlng<>'' group by arm_id");
    $i = 1;
    while ($row_tasks = $result_tasks->fetch_array()) {
        list ($lat, $lng) = explode(":", $row_tasks["latlng"]);
        echo "new google.maps.Marker({position: {lat: " . $lat . ", lng: " . $lng . 
            "}, map: map, icon: 'images/ball_green_s.png', title: '".$row_tasks["arm_id"]."'});
            ";
        $i++;
    }*/
    ?>
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcECW3bzY2r-yyC8NEU1OSAAXNB-o-d7s&libraries=geometry&callback=initMap" async defer></script>
    <!--script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEGBXxw-9wPP2x-f_aNMSpM1L5yCX1knI&callback=initMap" async defer></script-->
    <?php
    //echo "<form name='addCluster' method='post' action='./?c=7&action=clasters&do=edit'>";
    echo "<div style=\"margin-top: 10\">
        <form name='addCluster' method='post' onsubmit=\"onSubmit();return true;\">
        <b>Наименование: </b><input name='cname' type='text' size='40' value=''>
        <b>РФ: </b>
            <select name='rf'><option value=''></option>
                <option value='1'>Краснодарский</option>
                <option value='2' selected>Волгоградский</option>
            </select>
        <b>ЛТЦ: </b>" . select('ltc', "SELECT name FROM ps_ltc order by name") . "
        <b>Уровень: </b>
            <select name='cluster_level'>
                <option value='1' selected>1. масштаб района</option>
                <option value='2'>2. масштаб поселка/микрорайона</option>
            </select>
        <br>
        <b>Признак наличия PON в кластере: </b><input type='checkbox' name='flag_pon' value='1' checked>
        <b>Наличие медной сети: </b><input type='checkbox' name='flag_cuprum' value='1' checked>
        <b>Наличие Оптической сети (Кроме PON): </b><input type='checkbox' name='flag_optica' value='1' checked>
        <br>
        <b>Комментарий: </b><input name='comment' type='text' size='40' value=''>
        <input type='hidden' name='coord' value=''>
        <input type='hidden' name='claster_id' value=''>
        </form>
        </div>";

//        <input disabled name='сlusterAct' type='submit' value='Сохранить'>
    
}
?>
