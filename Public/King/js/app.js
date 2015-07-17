/**
 * 通过企业审核
 * @return {[type]} [description]
 */
function doIt(url,parameters){

	var xmlhttp;
	// 兼容  IE7+, Firefox, Chrome, Opera, Safari
	if(window.XMLHttpRequest){
	  xmlhttp=new XMLHttpRequest();
	}
	// 兼容 for IE6, IE5
	else{
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function(){
		if(xmlhttp.readyState==4 && xmlhttp.status==200){
			document.location.reload();
		}
	}
	url += parameters;
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
}


function shownotRead(){
    notRead=document.getElementById("notRead");
    havePass=document.getElementById("havePass");
    notPass=document.getElementById("notPass");
    notRead.style.display = "block";
    havePass.style.display = "none";
    notPass.style.display = "none";
}
function showhavePass(){
    notRead=document.getElementById("notRead");
    havePass=document.getElementById("havePass");
    notPass=document.getElementById("notPass");
    notRead.style.display = "none";
    havePass.style.display = "block";
    notPass.style.display = "none";
}
function shownotPass(){
    notRead=document.getElementById("notRead");
    havePass=document.getElementById("havePass");
    notPass=document.getElementById("notPass");
    notRead.style.display = "none";
    havePass.style.display = "none";
    notPass.style.display = "block";
}