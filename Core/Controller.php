<?php

/**
 * Controller
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.4
 */

namespace BMVC\Core;

use BMVC\Libs\Dir;

final class Controller
{
	use Call;

	/**
	 * @param string      $class
	 * @param object|null &$return
	 */
	public static function import(string $class, object &$return=null)
	{
		self::get('controller', $class, $get);

		if (@$get['_class'] != @$get['_class_']) {
			
			$loader = include(Dir::app('vendor' . DIRECTORY_SEPARATOR . 'autoload.php'));
			if (@class_exists(get_class($loader), false)) {
				if (@$loader->findFile($get['_class']) != false) {
					@header("Last-Modified: " . date('D, d M Y H:i:s \G\M\T', filemtime($loader->findFile($get['_class']))));
				}
			} elseif (@file_exists(Dir::app($get['_class'] . '.php')) == true) {
				@header("Last-Modified: " . date('D, d M Y H:i:s \G\M\T', filemtime(Dir::app($get['_class'] . '.php'))));
			} else {
				@header("Last-Modified: " . date('D, d M Y H:i:s \G\M\T'));
			}
		}

		return $return = @$get['_cl'];
	}
}