<?php

/**
 * App
 *
 * Mirarus BMVC Core
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.9
 */

namespace BMVC\Core;

use BMVC\Libs\Dir;
use Whoops\Run as WRun;
use Whoops\Handler\PrettyPageHandler as WPrettyPageHandler;
use Monolog\Logger as MlLogger;
use Monolog\Handler\StreamHandler as MlStreamHandler;
use Monolog\Formatter\LineFormatter as MlLineFormatter;
use Dotenv\Dotenv;

final class App
{

	/**
	 * @var boolean
	 */
	private static $init = false;
	
	public static $sc_file;
	public static $whoops;
	public static $log;
	public static $dotenv;

	/**
	 * @var array
	 */
	private static $whoops_blacklist = [
		'_GET' => [],
		'_POST' => [],
		'_FILES' => [],
		'_COOKIE' => [],
		'_SESSION' => [],
		'_SERVER' => [],
		'_ENV' => []
	];

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

		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			self::$sc_file = $_SERVER['SCRIPT_FILENAME'];
		}

		self::initWhoops($data);
		self::initMonolog();
		self::initDotenv();
		self::initError();
		self::initDefine();
		self::initHeader();
		self::initSession();
		self::initData($data);
		self::initRoute();

		self::$namespaces = @$data['namespaces'];
		Controller::$namespace = @self::$namespaces['controller'];
		Model::$namespace = @self::$namespaces['model'];
		View::$namespace = @self::$namespaces['view'] ? self::$namespaces['view'] : @$_ENV['VIEW_DIR'];

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
		if (is_string($par)) {
			if (array_key_exists($par, self::$namespaces)) {
				self::$namespaces[$par] = $sub . $value;
				if ($get === true) {
					return self::$namespaces[$par];
				}
			}
		} elseif (is_array($par)) {
			foreach (@$par as $key) {
				if (array_key_exists($key, self::$namespaces)) {
					self::$namespaces[$key] = $sub . $value;
					if ($get === true) {
						return self::$namespaces[$key];
					}
				}
			}

			foreach (@$par as $key => $val) {
				if (array_key_exists($key, self::$namespaces)) {
					self::$namespaces[$key] = $sub . $val;
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

	/**
	 * @param string $method
	 * @param mixed  $keys
	 */
	public static function whoops_blacklist(string $name, $keys): void
	{
		if (is_array($keys)) {
			foreach ($keys as $key) {
				self::$whoops_blacklist[$name][] = $key;
			}
		} elseif (is_string($keys)) {
			self::$whoops_blacklist[$name][] = $keys;
		}
	}

	/**
	 * @param array $data
	 */
	private static function initWhoops(array $data=[]): void
	{
		# Default Black List
		self::$whoops_blacklist = array_merge(self::$whoops_blacklist, [
			'_SERVER' => ['DIR', 'ENVIRONMENT', 'TIMEZONE', 'LOG', 'LANG', 'VIEW_DIR', 'VIEW_CACHE', 'DB_DSN', 'DB_USER', 'DB_PASS'],
			'_ENV' => ['DIR', 'ENVIRONMENT', 'TIMEZONE', 'LOG', 'LANG', 'VIEW_DIR', 'VIEW_CACHE', 'DB_DSN', 'DB_USER', 'DB_PASS']
		]);
		# Config Black List
		if (isset($data['whoops_blacklist'])) {
			foreach ($data['whoops_blacklist'] as $key => $val) {
				self::whoops_blacklist($key, $val);
			}
		}
		# Class
		$PPH = new WPrettyPageHandler;
		# BlackList Add
		foreach (self::$whoops_blacklist as $key => $val) {
			foreach ($val as $data) {
				$PPH->blacklist($key, $data);
			}
		}
		# Register
		$whoops = new WRun;
		$whoops->pushHandler($PPH);
		$whoops->register();
		self::$whoops = $whoops;
	}

	private static function initMonolog(): void
	{
		$log = new MlLogger('BMVC');
		$stream = new MlStreamHandler(Dir::app('/Logs/app.log'));
		$formatter = new MlLineFormatter(MlLineFormatter::SIMPLE_FORMAT, MlLineFormatter::SIMPLE_DATE);
		$formatter->includeStacktraces(true);
		$stream->setFormatter($formatter);
		$log->pushHandler($stream);
		self::$log = $log;
	}

	private static function initDotenv(): void
	{
		$dotenv = Dotenv::createImmutable(Dir::app());
		$dotenv->safeLoad();
		self::$dotenv = $dotenv;
	}

	private static function initError(): void
	{
		if ($_ENV['LOG'] == true) {
			self::$whoops->pushHandler(function ($exception, $inspector, $run) {
				self::$log->error($exception);
			});
		}
	}

	private static function initHeader(): void
	{
		@header_remove();
		@header("Strict-Transport-Security: max-age=15552000; preload");
		@header("X-Frame-Options: sameorigin");
		@header("X-Powered-By: PHP/BMVC");
		@header("X-Date: " . date('D, d M Y H:i:s \G\M\T'));
		@header("X-Last-Modified: " . date('D, d M Y H:i:s \G\M\T'));
		@header("X-Expires: " . date('D, d M Y H:i:s \G\M\T', time() + 3600 * 24));
	}

	private static function initSession(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE || session_id() === null) {
		/*@ini_set('session.cookie_httponly', 1);
			@ini_set('session.use_only_cookies', 1);
			@ini_set('session.gc_maxlifetime', 3600 * 24);
			@session_set_cookie_params(3600 * 24);*/
			@session_set_cookie_params([
				'lifetime' => 3600 * 24,
				'httponly' => true,
				'path' => base_url(null, false, false, true)['path']
			]);
			@session_name("BMVC");
			@session_start();
		}
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
		if (isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] != null && in_array($_ENV['ENVIRONMENT'], ['development', 'production'])) {
			@define('ENVIRONMENT', $_ENV['ENVIRONMENT']);
		} else {
			@define('ENVIRONMENT', 'development');
		}
		switch (ENVIRONMENT) {
			case 'development':
			@error_reporting(-1);
			@ini_set('display_errors', 0);
			break;
			case 'production':
			@ini_set('display_errors', 0);
			@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
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
	private static function initData(array $data=[])
	{
		if (is_callable($data)) {
			call_user_func($data);
		} elseif (is_array($data)) {

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

		if (function_exists('mb_internal_encoding')) {
			@mb_internal_encoding("UTF-8");
		}

		if (is_cli()) die("Cli Not Available, Browser Only.");
	}

	private static function initRoute()
	{
		Route::Run($route);

		$action = $route['action'];
		$params = $route['params'];

		if (is_callable($action)) {
			return call_user_func_array($action, array_values($params));
		} else {
			Controller::call(@$action, @$params);
		}
	}
}