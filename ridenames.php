<?php
/*
 * I B A U K - ridenames.php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 */

$RIDENAMES_SQL  = "SELECT SQL_CALC_FOUND_ROWS * FROM ridenames ";


function ridenames_table_row_header()
{
    global $MYKEYWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);

	$res = '';
    $res .= "<th class=\"text\">Lookup list of names of IBA rides</th>";
    return $res;
}

function ridenames_table_row_html($rd)
{
	$SHOW_RIDES_ICON = "\xF0\x9F\x8E\xA0\xF0\x9F\x8E\xA0";
	$RIDETYPE_AVAIL = "\xE2\x9C\x94";
	$RIDETYPE_NOT_AVAIL = "\xE2\x9C\x96";
	
    global $MYKEYWORDS, $CMDWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);

	$res = '';
    $res .= "<td class=\"text\">";
	$ro = ' readonly ';
	if ($OK) 
	{
			$ro = "";
			$res .= "<form action=\"index.php\" method=\"get\">";
			$res .= "<input type=\"hidden\" name=\"cmd\" value=\"".$CMDWORDS['editridetype']."\" />";
			$res .= "<input type=\"hidden\" name=\"".$CMDWORDS['uri']."\" value=\"".$rd['recid']."\" />";
	}
    $res .= "<input class=\"long\" type=\"text\" readonly value=\"".$rd['IBA_Ride']."\" />";
	if ($OK)
	{
		if ($rd['recid'] <> 'newrec') 	{
			if ($rd['Deleted'] == 'Y')
				$res .= "<span title=\"Defunct ridetype\"> ".$RIDETYPE_NOT_AVAIL." </span>";
			else
				$res .= "<span title=\"Current ridetype\"> ".$RIDETYPE_AVAIL." </span>";
			//$res .= "<input type=\"radio\" name=\"deletethisrec\" value=\"N\" checked > Save </input>";
			//$res .= "<input type=\"radio\" name=\"deletethisrec\" value=\"Y\"> Delete </input>";
			$res .= " [ <a href=\"index.php?c=se&x=x&f=".urlencode($rd['IBA_Ride'])."&parShowResults=rides\" title=\"show these rides\">".$SHOW_RIDES_ICON."</a> ] ";
			$res .= "<input type=\"submit\" value=\"Details\" />";			
			
		} else {
			$res .= "<input type=\"submit\" value=\"Create new entry\" />";
		}
	}
    $res .= "</td></form>";
    return $res;
}


function ridenames_table_row($uri)
{
    global $RIDENAMES_SQL;

    $SQL = $RIDENAMES_SQL." ORDER BY IBA_Ride ";
    $r = sql_query($SQL);
    $rd = mysqli_fetch_assoc($r);

    return ridenames_table_row_html($rd);

}


function show_ridenames_table($where)
{
    global $RIDENAMES_SQL, $ORDER, $DESC, $OFFSET, $PAGESIZE, $CMDWORDS;

    $SQL = $RIDENAMES_SQL;
    if ($where <> '') $SQL .= " WHERE $where ";

	$SQL .= " ORDER BY IBA_Ride ";
	//echo($SQL.'<hr />');
    $r = sql_query($SQL);
	$TotRows = foundrows();
	$xl = '';
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	echo("<div class=\"maindata\">");
	if ($TotRows > mysqli_num_rows($r))
		show_common_paging($TotRows,$xl);
    echo("<table  summary=\"List of ".$MYKEYWORDS['ridenames']."\">");
	echo("<tr>".ridenames_table_row_header()."</tr>\n");
	if ($OK) echo(ridenames_table_row_html(array('recid'=>'newrec','IBA_Ride'=>'')));
	$rownum = 0;
    while(true)
    {
        $rd = mysqli_fetch_assoc($r);
        if ($rd == false) break;
		$rownum++;
		if ($rownum % 2 == 1)
			echo("<tr class=\"row-1\">");
		else
			echo("<tr class=\"row-2\">");
        echo(ridenames_table_row_html($rd)."</tr>\n");
    }
    echo("</table></div>");
	
}



function show_ridenames_listing()
{
    global $OFFSET, $PAGESIZE, $SHOW, $MYKEYWORDS;

	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK) safe_default_action();
	
        start_html($MYKEYWORDS['ridenames']." listing");
        if ($_GET['show']=='all')
        {
            $OFFSET = 0;
            $PAGESIZE = -1;
        }
?>
		<p>This list of names is used to facilitate data entry when entering new ride details.<br />
		Ride records are associated with this list by the name (as opposed to a record identity). It aids consistency if names are not changed when rides already use the name.</p>
<?php
		show_ridenames_table("");
        echo("</body></html>");
        
}

function update_ridename()
{
	global $CMDWORDS;
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();

	if ($_POST[$CMDWORDS['uri']] == 'newrec' && $_POST['IBA_Ride'] <> '') {
		$SQL = "INSERT INTO ridenames (IBA_Ride) VALUES ('".safesql($_POST['IBA_Ride'])."')";
	} else if ($_POST['deletethisrec'] == 'Y') {
		$SQL = "DELETE FROM ridenames WHERE recid=".$_POST[$CMDWORDS['uri']];
	} else if ($_POST['IBA_Ride'] <> '') {
		$SQL = "UPDATE ridenames SET IBA_Ride='".safesql($_POST['IBA_Ride'])."' WHERE recid=".$_POST[$CMDWORDS['uri']];
	} else {
		$SQL = '';
	}
	if ($SQL <> '') sql_query($SQL);
	show_ridenames_listing();
}

function emit_filelist($listid,$folder_path,$fileRE)
{
	
	echo("<datalist id=\"".$listid."\">");
	$folder = opendir($folder_path);
 	while(false !== ($file = readdir($folder))) 
	{
		$file_path = $folder_path.$file;
		$extension = strtolower(pathinfo($file ,PATHINFO_EXTENSION));
		if (preg_match($fileRE,$extension))
			echo("<option>".$file."</option>");
	}
	echo('</datalist>');
		
}

function edit_ridetype()
{
	global $CMDWORDS;
	
	$uri = $_REQUEST[$CMDWORDS['uri']];
	$OK = ($_SESSION['ACCESSLEVEL'] > $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK || $uri == '')
		safe_default_action();
	
	$SQL = "SELECT recid,IBA_Ride,IfNull(IBA_Ride_Title,IBA_Ride) As IBA_Ride_Title,IBA_Ride_Desc,MaxHours,HdrImg,SigImg,TemplateID,Deleted FROM ridenames WHERE recid=".$uri;
    $rt  = sql_query($SQL);
    $rtd = mysqli_fetch_assoc($rt);
	start_html($rtd['IBA_Ride']);
	
?>	
<div class="maindata">
<br /><br />
<form action="index.php" method="post">
<?php
	echo("<input type=\"hidden\" name=\"cmd\" value=\"".$CMDWORDS['putridetype']."\" />");
	echo("<label for=\"recid\" class=\"vlabel2\">Ridetype #</label>");
	echo("<input type=\"number\" name=\"recid\" id=\"recid\" class=\"short\" readonly value=\"".$rtd['recid']."\" /><br /><br />");
	echo("<label for=\"IBA_Ride\" class=\"vlabel2\">IBA Ride</label>");
	echo("<input type=\"text\" name=\"IBA_Ride\" id=\"IBA_Ride\" title=\"Short name of the ride; shown on RoH; Must be unique\" class=\"vdata\" value=\"".$rtd['IBA_Ride']."\" /><br />");
	echo("<label for=\"IBA_Ride_Title\" class=\"vlabel2\">Full ride title</label>");
	echo("<input type=\"text\" name=\"IBA_Ride_Title\" id=\"IBA_Ride_Title\" title=\"Full name of the ride; shown on certificate\" class=\"vdata long\" value=\"".$rtd['IBA_Ride_Title']."\" /><br />");
	echo("<label for=\"IBA_Ride_Desc\" class=\"vlabel2\">Ride description</label>");
	echo("<input type=\"text\" name=\"IBA_Ride_Desc\" id=\"IBA_Ride_Desc\" title=\"Description of the ride; appears on certificate\" class=\"vdata long\" value=\"".$rtd['IBA_Ride_Desc']."\" /><br />");
	echo("<label for=\"MaxHours\" class=\"vlabel2\">Maximum hours</label>");
	echo("<input type=\"number\" name=\"MaxHours\" id=\"MaxHours\" title=\"Maximum hours allowed for this ride\" class=\"vdata short\" value=\"".$rtd['MaxHours']."\" /><br />");
	echo("<label for=\"HdrImg\" class=\"vlabel2\">Header image file</label>");
	echo("<input type=\"text\" list=\"Images\" name=\"HdrImg\" id=\"HdrImg\" title=\"Name of image file held in the library; used in certificate\" class=\"vdata\" value=\"".$rtd['HdrImg']."\" /><br />");
	emit_filelist("Images","./certificates/images","/jpg|gif|bmp|png/");
	echo("<label for=\"SigImg\" class=\"vlabel2\">Sig image file</label>");
	echo("<input type=\"text\" list=\"Images\" name=\"SigImg\" id=\"SigImg\" title=\"Name of image file held in the library; used in certificate\" class=\"vdata\" value=\"".$rtd['SigImg']."\" /><br />");
	echo("<label for=\"TemplateID\" class=\"vlabel2\">Template file</label>");
	echo("<input type=\"text\" name=\"TemplateID\" list=\"Templates\" id=\"TemplateID\" title=\"Name of certificate template held in the library\" class=\"vdata\" value=\"".$rtd['TemplateID']."\" /><br />");
	emit_filelist("Templates","./certificates/templates","/html/");
	echo("<br /><input title=\"Available for new rides\" type=\"radio\" name=\"deletethisrec\" value=\"N\"".Checkbox_isNotChecked($rtd['Deleted'])." > Current </input>");
	echo("<input title=\"No longer available for new rides\" type=\"radio\" name=\"deletethisrec\" value=\"Y\"".Checkbox_isChecked($rtd['Deleted'])."> Defunct </input>");
?>
<input type="submit" value="Update" />
</form>
</div>	
<?php
}

function update_ridetype()
{
	global $CMDWORDS;
	
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	if (!$OK) safe_default_action();

	if ($_POST['recid'] == 'newrec' && $_POST['IBA_Ride'] <> '') {
		$SQL = "INSERT INTO ridenames (IBA_Ride) VALUES ('".safesql($_POST['IBA_Ride'])."')";
	} else if ($_POST['deletethisrec'] == 'Y') {
		$SQL = "UPDATE ridenames SET Deleted='Y' WHERE recid=".$_POST['recid'];
	} else if ($_POST['IBA_Ride'] <> '') {
		$SQL = "UPDATE ridenames SET IBA_Ride='".safesql($_POST['IBA_Ride'])."' ";
		$SQL .= ", IBA_Ride_Title='".safesql($_POST['IBA_Ride_Title'])."' ";
		$SQL .= ", IBA_Ride_Desc='".safesql($_POST['IBA_Ride_Desc'])."' ";
		$SQL .= ", MaxHours=".safesql($_POST['MaxHours']);
		$SQL .= ", HdrImg='".safesql($_POST['HdrImg'])."' ";
		$SQL .= ", SigImg='".safesql($_POST['SigImg'])."' ";
		$SQL .= ", TemplateID='".safesql($_POST['TemplateID'])."' ";
		$SQL .= ", Deleted='N'";
		$SQL .= " WHERE recid=".$_POST['recid'];
	} else {
		$SQL = '';
	}
	if ($SQL <> '') sql_query($SQL);
	show_ridenames_listing();
	
}

?>
