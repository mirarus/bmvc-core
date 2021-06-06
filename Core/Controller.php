<?php

/**
 * Controller
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.1
 */

namespace BMVC\Core;

use BadMethodCallException;
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
	 * @param mixed       $action
	 * @param object|null &$return
	 */
	public static function import($action, object &$return=null)
	{
		$controller = null;
		$namespace  = null;

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

		if ($action > 1) {
			$controller = !is_string($action) ? @array_pop($action) : $action;
		} else {
			$controller = $action;
		}
		$namespace = ($action !== null && !is_string($action)) ? @implode('\\', $action) : null;

		if (($namespace === null || $namespace !== null) && $controller != null) {

			$_nsc_ = ($namespace != null) ? implode('/', [$namespace, '_controller_']) : '_controller_';
			$_controller_ = (self::$namespace . str_replace(['/', '//'], '\\', $_nsc_));
			if (class_exists($_controller_, false)) {
				new $_controller_;
			}

			$controller = ucfirst($controller);
			$_nsc = ($namespace != null) ? implode('/', [$namespace, $controller]) : $controller;
			$_controller = (self::$namespace . str_replace(['/', '//'], '\\', $_nsc));

			# Last-Modified Change
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
				return $return = new $_controller(self::$params);
			} else {
				return $return = new $_controller();
			}
		}
	}

	/**
	 * @param mixed       $action
	 * @param array|null  $params
	 * @param object|null &$return
	 */
	public static function call($action, array $params=null, object &$return=null)
	{
		$method     = null;
		$controller = null;
		$namespace  = null;

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

		pr($action);

/*		$method     = @array_pop($action);
		$controller = @array_pop($action);

		$namespace  = ($action !== null && !is_string($action)) ? @implode('\\', $action) : null;

		if (isset($namespace) && $controller != null && $method != null) {

			$class = self::import([$namespace, $controller]);
			
			if (method_exists($class, $method)) {
				if ($params == null) {
					return $return = call_user_func([$class, $method]);
				} else {
					return $return = call_user_func_array([$class, $method], array_values($params));
				}
			} else {
				$controller = ucfirst($controller);
				$_nsc = ($namespace != null) ? implode('/', [$namespace, $controller]) : $controller;
				throw new BadMethodCallException('Controller Method Not Found! | Controller: ' . $_nsc . ' - Method: ' . $method);
			}
		}*/
	}
}