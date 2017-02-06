<?php
/*
 * I B A U K - dbexport.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 */
 
require_once('general.conf.php');
require_once('db.conf.php');

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();

function export_database()
{
	global $db_ibauk, $APPLICATION_TITLE, $APPLICATION_VERSION;
	
	
	
	/* MySQL workbench has a problem importing this - can't find encoding */
	
	header('Content-Type: text/plain; charset=utf-8');
	header("Content-Disposition: attachment; filename=".$db_ibauk."_fulldump.sql");
	echo "-- $APPLICATION_TITLE v$APPLICATION_VERSION\n";
	echo "-- Exported at ".date("Y-m-d H:i:s")."\n\n";
	echo "CREATE DATABASE  IF NOT EXISTS `".$db_ibauk."` /*!40100 DEFAULT CHARACTER SET utf8 */;\n";
	echo "USE `".$db_ibauk."`;\n\n";
	sql_query("SET NAMES utf8");
  	$r = sql_query("SHOW TABLES");
	$xx = 'Tables_in_'.$db_ibauk;

	while (TRUE)
	{
		$rr = mysqli_fetch_assoc($r);
		if ($rr == FALSE) break;
		dump_table_sql($rr[$xx]);
	}

	echo "\n-- Export complete at ".date("Y-m-d H:i:s")."\n\n";

	exit();

}


export_database();

?>

