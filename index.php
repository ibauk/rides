<?php

/*
 * I B A U K - index.php
 *
 * Copyright (c) 2016 Bob Stammers
 *
 * 2017-01 melines = publicme.php
 */

session_start();

require_once("general.conf.php");
require_once("db.conf.php");
require_once("persist.php");

$cmd = strtok($_REQUEST['cmd']," ");
if ($cmd=="") $cmd = strtok($_REQUEST['c']," ");
//var_dump($_REQUEST);

if (!isset($_SESSION['GUEST_ACCESSLEVEL']))
	setGuestAccess();

	
switch (strtoupper($cmd))
{
	case strtoupper($CMDWORDS['savesettings']):
		if (savesettings()) exit;
		$cmd = '';
		break;
	case 'UPDATE':
	case 'UP':
	case 'LOGOUT':
	case 'LO':
        include("login.php");
		$cmd = '';
		break;
	case 'SEARCH':
	case 'SE':
        include("search.php");
		exit;
	case 'ABOUT':
	case 'AB':
		include("controlp.php");
		exit;
	case 'DBCHECK':
        include("dbcheck.php");
		checkDatabase();
		exit;
	case 'FRIDERS':
		include("dbcheck.php");
		showFilteredRiders();
		exit;
	case 'UNTAG':
	case 'TAG':
		include("riders.php");
		flip_rider_record_tag();
		exit;
	case 'DBEXPORT':
		include("dbexport.php");
		exit;
	case 'BIKESMM':
		include('bikes.php');
		show_bikes_listing();
		exit;
	case 'BIKESMAKE':
		include('bikes.php');
		show_make_listing();
		exit;
	case 'CSV';
		include("dbdump.php");
		dump_csv();
		exit;
	case strtoupper($CMDWORDS['marksent']):
		include('ridelists.php');
		mark_sent_to_USA();
		exit;
	case strtoupper($CMDWORDS['marklapsed']):
		include('riders.php');
		mark_as_lapsed();
		exit;
	case strtoupper('startride'):
		include("ridestart.php");
		if (showRiderlist() == TRUE)
			exit;
		$cmd = '';
		break;
	case strtoupper($CMDWORDS['listrpt']):
		include('ridelists.php');
		show_listreport();
		exit;
	case strtoupper($CMDWORDS['rides']):
		include("ridelists.php");
		show_full_rides_listing();
		exit;
	case strtoupper('rohlines'):
		include("publicroh.php");
		exit;
	case strtoupper('melines'):
		include("publicme.php");
		exit;
	case strtoupper('rrlines'):
		include("publicrr.php");
		exit;
	case strtoupper('ridecert'):
		include("certificate.php");
		rideCertificate(1165);
		exit;
	case strtoupper('savecert'):
		include("certificate.php");
		saveCertificate();
		exit;
	case strtoupper($CMDWORDS['showroh']):
		include("ridelists.php");
		show_roll_of_honour();
		exit;
	case strtoupper($CMDWORDS['showride']):
		include("riderecs.php");
		show_ride_details();
		exit;
	case strtoupper($CMDWORDS['startride']):
		include("riderecs.php");
		showNewRide();
		exit;
	case strtoupper($CMDWORDS['putride']):
		include("riderecs.php");
		putRide();
		exit;
	case strtoupper($CMDWORDS['newride']):
		include("riderecs.php");
		showNewRide();
		exit;
	case strtoupper($CMDWORDS['ridenames']):
		include("ridenames.php");
		show_ridenames_listing();
		exit;
	case strtoupper($CMDWORDS['editridetype']):
		include("ridenames.php");
		edit_ridetype();
		exit;
	case strtoupper($CMDWORDS['putridetype']):
		include("ridenames.php");
		update_ridetype();
		exit;
	case strtoupper($CMDWORDS['riders']):
		include("riders.php");
		show_riders_listing();
		exit;
	case strtoupper($CMDWORDS['rallies']):
		include("rallies.php");
		show_rallies_listing();
		exit;
	case 'UPDATERIDERRECORD':
	case strtoupper($CMDWORDS['showrider']):
		include("riders.php");
		show_rider_details();
		exit;
	case 'SNR':
	case strtoupper($CMDWORDS['shownewrider']):
		include("riders.php");
		showNewRider();
		exit;
	case strtoupper($CMDWORDS['putrider']):
		include("riders.php");
		put_rider();
		exit;
	case strtoupper($CMDWORDS['putridename']):
		include("ridenames.php");
		update_ridename();
		exit;
	case 'MERGERIDERS':
		include('dbcheck.php');
		mergeRidersList();
		exit;
	case 'MERGEBIKES':
		include("dbcheck.php");
		mergeBikes();
		exit;
	case 'USERS':
	case 'US':
        include("users.php");
		$cmd = '';
        break;
	case 'RBLR2017':
		include("rblr1000.php");
		break;
	case 'IMPORTXLS':
		include('importxls.php');
		exit;
	case 'LOADIMPORTS':
		include('loadimports.php');
		update_imports();
		exit;
	case 'STARTIMPORT':
		include('manageimports.php');
		exit;
    default:
		break;
}

start_html(application_title());
if ($cmd <> '') {
	var_dump($_REQUEST);
	echo("<p class=\"errormsg\">What on earth does \"<span class=\"errordata\">$cmd</span>\" mean?</p>");
}

$STARTED = 1;

include("ridelists.php");
show_roll_of_honour();

?>
