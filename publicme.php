<?php
/*
 * I B A U K - publicme.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */

require_once("general.conf.php");
require_once("db.conf.php");

$PUBLIC_RIDES_SQL  = "SELECT SQL_CALC_FOUND_ROWS Rider_Name,awardlevel,awardyear FROM mileeaters LEFT JOIN riders ON mileeaters.riderid=riders.riderid ";


?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>MileEaters</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script src="jquery.js" type="text/javascript"></script>
<script src="jquery.dataTables.min.js" type="text/javascript"></script>
<link href="public/css/demo_table_jui.css" rel="stylesheet" type="text/css" />
<style type="text/css">

	@import url("public/css/jquery.ui.all.css");
	#dataTable {padding: 0;margin:0;width:100%;}
	#dataTable_wrapper{width:100%;}
	#dataTable_wrapper th {cursor:pointer}
	#dataTable_wrapper tr.odd {color:#000; background-color:#FFF;font-size:0.875em;}
	#dataTable_wrapper tr.odd:hover {color:#333; background-color:#CCC}
	#dataTable_wrapper tr.odd td.sorting_1 {color:#000; background-color:#999}
	#dataTable_wrapper tr.odd:hover td.sorting_1 {color:#000; background-color:#666}
	#dataTable_wrapper tr.even {color:#FFF; background-color:#666;font-size:0.875em;}
	#dataTable_wrapper tr.even:hover, tr.even td.highlighted{color:#EEE; background-color:#333}
	#dataTable_wrapper tr.even td.sorting_1 {color:#CCC; background-color:#333}
	#dataTable_wrapper tr.even:hover td.sorting_1 {color:#FFF; background-color:#000}


</style>
</head>
<body>
<script>

$(document).ready(function() {
	$('#dataTable').dataTable({
		"order": [[2,"desc"]]
	});
} );
</script>
<table  id="dataTable">
<thead>
<tr>
<th>MileEater awarded</th>
<th>Rider Name</th>
<th>Year</th>
</tr>
</thead>
<tbody>
<?php

	$SQL = $PUBLIC_RIDES_SQL." WHERE mileeaters.Deleted='N' ORDER BY awardyear DESC";
	//echo($SQL."<hr />");
	$rs = sql_query($SQL);
	while (true)
	{
		$rd = $rs->fetchArray();
		if ($rd == false) break;
		
		echo("<tr>");
		echo("<td>".$rd['awardlevel']."</td>");
		echo("<td>".$rd['Rider_Name']);
		echo("</td>");
		echo("<td>".$rd['awardyear']."</td>");
		echo("</tr>\n");
		
	}

?>

</tbody>
</table>
</body>
</html>
