<?php
//
// фильтр SQL иньекции
function noSQLInj($text){
  //$loc_res='';
  //$name = filter_input(INPUT_GET, 't');
  //$name = $mysqli->real_escape_string($name);
  if(preg_match('/(;|\'|select)/i', $text))
  {
    return false;            
  } else {
    return true;            
  }
}
// процедура запуска функции в фоне 
// через внешний файл
function runPHPfunc($funcName)
{
    $url="http://localhost/phpServices.php?func=".$funcName;
    $port=80;
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_PORT, $port);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Host: ' . $_SERVER['HTTP_HOST']));

/*Опции запроса, чтобы не дожидаться ответа*/
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl_handle, CURLOPT_NOSIGNAL, 1);
    curl_setopt($curl_handle, CURLOPT_HEADER, false);
    curl_setopt($curl_handle, CURLOPT_NOBODY, true);
    curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT, true);

/*Если используется HTTPAUTH*/
    if( !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']) ) {
        curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl_handle, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
    }

/*Код можно прикрепить, чтобы при обработке проверить подлинность запроса*/
    $code=crc32($funcName); //  в качестве кода  - контрольная сумма названия ф-ции
    if( $code ) {
        curl_setopt($curl_handle, CURLOPT_COOKIE, 'curl_code=' . $code);
    }

/*Выполняем*/
    curl_exec($curl_handle);
    curl_close($curl_handle);
    
}
// вывод сообщения в протокол
// необходимы права на соответствующую директорию
function prot($lVar) {
    $fpn='/var/www/html/cs/buffer/psProt.txt';
    $fp = fopen($fpn, 'a');  
    if($fp){
        fwrite($fp,$lVar); 
        fwrite($fp,"\n\r"); 
        fclose($fp);
    } else {
        //d("Не могу открыть файл протокола $fpn");
        //error_log(iconv('CP1251','UTF-8', "Не могу открыть файл протокола $fpn"));
        error_log("prot $lVar");
    }
}
// echo текст
function e($text){
    echo "<br>".$text;
}
// если включена отладка - echo текст
function d($text){
    if (DEBUG_PS){
        print_r("<br>");
        if(is_array($text)){
            foreach ($text as $key => $value){
                print_r("$key => $value; ");
            }
        }else{
            print_r("".$text);
        }
    }
}
// если включена отладка - рекурсивная печать
function drec($text){
    if (DEBUG_PS){
        print_r("<br>");
        if(is_array($text)){
            foreach ($text as $key => $value){
                if(is_array($value)) drec($value);
                else print_r("$key => $value; ");
            }
        }else{
            print_r("".$text);
        }
    }
}
// получение значения $_REQUEST[$key]
function getReq($key){
    if(isset($_REQUEST[$key])){
        return $_REQUEST[$key];
    } else {
        return null;
    }
}
// 
function syncReqCook($arrname,$keyname,$default){
    if(isset($_REQUEST[$arrname."_".$keyname])){
        setcookie($arrname.'['.$keyname.']', $_REQUEST[$arrname."_".$keyname]);
    } elseif(isset($_COOKIE[$arrname][$keyname])){
        $_REQUEST[$arrname."_".$keyname]=$_COOKIE[$arrname][$keyname];
    } else {
        setcookie($arrname.'['.$keyname.']', $default);
        $_REQUEST[$arrname."_".$keyname]=$default;
    }
}
// загрузка файла через прокси через HTTP или HTTPS
function getUrl($url) {
    $ch = curl_init();
    $timeout = 0; // set to zero for no timeout 
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_PROXY, "http://10.147.2.108"); //your proxy url
//    curl_setopt($ch, CURLOPT_PROXYPORT, "3128"); // your proxy port number 
//    curl_setopt($ch, CURLOPT_PROXYUSERPWD, "name:password"); //username:pass 
    curl_setopt($ch, CURLOPT_PROXY, "http://10.147.13.108"); //your proxy url
    curl_setopt($ch, CURLOPT_PROXYPORT, "3128"); // your proxy port number 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // отключает возможности HTTPS
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // отключает возможности HTTPS
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // может быть не нужна
    $file_contents = curl_exec($ch);
    //sleep(1); // может быть не нужна
    curl_close($ch);
    return $file_contents;
}
// отправка почты
function eMail($address,$subject,$message) {
    //error_log("email $address  $subject  $message");
    $res=false;
    if (DEBUG_MAIL_PS){
        $res=mail(DEBUG_MAIL_PS,$subject,"Режим отладки. Письмо для ".$address."\r\n".$message,"From: PS \r\n"."X-Mailer: PHP/".phpversion()); 
    }else{
        $res=mail($address,$subject,$message,"From: PS \r\n"."X-Mailer: PHP/".phpversion());       
    }
    if(!$res){
        d("Следующее письмо не было отправлено:");
        d($address);
        d($subject);
        d($message);
    }
return TRUE;
}
// исполнение SQL запроса и !!! выход в случае ошибки !!!
// gпри ошибке показывает - запрос, ошибку, стек вызовов
function qSQL($query) {
    $res = mysql_query($query);
    if (!$res) {
        echo "<br>$query";
        echo "<br>Err.no." . mysql_errno() . ": " . mysql_error() . "\n";
        $debug=debug_backtrace(); 
        $errmsg="";
        $count=count($debug);     
        for ($i=1; $i<$count; $i++) { // обрабатываем с единицы, так как нулевой элемент -- это вызов самого обработчика error_handler       
        $errmsg.='<li>'.$debug[$i]['function'].'()'.
          ' — '.((isset($debug[$i]['file'])) ? $debug[$i]['file'] : 'неизвестный файл').', '.
          'строка '.((isset($debug[$i]['line'])) ? $debug[$i]['line'] : 'неизвестна').'</li>'; //.var_dump($debug[$i]['args'])     
        }
        echo $errmsg;
        exit();
    }
    return $res;
}
////////////////////////////////////////////////////////////////////////////////
// исполнение SQL запроса
function SQL($query) {
    return new CSQL($query);
}
// исполнение SQL запроса с отладочной печатью
function dSQL($query) {
    d($query);
    return new CSQL($query);
}
// массив ТОЛЬКО по первой строке SQL запроса
function rSQL($query,$arrmode=MYSQLI_ASSOC) {
    $rSQL_cursor=new CSQL($query);
    if ($arrmode==MYSQLI_ASSOC)   $rSQL_cursor->assoc();
    else                          $rSQL_cursor->farray();
    $rSQL_cursor->free();
    return $rSQL_cursor->r;
}
// курсор по SQL запросу
class CSQL{
    var $link;
    var $query;
    var $result;
    var $r;
//
    function __construct($query)
    {
        global $mysqli;
        $this->link = $mysqli;
        $this->query = $query;
        //$this->result=$this->link->query($this->query);
        $this->query();
    }
//
/* * * * $stmt->prepare * * * *
    $id_min = 81;
    $id_max = 88;
    $stmt = $mysqli->stmt_init();
    if(
    // подготовливаем запрос, там куда будут вствлятся данные отмечаем символом ? (плейсхолдоры)
    ($stmt->prepare(«SELECT title FROM sk2_articles WHERE id > ? and id < ?») ===FALSE)
    // привязываем переменные к плейсхолдорам
    or ($stmt->bind_param('ii', $id_min, $id_max) === FALSE)
    // отрправляем даные, которые на данный момент находятся в привязанных переменных
    or ($stmt->execute() === FALSE)
    // привязывем переменую для получения в нее результата
    or ($stmt->bind_result($title) === FALSE)
    // делаем запрос буферизированным, 
    // если бы этой строки не было, запрос был бы небуферезированым
    or ($stmt->store_result() === FALSE)
    // получение результата в привязанную переменную
    or ($stmt->fetch() === FALSE)
    // закрываем подготовленный запрос
    or ($stmt->close() === FALSE)
    ) {
    die('Select Error (' . $stmt->errno . ') ' . $stmt->error);
    }
    echo $title;
 */
    function query(){
        // MYSQLI_STORE_RESULT – вернет буферизированный результат, значение по умолчанию
        // MYSQLI_USE_RESULT – небуферизированный
        $this->result=$this->link->query($this->query,MYSQLI_STORE_RESULT);
        // не все функции MySQLi выбрасывают ошибки PHP, описанные выше функции из их числа. 
        // В случае если запрос неверный и сервер вернул ошибку, PHP не даст об этом знать. 
        // Для проверки используйте функции: $mysqli->error – описание ошибки $mysqli->errno – код ошибки
        if (!$this->result or $this->link->errno) {
            echo "<br>$this->query";
            echo "<br>Err.no." . $this->link->errno . ": " . $this->link->error . "\n";
            $debug=debug_backtrace(); 
            $errmsg="";
            $count=count($debug);     
            for ($i=1; $i<$count; $i++) { // обрабатываем с единицы, так как нулевой элемент -- это вызов самого обработчика error_handler       
            $errmsg.='<li>'.$debug[$i]['function'].'()'.
              ' — '.((isset($debug[$i]['file'])) ? $debug[$i]['file'] : 'неизвестный файл').', '.
              'строка '.((isset($debug[$i]['line'])) ? $debug[$i]['line'] : 'неизвестна').'</li>'; //.var_dump($debug[$i]['args'])     
            }
            echo $errmsg;
            exit();
        }
        return $this->result;
    }
    function sql($query){
        $this->query = $query;
        $this->query();
        return $this->result;
    }
// Выбирает одну строку из результирующего набора и помещает ее в ассоциативный массив
    function assoc(){
        $this->r=$this->result->fetch_assoc();
        // vvv работает vvv
        //$this->name=$this->r["name"]; 
        // ^^^ работает ^^^
        return $this->r;
    }
// Выбирает одну строку из результирующего набора и помещает ее в обычный массив
    function farray(){
        $this->r=$this->result->fetch_array(MYSQLI_NUM);

        return $this->r;
    }
// 
    function free(){
        return $this->result->free();
    }
//
    function data_seek($offset){
        return $this->result->data_seek($offset);
    }
// возвращает ID, генерируемый запросом (обычно INSERT) к таблице, которая содержит колонку с атрибутом AUTO_INCREMENT. 
// Если последний запрос не был INSERT или UPDATE или в модифицируемой таблице отсутствует колонка с атрибутом AUTO_INCREMENT, 
// данная функция вернет ноль
    function insert_id(){
        return $this->link->insert_id;
    }
// Возвращает число строк, затронутых последним INSERT, UPDATE, REPLACE или DELETE запросом
    function affected_rows(){
        return $this->link->affected_rows;
    }
//
    function commit(){
        return $this->link->commit();
    }
}
////////////////////////////////////////////////////////////////////////////////
// исполнение SQL запроса и возврат первой строки (выход в случае ошибки)
// устарело - используйте rSQL
function fSQL($query) {
    exit("устарело - используйте rSQL");
    $res = mysql_query($query);
    if (!$res) {
        echo "<br>$query";
        echo "<br>Err.no." . mysql_errno() . ": " . mysql_error() . "\n";
        exit();
    }
    $first_row = mysql_fetch_array($res);
    if (!$first_row) {
        //echo "<br>$query";
        //echo "<br>Err.no." . mysql_errno() . ": " . mysql_error() . "\n";
        //exit();
        return $first_row;
    }
    return $first_row;
}
// исполнение SQL запроса - можно удалить
//function SQL($query) {
//    $res = mysql_query($query);
//    if (!$res) {
//        echo "<br>$query";
//        echo "<br>Err.no." . mysql_errno() . ": " . mysql_error() . "\n";
//    }
//    return $res;
//}
/////////////////////////////////// H T M L ///////////////////////////////////
//class HTML {
    // Добавление тегов и вывод текста в поток out
    function t2($ld, $lstr) {
        echo "<" . $ld . ">" . $lstr . "</" . $ld . ">";
    }

    // Добавление тегов c параметрами и вывод текста в поток out
    function t3($ld, $lp, $lstr) {
        echo "<" . $ld . " " . $lp . ">" . $lstr . "</" . $ld . ">";
    }
// собираем элемент "select" на основе запроса к БД
// select(имя,соединение с БД,запрос,начальное значение,значение по умолчанию)
function select($elName,$query,$initial="",$default="все варианты"){
    $result="";
    //mysql_select_db($SQLSchema,$ms);
    $res=qSQL($query);
        $result=$result."<select name=\"".$elName."\" >";
        if($default==""){
            $row=mysql_fetch_array($res);
            if($row[0]==$initial)
                $result=$result."<option selected>".$row[0]."</option>" ;
            else
                $result=$result."<option>".$row[0]."</option>" ;
        } else {
            if($default==$initial)
                $result=$result."<option selected>".$default."</option>" ;
            else
                $result=$result."<option>".$default."</option>" ;
        }
        while($row=mysql_fetch_array($res)) {
            if($row[0]==$initial)
                $result=$result."<option selected>".$row[0]."</option>" ;
            else
                $result=$result."<option>".$row[0]."</option>" ;
        }
        $result=$result."</select>";
    return $result;
}
// собираем элемент "select" на основе запроса к БД из ДВУХ полей (имя,идентификатор)
// select(имя,соединение с БД,запрос,начальное значение,значение по умолчанию)
// пример - select 'все варианты',-1 union SELECT concat(id,' ',name),id FROM ps_teh_podkl
function select2($elName,$query,$initial=""){
    $result="";
    //mysql_select_db($SQLSchema,$ms);
    $res=qSQL($query);
        $result=$result."<select name=\"".$elName."\" >";
            $row=mysql_fetch_array($res);
            if($row[0]==$initial)
                $result=$result."<option value=\"".$row[1]."\" selected>".$row[0]."</option>" ;
            else
                $result=$result."<option value=\"".$row[1]."\">".$row[0]."</option>" ;

        while($row=mysql_fetch_array($res)) {
            if($row[0]==$initial)
                $result=$result."<option value=\"".$row[1]."\" selected>".$row[0]."</option>" ;
            else
                $result=$result."<option value=\"".$row[1]."\">".$row[0]."</option>" ;
        }
        $result=$result."</select>";
    return $result;
}
// multiple select 
// собираем элемент "select" на основе запроса к БД из ДВУХ полей (имя,идентификатор)
// select(имя,соединение с БД,запрос,начальное значение,значение по умолчанию)
// пример - select 'все варианты',-1 union SELECT concat(id,' ',name),id FROM ps_teh_podkl
/*function mSelect($elName,$query,$initial=""){
    $result="";
    //mysql_select_db($SQLSchema,$ms);
    $res=qSQL($query);
        $result=$result."<select name=\"".$elName."\" multiple='multiple'>";
            $row=mysql_fetch_array($res);
            if($row[0]==$initial)
                $result=$result."<option value=\"".$row[1]."\" selected>".$row[0]."</option>" ;
            else
                $result=$result."<option value=\"".$row[1]."\">".$row[0]."</option>" ;

        while($row=mysql_fetch_array($res)) {
            if($row[0]==$initial)
                $result=$result."<option value=\"".$row[1]."\" selected>".$row[0]."</option>" ;
            else
                $result=$result."<option value=\"".$row[1]."\">".$row[0]."</option>" ;
        }
        $result=$result."</select>";
    return $result;
}*/
// собираем элемент "select" на основе массива
// selArr(имя,массив,начальное значение,значение по умолчанию)
// selArr('имя',array('1', '2', '3', '4', '5', '6'),'5','значение по умолчанию');
function selArr($elName,$arr,$initial="",$default="все варианты"){
    $result="";
    $arrlen=count($arr);
    if($arrlen<1)
    {
        echo "пустой массив для элемента select <br>";
    } else {
        $i=0;
        $result=$result."<select name=\"".$elName."\" >";
        if($default==""){
            if($arr[0]==$initial)
                $result=$result."<option selected>".$arr[0]."</option>" ;
            else
                $result=$result."<option>".$arr[0]."</option>" ;
            $i++;
        } else {
            if($default==$initial)
                $result=$result."<option selected>".$default."</option>" ;
            else
                $result=$result."<option>".$default."</option>" ;
        }
        while($i<$arrlen) {
            if($arr[$i]==$initial)
                $result=$result."<option selected>".$arr[$i]."</option>" ;
            else
                $result=$result."<option>".$arr[$i]."</option>" ;
            $i++;
        }
        $result=$result."</select>";
    }
    return $result;
}
//
////////////////////////////////////////////////////////////////////////////////
// HTML элемент select
class CSelect{
    public $htmlname;
    public $query;
    public $htmlel;
    public $value;
    public $requestname;
    private $cursor;
    private $sOption; // 
    protected $style;
//
    function __construct($query,$htmlname,$value=-1,$requestname="",$sOption="") {
        //d($value);
        try {
            $this->htmlname = $htmlname;
            $this->query = $query;
            
            if(strpos($sOption, 'multiple')===false){
                $this->value=$value;
            }else{
                $this->value=explode(",",$value);
            }
            $this->sOption=$sOption;
            $this->htmlel="<select name=\"".$this->htmlname ."\" ".$this->sOption .">";
            //d($this->query);
            if(is_array($this->query)){ // делаем select из массива
                foreach ($this->query as &$value) {
                    if(strpos($sOption, 'multiple')===false){
                        $this->htmlel.="<option value=\"".$value[1] ."\" ".
                            (($value[1]==$this->value)? "selected" : " ") .">".$value[0] ."</option>" ;
                    }else{
                        $this->htmlel.="<option value=\"".$value[1] ."\" ".
                            ((array_search($value[1],$this->value)===false)? " " : "selected") .">".$value[0] ."</option>" ;
                    }
                    //d($this->htmlel);

                }
            } else { // делаем select из запроса
                $this->cursor=SQL($query);
                while ($this->cursor->farray()) {
                    if(strpos($sOption, 'multiple')===false){
                        $this->htmlel.="<option value=\"".$this->cursor->r[1] ."\" ".
                            (($this->cursor->r[1]==$this->value)? "selected" : " ") .">".$this->cursor->r[0] ."</option>" ;
                    }else{
                        //d($this->cursor->r[1]);
                        //d($this->value);
                        $this->htmlel.="<option value=\"".$this->cursor->r[1] ."\" ".
                            ((array_search($this->cursor->r[1],$this->value)===false)? " " : "selected") .">".$this->cursor->r[0] ."</option>" ;
                    }
                }
                $this->cursor->free();
            }
            $this->htmlel.="</select>";  
        } catch (Exception $e) {
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
    }
//
    function initvalue() {
        if(isset($_REQUEST[$this->requestname])){
            setcookie($this->requestname, $_REQUEST[$this->requestname]);
        } elseif(isset($_COOKIE[$this->requestname])){
            $_REQUEST[$this->requestname]=$_COOKIE[$this->requestname];
        } else {
            setcookie($this->requestname, "-1");
            $_REQUEST[$this->requestname]="-1";
        }
    }
} 
////////////////////////////////////////////////////////////////////////////////
// Список checkbox в окне (<div>) со скролингом
class CCheckBoxList{
    public $htmlname;
    public $query;
    public $htmlel;
    public $value;
    public $requestname;
    private $cursor;
    private $sOption; // 
    protected $style;
//
    function __construct($query,$htmlname,$value=-1,$requestname="",$sOption="") {
        try {
            $this->htmlname = $htmlname;
            $this->query = $query;
            $this->value=explode(",",$value);
            $this->cursor=SQL($query);
            $this->sOption=$sOption;
            $this->htmlel.="<div ".$this->sOption .">";
            while ($this->cursor->farray()) {
                $this->htmlel.="<input type=checkbox name=\"".$this->htmlname ."\" value=\"".$this->cursor->r[1] ."\" ".
                    ((array_search($this->cursor->r[1],$this->value)===false)? " " : "checked") .">".$this->cursor->r[0] ."<br>" ;
            }
            $this->cursor->free();
            $this->htmlel.="</div>";  
        } catch (Exception $e) {
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
    }
} 
//
////////////////////////////////////////////////////////////////////////////////
// HTML элемент radio на основе запроса
class CRadio{
    public $htmlname;
    public $query;
    public $htmlel;
    public $value;
    public $requestname;
    private $cursor;
    private $sOption; // 
    protected $style;
//
    function __construct($query,$htmlname,$value=-1,$newline=true,$requestname="",$sOption="") {
        //d($value);
        try {
            $this->htmlname = $htmlname; // имя input элемента
            $this->query = $query; // запрос: 1-я колонка - текст; 2 - id; 3 - признак для <br>
            $this->value=$value; // установленное значение
            $this->cursor=SQL($query);
            $this->newline=$newline; // true - выполняем <br> if($this->cursor->r[2]!=$group_by)
            $this->sOption=$sOption;
            $this->htmlel=" ";
            $group_by=false;
            
            while ($this->cursor->farray()) {
                if($this->newline){
                    if($group_by){
                        if($this->cursor->r[2]!=$group_by){ 
                            $this->htmlel.="<br>";
                            $group_by=$this->cursor->r[2];
                        }
                    } else {
                         $group_by=$this->cursor->r[2];
                    }
                }
                $this->htmlel.="<input type=radio name=\"".$this->htmlname ."\" ".$this->sOption ." value=\"".$this->cursor->r[1] ."\" ".
                        (($this->cursor->r[1]==$this->value)? "checked" : " ") .">".$this->cursor->r[0] ." " ;
            }
            $this->cursor->free();
            $this->htmlel.=" ";  
        } catch (Exception $e) {
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }
    }
//
} 
////////////////////////////////////////////////////////////////////////////////
// форматирование адреса для поиска - вариант с плюсами
// использовать нежелательно
function addressFormat($searchaddress) {
    $res=mb_strtoupper(trim($searchaddress),"CP1251");
    
//    echo "[".$res."]<br>";
//    echo "<br>". $res=preg_replace("/(,|;|:|!|\?)/"," ",$res)."<br>";
//    echo "<br>". $res=preg_replace(
//        "/(ПЕР\.|Р\.П\.|РП\.| РП |ПР\.|Р\.|СТ.|Г\.|УЛ\.|П\.|Б\.|Х\.|БУЛ\.| Г |С\.|Ш\.|Д\.|КВ\.|ИМ\.|ИМ|ИМЕНИ|НЕТ( *)УЛИЦЫ)/"," ",$res)."<br>";
//    echo "<br>". $res=preg_replace("/ВОЛГ[А-Я]+\sОБЛ([А-Я\.])*/"," ",$res)."<br>";    
//    echo "<br>".preg_replace("/(\s)+/","%",$res) ."<br>";
    
    $res=preg_replace("/(,|;|:|!|\?)/"," ",$res);
    $res=preg_replace("/(^|[\s,])Х[\s\.,]/"," ХУТОР ",$res);    
    $res=preg_replace(
        "/(^|[\s,])(ПЕР|Р\.П|РП|ПР|Р|СТ|СТ-ЦА|Г|УЛ|П|ПЛ|Б|Х|БУЛ|С|Ш|Д|КВ|ИМ|ИМЕНИ|Б\/Н|НЕТ( *)УЛИЦЫ|НЕ( *)УКАЗАНА)[\s\.,]/"," ",$res);
    $res=preg_replace("/ВОЛГ[А-Я]+\sОБЛ([А-Я\.])*/"," ",$res);    
    $res=str_ireplace("|||", ", ", $res);
     
    return "ВОЛГОГРАДСКАЯ ОБЛАСТЬ ".trim($res);
}
////////////////////////////////////////////////////////////////////////////////
//
function addressFormat6($region,$district=false,$settlement=false,$street=false,$house=false,$building=false) {
    if($district==false and $settlement==false and $street==false){
        return addressFormat($region);
    } else {
        $district=explode(" ",mb_strtoupper(trim($district),"CP1251"))[0];
        $settlement=mb_strtoupper(trim($settlement),"CP1251");
        if(strstr($settlement,$district)){
            //$district=" ";
            $settlement=preg_replace("/(".$district.")([\s\.,]|$)/"," ",$settlement);
        }
        $searchaddress=$district." ".$settlement." ||| ".trim($street)." ||| ".trim($house);
        if(empty(trim($building))){

        }else{
            $searchaddress.=" ||| ".trim($building);
        }
        //echo $searchaddress."<br>";
        return addressFormat($searchaddress);
    }
}
////////////////////////////////////////////////////////////////////////////////
function getCoord($searchaddress){
    echo "<br> --- ".$searchaddress;
    // yandex.ru
    $string = getUrl('https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)));
    //print_r($string);
    $xml = simplexml_load_string($string);
    $status=$xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found;
    $precision = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision; // Точность определения координаты. exact - точный, near -Найден дом с номером, близким к запрошенному

    //$foundresults=$xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->results;
    $foundresults=$status;

    echo "<br>Количество объектов: [" . $foundresults . "]";
    $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
    echo "Координаты объекта: [" . $cords . "]";
    //if ($status > 0) {
    //if ($precision=='exact' or $precision=='number' or $precision=='near' or $precision=='manual') {
        $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
        //list ($lat, $lng) = explode(" ", $cords);
        list ($lng, $lat) = explode(" ", $cords);
        $formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // Форматированный адрес Yandex
        $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
        $post_index = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->postal_code; // Почтовый индекс
        $place_id = 'YANDEX'; // Уникальный идентификатор объекта в Yandex
        //echo "<br>Адрес Yandex: [" . $formatted_address . "] индекс: " . $post_index . " Точность: " . $precision . "";
        // GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision // exact - Точное соответствие
        $_latlng = $lat . ":" . $lng;
        
        return array ($_latlng, $post_index, $formatted_address, $precision);
        
        for($i=1;$i<$foundresults;$i++){
            echo "<br>вариант ". (1+$i) .": ".iconv('UTF-8', 'CP1251',$xml->GeoObjectCollection->featureMember[$i]->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted);
        }
////////////////////////////////////////////////////////////////////////////////
        // vvv GOOGLE vvv
//        $string = getUrl('http://maps.google.com/maps/api/geocode/xml?address=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)) . '&language=ru');
//        d(iconv('UTF-8', 'CP1251',urldecode($string)));d("<br>");
//        $xml = simplexml_load_string($string);
//        //print_r($xml);
//        $status = $xml->status;
//        if ($status == "OK") {
//            echo "<br>нашлись координаты для " . $searchaddress;
//            $lat = $xml->result->geometry->location->lat;
//            $lng = $xml->result->geometry->location->lng;
//            $formatted_address = $xml->result->formatted_address; // Форматированный адрес гуглом
//            $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
//            $place_id = $xml->result->place_id; // Уникальный идентификатор объекта в гугл
//            $location_type = $xml->result->geometry->location_type; // Точность определения координаты. ROOFTOP - точный, с точным почтовым адресом.
//            $_latlng = $lat . ":" . $lng;
//            echo "<br>координаты = " . $_latlng;
//            /*$result_update1 = qSQL("update ps_list set latlng='" . $_latlng . "' where list_id='" . $row_cids["list_id"] . "'");
//            if (@$location_type == 'ROOFTOP') {
//                $add_claster = 1;
//            }
//            $result_update2 = qSQL("update ps_list_dop set formatted_address='" . @$formatted_address . 
//                "', place_id='" . @$place_id . "', location_type='" . @$location_type . "' where list_id='" . $row_cids["list_id"] . "'");*/
//            $k++;
//        } else
//            echo "<br><i style='color: red'>координаты НЕ определены !!!</i>";
        // ^^^ GOOGLE ^^^
////////////////////////////////////////////////////////////////////////////////
        echo "<br>";
}
////////////////////////////////////////////////////////////////////////////////
// УСТАРЕЛО
// форматирование адреса для поиска - вариант с запятыми
function addressFormat2($searchaddress) {
    //echo "<br>".$searchaddress;
    $searchaddress=str_ireplace(",", " ", $searchaddress); // может привести к появлению пробела в конце строки
    $searchaddress=str_ireplace(" Нет улицы", " ", $searchaddress);
    $searchaddress=str_ireplace(" имени ", " ", $searchaddress);
    $searchaddress=str_ireplace("пер.", " ", $searchaddress);
    $searchaddress=str_ireplace("р.п.", " ", $searchaddress);
    $searchaddress=str_ireplace("рп.", " ", $searchaddress);
    $searchaddress=str_ireplace(" рп ", " ", $searchaddress);
    $searchaddress=str_ireplace("пр.", " ", $searchaddress);
    $searchaddress=str_ireplace("р.", " ", $searchaddress);
    $searchaddress=str_ireplace("ст.", " ", $searchaddress);
    $searchaddress=str_ireplace("ул.", " ", $searchaddress);
    $searchaddress=str_ireplace("п.", " ", $searchaddress);
    $searchaddress=str_ireplace("б.", " ", $searchaddress);
    $searchaddress=str_ireplace("х.", " ", $searchaddress);
    $searchaddress=str_ireplace(" г ", " ", $searchaddress);
    $searchaddress=str_ireplace("г.", " ", $searchaddress);
    $searchaddress=str_ireplace("с.", " ", $searchaddress);
    $searchaddress=str_ireplace("ш.", " ", $searchaddress);
    $searchaddress=str_ireplace("д.", " ", $searchaddress);
    $searchaddress=str_ireplace("кв.", " ", $searchaddress);
    $searchaddress=str_ireplace(" им.", " ", $searchaddress);
    //echo "<br>".$searchaddress;
    $searchaddress=str_ireplace(" им ", " ", $searchaddress);
    $searchaddress=str_ireplace(" ИМ ", " ", $searchaddress);    
    //echo "<br>".$searchaddress;    
    $searchaddress=str_ireplace("|||", ", ", $searchaddress);
    $searchaddress=str_ireplace("   ", " ", $searchaddress);
    $searchaddress=str_ireplace("  ", " ", $searchaddress);
    $searchaddress=str_ireplace("  ", " ", $searchaddress);
    $searchaddress=trim($searchaddress); // 
    if(stristr($searchaddress,'ВОЛГОГРАДСКАЯ ОБЛАСТЬ') === FALSE) $searchaddress='Волгоградская область, '.$searchaddress;
    
    return $searchaddress;
}
// определение координат адреса $address (используем geocode-maps.yandex.ru)
function coordFix($address) {
    $address=addressFormat2($address);
    //e("---- ".$address);
    $string = getUrl('https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode(iconv('CP1251', 'UTF-8', $address)));
    $xml = simplexml_load_string($string);
    $coords='';
    $status = $xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found;
    //echo " [" . $status . "]"; // Количество найденных объектов
    if ($status > 0) {
        $coords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
        //list ($lat, $lng) = explode(" ", $coords);
        list ($lng, $lat) = explode(" ", $coords);
        $formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // Форматированный адрес Yandex
        $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // Преобразуем адрес в cp1251
        echo "<br>адрес Yandex [" . $formatted_address . "]";
        $post_index = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->postal_code; // Почтовый индекс
        echo " Почт.индекс [" . $post_index . "]";
        $place_id = 'YANDEX'; // Уникальный идентификатор объекта в Yandex
        $precision = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision; // Точность определения координаты. exact - точный, near -Найден дом с номером, близким к запрошенному
        echo " Точность [" . $precision . "]"; // exact - Точное соответствие
    }
    return $coords;
}
////////////////////////////////////////////////////////////////////////////////
// всплывающее информационное окно
function popup_info_window(){
    echo "<div id='info_window_darkening' class='ps_popup_darkening'> 
            <div id='info_window_main' class='ps_popup_main_window'> 
                <a class='ps_popup_close_button' title='Закрыть' 
                    onclick='document.getElementById(\"info_window_darkening\").style.display = \"none\";'>X</a>
                <div style=\" overflow: auto;     
                    max-width: 600px;
                    max-height: 800px; \" 
                    id='info_window_message'></div>
        </div></div>
        ";
}
// ^^ всплывающее информационное окно ^^
////////////////////////////////////////////////////////////////////////////////
//
class Polygon {
    protected $polygon = array();
    /**
     * Polygon itself, with basic vector-based structure
     * Array: [ [1,1], [2,1], [3,0], [2,-1] ]
     *
     * @var $polygon array
     */
    public function set_polygon($polygon) {
        if (count($polygon)<3) return false;
        if (!isset($polygon[0]['x'])) {
            foreach ($polygon as &$point) {
                $point = array('x' => $point[0], 'y' => $point[1]);
            }
        }
        $this->polygon = $polygon;
    }
    /**
     * Check if $polygon contains $test value
     *
     * @var $test array(x=>decimal, y=>decimal)
     */
    public function calc($test) {
        //echo $test;
        $q_patt= array( array(0,1), array(3,2) );
        $end = end($this->polygon);
        $pred_pt = end($this->polygon);
        $pred_pt['x'] -= $test['x'];
        $pred_pt['y'] -= $test['y'];
        $pred_q = $q_patt[$pred_pt['y']<0][$pred_pt['x']<0];
        $w = 0;
        for ($iter = reset($this->polygon); $iter!==false;$iter=next($this->polygon)) {
            $cur_pt = $iter;
            $cur_pt['x'] -= $test['x'];
            $cur_pt['y'] -= $test['y'];
            $q = $q_patt[$cur_pt['y']<0][$cur_pt['x']<0];
            switch ($q-$pred_q) {
                case -3:
                    ++$w;
                    break;
                case 3:
                    --$w;
                    break;
                case -2:
                    if ($pred_pt['x']*$cur_pt['y']>=$pred_pt['y']*$cur_pt['x'])
                        ++$w;
                    break;
                case 2:
                    if (!($pred_pt['x']*$cur_pt['y']>=$pred_pt['y']*$cur_pt['x']))
                        --$w;
                    break;
            }
            $pred_pt = $cur_pt;
            $pred_q = $q;
        }
        //return $w!=0;
		$w = abs($w);
        return $w;
    }
}
//
////////////////////////////////////////////////////////////////////////////////
// запрос по физ.объёмам
function com_object_query($type,$locId){
    return "SELECT ccmid,ccm.cnaid,cna.cnaname,ccm.subeid,sube.ename subename,ccm.oid,o.oname ooname,ccm.cxid,cx.cxname,
		    ccm.technology,tp.name tpname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.bid,b.bname,ccm.rcmid,rcm.name rcmname,pld.arm_id,ccm.lid,
                    max(ccm.ccmname) ccmname,
                    sum(ccm.amount) ccmamount,sum(ccmlen) ccmlen,
                    sum(ccm.capacity1) ccmcapacity1,sum(ccm.capacity2) ccmcapacity2,
                    max(ccm.price) ccmprice,max(ccm.comment) comment,
                    max(rcm.mgroup) rcmmgroup,max(rcm.pgroup) rcmpgroup,
                    max(rcm.price) rcmprice,max(rcm.unit) rcmunit,max(rcm.capacity1) rcmcapacity1,max(rcm.capacity2) rcmcapacity2
                FROM call_com_mat ccm 
                left join ref_com_mat rcm using(rcmid)
                left join cn_eq_type cet using(cetid) 
                left join ps_list_dop pld on ccm.stype=1 and pld.lid=ccm.lid
                left join sign_envir se on ccm.seid=se.seid
                left join cn_area cna on ccm.cnaid=cna.cnaid
                left join cn_envir ce on ccm.ceid=ce.ceid
                left join builder b on ccm.bid=b.bid
                left join subexpense sube on ccm.subeid=sube.seid
                left join owner o on ccm.oid=o.oid
                left join complexity cx on ccm.cxid=cx.cxid
                left join ps_teh_podkl tp on ccm.technology=tp.id
                where ccm.stype=".$type." and ccm.lid".$locId ." 
                group by ccm.cnaid,cna.cnaname,ccm.seid,se.sename,rcm.cetid,cet.cetname,
                    ccm.ceid,ce.cename,ccm.rcmid,rcm.name,pld.arm_id,ccm.lid,ccmid
		order by ccm.cnaid,ccm.seid,rcm.cetid,ccm.ceid,ccm.rcmid,ccm.lid
                ";
}
// запрос по СПРАВОЧНИКА по физ.объёмам
function ref_com_object_query($where=" ",$order=" "){
    return "SELECT rcm.mgroup,rcm.pgroup,rcm.cetid,cet.cetname,rcm.rcmid,rcm.name rcmname,rcm.comment,
                        rcm.price rcmprice,rcm.unit rcmunit,rcm.capacity1 rcmcapacity1,rcm.capacity2 rcmcapacity2,
                        rcm.cnaid,cna.cnaname,rcm.seid,se.sename,rcm.ceid,ce.cename,
                        rcm.eid,exp.ename ename,rcm.subeid,sube.ename subename,rcm.oid,o.oname ooname,rcm.cxid,cx.cxname,
                        rcm.technology,tp.name tpname,rcm.bid,b.bname
                    FROM ref_com_mat rcm 
                    left join cn_eq_type cet using(cetid) 
                    left join sign_envir se on rcm.seid=se.seid
                    left join cn_area cna on rcm.cnaid=cna.cnaid
                    left join cn_envir ce on rcm.ceid=ce.ceid
                    left join builder b on rcm.bid=b.bid
                    left join expense exp on rcm.eid=exp.eid
                    left join subexpense sube on rcm.subeid=sube.seid
                    left join owner o on rcm.oid=o.oid
                    left join complexity cx on rcm.cxid=cx.cxid
                    left join ps_teh_podkl tp on rcm.technology=tp.id ".
                    $where .$order;
}
////////////////////////////////////////////////////////////////////////////////

?>