<?php

/**
 * Session
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.5
 */

namespace BMVC\Libs;

use BMVC\Libs\Request;

class Session
{

	/**
	 * @param mixed $storage
	 * @param mixed $content
	 * @return array
	 */
	public static function set($storage, $content=null): array
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
	 * @param  string|null $storage
	 * @param  string|null $child
	 * @return mixed
	 */
	public static function get(string $storage=null, string $child=null)
	{
		if (is_null($storage)) {
			return $_SESSION;
		}
		
		return self::has($storage, $child);
	}

	/**
	 * @param  string      $storage
	 * @param  string|null $child
	 * @return mixed
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
	 * @param  string|null $storage
	 * @param  string|null $child
	 * @return mixed
	 */
	public static function delete(string $storage=null, string $child=null)
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

	public static function destroy()
	{
		session_destroy();
	}

	private static function generateHash()
	{
		if (Request::getIp() && Request::getUserAgent()) {
			return md5(sha1(md5(Request::getIp() . 'u2LMq1h4oUV0ohL9svqedoB5LebiIE4z' . Request::getUserAgent())));
		}
		return md5(sha1(md5('u2LMq1h4oUV0ohL9svqedoB5LebiIE4z')));
	}
}