<?php

/**
 * Lib
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.1
 */

namespace BMVC\Libs;

class Lib
{

	public function __call($class, $method)
	{
		$class = __NAMESPACE__ . '\\' . $class;
		return new $class;
	}

	public static function __callStatic($class, $method)
	{
		$class = __NAMESPACE__ . '\\' . $class;
		return new $class;
	}
}