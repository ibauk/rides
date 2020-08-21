<?php
/*
 * I B A U K - ridestab.php
 *
 * Copyright (c) 2016 Bob Stammers
 *
 */

$RIDES_SQL  = "SELECT SQL_CALC_FOUND_ROWS * FROM rides LEFT JOIN riders ON rides.riderid=riders.riderid LEFT JOIN bikes ON rides.bikeid=bikes.bikeid ";


function rides_table_row_header()
{
    global $MYKEYWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
    if ($OK) $res .= "<th class=\"uri\">".column_anchor($MYKEYWORDS['uri'],"uri")."</th>";
    $res .= "<th class=\"date\">".column_anchor('Ride date',"DateRideStart")."</th>";
    $res .= "<th class=\"certname\">".column_anchor("Rider","rides.NameOnCertificate")."</th>";
    $res .= "<th class=\"text\">".column_anchor("IBA#","IBA_Number")."</th>";
    $res .= "<th class=\"ride\">".column_anchor("Ride","IBA_Ride")."</th>";
	$res .= "<th class=\"bike\">".column_anchor("Bike","Bike")."</th>";
	$res .= "<th class=\"event\">".column_anchor("Event","EventName")."</th>";
    return $res;
}

function rides_table_row_html($ride_data)
{
    global $MYKEYWORDS, $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
    if ($OK) $res .= "<td class=\"uri\">";
    if ($OK) $res .= "<a href=\"index.php?c=".$CMDWORDS['showride']."&amp;".$CMDWORDS['uri']."=".$ride_data['URI'];
    if ($OK) $res .= "\">".$ride_data['URI']."</a></td>";
    $res .= "<td class=\"date\">".$ride_data['DateRideStart']."</td>";
    $res .= "<td class=\"certname\">".$ride_data['NameOnCertificate']."</td>";
    $res .= "<td class=\"text\">".$ride_data['IBA_Number']."</td>";
    $res .= "<td class=\"ride\">".$ride_data['IBA_Ride']."</td>";
	$res .= "<td class=\"bike\">".$ride_data['Bike']."</td>";
	$res .= "<td class=\"event\">".$ride_data['EventName']."</td>";
    return $res;
}


function rides_table_row($uri)
{
    global $RIDES_SQL;

    $SQL = $RIDES_SQL . " WHERE rides.URI = '$uri' ";
    $ride = sql_query($SQL);
    $ride_data = mysqli_fetch_assoc($ride);

    return rides_table_row_html($ride_data);

}


function show_rides_table($where)
{
    global $RIDES_SQL, $CMDWORDS;
	global $KEY_ORDER, $KEY_DESC, $PAGESIZE, $OFFSET, $SHOWALL;

    $SQL = $RIDES_SQL;
    if ($where <> '') $SQL .= " WHERE $where";

	if (!isset($KEY_ORDER) || $KEY_ORDER == '')
	{
		$KEY_ORDER = 'DateRideStart';
		$KEY_DESC = $KEY_ORDER;
	}
	$SQL .= sql_order();
	//echo($SQL.'<hr />');
    $ride = sql_query($SQL);
	$TotRows = foundrows();
	if ($_SESSION['UPDATING'])
		$xl ="<a href=\"index.php?c=".$CMDWORDS['newride']."\">Enter new ".$MYKEYWORDS['ride']."</a>";
	else
		$xl = '';
	echo("<div class=\"maindata\">");
	if ($TotRows > mysqli_num_rows($ride))
		show_common_paging($TotRows,$xl);
    echo("<table>");
	echo("<tr>".rides_table_row_header()."</tr>\n");
	$rownum = 0;
    while(true)
    {
        $ride_data = mysqli_fetch_assoc($ride);
        if ($ride_data == false) break;
		$rownum++;
		if ($rownum % 2 == 1)
			echo("<tr class=\"row-1\">");
		else
			echo("<tr class=\"row-2\">");
        echo(rides_table_row_html($ride_data)."</tr>\n");
    }
    echo("</table></div>");
	
}



function show_rides_listing()
{
    global $OFFSET, $PAGESIZE, $SHOW, $MYKEYWORDS;

    start_html($MYKEYWORDS['rides']." listing");
    if ($_GET['show']=='all')
    {
        $OFFSET = 0;
        $PAGESIZE = -1;
    }

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	show_rides_table($OK? "":"ShowRoh='Y'");
    echo("</body></html>");
        
}


function show_ride_details_content($ride_data)
{

	$OK = ($_SESSION['ACCESSLEVEL'] > $GLOBALS['ACCESSLEVEL_READONLY']);
	
	$ro = ' readonly ';
	if ($OK) $ro = '';
	$de = ' readonly '; /* Data Editable (or not) */
	if ($ride_data['URI'] == 'newrec')
		$de = $ro;
	$res = "<div class=\"detailform\"  title=\"URI is ".$ride_data['URI']."; riderid is ".$ride_data['riderid']."\">";
	$res .= "<form action=\"index.php\" method=\"post\" id=\"ridedetails\"><input type=\"hidden\" name=\"cmd\" value=\"putride\">";
	$res .= "<input type=\"hidden\" name=\"URI\" value=\"".$ride_data['URI']."\">";
	$res .= "<input type=\"hidden\" name=\"riderid\" value=\"".$ride_data['riderid']."\">";
	$res .= "<input type=\"hidden\" name=\"bikeid\" value=\"".$ride_data['bikeid']."\">";
	$res .= "<h2>Ride record for URI <span class=\"boldlabel\">".$ride_data['URI']."</span></h2>";
	
	$res .= "<div id=\"tabs_area\" style=\"display:inherit\"><ul id=\"tabs\">";
	$res .= "<li><a href=\"#tab_ridedata\">Ride details</a></li>";
	$res .= "<li><a href=\"#tab_ibadata\">Verification</a></li>";
	$res .= "<li><a href=\"#tab_paydata\">Payment</a></li>";
	$res .= "</ul></div>";
	$res .= "<div class=\"tabContent\" id=\"tab_ridedata\">";

	$res .= "<label for=\"IBA_Number\" class=\"vlabel\">IBA Number</label><input type=\"number\" name=\"IBA_Number\" class=\"vdata\" $de value=\"".$ride_data['IBA_Number']."\" />";
	$res .= "<label for \"Rider_Name\" class=\"vlabel\">Rider name</label><input type=\"text\" name=\"Rider_Name\" class=\"vdata\" $de value=\"".$ride_data['Rider_Name']."\" />";
	$res .= "<label for=\"NameOnCertificate\" class=\"vlabel\">Name for certificate</label><input type=\"text\" name=\"NameOnCertificate\" class=\"vdata\" $ro value=\"".$ride_data['NameOnCertificate']."\" />";
	$res .= "<label for=\"Postal_Address\" class=\"vlabel\">Postal address</label><textarea name=\"Postal_Address\" class=\"vdata tall\" $de>".$ride_data['Postal_Address']."</textarea><br />";
	$res .= "<label for=\"Postcode\" class=\"vlabel\">Postcode</label><input type=\"text\" name=\"Postcode\" class=\"vdata\" $de value=\"".$ride_data['Postcode']."\" />";
	$res .= "<label for=\"Email\" class=\"vlabel\">Email</label><input type=\"email\" name=\"Email\" class=\"vdata\" $de value=\"".$ride_data['Email']."\" />";
	$res .= "<label for=\"Phone\" class=\"vlabel\">Phone</label><input type=\"number\" name=\"Phone\" class=\"vdata\" $de value=\"".$ride_data['Phone']."\" />";
	$res .= "<label for=\"IBA_Ride\" class=\"vlabel\">IBA Ride</label><input list=\"IBA_Rides\" type=\"text\" name=\"IBA_Ride\" class=\"vdata\" $ro value=\"".$ride_data['IBA_Ride']."\" />";
	
	$rn = sql_query("SELECT * FROM ridenames ORDER BY IBA_Ride");
	$res .= "<datalist id=\"IBA_Rides\">";
	while(true)
	{
		$rnd = mysqli_fetch_assoc($rn);
		if ($rnd == false) break;
		$res .= "<option>".$rnd['IBA_Ride']."</option>";
	}
	$res .= "</datalist>";
	
	$res .= "<label for=\"DateRideStart\" class=\"vlabel\">Date ride started</label><input type=\"date\" name=\"DateRideStart\" class=\"vdata\" $ro value=\"".$ride_data['DateRideStart']."\" />";
	$res .= "<label for=\"DateRideFinish\" class=\"vlabel\">Date ride finished</label><input type=\"date\" name=\"DateRideFinish\" class=\"vdata\" $ro value=\"".$ride_data['DateRideFinish']."\" />";
	$res .= "<label for=\"EventName\" class=\"vlabel\">Event</label><input type=\"text\" name=\"EventName\" class=\"vdata\" $ro value=\"".$ride_data['EventName']."\" />";
	$res .= "<fieldset>";
	$res .= "<input type=\"radio\" name=\"IsPillion\" class=\"radio\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['IsPillion']).">Rider</input>";
	$res .= "<input type=\"radio\" name=\"IsPillion\" class=\"radio2\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['IsPillion']).">Pillion</input>";
	$res .= "</fieldset>";
	$res .= "<hr />";
	$res .= "<label for=\"Bike\" class=\"vlabel\">Bike</label><input list=\"bikelist\" onchange=\"alert('hello sailor');\" type=\"text\" id=\"TheBike\" name=\"Bike\" class=\"vdata\" $ro value=\"".$ride_data['Bike']."\" />";
	
	$rn = sql_query("SELECT * FROM bikes WHERE riderid=".$ride_data['riderid']);
	$res .= "<datalist id=\"bikelist\">";
	while(true)
	{
		$rnd = mysqli_fetch_assoc($rn);
		if ($rnd == false) break;
		$res .= "<option value=\"".$rnd['Bike']."\">".$rnd['Bike']." [".$rnd['Registration']."]</option>";
	}
	$res .= "</datalist>";
	$res .= "<label for=\"Registration\" class=\"vlabel\">Registration</label><input type=\"text\" name=\"Registration\" class=\"vdata\" $ro value=\"".$ride_data['Registration']."\" />";
	$res .= "<fieldset><legend>Odometer shows</legend>";
	$res .= "<input type=\"radio\" name=\"KmsOdo\" class=\"radio\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['KmsOdo']).">Miles</input>";
	$res .= "<input type=\"radio\" name=\"KmsOdo\" class=\"radio2\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['KmsOdo']).">Kilometres</input>";
	$res .= "</fieldset>";
	
	$res .= "<label for=\"StartOdo\" class=\"vlabel\">Odo start</label><input type=\"number\" name=\"StartOdo\" class=\"vdata\" $ro value=\"".$ride_data['StartOdo']."\" />";
	$res .= "<label for=\"FinishOdo\" class=\"vlabel\">Odo finish</label><input type=\"number\" name=\"FinishOdo\" class=\"vdata\" $ro value=\"".$ride_data['FinishOdo']."\" />";
	$res .= "<label for=\"TotalMiles\" class=\"vlabel\">Total miles</label><input type=\"number\" name=\"TotalMiles\" class=\"vdata\" $ro value=\"".$ride_data['TotalMiles']."\" />";
	$res .= "<label for=\"RideHours\" class=\"vlabel\">Ride hours</label><input type=\"number\" name=\"RideHours\" class=\"vdata\" $ro value=\"".$ride_data['RideHours']."\" />";
	$res .= "<label for=\"RideMins\" class=\"vlabel\">Ride minutes</label><input type=\"number\" name=\"RideMins\" class=\"vdata\" $ro value=\"".$ride_data['RideMins']."\" />";

	$res .= "<hr />";
	
	$res .= "<label for=\"StartPoint\" class=\"vlabel\">Start point</label><input type=\"text\" name=\"StartPoint\" class=\"vdata\" $ro value=\"".$ride_data['StartPoint']."\" />";
	$res .= "<label for=\"MidPoints\" class=\"vlabel\">via</label><input type=\"text\" name=\"MidPoints\" class=\"vdata\" $ro value=\"".$ride_data['MidPoints']."\" />";
	$res .= "<label for=\"FinishPoint\" class=\"vlabel\">Finish point</label><input type=\"text\" name=\"FinishPoint\" class=\"vdata\" $ro value=\"".$ride_data['FinishPoint']."\" />";
	$res .= "<label for=\"RiderNotes\" class=\"vlabel\">Rider notes</label><textarea name=\"RiderNotes\" class=\"vdata tall\" $ro>".$ride_data['RiderNotes']."</textarea><br />";
	$res .= "<fieldset><legend>Certificate wanted?</legend>";
	$res .= "<input type=\"radio\" name=\"WantCertificate\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['WantCertificate']).">Yes please</input>";
	$res .= "<input type=\"radio\" name=\"WantCertificate\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['WantCertificate']).">Nah</input>";
	$res .= "</fieldset>";
	$res .= "</div>";
	
	$res .= "<div class=\"tabContent\" id=\"tab_ibadata\">";
	$res .= "<label for=\"DateRcvd\" class=\"vlabel2\">Date received</label><input type=\"date\" name=\"DateRcvd\" class=\"vdata\" value=\"".$ride_data['DateRcvd']."\" />";
	$res .= "<fieldset><legend>Paperwork acknowledged?</legend>";
	$res .= "<input type=\"radio\" name=\"Acknowledged\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['Acknowledged']).">Yes</input>";
	$res .= "<input type=\"radio\" name=\"Acknowledged\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['Acknowledged']).">No</input>";
	$res .= "</fieldset><br />";

	$res .= "<label for=\"TimeStart\" class=\"vlabel2\">Time 1st receipt</label><input type=\"text\" name=\"TimeStart\" class=\"vdata\" value=\"".$ride_data['TimeStart']."\" />";
	$res .= "<label for=\"TimeFinish\" class=\"vlabel2\">Time last receipt</label><input type=\"text\" name=\"TimeFinish\" class=\"vdata\" value=\"".$ride_data['TimeFinish']."\" /><br />";
	$res .= "<label for=\"RideVerifier\" class=\"vlabel2\">Verifier</label><input type=\"text\" name=\"RideVerifier\" class=\"vdata\" value=\"".$ride_data['RideVerifier']."\" />";
	$res .= "<label for=\"VerifierNotes\" class=\"vlabel2\">Verifier notes</label><textarea name=\"VerifierNotes\" class=\"vdata tall\">".$ride_data['VerifierNotes']."</textarea><br />";
	$res .= "<label for=\"DateVerified\" class=\"vlabel2\">Date verified</label><input type=\"date\" name=\"DateVerified\" class=\"vdata\" value=\"".$ride_data['DateVerified']."\" />";
	$res .= "<fieldset><legend>Verification</legend>";
	$res .= "<input type=\"radio\" name=\"Failed\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['Failed']).">FAILED</input>";
	$res .= "<input type=\"radio\" name=\"Failed\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['Failed']).">ok</input>";
	$res .= "</fieldset>";
	$res .= "</div>";
	
	$res .= "<div class=\"tabContent\" id=\"tab_paydata\">";
	$res .= "<fieldset><legend>Accounted for by</legend>";
	$res .= "<input type=\"radio\" name=\"OriginUK\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['OriginUK']).">IBAUK</input>";
	$res .= "<input type=\"radio\" name=\"OriginUK\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['OriginUK']).">another IBA</input>";
	$res .= "</fieldset>";
	$res .= "<div id=\"paydata\">";
	$res .= "<label for=\"PayMethod\" class=\"vlabel2\">Payment method</label><input type=\"text\" name=\"PayMethod\" class=\"vdata\" value=\"".$ride_data['PayMethod']."\" />";
	$res .= "<label for=\"DatePayReq\" class=\"vlabel2\">Date payment requested</label><input type=\"date\" name=\"DatePayReq\" class=\"vdata\" value=\"".$ride_data['DatePayReq']."\" /><br />";
	$res .= "<label for=\"DatePayRcvd\" class=\"vlabel2\">Date payment received</label><input type=\"date\" name=\"DatePayRcvd\" class=\"vdata\" value=\"".$ride_data['DatePayRcvd']."\" />";
	$res .= "<label for=\"DateCertSent\" class=\"vlabel2\">Date Certificate sent</label><input type=\"date\" name=\"DateCertSent\" class=\"vdata\" value=\"".$ride_data['DateCertSent']."\" /><br />";
	$res .= "<label for=\"USA_FeeDollars\" class=\"vlabel2\">USA fee ($)</label><input type=\"number\" name=\"USA_FeeDollars\" class=\"vdata\" value=\"".$ride_data['USA_FeeDollars']."\" />";
	$res .= "<label for=\"DateUSAPaid\" class=\"vlabel2\">Date USA paid</label><input type=\"date\" name=\"DateUSAPaid\" class=\"vdata\" value=\"".$ride_data['DateUSAPaid']."\" />";
	$res .= "<fieldset><legend>Reported to USA</legend>";
	$res .= "<input type=\"radio\" name=\"PassedToUSA\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['PassedToUSA']).">Yes</input>";
	$res .= "<input type=\"radio\" name=\"PassedToUSA\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['PassedToUSA']).">No</input>";
	$res .= "</fieldset>";
	$res .= "</div>";
	
	$res .= "<fieldset><legend>Show on Roll of Honour</legend>";
	$res .= "<input type=\"radio\" name=\"ShowRoH\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['ShowRoH']).">Yes</input>";
	$res .= "<input type=\"radio\" name=\"ShowRoH\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['ShowRoH']).">No</input>";
	$res .= "</fieldset>";
	
	$res .= "</div>";
	$res .= "<input type=\"submit\" />";
	$res .= "</form>";
	$res .= "</div>";
    start_html("Ride ".$ride_data['URI']);
    echo($res);
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
	if (!$OK) {
		show_rides_listing();
		exit;
	}
	show_ride_details_uri($_REQUEST[$CMDWORDS['uri']]);
	
}

function showNewRide()
{
		$rd['URI'] = 'newrec';
		$riderid = $_REQUEST['rid'][$_REQUEST['ix']];
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
		$bikeid = $_REQUEST['bik'][$_REQUEST['ix']];
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
		$rd['DateVerified'] = date('Y-m-d');
		$rd['OriginUK'] = 'Y';
		$rd['PayMethod'] = 'Paypal';

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
		return FALSE;
	if ($_POST['DateRideStart'] == '')
		return FALSE;
	if ($_POST['Bike'] == '')
		return FALSE;
	if ($_POST['TotalMiles'] == '')
		return FALSE;
	return TRUE;
}

function putRide()
{
	global $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	
	if (!$OK)
	{
		//var_dump($_SESSION);
		//echo(" [[".intval($_SESSION['ACCESSLEVEL'])."]] ");
		//var_dump($GLOBALS['ACCESSLEVEL_UPDATE']);
		show_rides_listing();
		exit;
	}

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
		$SQL .= "'".safesql($_POST['IsPillion'])."'";
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
	if ($_POST['bikeid'] == '')
	{
		$SQL = "INSERT INTO bikes (riderid,KmsOdo,Bike,Registration) VALUES (";
		$SQL .= safesql($_POST['riderid']);
		$SQL .= ",'".safesql($_POST['kmsOdo'])."'";
		$SQL .= ",'".safesql($_POST['Bike'])."'";
		$SQL .= ",'".safesql($_POST['Registration'])."'";
		$SQL .= ")";
		sql_query($SQL);
		$_POST['bikeid'] = dblastid('bikes','bikeid');
	}
	else
	{
		$SQL = "UPDATE bikes SET ";
		$SQL .= ",KmsOdo='".$_POST['KmsOdo']."'";
		$SQL .= ",Bike='".safesql($_POST['Bike'])."'";
		$SQL .= ",Registration='".safesql($_POST['Registration'])."'";
		$SQL .= " WHERE riderid=".$_POST['riderid']." AND bikeid=".$_POST['bikeid'];
		sql_query($SQL);
	}
	if ($_POST[$CMDWORDS['uri']] == 'newrec') {
		$SQL = "INSERT INTO rides (";
		$SQL .= "DateRideStart,DateRideFinish,NameOnCertificate,IBA_Ride,IsPillion,EventName,KmsOdo,StartOdo,FinishOdo,TotalMiles";
		$SQL .= ",riderid,bikeid";
		$SQL .= ",StartPoint,FinishPoint,MidPoints";
		$SQL .= ",WantCertificate,RiderNotes,DateRcvd,RideVerifier,Acknowledged,DateVerified,Failed";
		$SQL .= ",DatePayReq,DatePayRcvd,PayMethod,DateCertSent,USA_FeeDollars,DateUSAPaid,TimeStart,TimeFinish";
		$SQL .= ",RideHours,RideMins,VerifierNotes,ShowRoH,PassedToUSA,OriginUK";
		$SQL .= ") VALUES (";
		$SQL .= safedatesql($_POST['DateRideStart']);
		$SQL .= ",".safedatesql($_POST['DateRideFinish']);
		$SQL .= ",'".safesql($_POST['NameOnCertificate'])."'";
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
		$SQL .= ")";
	} elseif ($_POST['deletethisrec'] == 'Y') {
		$SQL = "UPDATE rides SET Deleted='Y' WHERE recid=".$_POST[$CMDWORDS['uri']];
	} else {
		$SQL = "UPDATE rides SET ";
		$SQL .= "DateRideStart=".safedatesql($_POST['DateRideStart']);
		$SQL .= ",DateRideFinish=".safedatesql($_POST['DateRideFinish']);
		$SQL .= ",NameOnCertificate='".safesql($_POST['DateRideFinish'])."'";
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
