<?php

/**
 * Session
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.8
 */

namespace BMVC\Libs;

class Session
{

	/**
	 * @param mixed $storage
	 * @param mixed $content
	 */
	public static function set($storage, $content=null): void
	{
		if (is_array($storage)) {
			foreach ($storage as $key => $value) {
				$_SESSION[$key] = $value;
			}
		} else {
			$_SESSION[$storage] = $content;
		}
	}

	/**
	 * @param string|null $storage
	 * @param string|null $child
	 */
	public static function get(string $storage=null, string $child=null)
	{
		if (is_null($storage)) {
			return $_SESSION;
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
			if (isset($_SESSION[$storage])) {
				return $_SESSION[$storage];
			}
		} else {
			if (isset($_SESSION[$storage][$child])) {
				return $_SESSION[$storage][$child];
			}
		}
	}

	/**
	 * @param string|null $storage
	 * @param string|null $child
	 */
	public static function delete(string $storage=null, string $child=null): void
	{
		if (is_null($storage)) {
			session_unset();
		} else {
			if ($child === null) {
				if (isset($_SESSION[$storage])) {
					unset($_SESSION[$storage]);
				}
			} else {
				if (isset($_SESSION[$storage][$child])) {
					unset($_SESSION[$storage][$child]);
				}
			}
		}
	}

	public static function destroy(): void
	{
		session_destroy();
	}
}