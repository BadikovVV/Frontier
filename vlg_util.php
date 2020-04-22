<?php
//
// ������ SQL ��������
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
// ��������� ������� ������� � ���� 
// ����� ������� ����
function runPHPfunc($funcName)
{
    $url="http://localhost/phpServices.php?func=".$funcName;
    $port=80;
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_PORT, $port);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Host: ' . $_SERVER['HTTP_HOST']));

/*����� �������, ����� �� ���������� ������*/
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl_handle, CURLOPT_NOSIGNAL, 1);
    curl_setopt($curl_handle, CURLOPT_HEADER, false);
    curl_setopt($curl_handle, CURLOPT_NOBODY, true);
    curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT, true);

/*���� ������������ HTTPAUTH*/
    if( !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']) ) {
        curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl_handle, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
    }

/*��� ����� ����������, ����� ��� ��������� ��������� ����������� �������*/
    $code=crc32($funcName); //  � �������� ����  - ����������� ����� �������� �-���
    if( $code ) {
        curl_setopt($curl_handle, CURLOPT_COOKIE, 'curl_code=' . $code);
    }

/*���������*/
    curl_exec($curl_handle);
    curl_close($curl_handle);
    
}
// ����� ��������� � ��������
// ���������� ����� �� ��������������� ����������
function prot($lVar) {
    $fpn='/var/www/html/cs/buffer/psProt.txt';
    $fp = fopen($fpn, 'a');  
    if($fp){
        fwrite($fp,$lVar); 
        fwrite($fp,"\n\r"); 
        fclose($fp);
    } else {
        //d("�� ���� ������� ���� ��������� $fpn");
        //error_log(iconv('CP1251','UTF-8', "�� ���� ������� ���� ��������� $fpn"));
        error_log("prot $lVar");
    }
}
// echo �����
function e($text){
    echo "<br>".$text;
}
// ���� �������� ������� - echo �����
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
// ���� �������� ������� - ����������� ������
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
// ��������� �������� $_REQUEST[$key]
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
// �������� ����� ����� ������ ����� HTTP ��� HTTPS
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // ��������� ����������� HTTPS
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // ��������� ����������� HTTPS
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // ����� ���� �� �����
    $file_contents = curl_exec($ch);
    //sleep(1); // ����� ���� �� �����
    curl_close($ch);
    return $file_contents;
}
// �������� �����
function eMail($address,$subject,$message) {
    //error_log("email $address  $subject  $message");
    $res=false;
    if (DEBUG_MAIL_PS){
        $res=mail(DEBUG_MAIL_PS,$subject,"����� �������. ������ ��� ".$address."\r\n".$message,"From: PS \r\n"."X-Mailer: PHP/".phpversion()); 
    }else{
        $res=mail($address,$subject,$message,"From: PS \r\n"."X-Mailer: PHP/".phpversion());       
    }
    if(!$res){
        d("��������� ������ �� ���� ����������:");
        d($address);
        d($subject);
        d($message);
    }
return TRUE;
}
// ���������� SQL ������� � !!! ����� � ������ ������ !!!
// g��� ������ ���������� - ������, ������, ���� �������
function qSQL($query) {
    $res = mysql_query($query);
    if (!$res) {
        echo "<br>$query";
        echo "<br>Err.no." . mysql_errno() . ": " . mysql_error() . "\n";
        $debug=debug_backtrace(); 
        $errmsg="";
        $count=count($debug);     
        for ($i=1; $i<$count; $i++) { // ������������ � �������, ��� ��� ������� ������� -- ��� ����� ������ ����������� error_handler       
        $errmsg.='<li>'.$debug[$i]['function'].'()'.
          ' � '.((isset($debug[$i]['file'])) ? $debug[$i]['file'] : '����������� ����').', '.
          '������ '.((isset($debug[$i]['line'])) ? $debug[$i]['line'] : '����������').'</li>'; //.var_dump($debug[$i]['args'])     
        }
        echo $errmsg;
        exit();
    }
    return $res;
}
////////////////////////////////////////////////////////////////////////////////
// ���������� SQL �������
function SQL($query) {
    return new CSQL($query);
}
// ���������� SQL ������� � ���������� �������
function dSQL($query) {
    d($query);
    return new CSQL($query);
}
// ������ ������ �� ������ ������ SQL �������
function rSQL($query,$arrmode=MYSQLI_ASSOC) {
    $rSQL_cursor=new CSQL($query);
    if ($arrmode==MYSQLI_ASSOC)   $rSQL_cursor->assoc();
    else                          $rSQL_cursor->farray();
    $rSQL_cursor->free();
    return $rSQL_cursor->r;
}
// ������ �� SQL �������
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
    // �������������� ������, ��� ���� ����� ��������� ������ �������� �������� ? (������������)
    ($stmt->prepare(�SELECT title FROM sk2_articles WHERE id > ? and id < ?�) ===FALSE)
    // ����������� ���������� � �������������
    or ($stmt->bind_param('ii', $id_min, $id_max) === FALSE)
    // ����������� �����, ������� �� ������ ������ ��������� � ����������� ����������
    or ($stmt->execute() === FALSE)
    // ���������� ��������� ��� ��������� � ��� ����������
    or ($stmt->bind_result($title) === FALSE)
    // ������ ������ ����������������, 
    // ���� �� ���� ������ �� ����, ������ ��� �� �����������������
    or ($stmt->store_result() === FALSE)
    // ��������� ���������� � ����������� ����������
    or ($stmt->fetch() === FALSE)
    // ��������� �������������� ������
    or ($stmt->close() === FALSE)
    ) {
    die('Select Error (' . $stmt->errno . ') ' . $stmt->error);
    }
    echo $title;
 */
    function query(){
        // MYSQLI_STORE_RESULT � ������ ���������������� ���������, �������� �� ���������
        // MYSQLI_USE_RESULT � ������������������
        $this->result=$this->link->query($this->query,MYSQLI_STORE_RESULT);
        // �� ��� ������� MySQLi ����������� ������ PHP, ��������� ���� ������� �� �� �����. 
        // � ������ ���� ������ �������� � ������ ������ ������, PHP �� ���� �� ���� �����. 
        // ��� �������� ����������� �������: $mysqli->error � �������� ������ $mysqli->errno � ��� ������
        if (!$this->result or $this->link->errno) {
            echo "<br>$this->query";
            echo "<br>Err.no." . $this->link->errno . ": " . $this->link->error . "\n";
            $debug=debug_backtrace(); 
            $errmsg="";
            $count=count($debug);     
            for ($i=1; $i<$count; $i++) { // ������������ � �������, ��� ��� ������� ������� -- ��� ����� ������ ����������� error_handler       
            $errmsg.='<li>'.$debug[$i]['function'].'()'.
              ' � '.((isset($debug[$i]['file'])) ? $debug[$i]['file'] : '����������� ����').', '.
              '������ '.((isset($debug[$i]['line'])) ? $debug[$i]['line'] : '����������').'</li>'; //.var_dump($debug[$i]['args'])     
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
// �������� ���� ������ �� ��������������� ������ � �������� �� � ������������� ������
    function assoc(){
        $this->r=$this->result->fetch_assoc();
        // vvv �������� vvv
        //$this->name=$this->r["name"]; 
        // ^^^ �������� ^^^
        return $this->r;
    }
// �������� ���� ������ �� ��������������� ������ � �������� �� � ������� ������
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
// ���������� ID, ������������ �������� (������ INSERT) � �������, ������� �������� ������� � ��������� AUTO_INCREMENT. 
// ���� ��������� ������ �� ��� INSERT ��� UPDATE ��� � �������������� ������� ����������� ������� � ��������� AUTO_INCREMENT, 
// ������ ������� ������ ����
    function insert_id(){
        return $this->link->insert_id;
    }
// ���������� ����� �����, ���������� ��������� INSERT, UPDATE, REPLACE ��� DELETE ��������
    function affected_rows(){
        return $this->link->affected_rows;
    }
//
    function commit(){
        return $this->link->commit();
    }
}
////////////////////////////////////////////////////////////////////////////////
// ���������� SQL ������� � ������� ������ ������ (����� � ������ ������)
// �������� - ����������� rSQL
function fSQL($query) {
    exit("�������� - ����������� rSQL");
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
// ���������� SQL ������� - ����� �������
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
    // ���������� ����� � ����� ������ � ����� out
    function t2($ld, $lstr) {
        echo "<" . $ld . ">" . $lstr . "</" . $ld . ">";
    }

    // ���������� ����� c ����������� � ����� ������ � ����� out
    function t3($ld, $lp, $lstr) {
        echo "<" . $ld . " " . $lp . ">" . $lstr . "</" . $ld . ">";
    }
// �������� ������� "select" �� ������ ������� � ��
// select(���,���������� � ��,������,��������� ��������,�������� �� ���������)
function select($elName,$query,$initial="",$default="��� ��������"){
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
// �������� ������� "select" �� ������ ������� � �� �� ���� ����� (���,�������������)
// select(���,���������� � ��,������,��������� ��������,�������� �� ���������)
// ������ - select '��� ��������',-1 union SELECT concat(id,' ',name),id FROM ps_teh_podkl
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
// �������� ������� "select" �� ������ ������� � �� �� ���� ����� (���,�������������)
// select(���,���������� � ��,������,��������� ��������,�������� �� ���������)
// ������ - select '��� ��������',-1 union SELECT concat(id,' ',name),id FROM ps_teh_podkl
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
// �������� ������� "select" �� ������ �������
// selArr(���,������,��������� ��������,�������� �� ���������)
// selArr('���',array('1', '2', '3', '4', '5', '6'),'5','�������� �� ���������');
function selArr($elName,$arr,$initial="",$default="��� ��������"){
    $result="";
    $arrlen=count($arr);
    if($arrlen<1)
    {
        echo "������ ������ ��� �������� select <br>";
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
// HTML ������� select
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
            if(is_array($this->query)){ // ������ select �� �������
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
            } else { // ������ select �� �������
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
            echo '��������� ����������: ',  $e->getMessage(), "\n";
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
// ������ checkbox � ���� (<div>) �� ����������
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
            echo '��������� ����������: ',  $e->getMessage(), "\n";
        }
    }
} 
//
////////////////////////////////////////////////////////////////////////////////
// HTML ������� radio �� ������ �������
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
            $this->htmlname = $htmlname; // ��� input ��������
            $this->query = $query; // ������: 1-� ������� - �����; 2 - id; 3 - ������� ��� <br>
            $this->value=$value; // ������������� ��������
            $this->cursor=SQL($query);
            $this->newline=$newline; // true - ��������� <br> if($this->cursor->r[2]!=$group_by)
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
            echo '��������� ����������: ',  $e->getMessage(), "\n";
        }
    }
//
} 
////////////////////////////////////////////////////////////////////////////////
// �������������� ������ ��� ������ - ������� � �������
// ������������ ������������
function addressFormat($searchaddress) {
    $res=mb_strtoupper(trim($searchaddress),"CP1251");
    
//    echo "[".$res."]<br>";
//    echo "<br>". $res=preg_replace("/(,|;|:|!|\?)/"," ",$res)."<br>";
//    echo "<br>". $res=preg_replace(
//        "/(���\.|�\.�\.|��\.| �� |��\.|�\.|��.|�\.|��\.|�\.|�\.|�\.|���\.| � |�\.|�\.|�\.|��\.|��\.|��|�����|���( *)�����)/"," ",$res)."<br>";
//    echo "<br>". $res=preg_replace("/����[�-�]+\s���([�-�\.])*/"," ",$res)."<br>";    
//    echo "<br>".preg_replace("/(\s)+/","%",$res) ."<br>";
    
    $res=preg_replace("/(,|;|:|!|\?)/"," ",$res);
    $res=preg_replace("/(^|[\s,])�[\s\.,]/"," ����� ",$res);    
    $res=preg_replace(
        "/(^|[\s,])(���|�\.�|��|��|�|��|��-��|�|��|�|��|�|�|���|�|�|�|��|��|�����|�\/�|���( *)�����|��( *)�������)[\s\.,]/"," ",$res);
    $res=preg_replace("/����[�-�]+\s���([�-�\.])*/"," ",$res);    
    $res=str_ireplace("|||", ", ", $res);
     
    return "������������� ������� ".trim($res);
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
    $precision = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision; // �������� ����������� ����������. exact - ������, near -������ ��� � �������, ������� � ������������

    //$foundresults=$xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->results;
    $foundresults=$status;

    echo "<br>���������� ��������: [" . $foundresults . "]";
    $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
    echo "���������� �������: [" . $cords . "]";
    //if ($status > 0) {
    //if ($precision=='exact' or $precision=='number' or $precision=='near' or $precision=='manual') {
        $cords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
        //list ($lat, $lng) = explode(" ", $cords);
        list ($lng, $lat) = explode(" ", $cords);
        $formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // ��������������� ����� Yandex
        $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // ����������� ����� � cp1251
        $post_index = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->postal_code; // �������� ������
        $place_id = 'YANDEX'; // ���������� ������������� ������� � Yandex
        //echo "<br>����� Yandex: [" . $formatted_address . "] ������: " . $post_index . " ��������: " . $precision . "";
        // GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision // exact - ������ ������������
        $_latlng = $lat . ":" . $lng;
        
        return array ($_latlng, $post_index, $formatted_address, $precision);
        
        for($i=1;$i<$foundresults;$i++){
            echo "<br>������� ". (1+$i) .": ".iconv('UTF-8', 'CP1251',$xml->GeoObjectCollection->featureMember[$i]->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted);
        }
////////////////////////////////////////////////////////////////////////////////
        // vvv GOOGLE vvv
//        $string = getUrl('http://maps.google.com/maps/api/geocode/xml?address=' . urlencode(iconv('CP1251', 'UTF-8', $searchaddress)) . '&language=ru');
//        d(iconv('UTF-8', 'CP1251',urldecode($string)));d("<br>");
//        $xml = simplexml_load_string($string);
//        //print_r($xml);
//        $status = $xml->status;
//        if ($status == "OK") {
//            echo "<br>������� ���������� ��� " . $searchaddress;
//            $lat = $xml->result->geometry->location->lat;
//            $lng = $xml->result->geometry->location->lng;
//            $formatted_address = $xml->result->formatted_address; // ��������������� ����� ������
//            $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // ����������� ����� � cp1251
//            $place_id = $xml->result->place_id; // ���������� ������������� ������� � ����
//            $location_type = $xml->result->geometry->location_type; // �������� ����������� ����������. ROOFTOP - ������, � ������ �������� �������.
//            $_latlng = $lat . ":" . $lng;
//            echo "<br>���������� = " . $_latlng;
//            /*$result_update1 = qSQL("update ps_list set latlng='" . $_latlng . "' where list_id='" . $row_cids["list_id"] . "'");
//            if (@$location_type == 'ROOFTOP') {
//                $add_claster = 1;
//            }
//            $result_update2 = qSQL("update ps_list_dop set formatted_address='" . @$formatted_address . 
//                "', place_id='" . @$place_id . "', location_type='" . @$location_type . "' where list_id='" . $row_cids["list_id"] . "'");*/
//            $k++;
//        } else
//            echo "<br><i style='color: red'>���������� �� ���������� !!!</i>";
        // ^^^ GOOGLE ^^^
////////////////////////////////////////////////////////////////////////////////
        echo "<br>";
}
////////////////////////////////////////////////////////////////////////////////
// ��������
// �������������� ������ ��� ������ - ������� � ��������
function addressFormat2($searchaddress) {
    //echo "<br>".$searchaddress;
    $searchaddress=str_ireplace(",", " ", $searchaddress); // ����� �������� � ��������� ������� � ����� ������
    $searchaddress=str_ireplace(" ��� �����", " ", $searchaddress);
    $searchaddress=str_ireplace(" ����� ", " ", $searchaddress);
    $searchaddress=str_ireplace("���.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.�.", " ", $searchaddress);
    $searchaddress=str_ireplace("��.", " ", $searchaddress);
    $searchaddress=str_ireplace(" �� ", " ", $searchaddress);
    $searchaddress=str_ireplace("��.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace("��.", " ", $searchaddress);
    $searchaddress=str_ireplace("��.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace(" � ", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace("�.", " ", $searchaddress);
    $searchaddress=str_ireplace("��.", " ", $searchaddress);
    $searchaddress=str_ireplace(" ��.", " ", $searchaddress);
    //echo "<br>".$searchaddress;
    $searchaddress=str_ireplace(" �� ", " ", $searchaddress);
    $searchaddress=str_ireplace(" �� ", " ", $searchaddress);    
    //echo "<br>".$searchaddress;    
    $searchaddress=str_ireplace("|||", ", ", $searchaddress);
    $searchaddress=str_ireplace("   ", " ", $searchaddress);
    $searchaddress=str_ireplace("  ", " ", $searchaddress);
    $searchaddress=str_ireplace("  ", " ", $searchaddress);
    $searchaddress=trim($searchaddress); // 
    if(stristr($searchaddress,'������������� �������') === FALSE) $searchaddress='������������� �������, '.$searchaddress;
    
    return $searchaddress;
}
// ����������� ��������� ������ $address (���������� geocode-maps.yandex.ru)
function coordFix($address) {
    $address=addressFormat2($address);
    //e("---- ".$address);
    $string = getUrl('https://geocode-maps.yandex.ru/1.x/?geocode=' . urlencode(iconv('CP1251', 'UTF-8', $address)));
    $xml = simplexml_load_string($string);
    $coords='';
    $status = $xml->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found;
    //echo " [" . $status . "]"; // ���������� ��������� ��������
    if ($status > 0) {
        $coords = $xml->GeoObjectCollection->featureMember->GeoObject->Point->pos;
        //list ($lat, $lng) = explode(" ", $coords);
        list ($lng, $lat) = explode(" ", $coords);
        $formatted_address = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted; // ��������������� ����� Yandex
        $formatted_address = iconv('UTF-8', 'CP1251', $formatted_address); // ����������� ����� � cp1251
        echo "<br>����� Yandex [" . $formatted_address . "]";
        $post_index = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->Address->postal_code; // �������� ������
        echo " ����.������ [" . $post_index . "]";
        $place_id = 'YANDEX'; // ���������� ������������� ������� � Yandex
        $precision = $xml->GeoObjectCollection->featureMember->GeoObject->metaDataProperty->GeocoderMetaData->precision; // �������� ����������� ����������. exact - ������, near -������ ��� � �������, ������� � ������������
        echo " �������� [" . $precision . "]"; // exact - ������ ������������
    }
    return $coords;
}
////////////////////////////////////////////////////////////////////////////////
// ����������� �������������� ����
function popup_info_window(){
    echo "<div id='info_window_darkening' class='ps_popup_darkening'> 
            <div id='info_window_main' class='ps_popup_main_window'> 
                <a class='ps_popup_close_button' title='�������' 
                    onclick='document.getElementById(\"info_window_darkening\").style.display = \"none\";'>X</a>
                <div style=\" overflow: auto;     
                    max-width: 600px;
                    max-height: 800px; \" 
                    id='info_window_message'></div>
        </div></div>
        ";
}
// ^^ ����������� �������������� ���� ^^
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
// ������ �� ���.�������
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
// ������ �� ����������� �� ���.�������
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