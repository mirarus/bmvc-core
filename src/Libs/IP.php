<?php

/**
 * IP
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.3
 */

namespace BMVC\Libs;

class IP
{

	/**
	 * @var boolean
	 */
	private static $useProxy = false;

	/**
	 * @var array
	 */
	private static $trustedProxies = [];

	/**
	 * @var string
	 */
	private static $proxyHeader = 'HTTP_X_FORWARDED_FOR';

	/**
	 * @return string
	 */
	public static function get(): string
	{
		$ip = self::getFromProxy();
		if ($ip) {
			return $ip;
		}

		if (getenv("HTTP_CLIENT_IP")) {
			$ip = getenv("HTTP_CLIENT_IP");
		} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
			if (strstr($ip, ',')) {
				$tmp = explode (',', $ip);
				$ip = trim($tmp[0]);
			}
		} else {
			$ip = getenv("REMOTE_ADDR");
		}
		if ($ip) {
			return $ip;
		}

		$keys = array('X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR');
		foreach ($keys as $key) {
			if (isset($_SERVER[$key])) {
				return $_SERVER[$key];
			}
		}
	}

	/**
	 * @return mixed
	 */
	private static function getFromProxy()
	{
		if (!self::$useProxy || (isset($_SERVER['REMOTE_ADDR']) && !in_array($_SERVER['REMOTE_ADDR'], self::$trustedProxies))) {
			return false;
		}

		$header = self::$proxyHeader;
		if (!isset($_SERVER[$header]) || empty($_SERVER[$header])) {
			return false;
		}

		$ips = explode(',', $_SERVER[$header]);
		$ips = array_map('trim', $ips);
		$ips = array_diff($ips, self::$trustedProxies);

		if (empty($ips)) {
			return false;
		}

		$ip = array_pop($ips);
		return $ip;
	}
}