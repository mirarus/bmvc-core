<?php

/**
 * Dir
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.0
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
		$baseDir = dirname(__DIR__);

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
		$appDir = dirname(dirname(dirname(self::base())));

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
}