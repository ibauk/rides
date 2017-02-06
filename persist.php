<?php

/*
 * I B A U K - persist.php
 *
 * Copyright (c) 2016 Bob Stammers
 *
 */

function isLoggedin()
{
	global $SALT;
	
	$DEBUG = FALSE;
	
	if (isset($_COOKIE['authentication'])) {
		// cookie is set, lets see if its valid and log someone in
		$clean = array();
		$mysql = array();
		$now = time();
		list($identifier, $token) = explode(':', $_COOKIE['authentication']);
		if ($DEBUG) echo("[P] Cookie found ($identifier,$token) ... ");
		if (ctype_alnum($identifier) && ctype_alnum($token)) {
			$clean['identifier'] = $identifier;
			$clean['key'] = $token;

			$mysql['identifier'] = safesql($clean['identifier']);
			if ($DEBUG) echo(" More checking ".$mysql['identifier']." ... ");
			$result = sql_query("SELECT * FROM users  WHERE cookieid = '{$mysql['identifier']}'");
			if (mysqli_num_rows($result)) {
				$record = mysqli_fetch_assoc($result);
				if ($clean['key'] != $record['cookiekey']) {
					// fail because the key doesn't match
					if ($DEBUG) echo(" Bad key '".$clean['key']."' -- '".record['cookiekey']."' ");
				}elseif ($now > $record['cookietimeout']){
					// fail because the cookie has expired
					if ($DEBUG) echo(" Now=$now; then=".$record['cookietimeout']." ");
				}elseif ($clean['identifier'] != md5($SALT.$record['userid'])){
					// fail because the identifiers does not match
					if ($DEBUG) echo(" IDs don't match '".$record['userid']."'; [".$SALT."]=".md5($SALT.$record['userid'])."==".md5($SALT.$record['userid']));
				}else{
					/*
                          Success everything matches, now you can process
                          your login functions. The key must be re generated
                          for the next login. But don't increase the timeout to
                          ensure that the user must login in once the time
                          period has passed.
                       */
					// right, we're happy with the login now so
	
					if ($DEBUG) echo(" ok ");
					$_SESSION['ACCESSLEVEL'] = $record['accesslevel'];
					$_SESSION['USERNAME'] = $record['userid'];
					$_SESSION['UPDATING'] = $record['accesslevel'] >= $GLOBALS['ACCESSLEVEL_UPDATE'];
	
				}
			}
		}else {
			if ($DEBUG) echo(" info format bad ");
			/* failed because the information is not in the
                            correct format in the cookie */
		}
	
	} else
			if ($DEBUG) echo("[P] no cookie found");

}

isLoggedin();

?>