<?php

require_once("db.conf.php");

function respondWith($iba,$sname,$email) {

    $json = new StdClass();
    $json->iba = $iba;
    $json->sname = $sname;
    $json->email = $email;

    echo(json_encode($json));
    exit(0);
}

function lookupIBA($first, $last) {

    $fname = $first . ' ' . $last;
	$sqlx = "SELECT IfNull(IBA_Number,'') As IBA_Number, IfNull(Email,'') As Email FROM riders WHERE Rider_Name LIKE '" . $fname . "' AND IBA_Number <>'' COLLATE NOCASE";
	$r = sql_query($sqlx);
	if ($rd = $r->fetchArray()) {
        respondWith($rd['IBA_Number'],$last,$rd['Email']);
        return;
	}
	respondWith("",$last,"");
}

function lookupIBAMember($iba) {

	$sqlx = "SELECT IfNull(Rider_Name,'') As Rider_Name, IfNull(Email,'') As Email FROM riders WHERE IBA_Number = '" . $iba . "'";
	$r = sql_query($sqlx);
	if ($rd = $r->fetchArray()) {
        $nx = explode(" ",$rd['Rider_Name']);
        $ni = count($nx) - 1;
        respondWith($iba,$nx[$ni],$rd['Email']);
        return;
	}
	respondWith($iba,"","");
}

if (isset($_REQUEST['f']) && isset($_REQUEST['l'])) {
    lookupIBA($_REQUEST['f'],$_REQUEST['l']);
} else if (isset($_REQUEST['i'])) {
    lookupIBAMember($_REQUEST['i']);
}

?>
