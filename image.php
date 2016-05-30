<?php
header ("Cache-Control: max-age=6000");
if (isset($_GET['file'])) {
	$file = $_GET['file'];
	$filename = basename($file);
	$file_extension = strtolower(substr(strrchr($filename,"."),1));

	switch( $file_extension ) {
	    case "gif": $ctype="image/gif"; break;
	    case "png": $ctype="image/png"; break;
	    case "jpeg":
	    case "jpg": $ctype="image/jpeg"; break;
	    default:
	}

	header('Content-type: ' . $ctype);
	$img = explode(".", $file);
	readfile("/var/www/www/public/assets/uploads/images/resize/{$img[0]}-600x315.{$img[1]}");
} else {
	header('Content-type: image/png');
	readfile("/var/www/ctracker/includes/logo.png");
}