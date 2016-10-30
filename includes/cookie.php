<?php

class Cookie {
	protected $_prefix = "__vnative";

	protected $_domain;

	public function __construct() {
		$this->_domain = $_SERVER['HTTP_HOST'];
	}

	public function set($name, $value, $opts = array()) {
		$cname = $this->_prefix . $name;
		if (!$value) {
			$value = uniqid();
		}

		if (isset($opts['time'])) {
			$time = $opts['time'];
		} else {
			$time = time() + (86400 * 365);
		}

		if (isset($opts['path'])) {
			$path = $opts['path'];
		} else {
			$path = '/';
		}

		$domain = $this->_domain;
		$secure = false;
		$httpOnly = true;

		setcookie($cname, $value, $time, $path, $domain, $secure, $httpOnly);
		return $value;
	}

	public function get($name) {
		$cname = $this->_prefix . $name;

		if (isset($_COOKIE[$cname])) {
			return $_COOKIE[$cname];
		}
		return null;
	}
}