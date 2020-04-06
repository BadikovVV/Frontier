// ��������� ������� c MySQL ��� �������� ��������
// <!-- v. 2.0002 -->
// ���������� ������ quesAnsw �� MySQL ������ �� AJAX
// ��������� �� ������� ������� jSQL.php
// ���������� AJAX ������
function SQL(mode,quesAnsw) {
    //alert(quesAnsw);
    var sendData="mode="+encodeURIComponent(mode)+"&ques="+encodeURIComponent(quesAnsw);
    if (xmlHttp) {
        try {
            xmlHttp.open("POST", "jSQLi.php", false); // false - ������ ������������ ���������, true � ����������
            //xmlHttp.onreadystatechange = requestControl;
            xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xmlHttp.send(sendData);
            // ����������, ������ ���� ������ HTTP ����� "OK" 
            if (xmlHttp.status == 200) {
                try {
                    //if (typeof xmlHttp.psCallBackFunction != 'undefined'){
                    //    window[xmlHttp.psCallBackFunction](xmlHttp.responseText);
                    //} else {
                        //alert(xmlHttp.responseText);
                        // ������ ��������� ������� 
                        response = xmlHttp.responseText;
                        if(mode=="multiselect"){
                        // ������������ ����� �������
                            //alert(response);
                            eval(response);
                        }
                        // !!! JSON.parse ����� ����������� ���������� ������ � ���������� 1251
                        if(mode=="jsonselect"){
                            //alert(response);
                            return JSON.parse(response);
                        }
                        if(mode=="select"){
                        // ������������ ����� ������� 
                            eval(response);
                        }
                    //}
                }
                catch (e) {
                    alert("������ ��� ��������� ������ �������: " + e.toString());
                }
            } else {
                // ���������� ������ ������ ������� 
                alert("�������� � ���������� ������ �� �������:\n" + xmlHttp.status);
            }
        }
        catch (e) {
            alert("���������� ����������� � ��������:\n" + e.toString());
        }
    }
}
// ���������� ������ quesAnsw �� MySQL ������ �� AJAX
// ��������� �� ������� ������� jSQL.php
// ������� requestControl ���������� ��� ��������� ��������� AJAX ������� 
function jSQL(mode,quesAnsw) {
    //alert(quesAnsw);
    var sendData="mode="+encodeURIComponent(mode)+"&ques="+encodeURIComponent(quesAnsw);
    if (xmlHttp) {
        try {
        // GET
//            xmlHttp.open("GET", "../../../../php/loadContent.php?" + quesAnsw, true);
//            xmlHttp.onreadystatechange = requestControl;
//            xmlHttp.send(null);
        // POST
            //xmlHttp.open("POST", "jSQL.php", true);
            xmlHttp.open("POST", "jSQL.php", true); // false - ������ ������������ ���������, true � ����������
            //xmlHttp.open("POST", "loadContent.php", true);
            xmlHttp.onreadystatechange = requestControl;
            xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            //xmlHttp.setRequestHeader("Content-length",quesAnsw.length);
            //xmlHttp.setRequestHeader("Connection","close");
            xmlHttp.send(sendData);
        }
        catch (e) {
            alert("���������� ����������� � ��������:\n" + e.toString());
        }
    }
}
// JSON AJAX SQL ������
// ��������� �� ������� ������� nSQL.php
// ������� nRequestControl ���������� ��� ��������� ��������� AJAX ������� 
function nSQL(quesAnsw) {
    //alert(quesAnsw);
    var sendData="ques="+encodeURIComponent(quesAnsw);
    if (xmlHttp) {
        try {
            xmlHttp.open("POST", "nSQL.php", true);
            xmlHttp.onreadystatechange = nRequestControl;
            xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xmlHttp.send(sendData);
        }
        catch (e) {
            alert("���������� ����������� � ��������:\n" + e.toString());
        }
    }
}
// �������, ���������� ��� ��������� ��������� AJAX ������� 
function requestControl() {
    var response,myDiv,pBuffer;
    //alert("requestControl");
    // ���� readyState ����� 4, �� �� ������ ������������ ����� ������� 
    if (xmlHttp.readyState == 4) {
        // ����������, ������ ���� ������ HTTP ����� "OK" 
        if (xmlHttp.status == 200) {
            try {
                if (typeof xmlHttp.psCallBackFunction != 'undefined'){
                    //alert(xmlHttp.responseText);
                    window[xmlHttp.psCallBackFunction](xmlHttp.responseText);
                } else {
                    // ������ ��������� ������� 
                    response = xmlHttp.responseText;
                    // ������������ ����� ������� 
                    //alert(response);
                    eval(response);
                }
            }
            catch (e) {
                alert("������ ��� ��������� ������ �������: " + e.toString());
            }
        } else {
            // ���������� ������ ������ ������� 
            alert("�������� � ���������� ������ �� �������:\n" +
                    xmlHttp.status+" \n"+
                    xmlHttp.responseText);
        }
    }
}
// JSON �������, ���������� ��� ��������� ��������� AJAX ������� 
function nRequestControl() {
    var response,myDiv,pBuffer;
    //alert("requestControl");
    // ���� readyState ����� 4, �� �� ������ ������������ ����� ������� 
    if (xmlHttp.readyState == 4) {
        // ����������, ������ ���� ������ HTTP ����� "OK" 
        if (xmlHttp.status == 200) {
            try {
                if (typeof xmlHttp.psCallBackFunction != 'undefined'){
                    //alert(xmlHttp.responseText);
                    window[xmlHttp.psCallBackFunction](xmlHttp.responseText);
                } else {
                    // ������ ��������� ������� 
                    response = xmlHttp.responseText;
                    // ������������ ����� ������� 
                    ///alert(response);
                    eval(response);
                }
            }
            catch (e) {
                alert("������ ��� ��������� ������ �������: " + e.toString());
            }
        } else {
            // ���������� ������ ������ ������� 
            alert("�������� � ���������� ������ �� �������:\n" +
                    xmlHttp.statusText);
        }
    }
}
// ���������� �������� �������� key � ����� theForm �� ��������� value
function addHidden(theForm, key, value) {
    // Create a hidden input element, and append it to the form:
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = key; // 'the key/name of the attribute/field that is sent to the server
    input.value = value;
    theForm.appendChild(input);
}
// ���������� ������ javascript � ���� alert
/*window.onerror = function (msg, url, lineNo, columnNo, error) {
    var string = msg.toLowerCase();
    var substring = "script error";
    if (string.indexOf(substring) > -1){
        alert('Script Error: See Browser Console for Detail');
    } else {
        var message = [
            'Message: ' + msg,
            'URL: ' + url,
            'Line: ' + lineNo,
            'Column: ' + columnNo,
            'Error object: ' + JSON.stringify(error)
        ].join(' - ');

        alert(message);
    }
    return false;
};*/
// ����� String - ��������� �������
String.prototype.hashCode = function(){
    var hash = 0;
    if (this.length == 0) return hash;
    for (i = 0; i < this.length; i++) {
        char = this.charCodeAt(i);
        hash = ((hash<<5)-hash)+char;
        hash = hash & hash; // Convert to 32bit integer
    }
    return hash;
}
//
function get_cookie ( cookie_name )
{
  var results = document.cookie.match ( '(^|;) ?' + cookie_name + '=([^;]*)(;|$)' );
 
  if ( results )
    return ( unescape ( results[2] ) );
  else
    return null;
}
//
function delete_cookie ( cookie_name )
{
  var cookie_date = new Date ( );  // ������� ���� � �����
  cookie_date.setTime ( cookie_date.getTime() - 1 );
  document.cookie = cookie_name += "=; expires=" + cookie_date.toGMTString();
}
//
function set_cookie ( name, value, exp_y, exp_m, exp_d, path, domain, secure )
{
  var cookie_string = name + "=" + escape ( value );
 
  if ( exp_y )
  {
    var expires = new Date ( exp_y, exp_m, exp_d );
    cookie_string += "; expires=" + expires.toGMTString();
  }
 
  if ( path )
        cookie_string += "; path=" + escape ( path );
 
  if ( domain )
        cookie_string += "; domain=" + escape ( domain );
  
  if ( secure )
        cookie_string += "; secure";
  
  document.cookie = cookie_string;
}
//
function hexToRGBA(hex, opacity) {
    return 'rgba(' + (hex = hex.replace('#', '')).match(new RegExp('(.{' + hex.length/3 + 
        '})', 'g')).map(function(l) { return parseInt(hex.length%2 ? l+l : l, 16) }).concat(opacity||1).join(',') + ')';
}
//
function rGBAToHex(rgba) {
    rgba = rgba.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgba && rgba.length === 4) ? "#" +
        ("0" + parseInt(rgba[1],10).toString(16)).slice(-2) +
        ("0" + parseInt(rgba[2],10).toString(16)).slice(-2) +
        ("0" + parseInt(rgba[3],10).toString(16)).slice(-2) : '';
}