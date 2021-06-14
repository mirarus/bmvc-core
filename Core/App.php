<?php

/**
 * App
 *
 * Mirarus BMVC Core
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 6.5
 */

namespace BMVC\Core;

use BMVC\Libs\{Dir, CL, Whoops, Log};
use Dotenv\Dotenv;

final class App
{

	/**
	 * @var boolean
	 */
	private static $init = false;
	
	public static $dotenv;

	/**
	 * @var array
	 */
	public static $namespaces = [
		'controller' => null,
		'model'      => null,
		'view'       => null
	];

	/**
	 * @param array $data
	 */
	public function __construct(array $data=[])
	{
		self::Run($data);
	}

	/**
	 * @param array $data
	 */
	public static function init(array $data=[]): void
	{
		self::Run($data);
	}

	/**
	 * @param array $data
	 */
	public static function Run(array $data=[]): void
	{
		if (self::$init == true) return;

		self::initDotenv();
		self::initDefine();
		self::initWhoops($data);
		self::initMonolog();
		self::initHeader();
		self::initSession();
		self::initData($data);

		if (@$data['namespaces'] != null) self::$namespaces = $data['namespaces'];
		Controller::namespace(@self::$namespaces['controller']);
		Model::namespace(@self::$namespaces['model']);
		View::namespace((@self::$namespaces['view'] ? self::$namespaces['view'] : @$_ENV['VIEW_DIR']));

		self::initRoute();

		self::$init = true;
	}

	/**
	 * @param mixed        $par
	 * @param string|null  $value
	 * @param bool|boolean $get
	 * @param string|null  $sub
	 */
	public static function SGnamespace($par, string $value=null, bool $get=false, string $sub=null)
	{
		$sub = ($sub != null) ? (CL::trim($sub) . '\\') : null;

		if (is_string($par)) {
			if (array_key_exists($par, self::$namespaces)) {
				self::$namespaces[$par] = (CL::trim(($sub . $value)) . '\\');
				if ($get === true) {
					return self::$namespaces[$par];
				}
			}
		} elseif (is_array($par)) {
			foreach (@$par as $key) {
				if (array_key_exists($key, self::$namespaces)) {
					self::$namespaces[$key] = (CL::trim(($sub . $value)) . '\\');
					if ($get === true) {
						return self::$namespaces[$key];
					}
				}
			}

			foreach (@$par as $key => $val) {
				if (array_key_exists($key, self::$namespaces)) {
					self::$namespaces[$key] = (CL::trim(($sub . $val)) . '\\');
					if ($get === true) {
						return self::$namespaces[$key];
					}
				}
			}
		} else {
			if ($get === true) {
				return self::$namespaces;
			}
		}
		# if ($get === false) return new self;
	}

	/**
	 * @param array       $namespaces
	 * @param string|null $sub
	 */
	public static function namespace(array $namespaces=[], string $sub=null): void
	{
		self::SGnamespace($namespaces, null, false, $sub);
	}

	/**
	 * @param string $key
	 */
	public static function get(string $key)
	{
		if (in_array($key, get_class_vars(__CLASS__))) {
			return self::${$key};
		}
	}

	private static function initDotenv(): void
	{
		$dotenv = Dotenv::createImmutable(Dir::app());
		$dotenv->safeLoad();
		self::$dotenv = $dotenv;
	}

	private static function initDefine(): void
	{
		@define('URL', base_url());

		# TIMEZONE
		if (isset($_ENV['TIMEZONE']) && $_ENV['TIMEZONE'] != null) {
			@define('TIMEZONE', $_ENV['TIMEZONE']);
		} else {
			@define('TIMEZONE', 'Europe/Istanbul');
		}
		@date_default_timezone_set(TIMEZONE);

		# ENVIRONMENT
		if (isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] != null) {
			@define('ENVIRONMENT', $_ENV['ENVIRONMENT']);
		} else {
			@define('ENVIRONMENT', 'development');
		}
		switch (ENVIRONMENT) {
			case 'development':
			@error_reporting(-1);
			@ini_set('display_errors', 1);
			@error_reporting(E_ALL ^ E_WARNING ^ E_USER_WARNING ^ E_NOTICE ^ E_DEPRECATED);
			break;
			case 'testing':
			case 'production':
			@ini_set('display_errors', 0);
			if (version_compare(PHP_VERSION, '5.3', '>=')) {
				@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
			} else {
				@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
			}
			break;
			default:
			@header('HTTP/1.1 503 Service Unavailable.', true, 503);
			echo 'The application environment is not set correctly.';
			exit(1);
		}
	}

	/**
	 * @param array $data
	 */
	private static function initWhoops(array $data=[]): void
	{
		$blacklist = ['DIR', 'ENVIRONMENT', 'TIMEZONE', 'LOG', 'LANG', 'VIEW_DIR', 'VIEW_CACHE', 'PUBLIC_DIR', 'DB_DSN', 'DB_USER', 'DB_PASS'];

		Whoops::blacklist('_SERVER', $blacklist);
		Whoops::blacklist('_ENV', $blacklist);
		
		# Config Black List
		if (isset($data['whoops_blacklist'])) {
			foreach ($data['whoops_blacklist'] as $key => $val) {
				Whoops::blacklist($key, $val);
			}
		}

		Whoops::init();
	}

	private static function initMonolog(): void
	{
		Log::monolog();

		if (@$_ENV['LOG'] == true) {
			Whoops::$whoops->pushHandler(function ($exception, $inspector, $run) {
				Log::$monolog->error($exception);
			});
		}
	}

	private static function initHeader(): void
	{
		@header_remove();
		@header("Date: " . date("D, d M Y H:i:s") . " GMT");
		@header("Strict-Transport-Security: max-age=15552000; preload");
		@header("X-Frame-Options: sameorigin");
		@header("X-Powered-By: PHP/BMVC");
		(page_url() ? @header("X-Url: " . page_url()) : null);
		@header("X-XSS-Protection: 1; mode=block");
	}

	private static function initSession(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE || session_id() === null) {
			@ini_set('session.use_only_cookies', 1);
			if (PHP_VERSION_ID < 70300) {
				@session_set_cookie_params(3600 * 24, base_url(null, false, false, true)['path'], null, null, true);
			} else {
				@session_set_cookie_params([
					'lifetime' => 3600 * 24,
					'httponly' => true,
					'path' => base_url(null, false, false, true)['path']
				]);
			}
			@session_name("BMVC");
			@session_start();
		}
	}

	/**
	 * @param array $data
	 */
	private static function initData(array $data=[])
	{
		if ($data != null) {
			# File Import
			if (isset($data['files'])) {
				foreach ($data['files'] as $file) {
					require_once $file;
				}
			}
			# Class Load
			if (isset($data['init'])) {
				foreach ($data['init'] as $init) {
					new $init;
				}
			}
		}
		#

		if (function_exists('mb_internal_encoding')) @mb_internal_encoding("UTF-8");

		if (is_cli()) die("Cli Not Available, Browser Only.");
	}

	private static function initRoute()
	{
		Route::Run($route);

		if (@$route) {

			if (@$route['namespaces'] != null && is_array($route['namespaces'])) {
				foreach ($route['namespaces'] as $key => $val) {
					if (array_key_exists($key, self::$namespaces)) {
						call_user_func_array(["BMVC\Core\\" . ucfirst($key), 'namespace'], [$val]);
					}
				}
			}

			Controller::call(@$route['action'], @$route['params']);
		} elseif (@Route::$notFound) {
			Controller::call(Route::$notFound);
		}
	}
}