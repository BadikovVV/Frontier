<?php
header("Content-Type: text/html; charset=windows-1251"); //charset=utf8");
?>
<!DOCTYPE html>
<!--
���������� ����������� ����� � ��������� ���� ��������
-->
<html>
    <head>
        <title>TODO supply a title</title>
        <meta charset="windows-1251">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <?php
        echo "<img src=\"vlg_image_select.php?otype=".$_GET['otype'] ."&oid=".$_GET['oid'] .
            "\" alt=\"������ ".$_GET['otype'] ."/".$_GET['oid']." � �� �� ���������� ��� ��� �����!\" />";
        ?>
    </body>
</html>
