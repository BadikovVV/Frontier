<?php
ini_set('display_errors', 'On');
error_reporting('E_ALL');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//if (!defined("INDEX"))
//    DIE("Страница не найдена.");
$ip = getenv("REMOTE_ADDR"); // получает ip-номер пользователя
//$locate_glob = $_SERVER['REQUEST_URI'];
/* Соединение, выбор БД */
//INCLUDE "db_connect.php";
qSQL("set autocommit=1");
$udate2 = date("Y-m-d");
$uyear = date("Y");
$udate = date("d.m.Y");
$udate_time2 = date("d.m.Y H:i:s");
$udate_time = date("Y-m-d H:i:s");
// Активация нового пользователь уже заведённого в БД, его первый вход с передачей ключа hash
if (@$_GET['action'] == "active") {
    echo "<br><br><b>Активация пользователя...</b><br><br>";
    $message = "<img src='./images/loading.gif'>";
    $result_user = qSQL("SELECT * FROM ps_users WHERE login='" . $_GET["login"] . "'");
    if (mysql_num_rows($result_user) == 1) {
        $row = mysql_fetch_array($result_user);
        if ($row["hash"] == $_GET["hash"]) { // Проверим совпадает ли контрольная сумма при авторизации
            $q_update = "UPDATE ps_users SET status='1' WHERE id='" . $row["id"] . "';";
            $result_update = mysql_query($q_update) or die("<br><br><br><center><img border='0' src='./images/aff_cross.gif' align='absmiddle'> <b style='text-align: center; color: red'>Query failed. Не могу выполнить активацию пользователя!</b></center>");
        } // Если не совпала контрольная суума, но пользователь нашелся
        else
            echo "<br><br><img border='0' src='./images/aff_cross.gif' align='absmiddle'> 
                <b style='color: red'>Ошибка: Не удалось Активировать учётную запись для пользователя: <b style='color: #003399'>" . 
                $row["name"] . " (" . $row["login"] . ")</b> ! Не совпадение контрольной суммы.</b><br><br>";
    }
    echo "<SCRIPT language=javascript type=\"text/javascript\">
	function redir() {	setTimeout('location.replace(\"./\")', 2000);	}
	redir();
	</SCRIPT>";
}
if (@$_GET['action'] == "logout") {
    $uid=rSQL("SELECT uid FROM ps_users WHERE login='". $_COOKIE['rtcomug'][0] . "'")["uid"];
    qSQL("INSERT INTO occurrence(id,otype,uid,dateinsert,ipaddress)
        VALUES (NULL,2,". (isset($uid) ? $uid : -1)
            .",NULL,'". $_SERVER['REMOTE_ADDR']."')");
    setcookie("rtcomug[1]", '', time() - 34400); // Пароль
    unset($_COOKIE['rtcomug']);
    setcookie('rtcomug[1]', '', false, "/");
    setcookie("rtcomug[1]", "", time() - 34400, "/~rasmus/", ".utoronto.ca", 1);
    if (@$_COOKIE['rtcomug'][2] == 0) {
        //echo "1";
        setcookie("rtcomug[0]", "", time() - 34400); // Логин
        setcookie("rtcomug[1]", "", time() - 34400); // Пароль
        setcookie("rtcomug[2]", "0", time() - 34400); // Сохранить имя пользователя в куках (1) или нет (0)
    } else {
        //echo "2";
        setcookie("rtcomug[1]", "-", time() - 34400);
        setcookie("rtcomug[2]", "1", time() + 34400);
    }
    //$message = "<img src='./images/loading.gif'>";
    /* echo "<SCRIPT language=javascript type=\"text/javascript\">
      function redir() {	setTimeout('location.replace(\"./\")', 500);	}
      redir();
      </SCRIPT>"; */
}
if (@$_COOKIE['rtcomug'][0] and @ $_COOKIE['rtcomug'][0] != '' and @ $_COOKIE['rtcomug'][1] and @ $_COOKIE['rtcomug'][1] != '' and 
        @ $_GET['action'] != "logout") {// Если в куках логин и пароль
//echo "<br><br>Есть куки с логином и паролем<br><br>";
//echo "<br>[0]".$_COOKIE['rtcomug'][0];
//echo "<br>[1]".$_COOKIE['rtcomug'][1];
//echo "<br>[2]".$_COOKIE['rtcomug'][2];
    $result_users = qSQL("SELECT * FROM ps_users WHERE login='" . $_COOKIE['rtcomug'][0] . "'");
    if (mysql_num_rows($result_users) == 1) {
        $row_users = mysql_fetch_array($result_users);
        if ($row_users["pass"] == $_COOKIE['rtcomug'][1]) {
            if ($row_users["status"] == "2")  // 2 заблокированный пользователь
                $message = "<b style='color: red'>Учетная запись " . $_COOKIE['rtcomug'][0] . 
                    " Заблокирована, обратитесь к Администратору ресурса, для регистрации персональной учетной записи.".
                    " Отправьте письмо на <a href='mailto:Denis_Sakhnov@south.rt.ru'>электронный ящик</a> с данными пользователя.".
                    " (Или список всех пользователей, которые работали под данной учетной записью)</b><br>";
            elseif ($row_users["status"] == "1"){
                define("LOGINED", "TRUE", TRUE);  // Активный пользователь
                if(empty(trim($row_users["service2"]))){
                    $user_LTC_list='';
                }else{
                    //$cursor=SQL("SELECT name FROM ps_ltc where lid in (SELECT lid FROM ltc where lid in (".$row_users["service2"] ."))");
                    $cursor=SQL("SELECT SUBSTRING_INDEX(name, ' ', 1) name FROM ps_ltc where lid in "
                            . "(SELECT lid FROM ltc where lid in (".$row_users["service2"] .")) group by SUBSTRING_INDEX(name, ' ', 1)");
                    $user_LTC_list=[];
                    while ($cursor->assoc()) {
                        array_push($user_LTC_list, "'".$cursor->r["name"]."'");
                    }
                    $cursor->free();
                    $user_LTC_list=implode(",",$user_LTC_list);
                    //d($user_LTC_list);
                }
            }
            elseif ($row_users["status"] == "0")
                $message = "<b style='color: red'>Учетная запись " . $_COOKIE['rtcomug'][0] . " не активирована.".
                    " (Не проверена администратором).</b> <a href='mailto:Denis_Sakhnov@south.rt.ru'>Поддержка</a><br>"; // НЕ активный пользователь
        }
    }
} // ^^ Если в куках логин и пароль ^^
//													} // Есть есть куки у пользователя
else { // ЕСЛИ НЕТ
//echo "<br> No cookies ? no login? ".isset($_POST["login"]);

  if(isset($_POST["login"]) && noSQLInj($_POST["login"])){
//	echo "<br> trying to login";
    // Попросим авторизоваться
    if (@$_GET['action'] == "login" and @ $_POST["login"] and @ $_POST["pass"] and @ $_GET['action'] != "logout") {
        //echo "Авторизация началась...<br>";
        $_POST["login"] = str_replace(" ", "", $_POST["login"]);
        $_POST["pass"] = str_replace(" ", "", $_POST["pass"]);
        $result_users = qSQL("SELECT * FROM ps_users WHERE login='" . $_POST["login"] . "'");
        if (mysql_num_rows($result_users) != 0) {
            while ($row_users = mysql_fetch_array($result_users)) {
                if ($row_users["login"] == @$_POST["login"]) {
                    if ($row_users["status"] == "0") {
                        $message = "<center><img src='./images/cross.gif' align='absmiddle'> <b style='color: red'>Учетная запись <b style='color: black;'>" . 
                            $row_users["login"] . "</b> не активирована. (Не проверена администратором).</b> 
                            <a href='mailto:Denis_Sakhnov@south.rt.ru'>Поддержка</a></center>";
                        break;
                    }
                    /*if ($row_users["ugroup"] == 59) {
                        $message = "<center><img src='./images/cross.gif' align='absmiddle'> <b style='color: red'>"
                                . "Для авторизации с учетной записью <b style='color: black;'>" . $row_users["login"] . 
                                "</b> необходимо перейти по <a href='http://62.183.62.237/builder/?action=logout'>ссылке</a>."
                                . "</b> <a href='mailto:makarevichk@krd.south.rt.ru'>Поддержка</a></center>";
                        break;
                    }*/
                    if ($row_users["status"] == "2") {
                        $message = "<center><img src='./images/cross.gif' align='absmiddle'> <b style='color: red'>Учетная запись <b style='color: black;'>" . 
                            $row_users["login"] . "</b> Заблокирована, обратитесь к Администратору ресурса, для регистрации персональной учетной записи."
                                . "<br>Отправьте письмо на <a href='mailto:Denis_Sakhnov@south.rt.ru'>электронный ящик</a> "
                                . "с данными пользователя. (Или список всех пользователей, которые работали под данной учетной записью)</b></center>";
                        break;
                    }
                    //echo "Нашли логин в БД...<br>";
                    //echo "PASS: \"".$_POST["pass"]."\"<br>";
                    if ($row_users["pass"] == md5(@$_POST["pass"])) {
                        //	echo "Пароль совпал...<br>";
                        if (@$_POST["saveme"] == "1") {
                            setcookie("rtcomug[0]", $row_users["login"], time() + 9999999999);
                            setcookie("rtcomug[1]", $row_users["pass"], time() + 9999999999);
                            setcookie("rtcomug[2]", "1", time() + 9999999999);
                        } else {
                            setcookie("rtcomug[0]", $row_users["login"], time() + 34400);
                            setcookie("rtcomug[1]", $row_users["pass"], time() + 34400);
                            setcookie("rtcomug[2]", "0", time() + 34400);
                        }
                        
                        qSQL("INSERT INTO occurrence(id,otype,uid,dateinsert,ipaddress)
                            VALUES (NULL,1,". (isset($row_users["uid"]) ? $row_users["uid"] : -1) .",NULL,'". $_SERVER['REMOTE_ADDR']."')");
                        define("LOGINED", "TRUE", TRUE);
                        $message = "<img src='images/statusbar.gif'>";
                        echo "<SCRIPT language=javascript type=\"text/javascript\">
                            function redir() {	setTimeout('location.replace(\"./index.php\")', 400);	}
                            redir();
                            </SCRIPT>";
                        break;
                    } else {
                        $row_users["fio"] = '';
                        $message = "<b style='color: red'>Логин или Пароль не совпадают!</b>";
                        setcookie("rtcomug[1]", "", time() - 34400); // Пароль
                        setcookie("rtcomug[2]", "0", time() - 34400); // Сохранить имя пользователя в куках (1) или нет (0)
                        break;
                    }
                } else {
                    $message = "<b style='color: red'>Логин или пароль не совпадают!</b>";
                    setcookie("rtcomug[2]", "0", time() - 34400);
                }
            }
        } else {
            $message = "<b style='color: red'>Логин или пароль не совпадают!</b>";
            setcookie("rtcomug[2]", "0", time() - 34400);
        }
    } // ^^ ЕСЛИ НЕТ ^^ //else echo "<br>Куков нет и авторизации нет";
  } else {
	if (stripos($_SERVER['PHP_SELF'],'index.php')==false){
		echo "<meta http-equiv=\"Refresh\" content=\"2; url=/index.php?c=4\">";
	};
//	else
//	{
//		    echo 'обратитесь к Администратору ресурса';
//	};
    return;
  }
} 
?>	
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
        <META http-equiv=Pragma content=no-cache>
        <link rel="stylesheet" type="text/css" href="/src/style.css">
        <link rel="stylesheet" href="js/leaflet.css" />
        <link rel="stylesheet" type="text/css" href="css/ps_popup.css">

        <!-- Календарь от JsDatePick's Javascript Calendar -->
        <!--script language="JavaScript" src="/src/calendar.js"></script-->
        <!--link rel="stylesheet" type="text/css" media="all" href="/css/jsDatePick_ltr.min.css"-->
        <!--link rel="stylesheet" type="text/css" href="/src/calendar.css"-->
        <script type="text/javascript" src="js/vlg_util.js"></script>
        <!-- ************************************* -->
        <!-- Скрипт открытия диалоговых окон -->
        <!--script type="text/javascript" src="/src/winopn/highslid.js"></script>
        <link rel="stylesheet" type="text/css" href="/src/winopn/highslid.css" />
        <script type="text/javascript">
            hs.graphicsDir = '/src/winopn/graphics/';
            hs.outlineType = 'rounded-white';
            hs.wrapperClassName = 'draggable-header';
            hs.preserveContent = false;
            hs.showCredits = false;
            //hs.allowHeightReduction = true;
        </script-->
<!-- <script type="text/javascript" src="http://code.jquery.com/jquery-1.7.2.min.js"></script> -->
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script> -->
        <script src="/js/jquery.min.js"></script>
        <!-- FORM STYLER -->
        <link href="/css/cusel.css" rel="stylesheet" type="text/css">
        <!-- <script type="text/javascript" src="jquery.js"></script> -->
        <!-- TOOLTIP -->
        <!--link rel="stylesheet" href="/tooltip/css/bubble-tooltip.css" media="screen">
        <script type="text/javascript" src="/tooltip/js/bubble-tooltip.js"></script-->
        <!-- ************************************* -->
        <title>"Фронтир" Волгоградский филиал ОАО "Ростелеком"</title>
    </head>
    <BODY leftmargin="15" topmargin="5" bottommargin="0" rightmargin="0" class='default' onload="onBodyLoad();">
        <?php
        popup_info_window(); // создаём пустое всплывающее информационное окно
//        echo "<TABLE border='0' width='100%' height='100%' cellspacing='0' cellpadding='0'>
//        <TR><TD height='61' width='33%'><img src='images/logo.png' style='MARGIN-LEFT: 20px;MARGIN-TOP: 10px;'></TD>
//        <TD height='61' align='left'>" . @$row_users["fio"] . "<br>
//            <div style'display:inline-block; padding:1px; border:solid 1px darkblue;'>
//                МЦТЭТ " . str_replace('select name=',
//                    "select onchange = 'document.cookie = \"mapfilter[mctet]=\"+encodeURIComponent(document.getElementsByName(\"mapfilter_mctet\")[0].value);' name=",
//                    select('mapfilter_mctet', "SELECT name FROM ps_mctet",$_REQUEST["mapfilter_mctet"],"выберите...")) . "
//            </div>
//        </TD></TR>
//        <TR>
//        <TD height='40' colspan='2'>";
        echo "<TABLE border='0' width='100%' height='100%' cellspacing='0' cellpadding='0'>
        <TR>
            <TD height='61' width='33%'><img src='images/logo_small.png' style='MARGIN-LEFT: 50px;MARGIN-TOP: 10px;'>
            <!--div style='display:inline-block; height: 60px; width: 160px; text-align: center; font-size: 24px' > \"Хора\"</div--></TD>";
            if(isset($row_users["fio"])){
                echo "<TD height='40' align='left'> Вы вошли как <b>" . @$row_users["fio"] . "</b>
                    <br>Ваша группа <b>". rSQL("SELECT name FROM ggroup WHERE id='" . $row_users["ugroup"] . "'")["name"] ."</b>
                    <br><span id='loadbodytext' style='color: red;'>Выполняется загрузка страницы</span>";
            }
        echo "</TD>
        </TR>
        <TR>
        <TD height='40' colspan='2'>";
        if (defined("LOGINED") == TRUE) {
            $result_gruop = qSQL("SELECT * FROM ggroup WHERE id='" . $row_users["ugroup"] . "'");
            $row_gruop = mysql_fetch_array($result_gruop);
            /* $q_settings = "SELECT * FROM gsettings;";
              $result_settings = mysql_query($q_settings) or die ("Query failed. settings");
              $row_settings = mysql_fetch_array($result_settings); */
            //$locate_glob = $_SERVER['REQUEST_URI'];
            //$query_uget = explode("?", $locate_glob);
            global $ubord;
            $ubord=new CBord($row_users["uid"],$row_users["ugroup"],$row_users["rid"]); // объект для разграничения доступа
            //d($_REQUEST);d("<br>");
            //if($row_users["ugroup"]==1){ d($_REQUEST); d($_COOKIE); d("<br>"); }
            if($row_users["uid"]==900){ d($_REQUEST); d($_COOKIE); d("<br>"); }
            echo "
            <table border='0' height='31' cellspacing='0' cellpadding='0'>
            <tr>
            <td width='50'>&nbsp;</td>
            <td width='2'><img src='./images/tl_bg.jpg'></td>
            <td background='./images/top_yellow_bg.jpg' align='center'><a href='./?c=99' class='menu' style='padding-left: 15px;padding-right: 15px;'>Справка</a></td>
            <td width='1'><img align='absmiddle' src='./images/sep_menu.jpg'></td>            
            <td background='./images/top_bg.jpg' align='center'><a href='./?c=0' class='menu' style='padding-left: 15px;padding-right: 15px;'>Главная</a></td>
            <td width='1'><img align='absmiddle' src='./images/sep_menu.jpg'></td>";            
            if($ubord->havePrivilege("G1 G2 G3 G5 G6 G7", "*"))
                echo "<td background='./images/top_bg.jpg' align='center'><a href='vlg_map.php' class='menu' style='padding-left: 15px;padding-right: 15px;'>Карта</a></td>";
            else
                echo "<td background='./images/top_bg.jpg' align='center'><a href='' class='menu' style='padding-left: 15px;padding-right: 15px;'> -X- </a></td>";
            echo "<td width='11'><img align='absmiddle' src='./images/sep_menu.jpg'></td>
            <td background='./images/top_bg.jpg' align='center'><a href='vlg_project.php' class='menu' style='padding-left: 15px;padding-right: 15px;'>Проекты</a></td>
            <td width='11'><img align='absmiddle' src='./images/sep_menu.jpg'></td>
            <td background='./images/top_bg.jpg' align='center'><a href='./?c=2&for_my_job=on' class='menu' style='padding-left: 15px;padding-right: 15px;'>Заявки</a></td>
            <td width='11'><img align='absmiddle' src='./images/sep_menu.jpg'></td>
            <td background='./images/top_bg.jpg' align='center'><a href='./?c=5' class='menu' style='padding-left: 15px;padding-right: 15px;'>Кластеры</a></td>
            <td width='11'><img align='absmiddle' src='./images/sep_menu.jpg'></td>
            ";            
            if($ubord->havePrivilege("G2 U900 U918 U1082", "*"))
                echo "<td background='./images/top_bg.jpg' align='center'>"
                    . "<a href='./?c=3' class='menu' style='padding-left: 15px;padding-right: 15px;'>Отчёты</a></td>"
                    . "<td width='11'><img align='absmiddle' src='./images/sep_menu.jpg'></td>";
            if($ubord->havePrivilege("G1", "*"))
                echo "<td background='./images/top_bg.jpg' align='center'><a href='./?c=6' class='menu' style='padding-left: 15px;padding-right: 15px;'>Загрузка/координаты</a></td>";
            else
                echo "<td background='./images/top_bg.jpg' align='center'><a href='' class='menu' style='padding-left: 15px;padding-right: 15px;'> -X- </a></td>";
            echo "
            <td width='11'><img align='absmiddle' src='./images/sep_menu.jpg'></td>";            
            if($ubord->havePrivilege("G1", "*"))
                echo "<td background='./images/top_bg.jpg' align='center'><a href='./?c=7' class='menu' style='padding-left: 15px;padding-right: 15px;'>Настройки</a></td>";
            else
                echo "<td background='./images/top_bg.jpg' align='center'><a href='' class='menu' style='padding-left: 15px;padding-right: 15px;'> -X- </a></td>";
            echo "
            <td width='11'><img align='absmiddle' src='./images/sep_menu.jpg'></td>
            <td background='./images/top_yellow_bg.jpg' align='center'><a href='./?action=logout' class='menu' style='padding-left: 15px;padding-right: 15px;'>Выход</a></td>
            <td width='2'><img src='./images/tr_bg.jpg'></td></tr>
            </table>";
        }
        ?>
        <script>
        // 
        function onBodyLoad(){
            document.getElementById('loadbodytext').innerHTML="";
        }
        </script>
    </TD>
</TR>
