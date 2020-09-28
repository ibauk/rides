<?php
/*
 * I B A U K - rallies.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */


function rallies_table_row_header($hdrs)
{
    global $MYKEYWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
	$hdrcols = explode(';',$hdrs);
	foreach ($hdrcols as $col)
		$res .= "<th>".$col."</th>";

    return $res;
}

function rallies_table_row_html($ride_data)
{
    global $MYKEYWORDS, $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
	// Skip the first field, which is used for linking
	$numflds = count($ride_data) / 2; // Because numeric index as well as fieldname
	error_log("numflds==$numflds");
	for ($ix = 1; $ix < $numflds; $ix++)
		$res .= "<td>".$ride_data[$ix]."</td>";
    return $res;
}



function edit_rallies_table()
{
	start_html('Rally maintenance');

	if (isset($_REQUEST['save'])) {
		//print_r($_REQUEST);
		$n = count($_REQUEST['RallyID']);
		for ($i = 0 ; $i < $n; $i++) {
			$sql = "UPDATE rallies SET RallyTitle='".safesql($_REQUEST['RallyTitle'][$i])."' WHERE RallyID='".safesql($_REQUEST['RallyID'][$i])."'";
			sql_query($sql);
		}
		if (isset($_REQUEST['NewRallyID']) && $_REQUEST['NewRallyID'] <> '' && isset($_REQUEST['NewRallyTitle']) && $_REQUEST['NewRallyTitle'] <> '')  {
			$sql = "INSERT INTO rallies(RallyID,RallyTitle) VALUES(";
			$sql .= "'".safesql($_REQUEST['NewRallyID'])."'";
			$sql .= ",'".safesql($_REQUEST['NewRallyTitle'])."')";
			sql_query($sql);
		}
		$n = count($_REQUEST['Delete']);
		for ($i = 0; $i < $n; $i++) {
			$sql = "DELETE FROM rallies WHERE RallyID='".$_REQUEST['Delete'][$i]."'";
			sql_query($sql);
		}

	}
	echo('<p>This table identifies each rally for which we hold records. The <em>RallyID</em> field contains enough letters to uniquely identify each rally.<br>');
	echo('For example "Jor" matches "Jorvik17", "Jorvik18", etc. Each instance of a rally includes the base name and the year (Iceni15, Iceni16, BBL17, etc).</p>');

	$sql = "SELECT * FROM rallies ORDER BY RallyID";
	$R = sql_query($sql);
	echo('<form method="post"><input type="hidden" name="c" value="ralliestab">');
	echo('<input type="hidden" name="save" value="save">');
	echo('<table><thead><tr><th>RallyID</th><th colspan="2">Rally name</th></tr></thead><tbody>');
	while ($rd = $R->fetchArray()) {
		echo('<tr><td><input type="text" class="short" readonly name="RallyID[]" value="'.$rd['RallyID'].'"></td>');
		echo('<td><input type="text" name="RallyTitle[]" value="'.$rd['RallyTitle'].'"></td>');
		$sql = "SELECT Count(*) As Rex FROM rallyresults WHERE RallyID LIKE '".$rd['RallyID']."%'";
		$rex = getValueFromDB($sql,"Rex",27);
		
		if ($rex < 1) {
			echo('<td>Delete ? <input type="checkbox" data-rally="'.$rd['RallyID'].'" title="Tick to delete this entry" name="Delete[]" ');
			echo('value="0" onclick="if (this.checked) this.value=this.getAttribute(\'data-rally\'); else this.value=0;"></td>');
		} else {
			//echo('<td><input type="text" class="short" readonly title="Number of results for this entry" name="Delete[]" value="'.$rex.'"></td>');
			echo('<td title="Number of results held for this entry">'.$rex.'</td>');
		}
		echo('</tr>');
	}
	echo('<tr><td><input type="text" class="short" title="Enter the basename of the new rally" name="NewRallyID" value=""></td>');
	echo('<td><input type="text" title="Enter the full name of the new rally" name="NewRallyTitle" value=""></td></tr>');
	echo('</tbody></table>');
	echo('<input type="submit" value="Save changes">');
	echo('</form>');

}

function show_rallies_table($where,$what)
{
    global $ORDER, $DESC, $OFFSET, $PAGESIZE, $CMDWORDS, $RIDERS_SQL;

	$xl = '';
    $SQL = str_replace('#WHERE#',$where <> '' ? " WHERE $where " : '',$RIDERS_SQL);
	if (!isset($_REQUEST['event']) || $_REQUEST['event']=='')
	{
		$SQL = "SELECT RallyID As RowID,RallyID, Count(recid) As Finishers FROM rallyresults WHERE RallyID LIKE '".$_REQUEST['id']."%' GROUP BY RallyID ORDER BY RallyID";
		$hdrs = "Event;Finishers";
		$cmd = $CMDWORDS['rallies']."&id=".$_REQUEST['id']."&event=";
	}
	else
	{
		$SQL = "SELECT rallyresults.riderid,FinishPosition,Rider_Name,Bike,RallyMiles,RallyPoints FROM rallyresults LEFT JOIN riders ON rallyresults.riderid=riders.riderid LEFT JOIN bikes ON rallyresults.bikeid=bikes.bikeid WHERE RallyID='".$_REQUEST['event']."' ORDER BY FinishPosition";
		$hdrs = "Position;Rider;Bike;Miles;Points";
		$cmd = $CMDWORDS['showrider']."&".$CMDWORDS['uri']."=";
	}

	//$SQL .= sql_order();
	error_log($SQL);
    $ride = sql_query($SQL);
	$TotRows = foundrows($ride);
	echo("<div class=\"maindata\">");
	$numrows = 0;
	while ($rd = $ride->fetchArray())
		$numrows++;
	$ride->reset();
	//if ($TotRows > $numrows)
		//show_common_paging($TotRows,$xl);
    echo("<table>");
	echo("<caption>$what</caption>");
	echo("<tr>".rallies_table_row_header($hdrs)."</tr>\n");
	$rownum = 0;
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
    while(true)
    {
        $ride_data = $ride->fetchArray();
		if ($ride_data == false) break;
		//print_r($ride_data);
		//echo('<hr>');
		if ($OK)
			$trspec = "onclick=\"window.location='index.php?c=".$cmd.$ride_data[0]."'\" class=\"goto row-";
		else
			$trspec = "class=\"row-";
		$rownum++;
		if ($rownum % 2 == 1)
			echo("<tr ".$trspec."1\">");
		else
			echo("<tr ".$trspec."2\">");
        echo(rallies_table_row_html($ride_data)."</tr>\n");
    }
    echo("</table></div>");
	
}



function show_rallies_listing()
{
    global $OFFSET, $PAGESIZE, $SHOW, $MYKEYWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
    start_html($MYKEYWORDS['rallies']." listing");
	if ($_REQUEST['id'] <> '')
	{
		$where = "RallyID LIKE '".$_REQUEST['id']."'";
		$SQL = "SELECT RallyTitle FROM rallies WHERE RallyID='".$_REQUEST['id']."'";
		$rr = sql_query($SQL);
		
		$rd = $rr->fetchArray();
		$what = $rd['RallyTitle'].' <strong>'.$_REQUEST['event'].'</strong>';
	}

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	show_rallies_table($where,$what);
    echo("</body></html>");
        
}


?>
