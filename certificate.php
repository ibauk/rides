<?php

/*
 * I B A U K - certificate.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2025 Bob Stammers
 *
 */


$TEMPLATE_TEXT_PATH = 'certificates/templates/';
$TEMPLATE_IMAGES_PATH = 'certificates/images/';

 
function formattedDate($isodate)
{
	$dt = DateTime::createFromFormat("Y-m-d",$isodate);
	$res = $dt->format("j\<\s\u\p\>S\<\/\s\u\p\> F Y");
	return $res;
}

function spellNumber($n)
{
$ones = array(
 "",
 "one",
 "two",
 "three",
 "four",
 "five",
 "six",
 "seven",
 "eight",
 "nine",
 "ten",
 "eleven",
 "twelve",
 "thirteen",
 "fourteen",
 "fifteen",
 "sixteen",
 "seventeen",
 "eighteen",
 "nineteen"
);

$tens = array(
 "",
 "",
 "twenty",
 "thirty",
 "forty",
 "fifty",
 "sixty",
 "seventy",
 "eighty",
 "ninety"
);

	if ($n >= 100)
		return strval($n);
	$ntens = floor($n / 10);
	$nones = $n % 10;
	$restens = $tens[$ntens];
	$resones = $ones[$nones];
	if ($restens != '' && $resones != '')
		return $restens.'-'.$resones;
	else
		return $restens.$resones;
}

function formattedMidPoints($mps)
{
	try
	{
		$mp = explode(',',$mps);
	} catch(Exception $E) {
		return '';
	}
	$res = '';
	$i = 0;
	// Remove blank entries
	while ($i + 1 < count($mp))
	{
		if (trim($mp[$i])=='')
			$mp = array_splice($mp,$i,1);
		else
			$i = $i + 1;
	}
	$N = count($mp);
	if ($N<1)
		return '';
	$i = 0;
	while ($i < $N)
	{
		
		if ($res != '')
			if ($i + 1 == $N)
				$res .= ' and ';
			else
				$res .= ', ';
		$res .= trim($mp[$i]);
		$i = $i + 1;
	}
	return $res;
		
}

function formattedField($fldname,$fldval)
{
		if (preg_match("/Date/",$fldname))	{
			return formattedDate($fldval);
		} else if (preg_match("/TotalMiles|TotalKms/",$fldname)) {
			return number_format($fldval,0);
		} else if (preg_match("/MaxHours/",$fldname)) {
			return spellNumber($fldval);
		} else if (preg_match("/MidPoints/",$fldname)) {
			return formattedMidPoints($fldval);
		} else
			return $fldval;
}

function replaceHeaderBadge($txt,$badge)
{
	// This replaces the static image in the template with the image
	// taken from the ridetype specification

	global $TEMPLATE_IMAGES_PATH;
	
	return preg_replace("#id=\"header_badge\"\s+src=\"([^\"]+)#","id=\"header_badge\" src=\"".$TEMPLATE_IMAGES_PATH.$badge,$txt);
}
	
function fetchCertTextFromRidenames($ridename)
{
	global $TEMPLATE_TEXT_PATH;
	
	$SQL = "SELECT TemplateID FROM ridenames WHERE IBA_Ride='".$ridename."'";
	$rs = sql_query($SQL);
	$rd = $rs->fetchArray();
	return fetchCertTextFromDisk($TEMPLATE_TEXT_PATH.$rd['TemplateID']);
}

function fetchCertTextFromDisk($fpath)
{
	try {
		$res = file_get_contents($fpath);
	} catch (Exception $e) {
		$res = FALSE;
	}
	if ($res==FALSE)
		$res = "<p style=\"font-size:2em;font-weight:bold;\">ERROR - can't find that template #".$fpath."</p>";
	return $res;
}


function rideCertificateText($URI)
{
	global $TEMPLATE_TEXT_PATH;
	
	$Miles2Kilometres = 1.60934;

	$SQL = "SELECT CertText FROM certificates WHERE URI=".$URI;
	$rs = sql_query($SQL);
	$rd = $rs->fetchArray();
	if ($rd != FALSE)
	{
		$res = $rd['CertText'];
		return $res;
	}

	// No saved certificate exists so make one up
	$SQL = "SELECT *,IfNull(ridenames.IBA_Ride_Title,ridenames.IBA_Ride) AS IBA_Ride_Title,IBA_Ride_Desc,Round(TotalMiles*".$Miles2Kilometres.",0) AS TotalKms,TemplateID,HdrImg FROM rides LEFT JOIN ridenames ON rides.IBA_Ride=ridenames.IBA_Ride LEFT JOIN riders ON rides.riderid=riders.riderid LEFT JOIN bikes ON rides.bikeid=bikes.bikeid WHERE URI=".$URI;
	
	$rs = sql_query($SQL);
	$rd = $rs->fetchArray();
	if ($rd == FALSE)
	{
		echo("<p style=\"font-size:2em;font-weight:bold;\">ERROR - can't find that URI #".$URI."</p>");
		exit;
	}

	//var_dump($rd);
	
	// Load from template
	if (!is_null($rd['TemplateID']))
		$res = fetchCertTextFromDisk($TEMPLATE_TEXT_PATH.$rd['TemplateID']);
	else
		$res = FALSE;
	
	
	if ($res == FALSE)
		$res = fetchCertTextFromDisk($TEMPLATE_TEXT_PATH."default.html");

	if (!is_null($rd['HdrImg']))
		$res = replaceHeaderBadge($res,$rd['HdrImg']);
	preg_match_all("/(#[\\w]+#)/",$res,$mt,PREG_SET_ORDER);
	//var_dump($mt);
	foreach ($mt as $fld)
	{
		$fldname = substr($fld[0],1,strlen($fld[0])-2);
		$res = str_replace($fld,formattedField($fldname,$rd[$fldname]),$res);
	}

	$imgFile = $_REQUEST['imgFile'];
	$img = $_REQUEST['img'];
	if ($imgFile != '')
		$res = str_replace($imgFile,$img,$res);

	return $res;
}

function rideCertificate($URI)
{

	$MyURI = $_REQUEST['uri'];
	if ($MyURI=='')
		$MyURI = $URI;
	
	echo (rideCertificateText($MyURI));
}

function saveCertificate()
{
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	
	//if (!$OK) safe_default_action();

	//echo("<br /><br /><br />saveCertificate<hr />");
	//var_dump($_REQUEST);
	
	$page_detail = $_REQUEST['certtext'];
	$uri = $_REQUEST['URI'];

	$sql = "SELECT CertificateID FROM certificates WHERE URI=".$uri;
	$certid = getValueFromDB($sql,"CertificateID",0);

	if ($certid < 1) {
		$certid = intval(getValueFromDB("SELECT MAX(CertificateID) AS rex FROM certificates","rex",0)) + 1;
		$SQL = "INSERT INTO certificates (CertificateID,URI,CertText) VALUES($certid,$uri,'".safesql($page_detail)."')";
	} else {
		$SQL = "UPDATE CertText='".safesql($page_detail)."',Deleted='N' WHERE CertificateID=".$certid;
	}
	sql_query($SQL);
	echo('{"ok":true,"msg":"hello sailor"}');
	
}

?>
