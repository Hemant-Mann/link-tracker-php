<?php

namespace Shared;
class Utils {
	public static function toObject($array) {
		$result = new \stdClass();
		foreach ($array as $key => $value) {
		    if (is_array($value)) {
		        $result->{$key} = self::toObject($value);
		    } else {
		        $result->{$key} = $value;
		    }
		} return $result;
	}

	public static function get($key, $default = null) {
		if (isset($_GET[$key])) {
		    return $_GET[$key];
		}
		return $default;
	}

	public static function server($key, $default = null) {
		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		} return $default;
	}

	public static function redirect($loc) {
		header("Location: $loc");
		exit();
	}

	public static function removeEmoji($text) {
	    $clean_text = "";
	    // Match Emoticons
	    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
	    $clean_text = preg_replace($regexEmoticons, '', $text);
	    // Match Miscellaneous Symbols and Pictographs
	    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
	    $clean_text = preg_replace($regexSymbols, '', $clean_text);
	    // Match Transport And Map Symbols
	    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
	    $clean_text = preg_replace($regexTransport, '', $clean_text);
	    // Match Miscellaneous Symbols
	    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
	    $clean_text = preg_replace($regexMisc, '', $clean_text);
	    // Match Dingbats
	    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
	    $clean_text = preg_replace($regexDingbats, '', $clean_text);
	    return $clean_text;
	}

	public static function getMongoId($value) {
		return $value->{'$id'};
	}
}