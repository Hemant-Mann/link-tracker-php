<?php
require_once 'includes/tracker.php';
require_once 'includes/cookie.php';
require_once 'includes/autoloader.php';
use Shared\Utils as Utils;
header('Content-Type: image/gif');

$startTime = microtime();
// Utils::log('Starting Request: ' . $startTime);
$url = dirname(__FILE__) . '/public/_blue.gif';

$id = Utils::get('_id');
$isAd = (boolean) ((int) Utils::get('a'));
$cookie = new Cookie();
$ckid = $cookie->get('tracking');

$ref = Utils::server('HTTP_REFERER');
$host = Utils::server('HTTP_HOST'); $host = preg_quote($host, ".");
if (!$id || $isAd || !$ckid || !preg_match('#^http://'.$host.'/([A-Za-z0-9]+)$#', $ref)) { // go to hell
	echo file_get_contents($url);
	return;
}

$clickCol = Tracker::connectDB()->selectCollection("clicks");
try {
	$clickCol->update(['_id' => new \MongoId($id)], ['$set' => ['is_bot' => false]]);
} catch (\Exception $e) {
	// do something
}
$endTime = microtime();
// Utils::log('id: ' . $id . ' Time taken: ' . ($endTime - $startTime));

echo file_get_contents($url);

?>