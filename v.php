<?php
require_once 'includes/tracker.php';
require_once 'includes/cookie.php';
require_once 'includes/autoloader.php';
require_once 'includes/vendor/autoload.php';

use Shared\Utils as Utils;
header('Content-Type: image/gif');

$url = dirname(__FILE__) . '/public/_blue.gif';

$id = Utils::get('_id');
$isAd = (boolean) ((int) Utils::get('a'));
$cookie = new Cookie();
$ckid = $cookie->get('tracking');

$ref = Utils::server('HTTP_REFERER');
$host = Utils::server('HTTP_HOST'); $host = preg_quote($host, ".");

if (!$id || $isAd || !$ckid || !preg_match('#^http://'.$host.'/([A-Za-z0-9]{24})#', $ref)) { // go to hell
	echo file_get_contents($url);
	return;
}

$clickCol = Tracker::connectDB()->selectCollection("clicks");
try {
	$clickCol->updateOne(['_id' => new MongoDB\BSON\ObjectID($id)], ['$set' => ['is_bot' => false]]);
} catch (\Exception $e) {
	// do something
}

echo file_get_contents($url);

?>