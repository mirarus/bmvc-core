<?php

/**
 * Controller
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.2
 */

namespace BMVC\Core;

use BMVC\Libs\Dir;

final class Controller
{	

	/**
	 * @var array
	 */
	private static $params = [];

	/**
	 * @var string
	 */
	public static $namespace = null;

	/**
	 * @param string|null $namespace
	 */
	public static function namespace(string $namespace=null): void
	{
		self::$namespace = $namespace;
	}

	/**
	 * @param  array $params
	 * @return Controller
	 */
	public static function par(array $params=[]): Controller
	{
		self::$params = $params;
		return new self;
	}

	/**
	 * @param string      $class
	 * @param object|null &$return
	 */
	public static function import(string $class, object &$return=null)
	{
		$controller = null;

		#
		$class      = @str_replace(['/', '//'], '\\', $class);
		$action     = @explode('\\', $class);
		$controller = @array_pop($action);
		#
		$_namespace = @str_replace(['/', '//'], '\\', self::$namespace);
		$namespace  = (($action !== null) ? @implode('\\', $action) : null);
		$namespace  = @str_replace(['/', '//'], '\\', $namespace);
		#
		$_controller_ = ($namespace != null) ? implode('\\', [$namespace, '_controller_']) : '_controller_';
		$_controller_ = $_namespace . $_controller_;
		$_controller_ = @str_replace(['/', '//'], '\\', $_controller_);
		if (class_exists($_controller_, false)) new $_controller_;
		#
		$_controller = @ucfirst($controller);
		$_controller = ($namespace != null) ? implode('\\', [$namespace, $_controller]) : $_controller;
		$_controller = $_namespace . $_controller;
		$_controller = @str_replace(['/', '//'], '\\', $_controller);

		if ($_controller != $_controller_) {
			$loader = include(Dir::app('vendor' . DIRECTORY_SEPARATOR . 'autoload.php'));
			if (@$loader->findFile($_controller) != false) {
				@header("Last-Modified: " . date('D, d M Y H:i:s \G\M\T', filemtime($loader->findFile($_controller))));
			} elseif (@file_exists(Dir::app($_controller . '.php')) == true) {
				@header("Last-Modified: " . date('D, d M Y H:i:s \G\M\T', filemtime(Dir::app($_controller . '.php'))));
			} else {
				@header("Last-Modified: " . date('D, d M Y H:i:s \G\M\T'));
			}
		}

		if (is_array(self::$params) && !empty(self::$params)) {
			return $return = (new $_controller(self::$params));
		} else {
			return $return = (new $_controller);
		}
	}

	/**
	 * @param mixed       $action
	 * @param array       $params
	 * @param object|null &$return
	 */
	public static function call($action, array $params=[], object &$return=null)
	{
		$method     = null;
		$controller = null;

		if (@is_string($action)) {
			if (@strstr($action, '@')) {
				$action = explode('@', $action);
			} elseif (@strstr($action, '/')) {
				$action = explode('/', $action);
			} elseif (@strstr($action, '.')) {
				$action = explode('.', $action);
			} elseif (@strstr($action, '::')) {
				$action = explode('::', $action);
			} elseif (@strstr($action, ':')) {
				$action = explode(':', $action);
			}
		}
		if (@is_array($action)) {
			$method     = @array_pop($action);
			$controller = @array_pop($action);
		}
		#
		$namespace  = (($action != null) ? @implode('\\', $action) : null);
		$namespace  = @str_replace(['/', '//'], '\\', $namespace);
		$controller = ($namespace != null) ? implode('\\', [$namespace, $controller]) : $controller;
		$controller = @str_replace(['/', '//'], '\\', $controller);
		#
		$class = self::import($controller);

		if (method_exists($class, $method)) {
			if ($params == null) {
				return $return = call_user_func([$class, $method]);
			} else {
				return $return = call_user_func_array([$class, $method], array_values($params));
			}
		} else {
			return $return = $class->{$method}();
		}
	}
}