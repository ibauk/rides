<?php
/*
 * I B A U K - publicroh.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 * 2017-01	Added in RideStars handling
 */

require_once("general.conf.php");
require_once("db.conf.php");

$PUBLIC_RIDES_SQL  = "SELECT SQL_CALC_FOUND_ROWS DateRideStart,NameOnCertificate,IfNull(RideStars,'') As RideStars,IBA_Number,IBA_Ride,Bike,EventName FROM rides LEFT JOIN riders ON rides.riderid=riders.riderid LEFT JOIN bikes ON rides.bikeid=bikes.bikeid ";


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
		"bJQueryUI": true,
		"aoColumnDefs": [
						{ "iDataSort": [ 6 ], "aTargets": [ 0 ] },
                        { "bSearchable": false, "bVisible": false, "aTargets": [ 6 ] }
                    ],
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
<th style="width:12%;">Date</th>
<th style="width:16%">Rider Name</th>
<th style="width:8%">IBA Number</th>
<th style="width:20%">IBA Ride</th>
<th style="width:30%">Motorcycle</th>
<th style="width:14%">Event</th>
<th class="xx" style="width:0%">SDate</th>
</tr>
</thead>
<tbody>
<?php

	$SQL = $PUBLIC_RIDES_SQL." WHERE ShowRoH='Y' AND rides.Deleted='N'";
	//echo($SQL."<hr />");
	$rs = sql_query($SQL);
	while (true)
	{
		$rd = mysqli_fetch_assoc($rs);
		if ($rd == false) break;
		
		echo("<tr>");
		echo("<td>".colFmtDate($rd['DateRideStart'])."</td>");
		echo("<td>".$rd['NameOnCertificate']);
		if ($rd['RideStars'] != '')
			echo(" (".$rd['RideStars'].")");
		echo("</td>");
		echo("<td>".$rd['IBA_Number']."</td>");
		echo("<td>".$rd['IBA_Ride']."</td>");
		echo("<td>".$rd['Bike']."</td>");
		echo("<td>".$rd['EventName']."</td>");
		echo("<td>".$rd['DateRideStart']."</td>");
		echo("</tr>\n");
		
	}

?>

</tbody>
</table>
</body>
</html>
