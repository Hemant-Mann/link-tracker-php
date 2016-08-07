<?php
require_once 'includes/tracker.php';
require_once 'includes/cookie.php';
require_once 'includes/autoloader.php';
use Shared\Utils as Utils;

$url = '/public/_blue.gif';

$id = Utils::get('_id');
$isAd = (boolean) ((int) Utils::get('a'));
$cookie = new Cookie();
$found = $cookie->get('tracking');

$ref = Utils::server('HTTP_REFERER');
$host = Utils::server('HTTP_HOST'); $host = preg_quote($host, ".");
if (!$id || $isAd || !$found || !preg_match('#^http://'.$host.'/([A-Za-z0-9]+)$#', $ref)) {
	Utils::redirect($url);
}

$clickCol = Tracker::connectDB()->selectCollection("clicks");
try {
	$clickCol->update(['_id' => new \MongoId($id)], ['$set' => ['is_bot' => false]]);
} catch (\Exception $e) {
	// do something
}
Utils::redirect($url);

?>