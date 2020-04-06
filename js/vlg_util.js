// загрузить контент c MySQL для заданной страницы
// <!-- v. 2.0002 -->
// отправляем запрос quesAnsw на MySQL сервер по AJAX
// обработка на стороне сервера jSQL.php
// синхронный AJAX запрос
function SQL(mode,quesAnsw) {
    //alert(quesAnsw);
    var sendData="mode="+encodeURIComponent(mode)+"&ques="+encodeURIComponent(quesAnsw);
    if (xmlHttp) {
        try {
            xmlHttp.open("POST", "jSQLi.php", false); // false - запрос производится синхронно, true – асинхронно
            //xmlHttp.onreadystatechange = requestControl;
            xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            xmlHttp.send(sendData);
            // Продолжаем, только если статус HTTP равен "OK" 
            if (xmlHttp.status == 200) {
                try {
                    //if (typeof xmlHttp.psCallBackFunction != 'undefined'){
                    //    window[xmlHttp.psCallBackFunction](xmlHttp.responseText);
                    //} else {
                        //alert(xmlHttp.responseText);
                        // Чтение сообщения сервера 
                        response = xmlHttp.responseText;
                        if(mode=="multiselect"){
                        // Обрабатываем ответ сервера
                            //alert(response);
                            eval(response);
                        }
                        // !!! JSON.parse может неправильно отработать строки с кодировкой 1251
                        if(mode=="jsonselect"){
                            //alert(response);
                            return JSON.parse(response);
                        }
                        if(mode=="select"){
                        // Обрабатываем ответ сервера 
                            eval(response);
                        }
                    //}
                }
                catch (e) {
                    alert("Ошибка при обработке ответа сервера: " + e.toString());
                }
            } else {
                // Показываем статус ответа сервера 
                alert("Проблема с получением данных от сервера:\n" + xmlHttp.status);
            }
        }
        catch (e) {
            alert("Невозможно соединиться с сервером:\n" + e.toString());
        }
    }
}
// отправляем запрос quesAnsw на MySQL сервер по AJAX
// обработка на стороне сервера jSQL.php
// функция requestControl вызывается при изменении состояния AJAX запроса 
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
            xmlHttp.open("POST", "jSQL.php", true); // false - запрос производится синхронно, true – асинхронно
            //xmlHttp.open("POST", "loadContent.php", true);
            xmlHttp.onreadystatechange = requestControl;
            xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            //xmlHttp.setRequestHeader("Content-length",quesAnsw.length);
            //xmlHttp.setRequestHeader("Connection","close");
            xmlHttp.send(sendData);
        }
        catch (e) {
            alert("Невозможно соединиться с сервером:\n" + e.toString());
        }
    }
}
// JSON AJAX SQL запрос
// обработка на стороне сервера nSQL.php
// функция nRequestControl вызывается при изменении состояния AJAX запроса 
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
            alert("Невозможно соединиться с сервером:\n" + e.toString());
        }
    }
}
// Функция, вызываемая при изменении состояния AJAX запроса 
function requestControl() {
    var response,myDiv,pBuffer;
    //alert("requestControl");
    // Если readyState равно 4, то мы готовы обрабатывать ответ сервера 
    if (xmlHttp.readyState == 4) {
        // Продолжаем, только если статус HTTP равен "OK" 
        if (xmlHttp.status == 200) {
            try {
                if (typeof xmlHttp.psCallBackFunction != 'undefined'){
                    //alert(xmlHttp.responseText);
                    window[xmlHttp.psCallBackFunction](xmlHttp.responseText);
                } else {
                    // Чтение сообщения сервера 
                    response = xmlHttp.responseText;
                    // Обрабатываем ответ сервера 
                    //alert(response);
                    eval(response);
                }
            }
            catch (e) {
                alert("Ошибка при обработке ответа сервера: " + e.toString());
            }
        } else {
            // Показываем статус ответа сервера 
            alert("Проблема с получением данных от сервера:\n" +
                    xmlHttp.status+" \n"+
                    xmlHttp.responseText);
        }
    }
}
// JSON Функция, вызываемая при изменении состояния AJAX запроса 
function nRequestControl() {
    var response,myDiv,pBuffer;
    //alert("requestControl");
    // Если readyState равно 4, то мы готовы обрабатывать ответ сервера 
    if (xmlHttp.readyState == 4) {
        // Продолжаем, только если статус HTTP равен "OK" 
        if (xmlHttp.status == 200) {
            try {
                if (typeof xmlHttp.psCallBackFunction != 'undefined'){
                    //alert(xmlHttp.responseText);
                    window[xmlHttp.psCallBackFunction](xmlHttp.responseText);
                } else {
                    // Чтение сообщения сервера 
                    response = xmlHttp.responseText;
                    // Обрабатываем ответ сервера 
                    ///alert(response);
                    eval(response);
                }
            }
            catch (e) {
                alert("Ошибка при обработке ответа сервера: " + e.toString());
            }
        } else {
            // Показываем статус ответа сервера 
            alert("Проблема с получением данных от сервера:\n" +
                    xmlHttp.statusText);
        }
    }
}
// добавление скрытого элемента key к форме theForm со значением value
function addHidden(theForm, key, value) {
    // Create a hidden input element, and append it to the form:
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = key; // 'the key/name of the attribute/field that is sent to the server
    input.value = value;
    theForm.appendChild(input);
}
// показываем ошибку javascript в окне alert
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
// метод String - генерация хэшкода
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
  var cookie_date = new Date ( );  // Текущая дата и время
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