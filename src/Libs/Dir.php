<?php

/**
 * Dir
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 2.3
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
			return self::replace($baseDir . $dir);
		} else {
			return self::replace($baseDir);
		}
	}

	/**
	 * @param  string|null $dir
	 * @return string
	 */
	public static function app(string $dir=null): string
	{
		$appDir = dirname(dirname(dirname(dirname(self::base())))) . DIRECTORY_SEPARATOR;

		if ($dir !== null) {
			return self::replace($appDir . $dir);
		} else {
			return self::replace($appDir);
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
	public static function is_dir(string $dir, string $type=null): bool
	{
		if ($type == 'app') {
			$dir = self::app($dir);
		} elseif ($type == 'base') {
			$dir = self::base($dir);
		}
		return (is_dir($dir) && opendir($dir));
	}

	/**
	 * @param mixed $arg
	 */
	public static function replace($arg=null)
	{
		return @str_replace(['/', '//', '\\'], DIRECTORY_SEPARATOR, $arg);
	}
	
	/**
	 * @param  array  $arg
	 * @return string
	 */
	public static function implode(array $arg): string
	{
		return @implode(DIRECTORY_SEPARATOR, $arg);
	}

	/**
	 * @param  string $arg
	 * @return array
	 */
	public static function explode(string $arg): array
	{
		return @explode(DIRECTORY_SEPARATOR, $arg);
	}

	/**
	 * @param  string|null $arg
	 * @return string
	 */
	public static function trim(string $arg=null): string
	{
		return @trim(self::replace($arg), DIRECTORY_SEPARATOR);
	}

	/**
	 * @param  string       $dir
	 * @param  string|null  $type
	 * @param  int|integer  $perms
	 * @param  bool|boolean $recursive
	 * @return boolean
	 */
	public static function mk_dir(string $dir, string $type=null, int $perms=0777, bool $recursive=true): bool
	{
		if ($type == 'app') {
			$dir = self::app($dir);
		} elseif ($type == 'base') {
			$dir = self::base($dir);
		}

		if (!self::is_dir($dir)) {
			return (bool) @mkdir($dir, $perms, $recursive);
		} else {
			return false;
		}
	}

	/**
	 * @param  string      $dir
	 * @param  string|null $type
	 * @return boolean
	 */
	public static function rm_dir(string $dir, string $type=null): bool
	{
		if ($type == 'app') {
			$dir = self::app($dir);
		} elseif ($type == 'base') {
			$dir = self::base($dir);
		}

		if (self::is_dir($dir)) {
			if (PHP_OS_FAMILY === 'Windows') {
				return (bool) (null !== exec(sprintf("rd /s /q %s", escapeshellarg($dir))));
			} else {
				return (bool) (null !== exec(sprintf("rm -rf %s", escapeshellarg($dir))));
			}
		} else {
			return false;
		}
	}
}