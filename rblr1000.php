<?php

// rblr1000.php

$squires = "Squires cafe bar, Sherburn-in-Elmet";
$from_uri=1963;
$to_uri=2072;
$run_date='2017-06-24';
$routes = array("1001"=>"North, clockwise", "1002"=>"North, anticlockwise", "1003"=>"South, clockwise", "1004"=>"South, anticlockwise");

$sqlx = "UPDATE rides SET StartPoint='$squires',FinishPoint='$squires',PayMethod='PayPal'";
$sqlx .= ",RideVerifier='RBLR',Acknowledged='Y',DateVerified='$run_date',DatePayReq='$run_date',DatePayRcvd='$run_date',DateCertSent='$run_date',ShowRoH='Y' ";

$sql = $sqlx.",MidPoints='RBLR route' WHERE URI >=$from_uri and URI <= $to_uri";
//echo("<hr />".$sql."<hr />");
sql_query($sql);
$sql = $sqlx.",MidPoints='".$routes['1001']."' WHERE URI >=$from_uri and URI <= $to_uri and TotalMiles=1001";
sql_query($sql);
$sql = $sqlx.",MidPoints='".$routes['1002']."' WHERE URI >=$from_uri and URI <= $to_uri and TotalMiles=1002";
sql_query($sql);
$sql = $sqlx.",MidPoints='".$routes['1003']."' WHERE URI >=$from_uri and URI <= $to_uri and TotalMiles=1003";
sql_query($sql);
$sql = $sqlx.",MidPoints='".$routes['1004']."' WHERE URI >=$from_uri and URI <= $to_uri and TotalMiles=1004";
sql_query($sql);

?>


