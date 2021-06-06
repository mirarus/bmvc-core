<?php

/**
 * Model
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.1
 */

namespace BMVC\Core;

use BMVC\Libs\BasicDB;

final class Model
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
	 * @return Model
	 */
	public function __construct()
	{
		self::DB();
	}

	/**
	 * @return BasicDB
	 */
	public static function DB(): BasicDB
	{
		App::$dotenv->required('DB_DSN')->notEmpty();

		$dsn = $_ENV['DB_DSN'];

		if (is_nem(@$dsn)) {
			if (@strstr($dsn, 'mysql:')) {

				App::$dotenv->required(['DB_USER', 'DB_PASS']);
				App::$dotenv->required('DB_USER')->notEmpty();

				$user = $_ENV['DB_USER'];
				$pass = $_ENV['DB_PASS'];

				return new BasicDB($dsn, $user, $pass);
			} elseif (@strstr($dsn, 'sqlite:')) {

				return new BasicDB($dsn);
			}
		}
	}

	/**
	 * @param string|null $namespace
	 */
	public static function namespace(string $namespace=null): void
	{
		self::$namespace = $namespace;
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
	 * @param string      $class
	 * @param object|null &$return
	 */
	public static function import(string $class, object &$return=null)
	{
		$model = null;

		#
		$class  = @str_replace(['/', '//'], '\\', $class);
		$action = @explode('\\', $class);
		$model  = @array_pop($action);
		#
		$_namespace = @str_replace(['/', '//'], '\\', self::$namespace);
		$namespace  = (($action !== null) ? @implode('\\', $action) : null);
		$namespace  = @str_replace(['/', '//'], '\\', $namespace);
		#
		$_model_ = ($namespace != null) ? implode('\\', [$namespace, '_model_']) : '_model_';
		$_model_ = $_namespace . $_model_;
		$_model_ = @str_replace(['/', '//'], '\\', $_model_);
		if (class_exists($_model_, false)) new $_model_;
		#
		$_model = @ucfirst($model);
		$_model = ($namespace != null) ? implode('\\', [$namespace, $_model]) : $_model;
		$_model = $_namespace . $_model;
		$_model = @str_replace(['/', '//'], '\\', $_model);

		if (is_array(self::$params) && !empty(self::$params)) {
			return $return = (new $_model(self::$params));
		} else {
			return $return = (new $_model);
		}
	}

	/**
	 * @param mixed       $action
	 * @param array       $params
	 * @param object|null &$return
	 */
	public static function call($action, array $params=[], object &$return=null)
	{
		$method = null;
		$model  = null;

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
			$model = @array_pop($action);
		}
		#
		$namespace = (($action != null) ? @implode('\\', $action) : null);
		$namespace = @str_replace(['/', '//'], '\\', $namespace);
		$model     = ($namespace != null) ? implode('\\', [$namespace, $model]) : $model;
		$model     = @str_replace(['/', '//'], '\\', $model);
		#
		$class = self::import($model);

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