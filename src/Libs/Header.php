<?php

/**
 * Header
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.6
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
	 * @param  array  $data
	 * @return array
	 */
	public static function extract(array $data): array
	{
		$results = [];
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

	public static function set(): void
	{
		$args = func_get_args();

		if (is_array($args) && @$args[0]) {
			header($args[0] . ': ' . @$args[1]);
		} elseif (is_string($args)) {
			header($args);
		}
	}

	public static function get($key=null)
	{
		$headers = array_merge(getallheaders(), self::headers_list());

		if ($key == null) {
			return $headers;
		} else {
			foreach ($headers as $hkey => $hval) {
				if ($hkey == $key) return trim($hval);
			}
		}
	}

	/**
	 * @return array
	 */
	private static function headers_list(): array
	{
		$array = [];
		foreach (headers_list() as $header) {
			$header = explode(":", $header);
			$array[trim(array_shift($header))] = trim(implode(':', $header));
		}
		return $array;
	}
}