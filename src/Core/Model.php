<?php

/**
 * Model
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.6
 */

namespace BMVC\Core;

use BMVC\Libs\{classCall, Validate, BasicDB};

final class Model 
{

	use classCall;

	/**
	 * @var boolean
	 */
	private static $active = false;

	/**
	 * @return Model
	 */
	public function __construct()
	{
		self::$active = true;

		self::DB();
	}

	public static function DB()
	{
		if (self::$active == true) {

			App::$dotenv->required('DB_DSN')->notEmpty();

			$dsn = $_ENV['DB_DSN'];

			if (Validate::check(@$dsn)) {
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
	}

	/**
	 * @param string      $class
	 * @param object|null &$return
	 */
	public static function import(string $class, object &$return=null)
	{
		self::get('model', $class, $get);

		return $return = @$get['_cl'];
	}
}