<?php
require_once 'vlg_php_header.php';
//
    /*if(isset($_REQUEST["mapfilter_address"])){
        setcookie('mapfilter[address]', $_REQUEST["mapfilter_address"]);
    } elseif(isset($_COOKIE['mapfilter']['address'])){
        $_REQUEST["mapfilter_address"]=$_COOKIE['mapfilter']['address'];
    } else {
        setcookie ('mapfilter[address]', '');
        $_REQUEST["mapfilter_address"]='';
    }*/
//
    if(isset($_REQUEST["mapfilter_project"])){
        // ��������� - ������� �� ������
        if(isset($_COOKIE['mapfilter']['project']) and $_COOKIE['mapfilter']['project']!=$_REQUEST["mapfilter_project"]){
            // �� - ������ ������
            $project_as_map_center=explode(" ",$_REQUEST["mapfilter_project"])[0];
        }else{
            $project_as_map_center=-1;
        }
        setcookie('mapfilter[project]', $_REQUEST["mapfilter_project"]);
    } elseif(isset($_COOKIE['mapfilter']['project'])){
        $_REQUEST["mapfilter_project"]=$_COOKIE['mapfilter']['project'];
    } else {
        setcookie ('mapfilter[project]', "������� ������");
        $_REQUEST["mapfilter_project"]="������� ������";
    }
//
    /*if(isset($_REQUEST["mapfilter_mctet"])){
        setcookie('mapfilter[mctet]', $_REQUEST["mapfilter_mctet"]);
    } elseif(isset($_COOKIE['mapfilter']['mctet'])){
        $_REQUEST["mapfilter_mctet"]=$_COOKIE['mapfilter']['mctet'];
    } else {
        setcookie ('mapfilter[mctet]', "��������...");
        $_REQUEST["mapfilter_mctet"]="��������...";
    }*/
    syncReqCook("mapfilter","address","");
    syncReqCook("mapfilter","mctet","��������...");
    // vv ��� vv
    //syncReqCook("mapfilter","ltc","��������...");
    $ltc_as_map_center=false;
    if(isset($_REQUEST["mapfilter_ltc"])){
        // ��������� - ������� �� 
        if(isset($_COOKIE['mapfilter']['ltc']) and $_COOKIE['mapfilter']['ltc']!=$_REQUEST["mapfilter_ltc"]){
            // �� - ������
            //$ltc_as_map_center=explode(" ",$_REQUEST["mapfilter_ltc"])[0];
            $ltc_as_map_center=$_REQUEST["mapfilter_ltc"];
        }else{
        }
        setcookie('mapfilter[ltc]', $_REQUEST["mapfilter_ltc"]);
    } elseif(isset($_COOKIE['mapfilter']['ltc'])){
        $_REQUEST["mapfilter_ltc"]=$_COOKIE['mapfilter']['ltc'];
    } else {
        setcookie ('mapfilter[ltc]', "��������...");
        $_REQUEST["mapfilter_ltc"]="��������...";
    }
    // ^^ ��� ^^
    $mapfilter_ltc_id=rSQL("SELECT lid FROM ltc where lname='".$_REQUEST["mapfilter_ltc"]."'")["lid"];
    if(!$mapfilter_ltc_id) $mapfilter_ltc_id=-1;
    // �������� �� cookie ������� �����
    if (isset($_COOKIE['mapscale'])) $mapscale=$_COOKIE["mapscale"];
    else $mapscale='16';
    //
//
require_once 'func.inc.php';
require_once 'func_date.inc.php';
require_once 'vlg_util_ps.php';
require_once 'vlg_header.php'; // ����� ������ HTML ��������
if (!defined("LOGINED")) {
    "<a href='index.php?c=4'><b>��� ���������� �������������� - �������� �� ���� ������</b></a>";
    exit();
}
?>
<TR><TD colspan='2'>
<table border='0' cellspacing='0' cellpadding='0' width='98%' height='100%' align='center'>
<tr><td valign='top' style='PADDING-LEFT: 35px;'>
<?php
// ������ � ������, � �.�. ��� �������������� �������
//function vlg_map($row_users) {
    /*if (@$_GET["add_ps_id"]) {
        $result_add_cid2 = qSQL("SELECT arm_id, list_id, device_address,latlng FROM ps_list where list_id='" . $_GET["add_ps_id"] . "'");
        $row_add_cid2 = mysql_fetch_array($result_add_cid2);
        $_REQUEST["mapfilter_address"] = $row_add_cid2["device_address"];
    }*/ //elseif (!@$_POST["address"])
      //  $_POST["address"] = "�. ���������, ��. ����, 16";
    //
    ?>
    <script src="js/leaflet.js"></script>
    
    <!-- ��� ������������� ��������-�������� -->
    <link rel="stylesheet" href="js/Leaflet.markercluster-1.2.0/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="js/Leaflet.markercluster-1.2.0/dist/MarkerCluster.Default.css" />
    <script src="js/Leaflet.markercluster-1.2.0/dist/leaflet.markercluster-src.js"></script>
    
    <!--script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCcECW3bzY2r-yyC8NEU1OSAAXNB-o-d7s&libraries=geometry&callback=initMap" async defer></script-->
    <!--script src="js/leaflet-editable-polyline-master/src/leaflet-editable-polyline.js"></script-->
    <!--script src="js/leaflet-plotter-master/src/leaflet.plotter.js"></script--> 
    <script src="js/vlg.leaflet.plotter.js"></script> 
    <script type="text/javascript">
    var ruler=false; // �������
    var callArr=[]; // ������ �� �������
    var abonArr=[]; // ������ �� (������������) ���������
    var current_ps_list_dop=false; // ��������� ���������� �������� ps_list_dop.lid
    var lClaster=false;
    var popupBuffer;
    var �allChoose=false; // ��������� ��������� ������
    var callChooseMarker=false; // ��������� ��������� ��������� ������
    var UDChoose=false; // ��������� ��������� ��
    var UDChooseMarker=false; // ��������� ���������� ���������� ��
    //var mapobjChoose=false; // ��������� ��������� mapobj !!!  �� ����� - ���� lineBuffer.options.mapobj_id
    //var mapobjChooseMarker=false; // ��������� ���������� ���������� mapobj !!!  �� ����� - ���� lineBuffer
    var pointBuffer=false; // ������������� �����-������
    var lineBuffer=false; // ������������� �����
    var areaBuffer=false; // ������������� ������� 
    var mymap=false; // �����
    xmlHttp=new XMLHttpRequest();
    var userId=<?php echo $row_users["uid"]; ?>;
    // �������� ���� �������/�������� �������
        function onPojectIdNameButton(){
            var lPCMain=document.getElementById("project_content");
            var lPC=document.getElementById("project_content2");
            var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
            if(lPCMain.style.display=="block"){
                lPCMain.style.display='none';
                document.getElementsByName("projectIdNameButton")[0].value='������: ';
            } else {
                if(lSelectValue=="������� ������"){
                    //alert(lSelectValue);
                    document.getElementById("create_project").style.display="block";
                }else{
                    lPCMain.style.display="block";
                    document.getElementsByName("projectIdNameButton")[0].value='������� ���� �������';
                    var prId=lSelectValue.split(' ')[0];
                    var res="";
                    callArr.forEach( function (callE, indexE) { 
                        if(callE.project_id==prId) 
//                        res+="<a href='vlg_map.php?action=true&edit_project=true&project_id="+callE.project_id+
//                            "&proj_del_cid="+indexE+"&for_my_job="+document.saddress.for_my_job.value+"'>[X]</a> "+
                        res+="<a href='vlg_map.php?action=true&edit_project=true&project_id="+callE.project_id+
                            "&proj_del_cid="+indexE+"'>[X]</a> "+
                        "<b>"+callE.arm_id+"</b> "+
                        callE.device_address+"<br>";
                    } );
                    /*res+="<form name='edit_project_body' method='post' style='text-align: right;padding:4px; border:solid 1px darkblue;' action='vlg_map.php?save_project=true'>\
                        ������������ �������:<input type='text' name='project_name' size='40' value=''><br> \
                        �����������:<input type='text' name='project_deficient' size='16' value=''>��<br> \
                        ������.�����:<input type='text' name='project_install' size='16' value=''>���<br> \
                        ������.�����:<input type='text' name='project_month_pay' size='16' value=''>���<br> \
                        ����� ������ ���������:<input type='text' name='project_setting' size='16' value=''>���<br> \
                        �����������:<textarea name='comment' rows='4' cols='31'></textarea><br> \
                        <input type='submit' value='��������� ���������'> \
                    </form>";*/
                    lPC.innerHTML=res;
                }
            }
        }
        // ��������/�������� �������-�������
        function onAddRulerButtonClick(latC,lngC) {
            if(ruler){ // ������� ������������ �������
                mymap.removeLayer(ruler);
                ruler=false;
            } else {
                //ruler = L.polyline([[latC,lngC],[latC,lngC+0.01]], {color: 'blue', interactive: true}).addTo(mymap);
                // zoom the map to the polyline
                //map.fitBounds(ruler.getBounds());
                
//                ruler = L.Polyline.PolylineEditor([[latC,lngC],[latC,lngC+0.01]],  {
//                    // The user can add new polylines by clicking anywhere on the map:
//                    newPolylines: true,
//                    maxMarkers: 100
//                }).addTo(mymap);

                ruler = L.Polyline.Plotter([[latC,lngC],[latC,lngC+0.002]],{weight: 1, color: 'red'}).addTo(mymap);

                //mapLabel = new Label({ map: map });
                //mapLabel.bindTo('position', ruler, 'position');
                //ruler.addListener('mouseup', onRulerMouseUp);
                //document.getElementsByName('addRulerButton')[0].innerHTML="������� �������";
                //infoWindow.close();
            }
        };
        // ������� ����� ������� � ���������� � � document.getElementById("rulerlabel")
        function onRulerLabelClick(){
            if(ruler){ // ������� ����� �������
                //var points=ruler.getPoints();
                var points=ruler.getLatLngs();
                //ruler.setReadOnly(true);                    
                var dist=0;
                var str_points="";
                for(pin=0;pin<(points.length-1);pin++){
                    //dist+=points[pin].getLatLng().distanceTo(points[pin+1].getLatLng());
                    dist+=points[pin].distanceTo(points[pin+1]);
                    str_points+=""+points[pin].lat+","+points[pin].lng+"_";
                }
                str_points+=""+points[points.length-1].lat+","+points[points.length-1].lng;
                document.getElementById("rulerlabel").innerHTML="<b>"+dist.toFixed(1)+"&nbsp;�&nbsp;</b>"+
                    "<a href='vlg_report.php?func=1<?php echo "&ltc_id=".$mapfilter_ltc_id; ?>&points="+str_points+
                    "' title='Excel-����� �� �������� ������ �������'>[�����]</a>";
            } else {
                alert("��� �������� ������� - �������� �� �����");
            }
        }
        // ������� ������
        function onMoveMarkerButtonClick(latC,lngC) {
            if( �allChoose ){
                �allChoose.setLatLng([latC,lngC]);
                callChooseMarker.setLatLng([latC,lngC]);
                //jSQL("update","update ps_list set latlng='"+latC+":"+lngC+"' where list_id="+�allChoose.options.list_id);
                SQL("update","update ps_list set latlng='"+latC+":"+lngC+"' where list_id="+�allChoose.options.list_id);
                SQL("update","update ps_list_dop set location_type='manual' where list_id="+�allChoose.options.list_id);
            }
        };
        // ������� ��
        function onMoveUDButtonClick(latC,lngC) {
            if( UDChoose ){
                UDChoose.setLatLng([latC,lngC]);
                UDChooseMarker.setLatLng([latC,lngC]);
                //if(UDChoose.options.icon==spdporticon){
                    jSQL("update","update com_obj set lat='"+latC+"', lng='"+lngC+"', place_id='manual' where oid="+UDChoose.options.oid);
                //} else {
                //    jSQL("update","update ps_olayers set lat='"+latC+"', lng='"+lngC+"' where oid="+UDChoose.options.oid);
                //}
            }
        };
    </script>
    <div id="project_content" style="display:none; position:fixed; left:150px; top:190px; z-index:1000; opacity: 1; background-color:#FFF; "> 
        <div id="project_content2" style="
            width:500px; height:300px; 
            overflow-x:scroll; overflow-y:scroll; white-space:nowrap; 
            padding:2px; border:solid 1px darkblue; margin-bottom: 6px; "> 
        </div>
        <button name='projectContentButton' style="float:right;"
                onclick='document.getElementById("project_content").style.display="none";
                document.getElementsByName("projectIdNameButton")[0].value="������: ";'>������� ����</button>
    </div>
    <div id="create_project" style="display:none; position:fixed; left:350px; top:190px; z-index:1000; opacity: 1; 
         background-color:#FFF; padding:4px; border:solid 1px darkblue;"> 
        <form name='new_project' method='post' style="text-align: right;padding:4px; border:solid 1px darkblue;" action='vlg_map.php?create_project=true'>
            ������������ �������:<input type='text' name='project_name' size='40' value=''><br>
            �����������:<input type='text' name='project_deficient' size='16' value=''>��<br>
            ������.�����:<input type='text' name='project_install' size='16' value=''>���<br>
            ������.�����:<input type='text' name='project_month_pay' size='16' value=''>���<br>
            ����� ������ ���������:<input type='text' name='project_setting' size='16' value=''>���<br>
            �����������:<textarea name='comment' rows='4' cols='31'></textarea><br>
            <input type='submit' value='������� ������'>        
        </form>
            <button style="float:right;"
                onclick='document.getElementById("create_project").style.display="none";'>������� ����</button>
    </div>
    <div style="display:inline-block;"><form name='saddress' id='saddress_id' method='post' action='vlg_map.php?action=true'>
        <!--div style="display:inline-block; padding:2px; border:solid 1px darkblue; ">
            ��� ������ <input type="checkbox" name="for_my_job"<!--?php echo ((isset($_REQUEST["for_my_job"]) and $_REQUEST["for_my_job"]=='on') ? "checked" : "");?>>
        </div-->
        <div style="display:inline-block; padding:2px; border:solid 1px darkblue; ">
            <label id="map_scale_level" title="'�������' :-("><?php echo $mapscale;?></label>
            OSM<input type="checkbox" name="mapfilter_osm" title='�������� Yandex ��� OSM' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[osm]=1"; else document.cookie = "mapfilter[osm]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['osm']) and $_COOKIE['mapfilter']['osm']==1) ? "checked" : " ");?>>
            ��� ������<input type="checkbox" name="mapfilter_for_my_job" value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[for_my_job]=1"; else document.cookie = "mapfilter[for_my_job]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['for_my_job']) and $_COOKIE['mapfilter']['for_my_job']==1) ? "checked" : " ");?>>
            �����������<input type="checkbox" name="mapfilter_outskirts" id="checkbox_outskirts" value='true'>
            ��<input type="checkbox" name="mapfilter_clanalysis" title='���������� ������ ������' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[clanalysis]=1"; else document.cookie = "mapfilter[clanalysis]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['clanalysis']) and $_COOKIE['mapfilter']['clanalysis']==1) ? "checked" : " ");?>>
        </div>
        <div style="display:inline-block; padding:2px; border:solid 1px darkblue; ">
            B2C<input type="checkbox" name="mapfilter_b2c" title='�������� ���������� ���� � ������� � ���.���������' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[b2c]=1"; else document.cookie = "mapfilter[b2c]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['b2c']) and $_COOKIE['mapfilter']['b2c']==1) ? "checked" : " ");?>>
            B2B<input type="checkbox" name="mapfilter_b2b" title='�������� ����������� ���� � ������� � ���.���������' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[b2b]=1"; else document.cookie = "mapfilter[b2b]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['b2b']) and $_COOKIE['mapfilter']['b2b']==1) ? "checked" : " ");?>>
            ����.���<input type="checkbox" name="mapfilter_call" title='������ ���' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[call]=1"; else document.cookie = "mapfilter[call]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['call']) and $_COOKIE['mapfilter']['call']==1) ? "checked" : " ");?>>
            ���.���.����<input type="checkbox" name="mapfilter_restrict_existabon" 
                title='������������ ���-�� ������������ ������������ ���������' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[restrict_existabon]=1"; else document.cookie = "mapfilter[restrict_existabon]=0";'
                <?php 
                    // ver.1
                    //echo ((isset($_COOKIE['mapfilter']['restrict_existabon']) and $_COOKIE['mapfilter']['restrict_existabon']==1) ? "checked" : " ");
                    // ver.2
                    echo " checked disabled=true ";
                ?>>
            ���.����.<input type="checkbox" name="mapfilter_existabon" 
                value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[existabon]=1"; else document.cookie = "mapfilter[existabon]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['existabon']) and $_COOKIE['mapfilter']['existabon']==1) ? "checked" : " ");?>
                <?php echo (($mapscale>=14) ? 
                        " title='������������ ��������' " 
                        : 
                        "title='������������ �������� (��� ����������� ��������� ������� (�� 500 � [14]) � ������� [�������� �����])' "
                        . " disabled=true");?>
                >
            
            ������.<input type="checkbox" name="mapfilter_stray" title='������� � ��������� ������������' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[stray]=1"; else document.cookie = "mapfilter[stray]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['stray']) and $_COOKIE['mapfilter']['stray']==1) ? "checked" : " ");?>>
        </div>
        <div style="display:inline-block; padding:2px; border:solid 1px darkblue; ">
            ���<input type='checkbox' name='mapfilter_UD' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[UD]=1"; else document.cookie = "mapfilter[UD]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['UD']) and $_COOKIE['mapfilter']['UD']==1) ? " checked " : " "); ?>>
            ��<input type='checkbox' name='mapfilter_DB' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[DB]=1"; else document.cookie = "mapfilter[DB]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['DB']) and $_COOKIE['mapfilter']['DB']==1) ? " checked " : " "); ?>>
            ���<input type='checkbox' name='mapfilter_SPDPort' value='true'
                onchange = 'if (this.checked) document.cookie = "mapfilter[SPDPort]=1"; else document.cookie = "mapfilter[SPDPort]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['SPDPort']) and $_COOKIE['mapfilter']['SPDPort']==1) ? " checked " : " "); ?>>
        </div>
        <div style="display:inline-block; padding:2px; border:solid 1px darkblue; ">
            �������� I<input type='checkbox' name='mapfilter_cluster' value='true' 
                onchange = 'if (this.checked) document.cookie = "mapfilter[cluster]=1"; else document.cookie = "mapfilter[cluster]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['cluster']) and $_COOKIE['mapfilter']['cluster']==1) ? " checked " : " "); ?>>
            II<input type='checkbox' name='mapfilter_cluster2' value='true' 
                onchange = 'if (this.checked) document.cookie = "mapfilter[cluster2]=1"; else document.cookie = "mapfilter[cluster2]=0";'
                <?php echo ((isset($_COOKIE['mapfilter']['cluster2']) and $_COOKIE['mapfilter']['cluster2']==1) ? " checked " : " "); ?>>
        </div>
        <div style="display:inline-block; padding:1px; border:solid 1px darkblue; ">
            ����� �� ������/������<input type='text' name='mapfilter_address' size='40' value='<?php echo $_REQUEST["mapfilter_address"]; ?>'> 
        </div>
        <!--div style="display:inline-block; padding:1px; border:solid 1px darkblue; ">
            ����� <!--?php echo select('mapfilter_mctet', "SELECT name FROM ps_mctet order by name",$_REQUEST["mapfilter_mctet"],"��������..."); ?> 
        </div-->
        <div style="display:inline-block; padding:1px; border:solid 1px darkblue; ">
            ��� <?php echo select('mapfilter_ltc', "SELECT lname FROM ltc order by lname",$_REQUEST["mapfilter_ltc"],"��������..."); ?> 
        </div>
        <input type='submit' value='�������� �����'>
    </form></div><br>
    <div  style="display:inline-block; border:solid 1px darkblue;">
        <input type='button' name='projectIdNameButton' value='������: ' onclick='onPojectIdNameButton()'>
    <?php
    // �������� ����� �������� �������
    if (@$_GET["create_project"] == 'true') {
        $oCSQL=new CSQL("INSERT ps_project (project_id,project_name,user_id,
            comment,dateinsert,status,zatrat_smr,dev_summ,install,
            month_pay,deficient,setting,payback,project_type,project_subtype,
            technology,service_id,port_num,parent,household,ltc)values (NULL,'" . $_POST["project_name"] . "', '" . 
            $row_users["uid"] . "', '" . $_POST["comment"] . "', NULL, '10', 0, 0, '" . 
            ($_POST["project_install"] ? $_POST["project_install"] : 0) . "', '" . 
            ($_POST["project_month_pay"] ? $_POST["project_month_pay"] : 0) . "','" . 
            ($_POST["project_deficient"] ? $_POST["project_deficient"] : 0) . "','" . 
            ($_POST["project_setting"] ? $_POST["project_setting"] : 0) . "',0,'','',-1,0,0,NULL,0,".$mapfilter_ltc_id.")");
        $new_proj_id = $oCSQL->insert_id();
        $oCSQL->commit();
        echo "<script>document.getElementById(\"info_edit_project\").innerHTML=\"<br><b style='color: blue;'>������ ����� ������ � ".
                $new_proj_id."</b>\"</script>";
        $edit_mode_id = $new_proj_id;
    }
    // select ������� �����, ����� ���������� �������� �������
    if(isset($new_proj_id)){
        $project_id_name_initial=rSQL("SELECT concat(project_id,' ',project_name) project_name FROM ps_project where project_id=".
                $new_proj_id)["project_name"];
    }elseif(isset($_REQUEST['mapfilter_project'])){
        $project_id_name_initial=$_REQUEST['mapfilter_project'];
    }elseif(isset($_REQUEST['project_id'])){
        $project_id_name_initial=rSQL("SELECT concat(project_id,' ',project_name) project_name FROM ps_project where project_id=".
                $_REQUEST['project_id'])["project_name"];
    }else{
        $project_id_name_initial="������� ������";
    }
    echo str_replace('select name=',"select form='saddress_id' name=",
        select('mapfilter_project', 
            "SELECT concat(project_id,' ',ltc.lname,' [',project_name,']') FROM ps_project p left join ltc on p.ltc=ltc.lid order by ltc.lname,project_name",
            $project_id_name_initial,"������� ������")).
        "<b id='info_edit_project'></b></div>";
    // ^^ div �������^^
    echo "&nbsp;<label>�������/�������: </label><label id='rulerlabel' onclick='onRulerLabelClick();'><b>0</b></label>&nbsp;";
    //
    echo "&nbsp;<div style=\"display:inline-block; padding:1px; border:solid 1px darkblue; \">
        <input form='saddress_id' type='number' name='overdue_call_min' min=0 max=999 value='0' style='width: 4em;'>��. <= ������������ ������ <= 
        <input form='saddress_id' type='number' name='overdue_call_max' min=1 max=999 value='1'style='width: 4em;'>��. 
        </div>";
    // ������� ������ ��� �������� �� �������
    if (@$_GET["edit_project"] == 'true') {
        if (@$_GET["proj_del_cid"] != '') {

            /*$result_delete = qSQL("UPDATE ps_project_list SET delete_flag='1' where list_id='" . 
                    $_GET["proj_del_cid"] . "' and project_id='" . $_GET["project_id"] . "'");
            if (@$result_delete == TRUE)
                echo "<script>document.getElementById(\"info_edit_project\").innerHTML=\"<i style='color: red;'>�� ������� � ".
                    (isset($_GET['project_id']) ? $_GET['project_id'] : $new_proj_id)." ������� ������ </i>\"</script>";*/

            $result_delete = SQL("delete from ps_project_list where list_id='" . $_GET["proj_del_cid"] . 
                    "' and project_id='" . $_GET["project_id"] . "'")->commit();
            
        }
        $edit_mode_id = $_GET['project_id'];
    }
    // �������� ���� ������ �������� �� ���� ��������
    if(isset($_REQUEST['clusterdelfromprojects'])){
        SQL("delete from ps_project_list where list_id in (select list_id from ps_list_dop where claster_id=".$_REQUEST['clusterdelfromprojects'].")")->commit();
    }
    // �������� ������ �������� � ������
    if(isset($_REQUEST['clusterclick']) and isset($_REQUEST['addtoproject'])){
        SQL("delete from ps_project_list where list_id in (select list_id from ps_list_dop where claster_id=".$_REQUEST['clusterclick'].")")->commit();
        SQL("insert into ps_project_list (select NULL,'". $_REQUEST['addtoproject'] ."',list_id,NULL,'".
            $row_users["uid"] ."',0 from ps_list_dop where claster_id=". $_REQUEST['clusterclick'] .")")->commit();        
    }
    ////////////////////////////////////////////////////////////////////////////
    // ������������ click �� ������ ������� ����������/����������/��������
    if(isset($_REQUEST['areaclick']) or isset($_REQUEST['clustareaclick'])){
        if(isset($_REQUEST['areaclick'])){
            $row_map_obj=rSQL("select latlng from map_obj where id=".$_REQUEST['areaclick']);
        }else{
            $row_map_obj=rSQL("select coord latlng from cluster where id=".$_REQUEST['clustareaclick']);
        }
        $arPolygon = array();
        $row_map_obj_latlng=explode(" ",trim($row_map_obj["latlng"]));
        for($k=0;$k<count($row_map_obj_latlng);$k++){
            list($arPolygon[$k][0],$arPolygon[$k][1]) = explode(",",$row_map_obj_latlng[$k]);
        }
        //INCLUDE "classes.php";
        $polygon = new Polygon();
        $polygon->set_polygon($arPolygon);
//        $cursor=new CSQL("
//            SELECT '������' lay_type,psl.list_id,psl.latlng,psl.cs,psl.technology,psl.service
//                FROM ps_list psl left join ps_list_dop psld on psl.list_id=psld.list_id
//            union
//            select mot.mname lay_type,oid,concat(lat,':',lng),'','','' from com_obj co left join map_obj_type mot on co.lay_type=mot.id            
//            union            
//            select mot.mname type,mo.id,latlng,'','','' from map_obj mo left join map_obj_type mot on mo.type=mot.id");
        $cursor=new CSQL("
            SELECT '������' lay_type,psl.list_id,psl.latlng,case when psl.cs=1 then '��' else '��' end cs,psl.technology,psl.service
                FROM ps_list psl left join ps_list_dop psld on psl.list_id=psld.list_id
            union all
            select mot.mname lay_type,oid,concat(lat,':',lng),'','','' from com_obj co left join map_obj_type mot on co.lay_type=mot.id            
            union all            
            select mot.mname lay_type,mo.id,latlng,'','','' from map_obj mo left join map_obj_type mot on mo.type=mot.id
            union all
            select case when abonserv.tid=1 then '���.xDSL' else '���.FTTx' end lay_type,
		abonent.aid,concat(addrcache.lat,':',addrcache.lng),
                case when abonent.atype=1 then '�� ��' else '�� ��' end ,
                case when abonserv.tid=1 then 'xDSL' else 'FTTx' end ,'' 
                from abonent join addrcache on abonent.address_id=addrcache.aid 
                    join abonserv on abonent.aid=abonserv.aid
                where abonent.ltc=".$mapfilter_ltc_id);
        $clustermembercount=0;
        $testclustercount=0;
        $nolatlngcount=0;
        $areastat=array();
        while ($cursor->assoc()) { // ������� ������ � �������� �����	
            if ($cursor->r["latlng"] == ''){
                $nolatlngcount++; // ���������� �� ��������
            } else {
                $testclustercount++;
                list ($ulat_x, $ulng_y) = explode(":", $cursor->r["latlng"]);
                $result_polygon_calc = $polygon->calc([ 'x' => $ulat_x, 'y' => $ulng_y, ]);
                if ($result_polygon_calc == 1 or $result_polygon_calc == -1) {
                    if(isset($_REQUEST['areastat']) or isset($_REQUEST['clustareastat'])){ // ���������� �� ������
                        if(isset($areastat[$cursor->r["lay_type"]][$cursor->r["cs"]][$cursor->r["technology"]][$cursor->r["service"]]))
                            $areastat[$cursor->r["lay_type"]][$cursor->r["cs"]][$cursor->r["technology"]][$cursor->r["service"]]++;
                        else
                            $areastat[$cursor->r["lay_type"]][$cursor->r["cs"]][$cursor->r["technology"]][$cursor->r["service"]]=1;
                    } else if($cursor->r["lay_type"]=='������' and isset($_REQUEST['delfromproject'])){ // ������� ������ ������ �� �������
                        //<a href='vlg_map.php?areaclick="+lmapobj_id+"&delfromproject="+lproject_id+"'>������� ������ ������ �� �������</a>
                        // !!! ��� ������� ��. ����  !!! ����� �.�. ����� �������:
                        // SQL("delete from ps_project_list where project_id= ".$_REQUEST['delfromproject'] ." and list_id = ". $cursor->r["list_id"])->commit();
                        SQL("delete from ps_project_list where list_id = ". $cursor->r["list_id"])->commit();
                    } else if($cursor->r["lay_type"]=='������' and isset($_REQUEST['addtoproject'])){ // �������� ������ ������ � ������
                        SQL("delete from ps_project_list where list_id = ". $cursor->r["list_id"])->commit();
                        SQL("insert into ps_project_list values (NULL,'". $_REQUEST['addtoproject'] ."','" . $cursor->r["list_id"] ."',NULL,'".
                            $row_users["uid"] ."',0)")->commit();
                        $clustermembercount++;
                    }
                }
            }
        }
        $cursor->free();
        // ��� ����� �������� �������� ������ ��� ������ � �������� ���� ��������:
        //document.getElementById("info_edit_project").innerHTML="<i style='color: green;'>"+...+"</i>";
        //echo "������ � ������������ ������������ ".$nolatlngcount." ��������� ".$testclustercount." ����������� ".$clustermembercount."<br>";
        $areastat_html="";
        if(isset($_REQUEST['areastat']) or isset($_REQUEST['clustareastat'])){ // ���������� �� ������
            foreach($areastat as $key_lay_type => $value_lay_type) {
                foreach($value_lay_type as $key_cs => $value_cs) {
                    foreach($value_cs as $key_technology => $value_technology) {
                        foreach($value_technology as $key_service => $value_service) {
                            $areastat_html.="<tr><td><span class='emtextblue' >".$key_lay_type.
                                    "</span></td><td><span class='emtextgreen' >".$key_cs.
                                    "</span></td><td>".$key_technology.
                                    "</td><td>".$key_service.
                                    "</td><td>".$value_service."</td></tr>";
                        }
                    }
                }
            }
        }
    }
    //
    ////////////////////////////////////////////////////////////////////////////
    // ��������� �����
    $lat=$lng=false;
    if(is_numeric(trim($_REQUEST["mapfilter_address"]))){
    // ��������� ����� �� ������ ������
        list($lat, $lng) = explode(":", rSQL("SELECT latlng FROM  ps_list where arm_id='".trim($_REQUEST["mapfilter_address"])."'")["latlng"]);
    }
    if(!(empty($lat) or empty($lng))){
            
    }else{
    // ��������� ����� �� ������
        if(empty(trim($_REQUEST["mapfilter_address"]))){
            //$searchaddress="��������� �� ���� 16";
            $lng=44.5169300;
            $lat=48.7070730;
        } else {
            ////////////////////////////////////////////////////////////////////
            // Google API !!!
//            $searchaddress=addressFormat2($_REQUEST["mapfilter_address"]);
//            //d($searchaddress);d("<br>");
//            $xml = simplexml_load_string(getUrl('http://maps.google.com/maps/api/geocode/xml?address=' .
//                            urlencode(iconv('CP1251', 'UTF-8', $searchaddress)) . '&language=ru'));
//            $status = $xml->status;
//            //d("������: " . $status);
//            //if (@$row_add_cid2["latlng"] != '' and @ $_GET["add_ps_id"] > 0) { // ���� ���� ���������� � ������, ������� ������ ��� �������� � ������, 
//            // �� �������� ���������� ��� � ������ �� �����
//            //    list ($lat, $lng) = explode(":", $row_add_cid2["latlng"]);
//            //} else {
//                $lat = $xml->result->geometry->location->lat;
//                $lng = $xml->result->geometry->location->lng;
//            //}
//            //d("����������: " . $lat . ":" . $lng);
//            $formatted_address = $xml->result->formatted_address; // ��������������� ����� ������
//            $place_id = $xml->result->place_id; // ���������� ������������� ������� � ����
//            $location_type = $xml->result->geometry->location_type; // ROOFTOP - ������ ������, � ������ �������� �������.
//            //e("����� GOOGLE: " . iconv('UTF-8', 'CP1251', $formatted_address));
//            //d("��������: " . $location_type);
//            //d("���������� ����� GOOGLE: " . $place_id);
            ////////////////////////////////////////////////////////////////////
            // Yandex API !!!
            list ($lng,$lat) = explode(" ", coordFix($_REQUEST["mapfilter_address"]));
        }
    }
    // �������� �� cookie ����� �����
    if (isset($_COOKIE['mapcenterlng'])) $mapcenterlng=$_COOKIE["mapcenterlng"];
    else $mapcenterlng=$lng;
    if (isset($_COOKIE['mapcenterlat'])) $mapcenterlat=$_COOKIE["mapcenterlat"];
    else $mapcenterlat=$lat;
    // ������� 
    if (isset($_COOKIE['mapsouthlat'])) $mapsouthlat=$_COOKIE["mapsouthlat"];
    else $mapsouthlat=$lat-1;
    if (isset($_COOKIE['mapnorthlat'])) $mapnorthlat=$_COOKIE["mapnorthlat"];
    else $mapnorthlat=$lat+1;
    if (isset($_COOKIE['mapwestlng'])) $mapwestlng=$_COOKIE["mapwestlng"];
    else $mapwestlng=$lng-1;
    if (isset($_COOKIE['mapeastlng'])) $mapeastlng=$_COOKIE["mapeastlng"];
    else $mapeastlng=$lng+1;
    // ������ ����� ����� ������ ���� �������� ����� ��� �������� ����������
    if((!$lat and !$lng) or empty(trim($_REQUEST["mapfilter_address"]))){
    } else {
        $mapcenterlng=$lng;
        $mapcenterlat=$lat;
    }
    // ����������� �� �������� API yandex
    if(empty($mapcenterlng) or empty($lng)){ // ����������� �� �������� API yandex
        $mapcenterlng=44.5169300;
        $mapcenterlat=48.7070730;
    }
    //
    echo '<center><div id="mapid" style="width: 100%; height: 600px; border: solid 1px darkblue; "></div></center>';
    if(isset($_REQUEST['areastat']) or isset($_REQUEST['clustareastat'])){ 
    // ���������� �� ������ ��������� "��������" ������� ��� ���������
        echo "<div id=\"under_map_div\"><table>".$areastat_html."</table></div>";
    } else {
        echo "<div id=\"under_map_div\"></div>";
    }
    // ���� �� ��������� ����� ���������� ��� ����� ������
    if(empty($lat) or empty($lng)){
        $lng=44.5169300;
        $lat=48.7070730;
    }
    ////////////////////////////////////////////////////////////////////////////
    // �������� �������� ����� � ������ �����
    if($_REQUEST["mapfilter_osm"]=='true'){
        echo "<script>
                mymap = L.map('mapid',{
                    fadeAnimation: false,
                    zoomAnimation: false, 
                    markerZoomAnimation: false,
                    wheelPxPerZoomLevel: 200,
                    wheelDebounceTime:300,
                    doubleClickZoom: false}).
                    setView([".$mapcenterlat.",".$mapcenterlng,"],".$mapscale.");
                L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href=\"http://osm.org/copyright\">OpenStreetMap</a> contributors',
                    maxZoom: 20,
                    minZoom: 7, 
                    zoomAnimation: false,
                    doubleClickZoom: false,
                    markerZoomAnimation: false,
                    fadeAnimation: false
                }).addTo(mymap);
        </script>";
    }else{
/*        echo "<script>
                mymap = L.map('mapid').setView([".$mapcenterlat.",".$mapcenterlng,"],".$mapscale.");
                L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    minZoom: 7, 
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    zoomAnimation: false,
                    doubleClickZoom: false,
                    markerZoomAnimation: false,
                    fadeAnimation: false
                }).addTo(mymap);
        </script>";*/
        
////////////////////////////////////////////////////////////////////////////////
// ��� yandex.map ���������� �������������� �������� ����� �� ������������ ��������� �� �������������, 
// ����� ����� �������� ����� �� ����������� : L.map('mapid',{crs: L.CRS.EPSG3395})
        echo "<script>
                mymap = L.map('mapid',{
                    fadeAnimation: false,
                    zoomAnimation: false, 
                    markerZoomAnimation: false,
                    wheelPxPerZoomLevel: 200,
                    wheelDebounceTime:300,
                    doubleClickZoom: false, 
                    crs: L.CRS.EPSG3395}).
                    setView([".$mapcenterlat.",".$mapcenterlng,"],".$mapscale.");
                //mymap.options.crs = L.CRS.EPSG3395;
                L.tileLayer('http://vec{s}.maps.yandex.net/tiles?l=map&v=4.55.2&z={z}&x={x}&y={y}&scale=2&lang=ru_RU', {
                    maxZoom: 20,
                    minZoom: 7, 
                    subdomains: ['01', '02', '03', '04'],
                    attribution: '<a http=\"yandex.ru\" target=\"_blank\">������</a>',
                    reuseTiles: true,
                    updateWhenIdle: false
                }).addTo(mymap);
        </script>";
    }
// ��. ����, ��� ���� ...    
//        .cluster_analysis {
//            width: 80px;
//            height: 4px;
            /*border-radius: 2px;*/
            /*border:solid 1px red;*/
            /*background-color: rgba(255, 200, 200, 0.5)*/;
//            background-color: deeppink;
//            text-align: center;
            /*vertical-align: middle;*/
//            font-size: 14px;
//        }
    ?>
    <style>
        .cluster_analysis {
            width: 64px;
            height: 64px;
            border-radius: 32px;
            /*border:solid 1px red;*/
            background-color: rgba(255, 200, 200, 0.67);
            text-align: center;
            /*vertical-align: middle;*/
            font-size: 14px;
        }
    </style>
    <script>
////////////////////////////////////////////////////////////////////////////////
// ��������� ���������� javascript ��� ����������� �������� �� �����
    try {
            L.control.scale({imperial: false}).addTo(mymap); // ���������� �������
            document.getElementById('mapid').style.cursor = 'crosshair'; // ��������� ������� ��� <div id="mapid"...
            var flagMarkGreen = L.icon({iconUrl: 'images/flag_mark_green.png', iconAnchor: [1,31] });
            L.Icon.Default.prototype.options.iconUrl = 'images/flag_mark_green.png';
            var divcon = L.divIcon({className: 'ps-div-icon'});
            var udicon = L.icon({iconUrl: 'images/ud2_s.png', iconAnchor: [11,12] });
            var spdporticon = L.icon({iconUrl: 'images/spdport.png', iconAnchor: [6,6] });
            var exist_4_green_icon = L.icon({iconUrl: 'images/exist_4_green.png', iconAnchor: [5,5] });
            var exist_4_yellow_icon = L.icon({iconUrl: 'images/exist_4_yellow.png', iconAnchor: [5,5] });
            var exist_3_green_icon = L.icon({iconUrl: 'images/exist_3_green.png', iconAnchor: [5,5] });
            var exist_3_yellow_icon = L.icon({iconUrl: 'images/exist_3_yellow.png', iconAnchor: [5,5] });
            var exist_all_icon = L.icon({iconUrl: 'images/exist_all.png', iconAnchor: [6,6] });
            var dist_box = L.icon({iconUrl: 'images/dist_box.png', iconAnchor: [12,8] });
            var square24icon = L.icon({iconUrl: 'images/square24.png', iconAnchor: [12,12] });
            var edit4icon = L.icon({iconUrl: 'images/edit4_small.png', iconAnchor: [12,12] });
            var greenicon = L.icon({iconUrl: 'images/ball_green_s.png', iconAnchor: [5,5]});
            var redicon = L.icon({iconUrl: 'images/ball_red_s.png', iconAnchor: [5,5]});
            var cyanicon = L.icon({iconUrl: 'images/ball_cyan_s.png', iconAnchor: [5,6]});
            var blueicon = L.icon({iconUrl: 'images/ball_blue_s.png', iconAnchor: [5,5]});
            var grayicon = L.icon({iconUrl: 'images/ball_gray_s.png', iconAnchor: [5,5]});
            var yellowicon = L.icon({iconUrl: 'images/ball_yellow_s.png', iconAnchor: [5,5]});
            var orangeicon = L.icon({iconUrl: 'images/ball_orange_s.png', iconAnchor: [5,5]});
            var clustericon = L.icon({iconUrl: 'images/nuclear32.png', iconAnchor: [16,16]});
            var projecticon = L.icon({iconUrl: 'images/project24.png', iconAnchor: [12,12]});
            //vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv//
            var markers = L.markerClusterGroup(); // ��� ������������� ��������
            mymap.addLayer(markers); // ��� ������������� ��������
            var markers2 = L.markerClusterGroup({
                    iconCreateFunction: function(cluster) {
                        var markers = cluster.getAllChildMarkers();
                        var redM=0,greenM=0,blueM=0;
                        for (var i = 0; i < markers.length; i++) {
                            if(markers[i].options.icon==greenicon) greenM++;
                            else if(markers[i].options.icon==redicon) redM++;
                            else blueM++;
                        }
                        //return L.divIcon({ html: '<div>'+cluster.getChildCount()+'</div>', className: 'cluster_analysis', iconSize: L.point(32, 24) });
                        var clustext='<div style=\' padding-top: 20px \'>';
                        if(redM>0)clustext+='<i style=\'color: red \'>'+redM+'</i> ';
                        if(greenM>0)clustext+='<i style=\'color: green \'>'+greenM+'</i> ';
                        if(blueM>0)clustext+='<i style=\'color: blue \'>'+blueM+'</i>';
                        clustext+='</div>';
                        return L.divIcon({ html: clustext, className: 'cluster_analysis', iconSize: L.point(64, 64) });
                    }
                }); // ��� ������������� ��������
            mymap.addLayer(markers2); // ��� ������������� ��������
            //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
            // ����� �������, ���������� �� ������
            var marker = L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>],{icon: flagMarkGreen}).addTo(mymap);
            marker.bindPopup("<b><?php echo iconv('UTF-8', 'CP1251', $formatted_address); ?></b>");
    <?php
////////////////////////////////////////////////////////////////////////////////
// ���������� ���
    //echo "// ".$ltc_as_map_center ."
    //    ";
    if($ltc_as_map_center!==false){
        // ��� ������ ��� �������� �������� ���� �� ����
        $rLTC=rSQL("SELECT * FROM ltc where lid=".$mapfilter_ltc_id);
        //echo "// ".$rLTC["lat"] ." ".$rLTC["lng"] ."
        //    ";
	if ($rLTC["lat"]<999 and $rLTC["lng"]<999) {
            echo "var cookiedate = new Date(new Date().getTime() + 4*3600*1000);
                document.cookie = \"mapcenterlng=".$rLTC["lng"] ."; path=/; expires=\" + cookiedate.toUTCString();
                document.cookie = \"mapcenterlat=".$rLTC["lat"] ."; path=/; expires=\" + cookiedate.toUTCString();
                ";
            echo "mymap.panTo(new L.LatLng(".$rLTC["lat"] .", ".$rLTC["lng"] ."));
                ";
        }
    }
////////////////////////////////////////////////////////////////////////////////
    require_once 'vlg_map_show_obj2.php';
////////////////////////////////////////////////////////////////////////////////
    // ��������� ���������� javascript
    echo "
    } catch (e) {
        alert('������ ' + e.name + ':' + e.message + ' ' + e.stack);
    }
    ";
////////////////////////////////////////////////////////////////////////////////
// ���������� �� ������� (����� �������/�������), �������� ���������� $latlngs
/*    function areastat($latlngs){
        $arPolygon = array();
        $row_map_obj_latlng=explode(" ",trim($latlngs));
        for($k=0;$k<count($row_map_obj_latlng);$k++){
            list($arPolygon[$k][0],$arPolygon[$k][1]) = explode(",",$row_map_obj_latlng[$k]);
        }
        //INCLUDE "classes.php";
        $polygon = new Polygon();
        $polygon->set_polygon($arPolygon);
        $cursor=new CSQL("SELECT 103 lay_type,psl.list_id,psl.latlng,psl.cs,psl.technology,psl.service
            FROM ps_list psl left join ps_list_dop psld on psl.list_id=psld.list_id
        union
        select lay_type,oid,concat(lat,':',lng),'','','' from com_obj            
        union            
        select type,id,latlng,'','','' from map_obj");
        //$cursor=new CSQL("SELECT psl.list_id,psl.latlng
        //    FROM ps_list psl left join ps_list_dop psld on psl.list_id=psld.list_id");
        $clustermembercount=0;
        $testclustercount=0;
        $nolatlngcount=0;
        $areastat=array();
        while ($cursor->assoc()) { // ������� ������ � �������� �����	
            if ($cursor->r["latlng"] == ''){
                $nolatlngcount++; // ���������� �� ��������
            } else {
                $testclustercount++;
                list ($ulat_x, $ulng_y) = explode(":", $cursor->r["latlng"]);
                $result_polygon_calc = $polygon->calc([
                    'x' => $ulat_x,
                    'y' => $ulng_y,
                ]);
                if ($result_polygon_calc == 1 or $result_polygon_calc == -1) {
                    if(isset($_REQUEST['areastat'])){ // ���������� �� ������
                        if(isset($areastat[$cursor->r["lay_type"]][$cursor->r["cs"]][$cursor->r["technology"]][$cursor->r["service"]]))
                            $areastat[$cursor->r["lay_type"]][$cursor->r["cs"]][$cursor->r["technology"]][$cursor->r["service"]]++;
                        else
                            $areastat[$cursor->r["lay_type"]][$cursor->r["cs"]][$cursor->r["technology"]][$cursor->r["service"]]=1;
                    } else if($cursor->r["lay_type"]==103 and isset($_REQUEST['delfromproject'])){ // ������� ������ ������ �� �������
                        //<a href='vlg_map.php?areaclick="+lmapobj_id+"&delfromproject="+lproject_id+"'>������� ������ ������ �� �������</a>
                        // !!! ��� ������� ��. ����  !!! ����� �.�. ����� �������:
                        // SQL("delete from ps_project_list where project_id= ".$_REQUEST['delfromproject'] ." and list_id = ". $cursor->r["list_id"])->commit();
                        SQL("delete from ps_project_list where list_id = ". $cursor->r["list_id"])->commit();
                    } else if($cursor->r["lay_type"]==103 and isset($_REQUEST['addtoproject'])){ // �������� ������ ������ � ������
                        SQL("delete from ps_project_list where list_id = ". $cursor->r["list_id"])->commit();
                        SQL("insert into ps_project_list values (NULL,'". $_REQUEST['addtoproject'] ."','" . $cursor->r["list_id"] ."',NULL,'".
                            $row_users["uid"] ."',0)")->commit();
                        $clustermembercount++;
                    }
                }
            }
        }
        $cursor->free();
        // ��� ����� �������� �������� ������ ��� ������ � �������� ���� ��������:
        //document.getElementById("info_edit_project").innerHTML="<i style='color: green;'>"+...+"</i>";
        //echo "������ � ������������ ������������ ".$nolatlngcount." ��������� ".$testclustercount." ����������� ".$clustermembercount."<br>";
        $areastat_html="";
        if(isset($_REQUEST['areastat'])){ // ���������� �� ������
            foreach($areastat as $key_lay_type => $value_lay_type) {
                foreach($value_lay_type as $key_cs => $value_cs) {
                    foreach($value_cs as $key_technology => $value_technology) {
                        foreach($value_technology as $key_service => $value_service) {
                            $areastat_html.="<tr><td>".$key_lay_type.
                                    "</td><td>".$key_cs.
                                    "</td><td>".$key_technology.
                                    "</td><td>".$key_service.
                                    "</td><td>".$value_service."</td></tr>";
                        }
                    }
                }
            }
        }
    }*/    
    ?>
    // ������������ ������������ �����
    function outskirtsExplore(lLatLng){
        var outskirtsStr="";
        if(document.getElementById('checkbox_outskirts').checked){
            SQL("select","select count(*) cnt from ps_list \
                where SUBSTRING_INDEX(latlng,':',1) between "+ (lLatLng.lat-0.0003) +" and "+ (lLatLng.lat+0.0003) +" and \
                    SUBSTR(latlng, LOCATE(':', latlng)+1) between "+ (lLatLng.lng-0.0004) +" and "+ (lLatLng.lng+0.0004) +" ");
            outskirtsStr+="<br>����� "+xmlHttp_responseText+" ������,";

            SQL("select","select count(*) cnt from com_obj \
                where lat between "+ (lLatLng.lat-0.0003) +" and "+ (lLatLng.lat+0.0003) +" and \
                    lng between "+ (lLatLng.lng-0.0004) +" and "+ (lLatLng.lng+0.0004) +" ");
            outskirtsStr+="<br>� "+xmlHttp_responseText+" �������� �����.";
        }
        return outskirtsStr;
    }
    // ���� �� ������������� ��������
    function onAbonentClick(e) {
        //var lArmId=this.options.arm_id;
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        var lPopUp = L.popup();
        var strContent="";
        var strSubContent="";
        lPopUp.setLatLng(e.latlng);
        //alert(this.options.list_id+" "+abonArr[this.options.list_id].length);
        if(this.options.mapobj_type==126){
            strContent="<div style='width:300px; height:300px; overflow-x:scroll; overflow-y:scroll; white-space:nowrap; \
                padding:2px; border:solid 1px darkblue; margin-bottom: 6px; '>"; 
            for(i=0;i<abonArr[this.options.list_id].length;i++){
                //alert(abonArr[this.options.list_id][i].aid+". "+abonArr[this.options.list_id][i].aname);
                if(abonArr[this.options.list_id][i].atype==1 && abonArr[this.options.list_id][i].abonservtech==1) strSubContent="xDSL<i style='color: green;'> ";
                else if(abonArr[this.options.list_id][i].atype==1 && abonArr[this.options.list_id][i].abonservtech==2) strSubContent="FTTx<i style='color: green;'> ";
                else if(abonArr[this.options.list_id][i].atype==2 && abonArr[this.options.list_id][i].abonservtech==2) strSubContent="FTTx<i style='color: orange;'> ";
                else strSubContent="xDSL<i style='color: orange;'> ";
                strContent=strContent+strSubContent+
                    abonArr[this.options.list_id][i].aname+"</i> "+
                    abonArr[this.options.list_id][i].device_address+
                    " ("+abonArr[this.options.list_id][i].aid+")<br>";
            }
            strContent=strContent+"</div>";
        } else {
            strContent=""+this.options.list_id+". ������ '"+abonArr[this.options.list_id].int_status_name+"."+
                "'<br>"+abonArr[this.options.list_id].aname+
                "<br>"+abonArr[this.options.list_id].device_address;
        }
        lPopUp.setContent(strContent);
        lPopUp.openOn(mymap);
    }
    // ���� �� ������
    function onCallClick(e) {
        //var lArmId=this.options.arm_id;
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        var lPopUp = L.popup();
        current_ps_list_dop=callArr[this.options.list_id].lid; // ��������� ���������� �������� ps_list_dop.lid
        lPopUp.setLatLng(e.latlng);
        //alert(this.options.list_id);
        var outskirtsStr=outskirtsExplore(e.latlng);
        // ������� ��������� ��������� ������
        if( �allChoose && �allChoose!=this ){
            mymap.removeLayer(callChooseMarker);
        }
        �allChoose=this;
        callChooseMarker=L.circleMarker(�allChoose.getLatLng(), {radius: 15, weight: 3, color: '#F00', fillOpacity: 0}).addTo(mymap);
        // ^^ ������� ��������� ��������� ������ ^^            
        if(callArr[this.options.list_id].project_id=='new') { // ������ �� ������ �� � ���� ������
            if(lSelectValue=="������� ������"){
                lPopUp.setContent("<a href=' ' onclick='onCallARM(); return false;'>"+callArr[this.options.list_id].arm_id+" ("+this.options.list_id+
                        ").</a> ������ '"+callArr[this.options.list_id].status+"."+
                    callArr[this.options.list_id].int_status_name+"'<br>"+
                    callArr[this.options.list_id].device_address+
                    outskirtsStr+
                    "<br><b style='color: #800;'>��� ���������� - �������� ������.</b>");
//                        "<br><form name='new_project' method='post' action='vlg_map.php?create_project=true&add_ps_id="+this.options.list_id+"'>"+
//                        "������������ �������:<input type='text' name='project_name' size='40' value=''>"+
//                        "�����������:<textarea name='comment' rows='4' cols='31'></textarea>"+
//                        "<input type='submit' value='������� ������ � �������� ������'></form>");
            } else {
                lPopUp.setContent("<a href=' ' onclick='onCallARM(); return false;'>"+callArr[this.options.list_id].arm_id+" ("+this.options.list_id+
                        ").</a> ������ '"+callArr[this.options.list_id].status+"."+
                    callArr[this.options.list_id].int_status_name+"'<br>"+callArr[this.options.list_id].device_address+outskirtsStr);
                    /*"<br><a href='vlg_map.php?edit_project=true&project_id="+ 
                    prId+"&add_ps_id="+this.options.list_id+"'>�������� � ������</a>");*/
//                        prId+"&add_ps_id="+this.options.list_id+"&for_my_job="+document.saddress.for_my_job.value+"'>�������� � ������</a>");
            }
        } else { // ������ ������ �� ������� ���� � ������ this.options.project_id
            lPopUp.setContent("<a href=' ' onclick='onCallARM(); return false;'>"+callArr[this.options.list_id].arm_id+" ("+this.options.list_id+
                ").</a> ������ '"+callArr[this.options.list_id].status+"."+
                    callArr[this.options.list_id].int_status_name+"'<br>"+callArr[this.options.list_id].device_address+
                "<br><b>������ "+callArr[this.options.list_id].project_id+". "+callArr[this.options.list_id].project_name+"</b>"+
                outskirtsStr +
                "<br><a href='vlg_map.php?action=true&edit_project=true&project_id="+callArr[this.options.list_id].project_id+
//                    "&proj_del_cid="+this.options.list_id+"&for_my_job="+document.saddress.for_my_job.value+"'>������� �� �������</a>");
                "&proj_del_cid="+this.options.list_id+"'>������� �� �������</a>");
        }
        lPopUp.openOn(mymap);
    }
    // ������ ���� �� ������ - ��������� (AJAX) � ������ ��� ��������
    function onCallRightClick(e) {
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        var lPopUp = L.popup();
        lPopUp.setLatLng(e.latlng);
        if(callArr[this.options.list_id].project_id=='new') { // ������ �� ������ �� � ���� ������
            if(lSelectValue=="������� ������"){
                lPopUp.setContent("��� ���������� ������ �������� ������");
                lPopUp.openOn(mymap);
            }else if(confirm("�������� ������ � ������?")){
                jSQL("update","INSERT ps_project_list values (NULL,'"+prId+"', '"+this.options.list_id+"', NULL, '"+userId+"', '0')");
                document.getElementById("info_edit_project").innerHTML="<i style='color: green;'>� ������ � "+prId+
                    " ��������� ������ � "+callArr[this.options.list_id].arm_id+"</i>";
                //this.setIcon(greenicon);
                lMarker=L.circleMarker(this.getLatLng(), {mapobj_type: 103, radius: 9, weight: 1, color: '#FF00FF', fillOpacity: 0}).addTo(mymap);
                callArr[this.options.list_id].project_id=prId;
                callArr[this.options.list_id].circleMarker=lMarker;
            }
        } else if(confirm("������� ������ �� �������?")){ // ������ ������ �� ������� ���� � ������ this.options.project_id
            // ������� �������� ��� �������� ����� ������ ������
            // ��� ����� �������� �������� ����� �������� �������� �� ������
            jSQL("update","DELETE from ps_project_list where list_id="+this.options.list_id+"");
            callArr[this.options.list_id].project_id='new';
            mymap.removeLayer(callArr[this.options.list_id].circleMarker);
            document.getElementById("info_edit_project").innerHTML="<i style='color: green;'>�� ������� � "+callArr[this.options.list_id].project_id+
                " ������� ������ � "+callArr[this.options.list_id].arm_id+"</i>";
        }
        return true;
    }
    // ���� �� ��
    function onUDClick(e) {
        //var lPopUp = L.popup();
        //lPopUp.setLatLng(e.latlng);
        // ������� ��������� ��������� ������
        if( UDChoose && UDChoose!=this ){
            mymap.removeLayer(UDChooseMarker);
        }
        UDChoose=this;
        UDChooseMarker=L.circleMarker(UDChoose.getLatLng(), {radius: 17, weight: 4, color: '#0F0', fillOpacity: 0}).addTo(mymap);
        // ^^ ������� ��������� ��������� ������ ^^            
        //lPopUp.openOn(mymap);
    }
    // ���� �� ��������
    function onClusterClick(e) {
        var lClusterId=this.options.cluster_id;
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        var lPopUp = L.popup();
        lPopUp.setLatLng(e.latlng);
        var lcontent="<div style=' color: \"red\" '> "+lClusterId+". "+this.options.cluster_name+" </div>";
        //lcontent+="<a href=' ' onclick='onDelFromPrjClusterClick("+this.options.cluster_id+"); return true;'>������� ������ �������� �� ���� ��������</a>";
        // ����� delfromproject ���� �� ������������
        // "return false;" - �������� ������������ ��������
        lcontent+="\
            <a href='' onclick=' if(confirm(\"������� ������ �������� �� ���� ��������?\"))\
                window.location = \"vlg_map.php?clusterdelfromprojects="+lClusterId+"&delfromproject="+prId+"\"; return false; '>\
            ������� ������ �������� �� ���� ��������(�����?)</a><br>\
            <a href='vlg_map.php?clustareaclick="+lClusterId+"&clustareastat="+prId+
                        "' style=' '>���������� �� ��������</a><br>\
            <a href='vlg_map.php?clustareaclick="+lClusterId+"&clustaddtoproject="+prId+
                        "' style=' '>�������� ������ (� ��������) �������� � ������(�� ������.)</a><br>\
            <a href='' onclick=' if(confirm(\"������� ������ (� ��������) �������� �� �������?\"))\
                window.location = \"vlg_map.php?clustareaclick="+lClusterId+"&clustdelfromproject="+prId+"\"; return false; '>������� ������ (� ��������) �������� �� �������(�� ������.)</a>";
        if(lSelectValue=="������� ������"){
        } else {
            //lcontent+="<br><a href=' ' onclick='onAddToPrjClusterClick("+this.options.cluster_id+"); return true;'>�������� ������ �������� � ������</a>";
            lcontent+="<br><a href='vlg_map.php?clusterclick="+this.options.cluster_id+"&addtoproject="+prId+"'>�������� ������ �������� � ������(�����?)</a>";
        }
        lPopUp.setContent(lcontent);
        lPopUp.openOn(mymap);
    }
    // ���� �� ��������
    function onClusterRightClick(e) {
        var lClusterId=this.options.cluster_id;
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        var lPopUp = L.popup();
        lPopUp.setLatLng(e.latlng);
        var lcontent="<div style=' color: \"red\" '> "+lClusterId+". "+this.options.cluster_name+" </div>";
        //lcontent+="<a href=' ' onclick='onDelFromPrjClusterClick("+this.options.cluster_id+"); return true;'>������� ������ �������� �� ���� ��������</a>";
        // ����� delfromproject ���� �� ������������
        // "return false;" - �������� ������������ ��������
        lcontent+="\
            <a href='vlg_map.php?clustareaclick="+lClusterId+"&clustareastat="+prId+
                        "' style=' '>���������� �� ��������</a>\
            <br><a href='vlg_report.php?func=1<?php echo "&ltc_id=".$mapfilter_ltc_id; ?>&clustar_id="+lClusterId+
                    "' style=' '>����� �� ��������</a>\
            ";
        if(lSelectValue=="������� ������"){
        } else {
            //lcontent+="<br><a href=' ' onclick='onAddToPrjClusterClick("+this.options.cluster_id+"); return true;'>�������� ������ �������� � ������</a>";
            lcontent+="<br><a href='vlg_map.php?clusterclick="+this.options.cluster_id+"&addtoproject="+prId+"'>�������� ������ �������� � ������(�����?)</a>";
        }
        lPopUp.setContent(lcontent);
        lPopUp.openOn(mymap);
    }
    // ���� �� �����
    function onMapClick(event) {
        var popup = L.popup();
        var rulerStr="";
        var outskirtsStr=outskirtsExplore(event.latlng);
        var lLatLng=event.latlng;
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var popupContent='';
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������

        //delete xmlHttp.psCallBackFunction; 

/*        var numlayers = 0;
        mymap.eachLayer(function(layer) {
            if( layer instanceof L.TileLayer ){
                //layers.push(layer);
                dist=lLatLng.distanceTo(layer.latlng);
                if(dist<1000) alert(layer.latlng);
            }
        });
*/
                
        /*if(ruler){ // ������� ����� �������
            //var points=ruler.getPoints();
            var points=ruler.getLatLngs();
            //ruler.setReadOnly(true);                    
            var dist=0;
            for(pin=0;pin<(points.length-1);pin++){
                //dist+=points[pin].getLatLng().distanceTo(points[pin+1].getLatLng());
                dist+=points[pin].distanceTo(points[pin+1]);
            }
            rulerStr="<br>����� ������� "+dist.toFixed(1)+" � ";
        }*/
        popupContent="���������� ("+lLatLng.lat+","+lLatLng.lng+")"+ outskirtsStr +rulerStr+
                "<br><button name='addRulerButton' onclick='onAddRulerButtonClick("+event.latlng.lat+","+event.latlng.lng+")'>������� ��� ������� ��� �������</button >";
        if( �allChoose ){
            popupContent+="<br><button name='moveMarkerButton' onclick='onMoveMarkerButtonClick("+event.latlng.lat+","+event.latlng.lng+")'>��������� ������ ����</button >";
        };
        if( UDChoose ){
            popupContent+="<br><button name='moveMarkerButton' onclick='onMoveUDButtonClick("+event.latlng.lat+","+event.latlng.lng+")'>��������� �� ����</button >";
        }
        if(pointBuffer){
            popupContent+="<br><button name='movePointButton' onclick='onMovePointButtonClick("+event.latlng.lat+","+event.latlng.lng+")'>��������� ������������ ����</button >";
        }
        if(lSelectValue!="������� ������"){
            popupContent+="<br><button name='addPointButton' onclick='onAddPointButtonClick("+
                event.latlng.lat+","+event.latlng.lng+","+prId+")'>�������� � ������ ������������</button >";
            if(lineBuffer){
                //popupContent+="<br><button name='saveLineButton' onclick='onSaveLineButtonClick()'>��������� �����</button >";                    
            } else {
                popupContent+="<br><button name='addLineButton' onclick='onAddLineButtonClick("+
                event.latlng.lat+","+event.latlng.lng+","+prId+")'>�������� � ������ �����</button >";
            }
            if(areaBuffer){
            } else {
                popupContent+="<br><button name='addAreaButton' onclick='onAddAreaButtonClick("+
                    event.latlng.lat+","+event.latlng.lng+","+prId+")' >�������� � ������ �������</button >";
            }
        }
        popup.setLatLng(lLatLng).setContent(popupContent).openOn(mymap);
        
        return true;
    }
    mymap.on('click', onMapClick);
////////////////////////////////////////////////////////////////////////////////
// ������ � ���������
    // ���� �� �������
    function onProjectClick(e) {
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        var lPopUp = L.popup();
        lPopUp.setLatLng(e.latlng);
        var lcontent="<div style=' color: \"red\" '><a href='vlg_project.php?action=edit_project&project_id="+this.options.project_id+"'>"+
                this.options.project_id+". "+this.options.project_name+"</a></div>";
        lPopUp.setContent(lcontent);
        lPopUp.openOn(mymap);
    }
    // !!! �� ������������ !!! �������� ������ �������� � ������
    function onAddToPrjClusterClick(cluster_id) {
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        jSQL("update","delete from ps_project_list where list_id in (select list_id from ps_list_dop where claster_id="+cluster_id+")");
        jSQL("update","insert into ps_project_list (select NULL,'"+prId+"',list_id,NULL,'"+userId+"',0 from ps_list_dop where claster_id="+cluster_id+")");        
    }
    // !!! �� ������������ !!! ������� ������ �������� �� ���� ��������
    function onDelFromPrjClusterClick_Back() {
        delete xmlHttp.psCallBackFunction; 
        alert(xmlHttp.responseText);
        //window.location="http://"+location.hostname+":"+location.port+location.pathname+"vlg_map.php";
    }
    // !!! �� ������������ !!! 
    function onDelFromPrjClusterClick(cluster_id) {
        xmlHttp.psCallBackFunction="onDelFromPrjClusterClick_Back";
        var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        jSQL("update","delete from ps_project_list where list_id in (select list_id from ps_list_dop where claster_id="+cluster_id+")");
        //window.location="http://"+location.hostname+":"+location.port+location.pathname+"vlg_map.php";
    }
    // click �� ������� ������� (������� mapobj) 
    function onProjectObjectClick_Back(sEvalRes){
        delete xmlHttp.psCallBackFunction; 
        //alert(sEvalRes);
        var evalRes=eval(sEvalRes.replace(/\s/g,' ')); // \s - ������������� ������� "�������". ������������ /[ \f\n\r\t\v]/. 
        if(evalRes)
            popupBuffer.setContent(evalRes).openOn(mymap);
        //else
        //    popupBuffer.setContent('������ �� ���������').openOn(mymap);
    }
    function onProjectObjectClick(event) {
        pointBuffer=false;
        lineBuffer=false; //?!?
        areaBuffer=false; //?!?
        document.getElementById('under_map_div').innerHTML="";
        delete xmlHttp.psCallBackFunction; 
        popupBuffer = L.popup();
        var lEvLatLng=event.latlng;
        //var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        //var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        ////////////////////////////////////////////////////////////////////////
        switch(this.options.mapobj_type){
        case 1: // �������� ������
            //alert(this.options.mapobj_id+" "+this.options.project_id);
            pointBuffer=this;
            xmlHttp.psCallBackFunction="onProjectObjectClick_Back";
            jSQL("select","SELECT concat('',REPLACE(concat('<b>',eq.name,' / ',eq.coment,'</b><br> \
                <span style=''color: #800;''>',price,' ���.</a> �� ',ed),'\"',''''), ' ', mo.cosize,'(',mo.manual_size,') ��') \
                FROM map_obj mo \
                left join ps_equip eq on mo.type=1 and mo.subtype=eq.id \
                where mo.id="+this.options.mapobj_id);
            popupBuffer.setLatLng(lEvLatLng).setContent("������ ���");  
            document.getElementById('under_map_div').innerHTML=
                "<button name='typePointButton' onclick='onTypePointButtonClick()'>��� ������������</button >\
                <button name='deletePointButton' onclick='onDeletePointButtonClick()'>������� ������������</button >\
                <form name='projectobject' action=\" \" onSubmit='onProjectObjectSubmit(1,pointBuffer,"+this.options.mapobj_id+
                        ",projectobject.textprojectobject.value,projectobject.colorprojectobject.value,projectobject.manualsizeobject.value);return false; '>\
                    ������������<input type=\"text\" name=\"textprojectobject\" value=\""+this.options.oname+"\">\
                    ����������<input type=\"text\" name=\"manualsizeobject\" value=\""+this.options.manual_size+"\">\
                    <input type=\"color\" name=\"colorprojectobject\" value=\""+rGBAToHex(this.options.mocolor)+"\">\
                    <input type=\"submit\" value=\"��������� ���������\">\
                </form>"; 
        break;
        ////////////////////////////////////////////////////////////////////////
        case 2: // ����� �����
            //var points=this.options.ppolyline.getLatLngs().trim().split(" ");
            var points=this.options.ppolyline.getLatLngs();

            var lmapobj_id=this.options.mapobj_id;
            //alert(lmapobj_id+"   "+this.options.mapobj_id);
            var lproject_id=this.options.project_id;
            /*var llatlng=[];
            for(var i=0;i<points.length;i++){
                llatlng.push(points[i].split(","));
            }*/
            var llatlng=[];
            for(var i=0;i<points.length;i++){
                llatlng.push([points[i].lat,points[i].lng]);
            }
            lineBuffer = L.Polyline.Plotter(llatlng,{
                mapobj_id: lmapobj_id, 
                weight: 1, 
                color: this.options.ppolyline.options.mocolor, 
                project_id: lproject_id,
                mocolor: this.options.ppolyline.options.mocolor,
                oname: this.options.ppolyline.options.oname,
                cssid: this.options.ppolyline.options.cssid
                }).addTo(mymap);
            mymap.removeLayer(this.options.ppolyline);
            mymap.removeLayer(this);

            xmlHttp.psCallBackFunction="onProjectObjectClick_Back";
            jSQL("select","SELECT concat('',REPLACE(concat('<b>',lin.name,'</b><br> \
                <span style=''color: #800;''>',price,' ���.</a> �� ',ed),'\"',''''), ' ', mo.cosize,'(',mo.manual_size,') ��') \
                FROM map_obj mo \
                left join ps_smet_calc lin on mo.type=2 and mo.subtype=lin.id \
                where mo.id="+this.options.mapobj_id);
            popupBuffer.setLatLng(lEvLatLng).setContent("����� �����");            
            document.getElementById('under_map_div').innerHTML=
                "<!--button name='saveLineButton' onclick='onSaveLineButtonClick()'>��������� �����</button -->\
                <button name='typeLineButton' onclick='onTypeLineButtonClick()'>��� �����</button >\
                <button name='deleteLineButton' onclick='onDeleteLineButtonClick()'>������� �����</button >\
                <form name='projectobject' action=\" \" onSubmit='onProjectObjectSubmit(2,lineBuffer,"+this.options.mapobj_id+
                        ",projectobject.textprojectobject.value,projectobject.colorprojectobject.value,projectobject.manualsizeobject.value);return false; '>\
                    ������������<input type=\"text\" name=\"textprojectobject\" value=\""+this.options.ppolyline.options.oname+"\">\
                    �����<input type=\"text\" name=\"manualsizeobject\" value=\""+this.options.ppolyline.options.manual_size+"\">\
                    <input type=\"color\" name=\"colorprojectobject\" value=\""+rGBAToHex(this.options.ppolyline.options.mocolor)+"\">\
                    <input type=\"submit\" value=\"��������� ���������\">\
                </form>";
        break;
        ////////////////////////////////////////////////////////////////////////
        case 3: // ��������� ������ �����
            var points=this.options.ppolyline.getLatLngs()[0];
//points.forEach(function(item, i, arr) {  alert(i + ": " + item); });
            var lmapobj_id=this.options.mapobj_id;
            var lproject_id=this.options.project_id;
            var llatlng=[];
            for(var i=0;i<points.length;i++){
                llatlng.push([points[i].lat,points[i].lng]);
            }
            areaBuffer = L.Polyline.Plotter(llatlng,{
                mapobj_id: lmapobj_id, 
                weight: 1, 
                color: this.options.ppolyline.options.mocolor, 
                project_id: lproject_id,
                mocolor: this.options.ppolyline.options.mocolor,
                oname: this.options.ppolyline.options.oname,
                cssid: this.options.ppolyline.options.cssid
                }).addTo(mymap);
            mymap.removeLayer(this.options.ppolyline);
            mymap.removeLayer(this);
            xmlHttp.psCallBackFunction="onProjectObjectClick_Back";
            jSQL("select","SELECT concat('',REPLACE(concat('<b>',lin.name,'</b><br> \
                <span style=''color: #800;''>',price,' ���.</a> �� ',ed),'\"',''''), ' ', mo.cosize,'(',mo.manual_size,') ��') \
                FROM map_obj mo \
                left join ps_smet_calc lin on mo.type=3 and mo.subtype=lin.id \
                where mo.id="+lmapobj_id);
            popupBuffer.setLatLng(lEvLatLng).setContent("�������");       
            document.getElementById('under_map_div').innerHTML=
                "<button name='typeAreaButton' onclick='onTypeAreaButtonClick()'>��� �������</button >\
                <a href='vlg_map.php?areaclick="+lmapobj_id+"&areastat="+lproject_id+
                            "' style=' padding:2px; border:solid 1px darkblue; '>���������� �� ������</a>\
                <a href='vlg_report.php?func=1<?php echo "&ltc_id=".$mapfilter_ltc_id; ?>&area_id="+lmapobj_id+
                            "' style=' padding:2px; border:solid 1px darkblue; '>����� �� ������</a>\
                <a href='vlg_map.php?areaclick="+lmapobj_id+"&addtoproject="+lproject_id+
                            "' style=' padding:2px; border:solid 1px darkblue; '>�������� ������ ������ � ������</a>\
                <a href='vlg_map.php?areaclick="+lmapobj_id+"&delfromproject="+lproject_id+
                            "' style=' padding:2px; border:solid 1px darkblue; '>������� ������ ������ �� �������</a>\
                <button name='deleteAreaButton' onclick='onDeleteAreaButtonClick()'>������� �������</button >\
                <form name='projectobject' action=\" \" onSubmit='onProjectObjectSubmit(3,areaBuffer,"+lmapobj_id+
                        ",projectobject.textprojectobject.value,projectobject.colorprojectobject.value,projectobject.manualsizeobject.value);return false; '>\
                    ������������<input type=\"text\" name=\"textprojectobject\" value=\""+this.options.ppolyline.options.oname+"\">\
                    �������<input type=\"text\" name=\"manualsizeobject\" value=\""+this.options.ppolyline.options.manual_size+"\">\
                    <input type=\"color\" name=\"colorprojectobject\" value=\""+rGBAToHex(this.options.ppolyline.options.mocolor)+"\">\
                    <input type=\"submit\" value=\"��������� ���������\">\
                </form>";
        break;
        }
    };
////////////////////////////////////////////////////////////////////////////////
    function onProjectObjectRightClick(event) {
        pointBuffer=false;
        lineBuffer=false; //?!?
        areaBuffer=false; //?!?
        document.getElementById('under_map_div').innerHTML="";
        //delete xmlHttp.psCallBackFunction; 
        //popupBuffer = L.popup();
        var lEvLatLng=event.latlng;
        //var lSelectValue=document.getElementsByName("mapfilter_project")[0].value;
        //var prId=lSelectValue.split(' ')[0]; // ������������� ������� �� �������
        ////////////////////////////////////////////////////////////////////////
        switch(this.options.mapobj_type){
        case 1: // �������� ������
        break;
        ////////////////////////////////////////////////////////////////////////
        case 2: // ����� �����
        break;
        ////////////////////////////////////////////////////////////////////////
        case 3: // ��������� ������ �����
            var points=this.options.ppolyline.getLatLngs()[0];
            var lmapobj_id=this.options.mapobj_id;
            var lproject_id=this.options.project_id;
            var llatlng=[];
            for(var i=0;i<points.length;i++){
                llatlng.push([points[i].lat,points[i].lng]);
            }
            var lPopUp = L.popup();
            lPopUp.setLatLng(event.latlng);
            var lcontent="<div style=' color: \"red\" '> </div>";
            lcontent+="\
                    <a href='vlg_map.php?areaclick="+lmapobj_id+"&areastat="+lproject_id+
                                "' style=' '>���������� �� ������</a>\
                    <br><a href='vlg_report.php?func=1<?php echo "&ltc_id=".$mapfilter_ltc_id; ?>&area_id="+lmapobj_id+
                                "' style=' '>����� �� ������</a>\
                ";
            lPopUp.setContent(lcontent);
            lPopUp.openOn(mymap);
        break;
        }
    };    
////////////////////////////////////////////////////////////////////////////////    
    function onProjectObjectSubmit(oType,objBuffer,mapobj_id,text,color,manual_size) {
        objBuffer.options.mocolor=hexToRGBA(color, 1);
        objBuffer.options.oname=text;
        switch(oType){
        case 3: // �������
            onSaveAreaButtonClick(objBuffer,mapobj_id,text,color,manual_size);
        break;
        case 2: // �����
            onSaveLineButtonClick(objBuffer,mapobj_id,text,color,manual_size);
        break;
        case 1: // ������������
            jSQL("update","update map_obj set color='"+objBuffer.options.mocolor+"',oname='"+text+"',manual_size='"+manual_size+"' where id="+mapobj_id+"");
        break;
        }
        document.getElementById('under_map_div').innerHTML="";
    }
// ^^^ ������ � ��������� ^^^
////////////////////////////////////////////////////////////////////////////////
// ������ � ������������� �����
    // ����������/�������� ������������ � ������
    function onAddPointButtonClick_Back() {
        delete xmlHttp.psCallBackFunction; 
        //alert(xmlHttp.responseText);
        eval(xmlHttp.responseText);
        pointBuffer.options.mapobj_id=xmlHttp_responseText;
        document.getElementById('under_map_div').innerHTML=
            "<button name='typePointButton' onclick='onTypePointButtonClick()'>��� ������������</button >\
            <button name='deletePointButton' onclick='onDeletePointButtonClick()'>������� ������������</button >\
            <form name='projectobject' action=\" \" onSubmit='onProjectObjectSubmit(1,pointBuffer,"+pointBuffer.options.mapobj_id+
                    ",projectobject.textprojectobject.value,projectobject.colorprojectobject.value,projectobject.manualsizeobject.value);return false; '>\
                ������������<input type=\"text\" name=\"textprojectobject\" value=\"\">\
                ����������<input type=\"text\" name=\"manualsizeobject\" value=\"1.0\">\
                <input type=\"color\" name=\"colorprojectobject\" value=\"#FF0000\">\
                <input type=\"submit\" value=\"��������� ���������\">\
            </form>"; 
    };        
    function onAddPointButtonClick(latC,lngC,prId) {
        xmlHttp.psCallBackFunction="onAddPointButtonClick_Back";
        //alert("INSERT INTO map_obj(id,oname,type,latlng,subtype,uid,dateedit,comment,project_id,cosize,morphe,manual_size)\
        //    VALUES (NULL,'������ ���',1,'"+latC+","+lngC+"',NULL,'"+userId+"',NULL,'','"+prId+"',1,1,1.0)");
        jSQL("insert","INSERT INTO map_obj(id,oname,type,latlng,subtype,uid,dateedit,comment,project_id,cosize,morphe,manual_size)\
            VALUES (NULL,'',1,'"+latC+","+lngC+"',NULL,'"+userId+"',NULL,'','"+prId+"',1,1,1.0)");
//        pointBuffer=L.marker([latC,lngC], {mapobj_id: -1, icon: square24icon, project_id: prId}).addTo(mymap);
        lIcon=new L.DivIcon({className: 'rounddivicon8', 
            html: '<div><nobr style="color: black; ">1(1) �� ������ ���</nobr></div>', 
            iconSize: new L.Point(16, 16) });
        pointBuffer=L.marker([latC,lngC], {
            mapobj_type: 1,
            mapobj_id: -1, 
            icon: lIcon,
            project_id: prId,
            mocolor: 'black',
            manual_size: 1,
            oname: '������ ���'
            }).addTo(mymap);
        pointBuffer.on('click', onProjectObjectClick); 
    };
    //
    function onTypePointButtonClick_Back(sEvalRes){
        delete xmlHttp.psCallBackFunction; 
        var info_window_message="<b>��� ����� ����� </b><hr>";
        //alert(sEvalRes);
        var evalRes=eval(sEvalRes.replace(/\s/g,' ')); // \s - ������������� ������� "�������". ������������ /[ \f\n\r\t\v]/. 
        for(var i=0;i<evalRes.length;i++){
            info_window_message+=evalRes[i]+'<br>';
        }
        document.getElementById('info_window_message').innerHTML=info_window_message;
        document.getElementById("info_window_darkening").style.display = 'block';
    }
    function onTypePointButtonClick() {
        xmlHttp.psCallBackFunction="onTypePointButtonClick_Back";
        //alert("SELECT concat('\"',REPLACE(concat('<b>',name,' / ',coment,'</b><br>\
        //    <a href='''' onclick=''onSetTypeLine(',id,'); return false; '' style=''color: #800;''>',price,' ���.</a> �� ',ed),'\"',''''),'\"') sc \
        //        FROM ps_equip order by name");
        jSQL("multiselect","SELECT concat('\"',REPLACE(concat('<b>',name,' / ',coment,'</b><br>\
            <a href='''' onclick=''onSetTypePoint(',id,'); return false; '' style=''color: #800;''>',price,' ���.</a> �� ',ed),'\"',''''),'\"') sc \
                FROM ps_equip where old=0 order by name");
        return false;
    }
    function onSetTypePoint(smr_id){
        //alert("update map_obj set subtype="+smr_id+" where id="+lineBuffer.options.mapobj_id);
        jSQL("update","update map_obj set subtype="+smr_id+" where id="+pointBuffer.options.mapobj_id);
        document.getElementById("info_window_darkening").style.display = 'none';
    }
    function onDeletePointButtonClick(){
        //alert("delete from map_obj where id="+lineBuffer.options.mapobj_id);
        jSQL("update","delete from map_obj where id="+pointBuffer.options.mapobj_id);
        mymap.removeLayer(pointBuffer);
        pointBuffer=false;
        document.getElementById('under_map_div').innerHTML="";
    }
// ^^ ������ � ������������� ����� ^^
////////////////////////////////////////////////////////////////////////////////
// ������ � ������� �����
    // ����������/�������� ����� � ������
    function onAddLineButtonClick_Back() {
        delete xmlHttp.psCallBackFunction; 
        //alert(xmlHttp.responseText);
        eval(xmlHttp.responseText);
        lineBuffer.options.mapobj_id=xmlHttp_responseText;
        //mapobjChoose=lineBuffer.options.mapobj_id;
        document.getElementById('under_map_div').innerHTML=
            "<!--button name='saveLineButton' onclick='onSaveLineButtonClick()'>��������� �����</button -->\
            <button name='typeLineButton' onclick='onTypeLineButtonClick()'>��� �����</button >\
            <button name='deleteLineButton' onclick='onDeleteLineButtonClick()'>������� �����</button >\
            <form name='projectobject' action=\" \" onSubmit='onProjectObjectSubmit(2,lineBuffer,"+lineBuffer.options.mapobj_id+
                    ",projectobject.textprojectobject.value,projectobject.colorprojectobject.value,projectobject.manualsizeobject.value);return false; '>\
                ������������<input type=\"text\" name=\"textprojectobject\" value=\"\">\
                �����<input type=\"text\" name=\"manualsizeobject\" value=\"0.0\">\
                <input type=\"color\" name=\"colorprojectobject\" value=\"#FF0000\">\
                <input type=\"submit\" value=\"��������� ���������\">\
            </form>";
    };
    function onAddLineButtonClick(latC,lngC,prId) {
        xmlHttp.psCallBackFunction="onAddLineButtonClick_Back";
        lineBuffer = L.Polyline.Plotter([[latC,lngC],[latC,lngC+0.002]],{
            mapobj_id: -1, 
            weight: 1, 
            color: 'black',
            dashArray: '15,6,2,6', 
            mocolor: 'rgba(0,0,0,1.0)',
            oname: '',
            manual_size: '0.0',
            cssid: 2
            }).addTo(mymap);
        //alert("INSERT INTO map_obj(id,oname,type,latlng,subtype,uid,dateedit,comment,project_id,morphe,manual_size)\
        //    VALUES (NULL,'����� �����',2,'"+latC+","+lngC+" "+latC+","+(lngC+0.002)+"',NULL,'"+userId+"',NULL,'','"+prId+"',2,0)");
        jSQL("insert","INSERT INTO map_obj(id,oname,type,latlng,subtype,uid,dateedit,comment,project_id,morphe,manual_size)\
            VALUES (NULL,'����� �����',2,'"+latC+","+lngC+" "+latC+","+(lngC+0.002)+"',NULL,'"+userId+"',NULL,'','"+prId+"',2,0)");

        lineBuffer.on('click', onProjectObjectClick); 
    };
    //
/*    function onSaveLineButtonClick_old() {
        var dist=0;
        var lLatLngs='';
        if(lineBuffer){
            var points=lineBuffer.getLatLngs();
            //lineBuffer.setReadOnly(true);                    
            var pin=0;
            for(pin=0;pin<(points.length-1);pin++){
                dist+=points[pin].distanceTo(points[pin+1]);
                lLatLngs+=points[pin].lat.toString()+","+points[pin].lng.toString()+" ";
            }
            lLatLngs+=points[pin].lat.toString()+","+points[pin].lng.toString();
            //alert("update map_obj set latlng='"+latLngs+"' where id="+lineBuffer.options.mapobj_id+"");
            jSQL("update","update map_obj set latlng='"+lLatLngs.trim()+"',cosize="+dist/1000.0+" where id="+lineBuffer.options.mapobj_id+"");
            lLatLngs=[];
            for(pin=0;pin<points.length;pin++){
                lLatLngs.push([points[pin].lat,points[pin].lng]);
            }
            // !!! ���������� new ����� ������ "callinithooks is not a function" !!!
            lPolyline= new L.Polyline(lLatLngs,{
                mapobj_id: lineBuffer.options.mapobj_id, 
                dashArray: '15,6,2,6', 
                weight: 1, 
                color: lineBuffer.options.mocolor, 
                project_id: lineBuffer.options.project_id,
                mocolor: lineBuffer.options.mocolor,
                oname: lineBuffer.options.oname,
                manual_size: lineBuffer.options.manual_size,
                cssid: lineBuffer.options.cssid
                }).addTo(mymap);
            lIcon=new L.DivIcon({
                className: lineBuffer.options.cssid, 
                html: "<div><nobr style=\"color: "+lineBuffer.options.mocolor+"; \">"+lineBuffer.options.mapobj_id+
                        "&nbsp;&nbsp;&nbsp;"+dist/1000.0+" �� "+lineBuffer.options.oname+"</nobr></div>", 
                iconSize: new L.Point(14, 14) 
                });
            L.marker([(points[0].lat+points[1].lat)/2,(points[0].lng+points[1].lng)/2], 
                {ppolyline: lPolyline, mapobj_id: lineBuffer.options.mapobj_id, icon: lIcon, project_id: lineBuffer.options.project_id}).addTo(mymap)
                .on('click', onProjectObjectClick);
            mymap.removeLayer(lineBuffer);
            lineBuffer=false;
            document.getElementById('under_map_div').innerHTML="";
        }
    };*/
    //
    function onSaveLineButtonClick(objBuffer,mapobj_id,text,color,manual_size) {
        // objBuffer �� �� �����, ��� � lineBuffer
        var dist=0;
        var lLatLngs='';
        if(lineBuffer){
            var points=lineBuffer.getLatLngs();
            //lineBuffer.setReadOnly(true);                    
            var pin=0;
            for(pin=0;pin<(points.length-1);pin++){
                dist+=points[pin].distanceTo(points[pin+1]);
                lLatLngs+=points[pin].lat.toString()+","+points[pin].lng.toString()+" ";
            }
            lLatLngs+=points[pin].lat.toString()+","+points[pin].lng.toString();
            jSQL("update","update map_obj set latlng='"+lLatLngs.trim()+"',cosize="+dist/1000.0+
                    ", color='"+objBuffer.options.mocolor+"',oname='"+text+"',manual_size="+manual_size+" where id="+mapobj_id+"");
            lLatLngs=[];
            for(pin=0;pin<points.length;pin++){
                lLatLngs.push([points[pin].lat,points[pin].lng]);
            }
            // !!! ���������� new ����� ������ "callinithooks is not a function" !!!
            lPolyline= new L.Polyline(lLatLngs,{
                mapobj_id: lineBuffer.options.mapobj_id, 
                dashArray: '15,6,2,6', 
                weight: 1, 
                color: lineBuffer.options.mocolor, 
                project_id: lineBuffer.options.project_id,
                mocolor: lineBuffer.options.mocolor,
                oname: lineBuffer.options.oname,
                manual_size: manual_size,//lineBuffer.options.manual_size,
                cssid: lineBuffer.options.cssid
                }).addTo(mymap);
            lIcon=new L.DivIcon({
                className: lineBuffer.options.cssid, 
                html: "<div><nobr style=\"color: "+lineBuffer.options.mocolor+"; \">"+
                    lineBuffer.options.mapobj_id+/*"&nbsp;&nbsp;&nbsp;"+dist/1000.0+" ��"*/"&nbsp;"+lineBuffer.options.oname+"</nobr></div>", 
                iconSize: new L.Point(14, 14) 
                });
            L.marker([(points[0].lat+points[1].lat)/2,(points[0].lng+points[1].lng)/2], 
                {mapobj_type: 2, ppolyline: lPolyline, mapobj_id: lineBuffer.options.mapobj_id, icon: lIcon, project_id: lineBuffer.options.project_id}).addTo(mymap)
                .on('click', onProjectObjectClick);
            mymap.removeLayer(lineBuffer);
            lineBuffer=false;
            document.getElementById('under_map_div').innerHTML="";
        }
    };
    //
    function onTypeLineButtonClick_Back(sEvalRes){
        delete xmlHttp.psCallBackFunction; 
        var info_window_message="<b>��� ����� ����� </b><hr>";
        //alert(sEvalRes);

        var evalRes=eval(sEvalRes);
        for(var i=0;i<evalRes.length;i++){
            info_window_message+=evalRes[i]+'<br>';
        }
        document.getElementById('info_window_message').innerHTML=info_window_message;
        document.getElementById("info_window_darkening").style.display = 'block';
    }
    function onTypeLineButtonClick() {
        xmlHttp.psCallBackFunction="onTypeLineButtonClick_Back";
        jSQL("multiselect","SELECT concat('\"',REPLACE(concat('<b>',name,'</b><br>\
            <a href='''' onclick=''onSetTypeLine(',id,'); return false; '' style=''color: #800;''>',price,' ���.</a> �� ',ed),'\"',''''),'\"') sc \
                FROM ps_smet_calc order by name");
        return false;
    }
    function onSetTypeLine(smr_id){
        //alert("update map_obj set subtype="+smr_id+" where id="+lineBuffer.options.mapobj_id);
        jSQL("update","update map_obj set subtype="+smr_id+" where id="+lineBuffer.options.mapobj_id);
        document.getElementById("info_window_darkening").style.display = 'none';
    }
    function onDeleteLineButtonClick(){
        //alert("delete from map_obj where id="+lineBuffer.options.mapobj_id);
        jSQL("update","delete from map_obj where id="+lineBuffer.options.mapobj_id);
        mymap.removeLayer(lineBuffer);
        lineBuffer=false;
        document.getElementById('under_map_div').innerHTML="";
    }
// ^^ ������ � ������� ����� ^^
////////////////////////////////////////////////////////////////////////////////
// ������ � ��������� �������
    // ����������/�������� ����� � ������
    function onAddAreaButtonClick_Back() {
        delete xmlHttp.psCallBackFunction; 
        //alert(xmlHttp.responseText);
        eval(xmlHttp.responseText);
        areaBuffer.options.mapobj_id=xmlHttp_responseText;
        //mapobjChoose=areaBuffer.options.mapobj_id;
        document.getElementById('under_map_div').innerHTML=
            "<button name='typeAreaButton' onclick='onTypeAreaButtonClick()'>��� �������</button >\
            <button name='deleteAreaButton' onclick='onDeleteAreaButtonClick()'>������� �������</button >\
            <form name='projectobject' action=\" \" onSubmit='onProjectObjectSubmit(3,areaBuffer,"+areaBuffer.options.mapobj_id+
                    ",projectobject.textprojectobject.value,projectobject.colorprojectobject.value,projectobject.manualsizeobject.value);return false; '>\
                ������������<input type=\"text\" name=\"textprojectobject\" value=\"\">\
                �������<input type=\"text\" name=\"manualsizeobject\" value=\"0.0\">\
                <input type=\"color\" name=\"colorprojectobject\" value=\"#FF0000\">\
                <input type=\"submit\" value=\"��������� ���������\">\
            </form>";
    };
    function onAddAreaButtonClick(latC,lngC,prId) {
        xmlHttp.psCallBackFunction="onAddAreaButtonClick_Back";
        areaBuffer = L.Polyline.Plotter([[latC,lngC],[latC,lngC+0.002]],{
            mapobj_id: -1, 
            weight: 1, 
            color: 'black',
            dashArray: '3,3', 
            mocolor: 'rgba(0,0,0,1.0)',
            oname: '',
            manual_size: '0.0',
            cssid: 2
            }).addTo(mymap);
        //alert("INSERT INTO map_obj(id,oname,type,latlng,subtype,uid,dateedit,comment,project_id,morphe,manual_size)\
        //    VALUES (NULL,'����� �����',2,'"+latC+","+lngC+" "+latC+","+(lngC+0.002)+"',NULL,'"+userId+"',NULL,'','"+prId+"',2,0)");
        jSQL("insert","INSERT INTO map_obj(id,oname,type,latlng,subtype,uid,dateedit,comment,project_id,morphe,manual_size)\
            VALUES (NULL,'',3,'"+latC+","+lngC+" "+latC+","+(lngC+0.002)+"',NULL,'"+userId+"',NULL,'','"+prId+"',2,0)");

        areaBuffer.on('click', onProjectObjectClick); 
    };
    //
    function onSaveAreaButtonClick(objBuffer,mapobj_id,text,color,manual_size) {
        // objBuffer �� �� �����, ��� � areaBuffer
        var dist=0;
        var lLatLngs='';
        if(areaBuffer){
            var points=areaBuffer.getLatLngs();
            //areaBuffer.setReadOnly(true);                    
            var pin=0;
            for(pin=0;pin<(points.length-1);pin++){
                dist+=points[pin].distanceTo(points[pin+1]);
                lLatLngs+=points[pin].lat.toString()+","+points[pin].lng.toString()+" ";
            }
            lLatLngs+=points[pin].lat.toString()+","+points[pin].lng.toString();
            jSQL("update","update map_obj set latlng='"+lLatLngs.trim()+"',cosize="+dist/1000.0+
                    ", color='"+objBuffer.options.mocolor+"',oname='"+text+"',manual_size='"+manual_size+"' where id="+mapobj_id+"");
            lLatLngs=[];
            for(pin=0;pin<points.length;pin++){
                lLatLngs.push([points[pin].lat,points[pin].lng]);
            }
            // !!! ���������� new ����� ������ "callinithooks is not a function" !!!
            lPolyline= new L.polygon(lLatLngs,{
                mapobj_id: areaBuffer.options.mapobj_id, 
                dashArray: '3,3', 
                weight: 1, 
                color: areaBuffer.options.mocolor, 
                project_id: areaBuffer.options.project_id,
                mocolor: areaBuffer.options.mocolor,
                oname: areaBuffer.options.oname,
                manual_size: areaBuffer.options.manual_size,
                cssid: areaBuffer.options.cssid
                }).addTo(mymap);
            lIcon=new L.DivIcon({
                className: areaBuffer.options.cssid, 
                html: "<div><nobr style=\"color: "+areaBuffer.options.mocolor+"; \">"+
                        areaBuffer.options.mapobj_id+/*"&nbsp;&nbsp;&nbsp;"+dist/1000.0+" ��"*/" "+areaBuffer.options.oname+"</nobr></div>", 
                iconSize: new L.Point(14, 14) 
                });
            L.marker([(points[0].lat+points[1].lat)/2,(points[0].lng+points[1].lng)/2], 
                {mapobj_type: 3,ppolyline: lPolyline, mapobj_id: areaBuffer.options.mapobj_id, icon: lIcon, project_id: areaBuffer.options.project_id}).addTo(mymap)
                .on('click', onProjectObjectClick);
            mymap.removeLayer(areaBuffer);
            areaBuffer=false;
            document.getElementById('under_map_div').innerHTML="";
        }
    };
    //
    function onTypeAreaButtonClick_Back(sEvalRes){
        delete xmlHttp.psCallBackFunction; 
        var info_window_message="<b>��� ����� ����� </b><hr>";
        //alert(sEvalRes);

        var evalRes=eval(sEvalRes);
        for(var i=0;i<evalRes.length;i++){
            info_window_message+=evalRes[i]+'<br>';
        }
        document.getElementById('info_window_message').innerHTML=info_window_message;
        document.getElementById("info_window_darkening").style.display = 'block';
    }
    function onTypeAreaButtonClick() {
        xmlHttp.psCallBackFunction="onTypeAreaButtonClick_Back";
        jSQL("multiselect","SELECT concat('\"',REPLACE(concat('<b>',name,'</b><br>\
            <a href='''' onclick=''onSetTypeArea(',id,'); return false; '' style=''color: #800;''>',price,' ���.</a> �� ',ed),'\"',''''),'\"') sc \
                FROM ps_smet_calc order by name");
        return false;
    }
    function onSetTypeArea(smr_id){
        //alert("update map_obj set subtype="+smr_id+" where id="+areaBuffer.options.mapobj_id);
        jSQL("update","update map_obj set subtype="+smr_id+" where id="+areaBuffer.options.mapobj_id);
        document.getElementById("info_window_darkening").style.display = 'none';
    }
    function onDeleteAreaButtonClick(){
        //alert("delete from map_obj where id="+areaBuffer.options.mapobj_id);
        jSQL("update","delete from map_obj where id="+areaBuffer.options.mapobj_id);
        mymap.removeLayer(areaBuffer);
        areaBuffer=false;
        document.getElementById('under_map_div').innerHTML="";
    }
// ^^^ ������ � ��������� ������� ^^^
////////////////////////////////////////////////////////////////////////////////
    // ���������� ����� ����� � �������
    function onMoveEnd(e) {
        //alert("���������� " + e.latlng.toString() + " Zoom " + mymap.getZoom().toString());
        //console.log("Center " + mymap.getCenter().toString() + " Zoom " + mymap.getZoom().toString());
        //set_cookie("mapscale", vmymap.getZoom().toString(), 2099, 01, 01);
        var date = new Date(new Date().getTime() + 4*3600*1000); // 600 sec
        document.getElementById("map_scale_level").innerHTML = ""+mymap.getZoom().toString()+"";
        document.cookie = "mapscale="+mymap.getZoom().toString()+"; path=/; expires=" + date.toUTCString();
        document.cookie = "mapcenterlng="+mymap.getCenter().lng.toString()+"; path=/; expires=" + date.toUTCString();
        document.cookie = "mapcenterlat="+mymap.getCenter().lat.toString()+"; path=/; expires=" + date.toUTCString();
        // ���������� ������� ����
        document.cookie = "mapwestlng="+mymap.getBounds().getWest()+"; path=/; expires=" + date.toUTCString();
        document.cookie = "mapeastlng="+mymap.getBounds().getEast()+"; path=/; expires=" + date.toUTCString();
        document.cookie = "mapsouthlat="+mymap.getBounds().getSouth()+"; path=/; expires=" + date.toUTCString();
        document.cookie = "mapnorthlat="+mymap.getBounds().getNorth()+"; path=/; expires=" + date.toUTCString();
    }
    mymap.on('moveend', onMoveEnd);
    // ��������� ������� ��������� ������������
    // ����������/�������� ������� ��������� ��� �������� mymap.getZoom() ����� 13
    // ��� � �� �����: ��� 13 � 15 �������� ��� 14 ��� ?!
    mymap.on('zoomend', function() {
        if(mymap.getZoom() < 13){
            mymap.removeLayer(ClusterMarkers);
        } else {
            mymap.addLayer(ClusterMarkers);
        }
    });
    //
////////////////////////////////////////////////////////////////////////////////    
    </script>
    <table border='1' cellspacing='0' cellpadding='2' align='left'>
        <tr><td style='background: red;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� � ������</td>
            <td style='background: blue;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� � ������</td>
            <td><img src='./images/inproject.jpg' align='middle'></td>
            <td><span style='background: white;'>&nbsp;'�����������', �������� � ������</span></td>
            <td><img src='./images/exist_all.png' align='middle'></td>
            <td>������ ������������ ���������</td>
        </tr>
        <tr><td style='background: orange;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� � ���������</td>
            <td style='background: cyan;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� � ���������</td>
            <td><img src='./images/ud2_s.png' align='middle'></td>
            <td>���</td>
            <td><img src='./images/exist_4_yellow.png' align='middle'></td>
            <td>�� ������������ xDSL</td>
        </tr>
        <tr><td style='background: yellow;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� ����������</td>
            <td style='background: green;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� ����������</td>
            <td><img src='./images/dist_box.png' align='middle'></td>
            <td>��</td>
            <td><img src='./images/exist_4_green.png' align='middle'></td>
            <td>�� ������������ xDSL</td>
        </tr>
        <tr><td style='background: lightgray;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� �����</td>
            <td style='background: lightgray;'>&nbsp;&nbsp;</td>
            <td style='background: white;'>�� �����</td>
            <td><img src='./images/spdport.png' align='middle'></td>
            <td>���</td>
            <td><img src='./images/exist_3_yellow.png' align='middle'></td>
            <td>�� ������������ FTTx</td>
        </tr>
        <tr><td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><img src='./images/project24.png' align='middle'></td>
            <td>������</td>
            <td><img src='./images/exist_3_green.png' align='absmiddle'></td>
            <td>�� ������������ FTTx</td>
        </tr>
    </table>    
    <br>
    </td></tr>
    <tr><td colspan='2' height='20' background='images/top_bg.jpg' align='center' style='color: lightgray;'>���� powered </td></tr>
    </table>
    </TD></TR>
<?php
echo "<br><b></b>
    <br>";
include "footer.php";
?>        
