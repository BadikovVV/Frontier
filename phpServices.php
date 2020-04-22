<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->

<html>
    <head>
        <meta charset="windows-1251">
        <title></title>
    </head>
    <body>
        <?php
        define('__ROOT__',dirname(__FILE__));
        require_once (__ROOT__.'/vlg_geocod.php');
        ignore_user_abort();
        // put your code here
        if (isset($_GET['func']))
        {
                $funcName=$_GET['func'];
                # добавить проверку на куку curl_code , которая должна быть равна
                // crc32(имяФункции)
                $funcName();
            
        }
        ?>
    </body>
</html>
