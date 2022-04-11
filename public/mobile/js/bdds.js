 var  timeout  =   null ;  // setInterval函数句柄
 var  xmlHttp  =   false ;  //
// 初始化XMLHttpRequest对象
 function  createXmlHttp() {
    xmlHttp = false;
    if (window.ActiveXObject) {
        try {xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");} 
        catch (e) {xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");}
    }else if (window.XMLHttpRequest) {xmlHttp = new XMLHttpRequest();}
}
 function  sendRequest() {
    createXmlHttp();
    var url = "ajaxs.html?timestamp=" + new Date().getTime();
    if (!xmlHttp) {
        alert("XMLHttpRequest is not Create!");
    }
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = function(){//回调函数开始
        var tag = document.getElementById("container");
        tag.innerHTML = "";
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
        tag.innerHTML = xmlHttp.responseText;
        }
    }
xmlHttp.send(null);
}
timeout = window.setInterval("sendRequest()", 5000);
