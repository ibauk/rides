<?php
/*
 * I B A U K - ridelists.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 */



function rides_table_row_header($cols,$hdrs)
{
    global $MYKEYWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
	for ($i=0 ; $i < count($cols); $i++)
	{
		$hdrx = $hdrs[$i];
		$colx = $cols[$i];
		if (preg_match("/date/i",$cols[$i]))
			$cls = ' class="date"';
		else
			$cls = '';
		$res .= "<th$cls>".column_anchor($hdrx,$colx)."</th>";
	}
    return $res;
}

function rides_table_row_html($ride_data,$cols)
{
    global $MYKEYWORDS, $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
	for ($i=0; $i < count($cols); $i++)
	{
		if (preg_match("/date/i",$cols[$i]))
			$cls = ' class="date"';
		else
			$cls = '';
		$res .= "<td$cls>".$ride_data[$cols[$i]]."</td>";
	}
    return $res;
}



function start_new_ride_link()
{
	$res = "<span class=\"inlinemenu\" title=\"Enter the rider's name or number to start a new ride\">";
	$res .= "<script>function checkRider() {if (document.getElementById('riderIdText').value == '') document.getElementById('riderIdText').value = window.prompt('Please specify the Rider\'s name or IBA #',''); return (document.getElementById('riderIdText').value != ''); }</script>";
	$res .= "<form style=\"display:inline; margin:0; padding: 0;\" action=\"index.php\" method=\"get\">";
	$res .= "<input type=\"hidden\" name=\"c\" value=\"startride\">";
	$res .= "<input type=\"text\" name=\"f\" id=\"riderIdText\" placeholder=\"Identify rider\">";
	$res .= "<input type=\"submit\" onclick=\"return checkRider();\" style=\"font-size:.95em;\" value=\"Start new ride\" >";
	$res .= "</form></span>";
	return $res;

}

function show_rides_table($where,$caption,$colselection)
{
    global $CMDWORDS;
	global $KEY_ORDER, $KEY_DESC, $PAGESIZE, $OFFSET, $SHOWALL;

	$RIDES_SQL  = "SELECT SQL_CALC_FOUND_ROWS *,Concat_WS(' : ',NameOnCertificate,NullIf(RideStars,'')) As RoH_Name FROM rides LEFT JOIN riders ON rides.riderid=riders.riderid LEFT JOIN bikes ON rides.bikeid=bikes.bikeid ";
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	
    $SQL = $RIDES_SQL;

	// Also need to replace table headings.
	$hdrs = [];
	$cols = [];
	if ($colselection<>'')
	{
		$ca = explode(';',$colselection);
		foreach ($ca as $cap)
		{
			$caps = explode(',',$cap);
			array_push($hdrs,$caps[1]);
			array_push($cols,$caps[0]);
		}
		
	}
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
	$xl = '';
	if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE'])
		$xl = start_new_ride_link();
	echo("<div class=\"maindata\">");
	if ($TotRows > mysqli_num_rows($ride))
		show_common_paging($TotRows,$xl);
    echo("<table>");
	if ($caption<>'') 
	{
		echo("<caption>$caption (".number_format($TotRows).")");
		if ($_SESSION['ShowDeleted']=='Y')
			echo(" (including deletions)");
		echo("</caption>");
	}
	echo("<tr>".rides_table_row_header($cols,$hdrs)."</tr>\n");
	$rownum = 0;
    while(true)
    {
        $ride_data = mysqli_fetch_assoc($ride);
        if ($ride_data == false) break;
		
		if ($OK)
			$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['showride']."&".$CMDWORDS['uri']."=".$ride_data['URI']."'\" class=\"goto row-";
		else
			$trspec = "class=\"row-";
		$rownum++;
		if ($rownum % 2 == 1)
			echo("<tr ".$trspec."1\">");
		else
			echo("<tr ".$trspec."2\">");
		
		
		
        echo(rides_table_row_html($ride_data,$cols)."</tr>\n");
    }
    echo("</table>");
	if ($TotRows > mysqli_num_rows($ride))
		show_common_paging($TotRows,$xl);
	if ($OK && $TotRows > 0)
	{
?>
	<form action="index.php" method="post">
	<input type="hidden" name="cmd" value="csv">
	<input type="hidden" name="sql" value="<?php echo(urlencode($RIDES_SQL));?>">
	<input type="hidden" name="cols" value="<?php echo(urlencode($colselection));?>">
	<input type="hidden" name="where" value="<?php echo(urlencode($where));?>">
	<input type="submit" value="Download as .CSV">
	<form>
<?php
	}
	elseif ($TotRows < 1)
		echo("<p>No rides to show, sorry about that \xF0\x9F\x98\x9E</p>");
	echo("</div>");
}


function show_paged_rides_table($where,$caption,$cols)
{
    global $OFFSET, $PAGESIZE, $SHOW, $MYKEYWORDS, $STARTED;

    if ($STARTED == '') 
		start_html($MYKEYWORDS['rides']." listing");
    if ($_GET['show']=='all')
    {
        $OFFSET = 0;
        $PAGESIZE = -1;
    }

	show_rides_table($where,$caption,$cols);
	
    echo("</body></html>");
	
}


// High-level ride listing options

function show_full_rides_listing()
{
    global $KEY_ORDER, $KEY_DESC;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) 
	{
		show_roll_of_honour();
		exit;
	}

	$rptname = 'fullrides';
	
	$select = "DateRideStart,Date;NameOnCertificate,Rider;IBA_Number,IBA#;IBA_Ride,Ride;Bike,Bike;EventName,Event;URI,URI";
	$where = "";
	if ($_SESSION['ShowDeleted'] <> 'Y')
		$where .= " rides.Deleted='N'";
	$caption = "All Rides";
	//echo("'".$_SESSION['KEY_REPORT']."'=='$rptname'; Order='$KEY_ORDER'<hr />");
	if ($_SESSION['KEY_REPORT'] <> $rptname)
	{
		$KEY_ORDER = 'URI';
		$KEY_DESC = $KEY_ORDER;
		$_SESSION['KEY_REPORT'] = $rptname;
	}
	show_paged_rides_table($where,$caption,$select);
}



function show_roll_of_honour()
{
    global $KEY_ORDER, $KEY_DESC;
	
	$rptname = 'roh';

	$select = "DateRideStart,Date;RoH_Name,Rider;IBA_Number,IBA#;IBA_Ride,Ride;Bike,Bike;EventName,Event";
	$where = "ShowRoH='Y'";
	if ($_SESSION['ShowDeleted'] <> 'Y')
		$where .= " AND rides.Deleted='N'";
	$caption = "Roll of Honour";
	if ($_SESSION['KEY_REPORT'] <> $rptname)
	{
		$KEY_ORDER = 'DateRideStart';
		$KEY_DESC = $KEY_ORDER;
		$_SESSION['KEY_REPORT'] = $rptname;
	}
	show_paged_rides_table($where,$caption,$select);
}

function mark_sent_to_USA()
{
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();

	$sql = "UPDATE rides SET PassedToUSA='Y',DateUSAPaid=Date(Now()) WHERE (Failed='N' AND OriginUK='Y' AND PassedToUSA='N' AND ShowRoH='Y')";
	sql_query($sql);
	show_infoline('All outstanding rides marked as having been reported to the USA','infohilite');
	show_full_rides_listing();
}




function show_listreport()
{
    global $KEY_ORDER, $KEY_DESC;

	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
	// Currently ONLY rides reports catered for so reject other definitions
	
	$rptname = $_REQUEST['rptid'];
	//echo("Showing $rptname");

	$sql = "SELECT * FROM listreports WHERE rptid='".safesql($rptname)."'";
	$rs = sql_query($sql);
	$rd = mysqli_fetch_assoc($rs);
	if (!$rd)
	{
		show_roll_of_honour();
		exit;
	}
	$OK = ($_SESSION['ACCESSLEVEL'] >= $rd['AccessLevel']);
	if (!$OK || $rd['Subject'] <> 1)
	{
		show_roll_of_honour();
		exit;
	}
	
	$select = $rd['SelectCols'];
	$where = $rd['WhereConditions'];
	if ($_SESSION['ShowDeleted'] <> 'Y')
		$where .= " AND rides.Deleted='N'";
	$caption = $rd['ReportTitle'];
	if ($_SESSION['KEY_REPORT'] <> $rptname)
	{
		$KEY_ORDER = $rd['InitialOrder'];
		if ($rd['InitialDesc']==1)
			$KEY_DESC = $KEY_ORDER;
		else
			$KEY_DESC = '';
		$_SESSION['KEY_REPORT'] = $rptname;
	}
	show_paged_rides_table($where,$caption,$select);
}



?>
