<?php

/**
 * CL
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.0
 */

namespace BMVC\Libs;

class CL
{

	/**
	 * @param  string|null $str
	 * @return string
	 */
	public static function replace(string $str=null): string
	{
		return @str_replace(['/', '//'], '\\', $str);
	}

	/**
	 * @param mixed $arg
	 */
	public static function implode($arg=null)
	{
		return @implode('\\', $arg);
	}

	/**
	 * @param  mixed $arg
	 * @return array
	 */
	public static function explode($arg=null): array
	{
		return @explode('\\', $arg);
	}

	/**
	 * @param mixed $arg
	 */
	public static function trim($arg=null)
	{
		return @trim($arg, '\\');
	}
}