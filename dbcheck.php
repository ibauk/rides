<?php
/*
 * I B A U K - dbcheck.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 */

function mergeRiders($oldRider,$newRider)
{
	/*
	 * This merges data from $oldRider. including rides, rallies, bikes, etc into $newRider
	 * which must already exist then removes all records relating to $oldRider.
	 *
	 */
	 
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();
	
	$SQL = "SELECT Deleted FROM riders WHERE riderid=$newRider";
	$rr = sql_query($SQL);
	$rd = mysqli_fetch_assoc($rr);
	mysqli_close($rr);

	if ($rd == false)
	{
		show_infoline("New rider [id=$newRider] doesn't exist",'errormsg');
		exit;
	}
	if ($rd['Deleted']=='Y')
	{
		show_infoline("New rider [id=$newRider] is deleted",'errormsg');
		exit;
	}
	// Rallies
	$SQL = "UPDATE rallyresults SET riderid=$newRider WHERE riderid=$oldRider";
	sql_query($SQL);
	
	// Mileeaters
	$SQL = "UPDATE mileeaters SET riderid=$newRider WHERE riderid=$oldRider";
	sql_query($SQL);

	// Bikes
	$SQL = "UPDATE bikes SET riderid=$newRider WHERE riderid=$oldRider";
	sql_query($SQL);
//	show_infoline("Merging $oldRider into $newRider",'infohilite');

	// Rides
	$SQL = "UPDATE rides SET riderid=$newRider WHERE riderid=$oldRider";
	sql_query($SQL);

	// and the rider record itself
	$SQL = "UPDATE riders SET Deleted='Y' WHERE riderid=$oldRider";
	sql_query($SQL);
	
	// remove the tag for $oldRider
	$tagid = "riderids['$oldRider']";
	if (isset($_SESSION[$tagid]))
		unset($_SESSION[$tagid]);

}

function mergeRidersList()
{
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();
	$riderids = explode(",",$_REQUEST['riderids']);
	$targetid = $_REQUEST['targetid'];
	if ($targetid=='')
	{
		show_infoline('You must specify the riderid of the target record!','errormsg');
		exit;
	}
	$OK = false;
	foreach ($riderids as $rid)
	{
		if ($rid != $targetid)
		{
			$OK = true;
			mergeRiders($rid,$targetid);
		}
	}
	if (!$OK)
	{
		show_infoline('No records merged','infohilite');
		exit;
	}
	include("riders.php");
	$_SESSION['BACK2LIST'] = -2; // Offer back to list = refresh tagged riders
	show_rider_details_uri($targetid);	
	
	
}

function mergeBikes()
{
	/*
	 * This carries out a database cleansing operation by merging references to bike records
	 * into a single new record.  This is to clean up data imported from earlier versions of
	 * the database which allowed for looser data integrity.
	 */
	 
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();
	
	 $riderid = $_POST['riderid'];
	 $bikeids = $_POST['SelectBike'];
	 $ix = count($_POST['bikeid']) - 1;
	 if ($_POST['bikeid'][$ix] == 'newrec')
	 {
		 $bike = $_POST['Bike'][$ix];
		 $reg = $_POST['Registration'][$ix];
		 $kmsodo = $_POST["KmsOdo:$ix"][0];
	 }
	 else
	 {
		 show_infoline("You need to select at least two existing bikes and enter make &amp; model of replacement bike","errormsg");
		 return;
	 }
	
	// Validation complete, save the new master bike
	 $SQL = "INSERT INTO bikes (riderid,Bike,Registration,KmsOdo) VALUES (";
	 $SQL .= $riderid.",'".safesql($bike)."','".safesql($reg)."','".$kmsodo."')";
	 //echo($SQL."<hr />");
	 sql_query($SQL);
	 $newbikeid = dblastid('bikes','bikeid');
	 
	 // Update affected ride records
	 $SQL = "UPDATE rides SET bikeid=".$newbikeid." WHERE riderid=".$riderid." AND (";
	 $or = '';
	 foreach ($bikeids as $bi)
	 {
		 $SQL .= $or."bikeid=".$bi;
		 $or = ' OR ';
	 }
	 $SQL .= ")";
	 //echo($SQL."<hr />");
	 sql_query($SQL);
	 
	 // Zap redundant bike records
	 $SQL = "DELETE FROM bikes WHERE ";
	 $or = '';
	 foreach ($bikeids as $bi)
	 {
		 $SQL .= $or."bikeid=".$bi;
		 $or = ' OR ';
	 }
	 //echo($SQL."<hr />");
	 sql_query($SQL);

	require_once("riders.php");
	//show_infoline("Riderid is $riderid","infohilite");
	show_rider_details_uri($riderid);
}

function showFilteredRiders()
{
	global $CMDWORDS;
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
	start_html("Tagged riderlist");
	echo("<div class=\"maindata\">");
	
	$SQL = "SELECT riders.Rider_Name,riders.IBA_Number,riders.Postal_Address,riders.Postcode,riderid FROM riders WHERE ";
	$SQL .= "riderid IN (";
	$ids = '';
	foreach ($_SESSION as $r)
		if (preg_match("/riderids\['(\d+)'\]/",$r,$m))
		{
			if ($ids != '') $ids .= ',';
			$ids .= $m[1];
		}
	
	$SQL .= ($ids == '' ? '-1' : $ids).')';
	if ($_SESSION['ShowDeleted'] <> 'Y')
		$SQL .= " AND riders.Deleted='N'";
	
	//echo($SQL."<hr>");
	//var_dump($_SESSION);
	$rs = sql_query($SQL);
	$TotRows = mysqli_num_rows($rs);
	if ($TotRows < 1)
	{
		echo("<p class=\"sorry\">So sorry Anjin-san, I am unable to find any tagged records 	\xF0\x9F\x98\x93</p>");
		echo("</div></body></html>");
		return;
	}
	echo ("<table><caption>Tagged Rider List (".$TotRows.")</caption>");
	$hdrwritten = false;
	$row = 0;
	while (true)
	{
		$rd = mysqli_fetch_assoc($rs);
		if ($rd == false) break;
		$row++;
		if (!$hdrwritten)
		{
			echo ("<tr>");
			foreach ($rd as $fld=>$val)
				echo("<th>$fld</th>");
			echo ("</tr>");
			$hdrwritten = true;
		}
		$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['showrider']."&".$CMDWORDS['uri']."=".$rd['riderid']."'\" class=\"goto row-";
		echo("<tr ".$trspec.(($row % 2) + 1)."\">");
		foreach ($rd as $fld=>$val)
			echo ("<td>".htmlentities($val)."</td>");
		echo ("</tr>");
	}
	echo ("</table>");
	if ($ids != '')
	{
		echo("<form action=\"index.php\" method=\"post\">");
		echo("<input type=\"hidden\" name=\"cmd\" value=\"mergeriders\">");
		echo("<input type=\"hidden\" name=\"riderids\" value=\"$ids\">");
		echo("<input type=\"submit\" value=\"Merge tagged records into\">");
		echo("<select name=\"targetid\">");
		echo("<option selected value=\"\">Select the riderid to merge into</option>");
		foreach (explode(',',$ids) as $id)
			echo("<option value=\"$id\">$id</option>");
		echo("</select>");
		echo("</form>");
	}
	echo("</div></body></html>");
	
}

function showDuplicateRiders()
{
	global $CMDWORDS;
	
	$SQL = "SELECT riders.Rider_Name,riders.IBA_Number,riders.Postal_Address,riders.Postcode,riderid FROM riders WHERE IBA_Number In (SELECT IBA_Number FROM riders WHERE IBA_Number <> '' GROUP BY IBA_Number HAVING Count(IBA_Number)>1) ORDER BY IBA_Number";
	$rs = sql_query($SQL);
	$TotRows = mysqli_num_rows($rs);
	if ($TotRows < 1)
		return;
	echo ("<table><caption>Duplicated IBA numbers (".$TotRows.")</caption>");
	$hdrwritten = false;
	$row = 0;
	while (true)
	{
		$rd = mysqli_fetch_assoc($rs);
		if ($rd == false) break;
		$row++;
		if (!$hdrwritten)
		{
			echo ("<tr>");
			foreach ($rd as $fld=>$val)
				echo("<th>$fld</th>");
			echo ("</tr>");
			$hdrwritten = true;
		}
		$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['showrider']."&".$CMDWORDS['uri']."=".$rd['riderid']."'\" class=\"goto row-";
		echo("<tr ".$trspec.(($row % 2) + 1)."\">");
		foreach ($rd as $fld=>$val)
			echo ("<td>".htmlentities($val)."</td>");
		echo ("</tr>");
	}
	echo ("</table>");
}

function checkDatabase()
{
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
	start_html("Checking database");
	echo("<div class=\"maindata\">");
	showDuplicateRiders();
	echo("</div></body></html>");
}
?>

