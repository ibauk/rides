<?php

/*
 * I B A U K - general.conf.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2025 Bob Stammers
 *
 */

error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_PARSE);

require_once("serverstatus.php");
$APPLICATION_TITLE = "IBAUK Rides Database ($serverstatus)";
$PUBLIC_TITLE = "IBAUK Roll of Honour";

$APPLICATION_VERSION = "2.15";
// 2.0  01SEP16 Initial PHP release
// 2.1	20OCT16	Live release
// 2.2	04DEC16 Highlighting non-UK rides; defaults for non-UK rides; extra tooltips
// 2.3	18DEC16 New Ride enhancements + Mike's buglist + history=text(5000)
// 2.4	04JAN17 Valid rides omitted from RoH, Undelete ride rec, Cache updating
// 2.5	06FEB17 Certificates, RideType enforcement
// 2.6	18MAR17 Fixed password change code; rrlines - rally results feed
// 2.7	12JUN17	Added new rally entry code
// 2.8	23JUL17	Bulk data imports
// 2.9	13SEP17 Download CSV on searches + rider reports
// 2.10	14JUN18	Current/Lapsed member status + RBLR imports
// 2.11 21AUG20 Recode for SQLite, Code overhaul
// 2.12	28SEP20	Refactor import routines
// 2.13 25SEP21 Fix date width, suppress debug logging, TrackURL
// 2.14 30OCT23 Event list maintenance
// 2.15 22MAR25 Variety of aesthetic and functional enhancements

$APPLICATION_COPYRIGHT = "Copyright &copy; 2025 Bob Stammers on behalf of Iron Butt UK";



// Accesslevels are both discrete and hierarchical by numeric value
$ACCESSLEVEL_NOACCESS = 0;
$ACCESSLEVEL_PUBLICVIEW = 1;
$ACCESSLEVEL_READONLY = 2;
$ACCESSLEVEL_UPDATE = 3;
$ACCESSLEVEL_SUPER = 9;

$ACCESSLEVELS = Array($ACCESSLEVEL_SUPER=>'Controller', $ACCESSLEVEL_UPDATE=>'Update database', $ACCESSLEVEL_READONLY=>'View database', $ACCESSLEVEL_PUBLICVIEW=>'Public access', $ACCESSLEVEL_NOACCESS=>'no access');


// For use with PHPass
// Base-2 logarithm of the iteration count used for password stretching
$HASH_COST_LOG2 = 8;
// Do we require the hashes to be portable to older systems (less secure)?
$HASH_PORTABLE = FALSE;
// used for persistence cookie
$SALT = 'TheQuick32';


$HOME_COUNTRY = 'UK';


// Used to page through lists
$OFFSET = 0;
$PAGESIZE = 60;
$SHOWALL = FALSE;

$KEY_FIND = '';
$KEY_ORDER = '';
$KEY_DESC = '';

$ISOMONTHS = Array('01'=>'January', '02'=>'February', '03'=>'March',
                    '04'=>'April', '05'=>'May', '06'=>'June',
                    '07'=>'July','08'=>'August','09'=>'September',
                    '10'=>'October','11'=>'November','12'=>'December');

$MYKEYWORDS = Array('search'        =>'search',
					'ride'			=>'ride',
					'rides'			=>'rides',
					'riders'		=>'riders',
					'rallies'		=>'rallies',
					'ridenames'		=>'ridenames',
					'bikes'			=>'bikes',
					'uri'			=>'URI',
                    'users'         =>'users',
                    'user'          =>'user',
                    'Users'         =>'users',
                    'User'          =>'User',
					'showusa'		=>'O/s USA',
                    'logout'        =>'logout',
                    'update'        =>'login');

// This is not always used but does serve as a quick lookup					
$CMDWORDS	= Array('cmd'			=>'c',
					'update'		=>'up',
					'logout'		=>'lo',
					'search'		=>'se',
					'about'			=>'ab',
					'params'		=>'pa',
					'table'			=>'t',
					'users'			=>'us',
					'rides'			=>'rides',
					'rallies'		=>'rallies',
					'ridenames'		=>'ridenames',
					'putridename'	=>'putrn',
					'editridetype'	=>'ert',
					'putridetype'	=>'prt',
					'showride'		=>'showride',
					'shownewrider'	=>'snr',
					'riders'		=>'riders',
					'showrider'		=>'showrider',
					'marksent'		=>'marksent',
					'marklapsed'	=>'marklapsed',
					'putride'		=>'putride',
					'newride'		=>'newride',
					'putrider'		=>'putrider',
					'startride'		=>'startride',
					'showroh'		=>'showroh',
					'listrpt'		=>'listrpt',
					'uri'			=>'URI',
					'find'			=>'f',
					'offset'		=>'o',
					'order'			=>'order',
					'desc'			=>'desc',
					'show'			=>'show',
					'all'			=>'all',
					'pagesize'		=>'s',
					'savesettings'	=>'SaveSettings',
					'dbdump'		=>'dbdump',
					'dbexport'		=>'dbexport',
					'dbcheck'		=>'dbcheck');
					
					
$PUBLIC_FIELDS = '';
$GUEST_ACCESSLEVEL = $ACCESSLEVEL_NOACCESS;


if (isset($_GET['o'])) $OFFSET = $_GET['o'];
if (isset($_GET['s'])) $PAGESIZE = $_GET['s'];
if (isset($_GET['offset'])) $OFFSET = $_GET['offset'];
if (isset($_GET['pagesize'])) $PAGESIZE = $_GET['pagesize'];
if (isset($_GET['f'])) $KEY_FIND = $_GET['f'];
if (isset($_GET['find'])) $KEY_FIND = $_GET['find'];
if (isset($_GET['order'])) $KEY_ORDER = $_GET['order'];
if (isset($_GET['desc'])) $KEY_DESC = $_GET['desc'];
if (isset($_GET['all'])) $SHOWALL = TRUE;

function colFmtDate($dt)
{
	return str_replace(' ','&nbsp;',date('d M Y',strtotime($dt)));
}
					
function sql_order()
{
	global $KEY_ORDER, $KEY_DESC, $PAGESIZE, $OFFSET, $SHOWALL;
		
	$SQL = '';
	if ($KEY_ORDER != '')
	{
		$SQL .= ' ORDER BY '.$KEY_ORDER;
		if ($KEY_ORDER == $KEY_DESC)
			$SQL .= ' DESC';
	}


	/* Not applicable with SQLite */
	if (false) 	if (!$SHOWALL && $PAGESIZE > 0)
		$SQL .= " LIMIT $OFFSET, $PAGESIZE";
	return $SQL;	
}

function Checkbox_isChecked($yn)
{
	if ($yn == "Y")
		{return " checked ";}
	else
		{return "";}
}
function Checkbox_isNotChecked($yn)
{
	if ($yn != "Y")
		{return " checked ";}
	else
		{return "";}
}

function OptionSelected($val,$opt)
{
	if ($val==$opt)
		return " selected ";
	else
		return "";
}

function setGuestAccess()
{
	$SQL = "SELECT guestaccess,publicfields FROM sysvars WHERE recid=1";
	$r = sql_query($SQL);
	$rr = query_results($r);
	//var_dump($_SESSION);
	//echo('<hr />');
	$_SESSION['GUEST_ACCESSLEVEL'] =$rr ['guestaccess'];
	$_SESSION['PUBLIC_FIELDS'] = $rr['publicfields'];
	if (!isset($_SESSION['ACCESSLEVEL']))
		$_SESSION['ACCESSLEVEL'] = $_SESSION['GUEST_ACCESSLEVEL'];
	
}

function prevent_cache()
{
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                      // always modified
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0
}

function show_infoline($info,$class)
{
	echo("<p");
	if ($class <> '')
		echo(" class=\"".$class."\"");
	echo(">".$info."</p>");
}

function start_html($title)
{
    global $MYKEYWORDS, $APPLICATION_TITLE, $APPLICATION_VERSION, $CMDWORDS;

    if (headers_sent()) return;

    prevent_cache();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
echo("<title>$title</title>\n");
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" type="text/css" href="reboot.css?ver=<?= filemtime('reboot.css')?>">
<link href="ibauk.css?v=<?= filemtime('ibauk.css')?>" rel="stylesheet" />
<script src="ibauk.js?v=2"></script>
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<link rel="stylesheet" href="menustyles.css">
<script src="menuscript.js"></script>

</head>
<body onload="bodyLoaded()">
<img alt="*" src="ibauklogo.png" style="float:right;" />

<?php
if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'] ) 
	echo("<form action=\"index.php\" method=\"get\">");
echo("<h1>");
echo("<a title=\"v$APPLICATION_VERSION\" href=\"index.php\">".application_title()."</a>");
    if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'] ) {
?>
<br>
<input type="hidden" name="c" value="se">
<input type="text" name="f" placeholder="Rider name,IBA#,etc" style="display:inline; font-size: .6em;" >
<input type="submit" value="Search" title="This will search the database and return a list of riders matching the key entered" style="font-size:.5em; display:inline;">

<?php
	}
	else
		echo("<br /><br />"); // Keep the spacing good
echo("</h1>	");
if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'] ) 
	echo("</form>");
?>
<nav id="cssmenu" class="topmenu">
<ul>
<?php
//var_dump($_SESSION); echo('<hr />');
    if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'])
	{
        echo("<li><a href=\"#\" accesskey=\"r\"  >".$MYKEYWORDS['rides']."</a>");
		echo("<ul id=\"ridesmenu\" class=\"dropdown\">");
        echo("<li><a href=\"index.php?c=".$CMDWORDS['rides']."\">Show all rides</a></li>");
        echo("<li><a href=\"index.php?c=".$CMDWORDS['showroh']."\">Roll of Honour</a></li>");

		$sqlx = "SELECT rptid,ReportTitle FROM listreports";
		$sqlx .= " WHERE AccessLevel <= ".$_SESSION['ACCESSLEVEL'];
		$sqlx .= " ORDER BY rptid";
		$rs = sql_query($sqlx);
		while ($rd = $rs->fetchArray()) {
			echo('<li><a href="index.php?c='.$CMDWORDS['listrpt'].'&amp;rptid='.$rd['rptid'].'">'.$rd['ReportTitle'].'</a></li>');
		}
		/*
		echo("<li><a href=\"index.php?c=".$CMDWORDS['listrpt']."&amp;rptid=usaos\">Report to USA</a></li>"); 
		echo("<li><a href=\"index.php?c=".$CMDWORDS['listrpt']."&amp;rptid=pymtsos\">Waiting for payment</a></li>"); 
		echo("<li><a href=\"index.php?c=".$CMDWORDS['listrpt']."&amp;rptid=unverified\">Not yet verified</a></li>"); 
		echo("<li><a href=\"index.php?c=".$CMDWORDS['listrpt']."&amp;rptid=foreign\">Rides validated elsewhere</a></li>"); 
		echo("<li><a href=\"index.php?c=".$CMDWORDS['listrpt']."&amp;rptid=notroh\">Rides omitted from RoH</a></li>"); 
		*/
		echo("</ul>");
		echo("</li> ");
	}
    if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'])
	{
        echo("<li><a href=\"#\" accesskey=\"i\" >".$MYKEYWORDS['riders']."</a> ");
		echo("<ul id=\"ridersmenu\" class=\"dropdown\">");
		echo("<li><a href=\"index.php?c=".$CMDWORDS['riders']."\" >All Riders &amp; Pillions</a></li> ");
		echo("<li><a href=\"index.php?c=".$CMDWORDS['riders']."&amp;ShowPillions=only\" >Pillions only</a></li> ");
		echo("<li><a href=\"index.php?c=".$CMDWORDS['riders']."&amp;MileEaters=show\" >Mile Eaters</a></li> ");
		echo("<li><a href=\"index.php?c=".$CMDWORDS['riders']."&amp;NonUK=only\" >Non-UK Riders/Pillions</a></li> ");
		echo("<li><a href=\"index.php?c=".$CMDWORDS['riders']."&amp;oldnew=Inactive\" >Inactive members</a></li> ");
		echo("<li><a href=\"index.php?c=".$CMDWORDS['riders']."&amp;oldnew=Active\" >Active members</a></li> ");
		echo("<li><a href=\"index.php?c=dbcheck\" >Possible duplicates</a></li> ");
		echo("<li><a href=\"index.php?c=friders\" >Tagged records</a></li> ");
		echo("</ul>");
		echo("</li>");
	}
    if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'])
	{
        echo("<li><a href=\"#\" accesskey=\"b\" >".$MYKEYWORDS['bikes']."</a> ");
		echo("<ul id=\"bikesmenu\" class=\"dropdown\">");
		echo("<li><a href=\"index.php?c=bikesmm\">Bikes by make &amp; model</a></li>");
		echo("<li><a href=\"index.php?c=bikesmake\">Bikes by manufacturer</a></li>");
		echo("</ul></li>");
	}
    if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'])
	{
        echo("<li><a href=\"#\" >".$MYKEYWORDS['rallies']."</a> ");
		echo("<ul id=\"ralliesmenu\" class=\"dropdown\">");
		echo('<li><a href="index.php?c=ralliestab">Table maintenance</a></li>');
		$rr = sql_query("SELECT * FROM rallies ORDER BY RallyID");
		while(True)
		{
			$rd = $rr->fetchArray();
			if ($rd == FALSE) break;
			echo("<li><a href=\"index.php?c=".$CMDWORDS['rallies']."&amp;id=".$rd['RallyID']."\">".$rd['RallyTitle']."</a></li>");
		}
		echo("</ul></li>");
	}

    if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'])
        echo("<li><a href=\"index.php?c=".$CMDWORDS['ridenames']."\" accesskey=\"r\">".$MYKEYWORDS['ridenames']."</a></li> ");
    
	if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'])
        echo("<li><a href=\"index.php?c=".'events'."\" >".'EVENTS'."</a></li> ");
    
    if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY'])
		echo("<li><a href=\"index.php?c=".$CMDWORDS['about']."\" accesskey=\"a\">Control panel</a></li> ");
	if ($_SESSION['USERNAME'] <> '')
		echo("<li><a href=\"index.php?c=".$CMDWORDS['logout']."\" accesskey=\"l\">".$MYKEYWORDS['logout']." ".$_SESSION['USERNAME']."</a></li> ");
	else
		echo("<li><a href=\"index.php?c=".$CMDWORDS['update']."\" accesskey=\"u\">".$MYKEYWORDS['update']."</a></li> ");
    echo("</ul></nav>\n");
}

function column_anchor($TXT,$FLD)
{
    //global $CMD, $FIND, $ORDER, $DESC;

	$order = '';
	$desc = '';
	$qs = $_SERVER['QUERY_STRING'];
	if (preg_match('/order=(\w+)/i',$qs,$mm))
		$order = $mm[1];
	if (preg_match('/desc=(\w+)/i',$qs,$mm))
		$desc = $mm[1];
	if ($order <> $desc)
		$desc = "&amp;desc=$order";
	else
		$desc = '';
    //if ($_GET['order'] <> $_GET['desc'])
    //  $desc = "&amp;desc=".$_GET['order'];
    //else
    //  $desc = "";

    //$x = $_GET['cmd'];
	//if ($x=='') $x = $_GET['c'];
    //$cmd = "index.php?cmd=".$x."$desc";
    //if ($_GET['find'] <> '') $cmd .= "&amp;find=".$_GET['find'];
    //if ($_GET['f'] <> '') $cmd .= "&amp;f=".$_GET['f'];

	$cmd = $_SERVER['QUERY_STRING'];
	$cmd = preg_replace('/&order=\w+/i','',$cmd);
	$cmd = preg_replace('/&desc=\w+/i','',$cmd);
	
    $res  = "<a href=\"index.php?$cmd&amp;order=$FLD$desc\" title=\"Sort on this column\">";
    $res .= "$TXT</a>";
	
    return $res;
}


function show_date($isodate)
{
    $dt = explode('-',$isodate);
    $mm = Array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
    return $mm[$dt[1]-1]." ".$dt[0];

}


function application_title()
{
	global $APPLICATION_TITLE, $PUBLIC_TITLE;
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if ($OK)
		return $APPLICATION_TITLE;
	else
		return $PUBLIC_TITLE;
}

function show_common_paging($maxrows,$extralink='')
{
    global $OFFSET, $PAGESIZE, $CMD, $MYKEYWORDS;

	$NEXT_PAGE = "\xE2\x8F\xA9";
	$PREV_PAGE = "\xE2\x8F\xAA";
	
	
	$ADJACENTPAGELINKS = 10; // Maximum number of page links to show either side of current page

    if ($PAGESIZE < 1) return;

		
    $np = (int)($maxrows / $PAGESIZE);
    if (($np * $PAGESIZE) < $maxrows) $np++;
    $tp = (int)($OFFSET / $PAGESIZE);
    $tp++;
    if ($np <= 1) return;

	$qs = $_SERVER['QUERY_STRING'];
	$qs = preg_replace('/&offset=\d+/i','',$qs);
	$qs = preg_replace('/&pagesize=\d+/i','',$qs);
	
    echo("<nav class=\"pagelinks\"><ul>");
	
    if ($tp > 1) // Not first page so offer a 'previous' marker
    {
        $tpo = ($tp * $PAGESIZE) - $PAGESIZE - $PAGESIZE;
        echo("<li>[<a href=\"index.php?$qs&amp;offset=$tpo&amp;pagesize=$PAGESIZE\" title='Previous page'>$PREV_PAGE</a>]</li>");
    }

	$pmin = ($tp>$ADJACENTPAGELINKS) ? ($tp-$ADJACENTPAGELINKS) : 1;
	$pmax = ($tp<($np-$ADJACENTPAGELINKS)) ? ($tp+$ADJACENTPAGELINKS) : $np;

    foreach(range(1,$np) as $pn)
    {
        $tpo = ($pn * $PAGESIZE) - $PAGESIZE;
		
		if (($pn==1) || ($pn==$np) || ($pn>=$pmin && $pn<=$pmax))
		{
			if ($pn == $tp)
				echo("<li>[ <strong>$pn</strong> ]</li>");
			else
				echo("<li>[<a href=\"index.php?$qs&amp;offset=$tpo&amp;pagesize=$PAGESIZE\">$pn</a>]</li>");
		}
		elseif (($pn == $tp - ($ADJACENTPAGELINKS+1)) || ($pn == $tp + ($ADJACENTPAGELINKS+1)))
			echo("<li> ... </li>");
    }
    if ($tp < $np) // Not last page so offer a 'Next' marker
    {
        $tpo = ($tp * $PAGESIZE);
        echo("<li>[<a href=\"index.php?$qs&amp;offset=$tpo&amp;pagesize=$PAGESIZE\" title='Next page'>$NEXT_PAGE</a>]</li>");
    }
    echo("<li>[<a href=\"index.php?$qs&amp;show=all\">Show all</a>]</li>");
	if ($extralink != '') echo(" <li>[".$extralink."]</li> ");
    echo("</ul></nav>\n");

}

function savesettings()
{
	foreach ($_REQUEST as $var => $val)
		$_SESSION[$var] = $val;
}


function safe_default_action()
{
	include_once("ridelists.php");
	show_roll_of_honour();
	exit;

}

function touchRider($riderid)
/*
 * This touches the rider record by setting the value of CurrentMember to 'Y'
 * and DateLastActive to today's date.active
 * 
 * This is called whenever some positive action, such as a new ride, is recorded
 * for the specified rider.active
 * 
 */
{
	$sql = "UPDATE riders SET CurrentMember='Y', DateLastActive='".date("Y-m-d")."' WHERE riderid=".$riderid;
	sql_query($sql);
}


function resetBulkimports()
// This will establish an empty BULKIMPORTS table ready for fresh data
{

	// Don't empty it, drop it altogether
	$sql = "DROP TABLE IF EXISTS bulkimports; ";
	//echo('<hr>'.$sql);
	sql_query($sql);

	$sql = "CREATE TABLE `bulkimports` ";
	$sql .= "(`recid` INTEGER NOT NULL , `EventID` TEXT , `rider_name` TEXT , `IBA_number` TEXT , `is_pillion` INTEGER DEFAULT 0 ";
	$sql .= ", `Country` TEXT , `Bike` TEXT , `BikeReg` TEXT , `ridestars` TEXT , `route_number` TEXT DEFAULT '1001' ";
	$sql .= ", `riderid` INTEGER , `bikeid` INTEGER , `points` INTEGER DEFAULT 0 , `miles` INTEGER DEFAULT 0 , `finishposition` INTEGER DEFAULT 0 ";
	$sql .= ", `ride_rally` INTEGER DEFAULT 0 , `ridedate` TEXT , `Email` TEXT , `Postal_Address` TEXT , `Postcode` TEXT , `Phone` TEXT ";
	$sql .= ", Bollox TEXT";
	$sql .= ", `AltPhone` TEXT , PRIMARY KEY (`recid`) )";
	//echo('<hr>'.$sql);
	sql_query($sql);
	
}
?>