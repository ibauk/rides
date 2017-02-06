<?php
/*
 * I B A U K - search.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 */


// Fail quietly if called directly
if (!function_exists('start_html')) exit;

$SEARCH_SQL = "SELECT SQL_CALC_FOUND_ROWS ";
$SEARCH_SQL .= "riders.riderid As riderid,Rider_Name,IBA_Number,Postal_Address,Postcode,Email,Phone,AltPhone ";
$SEARCH_SQL .= ",URI,DateRideStart,NameOnCertificate,EventName,StartPoint,FinishPoint,MidPoints,IBA_Ride,OriginUK,Bike ";
$SEARCH_SQL .= "FROM riders LEFT JOIN rides ON rides.riderid=riders.riderid LEFT JOIN bikes ON rides.bikeid=bikes.bikeid WHERE ";

$WHEREKEY = '';

function orRideDate($datestr)
{
	/*
	 * If $datestr is a recognisable date then
	 *    return OR (DateRideStart = date)
	 * if $datestr is a range such as May 2015 then
	 *    return OR (DateRideStart LIKE 2015-05)
	 * else
	 *	  return empty string
	 */
	 
	$fulldate = "/(\d\d\d\d)\-(\d+)\-(\d+)/";
	$partdate = "/(\d\d\d\d)\-(\d+)/";
	if (preg_match($fulldate,$datestr,$dt))
	{
		$yy = $dt[1];
		$mm = $dt[2];
		$dd = $dt[3];
		return " OR (DateRideStart='".sprintf("%d-%02d-%02d",$yy,$mm,$dd)."')";
	}
	else if (preg_match($partdate,$datestr,$dt))
	{
		$yy = $dt[1];
		$mm = $dt[2];
		return " OR (DateRideStart LIKE '".sprintf("%d-%02d",$yy,$mm)."%')";
	}
	else
		return '';
	
}

function searchall($FIND,$ORDER,$DESC)
{
	
	global $SEARCH_SQL,$WHEREKEY, $OFFSET, $PAGESIZE, $KEY_ORDER, $KEY_DESC;


	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	$RETURN_RIDERLIST = (!isset($_REQUEST['parShowResults']) || $_REQUEST['parShowResults'] == 'riders');
	
	if ($FIND != '')
	{
		$SQL = "(";
		$SQL .= "(rides.NameOnCertificate LIKE '%$FIND%') ";
		$SQL .= orRideDate($FIND);
		if ($OK) $SQL .= "    OR (riders.Rider_Name LIKE '%$FIND%') ";
		$SQL .= "    OR (riders.IBA_Number = '$FIND') ";
		if ($OK) $SQL .= "    OR (riders.Postal_Address LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (riders.Postcode LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (riders.Country LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (riders.Email LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (riders.Phone LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (bikes.Registration LIKE '%$FIND%') ";
		$SQL .= "    OR (bikes.Bike LIKE '%$FIND%') ";
		$SQL .= "    OR (rides.IBA_Ride LIKE '$FIND') ";
		$SQL .= "    OR (rides.EventName LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (rides.StartPoint LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (rides.MidPoints LIKE '%$FIND%') ";
		if ($OK) $SQL .= "    OR (rides.FinishPoint LIKE '%$FIND%') ";
		$SQL .= ")";
	}
	else
		$SQL = '';
	
	if (isset($_REQUEST['likefld']))
	{
		if ($SQL != '')
			$SQL .= ' AND ';
		$SQL .= "(".$_REQUEST['likefld']." LIKE '".$_REQUEST['likeval']."')";
	}

	if ($_REQUEST['parRideDates'] != 'all')
	{
		if ($_REQUEST['parDateFrom'] != '')
		{
			if ($SQL != '')
				$SQL .= ' AND ';
			$SQL .= "(DateRideStart >= '".$_REQUEST['parDateFrom']."')";
		}
	
		if ($_REQUEST['parDateTo'] != '')
		{
			if ($SQL != '')
				$SQL .= ' AND ';
			$SQL .= "(DateRideStart <= '".$_REQUEST['parDateTo']."')";
		}
	}
	if ($_SESSION['ShowDeleted'] <> 'Y')
	{
		if ($SQL != '')
			$SQL .= ' AND ';
		if ($RETURN_RIDERLIST)
			$SQL .= "(riders.Deleted='N' OR riders.Deleted Is Null)";
		else
			$SQL .= "(rides.Deleted='N' OR rides.Deleted Is Null)";
	}

	if ($_REQUEST['parRideValidation']=='UK')
	{
		if ($SQL != '')
			$SQL .= ' AND ';
		$SQL .= "(OriginUK='Y')";
	}

	$WHEREKEY = $SQL;
	if (trim($WHEREKEY) == '') $WHEREKEY = ' true ';

	$SQL  = $SEARCH_SQL.$WHEREKEY;
	if ($RETURN_RIDERLIST)
	{
		$SQL .= " GROUP BY riders.riderid ";
		//$KEY_ORDER = 'NameOnCertificate';
		//$KEY_DESC = '';
	}
	else
	{
		$SQL .= " GROUP BY URI ";
		//$KEY_ORDER = 'DateRideStart';
		//$KEY_DESC = $KEY_ORDER;
	}
	$SQL .= sql_order();
	
    if ($_REQUEST['debug']=='sql')
		echo("<hr />$SQL<hr />");
	$res = sql_query($SQL);
	return $res;

}

function search_results_row($ride_data,$showrides)
{
    global $MYKEYWORDS, $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
	if ($_REQUEST['parShowResults']=='rides')
	{
		$res .= "<td class=\"date\">".$ride_data['DateRideStart']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Rider_Name']."</td>";
		$res .= "<td class=\"text\">".$ride_data['IBA_Number']."</td>";
		$res .= "<td class=\"text\">".$ride_data['IBA_Ride']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Bike']."</td>";
		$res .= "<td class=\"text\">".$ride_data['EventName']."</td>";
	}
	else
	{
		$res .= "<td class=\"text\">".$ride_data['Rider_Name']."</td>";
		$res .= "<td class=\"text\">".$ride_data['IBA_Number']."</td>";
		if ($showrides)
		{
			$SQL = "SELECT Count(*) AS Rex FROM rides WHERE rides.riderid=".$ride_data['riderid'];
			if ($_SESSION['ShowDeleted'] <> 'Y')
				$SQL .= " AND rides.Deleted='N'";
			$rr = sql_query($SQL);
			$rrd = mysqli_fetch_assoc($rr);
		}
		else
			$rrd['Rex'] = '';
		$res .= "<td class=\"number\">".$rrd['Rex']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Country']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Postcode']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Phone']."</td>";
		$res .= "<td class=\"text\">".$ride_data['Email']."</td>";
	}
    return $res;
}


function show_search_results($where)
{
    global $SEARCH_SQL, $_KEY_ORDER, $KEY_DESC, $OFFSET, $PAGESIZE, $CMDWORDS, $KEY_FIND;

    $SQL = $SEARCH_SQL;
    if ($where <> '') 
		$SQL .= $where;
	else
		$SQL .= 'true';

	if (!isset($_REQUEST['parShowResults']) || $_REQUEST['parShowResults']!='rides')
		$SQL .= " GROUP BY riders.riderid ";
	else
		$SQL .= " GROUP BY URI ";
	$SQL .= sql_order();
	//echo($SQL.'<hr />');
    $ride = sql_query($SQL);
	$TotRows = foundrows();
	$xl = '';
	echo("<div class=\"maindata\" $TotRows><br /<br />");
	if ($TotRows > mysqli_num_rows($ride))
		show_common_paging($TotRows,$xl);
    echo("<table>");
	if ($_REQUEST['parShowResults']=='rides')
	{
		echo("<caption>Rides matching search criteria (".number_format($TotRows).")</caption>");
		echo("<tr><th>".column_anchor('Date','DateRideStart')."</th>");
		echo("<th>".column_anchor('Rider','NameOnCertificate')."</th>");
		echo("<th>".column_anchor('IBA#','IBA_Number')."</th>");
		echo("<th>".column_anchor('Ride','IBA_Ride')."</th>");
		echo("<th>".column_anchor('Bike','Bike')."</th>");
		echo("<th>".column_anchor('Event','EventName')."</th>");
		echo("</tr>"); // Matches RoH
	}
	else
	{
		echo("<caption>Riders matching search criteria (".number_format($TotRows).")</caption>");
		echo("<tr><th>".column_anchor('Rider','NameOnCertificate')."</th>");
		echo("<th>".column_anchor('IBA#','IBA_Number')."</th>");
		echo("<th>Rides</th>");
		echo("<th>".column_anchor('Country','Country')."</th>");
		echo("<th>".column_anchor('Postcode','Postcode')."</th>");
		echo("<th>Phone</th><th>Email</th></tr>\n");
	}
	
	$rownum = 0;
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	$NRL = ($_REQUEST['c'] == 'snr');
    while(true)
    {
        $ride_data = mysqli_fetch_assoc($ride);
        if ($ride_data == false) 
		{
			break;
		}
		//var_dump($ride_data);
		if ($OK && $_REQUEST['parShowResults']=='rides')
			$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['showride']."&URI=".$ride_data['URI']."'\" class=\"goto row-";
		else if ($OK && $NRL)
			$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['newride']."&riderid=".$ride_data['riderid']."&bikeid=".$ride_data['bikeid']."'\" class=\"goto row-";
		else if ($OK)
			$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['showrider']."&".$CMDWORDS['uri']."=".$ride_data['riderid']."'\" class=\"goto row-";
		else
			$trspec = "class=\"row-";
		$rownum++;
		if ($rownum % 2 == 1)
			echo("<tr ".$trspec."1\">");
		else
			echo("<tr ".$trspec."2\">");
        echo(search_results_row($ride_data,true)."</tr>\n");
    }
	$ride_data['Rider_Name'] = '&lt;new rider&gt;';
	$ride_data['IBA_Number'] = '&lt;new rider&gt;';
	$ride_data['Phone'] = '&lt;new rider&gt;';
	$ride_data['Email'] = '&lt;new rider&gt;';
	if ($_REQUEST['parShowResults']!='rides')
	{
		if ($OK && $NRL)
			$trspec = "onclick=\"window.location='index.php?c=".$CMDWORDS['newride']."&key=".urlencode($KEY_FIND)."'\" class=\"goto row-";
		else if ($OK)
			$trspec = "title=\"Click here to create a new rider record\" onclick=\"window.location='index.php?c=".$CMDWORDS['shownewrider']."'\" class=\"goto row-";
		else
			$trspec = "class=\"row-";
		$rownum++;
		if ($rownum % 2 == 1)
			echo("<tr ".$trspec."1\">");
		else
			echo("<tr ".$trspec."2\">");
		echo(search_results_row($ride_data,false)."</tr>\n");
	}
	echo("</table>");
	if ($TotRows > mysqli_num_rows($ride))
		show_common_paging($TotRows,$xl);
    echo("</div>");
	
}


function sDD($dt)
{
	return "<span class=\"errordata\">".date('j\<\s\u\p\>S\<\/\s\u\p\> F Y',strtotime($dt))."</span>";
}

function searchDatesDescription()
{
	if ($_REQUEST['parRideDates'] == 'all')
		return '';
	if (isset($_REQUEST['parDateFrom']))
		if (isset($_REQUEST['parDateTo']))
			$res = " between ".sDD($_REQUEST['parDateFrom'])." and ".sDD($_REQUEST['parDateTo']);
		else
			$res = " since ".sDD($_REQUEST['parDateFrom']);
	else if (isset($_REQUEST['parDateTo']))
		$res = " upto ".sDD($_REQUEST['parDateTo']);
	else
		$res = '';
	return $res;
}

function reportSearchResults()
{
	global $KEY_FIND;
	
	$res = '';
	if ($KEY_FIND != '')
	{
		if ($res != '') $res .= '; ';
		$res .= "I was looking for <span class=\"errordata\">\"".$KEY_FIND."\"</span>".searchDatesDescription();
	}
	else if ($_REQUEST['parRideDates'] != 'all')
	{
		if ($res != '') $res .= '; ';
		$res .= "I was searching".searchDatesDescription();
	}
	if (isset($_REQUEST['likefld']))
	{
		if ($res != '') 
			$res .= '; ';
		else
			$res = "I was looking for ";
		$res .= "<span class=\"errordata\">".$_REQUEST['likefld']." matching '".htmlentities($_REQUEST['likeval'])."'</span>";
	}
	if ($_REQUEST['parRideValidation'] == 'UK')
	{
		if ($res != '') 
			$res .= '; ';
		else
			$res = "I was looking for ";
		$res .= "<span class=\"errordata\">UK validated rides only</span>";
	}
	return $res;
}


$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
if (!$OK) safe_default_action();


if ($KEY_FIND != '' || $_REQUEST['x'] != '')
{
	
    $res = searchall($KEY_FIND,$KEY_ORDER,$KEY_ORDER==$KEY_DESC);

	$N = foundrows();

    start_html("Searching records");
	//var_dump($_REQUEST);
    echo("<p>");

	echo reportSearchResults();

    if ($N < 1)
        echo(" but I found no matches at all.  Bad guess, maybe have another go?");
    echo("</p>");
    if ($N >= 0)
    {
		show_search_results($WHEREKEY);
        exit();
    }
}
elseif ($STARTED == '')
    start_html("Search database");

$SQL  = "SELECT COUNT(*) AS numrides FROM rides";
if ($_SESSION['ShowDeleted'] <> 'Y')
	$SQL .= " WHERE Deleted='N'";
$r = sql_query($SQL);
$rr = mysqli_fetch_assoc($r);
$numrides = $rr['numrides'];

$SQL  = "SELECT COUNT(*) AS numriders FROM riders";
if ($_SESSION['ShowDeleted'] <> 'Y')
	$SQL .= " WHERE Deleted='N'";
$r = sql_query($SQL);
$rr = mysqli_fetch_assoc($r);
$numriders = $rr['numriders'];

$SQL  = "SELECT COUNT(*) AS numbikes FROM bikes";
if ($_SESSION['ShowDeleted'] <> 'Y')
	$SQL .= " WHERE Deleted='N'";
$r = sql_query($SQL);
$rr = mysqli_fetch_assoc($r);
$numbikes = $rr['numbikes'];

?>

<div id="searchform">
<script>
function showDates()
{
	//alert("Hello sailor");
	if (document.getElementById('parRideDatesSome').checked)
		document.getElementById('ridedatesrange').style.display = 'inline';
	else
		document.getElementById('ridedatesrange').style.display = 'none';
}
function validateSearch()
{
	return true;
}
</script>
<h2>ADVANCED SEARCH</h2>
<p>I'm currently minding <strong><?php echo($numrides);?></strong> ride records on behalf of 
<strong><?php echo($numriders); ?></strong> riders who rode a total of <strong><?php echo($numbikes); ?>
</strong> bikes.<?php if ($_SESSION['ShowDeleted'] == 'Y')echo(" (all including deleted records)"); ?></p>

<form action="index.php" onsubmit="return validateSearch();">
<p>
<input type="hidden" name="c" value="se"/>
<input type="hidden" name="x" value="x"/>
<label for "f">What should I look for (if anything)?</label> <input type="text" name="f"/><br />
You can enter a rider's name or IBA number, a ride name such as <em>SS1000</em>
<?php 
if ($OK) 
	echo(", a postcode, email, phone or address"); 
?>
; Just enter the words you're looking for, no quote marks, ANDs, ORs, etc.
<fieldset><legend>Include rides ridden</legend>
<label for="parRidesDatesAll">Whenever</label><input onclick="showDates()" type="radio" id="parRideDatesAll" name="parRideDates" value="all" checked>  
<label for="parRidesDatesSome">only between dates</label><input onclick="showDates()" type="radio" id="parRideDatesSome" name="parRideDates" value="range" >
<span id="ridedatesrange"><input type="date" name="parDateFrom" title="From this date" value="2000-01-01"> <input type="date" title="Upto this date" name="parDateTo" value="<?php echo(gmdate("Y-m-d"));?>"></span>
</fieldset>
<fieldset><legend>Include rides validated</legend>
<label for="parRidesValidateAll">Anywhere</label><input type="radio" id="parRideValidateAll" name="parRideValidation" value="all" checked>  
<label for="parRidesValidateUK">only by IBAUK</label><input type="radio" id="parRideValidateSome" name="parRideValidation" value="UK" >
</fieldset>
<fieldset><legend>Show results as</legend>
<label for="parShowResultsRides">Individual rides</label><input type="radio" id="parShowResultsRides" name="parShowResults" value="rides" > 
<label for="parShowResultsRiders">Riders/Pillions</label><input type="radio" id="parShowResultsRiders" name="parShowResults" value="riders" checked>
</fieldset>
<input type="submit" value="Find it!"/><br />
</p>
</form>
<?php 
if ($OK)
{
?>
<form action="index.php"><input type="hidden" name="c" value="showride">
<p>If you want to look at a particular <?php echo($MYKEYWORDS['ride']);?>, enter its <?php echo($MYKEYWORDS['uri']);?> here
<input type="text" name="URI" size="10"/><input type="submit" value="Show <?php echo($MYKEYWORDS['ride']);?>"/></p></form>
<?php
}
?>
<script>
showDates();
</script>
</div>
</body>
</html>
