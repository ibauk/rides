<?php
/*
 * I B A U K - importxls.php
 *
 * This is the SQLITE version
 * 
 * Upgraded to PhpSpreadsheet
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */

$IMPORTSPEC['xlsname']  = 'import.csv';
$IMPORTSPEC['firstdatarow'] = 2;
$IMPORTSPEC['cols']['routes'] = [];
 
if (!isset($db_ibauk_conn)) {
	die("I cannot be called directly!");
}

$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
if (!$OK) safe_default_action();

$debuglog = false;

$target_dir = __DIR__ . DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR;

require_once("vendor".DIRECTORY_SEPARATOR."autoload.php");
use PhpOffice\PhpSpreadsheet\Spreadsheet;
require_once '.'.DIRECTORY_SEPARATOR.'includespec.php';
include "loadimports.php";

start_html("Loading data");

if (!isset($IMPORTSPEC['xlsname'])) 
	die("No spreadsheet name found");


if ($debuglog) echo("Opening ".$IMPORTSPEC['xlsname']."<br />");
try {
	$xlstype = \PhpOffice\PhpSpreadsheet\IOFactory::identify($target_dir.$IMPORTSPEC['xlsname']);
} catch (Exception $e) {show_infoline("Error: ".$e->getMessage(),'errormsg');
}

$rdr = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($xlstype); 
$rdr->setReadDataOnly(true);
$rdr->setLoadSheetsOnly($IMPORTSPEC['whichsheet']);

$xls = $rdr->load($target_dir.$IMPORTSPEC['xlsname']);

$sheet = $xls->getSheet($IMPORTSPEC['whichsheet']);
	
$row = $IMPORTSPEC['firstdatarow'];  // Skip the column headers
$nrows = 0;
$npillions = 0;

echo("<p>Importing data from <strong>".$IMPORTSPEC['xlsname']."</strong></p>");


$sql = "SELECT Count(*) AS Rex FROM bulkimports";
$rex = getValueFromDB($sql,"Rex",0);
if ($rex > 0) {
	if (!isset($_REQUEST['force'])) {
		show_infoline("The import table is not empty, please fix and retry","errormsg");
		exit;
	}
}

resetBulkimports();
if ($IMPORTSPEC['ridedate']=='')
	$IMPORTSPEC['ridedate'] = date("Y-m-d"); // So it's a valid date in the SQL query
sql_query('BEGIN');
while (true) {
	if ($debuglog) error_log('record '.$nrows);
	try {
		//if ($debuglog) echo('[cols][ridername]='.$IMPORTSPEC['cols']['ridername']);
		if (isset($IMPORTSPEC['cols']['ridername'])) {
			$rider_name = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['ridername'],$row)->getValue();
		} elseif (isset($IMPORTSPEC['cols']['firstname']) && isset($IMPORTSPEC['cols']['lastname'])) {
			$rider_name = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['firstname'],$row)->getValue().' '.$sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['lastname'],$row)->getValue();
		} else {
			$rider_name = '';
		}
		
		if (trim($rider_name)=='') 
			break;
		if ($debuglog) echo("Rider :$rider_name:");
		$nrows++;
		
		if (isset($IMPORTSPEC['cols']['RiderIBA'])) {
			$RiderIBA = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['RiderIBA'],$row)->getValue();
		} else {
			$RiderIBA = '';
		}
		if (isset($IMPORTSPEC['cols']['PillionIBA'])) {
			$PillionIBA = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['PillionIBA'],$row)->getValue();
		} else {
			$PillionIBA = '';
		}

		if (isset($IMPORTSPEC['cols']['bike'])) {
			$bike = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['bike'],$row)->getValue();
		} elseif (isset($IMPORTSPEC['cols']['make']) && isset($IMPORTSPEC['cols']['model'])) {
			$bike = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['make'],$row)->getValue().' '.$sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['model'],$row)->getValue();
		} else {
			$bike = '';
		}
		if ($debuglog) echo(" Bike: $bike");
		
		if (isset($IMPORTSPEC['cols']['BikeReg'])) {
			$BikeReg = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['BikeReg'],$row)->getValue();
		} else {
			$BikeReg = '';
		}
		// This chooses a route based on which spreadsheet column is not blank
		$route = '';
		if (isset($IMPORTSPEC['cols']['routes']) && is_array($IMPORTSPEC['cols']['routes'])) {
			foreach ($IMPORTSPEC['cols']['routes'] as $routenumber => $col) {
				if ($sheet->getCellByColumnAndRow($col,$row) <> '') {
					$route = $routenumber;
					break;
				}
			}
		}
		
		// This derives a route directly from the input file
		if (isset($IMPORTSPEC['cols']['route']))
			$route = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['route'],$row)->getValue();
		
		if (isset($IMPORTSPEC['cols']['ridestars'])) {
			$ridestars = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['ridestars'],$row)->getValue();
		} else {
			$ridestars = '';
		}
		if (isset($IMPORTSPEC['cols']['placing'])) {
			$placing = intval($sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['placing'],$row)->getValue());
		} else {
			$placing = 0;
		}
		if (isset($IMPORTSPEC['cols']['points'])) {
			$points = intval($sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['points'],$row)->getValue());
		} else {
			$points = 0;
		}
		if (isset($IMPORTSPEC['cols']['miles'])) {
			$miles = intval($sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['miles'],$row)->getValue());
		} else {
			$miles = 0;
		}

		if (isset($IMPORTSPEC['cols']['email'])) {
			$email = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['email'],$row)->getValue();
		} else {
			$email = '';
		}
		if (isset($IMPORTSPEC['cols']['address'])) {
			$address = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['address'],$row)->getValue();
		} else {
			$address = '';
		}
		if (isset($IMPORTSPEC['cols']['postcode'])) {
			$postcode = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['postcode'],$row)->getValue();
		} else {
			$postcode = '';
		}
		if (isset($IMPORTSPEC['cols']['phone'])) {
			$phone = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['phone'],$row)->getValue();
		} else {
			$phone = '';
		}
		if (isset($IMPORTSPEC['cols']['mobile'])) {
			$mobile = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['mobile'],$row)->getValue();
		} else {
			$mobile = '';
		}

		
		//if ($debuglog) echo(" route: $route");
		$sql = "INSERT INTO bulkimports (ride_rally,EventID,rider_name,IBA_Number,is_pillion,Bike,BikeReg,route_number,ridestars";
		$sql .= ",finishposition,points,miles,ridedate";
		$sql .= ",Email,Postal_Address,Postcode,Phone,AltPhone";
		$sql .= ") VALUES (";
		$sql .= $IMPORTSPEC['ride_rally'];
		$sql .= ",'".safesql(trim($IMPORTSPEC['eventid']))."'";
		$sql .= ",'".safesql(trim($rider_name))."','".$RiderIBA."',0,'".safesql(trim($bike))."','".safesql(trim($BikeReg))."','".safesql(trim($route))."','".safesql(trim($ridestars))."'";
		$sql .= ",$placing,$points,$miles";
		$sql .= ",'".$IMPORTSPEC['ridedate']."'";
		$sql .= ",'".safesql($email)."'";
		$sql .= ",'".safesql($address)."'";
		$sql .= ",'".safesql($postcode)."'";
		$sql .= ",'".safesql($phone)."'";
		$sql .= ",'".safesql($mobile)."'";
		$sql .= ")";
		//echo("$sql<hr />");
		sql_query($sql);
		if ($debuglog) echo("<br />");
		if (isset($IMPORTSPEC['cols']['pillionname'])) {
			$pillion = $sheet->getCellByColumnAndRow($IMPORTSPEC['cols']['pillionname'],$row)->getValue();
			if ($pillion <> '') {
				if ($debuglog) echo("Pillion: $pillion Bike: $bike");
				$npillions++;
				$sql = "INSERT INTO bulkimports (ride_rally,EventID,rider_name,IBA_Number,is_pillion,Bike,BikeReg,route_number,ridestars";
				$sql .= ",finishposition,points,miles,ridedate";
				$sql .= ",Email,Postal_Address,Postcode,Phone,AltPhone";
				$sql .= ") VALUES (";
				$sql .= $IMPORTSPEC['ride_rally'];
				$sql .= ",'".safesql(trim($IMPORTSPEC['eventid']))."'";
				$sql .= ",'".safesql(trim($pillion))."','".$PillionIBA."',1,'".safesql(trim($bike))."','".safesql(trim($BikeReg))."','".safesql(trim($route))."','".safesql(trim($ridestars))."'";
				$sql .= ",$placing,$points,$miles";
				$sql .= ",'".$IMPORTSPEC['ridedate']."'";
				$sql .= ",'".safesql($email)."'";
				$sql .= ",'".safesql($address)."'";
				$sql .= ",'".safesql($postcode)."'";
				$sql .= ",'".safesql($phone)."'";
				$sql .= ",'".safesql($mobile)."'";
				$sql .= ")";
				//echo("$sql<hr />");
				sql_query($sql);
				if ($debuglog) echo("<br />");
			}
		}
	} catch(Exception $e) {
		show_infoline("Caught ".$e->getMessage(),"errormsg");
		break;
	}
	$row++;
}
if ($debuglog) error_log('done');
echo("<p>All done - $nrows rows loaded; $npillions pillions</p>");
sql_query("COMMIT"); 
show_imports();
?>
