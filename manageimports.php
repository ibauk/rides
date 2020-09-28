<?php
/* 
 * I B A U K - manageimports.php
 *
 * This is the SQLITE version
 * 
 * 
 * Copyright (c) 2020 Bob Stammers
 *
 */


$target_dir = __DIR__ . "/public/uploads/";


// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $max_size = parse_size(ini_get('post_max_size'));

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}

function parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}


function upload() {

	global $target_dir;
	

	$myfiles = scandir($target_dir);
	foreach ($myfiles as $myf)
		if ($myf <> '' && $myf <> '..') {
			$target_file = $target_dir . $myf;	
			unlink($target_file);
		}

	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

	// Check file size
	if ($_FILES["fileToUpload"]["size"] > file_upload_max_size()) 
		show_infoline("Sorry, your file is too large.","errormsg");
	else if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
		show_infoline("The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.","infohilite");
	else 
		show_infoline("Sorry, there was an error uploading your file.","errormsg");
}



	if (isset($_REQUEST['uploadfile']))
		upload();
 

	$myfiles = scandir($target_dir);
 
	start_html("Starting an import");
	$R = sql_query("SELECT count(*) As Rex FROM bulkimports");
	$rr = $R->fetchArray();
	$nrex = $rr['Rex'];
?>
<h2>Import rides/rally results from spreadsheet</h2>

<p>You can import details of rides or rally results from a spreadsheet in a recognised formats by uploading it to the server and completing this process.</p>
<form action="index.php?c=startimport" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload" onchange="document.getElementById('uploadfile').disabled=false;">
    <input type="submit" value="Upload file" name="uploadfile" id="uploadfile" disabled>
</form>

<hr />
<?php
	if ($nrex > 0) {
		echo("<p class=\"infohilite\">The import table is not empty. It contains $nrex records. If you carry on those existing records will be replaced by this upload. </p>");
		echo("<p><form method=\"get\" action=\"index.php\">");
		echo("<input type=\"hidden\" name=\"cmd\" value=\"loadimports\">");
		echo("<input type=\"submit\" value=\"View existing records\">");
		echo("</form></p><hr />");
	}
?>
<form method="post" onsubmit="return validateLoad();">
<input type="hidden" name="cmd" value="importxls">
<?php
	if (count($myfiles) < 3)  { // . & .. are the first 2 
		echo('</body></html>');
		exit;
	}
	if ($nrex > 0) {
?>		
		<strong>
		<label for="useforce">Check to confirm overwrite of existing records </label>
		<input type="checkbox" id="useforce" name="force">
		</strong>
<?php		
	}
	
?>	
<p>You must choose one of these existing specifications. If the file format has changed or you need a completely different specification you must create a new <em>xxxspec.php</em> file, upload it to the server then modify this script, <em><?php echo(basename(__FILE__));?></em>, to include the new option.</p>
<script>
function swapspec() {
	
	let spec = document.querySelector('input[name = "spec"]:checked');
	let ir = spec.getAttribute('data-isrally');
	document.getElementById('ridedatespan').style.display=(ir==0 ? 'inline' : 'none');
}
function validateLoad() {

	console.log('validateLoad called');
	let force = document.getElementById('useforce');
	if (force && !force.checked) {
		alert('You must check the box to overwrite the existing records!');
		return false;
	}
	let spec = document.querySelector('input[name = "spec"]:checked');
	let ir = spec.getAttribute('data-isrally')==1;
	if (ir) {
		if (document.getElementById('eventid').value=='') {
			alert('You must supply an EventID');
			return false;
		}
	} else {
		if (document.getElementById('ridedate').value=='') {
			alert('You must supply a ride date');
			return false;
		}
	}
	return true;
}
function checkLoad()
{
	let spec = document.querySelector('input[name = "spec"]:checked');
	let ir = spec.getAttribute('data-isrally')=='1';
	let ls = document.getElementById('loadspreadsheet');
	if (ir) 
		ls.disabled = document.getElementById('eventid').value == '';
	else
		ls.disabled = document.getElementById('ridedate').value == '';
}
</script>
<?php 
/*
 *
 *   I M P O R T   S P E C S   G O   H E R E
 *
 */

echo('<input type="radio" name="spec" id="spec-rblr" data-isrally="0" checked value="rblr" onchange="swapspec();"> <label for="spec-rblr">RBLR1000 finishers</label><br>');
echo('<input type="radio" name="spec" id="spec-sm" data-isrally="1" value="sm" onchange="swapspec();"> <label for="spec-sm">Rally results from ScoreMaster</label><br>');

if (false) {
include("rblrspec.php"); $ev = $IMPORTSPEC['eventid']; $xl = $IMPORTSPEC['xlsname']; $sd = $IMPORTSPEC['ride_rally'];
echo("<input type=\"radio\" name=\"spec\" id=\"spec-rblr\" data-showdate=\"$sd\" data-eventid=\"$ev\" data-xlsname=\"$xl\" onchange=\"swapspec();\"  value=\"rblr\"><label for=\"spec-rblr\"> RBLR1000</label><br />");
include("smpec.php"); $ev = $IMPORTSPEC['eventid']; $xl = $IMPORTSPEC['xlsname']; $sd = $IMPORTSPEC['ride_rally'];
echo("<input type=\"radio\" name=\"spec\" id=\"spec-sm\" data-eventid=\"$ev\" data-showdate=\"$sd\" data-xlsname=\"$xl\" onchange=\"swapspec();\" checked value=\"sm\"> <label for=\"spec-sm\">Rally results from ScoreMaster</label><br />");
}
?>
<br /><br />
<label for="file">File containing data </label>

<?php
echo('<input type="text" readonly tabindex="-1" title="The file currently uploaded ready for processing" name="file" id="file" value="');
foreach($myfiles as $myf) 
	if ($myf <> '.' && $myf <> '..') {
		echo($myf);
		break;
	}
echo('">');
?>

<br /><br />
<label for="eventid">Event description </label><input type="text" title="For rides this is an optional event descriptor; For rallies this is the short label as it appears in the rally results listing. eg 'BBR19'" name="eventid" id="eventid" onchange="checkLoad();"><br /><br />
<span id="ridedatespan"><label for="ridedate">Ride date (n/a for rallies) </label><input type="date" id="ridedate" name="ridedate" onchange="checkLoad();"><br /><br /></span>
<input type="submit" id="loadspreadsheet" disabled value="Load spreadsheet" >
</form>


<script>swapspec();</script>
</body>
</html>




