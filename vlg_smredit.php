<?php
    if ($_REQUEST["smreditrow"] == "true") {
    // ����� �������������� ���
        if ($_REQUEST["smreditaddrow"] == "true") {
            $smr_row["id"]='new';
            $smr_row["mgroup"]='';
            $smr_row["pgroup"]='';
            $smr_row["name"]='';
            $smr_row["ed"]='�';
            $smr_row["price"]='0.0';
            $smr_row["coment"]='';
        } else {
            $smr_row=rSQL("SELECT * FROM ps_smet_calc where id=".$_REQUEST["smr_id"]);
        }
        echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
        echo "<legend>&nbsp;<b style='color: #006;'>�������������� ���</b>&nbsp;</legend>";
        echo "<form name='form_smr_edit' method='post' style='' "
                ."action='./?c=7&action=smredit&smr_id=".$smr_row["id"] ."&smreditsaverow=true'>
            ������:<input type='text' name='mgroup' size='80' value='".$smr_row["mgroup"] ."'><br>
            ������ �����:<input type='text' name='pgroup' size='80' value='".$smr_row["pgroup"] ."'><br>
            ������������:<input type='text' name='name' size='120' value='".$smr_row["name"] ."'><br>
            ������� ���������:<input type='text' name='ed' size='20' value='".$smr_row["ed"] ."'>&nbsp
            ���������:<input type='text' name='price' size='20' value='".$smr_row["price"] ."'>���.<br>
            ��������:<textarea name='coment' rows='4' cols='80'>".$smr_row["coment"] ."</textarea><br><br>
            <input type='submit' value='��������� ���������' ".$ubord->mayIdo("button")
                ." > <input type='button' onclick=' window.location = \"./?c=7&action=smredit\"; ' value='���������'/>        
                <input type='button' onclick=' document.form_smr_edit.action=\"./?c=7&action=smredit&smr_id=new&smreditsaverow=true\"; 
                            document.form_smr_edit.submit(); ' value='�������� ��� �����'/>
        </form></fieldset>";
    } else {
        echo "<nobr><b>�������������� ���</b>&nbsp";
        echo " <input type='button' value='�������� �����' 
            onclick=' window.location=\"./?c=7&action=smredit&smreditrow=true&smreditaddrow=true\";'></nobr>";
        //
        if ($_REQUEST["smreditsaverow"] == "true") {
            if($_REQUEST["smr_id"]=='new'){
                SQL("INSERT INTO ps_smet_calc (mgroup,pgroup,name,coment,price,old,ed) values('" . 
                    $_REQUEST["mgroup"] ."','". $_REQUEST["pgroup"] ."','". $_REQUEST["name"] ."','". 
                    $_REQUEST["coment"] ."','". $_REQUEST["price"] ."',0,'". $_REQUEST["ed"] ."')")->commit();
            }else{
                SQL("UPDATE ps_smet_calc SET pgroup = '". $_REQUEST["pgroup"] ."', name='". 
                        $_REQUEST["name"] ."', coment='". $_REQUEST["coment"] ."', price='". 
                        $_REQUEST["price"] ."', mgroup='". $_REQUEST["mgroup"] . "', ed='". $_REQUEST["ed"] . 
                        "' WHERE id='". $_REQUEST["smr_id"] ."'")->commit();
            }
        }
        //
        if ($_REQUEST["delete_smr"] == "true") {
            SQL("delete from ps_smet_calc where id=".$_REQUEST["smr_id"]) -> commit();
        }
        // ����� �������
        echo "<style type=\"text/css\">
                td.prjleftcol { color:#025; background:#EEF; }
                td.prjheader { color:#037; background:#DEF; }
                td.prjtype1 { color:#800; }
                td.prjtype2 { color:#080; }
                td.prjtype3 { color:#008; }
            </style>";
        echo "<table border='1' cellspacing='0' bordercolor='black' bordercolordark='white' width='100%'>";
        $cursor=SQL("SELECT * FROM ps_smet_calc order by mgroup,pgroup,name");
        echo "<tr>
            <td class='prjheader'><b>������</b></td>
            <td class='prjheader'><b>������ �����</b></td>
            <td class='prjheader'><b>������������ �����</b></td>
            <td class='prjheader'><b>��������</b></td>
            <td class='prjheader'><b>������� ���������</b></td>
            <td class='prjheader'><b>���������, ���.</b></td>
            <td class='prjheader'><b>&nbsp</b></td>
            </tr>";
        while ($cursor->assoc()) {
            //d($cursor->r);
            echo "<tr>
                <td align='center' class=''>".$cursor->r["mgroup"] ."</td>
                <td align='center' class=''>".$cursor->r["pgroup"] ."</td>
                <td align='center' class=''>
                    <a href='./?c=7&action=smredit&smr_id=" . $cursor->r["id"] . 
                    "&smreditrow=true' title='�������������'>(".
                    $cursor->r["id"].")<b>".str_ireplace("\"","`",$cursor->r["name"])."</b></a></td>
                <td align='center' class=''>".str_ireplace("\"","`",$cursor->r["coment"]) ."</td>
                <td align='center' class=''>".$cursor->r["ed"]."</td>    
                <td align='center' class=''>".$cursor->r["price"]."</td>
                <td align='center' class='prjleftcol' title='�������' onclick=' if(confirm(\"������� ��� ?\"))
                            window.location = \"./?c=7&action=smredit&smr_id=" . $cursor->r["id"] . 
                    "&delete_smr=true \"; '><b>X</b></td>
                </tr>";
        }
        echo "</table>";
        $cursor->free();
    }
?>