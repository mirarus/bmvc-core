<?php

/**
 * Cache
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.0
 */

namespace BMVC\Libs;

use DateTime;

use function shmop_close;
use function shmop_delete;
use function shmop_open;
use function shmop_read;
use function shmop_size;
use function shmop_write;

class Cache
{

	/**
	 * @var array
	 */
	private static $caches = [];

	/**
	 * @param mixed $data
	 * @param string $name
	 * @param int    $timeout
	 */
	public static function save_cache($data, string $name, int $timeout)
	{
		$id = shmop_open(self::get_cache_id($name), "a", 0, 0);
		shmop_delete($id);
		shmop_close($id);

		$id = shmop_open(self::get_cache_id($name), "c", 0644, strlen(serialize($data)));

		if ($id) {
			self::set_timeout($name, $timeout);
			return shmop_write($id, serialize($data), 0);
		} else {
			return false;
		}
	}

	/**
	 * @param string $name
	 */
	public static function get_cache(string $name)
	{
		if (!self::check_timeout($name)) {
			$id = shmop_open(self::get_cache_id($name), "a", 0, 0);

			if ($id) {
				$data = unserialize(shmop_read($id, 0, shmop_size($id)));
			} else {
				return false;
			}

			if ($data) {
				shmop_close();
				return $data;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param string $name
	 */
	public static function get_cache_id(string $name)
	{
		$id = self::$caches;
		return $id[$name];
	}

	/**
	 * @param string $name
	 * @param int    $int
	 */
	private static function set_timeout(string $name, int $int)
	{
		$timeout = new DateTime(date('Y-m-d H:i:s'));
		date_add($timeout, date_interval_create_from_date_string("$int seconds"));
		$timeout = date_format($timeout, 'YmdHis');

		$id = shmop_open(100, "a", 0, 0);
		if ($id) {
			$tl = unserialize(shmop_read($id, 0, shmop_size($id)));
		} else {
			$tl = [];
		}
		shmop_delete($id);
		shmop_close($id);

		$tl[$name] = $timeout;
		$id = shmop_open(100, "c", 0644, strlen(serialize($tl)));
		shmop_write($id, serialize($tl), 0);
	}

	/**
	 * @param string $name
	 */
	private static function check_timeout(string $name)
	{
		$now = new DateTime(date('Y-m-d H:i:s'));
		$now = date_format($now, 'YmdHis');

		$id = shmop_open(100, "a", 0, 0);
		if ($id) {
			$tl = unserialize(shmop_read($id, 0, shmop_size($id)));
		} else {
			return true;
		}
		shmop_close($id);

		$timeout = $tl[$name];
		return (intval($now) > intval($timeout));
	}
}