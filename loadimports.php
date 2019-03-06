<?php
/*
 * I B A U K - loadimports.php
 *
 * Copyright (c) 2018 Bob Stammers
 *
 * 2018-01 - Update CurrentMember, DateLastActive on new ride/rally data
 *
 */

 
$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
if (!$OK) safe_default_action();

require_once './includespec.php';
 
function match_imports() {
	
	// Match on IBA number if entered
	$sql = "UPDATE bulkimports JOIN riders ON bulkimports.IBA_number=riders.IBA_Number SET bulkimports.riderid=riders.riderid WHERE bulkimports.IBA_number Is Not Null AND bulkimports.riderid Is Null AND event.entrants.IBA_number<>''";
	//echo($sql."<hr />");
	sql_query($sql);
	
	// Match on name if no IBA number
	$sql = "UPDATE bulkimports JOIN riders ON bulkimports.rider_name=riders.rider_name SET bulkimports.riderid=riders.riderid,bulkimports.IBA_number=riders.IBA_Number WHERE (bulkimports.IBA_number Is Null OR bulkimports.IBA_number='') AND bulkimports.riderid Is Null";
	//echo($sql."<hr />");
	sql_query($sql);
	
	$sql = "UPDATE bulkimports JOIN bikes ON REPLACE(bulkimports.Bike,' ','')=REPLACE(bikes.Bike,' ','') AND bulkimports.riderid=bikes.riderid SET bulkimports.bikeid=bikes.bikeid WHERE bulkimports.riderid Is Not Null AND bulkimports.bikeid Is Null";
	//echo($sql."<hr />");
	sql_query($sql);
}

function zap_import_records() {

	$sql = "TRUNCATE bulkimports";
	sql_query($sql);
}

function import_imports() {

	global $IMPORTSPEC;
	
	$ridedate		= $IMPORTSPEC['ridedate'];
	$ibaride		= $IMPORTSPEC['ibaride'];
	$rideverifier	= $IMPORTSPEC['rideverifier'];
	$paymethod		= $IMPORTSPEC['paymethod'];
	
	$sql = "SELECT * FROM bulkimports ORDER BY recid";
	$r = sql_query($sql);
	while ($rr = mysqli_fetch_assoc($r)) {

		$ridedate = $rr['ridedate'];
		
		// The blank tests below are to ensure that we don't overwrite existing data
		// if the facility for new data exists in the import file but is not
		// actually supplied. RBLR1000 for example.
		
		if ($rr['riderid'] == '') { // Need to add new rider
			$sql = "INSERT INTO riders (Rider_Name,IBA_Number,IsPillion,DateLastActive";
			if ($rr['Email'] != '')
				$sql .= ",Email";
			if ($rr['Postal_Address'] != '')
				$sql .= ",Postal_Address";
			if ($rr['Postcode'] != '')
				$sql .= ",Postcode";
			if ($rr['Phone'] != '')
				$sql .= ",Phone";
			if ($rr['AltPhone'] != '')
				$sql .= ",AltPhone";
			$sql .= ") VALUES (";
			$sql .= "'".safesql($rr['rider_name'])."'";
			$sql .= ",'".safesql($rr['IBA_number'])."'";
			$sql .= ",".$rr['is_pillion'];
			$sql .= ",CurDate()";
			if ($rr['Email'] != '')
				$sql .= ",'".safesql($rr['Email'])."'";
			if ($rr['Postal_Address'] != '')
				$sql .= ",'".safesql($rr['Postal_Address'])."'";
			if ($rr['Postcode'] != '')
				$sql .= ",'".safesql($rr['Postcode'])."'";
			if ($rr['Phone'] != '')
				$sql .= ",'".safesql($rr['Phone'])."'";
			if ($rr['AltPhone'] != '')
				$sql .= ",'".safesql($rr['AltPhone'])."'";
			$sql .= ")";
			sql_query($sql);
			
			$rr['riderid'] = dblastid("riders","riderid");
		}
		else // Need to update DateLastActive
		{
			$sql = "UPDATE riders SET CurrentMember='Y', DateLastActive=CurDate() ";
			if ($rr['Email'] != '')
				$sql .= ",Email='".safesql($rr['Email'])."'";
			if ($rr['Postal_Address'] != '')
				$sql .= ",Postal_Address='".safesql($rr['Postal_Address'])."'";
			if ($rr['Postcode'] != '')
				$sql .= ",Postcode='".safesql($rr['Postcode'])."'";
			if ($rr['Phone'] != '')
				$sql .= ",Phone='".safesql($rr['Phone'])."'";
			if ($rr['AltPhone'] != '')
				$sql .= ",AltPhone='".safesql($rr['AltPhone'])."'";
			$sql .= " WHERE riderid=".$rr['riderid'];
			sql_query($sql);
		}
		if ($rr['bikeid'] == '') { // Need to add new bike
			$sql = "INSERT INTO bikes (riderid,Bike) VALUES (";
			$sql .= $rr['riderid'];
			$sql .= ",'".safesql($rr['Bike'])."'";
			$sql .= ")";
			sql_query($sql);
			$rr['bikeid'] = dblastid("bikes","bikeid");			
		}
		if ($rr['ride_rally'] == 1) {
			$sql = "INSERT INTO rallyresults (RallyID,FinishPosition,riderid,bikeid,RallyMiles,RallyPoints) VALUES (";
			$sql .= "'".safesql($rr['EventID'])."'";
			$sql .= ",".$rr['finishposition'];
			$sql .= ",".$rr['riderid'];
			$sql .= ",".$rr['bikeid'];
			$sql .= ",".$rr['miles'];
			$sql .= ",".$rr['points'];
			$sql .= ")";
			sql_query($sql);
		} else {
			
			if (!isset($IMPORTSPEC['routes'][$rr['route_number']])) {
				$rqq['startpoint'] = $rr['route_number'];
				$rqq['viapoints'] = $rr['route_number'];
				$rqq['finishpoint'] = $rr['route_number'];
				$rqq['miles'] = $rr['route_number'];
			} else {
				$rqq = $IMPORTSPEC['routes'][$rr['route_number']];
			}
			$sql = "INSERT INTO rides (riderid,NameOnCertificate,DateRideStart,DateRideFinish,IBA_Ride";
			$sql .= ",IsPillion,EventName,TotalMiles,bikeid,StartPoint,FinishPoint,MidPoints,WantCertificate";
			$sql .= ",DateRcvd,RideStars,RideVerifier,Acknowledged,DateVerified,DatePayReq,DatePayRcvd";
			$sql .= ",PayMethod,DateCertSent,ShowRoH) VALUES (";
			$sql .= $rr['riderid'];
			$sql .= ",'".safesql($rr['rider_name'])."'";
			$sql .= ",'$ridedate','$ridedate'";
			if (isset($rqq['ibaride']))
				$sql .= ",'".$rqq['ibaride']."'";
			else
				$sql .= ",'$ibaride'";
			$sql .= ",";
			if ($rr['is_pillion']==1)
				$sql .= "'Y'";
			else
				$sql .= "'N'";
			$sql .= ",'".safesql($rr['EventID'])."'";
			$sql .= ",".$rqq['miles'];
			$sql .= ",".$rr['bikeid'];
			$sql .= ",'".safesql($rqq['startpoint'])."'";
			$sql .= ",'".safesql($rqq['finishpoint'])."'";
			$sql .= ",'".safesql($rqq['viapoints'])."'";
			$sql .= ",'Y','$ridedate'";
			$sql .= ",'".safesql($rr['ridestars'])."'";
			$sql .= ",'".$rideverifier."'";
			$sql .= ",'Y','$ridedate','$ridedate','$ridedate'";
			$sql .= ",'$paymethod'";
			$sql .= ",'$ridedate','Y')";
			//echo($sql."<br />");
			sql_query($sql);
		}
	}
	zap_import_records();
	echo("<p>Data all loaded</p>");
}

function browse_imports($seq,$offset,$nrows) {

	global $IMPORTSPEC;
	
	match_imports();

	$sql = "SELECT * FROM bulkimports ORDER BY $seq LIMIT $offset, $nrows";
	//echo("<p>[[ $sql ]]</p>");
	$r = sql_query($sql);
	echo("<p>Data ready for upload</p>");
	echo("<form method=\"post\">");
	echo("<label for=\"ridedate\">Ride date </label>");
	echo("<input type=\"date\" title=\"Must be a valid date\" id=\"ridedate\" name=\"ridedate\" value=\"".$IMPORTSPEC['ridedate']."\">" );  // Preserve override
	echo("<label for=\"eventid\"> Event name </label>");
	echo("<input type=\"text\" id=\"eventid\" name=\"eventid\" value=\"".$IMPORTSPEC['eventid']."\"> " );	  // Preserve override
	echo("<input type=\"hidden\" name=\"cmd\" value=\"loadimports\">");
	echo("<input type=\"submit\" name=\"retry\" value=\"Update, rematch\"> ");
	echo("<input type=\"submit\" name=\"zap\" value=\"Ok, go for it!\"> ");
	echo("<table>");
	echo("<thead><tr>");
	echo("<th>#</th><th>Rider</th>");
	echo("<th>#</th><th>IBA #</th><th>Pillion</th>");
	echo("<th>Bike</th><th>#</th><th>RideStars</th><th>Route</th>");
	echo("<th>Points</th><th>Miles</th><th>Placed</th>");
	echo("<th>Drop this record</th>");
	echo("</tr></thead><tbody>");
	$row = "1";
	$alert = " class=\"infohilite\"";
	while ($rr = mysqli_fetch_assoc($r)) {
		echo("<tr class=\"row-$row\">");
		if ($row == '1')
			$row = '2';
		else
			$row = '1';
		echo("<td><input type=\"text\" readonly name=\"recid[]\" class=\"vdata short\" style=\"width:30px;\" value=\"".$rr['recid']."\"></td>");
		
		if ($rr['riderid'] <> '') {
			$c = '';
			$ro = " readonly ";
		} else {
			$c = $alert;
			$ro = '';
		}
		echo("<td><input type=\"text\" $ro name=\"rider_name[]\" class=\"vdata\" value=\"".$rr['rider_name']."\"></td>");
		echo("<td$c>".$rr['riderid']."</td>");
		echo("<td><input type=\"text\" $ro name=\"IBA_number[]\" class=\"vdata short\" value=\"".$rr['IBA_number']."\"></td>");
		echo("<td>".$rr['is_pillion']."</td>");
		if (($rr['bikeid'] <> '') || ($rr['riderid'] == '')) {
			$c = '';
			$ro = " readonly ";
			$bikes = '';
		} else {
			$c = $alert;
			$ro = '';
			$ssql = "SELECT Bike FROM bikes WHERE riderid=".$rr['riderid'];
			$rb = sql_query($ssql);
			$bikes = '';
			while ($rbd = mysqli_fetch_assoc($rb))
				$bikes .= $rbd['Bike']."\r\n";
			if ($bikes == '')
				$bikes = "No existing bike records";
		}
		echo("<td><input type=\"text\" $ro name=\"bike[]\" class=\"vdata\" value=\"".$rr['Bike']."\"></td>");
		echo("<td$c title=\"$bikes\">".$rr['bikeid']."</td>");
		echo("<td>".$rr['ridestars']."</td>");
		echo("<td>".$rr['route_number']."</td>");
		echo("<td>".$rr['points']."</td>");
		echo("<td>".$rr['miles']."</td>");
		echo("<td>".$rr['finishposition']."</td>");
		echo("<td class=\"center\" title=\"Tick to drop this record from the batch\"><input type=\"checkbox\" name=\"omitthisrec[]\" value=\"".$rr['recid']."\"></td>");
		echo("</tr>");
	}
	echo("</tbody></table>");
	echo("</form>");
}

function show_imports() {

	if (isset($_REQUEST['order']))
		$order = $_REQUEST['order'];
	else
		$order = 'recid';
	if (isset($_REQUEST['offset']))
		$offset = $_REQUEST['offset'];
	else
		$offset = 0;
	if (isset($_REQUEST['pagesize']))
		$pagesize = $_REQUEST['pagesize'];
	else
		$pagesize = 200;

	browse_imports($order,$offset,$pagesize);

}

function update_import_records() {

	if (isset($_REQUEST['omitthisrec'])) {
		$imax = count($_REQUEST['omitthisrec']);
		for ($ix = 0; $ix < $imax; $ix++) {
			$sql = "DELETE FROM bulkimports WHERE recid = ".$_REQUEST['omitthisrec'][$ix];
			sql_query($sql);
		}
	}

	// Update date/event using live fields
	sql_query("UPDATE bulkimports SET RideDate='".$_REQUEST['ridedate']."',EventID='".safesql($_REQUEST['eventid'])."'");
	
	// This next chunk will try to update records deleted above but life's too short 
	$imax = count($_REQUEST['recid']);
	//echo("<br />$imax<br />");
	
	for ($ix = 0; $ix < $imax; $ix++) {
		$recid = $_REQUEST['recid'][$ix];
		$sql = "UPDATE bulkimports SET rider_name='".safesql($_REQUEST['rider_name'][$ix])."'";
		$iba = $_REQUEST['IBA_number'][$ix];
		if ($iba <> '')
			$sql .= ",IBA_number='".safesql($_REQUEST['IBA_number'][$ix])."'";
		else
			$sql .= ",IBA_number=Null";
		$sql .= ",Bike='".safesql($_REQUEST['bike'][$ix])."'";
		$sql .= " WHERE recid = ".$recid;
		//echo($sql."<br />");
		sql_query($sql);
	}
}


function update_imports() {

	//var_dump($_REQUEST);

	start_html("Importing");
	if (isset($_REQUEST['recid']))
		update_import_records();
	if (isset($_REQUEST['zap']))
		import_imports();
	show_imports();
	
}

?>
