<?php
/*
 * I B A U K - riders.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 * 2017-06 - added new rally entries code
 * 2018-01 - added CurrentMember, DateLastActive code
 */

//$RIDERS_SQL  = "SELECT riders.*,IfNull(Count(URI),0) As NumRides FROM riders LEFT JOIN rides ON riders.riderid=rides.riderid #WHERE# GROUP BY riders.riderid";

$RIDERS_SQL  = "SELECT riders.*,NumRides,NumRallies FROM riders ";
$RIDERS_SQL .= "LEFT JOIN (SELECT *, IfNull(Count(URI),0) As NumRides FROM rides GROUP BY riderid) rides ON riders.riderid=rides.riderid ";
$RIDERS_SQL .= "LEFT JOIN (SELECT *, IfNull(Count(rallyresults.recid),0) As NumRallies FROM rallyresults GROUP BY riderid) rallyresults ON riders.riderid=rallyresults.riderid ";
$RIDERS_SQL .= "LEFT JOIN (SELECT *, IfNull(Count(mileeaters.recid),0) As NumMEs FROM mileeaters GROUP BY riderid) mileeaters ON riders.riderid=mileeaters.riderid ";
$RIDERS_SQL .= "LEFT JOIN (SELECT *, IfNull(Count(bikes.bikeid),0) As NumBikes FROM bikes GROUP BY riderid) bikes ON riders.riderid=bikes.riderid ";
$RIDERS_SQL .= "#WHERE# GROUP BY riders.riderid";

$CSV_COLS = "Rider_Name,Rider;IBA_Number,IBA#;NumRides,NumRides;NumRallies,NumRallies;riders.Country,Country;Postcode,Postcode;Phone,Phone;Email,Email";

function riders_table_row_header()
{
    global $MYKEYWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
    $res .= "<th class=\"text\">".column_anchor("Rider","Rider_Name")."</th>";
    $res .= "<th class=\"text\">".column_anchor("IBA#","IBA_Number")."</th>";
	$res .= "<th class=\"number\">".column_anchor('Rides','NumRides')."</th>";
	$res .= "<th class=\"number\">".column_anchor('Rallies','NumRallies')."</th>";
	if ($OK)
	{
		$res .= "<th class=\"text\">".column_anchor("Country","Country")."</th>";
		$res .= "<th class=\"text\">".column_anchor("Postcode","Postcode")."</th>";
		$res .= "<th class=\"text\">".column_anchor("Phone","Phone")."</th>";
		$res .= "<th class=\"text\">".column_anchor("Email","Email")."</th>";
		$res .= "<th class=\"date\">".column_anchor("LastActive","DateLastActive")."</th>";
		$res .= '<th></th>';
	}
	
    return $res;
}

function riders_table_row_html($ride_data)
{
    global $MYKEYWORDS, $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
    $res .= "<td class=\"text\">".$ride_data['Rider_Name']."</td>";
    $res .= "<td class=\"text\">".$ride_data['IBA_Number']."</td>";
	$res .= "<td class=\"number\">".$ride_data['NumRides']."</td>";
	$res .= "<td class=\"number\">".$ride_data['NumRallies']."</td>";
	if ($OK)
	{
		$res .= "<td class=\"text\">".$ride_data['Country']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Postcode']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Phone']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Email']."</td>";
		$res .= "<td class=\"date\">".$ride_data['DateLastActive']."</td>";
		$res .= '<td><input type="checkbox" data-riderid="'.$ride_data['riderid'].'"';
		$ridertag = "riderids['".$ride_data['riderid']."']";
		$res .= isset($_SESSION[$ridertag])? ' checked '  : '';
		$res .= 'onclick="setRiderTag(this);"';
		$res .= '></td>';
	}
    return $res;
}


function riders_table_row($uri)
{
    global $RIDERS_SQL;

    $SQL = str_replace('#WHERE'," WHERE riderid = '$uri' ",$RIDERS_SQL);
    $ride = sql_query($SQL);
    $ride_data = $ride->fetchArray();

    return riders_table_row_html($ride_data);

}


function show_riders_table($where,$what='Riders')
{
    global $RIDERS_SQL, $ORDER, $DESC, $OFFSET, $PAGESIZE, $CMDWORDS, $CSV_COLS;

    $SQL = str_replace('#WHERE#',$where <> '' ? " WHERE $where " : '',$RIDERS_SQL);

	$SQL .= sql_order();

//	print_r($_SESSION);

	//echo("[ $SQL ]");
	

    $ride = sql_query($SQL);
	$TotRows = foundrows($ride);
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	$xl = '';
	if ($OK)
		$xl = "<a href=\"index.php?c=snr\">Setup New Rider</a>";
	echo("<div class=\"maindata\" $TotRows><br /<br />");
	error_log("TotRows=$TotRows; PAGESIZE=$PAGESIZE;");
	$numrows = countrecs($ride);
	if ($PAGESIZE > 0 && $TotRows > $PAGESIZE)
		show_common_paging($TotRows,$xl);
    echo("<table>");
	echo("<caption>List of $what (".number_format($TotRows).")</caption>");
	echo("<tr>".riders_table_row_header()."</tr>\n");
	$rownum = 0;
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
    while(true)
    {
        $ride_data = $ride->fetchArray();
		if ($ride_data == false) break;
		

		if ($OK)
			$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['showrider']."&amp;".$CMDWORDS['uri']."=".$ride_data['riderid']."'\" class=\"goto row-";
		else
			$trspec = "class=\"row-";
		$rownum++;

		if ($rownum <= $OFFSET)
			continue;

		if ($PAGESIZE > 0 && $rownum - $OFFSET > $PAGESIZE)
			break;

		if ($rownum % 2 == 1)
			echo("<tr ".$trspec."1\">");
		else
			echo("<tr ".$trspec."2\">");
        echo(riders_table_row_html($ride_data)."</tr>\n");
    }
    echo("</table>");

	if ($TotRows > $numrows)
		show_common_paging($TotRows,$xl);
	//$cols = "Rider_Name,Rider;IBA_Number,IBA#;Count(URI),NumRides;Country,Country;Postcode,Postcode;Phone,Phone;Email,Email";
	$cols = $CSV_COLS;
?>
	<form action="index.php" method="post">
	<input type="hidden" name="cmd" value="csv">
	<input type="hidden" name="sql" value="<?php echo(urlencode($SQL));?>">
	<input type="hidden" name="cols" value="<?php echo(urlencode($cols));?>">
	<input type="hidden" name="where" value="<?php echo(urlencode(''));?>">
	<input type="hidden" name="csvname" value="ibaukriders.csv">
	<input type="submit" value="Download as .CSV">
	<form>
<?php
    echo("</div>");
	
}



function show_riders_listing()
{
    global $OFFSET, $PAGESIZE, $SHOW, $MYKEYWORDS;
	global $RIDERS_SQL, $HOME_COUNTRY;

	$where = '';
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();

    start_html($MYKEYWORDS['riders']." listing");
    if ($_GET['show']=='all')
    {
        $OFFSET = 0;
        $PAGESIZE = -1;
    }
	//var_dump($_REQUEST);
	//echo('<hr>');
	//var_dump($_SESSION);
	//echo('<hr>');
	$what = '';
	if ($_SESSION['ShowMemberStatus']=='current' || $_SESSION['ShowMemberStatus']=='lapsed')
		$what = $_SESSION['ShowMemberStatus'].' ';
	
	if ($_REQUEST['MileEaters']=='show')
	{
		//$RIDERS_SQL  = "SELECT  IfNull(Count(URI),0) As NumRides,riders.* FROM riders  JOIN ";
		//$RIDERS_SQL .= "(SELECT riderid FROM mileeaters GROUP BY riderid) As MEList ON MEList.riderid=riders.riderid ";
		//$RIDERS_SQL .= "LEFT JOIN rides ON riders.riderid=rides.riderid #WHERE# GROUP BY MEList.riderid";
		$where = ' NumMEs Is Not Null ';
		$what .= 'Mile Eaters';
	}
	else if ($_REQUEST['ShowPillions']=='only')
	{
		$where = "(riders.IsPillion='Y' OR rides.IsPillion='Y')";
		$what .= 'Pillions';
	}
	else if ($_REQUEST['NonUK']=='only')
	{
		$where = "riders.Country <> '".$HOME_COUNTRY."'";
		$what .= 'Non-'.$HOME_COUNTRY.' Riders';
	}
	else if (isset($_REQUEST['oldnew']) && $_REQUEST['oldnew']=='Inactive')
	{
		$days = intval($_REQUEST['Days']);
		if ($days < 1)
			$days = 1000;
		if ($where != '') $where .= ' AND ';

		$dt = New DateTime();
		$dtx = $dt->sub(new DateInterval('P'.$days.'D'));
		$dtxy = $dtx->format('Y-m-d');
			
		$where .= "( DateLastActive < '$dtxy'";
		$where .= " OR CurrentMember='N' )";
		$inactive_refresh = '<form style="display: inline;" action="index.php" method="get"><input type="hidden" name="c" value="riders">';
		$inactive_refresh .= '<select name="oldnew" onchange="this.form.submit();"><option value="Active">Active within</option><option value="Inactive" selected>Inactive for</option></select> ';
		$inactive_refresh .= '<input type="number"  onchange="this.form.submit();" title="active/inactive boundary" style="width:4em;" name="Days" value="'.$days.'"> days ';
		$inactive_refresh .= '<input type="submit" title="Refresh listing" value="&circlearrowright;"></form>';
		$what .= " members $inactive_refresh ";
	}
	else if (isset($_REQUEST['oldnew']) && $_REQUEST['oldnew']=='Active')
	{
		$days = intval($_REQUEST['Days']);
		if ($days < 1)
			$days = 1000;
		if ($where != '') $where .= ' AND ';

		$dt = New DateTime();
		$dtx = $dt->sub(new DateInterval('P'.$days.'D'));
		$dtxy = $dtx->format('Y-m-d');
			
		$where .= "( DateLastActive > '$dtxy'";
		$where .= " AND CurrentMember='Y' )";
		$inactive_refresh = '<form style="display: inline;" action="index.php" method="get"><input type="hidden" name="c" value="riders">';
		$inactive_refresh .= '<select name="oldnew" onchange="this.form.submit();"><option value="Active" selected>Active within</option><option value="Inactive">Inactive for</option></select> ';
		$inactive_refresh .= '<input type="number"  onchange="this.form.submit();" title="active/inactive boundary" style="width:4em;" name="Days" value="'.$days.'"> days ';
		$inactive_refresh .= '<input type="submit" title="Refresh listing" value="&circlearrowright;"></form>';
		$what .= " members $inactive_refresh ";
	}
	else if (isset($_REQUEST['Current']))
	{
		if ($where != '') $where .= ' AND ';
		$days = intval($_REQUEST['Current']);
		if ($days > 0)
			$where .= "DateLastActive >= DATE_SUB(CURDATE(), INTERVAL $days DAY) AND ";
		$where .= " CurrentMember='Y'";
		$what .= "Current members";
		if ($days > 0)
			$what .= " active within the last $days days";
	}
	else
	{
		$where = '';
		$what .= 'Members';
	}

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if ($_SESSION['ShowDeleted'] <> 'Y')
	{
		if ($where != '') $where .= ' AND ';
		$where .= " riders.Deleted='N'";
	}

	if (isset($_SESSION['ShowMemberStatus']) && $_SESSION['ShowMemberStatus'] <> 'all')
	{
		if ($where != '') $where .= ' AND ';
		if ($_SESSION['ShowMemberStatus']=='current')
			$where .= "riders.CurrentMember='Y'";
		else
			$where .= "riders.CurrentMember='N'";
	}


	//echo("<hr>$where<hr>");
	show_riders_table($where,$what);
    echo("</body></html>");
        
}


function andDeleted()
{
	if ($_SESSION['ShowDeleted'] <> 'Y')
		$where = " AND Deleted='N'";
	else
		$where = '';
	return $where;

}


function show_rider_details_content($ride_data)
{

	global $CMDWORDS;
	
	$PHONE_ICON = "\xE2\x98\x8E";
	$EMAIL_ICON = "	\xF0\x9F\x93\xA7";
	$TOUCH_DATE = " &#9759; ";
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	
	$newrec = $ride_data['riderid'] == '' || $ride_data['riderid'] == 'newrec';
	
	$ro = ' readonly ';
	if ($OK) $ro = '';

	$res = "<div class=\"riderform\">";
	$res .= "<form action=\"index.php\" method=\"post\" id=\"riderdetails\"  ><input type=\"hidden\" name=\"cmd\" value=\"putrider\">";
	$res .= "<input type=\"hidden\" name=\"riderid\" value=\"".$ride_data['riderid']."\" />";
	$ibahdr = "";
	if ($ride_data['IBA_Number'] <> '')
		$ibahdr = " - IBA # ".$ride_data['IBA_Number'];
	$ridername = '';
	if (!$newrec)
		$ridername = $ride_data['Rider_Name'];
	else
		$ridername = '&lt;new rider&gt;';
	$res .= "<h2><a href=\"index.php?c=".$CMDWORDS['showrider']."&amp;".$CMDWORDS['uri']."=".$ride_data['riderid']."\">Rider record for <span class=\"boldlabel\">".$ridername.$ibahdr."</span></a></h2>";


	$bikelist = "<datalist id=\"bikelist\">";
	$rr = sql_query("SELECT Bike FROM bikes GROUP BY Bike ORDER BY Bike");
	while(true)
	{
		$rd = $rr->fetchArray();
		if ($rd == false) break;
		$bikelist .= "<option>".htmlentities($rd['Bike'])."</option>";
	}
	
	$bikelist .= "</datalist>";
	$res .= $bikelist;
	
	$res .= "<div id=\"tabs_area\" style=\"display:inherit\"><ul id=\"tabs\">";
	$res .= "<li><a href=\"#tab_riderdata\">Rider</a></li>";
	$res .= "<li><a href=\"#tab_bikesdata\">Bikes</a></li>";
	$res .= "<li><a href=\"#tab_ridesdata\">Rides</a></li>";
	$res .= "<li><a href=\"#tab_rallies\">Rallies</a></li>";
	$res .= "<li><a href=\"#tab_mileeater\">Mile Eaters</a></li>";
	$res .= "<li><a href=\"#tab_notesdata\">Notes</a></li>";
	$res .= "</ul></div>";
	$res .= "<div class=\"tabContent\" id=\"tab_riderdata\"><br>";
	$res .= "<label for=\"Rider_Name\" class=\"vlabel\">Rider name</label><input oninput=\"enableSave();\" type=\"text\" name=\"Rider_Name\" id=\"Rider_Name\" class=\"vdata\" $ro value=\"".$ride_data['Rider_Name']."\" >";
	$res .= "<label for=\"IBA_Number\" class=\"vlabel\">IBA Number</label><input  oninput=\"enableSave();\" type=\"number\" name=\"IBA_Number\" id=\"IBA_Number\" class=\"vdata\" $ro value=\"".$ride_data['IBA_Number']."\" ><br />";
	$res .= "<label for=\"Postal_Address\" class=\"vlabel\">Postal address</label><textarea  oninput=\"enableSave();\" name=\"Postal_Address\" id=\"Postal_Address\" class=\"vdata tall\" maxlength=\"255\" $ro>".$ride_data['Postal_Address']."</textarea><br />";
	$res .= "<label for=\"Postcode\" class=\"vlabel\">Postcode</label><input  oninput=\"enableSave();\" type=\"text\" name=\"Postcode\" id=\"Postcode\" class=\"vdata\" $ro maxlength=\"10\" value=\"".$ride_data['Postcode']."\" ><br />";
	$res .= "<label for=\"Email\" class=\"vlabel\">Email</label>";
	$res .= "<input  oninput=\"enableSave();\" type=\"email\" name=\"Email\" id=\"Email\" class=\"vdata\" $ro maxlength=\"45\" value=\"".$ride_data['Email']."\" >";
	$res .= "<a id=\"emailer\" title=\"Open your email client\" tabindex=\"-1\" id=\"sendMail\" href=\"mailto:".$ride_data['Email']."\"> $EMAIL_ICON</a><br>";
	$res .= "<label for=\"Phone\" class=\"vlabel\">Phone</label><input  title=\"Call number\" oninput=\"enableSave();\" type=\"tel\" name=\"Phone\" id=\"Phone\" class=\"vdata\" $ro maxlength=\"16\" value=\"".$ride_data['Phone']."\" >";
	$res .= "<a tabindex=\"-1\" id=\"callPhone\"  href=\"tel:".$ride_data['Phone']."\"> $PHONE_ICON</a>";
	$res .= "<label for=\"AltPhone\" class=\"vlabel\">AltPhone</label><input  title=\"Call number\" oninput=\"enableSave();\" type=\"tel\" name=\"AltPhone\" id=\"AltPhone\" class=\"vdata\" $ro maxlength=\"16\" value=\"".$ride_data['AltPhone']."\" >";
	$res .= "<a tabindex=\"-1\" id=\"callAltPhone\"  href=\"tel:".$ride_data['AltPhone']."\"> $PHONE_ICON</a><br >";
	$res .= "<label for=\"Country\" class=\"vlabel\">Country</label> ";
	$res .= "<input  oninput=\"enableSave();\" type=\"text\" name=\"Country\" id=\"Country\" $ro class=\"vdata\" title=\"Used for reporting purposes, not part of address\" value=\"".$ride_data['Country']."\" ><br>";
	$res .= "<fieldset><legend>Is normally a</legend>";
	$res .= "<input type=\"radio\" name=\"IsPillion\"  oninput=\"enableSave();\" class=\"radio\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['IsPillion'])."> Rider ";
	$res .= " &nbsp;&nbsp; ";
	$res .= "<input type=\"radio\" name=\"IsPillion\"  oninput=\"enableSave();\" class=\"radio2\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['IsPillion'])."> Pillion ";
	$res .= "</fieldset>";
	
	$res .= "<fieldset><legend>Record status</legend>";
	$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"IsDeleted\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['Deleted'])."> Deleted ";
	$res .= " &nbsp;&nbsp; ";
	$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"IsDeleted\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['Deleted'])."> OK ";
	$res .= "</fieldset><br>";

	$res .= '<fieldset title="\'inactive\' members are either deceased or otherwise \'inactive\' (not ridden for a while, etc)"><legend>Active member?</legend>';
	$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"IsCurrentMember\" class=\"radio\" $ro value=\"Y\" ".Checkbox_isChecked($ride_data['CurrentMember'])."> Active";
	$res .= " &nbsp;&nbsp; ";
	$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"IsCurrentMember\" class=\"radio2\" $ro value=\"N\" ".Checkbox_isNotChecked($ride_data['CurrentMember'])."> Inactive";
	$res .= "</fieldset>";
	$res .= '<span title="The most recent date when this person was known to be active either by completing a ride, entering a rally, attending an RTE, etc">';
	$res .= "<label for=\"DateLastActive\" class=\"vlabel vbottom\" style=\"vertical-align:middle;\">last active</label> ";
	$res .= "<input  oninput=\"enableSave();\"  type=\"date\" id=\"DateLastActive\" name=\"DateLastActive\" class=\"vdata vbottom\"  style=\"vertical-align:middle;\" $ro value=\"".$ride_data['DateLastActive']."\" /> ";
	$res .= "<span onclick=\"touchDate('DateLastActive');\" style=\"font-size:1.4em; cursor:pointer;\" title=\"Touch the date, make it today\">".$TOUCH_DATE."</span>";
	$res .= '</span>';

	$res .= "</div><div class=\"tabContent\" id=\"tab_bikesdata\">";

	// Bikes
	$SQL = "SELECT * FROM bikes WHERE riderid=".$ride_data['riderid'].andDeleted()." ORDER BY bikeid";
	$rn = sql_query($SQL);
	$nbikes = foundrows($rn);
	$res .= "<table style=\" border: none; \"><tr><th>Bike</th><th>Registration</th><th>Odometer</th><th>Rides</th>";
	if ($nbikes > 1) 
		$res .= "<th>Select</th>";
	$res .= "</tr>";
	$ix = 0;
	
	$bikeslookup = "<datalist id=\"bikeslookup\">";
	while(true)
	{
		$rnd = $rn->fetchArray();
		if ($rnd == false) break;
		$ix++;
		$bikeslookup .= "<option>".$rnd['Bike']."</option>";
		$res .= "<tr>";
		$res .= "<td><input type=\"hidden\" name=\"bikeid[]\" value=\"".$rnd['bikeid']."\"><input  oninput=\"enableSave();\" type=\"text\" name=\"Bike[]\" class=\"vdata\" $ro value=\"".$rnd['Bike']."\" /></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"text\" name=\"Registration[]\" class=\"vdata\" $ro value=\"".$rnd['Registration']."\" /></td>";
		$res .= "<td><span class=\"vdata\">";
		$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"KmsOdo:".$ix."[]\" class=\"radio\" $ro value=\"N\" ".Checkbox_isNotChecked($rnd['KmsOdo']).">Miles";
		$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"KmsOdo:".$ix."[]\"  class=\"radio2\" $ro value=\"Y\" ".Checkbox_isChecked($rnd['KmsOdo']).">Kilometres";
		$res .= "</span></td>";
		$rr = sql_query("SELECT Count(*) AS Rex FROM rides WHERE rides.riderid=".$ride_data['riderid']." and rides.bikeid=".$rnd['bikeid'].andDeleted());
		$rrd = $rr->fetchArray();
		$res .= "<td><span class=\"vdata\" style=\"width:50px;\">".$rrd['Rex']."</span></td>";
		if ($nbikes > 1)
			$res .= "<td><input  oninput=\"enableSave();\" type=\"checkbox\" name=\"SelectBike[]\" value=\"".$rnd['bikeid']."\"  onchange=\"initBikeMerge();\" ></td>";
		$res .= "</tr>";
	}
	$bikeslookup .= "</datalist>";
	if ($OK)
	{
		$ix++;
		$res .= "<tr>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"hidden\" name=\"bikeid[]\" value=\"newrec\"><input  oninput=\"enableSave();\" type=\"text\" list=\"bikelist\" name=\"Bike[]\" id=\"NewBikeMakeModel\" onchange=\"initBikeMerge();\" class=\"vdata\" $ro placeholder=\"Add another bike here\"/></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"text\" name=\"Registration[]\" class=\"vdata\" $ro /></td>";
		$res .= "<td><span class=\"vdata\">";
		$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"KmsOdo:".$ix."[]\" class=\"radio\" $ro value=\"N\" checked=\"checked\">Miles";
		$res .= "<input  oninput=\"enableSave();\" type=\"radio\" name=\"KmsOdo:".$ix."[]\"  class=\"radio2\" $ro value=\"Y\" >Kilometres";
		$res .= "</span></td>";
		if ($nbikes > 1)
			$res .= "<td colspan=\"2\"><input type=\"submit\" name=\"cmd\" title=\"Replace the selected bike records with the single new one\" id=\"MergeBikesButton\" value=\"MergeBikes\"></td>";
		else
			$res .= "<td></td>";
		$res .= "</tr>";
	}
	$res .= "</table>";
	$res .= $bikeslookup;
	$res .= "</div><div class=\"tabContent\" id=\"tab_ridesdata\">";

	

	$rn = sql_query("SELECT * FROM rides WHERE riderid=".$ride_data['riderid'].andDeleted()." ORDER BY DateRideStart DESC");
	$rows = 0;
	while($rd=$rn->fetchArray())
		$rows++;
	$rn->reset();
	if ($rows < 1)
		$caption = "No rides!";
	elseif ($rows == 1)
		$caption = "One ride";
	else
		$caption = $rows." rides";
	$res .= "<table><caption>$caption</caption><tr><th>Date</th><th>Ride</th><th>Bike</th><th>Event</th><th>URI</th></tr>";
	$ix = 0;
	while(true)
	{
		$rnd = $rn->fetchArray();
		if ($rnd == false) break;
		$ix++;
		if ($ix % 2 == 0)
			$res .= "<tr class=\"row-1 goto\"";
		else
			$res .= "<tr class=\"row-2 goto\"";
		$res .= " onclick=\"window.location='index.php?c=".$CMDWORDS['showride']."&amp;".$CMDWORDS['uri']."=".$rnd['URI']."';\">";
		$res .= "<td class=\"date\">".$rnd['DateRideStart']."</td>";
		$res .= "<td>".$rnd['IBA_Ride']."</td>";
		$rr = sql_query("SELECT * FROM bikes WHERE bikes.bikeid=".$rnd['bikeid']);
		$rrd = $rr->fetchArray();
		$res .= "<td>".$rrd['Bike']." ".$rrd['Registration']."</td>";
		$res .= "<td>".$rnd['EventName']."</td>";
		$res .= "<td>".$rnd['URI']."</td>";
		$res .= "</tr>";
	}
	$res .= "</table>";

	$res .= "</div>"; // end tab

	$res .= "<div class=\"tabContent\" id=\"tab_rallies\">";
	$rn = sql_query("SELECT RallyID,FinishPosition,RallyMiles,RallyPoints,bikes.Bike,recid FROM rallyresults LEFT JOIN bikes ON rallyresults.bikeid=bikes.bikeid WHERE rallyresults.riderid=".$ride_data['riderid']." ");
	$rows = 0;
	while ($rd = $rn->fetchArray())
		$rows++;
	$rn->reset();
	if ($rows < 1)
		$caption = "No rallies!";
	elseif ($rows == 1)
		$caption = "One rally";
	else
		$caption = $rows." rallies";
	$res .= "<table><caption>$caption</caption><tr><th>Rally</th><th>Bike</th><th>Position</th><th>Miles</th><th>Points</th></tr>";
	$ix = 0;
	while(true)
	{
		$rnd = $rn->fetchArray();
		if ($rnd == false) break;
		$ix++;
		if ($ix % 2 == 0)
			$res .= "<tr class=\"row-1 \"";
		else
			$res .= "<tr class=\"row-2 \"";
		$res .= ">";
		$res .= "<td><input type=\"hidden\" name=\"Rally_recid[]\" value=\"".$rnd['recid']."\"><input  oninput=\"enableSave();\" type=\"text\" name=\"RallyID[]\" class=\"vdata\" $ro value=\"".$rnd['RallyID']."\"></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"text\" name=\"Rally_Bike[]\" $ro class=\"vdata\" list=\"bikeslookup\" value=\"".$rnd['Bike']."\"></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"number\" name=\"FinishPosition[]\" $ro class=\"vdata short\" value=\"".$rnd['FinishPosition']."\"></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"number\" name=\"RallyMiles[]\" $ro class=\"vdata short\" value=\"".$rnd['RallyMiles']."\"></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"number\" name=\"RallyPoints[]\" $ro class=\"vdata short\" value=\"".$rnd['RallyPoints']."\"></td>";
		$res .= "</tr>";
	}
	$ix++;
	if ($ix % 2 == 0)
		$res .= "<tr class=\"row-1 \"";
	else
		$res .= "<tr class=\"row-2 \"";
	$res .= ">";
	$res .= "<td><input type=\"hidden\" name=\"Rally_recid[]\" value=\"newrec\"><input type=\"text\" placeholder=\"new rally result\" name=\"RallyID[]\" class=\"vdata\" $ro value=\"\"></td>";
	$res .= "<td><input  oninput=\"enableSave();\" type=\"text\" name=\"Rally_Bike[]\" $ro list=\"bikeslookup\" class=\"vdata\" value=\"\"></td>";
	$res .= "<td><input  oninput=\"enableSave();\" type=\"number\" name=\"FinishPosition[]\" $ro class=\"vdata short\" value=\"\"></td>";
	$res .= "<td><input  oninput=\"enableSave();\" type=\"number\" name=\"RallyMiles[]\" $ro class=\"vdata short\" value=\"\"></td>";
	$res .= "<td><input  oninput=\"enableSave();\" type=\"number\" name=\"RallyPoints[]\" $ro class=\"vdata short\" value=\"\"></td>";
	$res .= "</tr>";
	$res .= "</table>";

	$res .= "</div>";
	
	$res .= "<div class=\"tabContent\" id=\"tab_mileeater\">";
	$rn = sql_query("SELECT * FROM mileeaters WHERE riderid=".$ride_data['riderid'].andDeleted()." ORDER BY awardyear");
	$rows = 0;
	while ($rd = $rn->fetchArray())
		$rows++;
	$rn->reset();
	if ($rows < 1)
		$caption = "No Mile Eaters claimed!";
	elseif ($rows == 1)
		$caption = "One Mile Eater";
	else
		$caption = $rows." Mile Eaters";
	$res .= "<table><caption>$caption</caption><tr><th>Year</th><th>Award</th><th>Citation</th></tr>";
	$ix = 0;
	while(true)
	{
		$rnd = $rn->fetchArray();
		if ($rnd == false) break;
		$ix++;
		if ($ix % 2 == 0)
			$res .= "<tr class=\"row-1 \">";
		else
			$res .= "<tr class=\"row-2 \">";
		$res .= "<td><input type=\"hidden\" name=\"ME_recid[]\" value=\"".$rnd['recid']."\"><input  oninput=\"enableSave();\" type=\"number\" name=\"awardyear[]\" class=\"vdata short\" $ro value=\"".$rnd['awardyear']."\"></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"text\" name=\"awardlevel[]\" list=\"MELevels\" class=\"vdata\" $ro value=\"".$rnd['awardlevel']."\"></td>";
		$res .= "<td><textarea  oninput=\"enableSave();\" class=\"vdata long\" name=\"Citation[]\" rows=\"2\" cols=\"40\">".$rnd['Citation']."</textarea></td>";
		$res .= "</tr>";
	}
	if ($OK)
	{
		$ix++;
		if ($ix % 2 == 0)
			$res .= "<tr class=\"row-1 \">";
		else
			$res .= "<tr class=\"row-2 \">";
		$res .= "<td><input type=\"hidden\" name=\"ME_recid[]\" value=\"newrec\"><input type=\"number\" name=\"awardyear[]\" class=\"vdata short\" $ro placeholder=\"Enter another ME year\" value=\"\"></td>";
		$res .= "<td><input  oninput=\"enableSave();\" type=\"text\" name=\"awardlevel[]\" list=\"MELevels\" class=\"vdata\" $ro placeholder=\"Choose another ME award\" value=\"\"></td>";
		$res .= "<td><textarea  oninput=\"enableSave();\" class=\"vdata long\" name=\"Citation[]\" rows=\"2\" cols=\"40\"></textarea></td>";
		$res .= "</tr>";
	}
	$res .= "</table>";
	$res .= "<datalist id=\"MELevels\">";
	$res .= "<option value=\"Mile Eater\">Mile Eater</option>";
	$res .= "<option value=\"Bronze\">Bronze</option>";
	$res .= "<option value=\"Silver\">Silver</option>";
	$res .= "<option value=\"Gold\">Gold</option>";
	$res .= "<option value=\"Platinum\">Platinum</option>";
	$res .= "<option value=\"Titanium\">Titanium</option>";
	$res .= "<option value=\"Diamond\">Diamond</option>";
	$res .= "<option value=\"Black\">Black</option>";
	$res .= "</datalist>";
	$res .= "</div>";
	
	

	$res .= "<div class=\"tabContent\" id=\"tab_notesdata\">";
	$res .= "<label for=\"Notes\" class=\"vlabel\"></label><textarea  oninput=\"enableSave();\" id=\"Notes\" name=\"Notes\" class=\"vdata filler\" maxlength=\"255\" $ro>".$ride_data['Notes']."</textarea><br />";
	
	$res .= "</div>";

	
	
	
	if ($ro == '')
	{
		$res .= "<input id=\"UpdateSaveButton\" disabled type=\"submit\" value=\"Update/save this rider record\"/>";
		if (!$newrec)
		{
			$res .= "<label for=\"newridebutton\"> </label>";
			$res .= "<input type=\"submit\" id=\"newridebutton\" name=\"cmd\" title=\"Click to enter a new ride for this rider\" value=\"NewRide\">";
			if (true)
			{
				$riderid = isset($_REQUEST['riderid']) ? $_REQUEST['riderid'] : $_REQUEST['URI'];
				$riderkey = "riderids['$riderid']";
				$res .= "<label for=\"tagbutton\"> </label>";
				//var_dump($_SESSION);
				//echo("<hr>$riderkey<hr>");
				if (isset($_SESSION[$riderkey]))
					$res .= "<input type=\"submit\" id=\"tagbutton\" name=\"cmd\" value=\"Untag this record\">";
				else
					$res .= "<input type=\"submit\" id=\"tagbutton\" name=\"cmd\" value=\"Tag this record\">";
			}
		}
	}
	$res .= "</form>";
	if ($_SESSION['BACK2LIST'] > 1)
		$res .= "<button onclick=\"window.history.go(-".$_SESSION['BACK2LIST'].");\">Return to the list</button>";
	else if ($_SESSION['BACK2LIST'] < 0)
		$res .= "<button onclick=\"window.location.replace('index.php?c=friders')\">Return to the list</button>";
	$res .= "</div>";


	
    start_html("Rider ".$ride_data['riderid']);
    echo($res);
	//var_dump($ride_data);
    echo("</body></html>");
	
}


function show_rider_details_uri($uri)
{
	
    $SQL  = "SELECT * FROM riders WHERE riderid = ".$uri;

	if (isset($_SESSION['BACK2LIST']))
		$_SESSION['BACK2LIST'] = $_SESSION['BACK2LIST'] + 1;
	else
		$_SESSION['BACK2LIST'] = 0;
	
    $ride  = sql_query($SQL);
    $ride_data = $ride->fetchArray();
	
	show_rider_details_content($ride_data);
	
}

function flip_rider_record_tag()
{
   global $CMDWORDS;


	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) {
		show_riders_listing();
		exit;
	}
	
	$riderid = $_REQUEST['riderid'];
	$riderkey = "riderids['$riderid']";
	if (isset($_SESSION[$riderkey]))
		$_SESSION[$riderkey] = NULL;
	else
		$_SESSION[$riderkey] = $riderkey;
	
	show_rider_details_uri($riderid);
		
		
}

function show_rider_details()
{
   global $CMDWORDS;


	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) {
		show_riders_listing();
		exit;
	}
	$_SESSION['BACK2LIST'] = 0; // Don't show button, just use regular back key
	if ($_REQUEST['riderid'] <> '')
		show_rider_details_uri($_REQUEST['riderid']);
	else
		show_rider_details_uri($_GET[$CMDWORDS['uri']]);
	
}

function showNewRider()
{
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();
	
	$rd['URI'] = 'newrec';
	$rd['Country'] = 'UK';
	$rd['CurrentMember'] = 'Y';
	$rd['DateLastActive'] = date('Y-m-d');
	show_rider_details_content($rd);
		
}

function fetchBikeid($riderid,$bike)
{
	$SQL = "SELECT bikeid FROM bikes WHERE riderid=$riderid AND bike like '".$bike."'";
	$r = sql_query($SQL);
	$rr = countrecs($r);
	if ($rr < 1) return -2;
	$rd = $r->fetchArray();
	return $rd['bikeid'];
}

function put_rider()
{
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	
	if (!$OK) safe_default_action();
	// Validation
	if ($_POST['Rider_Name'] == '')
	{
		show_infoline("Rider name may not be blank","errormsg");
		show_rider_details_uri($_POST['riderid']);
		exit;
	}
	$bmax = count($_POST['Bike']);
	//echo("Found $bmax bikes<hr />");
	for ($ix = 0;$ix < $bmax; $ix++)
	{
		if ($_POST['bikeid'][$ix] <> 'newrec' && $_POST['Bike'][$ix] == '')
		{
			show_infoline("Each bike entry must be non-blank","errormsg");
			show_rider_details_uri($_POST['riderid']);
			exit;
		}
	}
	$ME_max = count($_POST['ME_recid']);
	for ($ix = 0;$ix < $ME_max; $ix++)
	{
		if ($_POST['ME_recid'][$ix] <> 'newrec' && $_POST['awardlevel'][$ix] == '')
		{
			show_infoline("Each Mile Eater entry must be non-blank","errormsg");
			show_rider_details_uri($_POST['riderid']);
			exit;
		}
	}
	
	$Rally_max = count($_POST['Rally_recid']);
	for ($ix = 0;$ix < $Rally_max; $ix++)
	{
		if ($_POST['Rally_recid'][$ix] <> 'newrec' && $_POST['FinishPosition'][$ix] == '')
		{
			show_infoline("Each rally entry must be non-blank","errormsg");
			show_rider_details_uri($_POST['riderid']);
			exit;
		}
	}
	
	//var_dump($_POST);
	//echo("<hr />");
	
	$newrec = ($_POST['riderid'] == 'newrec' || $_POST['riderid'] == '');
	
	// Rider record
	if ($newrec) {
		$SQL = "INSERT INTO riders (";
		$SQL .= "Rider_Name,IBA_Number,Postal_Address,Postcode,Phone,AltPhone,Email,IsPillion,Notes,Country,Deleted,CurrentMember,DateLastActive";
		$SQL .= ") VALUES (";
		$SQL .= "'".safesql($_POST['Rider_Name'])."'";
		$SQL .= ",'".safesql($_POST['IBA_Number'])."'";
		$SQL .= ",'".safesql($_POST['Postal_Address'])."'";
		$SQL .= ",'".safesql($_POST['Postcode'])."'";
		$SQL .= ",'".safesql($_POST['Phone'])."'";
		$SQL .= ",'".safesql($_POST['AltPhone'])."'";
		$SQL .= ",'".safesql($_POST['Email'])."'";
		$SQL .= ",'".safesql($_POST['IsPillion'])."'";
		$SQL .= ",'".safesql($_POST['Notes'])."'";
		$SQL .= ",'".safesql($_POST['Country'])."'";
		$SQL .= ",'".safesql($_POST['IsDeleted'])."'";
		$SQL .= ",'".safesql($_POST['IsCurrentMember'])."'";
		$SQL .= ",".safedatesql($_POST['DateLastActive'])."";
		$SQL .= ")";
	} elseif (isset($_POST['deletethisrec'])) {
		$SQL = "UPDATE riders SET Deleted='Y' WHERE riderid=".$_POST['riderid'];
	} else {
		$SQL = "UPDATE riders SET ";
		$SQL .= "Rider_Name='".safesql($_POST['Rider_Name'])."'";
		$SQL .= ",IBA_Number='".safesql($_POST['IBA_Number'])."'";
		$SQL .= ",Postal_Address='".safesql($_POST['Postal_Address'])."'";
		$SQL .= ",Postcode='".safesql($_POST['Postcode'])."'";
		$SQL .= ",Phone='".safesql($_POST['Phone'])."'";
		$SQL .= ",AltPhone='".safesql($_POST['AltPhone'])."'";
		$SQL .= ",Email='".safesql($_POST['Email'])."'";
		$SQL .= ",IsPillion='".safesql($_POST['IsPillion'])."'";
		$SQL .= ",Notes='".safesql($_POST['Notes'])."'";
		$SQL .= ",Country='".safesql($_POST['Country'])."'";
		$SQL .= ",Deleted='".safesql($_POST['IsDeleted'])."'";
		$SQL .= ",CurrentMember='".safesql($_POST['IsCurrentMember'])."'";
		$SQL .= ",DateLastActive=".safedatesql($_POST['DateLastActive'])."";
		$SQL .= " WHERE riderid=".$_POST['riderid'];
	}

	//echo($SQL."<hr />");
	sql_query($SQL);
	if ($newrec)
		$_POST['riderid'] = dblastid("riders","riderid");
		
	// Bikes array
	for ($ix = 0;$ix < $bmax; $ix++)
	{
		$KmsOdo = "KmsOdo:".($ix+1);
		$kmsodoval = $_POST[$KmsOdo][0];
		//echo("$KmsOdo==$kmsodoval<hr />");
		if ($_POST['bikeid'][$ix] <> 'newrec')
		{
			$SQL = "UPDATE bikes SET ";
			$SQL .= "Bike='".safesql($_POST['Bike'][$ix])."'";
			$SQL .= ",Registration='".safesql($_POST['Registration'][$ix])."'";
			$SQL .= ",KmsOdo='".safesql($kmsodoval)."'";
			$SQL .= " WHERE riderid=".$_POST['riderid']." AND bikeid=".$_POST['bikeid'][$ix];
			//echo("$SQL<hr />");
			sql_query($SQL);
		}
		elseif ($_POST['Bike'][$ix] <> '')
		{
			$SQL = "INSERT INTO bikes (riderid,Bike,Registration,KmsOdo) VALUES (";
			$SQL .= $_POST['riderid'];
			$SQL .= ",'".safesql($_POST['Bike'][$ix])."'";
			$SQL .= ",'".safesql($_POST['Registration'][$ix])."'";
			$SQL .= ",'".safesql($_POST[$KmsOdo][0])."'";
			$SQL .= ")";
			//echo("$SQL<hr />");
			sql_query($SQL);
			touchRider($_POST['riderid']);
		}		
	}
	

	// Mile eater array
	for ($ix = 0;$ix < $ME_max; $ix++)
	{
		if ($_POST['ME_recid'][$ix] <> 'newrec')
		{
			$SQL = "UPDATE mileeaters SET ";
			$SQL .= "awardyear='".safesql($_POST['awardyear'][$ix])."'";
			$SQL .= ",awardlevel='".safesql($_POST['awardlevel'][$ix])."'";
			$SQL .= ",Citation='".safesql($_POST['Citation'][$ix])."'";
			$SQL .= " WHERE riderid=".$_POST['riderid']." AND recid=".$_POST['ME_recid'][$ix];
			//echo("$SQL<hr />");
			sql_query($SQL);
		}
		else if ($_POST['awardlevel'][$ix] <> '')
		{
			$SQL = "INSERT INTO mileeaters (riderid,awardyear,awardlevel,Citation) VALUES (";
			$SQL .= $_POST['riderid'];
			$SQL .= ",'".safesql($_POST['awardyear'][$ix])."'";
			$SQL .= ",'".safesql($_POST['awardlevel'][$ix])."'";
			$SQL .= ",'".safesql($_POST['Citation'][$ix])."'";
			$SQL .= ")";
			//echo("$SQL<hr />");
			sql_query($SQL);
			touchRider($_POST['riderid']);
		}		
	}
	
	// Rally array
	for ($ix = 0;$ix < $Rally_max; $ix++)
	{
		$bikeid = -1;
		while($bikeid < 0)
		{
			$bikename = $_POST['Rally_Bike'][$ix];
			if ($bikename == '') break;
			$bikeid = fetchBikeid($_POST['riderid'],$bikename);
			if ($bikeid < 0 && $bikename <> '')
			{
				$SQL = "INSERT INTO bikes (riderid,bike) VALUES(".$_POST['riderid'].",'".safesql($_POST['Rally_Bike'][$ix])."')";
				sql_query($SQL);
			}				
		}

		if ($_POST['Rally_recid'][$ix] <> 'newrec')
		{
			$SQL = "UPDATE rallyresults SET ";
			$SQL .= "RallyID='".safesql($_POST['RallyID'][$ix])."'";
			$SQL .= ",FinishPosition=".safesql($_POST['FinishPosition'][$ix])."";
			if ($_POST['Rally_Bike'][$ix] <> '')
				$SQL .= ",bikeid=".$bikeid;
			$SQL .= ",RallyMiles=".safesql($_POST['RallyMiles'][$ix])."";
			$SQL .= ",RallyPoints=".safesql($_POST['RallyPoints'][$ix])."";
			$SQL .= " WHERE riderid=".$_POST['riderid']." AND recid=".$_POST['Rally_recid'][$ix];
			//echo("$ix == $SQL<hr />");
			sql_query($SQL);
		}
		else if ($_POST['FinishPosition'][$ix] <> '')
		{
			//echo("Hello sailor<hr />");
			$SQL = "INSERT INTO rallyresults (RallyID,FinishPosition,riderid,bikeid,RallyMiles,RallyPoints,Country) VALUES (";
			$SQL .= "'".safesql($_POST['RallyID'][$ix])."'";
			$SQL .= ",".safesql($_POST['FinishPosition'][$ix])."";
			$SQL .= ",".$_POST['riderid'];
			$SQL .= ",".$bikeid;
			$SQL .= ",".safesql($_POST['RallyMiles'][$ix])."";
			$SQL .= ",".safesql($_POST['RallyPoints'][$ix])."";
			$SQL .= ",'".safesql($_POST['Country'][$ix])."'";
			$SQL .= ")";
			//echo("$SQL<hr />");
			sql_query($SQL);

			touchRider($_POST['riderid']);
		}		
	}
	

	show_infoline("Rider record saved ok","infohilite");
	show_rider_details_uri($_POST["riderid"]);
}

function mark_as_lapsed()
{
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();

	$sql = "UPDATE riders SET CurrentMember='N' WHERE CurrentMember='Y' AND DateLastActive<'".$_REQUEST['datelapsed']."';";
	sql_query($sql);
	show_infoline('Riders inactive since '.$_REQUEST['datelapsed'].' have been marked as inactive','infohilite');
	show_riders_listing();
}

?>
