<?php

// RBLR finisher import spec; matched to output from ScoreMaster

$IMPORTSPEC['ridedate']		= '2018-06-16';
$IMPORTSPEC['eventid']		= "RBLR 1000 ('18)";	
$IMPORTSPEC['xlsname']		= "rblr2018.xlsx";
$IMPORTSPEC['whichsheet']	= 0;

$IMPORTSPEC['ride_rally']	= 0;
$IMPORTSPEC['ibaride']		= 'SS1000';
$IMPORTSPEC['rideverifier']	= 'RBLR';
$IMPORTSPEC['paymethod']	= 'PayPal';

$IMPORTSPEC['routes']['2']['description']	= 'North clockwise';
$IMPORTSPEC['routes']['2']['startpoint']		= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['2']['viapoints']		= 'Fort William, Wick, Berwick';
$IMPORTSPEC['routes']['2']['finishpoint']	= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['2']['miles']			= 1006;
$IMPORTSPEC['routes']['2']['ibaride']		= "RBLR1000-NC";

$IMPORTSPEC['routes']['1']['description']	= 'North anticlockwise';
$IMPORTSPEC['routes']['1']['startpoint']		= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['1']['viapoints']		= 'Berwick, Wick, Fort William';
$IMPORTSPEC['routes']['1']['finishpoint']	= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['1']['miles']			= 1006;
$IMPORTSPEC['routes']['1']['ibaride']		= "RBLR1000-NA";

$IMPORTSPEC['routes']['4']['description']	= 'South clockwise';
$IMPORTSPEC['routes']['4']['startpoint']		= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['4']['viapoints']		= 'Lowestoft, Brighton, Bangor';
$IMPORTSPEC['routes']['4']['finishpoint']	= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['4']['miles']			= 1004;
$IMPORTSPEC['routes']['4']['ibaride']		= "RBLR1000-SC";

$IMPORTSPEC['routes']['3']['description']	= 'South anticlockwise';
$IMPORTSPEC['routes']['3']['startpoint']		= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['3']['viapoints']		= 'Bangor, Brighton, Lowestoft';
$IMPORTSPEC['routes']['3']['finishpoint']	= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['3']['miles']			= 1004;
$IMPORTSPEC['routes']['3']['ibaride']		= "RBLR1000-SA";

$IMPORTSPEC['routes']['5']['description']	= 'BunBurner Gold';
$IMPORTSPEC['routes']['5']['startpoint']		= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['5']['viapoints']		= 'Folkestone, Swansea, Perth';
$IMPORTSPEC['routes']['5']['finishpoint']	= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['5']['miles']			= 1527;
$IMPORTSPEC['routes']['5']['ibaride']		= "RBLR1000-BBG";

$IMPORTSPEC['routes']['12']['description']	= 'BunBurner';
$IMPORTSPEC['routes']['12']['startpoint']		= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['12']['viapoints']		= 'Folkestone, Swansea, Perth';
$IMPORTSPEC['routes']['12']['finishpoint']	= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['12']['miles']			= 1527;
$IMPORTSPEC['routes']['12']['ibaride']		= "BB1500";

$IMPORTSPEC['routes']['13']['description']	= 'SaddleSore';
$IMPORTSPEC['routes']['13']['startpoint']		= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['13']['viapoints']		= 'Folkestone, Swansea, Bodmin';
$IMPORTSPEC['routes']['13']['finishpoint']	= 'Squires cafe, Sherburn-in-Elmet';
$IMPORTSPEC['routes']['13']['miles']			= 1004;
$IMPORTSPEC['routes']['13']['ibaride']		= "SS1000";


// Spreadsheet column mapping. Leftmost is column 1
//$IMPORTSPEC['cols']['ridestars'] = 1;
$IMPORTSPEC['cols']['ridername'] = 1;
$IMPORTSPEC['cols']['pillionname'] = 2;
$IMPORTSPEC['cols']['bike'] = 3;
$IMPORTSPEC['cols']['miles'] = 5;
$IMPORTSPEC['cols']['route'] = 10;
$IMPORTSPEC['cols']['email'] = 11;
$IMPORTSPEC['cols']['address'] = 12;
$IMPORTSPEC['cols']['postcode'] = 13;
$IMPORTSPEC['cols']['phone'] = 15;
$IMPORTSPEC['cols']['mobile'] = 16;

//$IMPORTSPEC['cols']['routes']['1001'] = 7;
//$IMPORTSPEC['cols']['routes']['1002'] = 8;
//$IMPORTSPEC['cols']['routes']['1003'] = 9;
//$IMPORTSPEC['cols']['routes']['1004'] = 10;

 
?>
