<?php

/**
 * Controller
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.7
 */

namespace BMVC\Core;

use BMVC\Libs\{classCall, Dir};

final class Controller
{

	/**
	 * @param string|null  $namespace
	 * @param bool|boolean $new
	 */
	public static function namespace(string $namespace=null, bool $new=false)
	{
		classCall::init(get_class())->namespace($namespace, $new);
	}

	/**
	 * @param mixed       $action
	 * @param array|null  $params
	 * @param object|null &$return
	 */
	public static function call($action, array $params=null, object &$return=null)
	{
		classCall::init(get_class())->call($action, $params, $return);
	}

	/**
	 * @param string      $class
	 * @param object|null &$return
	 */
	public static function import(string $class, object &$return=null)
	{
		classCall::init(get_class())->get('controller', $class, $get);

		if (@$get['_class'] != @$get['_class_']) {
			
			$loader = include(Dir::app(Dir::implode(['vendor', 'autoload.php'])));

			if (@is_object($loader) && @class_exists(get_class($loader), false)) {
				if (@$loader->findFile($get['_class']) != false) {
					@header("Last-Modified: " . date("D, d M Y H:i:s", filemtime(@$loader->findFile($get['_class']))) . " GMT");
				}
			} elseif (@file_exists(Dir::app($get['_class'] . '.php')) == true) {
				@header("Last-Modified: " . date("D, d M Y H:i:s", filemtime(Dir::app($get['_class'] . '.php'))) . " GMT");
			} else {
				@header("Last-Modified: " . date("D, d M Y H:i:s") . " GMT");
			}
		}

		return $return = @$get['_cl'];
	}
}