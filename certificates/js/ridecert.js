/*
 * ridecert.js
 *
 */
 
function appendParam(pname,pvalue)
{
	var parm = document.createElement("input");
	parm.setAttribute("type","hidden");
	parm.setAttribute("name",pname);
	parm.setAttribute("value",pvalue);
	return parm;
}
function saveCertificate()
{
	var uri = document.getElementById("URI").innerHTML;
	var dt = new Date();
	
	var x = document.getElementsByTagName("title");
	for ( var i = 0; i < x.length; i++) {
		x[i].innerHTML = "Ride #"+uri+" certificate; saved "+dt.toUTCString();
	}
	var txt = document.documentElement.innerHTML;
	
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "index.php");
	form.appendChild(appendParam("cmd","savecert"));
	form.appendChild(appendParam("URI",uri));
	form.appendChild(appendParam("certtext",txt));
    form.submit();
}
function fixCertVars()
{
	var x = document.getElementsByTagName("title");
	for ( var i = 0; i < x.length; i++) {
		document.getElementById("toolbar_title").innerHTML = x[i].innerHTML;
	}
}

function updateImg(img)
{
	var a = document.createElement('a');
	a.href = img.src;
    var filename = a.pathname.split("/").pop();	
	var uri = document.getElementById("URI").innerHTML;
	window.location = "certimages.php?callback="+window.location+"&uri="+uri+"&imgid="+img.id+"&imgFile="+filename;
}
