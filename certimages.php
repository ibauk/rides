<?php
/*
 * I B A U K - .php
 *
 * Copyright (c) 2017 Bob Stammers
 *
 */



$folder_path = 'certificates/images/'; //image's folder path

$num_files = glob($folder_path . "*.{JPG,jpg,gif,png,bmp}", GLOB_BRACE);
 
function chooseImage($callback_url)
{
	global $folder_path, $num_files;
	
	$imgFile = $_REQUEST['imgFile'];
		
	$folder = opendir($folder_path);
 
	echo('<div id="image_gallery">');
	while(false !== ($file = readdir($folder))) 
	{
		$file_path = $folder_path.$file;
		$extension = strtolower(pathinfo($file ,PATHINFO_EXTENSION));
		if($extension=='jpg' || $extension =='png' || $extension == 'gif' || $extension == 'bmp') 
		{
			echo('<a href="'.$callback_url."&img=".$file.'">');
			echo('<img ');
			if ($file == $imgFile)
				echo(' class="selected" ');
			echo(' src="'.$file_path.'" height="120" /></a>');
		}
	}
	echo('</div>');
		
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Creating an Image Gallery From Folder using PHP</title>
<style type="text/css">
#image_gallery img.selected
{
	border: double;
	
	padding: 1em;4
}
#image_gallery img
{
 width:auto;
 box-shadow:0px 0px 20px #cecece;
 transform: scale(0.7);
 transition-duration: 0.6s; 
}
#image_gallery img:hover
{
  box-shadow: 20px 20px 20px #dcdcdc;
 transform: scale(0.8);
 transition-duration: 0.6s;
}
</style>
</head>
<body>
<?php

chooseImage($_REQUEST['callback'].'&uri='.$_REQUEST['uri'].'&imgid='.$_REQUEST['imgid'].'&imgFile='.$_REQUEST['imgFile']);
exit;







$folder_path = 'certificates/images/'; //image's folder path

$num_files = glob($folder_path . "*.{JPG,jpg,gif,png,bmp}", GLOB_BRACE);

if(isset($_POST['btn-upload']))
{
     $pic = rand(1000,100000)."-".$_FILES['pic']['name'];
    $pic_loc = $_FILES['pic']['tmp_name'];
     $folder="uploaded_files/";
     if(move_uploaded_file($pic_loc,$folder.$pic))
     {
        ?><script>alert('successfully uploaded');</script><?php
     }
     else
     {
        ?><script>alert('error while uploading file');</script><?php
     }
}

$folder = opendir($folder_path);
 
if($num_files > 0)
{
 while(false !== ($file = readdir($folder))) 
 {
  $file_path = $folder_path.$file;
  $extension = strtolower(pathinfo($file ,PATHINFO_EXTENSION));
  if($extension=='jpg' || $extension =='png' || $extension == 'gif' || $extension == 'bmp') 
  {
   ?>
            <a href="<?php echo $file_path; ?>"><img src="<?php echo $file_path; ?>"  height="250" /></a>
            <?php
  }
 }
}
else
{
 echo "the folder was empty !";
}
closedir($folder);
?>
<form action="" method="post" enctype="multipart/form-data">
<input type="file" name="pic" />
<button type="submit" name="btn-upload">upload</button>
</form>
</body>
</html>
