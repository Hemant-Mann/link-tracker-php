<?php

require_once 'autoloader.php';
use UAParser\Parser as Parser;

class Client {
	protected $_ip;

	protected $_ref;

	protected $_bot = false;

	protected $_ua;

	protected $_device;

	protected $_country;

	protected $_browser;

	protected $_os;

	public $result;

	public function __construct() {
		$this->_ip = $this->getIP();
		$this->_ua = $this->_server('HTTP_USER_AGENT', '');
		$this->_ref = $this->_server('HTTP_REFERER', '');

		$this->_parser();
		$this->_country = $this->_server('HTTP_CF_IPCOUNTRY', 'IN');

		$ans = [
			'ip' => $this->_ip,
			'ua' => $this->_ua,
			'referer' => $this->_ref,
			'device' => $this->_device,
			'country' => $this->_country,
			'bot' => $this->_bot,
			'browser' => $this->_browser,
			'os' => $this->_os
		];

		$this->result = (object) $ans;
	}

	protected function getDevice() {
		if (preg_match('/(iPad|SCH-I800|xoom|kindle)/', $this->_ua)) {
			$device = 'tablet';
		}
		else if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $this->_ua)) {
			$device = 'mobile';
		} else {
			$device = 'desktop';
		}

		return $device;
	}

	protected function _parser() {
		$parser = Parser::create();
		$result = $parser->parse($this->_ua);

		$this->_browser = $result->ua->family;
		$this->_os = $result->os->family;

		if ($result->device->family == "Spider" || preg_match('/bot|googlebot|crawler|spider|robot|crawling|curl/i', $this->_ua)) {
			$this->_bot = true;
		}

		if (preg_match('/mobile/i', $result->ua->family)) {
			$this->_device = 'mobile';
		} else {
			$this->_device = $this->getDevice();
		}
	}

	protected function _server($key, $default = null) {
		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		} return $default;
	}

	protected function getIP() {
		$ipaddress = '';
		if ($this->_server('HTTP_CLIENT_IP'))
		    $ipaddress = $this->_server('HTTP_CLIENT_IP');
		else if($this->_server('HTTP_X_FORWARDED_FOR'))
		    $ipaddress = $this->_server('HTTP_X_FORWARDED_FOR');
		else if($this->_server('HTTP_X_FORWARDED'))
		    $ipaddress = $this->_server('HTTP_X_FORWARDED');
		else if($this->_server('HTTP_FORWARDED_FOR'))
		    $ipaddress = $this->_server('HTTP_FORWARDED_FOR');
		else if($this->_server('HTTP_FORWARDED'))
		    $ipaddress = $this->_server('HTTP_FORWARDED');
		else if($this->_server('REMOTE_ADDR'))
		    $ipaddress = $this->_server('REMOTE_ADDR');
		else
		    $ipaddress = 'UNKNOWN';
		$ip = explode(",", $ipaddress);
		return $ip[0];
	}
}