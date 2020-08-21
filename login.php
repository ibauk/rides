<?php
/*
 * I B A U K - login.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */

require_once "general.conf.php";
require 'PasswordHash.php';
 

function login_failed()
{
    start_html("Login failed");
    echo("<p class=\"errormsg\">Your attempt at authentication failed</p>");
    exit();
}

function fail($pub, $pvt = '')
{
	$msg = $pub;
	if ($pvt !== '')
		$msg .= ": $pvt";
	exit("An error occurred ($msg).\n");
}

function login_user($u,$p)
{

	global $db_ibauk_conn;
	global $HASH_COST_LOG2, $HASH_PORTABLE, $SALT;
	
	$hash = '*'; // In case the user is not found
	$al = 0;  // accesslevel
	$cookieid = '';
	
	//var_dump($db_ibauk_conn);
	error_log("Fetching details for ".$u);
	try {
		$r = $db_ibauk_conn->query("SELECT userpass,accesslevel,cookieid FROM users WHERE userid='".$db_ibauk_conn->escapeString($u)."'");
	} catch(exception $e) {
		fail("DB query",$e->getMessage());
	}
	error_log("Extracting details for $u");
	
	if (!$rd = $r->fetchArray()) 
		fail('DB fetch FAILED!');
	error_log("Setting up hasher");
	$hash = $rd['userpass'];
	$al = $rd['accesslevel'];
	
	$hasher = new PasswordHash($HASH_COST_LOG2, $HASH_PORTABLE);
	if ($hasher->CheckPassword($p, $hash)) {
		$what = 'Authentication succeeded';
	} else {
		$what = 'Authentication failed';
		login_failed();
	}
	unset($hasher);
	
	// right, we're happy with the login now so
	
    $_SESSION['ACCESSLEVEL'] = $al;
    $_SESSION['USERNAME'] = $u;
    $_SESSION['UPDATING'] = $_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE'];
	
	$cookieid = md5($SALT.$u);
	if (isset($_POST['persist']))
	{
		$key = md5(uniqid(rand(), true));
		// calculate the time in 7 days ahead for expiry date
		$timeout = time() + (60 * 60 * 24 * $_POST['persist']);

		// Set the cookie with information
		setcookie('authentication', "$cookieid:$key", $timeout);
	}
	else
	{
		unset($_COOKIE['authentication']);
		$timeout = time() - 3600;
		setcookie('authentication','',$timeout);
		$key = '';
	}
	
	// now update the database with the new information
	$sql = "UPDATE users SET cookieid='$cookieid',cookiekey = '$key', cookietimeout=$timeout, lastlogin=Now()	WHERE userid = '$u'";
	sql_query($sql,TRUE);
	//echo("$sql<hr />");
	
}

function logout_user()
{
	
	if (isset($_COOKIE['authentication']))
	{
		unset($_COOKIE['authentication']);
		setcookie('authentication','',time() - 3600);
	}
	
	session_unset();
	session_destroy();
	session_start();
	setGuestAccess();
    $USERID = '';
	//echo("Logged out ok<hr />");
	
}

//start_html("");
//echo("<p>[login] U=$USERID, P=$USERPASS, A=".$_SESSION['ACCESSLEVEL']."</p>");
if ($_POST['userid'] <> '')
    login_user($_POST['userid'],$_POST['userpass']);

$cmd = strtok($_POST['cmd']," ");
if ($cmd=="") $cmd = strtok($_GET['cmd']," ");
if ($cmd=="") $cmd = strtok($_GET['c']," ");

if ($cmd == "logout" || $cmd == "lo")
{
	logout_user();
	return;
}


if ($_SESSION['USERNAME'] != "") return;

start_html("Authentication required");

?>
<div style="text-align: center;">
<h2>Authentication Required</h2>
<form action="index.php" method="post">
<input type="hidden" name="cmd" value="update">
<table border="1" summary="Login details" style="margin-left: auto; margin-right: auto;">
<tr>
    <td>Userid</td>
    <td><input type="text" name="userid" autofocus></td>
</tr>
<tr>
    <td>Password</td>
    <td><input type="password" name="userpass">
</tr>
<tr><td colspan="2">Stay logged in (not on public terminal) <input type="checkbox" name="persist" value="7" checked></td></tr>
<tr>
    <td colspan="2" style="text-align: center"><input type="submit" value="Authenticate"></td>
</tr>
</form>
</div>
</body>
</html>
<?php
exit();
?>
