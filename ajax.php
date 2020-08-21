<?php

/*
 * I B A U K - ajax.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */

session_start();

require_once("db.conf.php");

function setRiderTag() {

    $setTag = isset($_REQUEST['tag']) && $_REQUEST['tag']==1;
    $riderid = $_REQUEST['riderid'];
    $riderkey = "riderids['".$riderid."']";
    if ($setTag) {
        $_SESSION[$riderkey] = $riderkey;
    } else {
        if (isset($_SESSION[$riderkey]))
            unset($_SESSION[$riderkey]);
    }
}

if (isset($_REQUEST['c'])) {
    switch ($_REQUEST['c']) {
        case 'setRiderTag':
            setRiderTag();
            break;

    }
}
?>

