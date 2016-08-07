<?php
date_default_timezone_set('Asia/Kolkata');
require_once 'includes/config.constants.php';
require_once 'includes/autoloader.php';
require 'includes/tracker.php';
use Shared\Utils as Utils;

$lid = Utils::get('id');
if (!$lid) {
	$domain = Tracker::fallback();
	include 'view/static.php';
	return;
}

$tracker = new Tracker($lid);
$valid = $tracker->process();

if ($valid === false) {
	$domain = Tracker::fallback();
    include 'view/static.php';
    return;
}

$link = $tracker->getResult();
include 'view/dynamic.php';