<?php

/**
 * Middleware
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 0.2
 */

namespace BMVC\Core;

use BMVC\Libs\classCall;

final class Middleware
{

	use classCall;

  /**
   * @param string $class
   * @param object|null $return
   * @return mixed
   */
  public static function import(string $class, object &$return=null)
	{
		self::get('middleware', $class, $get);

		return $return = @$get['cls'];
	}
}