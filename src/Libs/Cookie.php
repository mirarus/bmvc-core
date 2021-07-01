<?php

/**
 * Cookie
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.0
 */

namespace BMVC\Libs;

class Cookie
{

	/**
	 * @param mixed $storage
	 * @param mixed $content
	 */
	public static function set($storage, $content=null): void
	{
		if (is_array($storage)) {
			foreach ($storage as $key => $value) {
				$_COOKIE[$key] = $value;
			}
		} else {
			$_COOKIE[$storage] = $content;
		}
	}

	/**
	 * @param string|null $storage
	 * @param string|null $child
	 */
	public static function get(string $storage=null, string $child=null)
	{
		if (is_null($storage)) {
			return $_COOKIE;
		}
		return self::has($storage, $child);
	}

	/**
	 * @param string      $storage
	 * @param string|null $child
	 */
	public static function has(string $storage, string $child=null)
	{
		if ($child === null) {
			if (isset($_COOKIE[$storage])) {
				return $_COOKIE[$storage];
			}
		} else {
			if (isset($_COOKIE[$storage][$child])) {
				return $_COOKIE[$storage][$child];
			}
		}
	}

	/**
	 * @param string      $storage
	 * @param string|null $child
	 */
	public static function delete(string $storage, string $child=null): void
	{
		if ($child === null) {
			if (isset($_COOKIE[$storage])) {
				unset($_COOKIE[$storage]);
			}
		} else {
			if (isset($_COOKIE[$storage][$child])) {
				unset($_COOKIE[$storage][$child]);
			}
		}
	}
}