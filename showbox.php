<?php
/*
 * B O X E S - showbox.php
 *
 * Copyright (c) 2016 Bob Stammers
 *
 */


function no_box($ourbox)
{
    global $MYKEYWORDS;

    start_html("NO SUCH ".strtoupper($MYKEYWORDS['box'])." $ourbox");
    echo("<p class=\"errormsg\">I'm sorry, I don't seem to have a ".$MYKEYWORDS['box']." called ");
    echo("<span class=\"errordata\">$ourbox</span>.</p>");
    echo("</body></html>");
    exit();
}

function show_box($ourbox)
{

    global $ISOMONTHS, $ADD_EXTRA_ROWS, $MYKEYWORDS, $ADD_NEWBOX_ROWS, $MAX_BOX_CONTENTS;

    $UPDATING = $_SESSION['UPDATING'];


    if ($ourbox != '')
    {
        $SQL  = "SELECT * FROM boxes WHERE boxid = '$ourbox'";
        $box  = sql_query($SQL);
        if ($box == FALSE) no_box($ourbox);

        $box_data = mysqli_fetch_assoc($box);
        if ($box_data == FALSE) no_box($ourbox);
    }
    elseif ($_POST['CMD'] == 'UPDATE')
	{
        $box_data = $_POST;
	}
    else
    {
        $box_data = array();
    }

    start_html($MYKEYWORDS['Box']." details - $ourbox");
    $res  = "\n<form action=\"index.php\" method=\"post\" name=\"BOXUPDATE\" onsubmit=\"return ValidateBoxUpdate()\">";
    $res .= "\n<input type=\"hidden\" name=\"CMD\" value=\"UPDATE\"/>\n";
    $res .= "\n<input type=\"hidden\" name=\"UPDATE\" value=\"BOX\"/>\n";
    $res .= "\n<input type=\"hidden\" name=\"BOX\" value=\"".$box_data['boxid']."\"/>"; 
    $res .= "\n<table cellspacing=\"4\" summary=\"".$MYKEYWORDS['Box']." details\">\n";
    $res .= "\n<tr><th class=\"vertical\">".$MYKEYWORDS['Box']." ID :</th>";
	
    $res .= "<td class=\"ourbox\">";
    if ($UPDATING AND ($ourbox==''))
        $res .= "<input type=\"text\" size=\"10\" name=\"newboxid\" onblur=\"this.value=this.value.toUpperCase()\" title=\"Enter a unique identifier for the new box\"/>";
    else
        $res .= $box_data['boxid'];
    $res .= "</td>\n";
    if ($UPDATING and ($ourbox <> ''))
        $res .= "<td><input type=\"checkbox\" name=\"KILLBOX\" id=\"KILLBOX\"/>  <label for=\"KILLBOX\">DELETE WHOLE BOX</label> </td>";
    $res .= "</tr>";


    $res .= "\n<tr><th class=\"vertical\">Location :</th>";
    $res .= "<td class=\"location\">";
    if ($UPDATING)
    {
        $res .= "<select name=\"LOCN\">";
        $SQL  = "SELECT location FROM locations ORDER BY location";
        $r = sql_query($SQL);
        while(TRUE)
        {
            $loc = mysqli_fetch_assoc($r);
            if ($loc == FALSE) break;
            $res .= "<option value=\"".$loc['location']."\"";
            if ($loc['location'] == $box_data['location'])
                $res .= " selected=\"selected\"";
            $res .= ">".$loc['location']."</option>";
        }
        $res .= "</select>";
    }
    else
    {
        $res .= "<a href=\"index.php?cmd=showlocn&amp;LOCN=";
        $res .= $box_data['location']."\">".$box_data['location']."</a>";
    }
    $res .= "</td></tr>";


    $res .= "\n<tr><th class=\"vertical\">Storage ref :</th>";
    $res .= "<td class=\"boxid\" title=\"The storage reference is the identity of this box as far as the storage agent is concerned. It may be the same as our Box ID.\">";
    if ($UPDATING)
        $res .= "<input type=\"text\" name=\"storeref\" value=\"";
    $res .= $box_data['storeref'];
    if ($UPDATING)
        $res .= "\" onblur=\"this.value=this.value.toUpperCase()\"/>";
    $res .= "</td></tr>";
    $res .= "\n<tr><th	class=\"vertical\">Contents :</th>";
    $res .= "<td class=\"overview\" title=\"Describe the contents of this box eg 'tax papers', 'leases', 'mixed documents'\">";
    if ($UPDATING)
        $res .= "<input type=\"text\" name=\"overview\" value=\"";
    $res .= $box_data['overview'];
    if ($UPDATING)
        $res .= "\" onblur=\"this.value=this.value.toUpperCase()\"/>";
    $res .= "</td></tr>";
    $res .= "\n<tr><th class=\"vertical\">N<sup>o</sup> of files :</th>";
    $res .= "<td title=\"The number of files stored in this box, detailed below\">".$box_data['numdocs']."</td></tr>";
    $res .= "\n<tr><th class=\"vertical\">Review date :</th>";
    if (show_date($box_data['min_review_date']) == show_date($box_data['max_review_date']))
        $res .= "<td class=\"date\" colspan=\"2\" title=\"The date when the contents of this box should be reviewed, details below\">".show_date($box_data['min_review_date'])."</td></tr>";
    else
    {
        $res .= "<td class=\"date\" title=\"The daterange when the contents of this box should be reviewed, details below\">".show_date($box_data['min_review_date']);
        $res .= " to ";
        $res .= show_date($box_data['max_review_date'])."</td></tr>";
    }
    $res .= "</table>";
    $res .= "<table border=\"1\" summary=\"Box contents\"><tr>";
    $res .= "<th class=\"owner\">Partner</th>";
    $res .= "<th class=\"client\">Client</th>";
    $res .= "<th class=\"name\">Name</th>";
    $res .= "<th class=\"contents\">Contents</th>";
    $res .= "<th class=\"date\">Review</th>";
    if ($UPDATING and ($ourbox <> ''))
        $res .= "<th>Delete</th>";
    $res .= "</tr>";
    $SQL  = "SELECT * ";
    $SQL .= "FROM contents WHERE boxid = '".$box_data['boxid']."' ";
    if ($UPDATING)
      $SQL .= "ORDER BY id ";
    else
      $SQL .= "ORDER BY owner, client, name ";
    $rr = sql_query($SQL);
	$rowcount = 0;
    while(TRUE)
    {
        $con_data = mysqli_fetch_assoc($rr);
        if ($con_data == FALSE) break;

		$rowcount = $rowcount + 1;
        $IDS[$con_data['id']] = $con_data['id'];
        $res .= "\n<tr>";
        $res .= "<td class=\"owner\">";
        if ($UPDATING)
            $res .= "<input type=\"text\" size=\"6\" name=\"owner$".$con_data['id']."\" value=\"".$con_data['owner']."\" onblur=\"this.value=this.value.toUpperCase()\"/></td>";
        else
            $res .= partner_anchor($con_data['owner'])."</td>";
        $res .= "<td class=\"client\">";
        if ($UPDATING)
            $res .= "<input type=\"text\" size=\"6\" name=\"client$".$con_data['id']."\" value=\"".$con_data['client']."\"/>";
        else
            $res .= $con_data['client'];
        $res .= "</td>";
        $res .= "<td class=\"name\">";
        if ($UPDATING)
            $res .= "<input type=\"text\" name=\"name$".$con_data['id']."\" value=\"".htmlentities($con_data['name'])."\"/>";
        else
            $res .= htmlentities($con_data['name']);
        $res .= "</td>";
        $res .= "<td class=\"contents\">";
        if ($UPDATING)
            $res .= "<input type=\"text\" size=\"30\" name=\"contents$".$con_data['id']."\" value=\"".htmlentities($con_data['contents'])."\"/>";
        else
            $res .= htmlentities($con_data['contents']);
        $res .= "</td>";
        $res .= "<td class=\"date\">";
        if ($UPDATING)
        {
            $res .= "<input type=\"hidden\" size=\"10\" name=\"review$".$con_data['id']."\" value=\"".$con_data['review_date']."\"/>";
            $res .= "<select name=\"reviewmonth$".$con_data['id']."\">";
            foreach($ISOMONTHS as $N=>$M)
            {
                $res .= "<option value=\"$N\"";
                if ($M == $ISOMONTHS[substr($con_data['review_date'],5,2)])
                    $res .= " selected=\"selected\"";
                $res .= ">$M</option>";
            }
            $res .= "</select>";
            $res .= "<select name=\"reviewyear$".$con_data['id']."\">";
            $y = getdate();
            $y = $y['year'];
            foreach(range(0,14) as $n)
            {
                $res .= "<option value=\"$y\"";
                if ($y == substr($con_data['review_date'],0,4)) $res .= " selected=\"selected\"";
                $res .= ">$y</option>";
                $y++;
            }
            $res .= "</select>";
        }
        else
            $res .= show_date($con_data['review_date']);
        $res .= "</td>";
        if ($UPDATING and ($ourbox <> ''))
            $res .= "<td style=\"text-align: center;\"><input type=\"checkbox\" name=\"delete$".$con_data['id']."\"/></td>";
        $res .= "</tr>";

    }
    if ($UPDATING)
    {
        foreach ($IDS as $id) {
          if ($xx != '') $xx .= ",";
          $xx .= "$id";
        }
    }
    
    if ($UPDATING and $ourbox != '') // old box
	{
      $j = $ADD_EXTRA_ROWS;
	}
    else
	{
      $j = $ADD_NEWBOX_ROWS;
	}
	if ($rowcount + $j > $MAX_BOX_CONTENTS)
	{
			$j = ($rowcount < $MAX_BOX_CONTENTS? $MAX_BOX_CONTENTS - $rowcount : 0);
	}
    if ($UPDATING) for ($i=0; $i < $j; $i++)
    {
        if ($xx != '') $xx .= ",";
        $xx .= "new$i";
        $res .= "\n<tr>";
        $res .= "<td class=\"owner\">";
        $res .= "<input type=\"text\" size=\"6\" name=\"owner\$new$i\" value=\"".$box_data['owner$new']."\" onblur=\"this.value=this.value.toUpperCase()\"/></td>";
        $res .= "<td class=\"client\">";
        $res .= "<input type=\"text\" size=\"6\" name=\"client\$new$i\" value=\"".$box_data['client$new']."\"/>";
        $res .= "</td>\n";
        $res .= "<td class=\"name\">";
        $res .= "<input type=\"text\" name=\"name\$new$i\" value=\"".$box_data['name$new']."\"/>";
        $res .= "</td>\n";
        $res .= "<td class=\"contents\">";
        $res .= "<input type=\"text\" size=\"30\" name=\"contents\$new$i\" value=\"".$box_data['contents$new']."\"/>";
        $res .= "</td>\n";
        $res .= "<td class=\"date\">";
        $res .= "<input type=\"hidden\" size=\"10\" name=\"review\$new$i\" value=\"".$box_data['review$new']."\"/>";
        $res .= "<select name=\"reviewmonth\$new$i\">";
            foreach($ISOMONTHS as $N=>$M)
            {
                $res .= "<option value=\"$N\"";
                if ($N == '12') $res .= " selected=\"selected\"";
                $res .= ">$M</option>";
            }
            $res .= "</select>";
            $res .= "<select name=\"reviewyear\$new$i\">";
            $y = getdate();
            $y = $y['year'];
            foreach(range(0,14) as $n)
            {
                $res .= "<option value=\"$y\"";
                if ($n == 7) $res .= " selected=\"selected\"";
                $res .= ">$y</option>";
                $y++;
            }
            $res .= "</select>";

        $res .= "</td>\n";
        $res .= "</tr>\n";
    }
    $res .= "</table>\n";
    if ($UPDATING)
    {
//        foreach ($IDS as $id) $xx .= "$id,";
//        $xx .= "new";
        $res .= "<input type=\"hidden\" name=\"OLDIDS\" value=\"$xx\"/>\n";
        $res .= "<input type=\"hidden\" name=\"ISVALID\" value=\"0\"/>\n";
        $res .= "<input type=\"submit\" value=\"Update box, continue\" title=\"Post the changes to this box then re-present the same box\"/> ";
        $res .= "<input type=\"submit\" name=\"EOBSOB\" value=\"Complete box, start new box\" title=\"Post changes to this box then give me a blank form so I can create a new box\"/>";
    }
    $res .= "</form>";
//    echo(ereg_replace("<","&lt;",$res));


    if ($UPDATING AND ($ourbox!=''))
    {
        $res .= "\n<script language=\"JavaScript\" type=\"text/javascript\">document.BOXUPDATE.owner\$new0.focus();</script>\n";
    }

    $res .= "</body></html>";
    echo($res);


}
if (isset($_GET['bx'])) $_GET['boxid'] = $_GET['bx']; 
if ($_SESSION['UPDATING'] or ($_GET['boxid'] <> ''))
    show_box($_GET['boxid']);
else
    include('search.php');	
?>
