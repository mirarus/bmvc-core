<?php

/**
 * Convert
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.1
 */

namespace BMVC\Libs;
use stdClass;
use SimpleXMLElement;

class Convert
{

	/**
	 * @param  array  $array
	 * @return object
	 */
	public static function arr_obj(array $array): object
	{
		$object = new stdClass();
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					$value = self::arr_obj($value);
				}
				$object->$key = $value;
			}
		}
		return $object;
	}

	/**
	 * @param  object $object
	 * @return array
	 */
	public static function obj_arr(object $object): array
	{
		$array = [];
		if (is_object($object)) {
			foreach ($object as $key => $value) {
				if (is_object($value)) {
					$value = self::obj_arr($value);
				}
				$array[$key] = $value;
			}
		}
		return $array;
	}

	/**
	 * @param array       $array
	 * @param object|null &$xml
	 */
	public static function arr_xml(array $array, object &$xml=null)
	{
		if ($xml == null) {
			$xml = new SimpleXMLElement('<result/>');
		}
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					self::arr_xml($value, $xml->addChild($key));
				} else {
					$xml->addChild($key, $value);
				}
			}
		}
		return $xml->asXML();
	}
}