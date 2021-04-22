<?php

/**
 * Header
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.3
 */

namespace BMVC\Libs;

class Header
{

	/**
	 * @var array
	 */
	private static $special = [
		'CONTENT_TYPE',
		'CONTENT_LENGTH',
		'PHP_AUTH_USER',
		'PHP_AUTH_PW',
		'PHP_AUTH_DIGEST',
		'AUTH_TYPE'
	];

	/**
	 * @param array $data
	 */
	public static function extract(array $data)
	{
		$results = array();
		foreach ($data as $key => $value) {
			$key = strtoupper($key);
			if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || in_array($key, self::$special)) {
				if ($key === 'HTTP_CONTENT_LENGTH') {
					continue;
				}
				$results[$key] = $value;
			}
		}
		return $results;
	}
}