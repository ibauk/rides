<?php

/*
 * includespec.php
 *
 */

$includespec = "rblr";

if (isset($_REQUEST['spec']))
	$includespec = $_REQUEST['spec'];


// External dataset definition
$includespec = "./$includespec"."spec.php";
require_once "$includespec";

if (isset($_REQUEST['file']))
	$IMPORTSPEC['xlsname'] = $_REQUEST['file'];

if (isset($_REQUEST['eventid']))
	$IMPORTSPEC['eventid'] = $_REQUEST['eventid'];

if (isset($_REQUEST['ridedate']))
	$IMPORTSPEC['ridedate'] = $_REQUEST['ridedate'];

?>
