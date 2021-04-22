<?php

/**
 * App
 *
 * Mirarus BMVC Core
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 4.6
 */

namespace BMVC\Core;

use BMVC\Libs\Dir;
use Whoops\Run as WRun;
use Whoops\Handler\PrettyPageHandler as WPrettyPageHandler;
use Monolog\Logger as MlLogger;
use Monolog\Handler\StreamHandler as MlStreamHandler;
use Monolog\Formatter\LineFormatter as MlLineFormatter;

final class App
{

	/**
	 * @var boolean
	 */
	private static $init = false;
	
	public static $whoops;
	public static $log;

	/**
	 * @var array
	 */
	public static $namespaces = [
		'controller' => 'App\Controller\\',
		'model'      => 'App\Model\\'
	];

	public function __construct(array $array = [])
	{
		self::Run($array);
	}

	public static function Run(array $array = []): void
	{
		if (self::$init == true) return;

		self::initWhoops();
		self::initMonolog();
		self::initError();
		self::initSession();
		self::initHeader();
		self::init($array);
		self::initAutoLoader();
		self::initialize();
		self::initRoute();

		self::$init = true;
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
		$stream = new MlStreamHandler(Dir::app('/App/Logs/app.log'));
		$formatter = new MlLineFormatter(MlLineFormatter::SIMPLE_FORMAT, MlLineFormatter::SIMPLE_DATE);
		$formatter->includeStacktraces(true);
		$stream->setFormatter($formatter);
		$log->pushHandler($stream);
		self::$log = $log;
	}

	private static function initError(): void
	{
		if (config('general/log') == true) {		
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

	private static function init(array $array = []): void
	{
		if (isset($array['files'])) {
			foreach ($array['files'] as $file) {
				require_once $file;
			}
		}

		if (function_exists('mb_internal_encoding')) {
			@mb_internal_encoding("UTF-8");
		}

		if (is_cli()) {
			die("Cli Not Available, Browser Only.");
		}

		if (!defined('URL')) {
			define("URL", base_url());
		}

		if (is_string(config('general/timezone'))) {
			define("TIMEZONE", config('general/timezone'));
		}

		if (is_string(config('general/environment'))) {
			define("ENVIRONMENT", config('general/environment'));
		}

		if (!defined('TIMEZONE')) {
			define("TIMEZONE", "Europe/Istanbul");
		}
		@date_default_timezone_set(TIMEZONE);

		if (!defined('ENVIRONMENT')) {
			define("ENVIRONMENT", "development");
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

	private static function initAutoLoader(): void
	{
		spl_autoload_register(function ($class) {
			if ($class == 'index') return false;

			$file = Dir::app('/App/Libraries/' . $class . '.php');
			$file = @strtr($file, ['\\' => '/', '//' => '/']);

			if (file_exists($file)) {
				require_once $file;
			}
		});

		array_map(function ($file) {
			if ($file == Dir::app('/App/Helpers/index.php')) return false;
			require_once $file;
		}, glob(Dir::app("/App/Helpers/*.php")));
	}

	private static function initialize(): void
	{
		if (is_array(config('init')) && config('init') !== null) {
			foreach (config('init') as $init) {
				new $init;
			}
		}
	}

	private static function initRoute()
	{
		$route = Route::Run();

		$action = $route['action'];
		$params = $route['params'];
		$_url   = $route['_url'];

		if (is_callable($action)) {
			return call_user_func_array($action, array_values($params));
		} else {
			Controller::call(@$action, @$params);
		}
	}
}

define("BMVC_END", microtime(true));
define("BMVC_LOAD", number_format((BMVC_END - BMVC_START), 5));