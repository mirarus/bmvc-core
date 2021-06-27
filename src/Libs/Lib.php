<?php

/**
 * Lib
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.0
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