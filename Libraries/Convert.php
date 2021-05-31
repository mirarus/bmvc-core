<?php

/**
 * Convert
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.0
 */

namespace BMVC\Libs;
use stdClass;

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
	 * @param  mixed        $arg
	 * @param  bool|boolean $array
	 * @return mixed
	 */
	public static function __($arg, bool $array=true)
	{
		if ($array == true) {
			return self::arr_obj($arg);
		} else {
			return self::obj_arr($arg);
		}
	}
}