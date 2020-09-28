<?php
/*
 * I B A U K - users.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */


// Fail quietly if called directly
if (!function_exists('start_html')) exit;

require 'PasswordHash.php';

function update_users()
{

	global $HASH_COST_LOG2, $HASH_PORTABLE, $db_ibauk_conn;

    foreach($_POST as $K => $V)
    {
        $p = strpos($K,'$');
        if ($p) $IDS[substr($K,$p + 1)] = substr($K,$p + 1);
    }
    $nu = strtolower($_POST['newuserid']);
    if ($nu <> '')
    {
        if ($nu == 'new')
            $baduser = true;
        else
        {
            $SQL  = "SELECT * FROM users WHERE userid = '$nu'";
            $r = sql_query($SQL);
            $rr = $r->fetchArray();
            $baduser = ($rr <> false);
        }
		
        if ($baduser)
        {
            start_html("Error");
            echo("<p class=\"errormsg\">Userid <span class=\"errordata\">$nu</span> is already in use.</p>");
            exit();
        }
        if ($_POST['accesslevel'] > $_SESSION['ACCESSLEVEL'])
            $_POST['accesslevel'] = $_SESSION['ACCESSLEVEL'];

        if ($_POST['password1'] == '')
        {
            $_POST['password1'] = $nu;
            $_POST['password2'] = $nu;
        }


        if ($_POST['password1'] <> $_POST['password2'])
        {
            start_html("Bad password");
            echo("<p class=\"errormsg\">The new password does not match its repeat</p>");
            exit();
        }

		$hasher = new PasswordHash($HASH_COST_LOG2, $HASH_PORTABLE);
		$hash = $hasher->HashPassword($_POST['password1']);
		unset($hasher);
		
        $SQL  = "INSERT INTO users (userid,userpass";
        if ($_POST['accesslevel']) $SQL .= ",accesslevel";
        $SQL .= ") VALUES ('".safesql($nu)."','".$hash."'";
        if ($_POST['accesslevel']) $SQL .= ",".$_POST['accesslevel'];
		$SQL .= ")";
        sql_query($SQL);
		show_infoline("User $nu established ok",'infohilite');

    }
	
    foreach ($IDS as $id)
    {
	
        if ($_POST['delete$'.$id])
        {
            $SQL  = "DELETE FROM users WHERE userid = '".$id."'";
			
            sql_query($SQL);
			show_infoline("User $id DELETED","infohilite");
            continue;
        }
		
        if ($_POST['password1$'.$id] <> '')
        {
            if ($_POST['password1$'.$id] <> $_POST['password2$'.$id])
            {
                start_html("password change");
                echo("<p class=\"errormsg\">The new password does not match its repeat</p>");
                exit();
            }
            //$_POST['password1$'.$id] = strtolower($_POST['password1$'.$id]);
            //$_POST['oldpassword$'.$id] = strtolower($_POST['oldpassword$'.$id]);
            $SQL  = "SELECT * FROM users WHERE userid = '$id'";
            $r = sql_query($SQL);
            $usr = $r->fetchArray();
            if ($usr['accesslevel'] > $_SESSION['ACCESSLEVEL'])
            {
                start_html("password change");
                echo("<p class=\"errormsg\">You do not have authority to change the password for ");
                echo("<span class=\"errordata\"$id</span></p>");
                exit();
            }

			$hasher = new PasswordHash($HASH_COST_LOG2, $HASH_PORTABLE);
			
            if ( ($hasher->CheckPassword($_POST['oldpassword$'.$id],$usr['userpass'])) or
                 ($_SESSION['ACCESSLEVEL'] > $usr['accesslevel']) )
            {
                // ok
                $SQL  = "UPDATE users SET ";
                $SQL .= "userpass = '".$hasher->HashPassword($_POST['password1$'.$id])."'";
                $SQL .= ",accesslevel = ".$_POST['accesslevel$'.$id];
                $SQL .= " WHERE userid = '$id'";
                sql_query($SQL);
				show_infoline('Password changed successfully','infohilite');
            }
            else
            {
                start_html("password change");
                echo("<p class=\"errormsg\">Your password change failed to authenticate</p>");
                exit();
            }
			unset($hasher);
        }
		else if ($_POST['accesslevel$'.$id] != $_POST['oldaccesslevel$'.$id])
		{
                $SQL  = "UPDATE users SET ";
                $SQL .= "accesslevel = ".$_POST['accesslevel$'.$id];
                $SQL .= " WHERE userid = '$id'";
                sql_query($SQL);
				show_infoline('Accesslevel changed successfully','infohilite');
		
		}
		
    }


}
function browse_users($onlyme)
{
	global $ACCESSLEVEL_READONLY, $ACCESSLEVEL_UPDATE, $ACCESSLEVEL_SUPER, $ACCESSLEVELS;
	
    //start_html("User maintenance");

    $SQL  = "SELECT * FROM users WHERE ";
    if ($onlyme)
        $SQL .= "userid = '".$_SESSION['USERNAME']."'";
    else
        $SQL .= "accesslevel <= ".$_SESSION['ACCESSLEVEL'];
    $r = sql_query($SQL);
	
	echo("<p>You may alter your password by entering the existing password and a new one twice.");
	echo(" If you don't know your existing password you'll have to get someone");
	if ($_SESSION['ACCESSLEVEL'] == $ACCESSLEVEL_SUPER) echo(" else");
	echo(" with an accesslevel of ".$ACCESSLEVELS[$ACCESSLEVEL_SUPER]." to change it for you</p>");
	if ($_SESSION['ACCESSLEVEL'] == $ACCESSLEVEL_SUPER)
		echo("<p>You may alter other users' passwords' by entering the new password twice, no need to enter the old one.</p>");
	
    echo("<form action=\"index.php\" method=\"post\">");
    echo("<input type=\"hidden\" name=\"cmd\" value=\"USERS\"/>");
    echo("<input type=\"hidden\" name=\"UPDATE\" value=\"USERS\"/>");
    echo("<table>");
    echo("<tr><th>Userid</th>");
    if (!$onlyme) echo("<th>Access level</th>");
    echo("<th>Old Password</th>");
    echo("<th>New password</th>");
    echo("<th>and again</th>");
    if (!$onlyme)
        echo("<th>Delete user</th>");
    echo("</tr>");

    while(TRUE)
    {
        $usr = $r->fetchArray();
        if ($usr == FALSE) break;
        echo("<tr><td>".$usr['userid']);
        if (!$onlyme) 
		{
			echo("<input type=\"hidden\" name=\"oldaccesslevel\$".$usr['userid']."\" value=\"".$usr['accesslevel']."\"/></td>");
			
			echo("<td><select name=\"accesslevel\$".$usr['userid']."\">"); 
			foreach ($ACCESSLEVELS as $lvl=>$val)
			{
				echo("<option value=\"$lvl\"");
				if ($lvl==$usr['accesslevel']) echo(" selected=\"selected\"");
				echo(">$val</option>");
			}
			echo("</select></td>");
		}
        echo("<td><input type=\"password\" name=\"oldpassword\$".$usr['userid']."\"/></td>");
        echo("<td><input type=\"password\" name=\"password1\$".$usr['userid']."\"/></td>");
        echo("<td><input type=\"password\" name=\"password2\$".$usr['userid']."\"/></td>");
        if (!$onlyme)
            if (strtoupper($usr['userid']) != strtoupper($_SESSION['USERNAME']))
                echo("<td><input type=\"checkbox\" name=\"delete\$".$usr['userid']."\"/></td>");
            else
                echo('<td></td>');
        echo("</tr>");
    }
    if (!$onlyme)
    {
        echo("<tr><td><input type=\"text\" name=\"newuserid\"/></td>");
			echo("<td><select name=\"accesslevel".$usr['userid']."\">"); 
			foreach ($ACCESSLEVELS as $lvl=>$val)
			{
				echo("<option value=\"$lvl\"");
				if ($lvl==$ACCESSLEVEL_UPDATE) echo(" selected=\"selected\"");
				echo(">$val</option>");
			}
			echo("</select></td>");
        //echo("<td><input type=\"password\" name=\"oldpassword\"/></td>");
		echo("<td></td>");
        echo("<td><input type=\"password\" name=\"password1\" title=\"If blank will default to userid\"/></td>");
        echo("<td><input type=\"password\" name=\"password2\" title=\"If blank will default to userid\"/></td>");
        echo("<td></td></tr>");
    }
    echo("</table>");
    echo("<input type=\"submit\" value=\"Update\"/>");
    echo("</form>");
}

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();


if ($_POST['UPDATE'] == 'USERS')
    update_users();
else
	browse_users($_SESSION['ACCESSLEVEL'] < $GLOBALS['ACCESSLEVEL_SUPER']);

?>
