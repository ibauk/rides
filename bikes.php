<?php
/*
 * I B A U K - bikes.php
 *
 * Copyright (c) 2016 Bob Stammers
 *
 */


function show_bikes_listing()
{

	global $KEY_ORDER, $KEY_DESC;
		
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
	$SQL = "SELECT SQL_CALC_FOUND_ROWS bikes.Bike As BikeDesc,Count(Distinct bikes.bikeid) As NumBikes,Count(URI) As NumRides FROM rides LEFT JOIN bikes ON rides.bikeid=bikes.bikeid";
	if ($_SESSION['ShowDeleted']!='Y')
		$SQL .= " WHERE rides.Deleted='N' AND bikes.Deleted='N'";
	$SQL .= " GROUP BY BikeDesc";
	if ($_REQUEST['order']=='')
	{
		$KEY_ORDER = "NumRides DESC";
		$KEY_DESC = '';
	}
	$rs = sql_query($SQL.sql_order());
	$TotRows = foundrows();
	start_html("Bikes by Make &amp; Model");

	echo("<p>This table shows the number of bike records held in the database grouped by description. A small number of bikes will effectively be double counted as both rider and pillion have associated bike records.</p>");

	echo("<div class=\"maindata\">");
	

	if ($TotRows > mysqli_num_rows($rs))
		show_common_paging($TotRows,'');

	echo("<table><caption>Bikes by Make &amp; Model (".number_format($TotRows).")</caption>");
	echo("<tr><th>".column_anchor('Bike','BikeDesc')."</th><th>".column_anchor('N<sup>o</sup> of Bikes','NumBikes')."</th><th>".column_anchor('N<sup>o</sup> of Rides','NumRides')."</th></tr>");

	// Set fields for later .CSV extraction
	$colselection = "BikeDesc,Bike;NumBikes,NumBikes;NumRides,NumRides";
	
	$rownum = 0;
	while (true)
	{
		$rr = mysqli_fetch_assoc($rs);
		if ($rr == false) break;
		$rownum++;
		$trspec = "onclick=\"window.location='index.php?c=se&x=x&likefld=Bike&likeval=".urlencode($rr['BikeDesc'])."'\" ";
		if ($rownum % 2 == 1)
			echo("<tr $trspec class=\"goto row-1\">");
		else
			echo("<tr $trspec class=\"goto row-2\">");
		echo("<td>".htmlentities($rr['BikeDesc'])."</td><td>".$rr['NumBikes']."</td><td>".$rr['NumRides']."</td></tr>");
	}
	echo("</table>");
	if ($TotRows > mysqli_num_rows($rs))
		show_common_paging($TotRows,'');
?>
	<form action="index.php" method="post">
	<input type="hidden" name="cmd" value="csv">
	<input type="hidden" name="sql" value="<?php echo(urlencode($SQL));?>">
	<input type="hidden" name="cols" value="<?php echo(urlencode($colselection));?>">
	<input type="hidden" name="where" value="">
	<input type="hidden" name="csvname" value="bikesbymodel.csv">
	<input type="submit" value="Download as .CSV">
	<form>
<?php
	echo("</div></body></html>");
	exit;
}

function show_make_listing()
{
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();

	$SQL = "SELECT SQL_CALC_FOUND_ROWS IF(InStr(bikes.Bike,' ')>0,LEFT(bikes.Bike,InStr(bikes.Bike,' ')),bikes.Bike) As Make,COUNT(DISTINCT bikes.bikeid) AS NumBikes,COUNT(URI) As NumRides FROM rides LEFT JOIN bikes ON rides.bikeid=bikes.bikeid";
	if ($_SESSION['ShowDeleted']!='Y')
		$SQL .= " WHERE rides.Deleted='N' AND bikes.Deleted='N'";
	$SQL .= " GROUP BY Make";
	if ($_REQUEST['order']=='')
	{
		$KEY_ORDER = "NumRides DESC";
		$KEY_DESC = '';
	}
	$rs = sql_query($SQL.sql_order());
	$TotRows = foundrows();
	start_html("Bikes by Manufacturer");

	echo("<p>This table shows the number of bike records held in the database grouped by manufacturer (The first word of the description). A small number of bikes will effectively be double counted as both rider and pillion have associated bike records.</p>");
	echo("<div class=\"maindata\">");
	echo("<table><caption>Bikes by Manufacturer (".number_format($TotRows).")</caption>");
	echo("<tr><th>".column_anchor('Make','Make')."</th><th>".column_anchor('N<sup>o</sup> of Bikes','NumBikes')."</th><th>".column_anchor('N<sup>o</sup> of Rides','NumRides')."</th></tr>");

	// Set fields for later .CSV extraction
	$colselection = "Make,Make;NumBikes,NumBikes;NumRides,NumRides";

	$rownum = 0;
	while (true)
	{
		$rr = mysqli_fetch_assoc($rs);
		if ($rr == false) break;
		$rownum++;
		$trspec = "onclick=\"window.location='index.php?c=se&x=x&likefld=Bike&likeval=".urlencode($rr['Make'])."%'\" ";
		if ($rownum % 2 == 1)
			echo("<tr $trspec class=\"goto row-1\">");
		else
			echo("<tr $trspec class=\"goto row-2\">");
		echo("<td>".htmlentities($rr['Make'])."</td><td>".$rr['NumBikes']."</td><td>".$rr['NumRides']."</td></tr>");
	}
	echo('</table>');
?>
	<form action="index.php" method="post">
	<input type="hidden" name="cmd" value="csv">
	<input type="hidden" name="sql" value="<?php echo(urlencode($SQL));?>">
	<input type="hidden" name="cols" value="<?php echo(urlencode($colselection));?>">
	<input type="hidden" name="where" value="">
	<input type="hidden" name="csvname" value="bikesbymake.csv">
	<input type="submit" value="Download as .CSV">
	<form>
<?php
	echo("</div></body></html>");
	exit;
}



?>
