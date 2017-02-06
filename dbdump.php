<?php
/*
 * I B A U K  - dbdump.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 * 2017-01	Trap trailing ; in column list
 */


function dump_csv()
{
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
	$sql = urldecode($_REQUEST['sql']);
	$where = urldecode($_REQUEST['where']);
	$cols = explode(';',urldecode($_REQUEST['cols']));
	if (isset($_REQUEST['csvname']))
		$csvname = urldecode($_REQUEST['csvname']);
	else
		$csvname = "ibaukrides.csv";
		
	$scols = '';
	
	for ($i=0; $i < count($cols); $i++)
	{
		if (strpos($cols[$i],',')) // May have trailing ;
		{
			$ch = explode(',',$cols[$i]);
			if ($scols <> '') 
				$scols .= ',';
			$scols .= $ch[0].' as `'.$ch[1].'`';
		}
	}
	if ($where != '')
		$wx = " WHERE $where";
	else
		$wx = '';
	$mysql = str_replace('*',$scols,$sql).$wx;
	//echo($mysql."<hr />");
	header('Content-type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="'.$csvname.'"');
	dump_selection($mysql);
		
}




?>