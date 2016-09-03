<?php
use Shared\Utils as Utils;
require_once 'vendor/autoload.php';

include 'cookie.php';
include 'client.php';
include 'config.php';
require_once 'config.constants.php';

class Tracker {
	protected $_id;

	protected $_cookie;

	protected $_client;

	protected static $_mongoDB = null;

	protected $linkObj;

	public function __construct($id) {
		$this->_id = $id;
		$this->_cookie = new Cookie();
		$client = new Client();
		$this->_client = $client->result;
	}

	public static function connectDB() {
		if (self::$_mongoDB) {
			return self::$_mongoDB;
		}

		global $dbconf;

		$dbconf = (object) $dbconf;
		$mongodb = new MongoDB\Client("mongodb://" . $dbconf->user . ":" . $dbconf->pass . "@" . $dbconf->url ."/" .$dbconf->dbname . "?" . $dbconf->opts, [
				'server' => [
					'socketOptions' => [ 'connectTimeoutMS' => '300000', 'socketTimeoutMS' => '300000']
				]
			]);
		self::$_mongoDB = $mongodb->selectDatabase($dbconf->dbname);

		return self::$_mongoDB;
	}

	public static function fallback() {
		$host = $_SERVER['HTTP_HOST'];
		// find the organization containing this proxy url
		$mongodb = self::connectDB();

		$orgCol = $mongodb->selectCollection("organizations");
		$linkCol = $mongodb->selectCollection("links");
		$start = strtotime('now');
		$org = $orgCol->findOne();
		$end = strtotime('now');
		var_dump($end - $start);
		var_dump($org);
		die('complete');

		if ($org) {
			if ($org['url']) {
				return $org['url'];	
			} else {
				return $org['domain'] . '.' . DOMAIN;
			}
		}
		return DOMAIN;
	}

	public function getResult() {
		return $this->linkObj;
	}

	protected function _ckid() {
		$cookie = $this->_cookie;
		$ckid = $cookie->get('tracking');
		if (!$ckid) {
			$ckid = $cookie->set('tracking', null);
		}
		return $ckid;
	}

	protected function redirectUrl($link, $ad) {
		$url = Utils::removeEmoji($ad->url);

		$parsed = parse_url($url);
		$query = []; $modify = false;
		if (isset($parsed['query'])) {
			parse_str($parsed['query'], $query);
		} else {
			$modify = true;
		}

		if (!(isset($query['utm_source']) || isset($query['utm_campaign']))) {
			$modify = true;
		}
		if ($modify) {
			$query['utm_source'] = sprintf('%s', $link->user_id);
			$query['utm_medium'] = 'affiliate';
			$query['utm_term'] = $this->_client->referer;
			$query['utm_content'] = urlencode($ad->title);
			$query['utm_campaign'] = sprintf('%s', $ad->_id);

			$finalUrl = 'http://' . $parsed['host'] . $parsed['path'] . '?' . http_build_query($query);
		} else {
			$finalUrl = $url;
		}

		return $finalUrl;
	}

	public function process() {
		$mongodb = self::connectDB();
		$adcol = $mongodb->selectCollection("ads");
		$clickcol = $mongodb->selectCollection("clicks");
		$linkcol = $mongodb->selectCollection("links");

		// check valid link and it's domain
		try {
			$id = new MongoDB\BSON\ObjectID($this->_id);
			$link = $linkcol->findOne(['_id' => $id], [
				'projection' => [
					'_id' => 1, 'user_id' => 1, 'domain' => 1, 'ad_id' => 1
				]
			]);
			if (!$link) {
				return false;
			} else {
				$link = Utils::toObject($link);
			}

			// find AD Details
			$ad = $adcol->findOne(['_id' => $link->ad_id], [
				'projection' => [
					'_id' => 1, 'title' => 1, 'live' => 1, 'description' => 1,
					'image' => 1, 'url' => 1, 'user_id' => 1
				]
			]);
			if (!$ad) return false;
			$ad = Utils::toObject($ad);
			$fullUrl = $this->redirectUrl($link, $ad);

			$ckid = $this->_ckid();
			$client = $this->_client;

			// Link is verified make the obj to be set in view
			$img = ['width' => 600, 'height' => 315];
			$arr = [
				'title' => $ad->title,
				'description' => $ad->description,
				'image' => 'http://cdn.'. $_SERVER['HTTP_HOST'] ."/images/". $ad->image,
				'width' => $img['width'],
				'height' => $img['height'],
				'url' => Utils::removeEmoji($ad->url),
				'subdomain' => $link->domain,
				'ad' => true,
				'__id' => $ad->_id
			];
			$this->linkObj = Utils::toObject($arr);

			// If visitor is valid
			$live = (property_exists($ad, 'live')) ? $ad->live : false;
			if ($client->bot || !$live) {
				return true;
			}

			//GA
			$this->_ga($ad, $ckid, $client, $link);

			$search = [
				'adid' => $ad->_id,
				'ipaddr' => $client->ip,
				'cookie' => $ckid,
				'pid' => $link->user_id	// It should be object
			];
			$record = $clickcol->findOne($search, ['projection' => ['_id' => 1]]);
			
			if (!$record) {
				// check for fraud by searching records on the basis
				// of the ip of the user
				$doc = array_merge($search, [
					'ua' => $client->ua,
					'referer' => $client->referer,
					'device' => $client->device,
					'country' => $client->country,
					'created' => new MongoDB\BSON\UTCDateTime(strtotime('now') * 1000),
					'is_bot' => true
				]);
				
				$result = $clickcol->insertOne($doc);
				$this->linkObj->url = $fullUrl;
				$this->linkObj->__id = $result->getInsertedId();
				$this->linkObj->ad = false;
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	protected function _ga($ad, $ckid, $client, $link) {
		$params = array(
			'v' => 1,
			'tid' => MGAID,
			'ds' => $ad->user_id,
			'cid' => $ckid,
			'uip' => $client->ip,
			'ua' => $client->ua,
			'dr' => $client->referer,
			'ci' => $ad->_id,
			'cn' => $ad->title,
			'cs' => $link->user_id,
			'cm' => 'click',
			'cc' => $ad->title,
			't' => 'pageview',
			'dl' => URL,
			'dh' => $_SERVER['HTTP_HOST'],
			'dp' => $_SERVER['REQUEST_URI'],
			'dt' => $ad->title
		);
		
		$curl = curl_init();
		$gaurl = 'https://www.google-analytics.com/collect?'.http_build_query($params);
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $gaurl,
		    CURLOPT_USERAGENT => $client->ua,
		));
		$resp = curl_exec($curl);
		curl_close($curl);
	}
}
