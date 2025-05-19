<?php
/*
 * I B A U K - ridenames.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2025 Bob Stammers
 *
 */

$RIDENAMES_SQL  = "SELECT * FROM ridenames ";


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
	if ($rd['IBA_Ride']=='')
		$res .= "";
	else
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
    $res .= "</form></td>";
    return $res;
}


function ridenames_table_row($uri)
{
    global $RIDENAMES_SQL;

    $SQL = $RIDENAMES_SQL." ORDER BY IBA_Ride ";
    $r = sql_query($SQL);
    $rd = $r->fetchArray();

    return ridenames_table_row_html($rd);

}


function show_ridenames_table($where)
{
    global $RIDENAMES_SQL, $ORDER, $DESC, $OFFSET, $PAGESIZE, $CMDWORDS, $MYKEYWORDS;

    $SQL = $RIDENAMES_SQL;
    if ($where <> '') $SQL .= " WHERE $where ";

	$SQL .= " ORDER BY IBA_Ride ";
	//echo($SQL.'<hr />');
    $r = sql_query($SQL);
	$TotRows = foundrows($r);
	$xl = '';
	$OK = ($_SESSION['ACCESSLEVEL'] >= $GLOBALS['ACCESSLEVEL_UPDATE']);
	echo("<div class=\"maindata\">");
	//$numrows = countrecs($r);
	if ($PAGESIZE > 0 && $TotRows > $PAGESIZE)
		show_common_paging($TotRows,$xl);
    echo("<table>");
	echo("<tr>".ridenames_table_row_header()."</tr>\n");
	if ($OK) echo('<tr>'.ridenames_table_row_html(array('recid'=>'newrec','IBA_Ride'=>'')).'</tr>');
	$rownum = 0;
    while(true)
    {
        $rd = $r->fetchArray();
        if ($rd == false) break;
		$rownum++;
		if ($rownum <= $OFFSET)
			continue;

		if ($PAGESIZE > 0 && $rownum - $OFFSET > $PAGESIZE)
			break;

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

function emit_filelistOptions($folderPath,$fileRE,$current)
{
	$folder = scandir($folderPath);
	for ($i = 0; $i < count($folder); $i++) {
		if ($folder[$i] == '.') continue;
		if ($folder[$i] == '..') continue;
		$file = $folder[$i];
		$extension = strtolower(pathinfo($file ,PATHINFO_EXTENSION));
		if (preg_match($fileRE,$extension)) {
			echo('<option value="'.$file.'" ');
			if (strtolower($file)==strtolower($current))
				echo(' selected ');
			echo('>'.$file.'</option>');
		}

	}	
}

function edit_ridetype()
{
	global $CMDWORDS;
	
	$uri = $_REQUEST[$CMDWORDS['uri']];
	$OK = ($_SESSION['ACCESSLEVEL'] > $GLOBALS['ACCESSLEVEL_READONLY']);
	if (!$OK || $uri == '')
		safe_default_action();
	
	$SQL = "SELECT recid,IBA_Ride,IfNull(IBA_Ride_Title,IBA_Ride) As IBA_Ride_Title,IBA_Ride_Desc,MaxHours,HdrImg,SigImg,TemplateID,Deleted,MilesKms,MinDistance FROM ridenames WHERE recid=".$uri;
	$rt  = sql_query($SQL);
	if ($rt)
		$rtd = $rt->fetchArray();
	else
		$rtd = defaultRecord("ridenames");
	start_html($rtd['IBA_Ride']);
	//print_r($rtd);
	//foreach ($rtd as $r)
	//	echo('<br>'.gettype($r));
	//echo('<hr>');
?>	
<div class="maindata">
<br /><br />
<form action="index.php" method="post">
<?php
	echo("<input type=\"hidden\" name=\"cmd\" value=\"".$CMDWORDS['putridetype']."\" />");


	echo('<span title="This is the name used to identify this ride type (SS1000)">');
	echo("<label for=\"IBA_Ride\" class=\"vlabel2\">Unique ride name for RoH</label> ");
	$x = gettype($rtd['IBA_Ride'])=='NULL' ? '' : htmlspecialchars($rtd['IBA_Ride']);
	echo('<input type="hidden" name="IBA_Ride_Old" value="'.$x.'">');
	echo("<input type=\"text\" name=\"IBA_Ride\" id=\"IBA_Ride\"  class=\"vdata long\" value=\"".$x."\" />");
	echo('<br><br>');
	echo('</span>');

	echo('<span title="Ride name as it appears on the certificate (SaddleSore 1000)">');
	echo("<label for=\"IBA_Ride_Title\" class=\"vlabel2\">Full ride title for certificate</label> ");
	echo("<input type=\"text\" name=\"IBA_Ride_Title\" id=\"IBA_Ride_Title\"  class=\"vdata long\" value=\"".htmlspecialchars($rtd['IBA_Ride_Title'])."\" />");
	echo('<br><br>');
	echo('</span>');

	echo('<span title="Appears on the certificate (1,000 miles in 24 hours)">');
	echo("<label for=\"IBA_Ride_Desc\" class=\"vlabel2\">Description for certificate</label> ");
	echo("<input type=\"text\" name=\"IBA_Ride_Desc\" id=\"IBA_Ride_Desc\" class=\"vdata long\" value=\"".htmlspecialchars($rtd['IBA_Ride_Desc'])."\" />");
	echo('<br><br>');
	echo('</span>');

	
	echo("<label for=\"MaxHours\" class=\"vlabel2\">Maximum hours</label> ");
	echo("<input type=\"number\" name=\"MaxHours\" id=\"MaxHours\" class=\"vdata short\"  title=\"Maximum hours allowed for this ride\"  value=\"".$rtd['MaxHours']."\" />");
	echo('<br><br>');

	echo('<span title="Ride specified in miles, or kilometres">');
	echo('<label class="vlabel2" for="MilesKms">Unit of distance</label> ');
	echo('<select id="MilesKms" name="MilesKms" class="vdata short">');
	echo('<option value="0" '.($rtd['MilesKms']==0? ' selected ' : '').'>Miles</option>');
	echo('<option value="1" '.($rtd['MilesKms']!=0? ' selected ' : '').'>Kilometres</option>');
	echo('</select>');
	echo('<br><br>');
	echo('</span>');

	echo('<span title="Minimum number of miles/kms. 0=no limit">');
	echo('<label for="MinDistance" class="vlabel2">Minimum distance</label> ');
	echo('<input type="number" class="vdata short" id="MinDistance" name="MinDistance" value="'.$rtd['MinDistance'].'">');
	echo('<br><br>');
	echo('</span>');

	echo("<label for=\"HdrImg\" class=\"vlabel2\">Header image file</label> ");
	echo('<select name="HdrImg" id="HdrImg" class="vdata">');
	emit_filelistOptions("./certificates/images","/jpg|gif|bmp|png/",$rtd['HdrImg']);
	echo('</select>');
	//echo("<input type=\"text\" list=\"Images\" name=\"HdrImg\" id=\"HdrImg\" title=\"Name of image file held in the library; used in certificate\" class=\"vdata\" value=\"".$rtd['HdrImg']."\" />");
	echo('<br><br>');
	//emit_filelist("Images","./certificates/images","/jpg|gif|bmp|png/");

	/**
	echo("<label for=\"SigImg\" class=\"vlabel2\">Sig image file</label>");
	echo("<input type=\"text\" list=\"Images\" name=\"SigImg\" id=\"SigImg\" title=\"Name of image file held in the library; used in certificate\" class=\"vdata\" value=\"".$rtd['SigImg']."\" />");
	echo('<br><br>');
	**/

	echo("<label for=\"TemplateID\" class=\"vlabel2\">Template file</label> ");
	echo('<select name="TemplateID" id="TemplateID" class="vdata">');
	emit_filelistOptions("./certificates/templates","/html/",$rtd['TemplateID']);
	echo('</select>');
	//echo("<input type=\"text\" name=\"TemplateID\" list=\"Templates\" id=\"TemplateID\" title=\"Name of certificate template held in the library\" class=\"vdata\" value=\"".$rtd['TemplateID']."\" />");
	echo('<br><br>');
	//emit_filelist("Templates","./certificates/templates","/html/");

	echo('<span title="Is this still available for new rides?">');
	echo('<label for="deletethisrec" class="vlabel2">Available for new rides</label> ');
	echo('<select id="deletethisrec" name="deletethisrec" class="vdata">');
	echo('<option value="N" '.OptionSelected($rtd['Deleted'],"N").'>Yes - current ride</option>');
	echo('<option value="Y" '.OptionSelected($rtd['Deleted'],"Y").'>No - defunct ride</option>');
	echo('</select>');
	//echo("<input title=\"Available for new rides\" type=\"radio\" id=\"deletethisrecN\" name=\"deletethisrec\" value=\"N\"".Checkbox_isNotChecked($rtd['Deleted'])." > <label for=\"deletethisrecN\">Current </label>");
	//echo("<input title=\"No longer available for new rides\" type=\"radio\" id=\"deletethisrecY\" name=\"deletethisrec\" value=\"Y\"".Checkbox_isChecked($rtd['Deleted'])."> <label for=\"deletethisrecY\">Defunct </label>");
	echo('</span>');

	//echo("<label for=\"recid\" class=\"vlabel2\">Ridetype #</label>");
	echo("<input type=\"hidden\" name=\"recid\" id=\"recid\" class=\"short\"  value=\"".$rtd['recid']."\" />");

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

	if (($_POST['recid'] == 'newrec' || $_POST['recid'] == '') ) { 

		// No protection here against inserting a duplicate IBA_Ride!

		$sql = "SELECT IBA_Ride_Title FROM ridenames WHERE IBA_Ride='".safesql($_REQUEST['IBA_Ride'])."'";
		$rs = sql_query($sql);
		$rd = $rs->fetchArray();
		if ($rd) {
			start_html("error");
			show_infoline("'".$_REQUEST['IBA_Ride']."' already exists","errormsg");
			return;
		}
		

		$sql = "INSERT INTO ridenames (IBA_Ride,IBA_Ride_Title,IBA_Ride_Desc,MaxHours";
		$sql .= ",HdrImg,TemplateID,Deleted,MilesKms,MinDistance) VALUES(";
		$sql .= "'".safesql($_REQUEST['IBA_Ride'])."'";
		$sql .= ",'".safesql($_REQUEST['IBA_Ride_Title'])."'";
		$sql .= ",'".safesql($_REQUEST['IBA_Ride_Desc'])."'";
		$sql .= ",".intval($_REQUEST['MaxHours']);
		$sql .= ",'".safesql($_REQUEST['HdrImg'])."'";
		$sql .= ",'".safesql($_REQUEST['TemplateID'])."'";
		$sql .= ",'".safesql($_REQUEST['deletethisrec'])."'";
		$sql .= ",".intval($_REQUEST['MilesKms']);
		$sql .= ",".intval($_REQUEST['MinDistance']);
		$sql .= ")";
		sql_query($sql);
	} else {
		$sql = "UPDATE ridenames SET ";
		$sql .= "IBA_Ride='".safesql($_REQUEST['IBA_Ride'])."'";
		$sql .= ",IBA_Ride_Title='".safesql($_REQUEST['IBA_Ride_Title'])."'";
		$sql .= ",IBA_Ride_Desc='".safesql($_REQUEST['IBA_Ride_Desc'])."'";
		$sql .= ",MaxHours=".intval($_REQUEST['MaxHours']);
		$sql .= ",HdrImg='".safesql($_REQUEST['HdrImg'])."'";
		$sql .= ",TemplateID='".safesql($_REQUEST['TemplateID'])."'";
		$sql .= ",Deleted='".safesql($_REQUEST['deletethisrec'])."'";
		$sql .= ",MilesKms=".intval($_REQUEST['MilesKms']);
		$sql .= ",MinDistance=".intval($_REQUEST['MinDistance']);
		$sql .= " WHERE recid=".$_REQUEST['recid'];
		sql_query("BEGIN");
		sql_query($sql);
		if ($_REQUEST['IBA_Ride_old'] != $_REQUEST['IBA_Ride']) {
			$sql = "UPDATE rides SET IBA_Ride=";
			$sql .= "'".safesql($_REQUEST['IBA_Ride'])."'";
			$sql .= " WHERE IBA_Ride=";
			$sql .= "'".safesql($_REQUEST['IBA_Ride_Old'])."'";
			sql_query($sql);
		}
		sql_query("COMMIT");
	}
	show_ridenames_listing();
	
}

?>
