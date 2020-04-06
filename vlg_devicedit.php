<?php

                echo "<b>Редактирование оборудования</b>&nbsp";
                if (@$_GET["do"] == "copyform") {
                    echo "<fieldset style='padding: 20px; width: 50%;border-color: darkgray;'>";
                    if (@$_GET["did"] and @ $_GET["did"] != 0) {
                        if (@$_GET["onlyedit"] == "true")
                            echo "<legend>&nbsp;<b style='color: orangered;'>Редактирование</b>&nbsp;</legend>";
                        else
                            echo "<legend>&nbsp;<b style='color: orangered;'>Копирование оборудования</b>&nbsp;</legend>";
                        $result_dev1 = qSQL("SELECT * FROM ps_equip WHERE id='" . $_GET["did"] . "'");
                        $row_dev1 = mysql_fetch_array($result_dev1);
                        echo "<br><BIG style='color: midnightblue;'><b>" . $row_dev1["name"] . "</b></BIG> [" . $row_dev1["price"] . " руб.]</p>";
                    } else {
                        echo "<legend>&nbsp;<b style='color: orangered;'>Добавление нового оборудования</b>&nbsp</legend>";
                    }
                    if (@$_GET["onlyedit"] == "true")
                        $addedit = "&onlyedit=true";
                    echo "<form method='post' action='./?c=7&action=newDeviceList&do=copy&did=" . @$_GET["did"] . @$addedit . "'>";
                    if (@$row_dev1["for_report"] == 1)
                        $ch_f_report = " checked";
                    else
                        $ch_f_report = '';
                    echo "<table>
                    <tr><td><b>Раздел: </b><input type='text' name='dgroup' value='" . @$row_dev1["pgroup"] . "'></td>
                    <tr><td><b>Артикул: </b><input type='text' name='dname' value='" . @$row_dev1["name"] . "'></td>
                    <tr><td><b>Наименование комплектующего в составе оборудования: </b> <br><textarea style='width: 400px' rows='2' name='dcoment'>" . 
                            @$row_dev1["coment"] . "</textarea></td>
                    <tr><td><b>Стоимость (в рублях!): </b><input type='text' name='dprice' value='" . @$row_dev1["price"] . "'></td></tr>;
                    <tr><td><b>Отображать в отчетах: </b><input type='checkbox' name='cb_for_report_input' " . @$ch_f_report . " value='true'></td></tr></table>";
                    echo "<p>";
                    if (@$_GET["did"] and @ $_GET["did"] != 0 and ! @$_GET["onlyedit"]) {
                        echo "<input type='checkbox' name='oldtrue' value='true' checked> Устаревшее оборудование или изменение стоимости *";
                        echo "<br><br>* - Установив данный флаг Вы запретите выбор данного (текущего) оборудования для указания в выпадающем списке «Тип оборудования, необходимого для организации канала» для новых проработка ТВ, и для изменения старых заявок.";
                    }
                    echo "<p><input type='submit' value='Сохранить'></p></form>";
                    echo "</fieldset><br><br>";
                } else
                    echo " <input type='button' value='Добавить новое' Onclick='javascript:window.open(\"./?c=7&action=newDeviceList&do=copyform\",\"_self\")'><p>";
                if (@$_GET["do"] == "copy" and @ $_GET["did"] != 9999) {
                    $result_dev2 = qSQL("SELECT * FROM ps_equip WHERE id='" . $_GET["did"] . "'");
                    $row_dev2 = mysql_fetch_array($result_dev2);
                    if (@$_POST["dname"] != '' and @ $_POST["dprice"] != '') {
                        if (@$_POST["cb_for_report_input"] and @ $_POST["cb_for_report_input"] == "true")
                            $_POST["cb_for_report_input"] = 1;
                        else
                            $_POST["cb_for_report_input"] = 0;
                        if (@$_GET["onlyedit"] != "true") {
                            $result_insert = qSQL("INSERT INTO ps_equip (pgroup,name,coment,price,for_report,old,ed) values('" . 
                                    $_POST["dgroup"] . "','" . $_POST["dname"] . "','" . $_POST["dcoment"] . "','" . 
                                    $_POST["dprice"] . "','" . $_POST["cb_for_report_input"] . "',1,'шт.')");
                        } else {
                            $result_update = qSQL("UPDATE ps_equip SET pgroup = '" . $_POST["dgroup"] . "', name='" . 
                                    $_POST["dname"] . "', coment='" . $_POST["dcoment"] . "', price='" . 
                                    $_POST["dprice"] . "', for_report='" . $_POST["cb_for_report_input"] . 
                                    "' WHERE id='" . $_GET["did"] . "'");
                        }
                    } else
                        echo "<br><b style='color: red;'>Ошибка! Одно из полей не заполнено.</b><br>";
                    if (@$_POST["oldtrue"] == "true" and @ $_GET["did"]) {
                        $result_update = qSQL("UPDATE ps_equip SET old=1 WHERE id='" . $_GET["did"] . "'");
                    }
                    if (@$result_insert == TRUE) {
                        echo "<p><img src='./images/check.gif' align='absmiddle'> <b>Оборудование </b><b style='color: green;'>" . 
                                $_POST["dname"] . "</b> <b>успешно добалено в БД.</b></b><br>";
                    }
                }
                if (@$_GET["do"] == "hide" and @ $_GET["did"] != 9999 and @ $_GET["did"] and @ $_GET["did"] != '') {
                    $result_dev2 = qSQL("SELECT * FROM ps_equip WHERE id='" . $_GET["did"] . "'");
                    $row_dev2 = mysql_fetch_array($result_dev2);
                    if ($row_dev2["old"] == 1)
                        $dotset = 0;
                    else
                        $dotset = 1;
                    $result_update = qSQL("UPDATE ps_equip SET old='" . $dotset . "' WHERE id='" . $_GET["did"] . "'");
                }
                echo "<table border='1' cellspacing='0' bordercolor='black' bordercolordark='white' width='100%'>";
                $query_dev = "SELECT * FROM ps_equip order by pgroup;";
                $result_dev = mysql_query($query_dev) or die("Query failed: dev");
                echo "<tr bgcolor='lightgrey'><td><b>ID</b></td><td><b>Раздел</b></td><td><b>Артикул</b></td><td><b>Наименование комплектующего в составе оборудования</b></td><td><b>Стоимость, руб.</b></td><td><b>Отображать в отчетах.</b></td><td>&nbsp;</td><td>&nbsp;</td></tr>";
                while ($row_dev = mysql_fetch_array($result_dev)) {
                    if (@$row_dev["for_report"] == 1)
                        $ch_for_report = " checked";
                    else
                        $ch_for_report = '';
                    if ($row_dev["old"] == 1)
                        $odl_dev = " bgcolor=wheat";
                    else
                        $odl_dev = '';
                    if ($row_dev["old"] == 1)
                        $hide_link = "<a href='./?c=7&action=newDeviceList&do=hide&did=" . $row_dev["id"] . 
                            "' title='Разрешить выбор'><img src='./images/red_dot.gif' align='absmiddle'></a>";
                    else
                        $hide_link = "<a href='./?c=7&action=newDeviceList&do=hide&did=" . $row_dev["id"] . 
                            "' title='Запретить выбор'><img src='./images/green_dot.gif' align='absmiddle'></a>";
                    echo "<tr" . @$odl_dev . "><td>" . $row_dev["id"] . "</td><td>" . $row_dev["pgroup"] . 
                            "</td><td>" . $row_dev["name"] . "</td><td>" . $row_dev["coment"] . "</td><td align='right'>" . 
                            $row_dev["price"] . "</td><td align='center'><input type='checkbox' name='cb_for_report' disabled='true' value='true'" . 
                            @$ch_for_report . "></td><td align='center'><a href='./?c=7&action=newDeviceList&do=copyform&did=" . 
                            $row_dev["id"] . "&onlyedit=true' title='Редактировать'><img src='./images/edit.gif' align='absmiddle'></a></td><td align='center'>" . 
                            @$hide_link . "</td></tr>";
                }
                echo "</table>";
?>
