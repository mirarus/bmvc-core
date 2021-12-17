<?php

/**
 * App
 *
 * Mirarus BMVC Core
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 8.8
 */

namespace BMVC\Core;

use BMVC\Libs\{FS, CL, Whoops, Monolog, Request, Header, Route, Util};
use Dotenv\Dotenv;

final class App
{

	/**
	 * @var boolean
	 */
	private static $active = false;
	
	/**
	 * @var Dotenv
	 */
	public static $dotenv;

	/**
	 * @var float
	 */
	public static $_microtime;

	/**
	 * @var string
	 */
	public static $microtime;

	/**
	 * @var float
	 */
	public static $memory;

	/**
	 * @var string|null|mixed
	 */
	public static $url;

	/**
	 * @var string
	 */
	public static $page;

	/**
	 * @var string
	 */
	public static $timezone;

	/**
	 * @var string
	 */
	public static $environment;

	/**
	 * @var array
	 *
	 * @phpstan-ignore-next-line
	 */
	public static $namespaces = [
		'controller' => null,
		'middleware' => null,
		'model'      => null,
		'view'       => null
	];

	/**
	 * @param array $data
	 *
	 * @phpstan-ignore-next-line
	 */
	public static function init(array $data = []): void
	{
		self::Run($data);
	}

	/**
	 * @param array $data
	 *
	 * @phpstan-ignore-next-line
	 */
	public static function Run(array $data = []): void
	{
		if (self::$active == true) return;

		self::$_microtime = microtime(true);

		self::initDotenv();
		self::initDefine();
		self::initHeader();
		self::initSession();
		self::initWhoops($data);
		self::initData($data);
		self::initMonolog();

		self::_routes();

		if (@$data['namespaces'] != null) self::$namespaces = $data['namespaces'];

		if (@self::$namespaces['controller']) {
			Controller::namespace(@self::$namespaces['controller']);
		}
		if (@self::$namespaces['middleware']) {
			Middleware::namespace(@self::$namespaces['middleware']);
		}
		if (@self::$namespaces['model']) {
			Model::namespace(@self::$namespaces['model']);
		}
		if (@self::$namespaces['view'] || @$_ENV['VIEW_DIR']) {
			View::namespace((@self::$namespaces['view'] ? self::$namespaces['view'] : @$_ENV['VIEW_DIR']));
		}

		self::initRoute();

		self::$active = true;
	}

	/**
	 * @param mixed        $par
	 * @param string|null  $value
	 * @param bool|boolean $get
	 * @param string|null  $sub
	 * @param bool|boolean $new
	 * 
	 * @phpstan-ignore-next-line
	 */
	public static function SGnamespace($par, string $value = null, bool $get = false, string $sub = null, bool $new = false)
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
		if ($new == true) return new self;
	}

	/**
	 * @param array        $namespaces
	 * @param string|null  $sub
	 * @param bool|boolean $new
	 *
	 * @phpstan-ignore-next-line
	 */
	public static function namespace(array $namespaces = [], string $sub = null, bool $new = false)
	{
		self::SGnamespace($namespaces, null, false, $sub);
		if ($new == true) return new self;
	}

	/**
	 * @param string $key
	 *
	 * @phpstan-ignore-next-line
	 */
	public static function get(string $key)
	{
		if (in_array($key, get_class_vars(__CLASS__))) {
			return self::${$key};
		}
	}

	private static function initDotenv(): void
	{
		$dotenv = Dotenv::createImmutable(FS::app());
		$dotenv->safeLoad();
		self::$dotenv = $dotenv;
	}

	private static function initDefine(): void
	{
		# URL
		self::$url = Util::base_url();
		@define('URL', self::$url);

		# PAGE
		self::$page = Util::page_url();
		@define('PAGE', self::$page);

		# TIMEZONE
		self::$timezone = ((isset($_ENV['TIMEZONE']) && $_ENV['TIMEZONE'] != null) ? $_ENV['TIMEZONE'] : 'Europe/Istanbul');
		@define('TIMEZONE', self::$timezone);

		@date_default_timezone_set(self::$timezone);

		# ENVIRONMENT
		self::$environment = ((isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] != null) ? $_ENV['ENVIRONMENT'] : 'development');
		@define('ENVIRONMENT', self::$environment);

		switch (self::$environment) {
			case 'staging':
			case 'development':
			@error_reporting(-1);
			@ini_set('display_errors', '1');
			@error_reporting(E_ALL ^ E_WARNING ^ E_USER_WARNING ^ E_NOTICE ^ E_DEPRECATED);
			break;
			case 'testing':
			case 'production':
			@ini_set('display_errors', '0');
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

	private static function initHeader(): void
	{
		@header_remove();
		@header('Date: ' . date('D, d M Y H:i:s') . ' GMT');
		@header('Strict-Transport-Security: max-age=15552000; preload');
		@header('X-Frame-Options: sameorigin');
		@header('X-Powered-By: PHP/BMVC');
		if (self::$page) @header('X-Url: ' . self::$page);
		@header('X-XSS-Protection: 1; mode=block');
	}

	private static function initSession(): void
	{
		if (session_status() != PHP_SESSION_ACTIVE || session_id() == null) {
			@ini_set('session.use_only_cookies', '1');
			@session_set_cookie_params([ // @phpstan-ignore-line
				'lifetime' => 3600 * 24,
				'httponly' => true,
				'path' => Util::base_url()
			]);
			@session_name('BMVC');
			@session_start();
		}
	}

	/**
	 * @param array $data
	 *
	 * @phpstan-ignore-next-line
	 */
	private static function initWhoops(array $data = []): void
	{
		$blacklist = array_keys($_ENV);

		Whoops::blacklist('_SERVER', $blacklist);
		Whoops::blacklist('_ENV', $blacklist);
		
		# Config Black List
		if (isset($data['whoops_blacklist'])) {
			foreach ($data['whoops_blacklist'] as $key => $val) {
				Whoops::blacklist($key, $val);
			}
		}

		Whoops::set('environment', self::$environment);
		Whoops::init();
	}

	/**
	 * @param array $data
	 *
	 * @phpstan-ignore-next-line
	 */
	private static function initData(array $data = []): void
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

		if (function_exists('mb_internal_encoding')) @mb_internal_encoding('UTF-8');

		if (Util::is_cli()) die('Cli Not Available, Browser Only.');
	}
	
	private static function initMonolog(): void
	{
		//Monolog::init();

		if (@$_ENV['LOG'] == true && Monolog::$log) { // @phpstan-ignore-line
			Whoops::$whoops->pushHandler(function ($exception, $inspector, $run) { // @phpstan-ignore-line
				Monolog::$log->error($exception); // @phpstan-ignore-line
			});
		}
	}

	private static function _routes(): void
	{		
		Route::match(['GET', 'POST'], 'route/:all', function($url) {
			$_url = Route::url($url);
			if ($_url) {
				if (Request::get('return') == true) {
					url($_url);
				} else {
					url($_url, true);
				}
			} else {
				Route::get_404();
			}
		});
	}

	private static function initRoute(): void
	{
		// @phpstan-ignore-next-line
		if (@$_ENV['PUBLIC_DIR'] && strpos((string) Request::server('REQUEST_URI'), @$_ENV['PUBLIC_DIR'])) {
			// @phpstan-ignore-next-line
			redirect(strstr((string) Request::server('REQUEST_URI'), [@$_ENV['PUBLIC_DIR'] => '/']));
		}

		if (strstr((string) @Request::_server('REQUEST_URI'), ('/' . trim(@$_ENV['PUBLIC_DIR'], '/') . '/'))) Route::get_404();

		$route = Route::Run();

		# MICROTIME
		self::$microtime = number_format(microtime(true) - self::$microtime, 3);
		@define('MICROTIME', self::$microtime);

		# MEMORY
		self::$memory = round(memory_get_usage() / 1024, 4);
		@define('MEMORY', self::$memory);

		if (@$route) {

			if (array_key_exists('middlewares', $route)) {
				foreach ($route['middlewares'] as $key => $val) { // @phpstan-ignore-line
					Middleware::call(@$val['callback']); // @phpstan-ignore-line
				}
			}

			if (@$route['namespaces'] != null && is_array($route['namespaces'])) {
				foreach ($route['namespaces'] as $key => $val) {
					if (array_key_exists($key, self::$namespaces)) {
						call_user_func_array([CL::implode([__NAMESPACE__, ucfirst($key)]), 'namespace'], [$val]); // @phpstan-ignore-line
					}
				}
			}

			Controller::call(@$route['action'], @$route['params']);
			 // @phpstan-ignore-next-line
			if (@$route['_return'] && !Header::check_type(@$route['_return'])) Route::get_404();

		} elseif (@Route::$notFound) {
			Controller::call(Route::$notFound);
		}
	}
}