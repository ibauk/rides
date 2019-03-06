<?php

/*
 * I B A U K - controlp.php
 *
 * Copyright (c) 2018 Bob Stammers
 *
 */


 // Fail quietly if called directly
 if (!function_exists('start_html')) exit;
 
//function show_about()
//{
    global $db_ibauk, $db_user, $ACCESSLEVELS, $MYKEYWORDS, $db_ibauk_conn, $CMDWORDS;

    start_html("About ".application_title());
    echo("<h2>".application_title()." version ".$GLOBALS['APPLICATION_VERSION']."</h2>\n");
	echo("<p class=\"copyrite\">".$GLOBALS['APPLICATION_COPYRIGHT']."<br /><br /></p>");

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK)
	{
		echo("</body></html>\n");
		return;
	}
	
    $SQL  = " SELECT timestamp, DATE_FORMAT(timestamp,'%W, %D %M %Y at %l:%i%p') AS timestamp1,userid FROM history ORDER BY timestamp DESC LIMIT 0,1";
    $r = sql_query($SQL);
    $rr = mysqli_fetch_assoc($r);
    if ($rr <> FALSE)
        echo("<p>The database was last updated ".$rr['timestamp1']." by '".$rr['userid']."'</p>");
		
	if ($_SESSION['USERNAME'] != "")
		echo("<p>You are logged in as ".strtoupper($_SESSION['USERNAME'])." with access level ".strtoupper($ACCESSLEVELS[$_SESSION['ACCESSLEVEL']])."</p>");
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);

?>
<div id="tabs_area" style="display:inherit"><ul id="tabs">
<li><a href="#tab_userapp">About</a></li>
<li><a href="#tab_environment">Host environment</a></li>
<?php if ($OK) {?>
	<li><a href="#tab_database">Database updates</a></li>
	<li><a href="#tab_settings">Settings</a></li>
<?php }
	if ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_SUPER'])
		echo('<li><a href="#tab_users">Users</a></li>');
	  else 
		  echo('<li><a href="#tab_users">Change password</a></li>');
	?>
</ul></div>
<div class="tabContent" id="tab_userapp">
<p>This application maintains records of riders &amp; pillions and their bikes as well as certified rides, rally finishers and Mile Eaters on behalf of IBA UK.</p>
<p>Guest access to the Roll of Honour containing published details of certified rides only is available without logging in as a registered user. </p>
<p>Records are never physically deleted from the database but are instead flagged as being deleted. Such records are excluded from all normal lists and counts unless the appropriate control panel option is checked.</p>
<p>Dates are stored explicitly as dates, not text fields. They are shown and manipulated in the user interface according to the whims of your device/browser/locale. In your case, the date <strong>Wednesday 3rd August 2016</strong> appears as <input type="date" value="2016-08-03"></p>
<p>[<a href="https://docs.google.com/document/d/1nBYhetAuxPZm5rJ5wWLR9SwHBRE6ot1HaaV2liTRsHU/edit?usp=sharing" target="docwin">User Guide</a>]  [<a href="https://docs.google.com/document/d/1rjcKdSpQGYNaDt6oYlRo-QmxswAHYhwY74NQ1pXHE-s/edit?usp=sharing" target="docwin">Database specifications</a>]</p>
</div>
<div class="tabContent" id="tab_environment">
<?php	
	$servername = php_uname('n');
	$serveraddr = $_SERVER['SERVER_ADDR'];
	if ($serveraddr=='')
		$serveraddr = $_SERVER['LOCAL_ADDR'];
	//var_dump($_SERVER);
	$mysqlname = strtok(mysqli_get_host_info($db_ibauk_conn)," ");
	$mysqladdr = gethostbyname($mysqlname);
	if (strtoupper($mysqlname)=='LOCALHOST' and $serveraddr=='127.0.0.1')
		$mysqlname = $servername;

	echo("<p>This is a PHP/MySQL application running on a computer called <strong>".$servername." [".$serveraddr."]</strong> ");
	echo("It's installed in the folder <strong>".$_SERVER['DOCUMENT_ROOT']."</strong> and is using PHP version <strong>".phpversion()."</strong> running under <strong>".php_uname('s')." ".php_uname('v')."; ".$_SERVER['SERVER_SOFTWARE']."</strong></p>");

    echo("<p>MySQL is version <strong>".mysqli_get_server_info($db_ibauk_conn)."</strong>, running on <strong>".mysqli_get_host_info($db_ibauk_conn)." [".$mysqlname."]</strong>.</p>");
    echo("<p>The database schema is <strong>$db_ibauk</strong> and is accessed via userid '<strong>$db_user</strong>'");
	$r = sql_query("SHOW VARIABLES LIKE 'datadir'");
	$rr = mysqli_fetch_assoc($r);
	
	$xx = $rr["Value"];
	
	if ($rr <> FALSE)
		echo("&nbsp;&nbsp; MySQL stores its databases in the folder <strong>".$xx."</strong>");
	echo("</p>");

	$r = sql_query("SHOW TABLES");
	$xx = 'Tables_in_'.$db_ibauk;
	echo("<ul>");
	while (TRUE)
	{
		$rr = mysqli_fetch_assoc($r);
		if ($rr == FALSE) break;
		$SQL = "SELECT count(*) as Rex FROM ".$rr[$xx];
		$n = sql_query($SQL);
		$nn = mysqli_fetch_assoc($n);
		$SQL = "SELECT count(*) as Rex FROM ".$rr[$xx]." WHERE Deleted='Y'";
		$n = sql_query($SQL);
		$zz = mysqli_fetch_assoc($n);
		
		echo("<li>Table <strong>".$rr[$xx]."</strong> has <strong>".number_format($nn['Rex'])."</strong> records");
		if ($zz != false)
			if ($zz['Rex'] > 0)
				echo(" (<strong>".number_format($zz['Rex'])."</strong> deleted)");
		echo("</li>");
	}
	echo("</ul>");
	echo("<p>[<a onclick=\"this.parentNode.innerHTML='Database exported!'\" href=\"index.php?cmd=dbexport\" title=\"Full SQL dump of the database\">Export database</a>]</p>");
		
	echo("</div>"); // End environment tab
	if ($OK)
	{
		if (!isset($_SESSION['ShowMemberStatus']))
			$_SESSION['ShowMemberStatus'] = 'all';
?>	
<div class="tabContent" id="tab_database">
	<form action="index.php" method="post">
	<input type="hidden" name="cmd" value="marksent">
	<p>You can mark all outstanding rides as having been reported to the USA by clicking this button. Those rides will all be marked as having been reported today. <strong>This is an immediate and irreversible operation, only click the button if you're sure!</strong></p>
	<input type="submit" title="This will act without further confirmation!" value="Mark all as sent to USA">
	</form>
	<hr />
	<form action="index.php" method="get">
	<input type="hidden" name="cmd" value="startimport">
	<p>Import ride/rally data from a spreadsheet</p>
	<input type="submit" title="" value="Start import">
	</form>
	<hr />
	<form action="index.php" method="post">
	<input type="hidden" name="cmd" value="marklapsed">
	<p>You can mark members as being 'Lapsed' by setting a cutoff date for recent activity. Anyone inactive since
	<?php
	$dt = New DateTime();
	$dtx = $dt->sub(new DateInterval('P3Y'));
	$dtxy = $dtx->format('Y-m-d');
	echo("<input type=\"date\" name=\"datelapsed\" value=\"$dtxy\">");
	?>
	 will be marked as lapsed when you press <input type="submit" title="" value="Mark as lapsed"</p>
	</form>
	
</div>
<div class="tabContent" id="tab_settings">
	<form action="index.php" method="get">
	<fieldset><legend>Show deleted records</legend>
	<input type="radio" name="ShowDeleted" class="radio" value="Y" <?php echo(Checkbox_isChecked($_SESSION['ShowDeleted']));?>>YES</input>
	<input type="radio" name="ShowDeleted" class="radio2" value="N" <?php echo(Checkbox_isNotChecked($_SESSION['ShowDeleted']));?>>no</input>
	</fieldset>	
	<fieldset><legend>Member status</legend>
	<select name="ShowMemberStatus">
	<option value="all"<?php echo(OptionSelected($_SESSION['ShowMemberStatus'],'all'));?>>Show all members</option>
	<option value="current"<?php echo(OptionSelected($_SESSION['ShowMemberStatus'],'current'));?>>Only current members</option>
	<option value="lapsed"<?php echo(OptionSelected($_SESSION['ShowMemberStatus'],'lapsed'));?>>Only lapsed members</option>
	</select>
	</fieldset>	
	<input type="submit" name="cmd" value="<?php echo($CMDWORDS['savesettings']);?>">
	</form>
</div> 	
	<?php } 
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if ($OK)
	{
		echo('<div class="tabContent" id="tab_users">');
		include('users.php');
		echo('</div>');
	}

    echo("</body></html>\n");
//}



?>