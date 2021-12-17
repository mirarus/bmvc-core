<?php

/**
 * Model
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.8
 */

namespace BMVC\Core;

use BMVC\Libs\{classCall, Validate};
use Mirarus\DB\{DB, Connect};

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

	/**
	 * @return DB|never
	 */
	public static function DB()
	{
		if (self::$active == true) { // @phpstan-ignore-line

			App::$dotenv->required('DB_DSN')->notEmpty(); 

			$db_dsn  = @$_ENV['DB_DSN'];
			$db_user = @$_ENV['DB_USER'];
			$db_pass = @$_ENV['DB_PASS'];

			if (Validate::check(@$db_dsn)) {

				$connect = new Connect();
				$connect->driver('basicdb-mysql');

				if (@strstr($db_dsn, 'mysql:')) {

					App::$dotenv->required(['DB_USER', 'DB_PASS']);
					App::$dotenv->required('DB_USER')->notEmpty();

					$connect->dsn($db_dsn, $db_user, $db_pass);			
				} elseif (@strstr($db_dsn, 'sqlite:')) {
					$connect->dsn($db_dsn);
				}

				return new DB($connect);
			}
		}
	}

	/**
	 * @param string      $class
	 * @param object|null &$return
	 */
	public static function import(string $class, object &$return = null) // @phpstan-ignore-line
	{
		self::get('model', $class, $get);

		return $return = @$get['cls'];
	}
}