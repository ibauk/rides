<?php
/*
 * I B A U K - rallies.php
 *
 * Copyright (c) 2016 Bob Stammers
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
	for ($ix = 1; $ix < count($ride_data); $ix++)
		$res .= "<td>".$ride_data[$ix]."</td>";
    return $res;
}




function show_rallies_table($where,$what)
{
    global $ORDER, $DESC, $OFFSET, $PAGESIZE, $CMDWORDS;

    $SQL = str_replace('#WHERE#',$where <> '' ? " WHERE $where " : '',$RIDERS_SQL);
	if ($_REQUEST['event']=='')
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
	//echo($SQL.'<hr />');
    $ride = sql_query($SQL);
	$TotRows = foundrows();
	echo("<div class=\"maindata\" $TotRows><br /<br />");
	if ($TotRows > mysqli_num_rows($ride))
		show_common_paging($TotRows,$xl);
    echo("<table>");
	echo("<caption>$what</caption>");
	echo("<tr>".rallies_table_row_header($hdrs)."</tr>\n");
	$rownum = 0;
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
    while(true)
    {
        $ride_data = mysqli_fetch_array($ride,MYSQLI_NUM);
        if ($ride_data == false) break;
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
		
		$rd = mysqli_fetch_assoc($rr);
		$what = $rd['RallyTitle'].' <strong>'.$_REQUEST['event'].'</strong>';
		mysqli_close($rr);
	}

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	show_rallies_table($where,$what);
    echo("</body></html>");
        
}


?>
