<?php
use Shared\Utils as Utils;
use Shared\Db as Db;
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
					'socketOptions' => [ 'connectTimeoutMS' => '300000', 'socketTimeoutMS' => '300000' ]
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
		$org = $orgCol->findOne(['tdomains' => [
			'$elemMatch' => ['$eq' => $host]
		]]);
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

	protected function redirectUrl($link, $ad, $click = null) {
		$url = Utils::removeEmoji($ad->url);
		$url = html_entity_decode($url);

		$parsed = parse_url($url); $query = [];
		if (isset($parsed['query'])) {
			parse_str($parsed['query'], $query);
		}
		
		$allowedParams = ['aff_id' => Utils::getMongoId($link->user_id), 'ad_id' => Utils::getMongoId($ad->_id), 'adv_id' => Utils::getMongoId($ad->user_id), 'tdomain' => Utils::server('HTTP_HOST')];
		if (is_object($click) && property_exists($click, '_id')) {
			$allowedParams['click_id'] = Utils::getMongoId($click->_id);
		}
		$query = Utils::queryParams($query, $allowedParams);

		return Utils::makeUrl($parsed, $query);
	}

	public function process() {
		$mongodb = self::connectDB();
		$adcol = $mongodb->selectCollection("ads");
		$clickcol = $mongodb->selectCollection("clicks");
		$linkcol = $mongodb->selectCollection("links");
		$commCol = $mongodb->selectCollection("commissions");

		// check valid link and it's domain
		try {
			$id = Db::convertType($this->_id, 'id');
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
				'description' => $ad->description ?? $ad->title,
				'image' => 'http://cdn.'. $_SERVER['HTTP_HOST'] ."/images/". $ad->image,
				'width' => $img['width'],
				'height' => $img['height'],
				'url' => $fullUrl,
				'subdomain' => $link->domain,
				'ad' => true,
				'__id' => $ad->_id,
				'cookie' => null
			];

			$comm = $commCol->count(['model' => Utils::mongoRegex('cpi|cpa'), 'ad_id' => $link->ad_id]);
			if ($comm !== 0) {
				$arr['cookie'] = $ckid;
			}
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
				if ($client->referer) {
					$ref = parse_url($client->referer, PHP_URL_HOST);
				} else {
					$ref = $client->referer;
				}

				$doc = array_merge($search, [
					'browser' => $client->browser,
					'referer' => $ref,
					'device' => $client->device,
					'country' => $client->country,
					'created' => Db::time(),
					'is_bot' => true
				]);

				if ($client->os) {
					$doc['os'] = $client->os;
				}

				// If extra Param
				$p1 = Utils::get('p1'); $p2 = Utils::get('p2');
				if ($p1 && $p2) {
					$doc['p1'] = $p1; $doc['p2'] = $p2;
				}
				
				$result = $clickcol->insertOne($doc);
				$this->linkObj->__id = $result->getInsertedId();
				$insertedObj = Utils::toObject(['_id' => $this->linkObj->__id]);
				$this->linkObj->url = $this->redirectUrl($link, $ad, $insertedObj);
				$this->linkObj->ad = false;
			} else {
				$insertedObj = Utils::toObject($record);
				$this->linkObj->url = $this->redirectUrl($link, $ad, $insertedObj);
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
