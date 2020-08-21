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
	
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	$uploadOk = 1;

	// Check file size
	if ($_FILES["fileToUpload"]["size"] > file_upload_max_size()) {
		show_infoline("Sorry, your file is too large.","errormsg");
		$uploadOk = 0;
	}
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
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

<p>You can import details of rides or rally results from a spreadsheet in one of several recognised formats by uploading it to the server and completing the form below.</p>
<form action="index.php?c=startimport" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload file" name="uploadfile">
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
<form method="post">
<input type="hidden" name="cmd" value="importxls">
<?php
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
	
	var spec = document.querySelector('input[name = "spec"]:checked');
	var ev = spec.getAttribute('data-eventid');
	document.querySelector('input[name = "eventid"]').value = spec.getAttribute('data-eventid');
	document.querySelector('select[id="file"]').value = spec.getAttribute('data-xlsname');
}
</script>
<?php 
/*
 *
 *   I M P O R T   S P E C S   G O   H E R E
 *
 */

include("rblrspec.php"); $ev = $IMPORTSPEC['eventid']; $xl = $IMPORTSPEC['xlsname']; 
echo("<input type=\"radio\" name=\"spec\" id=\"spec-rblr\" data-eventid=\"$ev\" data-xlsname=\"$xl\" onchange=\"swapspec();\" checked value=\"rblr\"><label for=\"spec-rblr\"> RBLR1000</label><br />");
include("bbrspec.php"); $ev = $IMPORTSPEC['eventid']; $xl = $IMPORTSPEC['xlsname']; 
echo("<input type=\"radio\" name=\"spec\" data-eventid=\"$ev\" data-xlsname=\"$xl\" onchange=\"swapspec();\" value=\"bbr\"> Brit Butt<br />");
include("bblspec.php"); $ev = $IMPORTSPEC['eventid']; $xl = $IMPORTSPEC['xlsname']; 
echo("<input type=\"radio\" name=\"spec\" data-eventid=\"$ev\" data-xlsname=\"$xl\" onchange=\"swapspec();\" value=\"bbl\"> Brit Butt Light<br />");
include("jor18spec.php"); $ev = $IMPORTSPEC['eventid']; $xl = $IMPORTSPEC['xlsname']; 
echo("<input type=\"radio\" name=\"spec\" data-eventid=\"$ev\" data-xlsname=\"$xl\" onchange=\"swapspec();\" value=\"jor\"> Jorvic<br />");
?>
<br /><br />
<label for="file">File containing data </label><select name="file" id="file">

<?php
foreach($myfiles as $myf) {
	if ($myf <> '.' && $myf <> '..')
		echo("<option value=\"$myf\">$myf</options>");
}
?>
</select>
<br /><br />
<label for="eventid">Event description </label><input type="text" placeholder="BBR17, &quot;RBLR 1000 ('17)&quot;" name="eventid" id="eventid" ><br /><br />
<label for="ridedate">Ride date (n/a for rallies) </label><input type="date" id="ridedate" name="ridedate"><br /><br />
<input type="submit" value="Load spreadsheet">
</form>


<script>swapspec();</script>
</body>
</html>




