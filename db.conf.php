<?php

/*
 * I B A U K - db.conf.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */

$db_type = "sqlite";


$db_ibauk = "ibaukrd.db";

$db_startup = "";

$error_header = "<html><head><title>Fatal error encountered</title><meta name=\"author\" content=\"Bob Stammers\" /></head><body>";
$error_help = "<p>Is that machine running? Is MySQL installed and generally happy? Has someone fiddled with userids or passwords?<br />If you can't work it out yourself you'll have to call a grownup, sorry about that.</p></body></html>";

// Open the database	
try
{
	$db_ibauk_conn = new SQLite3($db_ibauk);
} catch(Exception $ex) {
	die("OMG ".$ex->getMessage().' file=[ '.$db_ibauk.' ]');
}


function dblastid($table,$col)
{
	$sql = "SELECT $col FROM $table ORDER BY $col DESC LIMIT 1";
	$r = sql_query($sql);
	$rr = $r->fetchArray();
	return $rr[$col];
}

function defaultRecord($table)
{
	global $db_ibauk_conn;

	$sql = "PRAGMA table_info($table)";
	$R = $db_ibauk_conn->query($sql);
	$res = [];
	while ($rd = $R->fetchArray()) 
		$res[$rd['name']] = $rd['dflt_value'];
	return $res;

}

function sql_query($sql,$DontLogThis=FALSE)
{
    global $db_ibauk_conn;

//    echo("<hr /><p>$sql</p></hr />");
	try {
		$res = $db_ibauk_conn->query($sql);
		if (!$DontLogThis) {
			$words = explode(" ",$sql);
			if (preg_match("/insert|update|delete|replace/i",$words[0])) {
				$log = "UPDATE sysvars SET LastUpdated='".date("Y-m-d H:i:s")."',LastUpdatedBy='".safesql($_SESSION['USERNAME'])."'";
				sql_query($log,TRUE);
			}

		}
		$lc = $db_ibauk_conn->lastErrorCode();
		if ($lc != 0 && FALSE)
			echo("<hr>GOT $lc (".$db_ibauk_conn->lastErrorMsg()." - $sql");
	} catch(exception $err) {
	 	echo('<hr /><p class=\"errormsg\">ERROR: '.$err->getMessage().'</p></hr />'.$sql.'<hr />');
	}
    return $res;
}

function query_results($R) {

	try {
		return $R->fetchArray();
	} catch (exception $e) {
		return null;
	}

}

function countrecs($R)
{
	$rows = 0;
	if ($R)
	try {
		while ($rd = $R->fetchArray())
			$rows++;
		$R->reset();
	} catch (exception $e) {
		;
	}
	return $rows;

}

function safesql($s)
{
	global $db_ibauk_conn;
	
	return $db_ibauk_conn->escapeString($s);
}

function safedatesql($d)
{
	if ($d == '' or $d == 'null')
		return "null";
	else
		return "'".safesql($d)."'";
}

function foundrows($R) 
/*
 * SQLite version
 * 
 * MySQL has a function FOUND_ROWS which, used with a special clause in the SQL
 * returns the number of rows that would have been returned without a LIMIT clause.
 * 
 * SQLite has no such functionality (or need) so we're just going to count the
 * rows returned then reset the handle.
 * 
 */
{
	$res = 0;
	while ($R->fetchArray())
		$res++;
	$R->reset();
	return $res;
}



function dump_selection($sql)
{
	global $db_ibauk, $db_ibauk_conn;
	
	$rsSearchResults = $db_ibauk_conn->query($sql);


	$hdr_written = false;
	while ($flds = $rsSearchResults->fetchArray(SQLITE3_ASSOC)) {
		if (!$hdr_written)
		{
			for ($i = 0; $i < count($flds); $i++) 
				echo('"'.array_keys($flds)[$i].'",');
			echo("\n");
			$hdr_written = true;
		}
		for ($i = 0; $i < count($flds); $i++) {
			$fname = array_keys($flds)[$i];
			$fval = array_values($flds)[$i];
			if (!preg_match("/\d\d\d\d\-\d\d\-\d\d/",$fval)) // It's a date so don't quote it
				echo('"'.$fval.'",');
			else
				echo($fval.",");
		}
		echo("\n");
	}
	
	
}

function dump_table($table)
{
	
	$sql = "SELECT * FROM $table";
	
	$out = dump_selection($sql);
	
	return $out;

}

function is_stringfield($fldtype)
{
		return preg_match("/varchar|char|text/i",$fldtype);
}


function getfields($table)
{
	global $db_ibauk_conn;
	
	$res = array();
	$rr = sql_query("PRAGMA table_info($table)");
	$col = 0;
	while ($r = $rr->fetchArray()) {
		$res[$col++] = $r['type'];
	}
	return $res;
	
}

function dump_table_sql($table)
{
	global $db_ibauk, $db_ibauk_conn;

	echo "DROP TABLE IF EXISTS `$table` ;\n";

	$R = sql_query("PRAGMA table_info($table)");
	echo("CREATE TABLE `$table` (");
	$comma = '';
	$pk = '';
	while ($rd = $R->fetchArray()) {
		echo($comma);
		$comma = ', ';
		echo('`'.$rd['name'].'` '.$rd['type'].' ');
		if ($rd['notnull']==1) {
			echo('NOT NULL ');
		}
		if ($rd['dflt_value']!='') {
			echo('DEFAULT '.$rd['dflt_value'].' ');
		}
		if ($rd['pk']!=0) {
			$pk = $rd['name'];
		}
	}
	if ($pk!='') {
		echo($comma.'PRIMARY KEY (`'.$pk.'`)');
	}
	echo(");\r\n");

	
	$sql = "SELECT * FROM $table";

	$rsSearchResults = sql_query($sql);
	$fields = getfields($table);
	$columns = sizeof($fields);

	echo"\n";


	// Add all values in the table
	while ($row = $rsSearchResults->fetchArray()) {
		echo "INSERT INTO `$table` ";
		echo " VALUES (";
		
		
		$comma = '';
		for ($col = 0; $col < $columns; $col++) {
		//foreach ($row as $col) {
			echo($comma);
			$comma = ',';
			if (is_null($row[$col]))
				echo('null');
			else if (is_stringfield($fields[$col]))
				echo("'".safesql($row[$col])."'");
			else
				echo($row[$col]);
		}
		echo ");\n";
	}
	
	
}

function getValueFromDB($sql,$col,$defval)
{
	$r = sql_query($sql);
	if ($r && $rd = $r->fetchArray())
		return $rd[$col];
	else
		return $defval;
}

?>