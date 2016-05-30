<?php
	require 'config.php';
    require 'vendor/autoload.php';
    require 'tracker.php';

    $arr["success"] = false;
	if (isset($_GET['id']) && isset($_SERVER['HTTP_CLICKS99TRACK'])) {
		$track = new LinkTracker($_GET['id']);
		if (isset($track)) {
			$track->process();
			$arr["success"] = true;
		} else {
			$arr["success"] = "Link Doesnot exist";
		}
	} else {
		$arr["success"] = "No Id Provided";
	}

	echo json_encode($arr);