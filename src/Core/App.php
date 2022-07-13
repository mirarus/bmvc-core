<?php

/**
 * App
 *
 * Mirarus BMVC Core
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 9.15
 */

namespace BMVC\Core;

use Dotenv\Dotenv;
use BMVC\Libs\FS;
use BMVC\Libs\CL;
use BMVC\Libs\Whoops;
use BMVC\Libs\Monolog;
use BMVC\Libs\Request;
use BMVC\Libs\Header;
use BMVC\Libs\Route;
use BMVC\Libs\Util;

final class App
{

  /**
   * @var bool
   */
  private static $active = false;

  /**
   * @var
   */
  public static $dotenv;

  /**
   * @var
   */
  public static $_microtime;

  /**
   * @var
   */
  public static $microtime;

  /**
   * @var
   */
  public static $memory;

  /**
   * @var
   */
  public static $url;

  /**
   * @var
   */
  public static $page;

  /**
   * @var
   */
  public static $timezone;

  /**
   * @var
   */
  public static $locale;

  /**
   * @var
   */
  public static $activeLocale;

  /**
   * @var
   */
  public static $environment;

  /**
   * @var null[]
   */
  public static $namespaces = [
    'controller' => null,
    'middleware' => null,
    'model' => null
  ];

  /**
   * @param array $data
   * @return void
   */
  public static function init(array $data = []): void
  {
    if (self::$active) return;

    self::$_microtime = microtime(true);

    self::init_Dotenv();
    self::init_Define();
    self::init_Header();
    self::init_Session();
    self::init_Whoops($data);
    self::init_Data($data);
    self::init_Monolog();
    self::init_i18n();
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

    self::init_Route();

    self::$active = true;
  }

  /**
   * @param $par
   * @param string|null $value
   * @param bool $get
   * @param string|null $sub
   * @param bool $new
   * @return array|App|null[]|string|void
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
    if ($new) return new self;
  }

  /**
   * @param array $namespaces
   * @param string|null $sub
   * @param bool $new
   * @return App|void
   */
  public static function namespace(array $namespaces = [], string $sub = null, bool $new = false)
  {
    self::SGnamespace($namespaces, null, false, $sub);
    if ($new) return new self;
  }

  /**
   * @param string $key
   * @return void
   */
  public static function get(string $key)
  {
    if (in_array($key, get_class_vars(__CLASS__))) {
      return self::${$key};
    }
  }

  /**
   * @return void
   */
  private static function init_Dotenv(): void
  {
    $dotenv = Dotenv::createImmutable(FS::app());
    $dotenv->safeLoad();
    self::$dotenv = $dotenv;
  }

  /**
   * @return void
   */
  private static function init_Define(): void
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

  /**
   * @return void
   */
  private static function init_Header(): void
  {
    @header_remove();
    @header('Date: ' . date('D, d M Y H:i:s') . ' GMT');
    @header('Strict-Transport-Security: max-age=15552000; preload');
    @header('X-Frame-Options: sameorigin');
    if (self::$environment == 'development') {
      @header('X-Powered-By: PHP/BMVC');
    }
    if (self::$page) @header('X-Url: ' . self::$page);
    @header('X-XSS-Protection: 1; mode=block');
  }

  /**
   * @return void
   */
  private static function init_Session(): void
  {
    if (session_status() != PHP_SESSION_ACTIVE || session_id() == null) {
      @ini_set('session.use_only_cookies', '1');
      @session_set_cookie_params([
        'lifetime' => 3600 * 24,
        'httponly' => true,
        // 'path' => self::$url
      ]);
      if (self::$environment == 'development') {
        @session_name('BMVC');
      }
      @session_start();
    }
  }

  /**
   * @param array $data
   * @return void
   */
  private static function init_Whoops(array $data = []): void
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
   * @return void
   */
  private static function init_Data(array $data = []): void
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

  /**
   * @return void
   */
  private static function init_Monolog(): void
  {
    if (@$_ENV['LOG']) Monolog::init();

    if (@$_ENV['LOG'] && Monolog::$log) {
      Whoops::$whoops->pushHandler(function ($exception, $inspector, $run) {
        Monolog::$log->error($exception);
      });
    }
  }

  /**
   * @return void
   */
  private static function init_i18n(): void
  {
    if (isset($_GET['locale']) && in_array($_GET['locale'], self::locales('locales'))) {
      $locale = $_GET['locale'];
      setcookie('locale', $locale, 0, '/');
    } elseif (isset($_COOKIE['locale']) && in_array($_COOKIE['locale'], self::locales('locales'))) {
      $locale = $_COOKIE['locale'];
    } elseif (isset($_ENV['LOCALE'])) {
      $locale = $_ENV['LOCALE'];
    } elseif (isset(self::$locale)) {
      $locale = self::$locale;
    } else {
      $locale = 'en_US';
    }

    self::$activeLocale = $locale;

    putenv("LC_ALL=$locale");
    putenv("LANGUAGE=$locale");
    putenv("LANG=$locale");

    if ($locale == 'tr_TR') {
      setlocale(LC_ALL, 'tr_TR.UTF-8', 'tr_TR', 'tr', 'turkish');
    } else {
      setlocale(LC_ALL, $locale . '.UTF-8');
    }

    bindtextdomain($locale, FS::app('Locales'));
    bind_textdomain_codeset($locale, 'UTF-8');
    textdomain($locale);
    date_default_timezone_set("Europe/Istanbul");

    bindtextdomain('system', FS::base('Locales'));
    bind_textdomain_codeset('system', 'UTF-8');
  }

  /**
   * @return mixed
   */
  public static function locales($index = null)
  {
    $httpLocales = array_reduce(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']), function ($res, $el) {
      $res[] = str_replace('-', '_', array_merge(explode(';q=', $el), [1])[0]);
      return $res;
    }, []);
    $unixLocales = (array) trim(explode('.utf8' . "\n", trim(shell_exec("locale -a|grep .utf8"))));
    $locales = ($unixLocales ? array_intersect($httpLocales, $unixLocales) : $httpLocales);
    $locales = array_intersect(FS::directories(FS::app('Locales')), $locales);

    $arr = ($locales ? [
      'locale' => self::$activeLocale,
      'locales' => $locales
    ] : []);

    return $index ? $arr[$index] : $arr;
  }

  /**
   * @return void
   */
  private static function init_Route(): void
  {
    if (@$_ENV['PUBLIC_DIR'] && strpos((string)Request::server('REQUEST_URI'), @$_ENV['PUBLIC_DIR'])) {
      redirect(strstr((string)Request::server('REQUEST_URI'), [@$_ENV['PUBLIC_DIR'] => '/']));
    }

    if (strstr((string)@Request::_server('REQUEST_URI'), ('/' . trim(@$_ENV['PUBLIC_DIR'], '/') . '/'))) Route::getErrors(404);

    $route = Route::Run();

    # MICROTIME
    self::$microtime = number_format(microtime(true) - self::$microtime, 3);
    @define('MICROTIME', self::$microtime);

    # MEMORY
    self::$memory = round(memory_get_usage() / 1024, 4);
    @define('MEMORY', self::$memory);

    if (@$route) {

      if (@$route['namespaces'] != null && is_array($route['namespaces'])) {
        foreach ($route['namespaces'] as $key => $val) {
          if (array_key_exists($key, self::$namespaces)) {
            call_user_func_array([CL::implode([__NAMESPACE__, ucfirst($key)]), 'namespace'], [$val]);
          }
        }
      }

      if (array_key_exists('middlewares', $route)) {
        foreach ($route['middlewares'] as $key => $val) {
          Middleware::call(@$val['callback']);
        }
      }

      Controller::call(@$route['action'], @$route['params']);

      if (@$route['_return'] && !Header::check_type(@$route['_return'])) Route::getErrors(404);
    } else {
      Route::getErrors(404);
    }
  }

  /**
   * @return void
   */
  private static function _routes(): void
  {
    Route::match(['GET', 'POST'], 'route/:all', function ($url) {
      $_url = Route::url($url);
      if ($_url) {
        url($_url, (bool)!Request::get('return'));
      } else {
        Route::getErrors(404);
      }
    });
  }
}
