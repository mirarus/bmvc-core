<?php

/**
 * Dir
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.4
 */

namespace BMVC\Libs;

class Dir
{

	/**
	 * @param  string|null $dir
	 * @return string
	 */
	public static function base(string $dir=null): string
	{
		$baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

		if ($dir !== null) {
			return $baseDir . $dir;
		} else {
			return $baseDir;
		}
	}

	/**
	 * @param  string|null $dir
	 * @return string
	 */
	public static function app(string $dir=null): string
	{
		$appDir = dirname(dirname(dirname(self::base()))) . DIRECTORY_SEPARATOR;

		if ($dir !== null) {
			return $appDir . $dir;
		} else {
			return $appDir;
		}
	}

	/**
	 * @param  string|null $type
	 * @param  string|null $dir
	 * @return mixed
	 */
	public static function get(string $type=null, string $dir=null)
	{
		if ($type == 'base') {
			return self::base($dir);
		} elseif ($type == 'app') {
			return self::app($dir);
		} else {
			return [
				'base' => self::base($dir),
				'app' => self::app($dir)
			];
		}
	}

	/**
	 * @param  string      $dir
	 * @param  string|null $type
	 * @return boolean
	 */
	public static function check(string $dir, string $type=null): bool
	{
		return self::is_dir($dir, $type);
	}

	/**
	 * @param  string      $dir
	 * @param  string|null $type
	 * @return boolean
	 */
	public static function is_dir(string $dir, string $type=null): bool
	{
		if ($type == 'app') {
			$dir = Dir::app($dir);
		} elseif ($type == 'base') {
			$dir = Dir::base($dir);
		}
		return (is_dir($dir) && opendir($dir));
	}
}