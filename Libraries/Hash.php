<?php

/**
 * Hash
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.2
 */

namespace BMVC\Libs;

use Exception;

class Hash
{

	/**
	 * @var integer
	 */
	private static $cost = 10;

	/**
	 * @param string $value
	 * @param array  $options
	 */
	public static function make(string $value, array $options=[])
	{
		if (!array_key_exists('cost', $options)) {
			$options['cost'] = self::$cost;
		}
		$hash = password_hash($value, PASSWORD_DEFAULT, $options);
		if ($hash === false) {
			throw new Exception('Hash Error! | Bcrypt hash is not supported.');
		}
		return $hash;
	}

	/**
	 * @param string $value
	 * @param string $hashedValue
	 */
	public static function check(string $value, string $hashedValue)
	{
		return password_verify($value, $hashedValue);
	}

	/**
	 * @param string $hashedValue
	 * @param array  $options
	 */
	public static function rehash(string $hashedValue, array $options=[])
	{
		if (!array_key_exists('cost', $options)) {
			$options['cost'] = self::$cost;
		}
		return password_needs_rehash($hashedValue, PASSWORD_DEFAULT, $options);
	}
}