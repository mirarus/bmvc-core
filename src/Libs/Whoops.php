<?php

/**
 * Whoops
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.1
 */

namespace BMVC\Libs;

use Whoops\Run as WRun;
use Whoops\Handler\PrettyPageHandler as WPrettyPageHandler;

class Whoops
{

	/**
	 * @var array
	 */
	private static $blacklist = [
		'_GET' => [],
		'_POST' => [],
		'_FILES' => [],
		'_COOKIE' => [],
		'_SESSION' => [],
		'_SERVER' => [],
		'_ENV' => []
	];

	public static $whoops;

	public function __construct()
	{
		self::init();
	}

	public static function init(): void
	{
		$PPH = new WPrettyPageHandler;

		foreach (self::$blacklist as $key => $val) {
			foreach ($val as $arg) {
				$PPH->blacklist($key, $arg);
			}
		}
		#
		$whoops = new WRun;

		$environment = (@defined('ENVIRONMENT') ? ENVIRONMENT : ((isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] != null) ? $_ENV['ENVIRONMENT'] : 'development'));

		if ($environment == 'development') {
			$whoops->pushHandler($PPH);
		}

		$whoops->register();
		self::$whoops = $whoops;
	}

	/**
	 * @param string $name
	 * @param mixed  $keys
	 */
	public static function blacklist(string $name, $keys): void
	{
		if (is_array($keys)) {
			foreach ($keys as $key) {
				self::$blacklist[$name][] = $key;
			}
		} elseif (is_string($keys)) {
			self::$blacklist[$name][] = $keys;
		}
	}
}