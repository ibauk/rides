<?php
/*
 * I B A U K - publicevents.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2023 Bob Stammers
 *
 */

require_once("general.conf.php");
require_once("db.conf.php");

$PUBLIC_EVENTS_SQL  = "SELECT startdate,ifnull(finishdate,startdate) AS finishdate,eventdesc,eventtype,ifnull(forumurl,'') as forumurl,ifnull(urltarget,'') as urltarget,mapref,cancelled FROM events";

$EVENT_TYPES = [1=>"Rides To Eat",2=>"Special Rides",3=>"Rallies",4=>"Other Events"];

$where = ' WHERE ';
$et = 0;
if (isset($_REQUEST['type']) && array_key_exists($_REQUEST['type'],$EVENT_TYPES)) {
    $where .= " eventtype=".$_REQUEST['type']." AND ";
    $et = intval($_REQUEST['type']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Schedule of upcoming events for IBAUK">
<title>IBAUK calendar</title>
<style type="text/css">

th  { text-align: left;}
td  { padding: 0 3px 0 3px; }

</style>
</head>
<body>
<script type="text/javascript">

</script>
<?php
    echo('<nav>');
    if ($et < 1) {
        echo('ALL ENTRIES ');
    }
    else {
        echo('[<a href="publicevents.php">all entries</a>] ');
    }
    foreach($EVENT_TYPES as $n=>$x) {
        $y = strtolower($x);
        if ($n == $et) {
            $y = strtoupper($x);
            echo($y." ");
        } else {
            echo('[<a href="publicevents.php?type='.$n.'">'.$y.'</a>] ');
        }
    }
echo('</nav><hr>');
?>
<style>
    table a { text-decoration: none; color: blue; }
</style>
<table  id="dataTable" style="font-family:Verdana, Geneva, Tahoma, sans-serif;">
<thead>
<tr>
</tr>
</thead>
<tbody>
<?php

    //echo('Hello sailor<br>');
    $todaysdate = new DateTime('now',new DateTimeZone('Europe/London'));
    $todaysdateiso =$todaysdate->format('Y-m-d');

	$SQL = $PUBLIC_EVENTS_SQL.$where." startdate >= '$todaysdateiso' ORDER BY startdate";
	//echo($SQL."<hr />"); exit;
	$rs = sql_query($SQL);
    $curyear = date("Y");
	while (true)
	{
		$rd = $rs->fetchArray();
		if ($rd == false) break;
		
		echo("<tr>");
        $xbr = '';
        $evtyear = substr($rd['startdate'],0,4);
        if ($evtyear != $curyear) {
            $xbr = '<br>';
            $curyear = $evtyear;
        }
		echo("<td>".$xbr);
        if ($rd['forumurl'] <> "") {
            echo('<a href="'.$rd['forumurl'].'"');
            echo(' title="click for details"');
            if ($rd['urltarget'] <> "") {
                echo(' target="'.$rd['urltarget'].'">');
            } else {
                echo(' target="forumurl">');
            }
            echo($rd['eventdesc'].'</a>');
        } else {
            echo('<span title="no details yet">');
            echo($rd['eventdesc']);
            echo('</span>');
        }
        echo("</td>");
		echo("<td>".$xbr.colFmtDate($rd['startdate']));
        if ($rd['startdate'] != $rd['finishdate']) {
            echo(' - '.colFmtDate($rd['finishdate']));
        }
        echo("</td>");
		echo("</tr>\n");
		
	}

?>

</tbody>
</table>
</body>
</html>
