<?php

/*
 * I B A U K - db.conf.php
 *
 * Copyright (c) 2016 Bob Stammers
 *
 */

$db_type = "mysql";

$db_host = "localhost";
$db_user = "root";
$db_pass = "201053";
$db_ibauk = "ibauk";

//$db_host = "160.153.16.16"; //GoDaddy
//$db_user = "ibaukrd";
//$db_pass = "Pc$9j0%u%v";
//$db_ibauk = "ibaukrd";

$db_startup = "";

$error_header = "<html><head><title>Fatal error encountered</title><meta name=\"author\" content=\"Bob Stammers\" /></head><body>";
$error_help = "<p>Is that machine running? Is MySQL installed and generally happy? Has someone fiddled with userids or passwords?<br />If you can't work it out yourself you'll have to call a grownup, sorry about that.</p></body></html>";


//if (!isset($db_ibauk)) $db_ibauk = "ibaukrd";

// Use persistent connections if available

	$db_ibauk_conn = mysqli_connect($db_host,$db_user,$db_pass,$db_ibauk);
	if (!$db_ibauk_conn) {
            system($db_startup);
            $db_ibauk_conn = mysqli_connect($db_host,$db_user,$db_pass,$db_ibauk)
              or die($error_header."<p>I'm sorry but I couldn't connect to the database running on $db_host with userid=$db_user !<br />MySQL reported <strong>".mysqli_connect_errno()."</strong></p>".$error_help);
	  }
	//mysqli_set_charset($db_ibauk_conn,"utf8");
	//var_dump($db_ibauk_conn);
	//die("Hello sailor");

function dblastid($table,$col)
{
	$sql = "SELECT $col FROM $table ORDER BY $col DESC LIMIT 1";
	$r = sql_query($sql);
	$rr = mysqli_fetch_assoc($r);
	return $rr[$col];
}

function sql_query($sql,$DontLogThis=FALSE)
{
    global $db_ibauk_conn;

//    echo("<hr /><p>$sql</p></hr />");
    $res = mysqli_query($db_ibauk_conn,$sql);
    $err = mysqli_errno();
    if ($err != 0) echo('<hr /><p class=\"errormsg\">ERROR: '.mysqli_error().'</p></hr />'.$sql.'<hr />');
    $act = strtoupper(strtok($sql,' '));
    if ($act <> 'SELECT' && $act <> 'SHOW' && !$DontLogThis)
    {
        $logsql  = "INSERT IGNORE INTO history (userid,thesql,theresult) ";
        $logsql .= "VALUES('".$_SESSION['USERNAME']."','".safesql($sql)."',0".$err.")";
		//echo($logsql."<hr />");
        mysqli_query($db_ibauk_conn,$logsql);
		$err = mysqli_errno();
		if ($err != 0) echo('<hr /><p class=\"errormsg\">ERROR: '.mysqli_error().'</p></hr />'.$sql.'<hr />');
    }
    return $res;
}

function safesql($s)
{
	global $db_ibauk_conn;
	
	return mysqli_real_escape_string($db_ibauk_conn,$s);
}

function safedatesql($d)
{
	if ($d == '' or $d == 'null')
		return "null";
	else
		return "'".safesql($d)."'";
}

function foundrows()
{
	$res2 = mysqli_fetch_assoc(sql_query("SELECT FOUND_ROWS()"));
	$N = intval($res2['FOUND_ROWS()']);
	return $N;	
}

function dump_selection($sql)
{
	global $db_ibauk, $db_ibauk_conn;
	
	$rsSearchResults = mysqli_query($db_ibauk_conn, $sql);


	$hdr_written = false;
	while ($flds = mysqli_fetch_array($rsSearchResults,MYSQLI_ASSOC)) {
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
	
	global $db_ibauk, $db_ibauk_conn;
	
	$sql = "SELECT * FROM $table";
	
	$out = dump_selection($sql);
	
	return $out;

}

function is_stringfield($fldtype)
{
		return preg_match("/varchar|char|text/i",$fldtype);
}

function is_datefield($fldtype)
{
		return preg_match("/date|timestamp/i",$fldtype);
}

function getfields($table)
{
	global $db_ibauk_conn;
	
	$res = array();
	$rr = mysqli_query($db_ibauk_conn,"SHOW COLUMNS FROM $table");
	while ($r = mysqli_fetch_assoc($rr)) {
		$res[$r['Field']] = $r['Type'];
	}
	return $res;
	
}

function dump_table_sql($table)
{
	global $db_ibauk, $db_ibauk_conn;
	
	$sql = "SHOW CREATE TABLE $table";
	$rsSearchResults = sql_query($sql);
	$i = mysqli_fetch_array($rsSearchResults);
	
	echo "DROP TABLE IF EXISTS `$table` ;\n";
	echo str_replace("CHARSET=latin1","CHARSET=utf8",$i[1]).";\n";
	echo "LOCK TABLES `$table` WRITE;\n";
	
	$sql = "SELECT * FROM $table";

	$rsSearchResults = sql_query($sql);
	$fields = getfields($table);
	$columns = sizeof($fields);

	echo"\n";


	// Add all values in the table
	while ($row = mysqli_fetch_array($rsSearchResults)) {
		echo "INSERT INTO `$table` ";
		// Put the name of all fields
		if (FALSE)
		{
			echo "(";
			for ($col = 0; $col < $columns; $col++) {
				$fld = array_keys($fields)[$col];
				echo "`".$fld.'`';
				if ($col+1 < $columns)
					echo ',';
			}
			echo ") ";
		}
		echo " VALUES (";
		
		
		for ($col = 0; $col < $columns; $col++) {
			if (is_null($row[$col]))
				echo 'null';
			else if (is_stringfield(array_values($fields)[$col]))
				echo "'".mysqli_real_escape_string($db_ibauk_conn,$row[$col])."'";
			else if (is_datefield(array_values($fields)[$col]))
				echo "'".mysqli_real_escape_string($db_ibauk_conn,$row[$col])."'";
			else
				echo $row[$col];
			if ($col+1 < $columns)
				echo ',';
		}
		echo ");\n";
	}
	echo "UNLOCK TABLES;\n";
	
	
}


?>