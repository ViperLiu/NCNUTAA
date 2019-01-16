var data;
window.onload = function(){
    show_data();
}
var arr = new Array();
var count=0;
var isSelectAll = false;
function show_data(){
    var show="";
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            data = JSON.parse(xhttp.responseText);
            for(var i = 0;data[i] != null;i++){
                if(data[i] == null)
                    break;
                if(data[i][4]=="")
                    continue;
                if(count%2==0)
                    show+="<tr>";
                show+="<td data-mail="+data[i][4]+" data-index="+count+" onclick=\"selectName(this)\">";
                for(var j = 0;data[0][j] != null;j++){
                    if(j==0 || j==1 || j==2 || j==4){
                        show+=data[i][j];
                        show+=" ";
                    }
                }
                show+="</td>";
                if(count%2==1)
                    show+="</tr>";
                count++;
                arr.push(0);
            }
            document.getElementById("content").innerHTML = show;
        }
    };
    xhttp.open("POST", "show_data.php", true);
    xhttp.send();
}
function selectName(x){
    if(arr[x.dataset.index] != 0){
        x.style.background = "#FFF";
        arr[x.dataset.index] = 0;
    }
    else{
        x.style.background = "#AAF";
        arr[x.dataset.index] = x.dataset.mail;
    }
}
function selectAll(){
    var x = document.getElementsByTagName("td");
    if(!isSelectAll){
        isSelectAll = true;
        for(var i=0;i<x.length;i++){
            x[i].style.background = "#AAF";
            arr[x[i].dataset.index] = x[i].dataset.mail;
        }
    }
    else{
        isSelectAll = false;
        for(var i=0;i<x.length;i++){
            x[i].style.background = "#FFF";
            arr[x[i].dataset.index] = 0;
        }
    }
}
function sendMail(){
    var xhttp = new XMLHttpRequest();
    var arr2 = new Array();
    for(var i=0;i<arr.length;i++){
        if(arr[i] != 0)
            arr2.push(arr[i]);
    }
    var receivers = encodeURIComponent(JSON.stringify(arr2));
    var subject = encodeURIComponent(document.getElementById("subject").value);
    var body = encodeURIComponent(document.getElementById("body").value);
    var parameters="receivers="+receivers+"&subject="+subject+"&body="+body;
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState == 4 && xhttp.status == 200) {
            alert(xhttp.responseText);
            document.location.href="mail.html";
        }
    };
    xhttp.open("POST", "send.php", true);
    xhttp.setRequestHeader("Content-type", "text/plain");
    xhttp.send(parameters);
}