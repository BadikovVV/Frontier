<?php
/*<way id="10509893" version="18" timestamp="2016-09-16T05:24:06Z" uid="395588" user="Danidin9" changeset="42194435">
    <nd ref="90451720"/>
    <nd ref="3322443770"/>
    <nd ref="3322443762"/>
    <nd ref="3322438183"/>
    <nd ref="668188370"/>
    <nd ref="90451721"/>
    <nd ref="668188374"/>
    <nd ref="668188372"/>
    <nd ref="305574210"/>
    <nd ref="668188371"/>
    <nd ref="1347102952"/>
    <nd ref="90451722"/>
    <nd ref="1347103013"/>
    <nd ref="668188377"/>
    <nd ref="305574211"/>
    <nd ref="668188375"/>
    <nd ref="305574212"/>
    <nd ref="4403737253"/>
    <nd ref="90451723"/>
    <nd ref="668188380"/>
    <nd ref="305574214"/>
    <nd ref="668188379"/>
    <nd ref="668188378"/>
    <nd ref="90451724"/>
    <tag k="ref" v="18&#x41A;-9"/>
    <tag k="name" v="&#x413;&#x43C;&#x435;&#x43B;&#x438;&#x43D;&#x43A;&#x430; - &#x41F;&#x430;&#x43B;&#x43B;&#x430;&#x441;&#x43E;&#x432;&#x43A;&#x430;"/>
    <tag k="lanes" v="2"/>
    <tag k="oneway" v="no"/>
    <tag k="highway" v="secondary"/>
    <tag k="surface" v="asphalt"/>
    <tag k="maxspeed" v="90"/>
  </way>*/
            $reader = new XMLReader();
            $reader->open('/var/www/html/cs/buffer/RU-VGG-test.osm');
            SQL("TRUNCATE TABLE osm.node");
            SQL("TRUNCATE TABLE osm.building");
            SQL("TRUNCATE TABLE osm.nodetag");
            SQL("TRUNCATE TABLE osm.waynode");
            SQL("TRUNCATE TABLE osm.waytag");
            //echo "-";
            // циклическое чтение документа
            $i=0;
            $prevdepth=-1;
            //$prevsupernodeid=-1;
            //$prevnode;
            $stacknodename=array('root');
            $stacknodeid=array(0);
            while($reader->read() and $i<40) {
//                            echo "-----------------".
//                            $reader->nodeType." ".
//                            $reader->attributeCount." ".
//                            //$reader->baseURI." ".
//                            $reader->depth." ".
//                            $reader->hasAttributes." ".
//                            $reader->hasValue." ".
//                            $reader->isDefault." ".
//                            $reader->isEmptyElement." ".
//                            $reader->localName." ".
//                            $reader->name." ".
//                            $reader->namespaceURI." ".                    
//                            $reader->prefix." ".
//                            $reader->value." ".
//                            $reader->xmlLang."<br>|_";

                switch($reader->nodeType){
                case XMLReader::ELEMENT:
                    $stacknodename[$reader->depth]=$reader->localName;
                    $stacknodeid[$reader->depth]=$reader->getAttribute('id');
                    $depthstr=str_pad("" ,  $reader->depth, '.  ');
                    // 
                    switch($reader->localName){
                    case 'node1':
                        $i++;
//                        echo $depthstr."node(".$reader->getAttribute('id')." ".
//                            $reader->getAttribute('lat')." ".
//                            $reader->getAttribute('lon').")<br>";
                        //SQL("INSERT INTO osm.node (nid,lat,lon) values(".
                        //        $reader->getAttribute('id') .",".$reader->getAttribute('lat') .",".$reader->getAttribute('lon') .")")->commit();
                        $sxe = new SimpleXMLElement($reader->readOuterXml());
                        $nid=$sxe['id'];
                        //echo '$sxe '.$nid .":";
                        SQL("INSERT INTO osm.node (nid,lat,lon) values(".
                                $nid .",".$sxe['lat'] .",".$sxe['lon'] .")")->commit();                        
                        //foreach($sxe->attributes() as $a => $b) {
                        //    echo $a,'="',$b,"\"\n";
                        //}
                        foreach($sxe->children() as $a => $b) {
                            if($a=='tag'){
                                SQL("INSERT INTO osm.nodetag (nid,tkey,tvalue) values(".
                                $nid .",'".
                                $b['k'] ."','".
                                preg_replace("/'/"," ",iconv('UTF-8', 'CP1251',$b['v'])) ."')")->commit();
                            }
                        }
                    break;
                    case 'tag':
                        //echo $depthstr.$stacknodename[$reader->depth-1]."-tag[".$reader->getAttribute('k')."]=".
                        //    iconv('UTF-8', 'CP1251',$reader->getAttribute('v'))."<br>";
                        switch($stacknodename[$reader->depth-1]){
                        case 'node': // node tag
                        //    echo $depthstr."nodetag[".$reader->getAttribute('k')."]=".
                        //        iconv('UTF-8', 'CP1251',$reader->getAttribute('v'))."<br>";
                        //    SQL("INSERT INTO osm.nodetag (nid,tkey,tvalue) values(".
                        //        $stacknodeid[$reader->depth-1] .",'".
                        //        $reader->getAttribute('k') ."','".
                        //        preg_replace("/'/"," ",iconv('UTF-8', 'CP1251',$reader->getAttribute('v'))) ."')")->commit();
                        break;
                        case 'way': // way tag
//                            echo $depthstr."waytag[".$reader->getAttribute('k')."]=".
//                                iconv('UTF-8', 'CP1251',$reader->getAttribute('v'))."<br>";
//                            SQL("INSERT INTO osm.waytag (wid,tkey,tvalue) values(".
//                                $stacknodeid[$reader->depth-1] .",'".
//                                $reader->getAttribute('k') ."','".
//                                preg_replace("/'/"," ",iconv('UTF-8', 'CP1251',$reader->getAttribute('v'))) ."')")->commit(); 
                        break;
                        }
                    break;
                    case 'way':
                        $i++;
                        $sxe = new SimpleXMLElement($reader->readOuterXml());
                        $wid=$sxe['id'];
                        //
                        //echo $depthstr."way[".$reader->getAttribute('id')."]:".$reader->readOuterXml() ."<br>";
                        echo '$sxe '.$wid .":";
                        //foreach($sxe->attributes() as $a => $b) {
                        //    echo $a,'="',$b,"\"\n";
                        //}
                        //
//                        foreach($sxe->children() as $a => $b) {
//                            //echo $a,'="',$b['ref'],"\"\n";
//                            if($a=='nd'){
//                                SQL("INSERT INTO osm.waynode (wid,nid) values(".$wid .",".$b['ref'] .")")->commit();
//                            }
//                            if($a=='tag'){
//                                SQL("INSERT INTO osm.waytag (wid,tkey,tvalue) values(".
//                                $wid .",'".
//                                $b['k'] ."','".
//                                preg_replace("/'/"," ",iconv('UTF-8', 'CP1251',$b['v'])) ."')")->commit();
//                            }
//                        }
                        //
                        $tags=array();
                        $building=false;
                        foreach($sxe->children() as $a => $b) {
                            if($a=='tag'){
                                //echo $b['k'],'="',$b['v'],"\"\n";
                                $tags[$b['k']]=preg_replace("/'/"," ",iconv('UTF-8', 'CP1251',$b['v']));
                                echo "[".$b['k'] ."]=".preg_replace("/'/"," ",iconv('UTF-8', 'CP1251',$b['v'])) .'<br>' .$tags["addr:street"] ."***";
                                if($b['k']=="building" and $b['v']=="yes"){
                                    $building=true;
                                }
                            }
                        }
                        if($building){
                            SQL("INSERT INTO osm.building (wid,street,number) values(".
                                $wid .",'".
                                $tags["addr:street"] ."','".
                                $tags["addr:housenumber"] ."')")->commit();
                        }
                        
                    break;
                    case 'nd':
                        //echo $depthstr."nd/".$reader->getAttribute('ref')."/<br>";
                        //SQL("INSERT INTO osm.waynode (wid,nid) values(".$stacknodeid[$reader->depth-1] .",".$reader->getAttribute('ref') .")")->commit();
                        
                    break;
                    case 'relation':
//                        $data = array();
//                        // считываем аттрибут number
//                        $data['id'] = $reader->getAttribute('id');
//                        // читаем дальше для получения текстового элемента
//                        $reader->read();
//                        if($reader->nodeType == XMLReader::TEXT) {
//                            $data['name'] = $reader->value;
//                        }
//                        SQL("INSERT INTO osm.member (type,obid,role) values('rel',".$data['id'] .",'test')")->commit();
                    break;
                    default:
//                        echo "-----------------".
//                            $reader->nodeType." ".
//                            $reader->attributeCount." ".
//                            //$reader->baseURI." ".
//                            $reader->depth." ".
//                            $reader->hasAttributes." ".
//                            $reader->hasValue." ".
//                            $reader->isDefault." ".
//                            $reader->isEmptyElement." ".
//                            $reader->localName." ".
//                            $reader->name." ".
//                            $reader->namespaceURI." ".                    
//                            $reader->prefix." ".
//                            $reader->value." ".
//                            $reader->xmlLang."<br>|_";
//                        for($j=0;$j<$reader->attributeCount;$j++){
//                            echo iconv('UTF-8', 'CP1251',$reader->getAttributeNo($j))."_|_";
//                        }
//                        echo "_|<br>";
                    }
                    //$prevnode=$reader->localName;
                break;
                case XMLReader::TEXT:
                    echo "-----------------".
                        $reader->nodeType." ".
                        $reader->attributeCount." ".
                        //$reader->baseURI." ".
                        $reader->depth." ".
                        $reader->hasAttributes." ".
                        $reader->hasValue." ".
                        $reader->isDefault." ".
                        $reader->isEmptyElement." ".
                        $reader->localName." ".
                        $reader->name." ".
                        $reader->namespaceURI." ".                    
                        $reader->prefix." ".
                        $reader->value." ".
                        $reader->xmlLang."<br>|_";
                    for($j=0;$j<$reader->attributeCount;$j++){
                        echo iconv('UTF-8', 'CP1251',$reader->getAttributeNo($j))."_|_";
                    }
                    echo "_|<br>";
                    //$i++;
                break;
                default:                
                }
                //$i++;
                $prevdepth=$reader->depth;
            }
            $reader->close();
?>