<?php
/*
 * I B A U K - riderecs.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 * 2017-01	Set Deleted=N as appropriate
 */

$RIDES_SQL  = "SELECT SQL_CALC_FOUND_ROWS *, rides.Deleted as RideDeleted FROM rides LEFT JOIN riders ON rides.riderid=riders.riderid LEFT JOIN bikes ON rides.bikeid=bikes.bikeid ";



function show_ride_details_content($ride_data)
{
	$PHONE_ICON = "\xE2\x98\x8E";
	$EMAIL_ICON = "	\xF0\x9F\x93\xA7";

	$OK = ($_SESSION['ACCESSLEVEL'] > $GLOBALS['ACCESSLEVEL_READONLY']);
	//var_dump($ride_data); echo('<hr />');
	$ro = ' readonly ';
	$de = ''; /* Data Editable (or not) */
	if ($ride_data['riderid'] <> 'newrec' && $ride_data['riderid'] <> '')
		$de = $ro;
	if ($OK) $ro = '';
	if ($ro != '')
		$disabled = 'disabled';
	else
		$disabled = '';
	
	$res = "<div class=\"detailform\">";
	$bikelist = "<datalist id=\"bikelist\">";
	$rr = sql_query("SELECT Bike FROM bikes GROUP BY Bike ORDER BY Bike");
	while(true)
	{
		$rd = mysqli_fetch_assoc($rr);
		if ($rd == false) break;
		$bikelist .= "<option>".htmlentities($rd['Bike'])."</option>";
	}
	mysqli_close($rr);
	$bikelist .= "</datalist>\r\n";
	$res .= $bikelist;
	$res .= "<form action=\"index.php\" method=\"post\" id=\"ridedetails\"><input type=\"hidden\" name=\"cmd\" value=\"putride\">";
	$res .= "<input type=\"hidden\" name=\"URI\" value=\"".$ride_data['URI']."\">";
	$res .= "<input type=\"hidden\" name=\"riderid\" value=\"".$ride_data['riderid']."\">";
	$res .= "<input type=\"hidden\" id=\"bikeid\" name=\"bikeid\" value=\"".$ride_data['bikeid']."\">";
	if ($ride_data['URI'] == 'newrec')
		$res .= "<h2>Entering new ride details for <span class=\"boldlabel\">".$ride_data['Rider_Name']."</span>";
	else
		$res .= "<h2 title=\"Unique Ride Identifier\">Ride record for URI <span class=\"boldlabel\">".$ride_data['URI']."</span>";
	
	$res .= "  <span id=\"CurrentRideStatus\" title=\"Status of this ride record\"></span>";
	$res .= "</h2>\r\n";
	$res .= "<div id=\"tabs_area\" style=\"display:inherit\"><ul id=\"tabs\">";
	$res .= "<li><a href=\"#tab_ridedata\">Ride details</a></li>";
	$res .= "<li><a href=\"#tab_ibadata\">Verification</a></li>";
	$res .= "<li><a href=\"#tab_paydata\">Payment</a></li>";
	$res .= "<li><a href=\"#tab_status\">Flags</a></li>";
	
	$showCertificate = ($ride_data['URI'] != 'newrec') && ($ride_data['IBA_Ride'] != '') && ($ride_data['StartPoint'] != '') && ($ride_data['OriginUK'] == 'Y') && ($ride_data['Failed'] == 'N');
	if ($showCertificate)
		$res .= "<li><a href=\"#tab_ridecert\">Certificate</a></li>";
	$res .= "</ul></div>\r\n";
	$res .= "<div class=\"tabContent\" id=\"tab_ridedata\">";

	$res .= "<label for=\"IBA_Number\" class=\"vlabel\">IBA Number</label><input type=\"number\" name=\"IBA_Number\" id=\"IBA_Number\" class=\"vdata\" $de value=\"".$ride_data['IBA_Number']."\" />";
	if ($ride_data['URI'] == 'newrec')	
		$onc = ' oninput="setCertificateName(this);" ';
	else
		$onc = '';	
	$res .= "<label for=\"Rider_Name\" class=\"vlabel\">Rider name</label><input $onc type=\"text\" name=\"Rider_Name\" id=\"Rider_Name\" class=\"vdata\" $de value=\"".$ride_data['Rider_Name']."\" />";
	$res .= "<label for=\"NameOnCertificate\" class=\"vlabel\">Name for certificate</label><input type=\"text\" name=\"NameOnCertificate\" id=\"NameOnCertificate\" class=\"vdata\" $ro value=\"".$ride_data['NameOnCertificate']."\" /><br>";
	$res .= "<label for=\"Postal_Address\" class=\"vlabel\">Postal address</label><textarea name=\"Postal_Address\" id=\"Postal_Address\" $ro class=\"vdata tall\" >".$ride_data['Postal_Address']."</textarea>";
	$res .= "<label for=\"RideStars\" class=\"vlabel\">RideStars</label><input tabindex=\"-1\" type=\"text\" name=\"RideStars\" id=\"RideStars\" class=\"vdata\" title=\"What's significant about the rider/pillion on this ride, age?\" $ro value=\"".$ride_data['RideStars']."\" />";
	if ($ride_data['URI'] <> 'newrec')
	{
		$res .= "<label for=\"editriderbutton\" class=\"vlabel\"></label>";
		$res .= "<input type=\"submit\" id=\"editriderbutton\" name=\"cmd\" value=\"UpdateRiderRecord\" title=\"Click to update the full rider record\">";
	}
	$res .= "<br />";
	$res .= "<label for=\"Postcode\" class=\"vlabel\">Postcode</label><input type=\"text\" name=\"Postcode\" id=\"Postcode\" class=\"vdata\"  $ro value=\"".$ride_data['Postcode']."\" />";
	$res .= "<label for=\"Email\" class=\"vlabel\">Email</label><input type=\"email\" name=\"Email\" id=\"Email\" class=\"vdata\"  $ro value=\"".$ride_data['Email']."\" />";
	$res .= "<a tabindex=\"-1\" id=\"sendMail\" href=\"mailto:".$ride_data['Email']."\"> $EMAIL_ICON</a>";

	$res .= "<label for=\"Phone\" class=\"vlabel\">Phone</label><input type=\"tel\" style=\"width:12em;\" name=\"Phone\" id=\"Phone\" $ro class=\"vdata\" value=\"".$ride_data['Phone']."\" />";
	$res .= "<a tabindex=\"-1\" id=\"callPhone\"  href=\"tel:".$ride_data['Phone']."\"> $PHONE_ICON</a>";
	$res .= "<hr />";
	$res .= "<label for=\"IBA_RideID\" class=\"vlabel bold\">IBA Ride</label>";
	//$res .= "<input autofocus list=\"IBA_Rides\" type=\"text\" name=\"IBA_Ride\" id=\"IBA_Ride\" class=\"vdata\" $ro value=\"".$ride_data['IBA_Ride']."\" />";
	$res .= "<input type=\"hidden\" name=\"IBA_Ride\" id=\"IBA_Ride\" class=\"vdata\" $ro value=\"".$ride_data['IBA_Ride']."\" />";
	
	$res .= "<select autofocus onchange=\"setRideFromRideID();\" name=\"IBA_RideID\" id=\"IBA_RideID\" class=\"vdata\" $ro>";
	
	$where = "WHERE Deleted='N'";
	if ($ride_data['URI'] <> 'newrec')
		$where .= " OR IBA_Ride='".$ride_data['IBA_Ride']."'";
	$rn = sql_query("SELECT * FROM ridenames $where ORDER BY IBA_Ride");
	//$res .= "\r\n<datalist id=\"IBA_Rides\">";
	while(true)
	{
		$rnd = mysqli_fetch_assoc($rn);
		if ($rnd == false) break;
		$res .= "<option value=\"".htmlentities($rnd['recid'])."\" ";
		if ( ($rnd['IBA_Ride'] == $ride_data['IBA_Ride']) || (($rnd['IBA_Ride'] == 'SS1000') && ($ride_data['URI'] == 'newrec')))
			$res .= " selected ";
		$res .= ">".htmlentities($rnd['IBA_Ride'])."</option>";
	}
	//$res .= "</datalist>\r\n";
	$res .= "</select>";
	
	$res .= "<label for=\"DateRideStart\" class=\"vlabel bold\">Date ride started</label><input type=\"date\" id=\"DateRideStart\" name=\"DateRideStart\" class=\"vdata\" $ro value=\"".$ride_data['DateRideStart']."\" onchange=\"document.getElementById('DateRideFinish').value=document.getElementById('DateRideStart').value;\" />";
	$res .= "<label for=\"DateRideFinish\" class=\"vlabel\">Date ride finished</label><input type=\"date\" id=\"DateRideFinish\" name=\"DateRideFinish\" class=\"vdata\" $ro value=\"".$ride_data['DateRideFinish']."\" /><br>";
	$res .= "<label for=\"EventName\" class=\"vlabel\">Event</label><input type=\"text\" name=\"EventName\" id=\"EventName\" class=\"vdata\" $ro value=\"".$ride_data['EventName']."\" />";
	$res .= "<fieldset>";
	$res .= "<input type=\"radio\" name=\"IsPillion\" class=\"radio\" $disabled value=\"N\" ".Checkbox_isNotChecked($ride_data['IsPillion']).">Rider";
	$res .= "<input type=\"radio\" name=\"IsPillion\" class=\"radio2\" $disabled value=\"Y\" ".Checkbox_isChecked($ride_data['IsPillion']).">Pillion";
	$res .= "</fieldset>";
	$res .= "<hr />";
	$res .= "<label for=\"BikeChoice\" class=\"vlabel bold\">Bike</label>";
	$res .= "<select  onchange=\"chooseBike();\" $ro id=\"BikeChoice\" name=\"Bike\" class=\"vdata\" $ro >";
	$rn = sql_query("SELECT * FROM bikes WHERE riderid=".$ride_data['riderid']);
	while(true)
	{
		$rnd = mysqli_fetch_assoc($rn);
		if ($rnd == false) break;
		$res .= "<option $disabled value=\"".htmlentities($rnd['bikeid']).'|'.htmlentities($rnd['Registration'])."\"";
		if ($rnd['bikeid'] == $ride_data['bikeid'])
			$res .= " selected ";
		$res .= ">".htmlentities($rnd['Bike']);
		$res .= "</option>";
	}
	$res .= "<option $disabled value=\"newrec|\">&lt;new bike&gt;</option>";
	$res .= "</select>";
	$res .= "<input type=\"text\" list=\"bikelist\" placeholder=\"New bike make &amp; model\" class=\"vdata\" id=\"BikeText\" name=\"BikeText\">";
	$res .= "<label for=\"BikeReg\" class=\"vlabel\">Registration</label><input type=\"text\" id=\"BikeReg\" name=\"Registration\" class=\"vdata\" $ro value=\"".$ride_data['Registration']."\" />";
	$res .= "<fieldset><legend>Odometer shows</legend>";
	$res .= "<input type=\"radio\" name=\"KmsOdo\" id=\"ko1\" class=\"radio\" $disabled value=\"N\" ".Checkbox_isNotChecked($ride_data['KmsOdo']).">Miles";
	$res .= "<input type=\"radio\" name=\"KmsOdo\" id=\"ko2\" class=\"radio2\" $disabled value=\"Y\" ".Checkbox_isChecked($ride_data['KmsOdo']).">Kms";
	$res .= "</fieldset>";
	
	$res .= "<label for=\"StartOdo\" class=\"vlabel\">Odo start</label><input type=\"number\" name=\"StartOdo\" id=\"StartOdo\" class=\" short vdata\" $ro value=\"".$ride_data['StartOdo']."\" />";
	$res .= "<label for=\"FinishOdo\" class=\"vlabel\">Odo finish</label><input type=\"number\" name=\"FinishOdo\" id=\"FinishOdo\" class=\"short vdata\" $ro value=\"".$ride_data['FinishOdo']."\" />";
	$res .= "<label for=\"TotalMiles\" class=\"vlabel\">Total miles</label><input title=\"The number of MILES (not kilometres) ridden\" type=\"number\" name=\"TotalMiles\" id=\"TotalMiles\" class=\"short vdata\" $ro value=\"".$ride_data['TotalMiles']."\" />";
	$res .= "<br /><label for=\"RideHours\" class=\"vlabel\">Ride hours</label><input type=\"number\" name=\"RideHours\" id=\"RideHours\" class=\"short vdata\" $ro value=\"".$ride_data['RideHours']."\" />";
	$res .= "<label for=\"RideMins\" class=\"vlabel\">Ride minutes</label><input type=\"number\" name=\"RideMins\" id=\"RideMins\" class=\"short vdata\" max=\"59\" $ro value=\"".$ride_data['RideMins']."\" />";

	$res .= "<hr />";
	
	$res .= "<label for=\"StartPoint\" class=\"vlabel\">Start point</label><input type=\"text\" name=\"StartPoint\" id=\"StartPoint\" class=\"vdata\" $ro value=\"".$ride_data['StartPoint']."\" />";
	$res .= "<label for=\"MidPoints\" class=\"vlabel\">via</label><input type=\"text\" name=\"MidPoints\" id=\"MidPoints\" class=\"vdata\" $ro value=\"".$ride_data['MidPoints']."\" />";
	$res .= "<label for=\"FinishPoint\" class=\"vlabel\">Finish point</label><input type=\"text\" name=\"FinishPoint\" id=\"FinishPoint\" class=\"vdata\" $ro value=\"".$ride_data['FinishPoint']."\" />";
	$res .= "<label for=\"RiderNotes\" class=\"vlabel\">Rider notes</label><textarea name=\"RiderNotes\" id=\"RiderNotes\" class=\"vdata tall\" $ro>".$ride_data['RiderNotes']."</textarea><br />";
	$res .= "</div>";
	
	$res .= "<div class=\"tabContent\" id=\"tab_ibadata\">";
	$res .= "<label for=\"DateRcvd\" class=\"vlabel2\">Date received</label><input type=\"date\" name=\"DateRcvd\" id=\"DateRcvd\" class=\"vdata\" value=\"".$ride_data['DateRcvd']."\" />";
	$res .= "<fieldset title=\"Indicates whether or not we need to send an acknowledgement for receipt of ride details before verification\"><legend>Paperwork acknowledged?</legend>";
	$res .= "<input type=\"radio\" id=\"AckSent\" name=\"Acknowledged\" onchange=\"setRideStatus();\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['Acknowledged']).">Yes";
	$res .= "<input type=\"radio\" name=\"Acknowledged\" onchange=\"setRideStatus();\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['Acknowledged']).">No";
	$res .= "</fieldset><br />";

	$res .= "<label for=\"TimeStart\" class=\"vlabel2\">Time 1st receipt</label><input type=\"text\" name=\"TimeStart\" id=\"TimeStart\" class=\"vdata\" value=\"".$ride_data['TimeStart']."\" />";
	$res .= "<label for=\"TimeFinish\" class=\"vlabel2\">Time last receipt</label><input type=\"text\" name=\"TimeFinish\" id=\"TimeFinish\" class=\"vdata\" value=\"".$ride_data['TimeFinish']."\" /><br />";
	$res .= "<label for=\"RideVerifier\" class=\"vlabel2\">Verifier</label><input type=\"text\" name=\"RideVerifier\" id=\"RideVerifier\" class=\"vdata\" value=\"".$ride_data['RideVerifier']."\" />";
	$res .= "<label for=\"VerifierNotes\" class=\"vlabel2\">Verifier notes</label><textarea name=\"VerifierNotes\" id=\"VerifierNotes\" class=\"vdata tall\">".$ride_data['VerifierNotes']."</textarea><br />";
	$res .= "<label for=\"DateVerified\" class=\"vlabel2\">Date verified</label><input type=\"date\" name=\"DateVerified\" id=\"DateVerified\" onchange=\"setRideStatus();\" class=\"vdata\" value=\"".$ride_data['DateVerified']."\" />";
	$res .= "<fieldset><legend>Verification</legend>";
	$res .= "<input type=\"radio\" id=\"isFailedRide\" name=\"Failed\" onchange=\"setRideStatus();\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['Failed']).">FAILED";
	$res .= "<input type=\"radio\" name=\"Failed\" onchange=\"setRideStatus();\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['Failed']).">ok";
	$res .= "</fieldset>";
	$res .= "</div>";
	
	$res .= "<div class=\"tabContent\" id=\"tab_paydata\">";
	$res .= "<div id=\"paydata\">";
	$res .= "<label for=\"PayMethod\" class=\"vlabel2\">Payment method</label><input type=\"text\" list=\"paymethods\" name=\"PayMethod\" id=\"PayMethod\" class=\"vdata\" value=\"".$ride_data['PayMethod']."\" />";
	$res .= "<datalist id=\"paymethods\"><option>Paypal</option><option>FOC</option></datalist>";
	$res .= "<br />&nbsp;<br /><label for=\"DatePayReq\" class=\"vlabel2\">Date payment requested</label><input type=\"date\" name=\"DatePayReq\" id=\"DatePayReq\" onchange=\"setRideStatus();\" class=\"vdata\" value=\"".$ride_data['DatePayReq']."\" />";
	$res .= "<label for=\"DatePayRcvd\" class=\"vlabel2\">Date payment received</label><input type=\"date\" name=\"DatePayRcvd\" id=\"DatePayRcvd\" onchange=\"setRideStatus();doPaymentReceived();\" class=\"vdata\" value=\"".$ride_data['DatePayRcvd']."\" />";
	$res .= "<label for=\"DateCertSent\" class=\"vlabel2\">Date Certificate sent</label><input type=\"date\" name=\"DateCertSent\" id=\"DateCertSent\" class=\"vdata\" value=\"".$ride_data['DateCertSent']."\" /><br />";
	$res .= "&nbsp;<br /><label for=\"USA_FeeDollars\" class=\"vlabel2\">USA fee ($)</label><input type=\"number\" name=\"USA_FeeDollars\" id=\"USA_FeeDollars\" class=\"vdata\" value=\"".$ride_data['USA_FeeDollars']."\" />";
	$res .= "<label for=\"DateUSAPaid\" class=\"vlabel2\">Date USA paid</label><input type=\"date\" name=\"DateUSAPaid\" id=\"DateUSAPaid\" onchange=\"setRideStatus();\" class=\"vdata\" value=\"".$ride_data['DateUSAPaid']."\" />";
	$res .= "<fieldset title=\"For UK rides which need to be reported and paid to the USA\"><legend>Reported to USA</legend>";
	$res .= "<input type=\"radio\" name=\"PassedToUSA\" id=\"SentToUSA\" onchange=\"setRideStatus();\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['PassedToUSA']).">Yes";
	$res .= "<input type=\"radio\" name=\"PassedToUSA\" onchange=\"setRideStatus();\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['PassedToUSA']).">No";
	$res .= "</fieldset>";
	$res .= "</div>";

	$res .= "</div>";

	$res .= "<div class=\"tabContent\" id=\"tab_status\">";
	$res .= "<div id=\"recordflags\">";
	$res .= "<fieldset title=\"Are we expected to supply a certificate for this ride?\"><legend>Certificate wanted?</legend>";
	$res .= "<input type=\"radio\" name=\"WantCertificate\" id=\"DoWantCertificate\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['WantCertificate']).">Yes please";
	$res .= "<input type=\"radio\" name=\"WantCertificate\" id=\"DontWantCertificate\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['WantCertificate']).">Nah";
	$res .= "</fieldset>";
	$res .= "<fieldset><legend>Show on RoH?</legend>";
	$res .= "<input type=\"radio\" id=\"publishRoH\" onchange=\"setRideStatus();\" name=\"ShowRoH\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['ShowRoH']).">Yes";
	$res .= "<input type=\"radio\" name=\"ShowRoH\" onchange=\"setRideStatus();\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['ShowRoH']).">No";
	$res .= "</fieldset>";
	$res .= "<fieldset title=\"If non-UK we don't supply certificates, take payments or report it to the USA\"><legend>Accounted for by</legend>";
	$res .= "<input type=\"radio\" name=\"OriginUK\" onchange=\"setRideDefaults();\" class=\"radio\" $ro title=\"Ride certified by IBA UK\" value=\"Y\" ".Checkbox_isChecked($ride_data['OriginUK']).">IBAUK";
	$res .= "<input type=\"radio\" name=\"OriginUK\" id=\"foreignCert\" onchange=\"setRideDefaults();\" class=\"radio2\" $ro title=\" Ride certified by USA? Finland?\" value=\"N\" ".Checkbox_isNotChecked($ride_data['OriginUK']).">other";
	$res .= "</fieldset>";
	
	if ($ride_data['URI'] <> 'newrec')	
	{
		$res .= "<fieldset><legend>Record status</legend>";
		$res .= "<input type=\"radio\" name=\"RideDeleted\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['RideDeleted']).">Deleted";
		$res .= "<input type=\"radio\" name=\"RideDeleted\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['RideDeleted']).">OK";
		$res .= "</fieldset>";
	}

	$res .= "</div>"; // recordflags
	$res .= "</div>"; // tab_status
	
	if ($showCertificate)
	{
		$res .= "<div class=\"tabContent\" id=\"tab_ridecert\">";
		$res .= "<iframe style=\"min-height:400px; width:100%;\" src=\"index.php?c=ridecert&uri=".$ride_data['URI']."\"></iframe>";
		$res .= "</div>"; // tab_ridecert
	}
	if ($ro=='')
	{
		$res .= "<input type=\"submit\" value=\"Update/Save these details\" />";
		$res .= "<label for=\"newridebutton\"> </label>";
		$res .= "<input type=\"submit\" id=\"newridebutton\" name=\"cmd\" title=\"Click to enter a new ride for this rider\" value=\"NewRide\">";
	}
	$res .= "</form>";
	$res .= "</div>";
	
	$res .= "<script>setRideStatus();reflectCertOrigin();chooseBike();</script>";
    start_html("Ride ".$ride_data['URI']);
    echo($res);
?>
<script>
</script>
<?php	
	//var_dump($ride_data);
    echo("</body></html>");
	
}

function show_ride_details_uri($uri)
{
	global $RIDES_SQL;
	
    $SQL  = $RIDES_SQL." WHERE URI = ".$uri;
    $ride  = sql_query($SQL);
    $ride_data = mysqli_fetch_assoc($ride);
	
	show_ride_details_content($ride_data);
	
}

function show_ride_details()
{
   global $CMDWORDS;


	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
	show_ride_details_uri($_REQUEST[$CMDWORDS['uri']]);
	
}

function showNewRide()
{
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();
	
		$rd['URI'] = 'newrec';
		if ($_REQUEST['riderid'] <> '')
		{
			$riderid = $_REQUEST['riderid'];
			$bikeid = $_REQUEST['bikeid'];
		}
		else
		{
			$riderid = $_REQUEST['rid'][$_REQUEST['ix']];
			$bikeid = $_REQUEST['bik'][$_REQUEST['ix']];
		}
		if ($riderid <> '' && $riderid <> 'new')
		{
			$sql = "SELECT * FROM riders WHERE riderid=".$riderid;
			$r = sql_query($sql);
			$rr = mysqli_fetch_assoc($r);
			foreach ($rr as $k => $v)
			{
				$rd[$k] = $v;
			}
		}
		else if (is_numeric($_REQUEST['key']))
			$rd['IBA_Number'] = $_REQUEST['key'];
		else
			$rd['Rider_Name'] = $_REQUEST['key'];
		if ($bikeid <> '' && $bikeid <> 'new')
		{
			$sql = "SELECT * FROM bikes WHERE riderid=".$riderid." AND bikeid=".$bikeid;
			$r = sql_query($sql);
			$rr = mysqli_fetch_assoc($r);
			foreach ($rr as $k => $v)
			{
				$rd[$k] = $v;
			}
		}
		// Set some defaults
		$rd['NameOnCertificate'] = $rd['Rider_Name'];			
		$rd['WantCertificate'] = 'Y';
		$rd['DateRcvd'] = date('Y-m-d');
		$rd['OriginUK'] = 'Y';
		$rd['PayMethod'] = ''; // Setting default hides options

		show_ride_details_content($rd);
		
}

function establishNewRide()
{
	if ($_POST['riderid']=='')
	{
		$SQL = "INSERT INTO riders (Rider_Name,IBA_Number,Postal_Address,Postcode,Email,Phone,IsPillion) VALUES (";
		$SQL .= "'".safesql($_POST['Rider_Name'])."'";
		$SQL .= ",'".safesql($_POST['IBA_NUmber'])."'";
		$SQL .= ",'".safesql($_POST['Postal_Address'])."'";
		$SQL .= ",'".safesql($_POST['Postcode'])."'";
		$SQL .= ",'".safesql($_POST['Email'])."'";
		$SQL .= ",'".safesql($_POST['Phone'])."'";
		$SQL .= ",'".safesql($_POST['IsPillion'])."'";
		$SQL .= ")";
		sql_query($SQL);
		$SQL = "SELECT riderid FROM riders ORDER BY riderid DESC LIMIT 1";
		$r = sql_query($SQL);
		$rd = mysqli_fetch_assoc($r);
		$_POST['riderid'] = $rd['riderid'];
	}
	if ($_POST['bikeid']=='')
	{
		$SQL = "INSERT INTO bikes (Bike,Registration,KmsOdo) VALUES(";
		$SQL .= "'".safesql($_POST['Bike'])."'";
		$SQL .= ",'".safesql($_POST['Registration'])."'";
		$SQL .= ",'".safesql($_POST['KmsOdo'])."'";
		$SQL .= ")";
		sql_query($SQL);
		$SQL = "SELECT bikeid FROM bikes ORDER BY bikeid DESC WHERE riderid=".$_POST['riderid']." LIMIT 1";
		$r = sql_query($SQL);
		$rd = mysqli_fetch_assoc($r);
		$_POST['bikeid'] = $rd['bikeid'];
	}
	$SQL = "INSERT INTO rides (riderid,bikeid,NameOnCertificate,DateRideStart,DateRideFinish,IBA_Ride,IsPillion";
	$SQL .= ",EventName,StartOdo,FinishOdo,TotalMiles,StartPoint,MidPoints,FinishPoint,WantCertificate,RiderNotes";
	$SQL .= ",DateRcvd,RideVerifier,Acknowledged,DateVerified,Failed,DatePayReq,DatePayRcvd,PayMethod,DateCertSent";
}

function validateRideDetails()
{
	if ($_POST['IBA_Ride'] == '')
	{
		show_infoline("You must specify which IBA Ride was performed","errormsg");
		return FALSE;
	}
	if ($_POST['DateRideStart'] == '')
	{
		show_infoline("You must specify the Date ride started","errormsg");
		return FALSE;
	}
	if (trim($_POST['Bike'] == ''))
	{
		show_infoline("You must specify the Bike used for the ride","errormsg");
		return FALSE;
	}
	if ($_POST['TotalMiles'] == '')
	{
		show_infoline("You must specify the total miles ridden","errormsg");
		return FALSE;
	}
	return TRUE;
}

function putRide()
{
	global $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	
	if (!$OK) safe_default_action();

	//var_dump($_POST);
	
	if (!validateRideDetails())
	{
		show_ride_details_content($_POST);
		exit;
	}
	
	// Fixups
	
	if ($_POST['DateRideFinish'] == '')
		$_POST['DateRideFinish'] = $_POST['DateRideStart'];
	
		
	// Post the rider details
	if ($_POST['riderid'] == '')
	{
		$SQL = "INSERT INTO riders (Rider_Name,IBA_Number,Postal_Address,Postcode,Email,Phone,IsPillion) VALUES (";
		$SQL .= "'".safesql($_POST['Rider_Name'])."'";
		$SQL .= ",'".safesql($_POST['IBA_Number'])."'";
		$SQL .= ",'".safesql($_POST['Postal_Address'])."'";
		$SQL .= ",'".safesql($_POST['Postcode'])."'";
		$SQL .= ",'".safesql($_POST['Email'])."'";
		$SQL .= ",'".safesql($_POST['Phone'])."'";
		$SQL .= ",'".safesql($_POST['IsPillion'])."'";
		$SQL .= ")";
		sql_query($SQL);
		$_POST['riderid'] = dblastid('riders','riderid');
	} 
	else
	{
		$SQL = "UPDATE riders SET ";
		$SQL .= "Rider_Name='".safesql($_POST['Rider_Name'])."'";
		$SQL .= ",IBA_Number='".safesql($_POST['IBA_Number'])."'";
		$SQL .= ",Postal_Address='".safesql($_POST['Postal_Address'])."'";
		$SQL .= ",Postcode='".safesql($_POST['Postcode'])."'";
		$SQL .= ",Email='".safesql($_POST['Email'])."'";
		$SQL .= ",Phone='".safesql($_POST['Phone'])."'";
		$SQL .= ",IsPillion='".safesql($_POST['IsPillion'])."'";
		$SQL .= " WHERE riderid=".$_POST['riderid'];
		sql_query($SQL);
	}
	// Post the bike details
	if ($_POST['bikeid'] == '' || $_POST['bikeid'] == 'newrec')
	{
		$SQL = "INSERT INTO bikes (riderid,KmsOdo,Bike,Registration) VALUES (";
		$SQL .= safesql($_POST['riderid']);
		$SQL .= ",'".safesql($_POST['kmsOdo'])."'";
		$SQL .= ",'".safesql($_POST['BikeText'])."'";
		$SQL .= ",'".safesql($_POST['Registration'])."'";
		$SQL .= ")";
		sql_query($SQL);
		$_POST['bikeid'] = dblastid('bikes','bikeid');
	}
	else
	{
		//var_dump($_POST);
		$SQL = "UPDATE bikes SET ";
		$SQL .= "KmsOdo='".$_POST['KmsOdo']."'";
		$SQL .= ",Bike='".safesql($_POST['Bike'])."'";
		$SQL .= ",Registration='".safesql($_POST['Registration'])."'";
		$SQL .= " WHERE riderid=".$_POST['riderid']." AND bikeid=".$_POST['bikeid'];
		//sql_query($SQL);
	}
	//var_dump($_POST);
	if ($_POST[$CMDWORDS['uri']] == 'newrec') {
		$SQL = "INSERT INTO rides (";
		$SQL .= "DateRideStart,DateRideFinish,NameOnCertificate,RideStars,IBA_Ride,IsPillion,EventName,KmsOdo,StartOdo,FinishOdo,TotalMiles";
		$SQL .= ",riderid,bikeid";
		$SQL .= ",StartPoint,FinishPoint,MidPoints";
		$SQL .= ",WantCertificate,RiderNotes,DateRcvd,RideVerifier,Acknowledged,DateVerified,Failed";
		$SQL .= ",DatePayReq,DatePayRcvd,PayMethod,DateCertSent,USA_FeeDollars,DateUSAPaid,TimeStart,TimeFinish";
		$SQL .= ",RideHours,RideMins,VerifierNotes,ShowRoH,PassedToUSA,OriginUK,IBA_RideID";
		$SQL .= ") VALUES (";
		$SQL .= safedatesql($_POST['DateRideStart']);
		$SQL .= ",".safedatesql($_POST['DateRideFinish']);
		$SQL .= ",'".safesql($_POST['NameOnCertificate'])."'";
		$SQL .= ",'".safesql($_POST['RideStars'])."'";
		$SQL .= ",'".safesql($_POST['IBA_Ride'])."'";
		$SQL .= ",'".safesql($_POST['IsPillion'])."'";
		$SQL .= ",'".safesql($_POST['EventName'])."'";
		$SQL .= ",'".safesql($_POST['KmsOdo'])."'";
		$SQL .= ",'".safesql($_POST['StartOdo'])."'";
		$SQL .= ",'".safesql($_POST['FinishOdo'])."'";
		$SQL .= ",'".safesql($_POST['TotalMiles'])."'";
		$SQL .= ",".safesql($_POST['riderid'])."";
		$SQL .= ",".safesql($_POST['bikeid'])."";
		$SQL .= ",'".safesql($_POST['StartPoint'])."'";
		$SQL .= ",'".safesql($_POST['FinishPoint'])."'";
		$SQL .= ",'".safesql($_POST['MidPoints'])."'";
		$SQL .= ",'".safesql($_POST['WantCertificate'])."'";
		$SQL .= ",'".safesql($_POST['RiderNotes'])."'";
		$SQL .= ",".safedatesql($_POST['DateRcvd']);
		$SQL .= ",'".safesql($_POST['RideVerifier'])."'";
		$SQL .= ",'".safesql($_POST['Acknowledged'])."'";
		$SQL .= ",".safedatesql($_POST['DateVerified']);
		$SQL .= ",'".safesql($_POST['Failed'])."'";
		$SQL .= ",".safedatesql($_POST['DatePayReq']);
		$SQL .= ",".safedatesql($_POST['DatePayRcvd']);
		$SQL .= ",'".safesql($_POST['PayMethod'])."'";
		$SQL .= ",".safedatesql($_POST['DateCertSent']);
		$SQL .= ",'".safesql($_POST['USA_FeeDollars'])."'";
		$SQL .= ",".safedatesql($_POST['DateUSAPaid']);
		$SQL .= ",'".safesql($_POST['TimeStart'])."'";
		$SQL .= ",'".safesql($_POST['TimeFinish'])."'";
		$SQL .= ",0".safesql($_POST['RideHours'])."";
		$SQL .= ",0".safesql($_POST['RideMins'])."";
		$SQL .= ",'".safesql($_POST['VerifierNotes'])."'";
		$SQL .= ",'".safesql($_POST['ShowRoH'])."'";
		$SQL .= ",'".safesql($_POST['PassedToUSA'])."'";
		$SQL .= ",'".safesql($_POST['OriginUK'])."'";
		$SQL .= ",".safesql($_POST['IBA_RideID']);
		$SQL .= ")";
	} elseif ($_POST['RideDeleted']=='Y') {
		$SQL = "UPDATE rides SET Deleted='Y' WHERE URI=".$_POST[$CMDWORDS['uri']];
	} else {
		$SQL = "UPDATE rides SET ";
		$SQL .= "DateRideStart=".safedatesql($_POST['DateRideStart']);
		$SQL .= ",DateRideFinish=".safedatesql($_POST['DateRideFinish']);
		$SQL .= ",NameOnCertificate='".safesql($_POST['NameOnCertificate'])."'";
		$SQL .= ",RideStars='".safesql($_POST['RideStars'])."'";
		$SQL .= ",IBA_Ride='".safesql($_POST['IBA_Ride'])."'";
		$SQL .= ",isPillion='".safesql($_POST['IsPillion'])."'";
		$SQL .= ",EventName='".safesql($_POST['EventName'])."'";
		$SQL .= ",KmsOdo='".safesql($_POST['KmsOdo'])."'";
		$SQL .= ",StartOdo='".safesql($_POST['StartOdo'])."'";
		$SQL .= ",FinishOdo='".safesql($_POST['FinishOdo'])."'";
		$SQL .= ",TotalMiles='".safesql($_POST['TotalMiles'])."'";
		$SQL .= ",riderid=".safesql($_POST['riderid'])."";
		$SQL .= ",bikeid=".safesql($_POST['bikeid'])."";
		$SQL .= ",StartPoint='".safesql($_POST['StartPoint'])."'";
		$SQL .= ",FinishPoint='".safesql($_POST['FinishPoint'])."'";
		$SQL .= ",MidPoints='".safesql($_POST['MidPoints'])."'";
		$SQL .= ",WantCertificate='".safesql($_POST['WantCertificate'])."'";
		$SQL .= ",RiderNotes='".safesql($_POST['RiderNotes'])."'";
		$SQL .= ",DateRcvd=".safedatesql($_POST['DateRcvd']);
		$SQL .= ",RideVerifier='".safesql($_POST['RideVerifier'])."'";
		$SQL .= ",Acknowledged='".safesql($_POST['Acknowledged'])."'";
		$SQL .= ",DateVerified=".safedatesql($_POST['DateVerified']);
		$SQL .= ",Failed='".safesql($_POST['Failed'])."'";
		$SQL .= ",DatePayReq=".safedatesql($_POST['DatePayReq']);
		$SQL .= ",DatePayRcvd=".safedatesql($_POST['DatePayRcvd']);
		$SQL .= ",PayMethod='".safesql($_POST['PayMethod'])."'";
		$SQL .= ",DateCertSent=".safedatesql($_POST['DateCertSent']);
		$SQL .= ",USA_FeeDollars='".safesql($_POST['USA_FeeDollars'])."'";
		$SQL .= ",DateUSAPaid=".safedatesql($_POST['DateUSAPaid']);
		$SQL .= ",TimeStart='".safesql($_POST['TimeStart'])."'";
		$SQL .= ",TimeFinish='".safesql($_POST['TimeFinish'])."'";
		$SQL .= ",RideHours=0".safesql($_POST['RideHours'])."";
		$SQL .= ",RideMins=0".safesql($_POST['RideMins'])."";
		$SQL .= ",VerifierNotes='".safesql($_POST['VerifierNotes'])."'";
		$SQL .= ",ShowRoh='".safesql($_POST['ShowRoH'])."'";
		$SQL .= ",PassedToUSA='".safesql($_POST['PassedToUSA'])."'";
		$SQL .= ",OriginUK='".safesql($_POST['OriginUK'])."'";
		$SQL .= ",Deleted='N'";
		$SQL .= ",IBA_RideID=".safesql($_POST['IBA_RideID']);
		$SQL .= " WHERE URI=".$_POST[$CMDWORDS['uri']];
	}

	//echo($SQL."<hr />");
	sql_query($SQL);
	if ($_POST[$CMDWORDS['uri']] == 'newrec') {	
		$_POST[$CMDWORDS['uri']] = dblastid('rides','URI');
	}
	show_infoline("Ride ".$_POST[$CMDWORDS['uri']]." saved ok","infohilite");
	show_ride_details_uri($_POST[$CMDWORDS['uri']]);
	
	
}

?>
