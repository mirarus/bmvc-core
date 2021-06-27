<?php

/**
 * Hook
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.2
 */

namespace BMVC\Libs;
use Closure;

class Hook
{

	/**
	 * @param string       $name
	 * @param Closure|null $callback
	 * @param string|null  $value
	 */
	public static function hook_load(string $name, Closure $callback=null, string $value=null)
	{
		static $events = [];
		if ($callback !== null) {
			if ($callback) {
				$events[$name][] = $callback;
			} else {
				unset($events[$name]);
			}
		} elseif (isset($events[$name])) {
			asort($events[$name]);
			foreach ($events[$name] as $callback) {
				$value = call_user_func($callback, $value);
			}
			return $value;
		}
	}

	/**
	 * @param string       $name
	 * @param Closure|null $callback
	 */
	public static function add_action(string $name, Closure $callback=null)
	{
		return self::hook_load($name, $callback, null);
	}

	/**
	 * @param string      $name
	 * @param string|null $value
	 */
	public static function do_action(string $name, string $value=null)
	{
		return self::hook_load($name, null, $value);
	}

	/**
	 * @param string $name
	 */
	public static function remove_action(string $name)
	{
		return self::hook_load($name, false);
	}
}