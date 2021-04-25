<?php

/**
 * App
 *
 * Mirarus BMVC Core
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 5.1
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
	
	public static $run_file;
	public static $whoops;
	public static $log;
	public static $dotenv;

	/**
	 * @var array
	 */
	public static $namespaces = [
		'controller' => null,
		'model'      => null
	];

	public function __construct($data=[])
	{
		self::Run($data);
	}

	public static function Run($data=[]): void
	{
		if (self::$init == true) return;

		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			self::$run_file = $_SERVER['SCRIPT_FILENAME'];
		}

		self::initWhoops();
		self::initMonolog();
		self::initDotenv();
		self::initError();
		self::initSession();
		self::initHeader();
		self::initDefine();
		self::init($data);
		self::initRoute();

		self::$init = true;
	}

	/**
	 * @param mixed        $par
	 * @param string|null  $value
	 * @param bool|boolean $get
	 */
	public static function namespace($par, string $value=null, bool $get=false)
	{
		if (is_string($par)) {
			if (array_key_exists($par, self::$namespaces)) {
				self::$namespaces[$par] = $value;
				if ($get === true) {
					return self::$namespaces[$par];
				}
			}
		} elseif (is_array($par)) {

			foreach (@$par as $key) {
				if (array_key_exists($key, self::$namespaces)) {
					self::$namespaces[$key] = $value;
					if ($get === true) {
						return self::$namespaces[$key];
					}
				}
			}

			foreach (@$par as $key => $val) {
				if (array_key_exists($key, self::$namespaces)) {
					self::$namespaces[$key] = $val;
					if ($get === true) {
						return self::$namespaces[$key];
					}
				}
			}
		}
		# if ($get === false) return new self;
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

	private static function initWhoops(): void
	{
		$whoops = new WRun;
		$whoops->pushHandler(new WPrettyPageHandler);
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
	
	private static function initSession(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE || session_id() === null) {
			@ini_set('session.cookie_httponly', 1);
			@ini_set('session.use_only_cookies', 1);
			@ini_set('session.gc_maxlifetime', 3600 * 24);
			@session_set_cookie_params(3600 * 24);
			
			@session_name("BMVC");
			@session_start();
		}
	}

	private static function initHeader(): void
	{
		@header_remove();
		@header("X-Frame-Options: sameorigin");
		@header("Strict-Transport-Security: max-age=15552000; preload");
		@header("X-Powered-By: PHP/BMVC");
	}

	private static function initDefine(): void
	{
		# URL
		if (isset($_ENV['URL']) && $_ENV['URL'] != null) {
			@define('URL', $_ENV['URL']);
		} else {
			@define('URL', base_url());
		}

		# TIMEZONE
		if (isset($_ENV['TIMEZONE']) && $_ENV['TIMEZONE'] != null) {
			@define('TIMEZONE', $_ENV['TIMEZONE']);
		} else {
			@define('TIMEZONE', 'Europe/Istanbul');
		}
		@date_default_timezone_set(TIMEZONE);

		# ENVIRONMENT
		if (isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] != null && in_array($_ENV['ENVIRONMENT'], ['development', 'testing', 'production'])) {
			@define('ENVIRONMENT', $_ENV['ENVIRONMENT']);
		} else {
			@define('ENVIRONMENT', 'development');
		}
		switch (ENVIRONMENT) {
			case 'development':
			@error_reporting(-1);
			@ini_set('display_errors', 0);
			break;
			case 'testing':
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

	private static function init($data=[])
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
		$route = Route::Run();

		$action = $route['action'];
		$params = $route['params'];

		if (is_callable($action)) {
			return call_user_func_array($action, array_values($params));
		} else {
			Controller::call(@$action, @$params);
		}
	}
}