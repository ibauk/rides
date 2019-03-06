/*
 *
 * I B A U K - ibauk.js
 *
 * Copyright (c) 2017 Bob Stammers
 *
 * 2017-01	Include 'Omitted from RoH' status
 */
function isBadLength(sObj,iLen,sMsg) {

    if (sObj.value.length < iLen) {
      alert(sMsg)
      sObj.focus()
      return true
    }
}
function getObj(frmname,fname,fid) {

    x = "res=document." + frmname + '.' + fname + '$' + fid;
    res = '';
    eval(x);
    return res;

}

function todaysDate()
/* Return today's date as YYYY-MM-DD */
{
	var dt = new Date;
	return dt.toISOString().substr(0,10);
}

function ValidateDate(dtObj)
{
    var dt = new Date;
    var dta = dtObj.value.split('-');
    var yy,mm,dd;

    if ((dta.length == 3) && (dta[0].length == 4)) // probably ok
        return true;

//    alert("Parsing");
    dt.setTime(Date.parse(dtObj.value));
    yy = dt.getYear();
    mm = dt.getMonth();
    dd = dt.getDate();
    //alert("res=" + yy + "/" + mm + "/" + dd);
    if (isNaN(yy)) return false;
    if (isNaN(mm)) mm = 0;
    if (isNaN(dd)) dd = 1;
    dtObj.value = '' + yy + '-' + (mm+1) + '-' + dd;
    return true;
}


function setselects(slct,v)
{
    for (i=0;i<length(slct);i++) { slct[i].selected = v; }
}


 var tabLinks = new Array();
 var contentDivs = new Array();

function getHash( url ) {
      var hashPos = url.lastIndexOf ( '#' );
      return url.substring( hashPos + 1 );
    }
	
function getFirstChildWithTagName( element, tagName ) {
      for ( var i = 0; i < element.childNodes.length; i++ ) {
        if ( element.childNodes[i].nodeName == tagName ) return element.childNodes[i];
      }
    }
	

function showTab() {
      var selectedId = getHash( this.getAttribute('href') );

      // Highlight the selected tab, and dim all others.
      // Also show the selected content div, and hide all others.
      for ( var id in contentDivs ) {
        if ( id == selectedId ) {
          tabLinks[id].className = 'selected';
          contentDivs[id].className = 'tabContent';
        } else {
          tabLinks[id].className = '';
          contentDivs[id].className = 'tabContenthide';
        }
      }

      // Stop the browser following the link
      return false;
    }
function bodyLoaded()
{

	
// Grab the tab links and content divs from the page
      var tabListItems = document.getElementById('tabs').childNodes;
      for ( var i = 0; i < tabListItems.length; i++ ) {
        if ( tabListItems[i].nodeName == "LI" ) {
          var tabLink = getFirstChildWithTagName( tabListItems[i], 'A' );
          var id = getHash( tabLink.getAttribute('href') );
          tabLinks[id] = tabLink;
          contentDivs[id] = document.getElementById( id );
        }
      }

      // Assign onclick events to the tab links, and
      // highlight the first tab
      var i = 0;

      for ( var id in tabLinks ) {
        tabLinks[id].onclick = showTab;
        tabLinks[id].onfocus = function() { this.blur() };
        if ( i == 0 ) tabLinks[id].className = 'selected';
        i++;
      }

      // Hide all content divs except the first
      var i = 0;

      for ( var id in contentDivs ) {
        if ( i != 0 ) contentDivs[id].className = 'tabContenthide';
        i++;
      }
	  
	  var bikemerge = document.getElementById("MergeBikesButton");
	  if (bikemerge != null)
		  initBikeMerge();
		
}

function chooseBike()
{
	/*
	 * called during ride editing; fills in data fields from picklist
	 */
	var x = document.getElementById("BikeChoice");
	var ix = x.selectedIndex;
	var y = x.getElementsByTagName("option")[ix].value;
	var p = y.split("|");
	var bikeid = document.getElementById("bikeid");
	bikeid.value = p[0];
	var bikereg = document.getElementById("BikeReg");
	bikereg.value = p[1];
	var bx = document.getElementById("BikeText");
	bx.style.top = x.style.top;
	bx.style.left = x.style.left;
	if ( p[0] == 'newrec' )
	{
		bx.type = 'text';
		bikereg.readOnly = false;
	}
	else
	{
		bx.type = 'hidden';
		bikereg.readOnly = true;
	}
	
}

function initBikeMerge()
{
	/*
	 * called during rider editing; suppresses the [MergeBikes] button
	 * unless new bike specified and 2+ existing bikes selected
	 */
	 
	 //alert("Bollox");
	 var ok = true;
	 var x = document.getElementById("NewBikeMakeModel");
	 ok = x.value != ""; // New bike specified
	 if (ok)
	 {
		 var y = document.getElementsByName("SelectBike[]");
		 var sc = 0;
		 for (var i=0; i < y.length; i++)
			 if (y[i].checked)
				 sc++;
		ok = sc > 1;
	 }
 	 document.getElementById("MergeBikesButton").disabled = !ok;

}

function setRideDefaults()
{
	/* Set some field values depending on whether origin is UK or foreign
	 * called on change of OriginUK flag
	 */
	 var xForeignCert = document.getElementById('foreignCert').checked;
	 var foreignLit = '** NOT UK **';
	 if (xForeignCert)
	 {
		 if (document.getElementById("RideVerifier").value == '')
			 document.getElementById("RideVerifier").value = foreignLit;
		 document.getElementById("PayMethod").value = foreignLit;
		 document.getElementById("SentToUSA").checked = true;
		 document.getElementById("DontWantCertificate").checked = true;
		 document.getElementById("publishRoH").checked = true;
		 document.getElementById("AckSent").checked = true;
	 }
	 else
	 {
		 if (document.getElementById("RideVerifier").value == foreignLit)
			 document.getElementById("RideVerifier").value = '';
		 if (document.getElementById("PayMethod").value == foreignLit)
			 document.getElementById("PayMethod").value = '';
	 }
	 reflectCertOrigin();
	 setRideStatus();
	
}

function reflectCertOrigin()
{
	/* Flag various UI characteristics to distinguish
	 * foreign verifications - affects ride display only
	 *
	 */
	 var foreignColor = "red";
	 var localColor = "#fff0b3";
	 var xForeignCert = document.getElementById('foreignCert').checked;
	 var xDiv = document.getElementById('tab_ibadata');
	 if (!xDiv) return;
	 var yDiv = document.getElementById('tab_paydata');
	 
	 if (xForeignCert)
	 {
		 xDiv.style.backgroundColor = foreignColor;
		 yDiv.style.backgroundColor = foreignColor;
	 }
	 else
	 {
		 xDiv.style.backgroundColor = localColor;
		 yDiv.style.backgroundColor = localColor;
	 }
	
}

function setRideStatus()
{
	var res = '++++++++';
	/*alert('[' + document.getElementById('DateVerified').value + ']');*/
	if (document.getElementById('foreignCert').checked && document.getElementById('publishRoH').checked) {
		res = 'Complete';
	} else if (document.getElementById('isFailedRide').checked) {
		res = 'FAILED';
	} else if (document.getElementById('SentToUSA').checked && document.getElementById('publishRoH').checked) {
		res = 'COMPLETE';
	} else if (document.getElementById('publishRoH').checked) {
		res = 'Show RoH (not reported to USA)';
	} else if (document.getElementById('DatePayReq').value != '' && document.getElementById('DatePayRcvd').value == '') {
		res = 'Awaiting payment';
	} else if (document.getElementById('DateVerified').value != '' && document.getElementById('DatePayRcvd').value != '') {
		res = 'Omitted from RoH';
	} else if (document.getElementById('DateVerified').value != '') {
		res = 'Validated';
	} else if (document.getElementById('AckSent').checked) {
		res = 'Acknowledged, awaiting verification';
	} else {
		res = 'Received, not acknowledged';
	}
	//alert(res);
	document.getElementById('CurrentRideStatus').innerHTML = res;
}

function setCertificateName(obj)
{
	document.getElementById("NameOnCertificate").value = obj.value;
}

function doPaymentReceived()
{
	/* Called when DatePayRcvd is updated */
	document.getElementById('DateCertSent').value = document.getElementById('DatePayRcvd').value;
	document.getElementById("publishRoH").checked = document.getElementById('DoWantCertificate').checked;

}

function setRideFromRideID()
{
	/* Extract the ride name from the selected option */
	var sel = document.getElementById('IBA_RideID');
	var ride = sel.options[sel.selectedIndex].text;
	var txt = document.getElementById('IBA_Ride');
	txt.value = ride;
}
