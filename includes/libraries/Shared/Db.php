<?php
namespace Shared;

class Db {
	public static function convertType($value, $type = 'id') {
		switch ($type) {
			case 'id':
				return Utils::mongoObjectId($value);

			case 'regex':
				return Utils::mongoRegex($value);
			
			case 'date':
			case 'datetime':
			case 'time':
				return self::time($value);
		}
		return '';
	}

	public static function time($date = null) {
		if ($date) {
			$time = strtotime($date);
		} else {
			$time = strtotime('now');
		}

		return new \MongoDB\BSON\UTCDateTime($time * 1000);
	}

	public static function isType($value, $type = '') {
		switch ($type) {
			case 'id':
				return is_object($value) && is_a($value, 'MongoDB\BSON\ObjectID');

			case 'regex':
				return is_object($value) && is_a($value, 'MongoDB\BSON\Regex');

			case 'document':
				return (is_object($value) && (
					is_a($value, 'MongoDB\Model\BSONArray') ||
					is_a($value, 'MongoDB\Model\BSONDocument') ||
					is_a($value, 'stdClass')
				));
			
			case 'date':
			case 'datetime':
			case 'time':
				return is_object($value) && is_a($value, 'MongoDB\BSON\UTCDateTime');

			default:
				return is_object($value) && is_a($value, 'MongoDB\BSON\ObjectID');
		}
	}
}