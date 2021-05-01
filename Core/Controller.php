<?php

/**
 * Controller
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 4.5
 */

namespace BMVC\Core;

use Exception;

final class Controller
{	

	/**
	 * @var array
	 */
	private static $params = [];

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
			
			$_controller_ = (App::$namespaces['controller'] . str_replace(['/', '//'], '\\', $_nsc_));
			if (class_exists($_controller_)) {
				new $_controller_();
			}

			$controller = ucfirst($controller);
			$_nsc = ($namespace != null) ? implode('/', [$namespace, $controller]) : $controller;
			$_controller = (App::$namespaces['controller'] . str_replace(['/', '//'], '\\', $_nsc));

			if (is_array(self::$params) && !empty(self::$params)) {
				return $return = new $_controller(self::$params);
			} else {
				return $return = new $_controller();
			}
		}
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
			} elseif (@strstr($action, ':')) {
				$action = explode(':', $action);
			}
		}

		$method     = @array_pop($action);
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
				throw new Exception('Controller Method Not Found! | Controller: ' . $_nsc . ' - Method: ' . $method);
			}
		}
	}
}
