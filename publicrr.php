<?php
/*
 * I B A U K - publicrr.php
 *
 * Copyright (c) 2020 Bob Stammers
 * 
 * SQLite version
 *
 * Rally results
 */

require_once("general.conf.php");
require_once("db.conf.php");

$PUBLIC_RIDES_SQL  = "SELECT RallyID,FinishPosition,RallyMiles,RallyPoints,Rider_Name,IfNull(Bike,'&nbsp;') As Bike FROM rallyresults LEFT JOIN riders ON rallyresults.riderid=riders.riderid LEFT JOIN bikes ON rallyresults.bikeid=bikes.bikeid ORDER BY 1 DESC,2";


?>
<!DOCTYPE html>
<html lang="en">
<head>
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
<script type="text/javascript">
$(document).ready(function() {
	oTable = $('#dataTable').dataTable({
		"order": [],
		"bJQueryUI": true,
		"bScrollCollapse": false,
		"sScrollY": "475px",
		"bAutoWidth": true,
		"bPaginate": true,
		"sPaginationType": "full_numbers", //full_numbers,two_button
		"bStateSave": true,
		"bInfo": true,
		"bFilter": true,
		"iDisplayLength": 25,
		"bLengthChange": true,
		"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
	});
} );

</script>
<table  id="dataTable">
<thead>
<tr>
<th>Rally</th>
<th>Position</th>
<th>Rider Name</th>
<th>Bike</th>
<th>Miles</th>
<th>Points</th>
</tr>
</thead>
<tbody>
<?php

	$SQL = $PUBLIC_RIDES_SQL;
	//echo($SQL."<hr />");
	$rs = sql_query($SQL);
	while (true)
	{
		$rd = $rs->fetchArray(SQLITE3_ASSOC);
		if ($rd == false) break;
		
		echo("<tr>");
		echo("<td>".$rd['RallyID']."</td>");
		echo("<td>".$rd['FinishPosition']."</td>");
		echo("<td>".$rd['Rider_Name']."</td>");
		echo("<td>".$rd['Bike']."</td>");
		echo("<td>".$rd['RallyMiles']."</td>");
		echo("<td>".$rd['RallyPoints']."</td>");
		echo("</tr>\n");
		
	}

?>

</tbody>
</table>
</body>
</html>
