<?php
date_default_timezone_set('Asia/Kolkata');
require_once 'config.constants.php';
require_once 'autoloader.php';
require 'tracker.php';
use Shared\Utils as Utils;

$clickCol = Tracker::connectDB()->selectCollection("clicks");
// Remove Useless data to prevent query overhead
$clickCol->remove(['is_bot' => true]);

