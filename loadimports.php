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
	
	// SQLite can't do an UPDATE on a pair of JOINed tables so do this instead

	sql_query('BEGIN');
	$sql = "SELECT recid,rider_name,IBA_Number FROM bulkimports WHERE riderid Is Null";
	$R = sql_query($sql);
	while ($rd = $R->fetchArray()) {
		$sql = "SELECT riderid,rider_name,IBA_Number FROM riders WHERE ";
		$sql .= "rider_name LIKE '".safesql($rd['rider_name'])."'";
		if (!is_null($rd['IBA_Number']) && $rd['IBA_Number'] != '')
			$sql .= " OR IBA_Number LIKE '".safesql($rd['IBA_Number']);
		//echo('<hr>'.$sql);
		$RR = sql_query($sql);
		if ($rrd = $RR->fetchArray()) {
			$sql = "UPDATE bulkimports SET riderid=".$rrd['riderid'];
			if (!is_null(($rrd['IBA_Number']) && $rrd['IBA_Number'] != ''))
				$sql .= ", IBA_Number='".$rrd['IBA_Number']."'";
			$sql .= " WHERE recid=".$rd['recid'];
			//echo('<br>'.$sql);
			sql_query($sql);
		}
	}

	$sql = "SELECT recid,riderid,Bike,BikeReg FROM bulkimports WHERE riderid Is Not Null AND bikeid Is Null";
	$R = sql_query($sql);
	while ($rd = $R->fetchArray()) {
		$sql = "SELECT bikeid,Bike,Registration FROM bikes WHERE ";
		$sql .= "bikes.riderid=".$rd['riderid']. " AND (";
		if (!is_null($rd['BikeReg']) && $rd['BikeReg'] != '')
			$sql .=  "Registration LIKE '".safesql($rd['BikeReg'])."' OR";
		$sql .= " Bike LIKE '".safesql($rd['Bike'])."')";
		$sql .= " ORDER BY Bike,Registration DESC";
		//echo('<hr>'.$sql);
		$RR = sql_query($sql);
		if ($rrd = $RR->fetchArray()) {
			$sql = "UPDATE bulkimports SET bikeid=".$rrd['bikeid'];
			$sql .= ",Bike='".safesql($rrd['Bike'])."'";
			if (!is_null($rrd['Registration']) && $rrd['Registration'] != '')
				$sql .= ", BikeReg='".$rrd['Registration']."'";
			$sql .= " WHERE recid=".$rd['recid'];
			//echo('<br>'.$sql);
			sql_query($sql);
		}
	}
	sql_query('COMMIT');


}

function zap_import_records() {

	resetBulkimports();
}

function import_imports() {

	global $IMPORTSPEC;
	
	$ridedate		= $IMPORTSPEC['ridedate'];
	$ibaride		= $IMPORTSPEC['ibaride'];
	$rideverifier	= $IMPORTSPEC['rideverifier'];
	$paymethod		= $IMPORTSPEC['paymethod'];
	
	$sql = "SELECT * FROM bulkimports ORDER BY recid";
	$r = sql_query($sql);
	sql_query('BEGIN');
	$n = 0;
	$curdate = new DateTime;
	$curdatex = $curdate->format('Y-m-d');
	while ($rr = $r->fetchArray()) {

		$ridedate = $rr['ridedate'];
		$n++;
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
			$sql .= ",'".$curdatex."'";
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
			//echo($sql.'<br>');
			sql_query($sql);
			
			$rr['riderid'] = dblastid("riders","riderid");
		}
		else // Need to update DateLastActive
		{
			$sql = "UPDATE riders SET CurrentMember='Y', DateLastActive='".$curdatex."'";
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
			$sql = "INSERT INTO bikes (riderid,Bike,Registration) VALUES (";
			$sql .= $rr['riderid'];
			$sql .= ",'".safesql($rr['Bike'])."'";
			$sql .= ",'".safesql($rr['BikeReg'])."'";
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
			
			//echo($rr['rider_name'].'<br>');
			// Ride records. If this ride's route is not defined, just drop the record
			if (!isset($IMPORTSPEC['routes'][$rr['route_number']])) 
				continue;

			$rqq = $IMPORTSPEC['routes'][$rr['route_number']];

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
	sql_query('COMMIT');
	zap_import_records();
	echo("<p>$n Data records loaded - Import complete.</p>");
}

function browse_imports($seq,$offset,$nrows) {

	global $IMPORTSPEC;
	
	//error_log('Matching');
	match_imports();
	//error_log('matched');

	$sql = "SELECT * FROM bulkimports ORDER BY $seq LIMIT $offset, $nrows";
	//echo("<p>[[ $sql ]]</p>");
	//error_log($sql);
	$r = sql_query($sql);
	//error_log('retrieved');
	$RideRally = ($IMPORTSPEC['ride_rally']==0 ? 'Ride' : 'Rally');
	echo("<p>$RideRally Data ready for upload</p>");
?>
<script>
function enableRematch()
{
	document.getElementById('retry').disabled = false;
}
function checkSubmit()
{
	if (!document.getElementById('retry').disabled) 
		return window.confirm("You have unsaved changes, are you sure?");
	else
		return true;
}
</script>
<?php

	echo('<form method="post" onsubmit="return checkSubmit();">');
	if ($IMPORTSPEC['ride_rally']==0) {
		echo("<label for=\"ridedate\">Ride date </label>");
		echo('<input type="date" title="Must be a valid date" id="ridedate" name="ridedate" value="'.$IMPORTSPEC['ridedate'].'" onchange="enableRematch();">' );  // Preserve override
	}
	echo("<label for=\"eventid\"> Event name </label>");
	echo('<input type="text" id="eventid" name="eventid" value="'.$IMPORTSPEC['eventid'].'" onchange="enableRematch();"> ' );	  // Preserve override
	echo("<input type=\"hidden\" name=\"cmd\" value=\"loadimports\">");

	echo('<input title="Update the import records and rerun the matching process" onclick="this.disabled=true;" type="submit" name="retry" id="retry" disabled value="Update, rematch"> ');

	echo("<input type=\"submit\" name=\"zap\" value=\"Ok, go for it!\"> ");
	echo("<table class=\"scrollable\">");
	echo("<thead><tr style=\"display:block;\">");
	echo('<th class="cellRecid">#</th><th class="cellName">Name</th>');
	echo('<th class="cellRecid">#</th><th class="cellRecid">IBA #</th><th class="cellRP">R/P</th>');
	echo('<th class="cellBike">Bike</th><th class="cellReg">BikeReg</th><th class="cellRecid">#</th>');
	echo('<th class="cellStars" title="RideStars: what\'s special about this if anything">**</th><th class="cellRoute">Route</th>');
	echo('<th class="cellPoints">Points</th><th class="cellMiles">Miles</th><th class="cellRank">Placed</th>');
	echo('<th class="cellDrop" title="Drop this record">Drop</th>');
	echo("</tr></thead>");
	echo('<tbody style="display:block; width:100%; height: 60em; overflow-y: scroll;">');
	$row = "1";
	$alert = " class=\"infohilite\"";
	while ($rr = $r->fetchArray()) {
		//error_log($rr['rider_name']);
		echo("<tr class=\"row-$row\">");
		if ($row == '1')
			$row = '2';
		else
			$row = '1';
		echo('<td class="cellRecid"><input type="text" readonly name="recid[]" value="'.$rr['recid'].'"></td>');
		
		if ($rr['riderid'] <> '') {
			$c = '';
			$ro = " readonly ";
			$ti = 'Update existing rider record #'.$rr['riderid'];
		} else {
			$c = $alert;
			$ro = '';
			$ti = 'Create new rider record';
		}
		echo('<td class="cellName"><input type="text" '.$ro.' name="rider_name[]" value="'.$rr['rider_name'].'" onchange="enableRematch();"></td>');
		echo('<td'.$c.' title="'.$ti.'" class="cellRecid">'.$rr['riderid'].'</td>');
		echo('<td class="cellRecid"><input type="text" '.$ro.' name="IBA_number[]"  value="'.$rr['IBA_number'].'" onchange="enableRematch();"></td>');
		$rp = $rr['is_pillion'] ? 'Pillion' : 'Rider';
		echo('<td class="cellRP">'.$rp.'</td>');
		if (($rr['bikeid'] <> '')) {
			$c = '';
			$ro = " readonly ";
			$bikes = $rr['bikeid'] <> '' ? 'Identified existing bike #'.$rr['bikeid'] : 'Create new bike record';
		} else {
			$c = $alert;
			$ro = '';
			$bikes = '';
			if ($rr['riderid'] <> '') {
				$ssql = "SELECT Bike,bikeid FROM bikes WHERE riderid=".$rr['riderid'];
				$rb = sql_query($ssql);
				while ($rbd = $rb->fetchArray())
					$bikes .= $rbd['bikeid'].': '.$rbd['Bike']."\r\n";
				if ($bikes == '')
					$bikes = "No existing bike records";
				else
					$bikes = "Creating new record.\r\nExisting bikes:\r\n".$bikes;
			} 

		}
		echo('<td class="cellBike"><input type="text" '.$ro.' name="bike[]" value="'.$rr['Bike'].'" onchange="enableRematch();"></td>');
		echo('<td class="cellReg"><input type="text" '.$ro.' name="BikeReg[]" value="'.$rr['BikeReg'].'" onchange="enableRematch();"></td>');
		echo('<td'.$c.' title="'.$bikes.'" class="cellRecid">'.$rr['bikeid'].'</td>');
		echo('<td class="cellStars">'.$rr['ridestars'].'</td>');
		echo('<td class="cellRoute">'.$rr['route_number'].'</td>');
		echo('<td class="cellPoints">'.$rr['points'].'</td>');
		echo('<td class="cellMiles">'.$rr['miles'].'</td>');
		echo('<td class="cellRank">'.$rr['finishposition'].'</td>');
		echo('<td class="cellDrop"title="Tick to drop this record from the batch"><input type="checkbox" name="omitthisrec[]" value="'.$rr['recid'].'" onchange="enableRematch();"></td>');
		echo("</tr>");
	}
	echo("</tbody></table>");
	echo("</form>");

	//echo('</div>');
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
	else
		show_imports();
	
}

?>
