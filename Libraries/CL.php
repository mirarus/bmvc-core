<?php

/**
 * CL
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.1
 */

namespace BMVC\Libs;

class CL
{

	/**
	 * @param mixed $arg
	 */
	public static function replace($arg)
	{
		return @str_replace(['/', '//'], '\\', $arg);
	}

	/**
	 * @param  array  $arg
	 * @return string
	 */
	public static function implode(array $arg): string
	{
		return @implode('\\', $arg);
	}

	/**
	 * @param  string $arg
	 * @return array
	 */
	public static function explode(string $arg): array
	{
		return @explode('\\', $arg);
	}

	/**
	 * @param  string $arg
	 * @return string
	 */
	public static function trim(string $arg): string
	{
		return @trim(self::replace($arg), '\\');
	}
}