<?php
use Shared\Utils as Utils;

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

		self::connectDB();
	}

	protected static function connectDB() {
		if (self::$_mongoDB) {
			return self::$_mongoDB;
		}

		global $dbconf;

		$dbconf = (object) $dbconf;
		$mongo = new \MongoClient("mongodb://".$dbconf->user.":".$dbconf->pass. $dbconf->url.":25849/".$dbconf->dbname."?replicaSet=rs-ds025849");
		$mongoDB = $mongo->selectDB($dbconf->dbname);

		self::$_mongoDB = $mongoDB;

		return $mongoDB;
	}

	public static function fallback() {
		$host = $_SERVER['HTTP_HOST'];
		// find the organization containing this proxy url
		$mongodb = self::connectDB();

		$orgCol = $mongodb->selectCollection("organizations");
		$org = $orgCol->findOne(['tdomains' => [
			'$elemMatch' => ['$eq' => $host]
		]]);
		if ($org && isset($org['url'])) {
			return $org['url'];
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
		$track = 'utm_term='. $link->_id;
		$url = Utils::removeEmoji($ad->url);

		$parsed = parse_url($url);
		if (isset($parsed['query'])) {
			$finalUrl = $url . "&utm_term=" . $link->_id;
		} else {
			$finalUrl = $url . "?utm_term=" . $link->_id;
		}

		return $finalUrl;
	}

	protected function _getDomain($link) {
		if (property_exists($link, 'app') && $link->app) {
			return $link->app . '.' . DOMAIN;
		}
		$uid = $link->user_id;
		$userCol = self::$_mongoDB->selectCollection("users");
		$orgCol = self::$_mongoDB->selectCollection("organizations");

		$user = $userCol->findOne(['_id' => $uid], ['organization_id']);
		if (!$user) {
			return DOMAIN;
		}
		$org = $orgCol->findOne(['_id' => $user['organization_id']], ['domain']);

		return $org['domain'] . '.' . DOMAIN;
	}

	public function process() {
		$mongodb = self::$_mongoDB;
		$adcol = $mongodb->selectCollection("ads");
		$clickcol = $mongodb->selectCollection("clicks");
		$linkcol = $mongodb->selectCollection("links");

		// check valid link and it's domain
		try {
			$id = new \MongoId($this->_id);
			$link = $linkcol->findOne(['_id' => $id]);
			if (!$link) {
				return false;
			} else {
				$link = Utils::toObject($link);
			}

			// find AD Details
			$ad = $adcol->findOne(['_id' => $link->ad_id]);
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

			$search = [
				'adid' => $ad->_id,
				'ipaddr' => $client->ip,
				'referer' => $client->referer,
				'country' => $client->country,
				'cookie' => $ckid,
				'pid' => $link->user_id	// It should be object
			];
			$record = $clickcol->findOne($search);
			
			if (!$record) {
				// check for fraud by searching records on the basis
				// of the ip of the user
				$doc = array_merge($search, [
					'ua' => $client->ua,
					'device' => $client->device,
					'created' => new \MongoDate(),
					'is_bot' => true
				]);

				$this->linkObj->url = $fullUrl;
				$this->linkObj->__id = $doc['_id'];
				$this->linkObj->ad = false;

				$clickcol->insert($doc);
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
