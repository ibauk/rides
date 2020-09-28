<?php
/* 
 * I B A U K - importer.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */



$target_dir = __DIR__ . "/public/uploads/";

// These are defaults but, let's face it, there's never going to be a need to override
$IMPORTSPEC['xlsname']	= "import.xls";
$IMPORTSPEC['whichsheet']	= 0;
$IMPORTSPEC['FirstDataRow']	= 2;
$IMPORTSPEC['cols'] = [];
$IMPORTSPEC['type'] = 1;		/* 0 = rides, 1 = rally results */

$upload_state = 0;

// Declare psuedo fields here then load from database schema
$IGNORE_COLUMN = 'zzzzzzz';
$ENTRANT_FIELDS = [$IGNORE_COLUMN=>'ignore','RiderLast'=>'RiderLast','PillionLast'=>'PillionLast','NoKFirst'=>'NoKFirst','NoKLast'=>'NoKLast'];
$BONUS_FIELDS = [$IGNORE_COLUMN=>'ignore'];



// Load list of templates
$SPECFILES = [];
$sql = "SELECT * FROM importspecs ORDER BY specid";
$R = $db_ibauk_conn->query($sql);
while ($rd = $R->fetchArray()) {
	$SPECFILES[$rd['specid']] = $rd['specTitle'];
}

require_once("vendor\autoload.php");
use PhpOffice\PhpSpreadsheet\Spreadsheet;


function processUpload()
{
	global $IMPORTSPEC, $target_dir, $upload_state;

	//echo('<br>processing upload<br>');
	
	if(isset($_FILES['fileid']['tmp_name']) && $_FILES['fileid']['tmp_name']!='')
	  if (!move_uploaded_file($_FILES['fileid']['tmp_name'],$target_dir.$IMPORTSPEC['xlsname']))
		die('Upload failed ['.$_FILES['fileid']['tmp_name'].']==>['.$target_dir.$IMPORTSPEC['xlsname'].']');
	$upload_state = 2;
}




function showUpload()
{
	global $TAGS, $SPECFILES, $upload_state, $IMPORTSPEC;
	
	start_html("Import");
	

	echo('<h3>Import ride/rally details from spreadsheet</h3>');

	echo('<form id="uploadxls" action="index.php" method="post" enctype="multipart/form-data">');
	echo('<input type="hidden" name="c" value="import">');
	echo('<input type="hidden" name="type" value="'.$IMPORTSPEC['type'].'">');

	$myfile = (isset($_REQUEST['filename']) ? htmlentities(basename($_REQUEST['filename']))	: '');
?>
<script>
function repainthdrs(nhdrs) {
	let tab = document.getElementById('previewrows');
	if (!tab)
		return;
	for (let i = 1, row; row = tab.rows[i]; i++) /* First row is my select, not data */
		if (i <= nhdrs)
			row.classList.add('xlshdr');
		else
			row.classList.remove('xlshdr');
}
function postForm(obj) {
	console.log('Posting form');
	let frm = obj.form;
	if (!frm) return;
	console.log('Posting form: '+frm.id);
	frm.method = 'post';
	//frm.submit();
	//alert('Form posted');
	return true;
}
function uploadFile(obj) {
	console.log('Uploading file '+obj.value);
	document.getElementById('fileuploaded').value=1;
	document.getElementById('filename').value=obj.value;
	obj.form.method = 'post';
	obj.form.submit();
}
</script>

<?php
	echo('<input type="hidden" id="fileuploaded" name="fileuploaded" value="'.$upload_state.'">');

	echo('<span class="vlabel3" '.($myfile=='' ? ' style="display:none;">' : '>'));
	echo('<label for="filename">Filename</label> ');
	echo('<input type="text" readonly name="filename" id="filename"  value="'.$myfile.'" > '); 
	echo('<button onclick="document.getElementById(\'filepick\').style.display=\'block\';this.disabled=true;return false;">Choose a different file</button>');
	echo('</span>');

	echo('<span class="vlabel3"  id="filepick" style="font-size:smaller; '.($myfile!='' ? 'display:none;">' : '">'));
	//echo('<label for="fileid">Choose a file</label> ');
	echo('<input type="file" name="fileid" id="fileid" onchange="uploadFile(this);">');
	echo('</span>');


	defaultSpecfile();
	
	//print_r($_REQUEST);

	loadSpecs();
	
	$chk = isset($_REQUEST['specfile']) ? $_REQUEST['specfile'] : '';
	$i = 0;
	if (!isset($_REQUEST['fileuploaded'])) {
		echo('</form>');
		return;
	}
	
	echo('<span class="vlabel3" style="font-size:smaller;">');
	echo('<label for="specfile">File format</label> ');
	echo('<select name="specfile" id="specfile" onchange="'."document.getElementById('uploadxls').submit();".'">');
	//print_r($SPECFILES);
	
	foreach ($SPECFILES as $spc => $specs)
	{
		$i++;
		echo('<option id="specfile'.$i.'"');
		if ($chk==$spc) {
			echo(' selected ');
			$chk = FALSE;
		}
		echo(' value="'.$spc.'">'.$specs.'</option>');
	}
	echo('</select>');
	echo(' <label title="Number of rows to skip before data" for="hdrs">Header rows</label> ');
	echo('<input type="number" name="hdrs" id="hdrs" style="width:3em;" value="'.(intval($IMPORTSPEC['FirstDataRow'])-1).'" onchange="repainthdrs(this.value);"> ');
	echo('<input  id="submitform" type="submit" name="load" value="Upload" onclick="return postForm(this);">');
	echo('</span>');

	previewSpreadsheet();
	echo('</form>');
		
	
}

function defaultSpecfile()
{
	global $TAGS, $SPECFILES, $upload_state, $IMPORTSPEC, $TYPE_BONUSES, $TYPE_ENTRANTS;
	
	if (isset($_REQUEST['specfile']))
		return;
	
	$_REQUEST['specfile'] = getValueFromDB("SELECT specid FROM importspecs ORDER BY specid LIMIT 1","specid","");
	
}


function loadSpecs()
{
	global $db_ibauk_conn,$IMPORTSPEC,$TAGS;

	if (!isset($_REQUEST['specfile']))
		return;
	
	$sql = "SELECT * FROM importspecs WHERE specid='".$_REQUEST['specfile']."'";
	//echo($sql);
	$R = $db_ibauk_conn->query($sql);
	if (!$rd =$R->fetchArray())
		return;
		//die($TAGS['xlsNoSpecfile'][1]);
	

	try {
		eval($rd['fieldSpecs']);
		
		if (isset($_REQUEST['hdrs']))
			$IMPORTSPEC['FirstDataRow'] = intval($_REQUEST['hdrs']) + 1;
	} catch (Exception $e) {
		die("HELLO !!! ".$e->getMessage());
	}
	
}

function previewSpreadsheet()
{
	global $DB,$target_dir, $ENTRANT_FIELDS, $IMPORTSPEC, $IGNORE_COLUMN, $TYPE_BONUSES, $TYPE_ENTRANTS, $BONUS_FIELDS;
	
//	loadSpecs(); // already loaded now
	
	if (!isset($IMPORTSPEC['xlsname'])) 
		return;


	//echo('1 .. ');
	$sheet = openWorksheet();
	//echo('2 .. ');
	$maxcol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
	$maxrow = $sheet->getHighestRow();
	//echo('3 .. ');
	// Build column lookup by name table
	for ($i=1; $i<=$maxcol;$i++)
	{
		$colname = $sheet->getCellByColumnAndRow($i,1)->getValue();
		$XLSFIELDS[$colname]=$i;
	}
	
	
	//echo('preview-4 ');

	$hdrs = $IMPORTSPEC['FirstDataRow'];  // Skip the column headers
	$row = 0;
	
	$MYFIELDS = $IMPORTSPEC['type']==$TYPE_BONUSES ? $BONUS_FIELDS : $ENTRANT_FIELDS;
	//print_r($IMPORTSPEC['cols']);
	echo('<table id="previewrows" style="font-size:small;">');
	echo('<tr>');
	for ($col = 1; $col <= $maxcol; $col++) {
		echo('<td><select name="colmaps[]" class="fldsel">');
		$selfld = '';
		foreach($MYFIELDS as $k => $v) {
			echo('<option value="'.$k.'"');
			if (isset($IMPORTSPEC['cols']) && array_search($col - 1,$IMPORTSPEC['cols'])==$k){
				$selfld = $k;
				echo(" selected ");
			} else if ($k == $IGNORE_COLUMN && $selfld == '')
				echo(" selected ");
			echo('>'.$v.'</option>');
		}
		echo('</select></td>');
	}
	echo('</tr>');
	while ($row++ < $maxrow)
	{
		if ($row < $hdrs)
			echo('<tr class="xlshdr">');
		else
			echo('<tr>');
		$col = 0;
		while ($col++ <= $maxcol)
			echo('<td>'.$sheet->getCellByColumnAndRow($col,$row)->getValue().'</td>');
		echo('</tr>');
	}
	echo('</table>');
}


function openWorksheet()
{
	global $target_dir,$IMPORTSPEC;
	
	$filetype = \PhpOffice\PhpSpreadsheet\IOFactory::identify($target_dir.$IMPORTSPEC['xlsname']);

	$rdr = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($filetype);

	$rdr->setReadDataOnly(true);
	$rdr->setLoadSheetsOnly($IMPORTSPEC['whichsheet']);
	try {
		$xls = $rdr->load($target_dir.$IMPORTSPEC['xlsname']);
	} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
		die("Error: ".$e->getMessage());
	}

	$sheet = $xls->getSheet($IMPORTSPEC['whichsheet']);
	return $sheet;
}


if (isset($_REQUEST['fileuploaded']) && $_REQUEST['fileuploaded']=='1') {
	processUpload();
}

showUpload();
