<?php

/**
 * Model
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 4.2
 */

namespace BMVC\Core;

use BMVC\Libs\BasicDB;
use BadMethodCallException;

final class Model
{

	/**
	 * @var array
	 */
	private static $params = [];

	/**
	 * @return Model
	 */
	public function __construct()
	{
		self::DB();
	}

	/**
	 * @return BasicDB
	 */
	public static function DB()
	{
		App::$dotenv->required(['DB_ENGINE'])->notEmpty();

		$engine = $_ENV['DB_ENGINE'];

		if ($engine == 'mysql') {

			App::$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
			App::$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();

			$host = $_ENV['DB_HOST'];
			$name = $_ENV['DB_NAME'];
			$user = $_ENV['DB_USER'];
			$pass = $_ENV['DB_PASS'];

			return new BasicDB('mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8', $user, $pass);

		} elseif ($engine == 'sqlite') {

			App::$dotenv->required(['DB_SQLITE'])->notEmpty();

			$sqlite = $_ENV['DB_SQLITE'];

			return new BasicDB('sqlite:' . $sqlite);
		}
	}

	/**
	 * @param mixed       $action
	 * @param object|null &$return
	 */
	public static function import($action, object &$return=null)
	{
		$model     = null;
		$namespace = null;

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
			$model = !is_string($action) ? @array_pop($action) : $action;
		} else {
			$model = $action;
		}
		$namespace = ($action !== null && !is_string($action)) ? @implode('\\', $action) : null;

		if (($namespace === null || $namespace !== null) && $model != null) {

			$_nsm_ = ($namespace != null) ? implode('/', [$namespace, '_model_']) : '_model_';
			
			$_model_ = (App::$namespaces['model'] . str_replace(['/', '//'], '\\', $_nsm_));
			if (class_exists($_model_)) {
				new $_model_();
			}

			$model = ucfirst($model);
			$_nsm = ($namespace != null) ? implode('/', [$namespace, $model]) : $model;
			$_model = (App::$namespaces['model'] . str_replace(['/', '//'], '\\', $_nsm));

			if (is_array(self::$params) && !empty(self::$params)) {
				return $return = new $_model(self::$params);
			} else {
				return $return = new $_model();
			}
		}
	}

	/**
	 * @param  array $params
	 * @return Model
	 */
	public static function par(array $params=[]): Model
	{
		self::$params = $params;
		return new self;
	}

	/**
	 * @param mixed       $action
	 * @param array       $params
	 * @param object|null &$return
	 */
	public static function call($action, array $params=[], object &$return=null)
	{
		$method    = null;
		$model     = null;
		$namespace = null;

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

		$method    = @array_pop($action);
		$model     = @array_pop($action);
		$namespace = ($action !== null && !is_string($action)) ? @implode('\\', $action) : null;

		if (isset($namespace) && $model != null && $method != null) {

			$class = self::import([$namespace, $model]);
			
			if (method_exists($class, $method)) {
				if ($params == null) {
					return $return = call_user_func([$class, $method]);
				} else {
					return $return = call_user_func_array([$class, $method], array_values($params));
				}
			} else {
				$model = ucfirst($model);
				$_nsm  = ($namespace != null) ? implode('/', [$namespace, $model]) : $model;
				throw new BadMethodCallException('Model Method Not Found! | Model: ' . $_nsm . ' - Method: ' . $method);
			}
		}
	}
}