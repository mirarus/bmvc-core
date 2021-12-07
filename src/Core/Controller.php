<?php

/**
 * Controller
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 6.3
 */

namespace BMVC\Core;

use BMVC\Libs\{classCall, FS};

final class Controller
{

	use classCall;

	/**
	 * @param string      $class
	 * @param object|null &$return
	 */
	public static function import(string $class, object &$return=null)
	{
		self::get('controller', $class, $get);

		$load = include(FS::app(FS::implode(['vendor', 'autoload.php'])));
		$file = FS::app($get['class'] . '.php');

		if (@is_object($load) && @class_exists(get_class($load), false) && (@$load->findFile($get['class']) != false)) {
			@header("Last-Modified: " . date("D, d M Y H:i:s", filemtime(@$load->findFile($get['class']))) . " GMT");
		} elseif (@file_exists($file) == true) {
			@header("Last-Modified: " . date("D, d M Y H:i:s", filemtime($file)) . " GMT");
		} else {
			@header("Last-Modified: " . date("D, d M Y H:i:s") . " GMT");
		}

		return $return = @$get['cls'];
	}
}