<?php

/**
 * View
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 7.2
 */

namespace BMVC\Core;

use Exception;
use Closure;
use BMVC\Libs\FS;

final class View
{

  /**
   * @var null
   */
  private static $namespace = null;

  /**
   * @var string[]
   */
  private static $engines = ['php', 'blade'];

  /**
   * @var string
   */
  private static $engine = 'php';

  /**
   * @var array
   */
  private static $data = [];

  /**
   * @var string
   */
  private static $extension = 'php';

  /**
   * @var
   */
  private static $content;

  /**
   * @var string[]
   */
  private static $separators = ['@', '/', '.', '::', ':'];


  /**
   * @param string|null $namespace
   * @param bool $new
   * @return View|void
   */
  public static function namespace(string $namespace = null, bool $new = false)
  {
    self::$namespace = FS::trim($namespace) . DIRECTORY_SEPARATOR;
    if ($new) return new self;

  }

  /**
   * @param string $engine
   * @return static
   */
  public static function engine(string $engine): self
  {
    if (in_array($engine, self::$engines)) {
      self::$engine = $engine;
    }
    return new self;
  }

  /**
   * @param Closure $callback
   * @param $data
   * @param bool $render
   * @return View|void
   * @throws Exception
   */
  public static function layout(Closure $callback, $data = null, bool $render = true)
  {
    $data = self::$data = array_merge((array)$data, self::$data);
    @extract((array)$data);
    @$GLOBALS['view'] = $data;
    @$_REQUEST['vd'] = $data;

    $_ns = @array_key_exists('namespace', $data) ? $data['namespace'] : null;
    $_ns = FS::implode([FS::trim(self::$namespace), FS::trim(FS::implode([FS::trim($_ns), 'Layout', 'Main']))]);
    $file = FS::app($_ns . '.' . self::$extension);

    if (file_exists($file)) {

      $content = call_user_func($callback);

      ob_start();
      require_once $file;
      $ob_content = ob_get_contents();
      ob_end_clean();

      # Replace
      if (isset($data['page_title'])) {
        $ob_content = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . (empty($data['page_title']) ? '$2' : $data['page_title'] . ' | $2') . '$3', $ob_content);
      }

      self::$content = $ob_content;

      if ($render) {
        echo self::$content;
      } else {
        return new self;
      }
    } else {
      throw new Exception('Layout [' . @str_replace([FS::app()], "", $file) . '] Not Found');
    }
  }

  /**
   * @param $action
   * @param $data
   * @param bool $layout
   * @param bool $render
   * @return View|void
   * @throws Exception
   */
  public static function load($action, $data = null, bool $layout = false, bool $render = true)
  {
    $data = self::$data = array_merge((array)$data, self::$data);
    @extract((array)$data);
    @$GLOBALS['view'] = $data;
    @$_REQUEST['vd'] = $data;

    $view = null;

    if (@is_string($action)) {
      if (self::$separators != null) {
        foreach (self::$separators as $separator) {
          if (@is_string($action)) {
            if (@strstr($action, $separator)) {
              $action = @explode($separator, $action);
            }
          }
        }
      }
    }

    if (@is_array($action) && count($action) > 1) {
      $view = @array_pop($action);
    } elseif (@is_string($action)) {
      $view = $action;
    }
    #
    $namespace = (($action != null && @is_array($action)) ? FS::implode($action) : null);
    $namespace = FS::replace($namespace);
    $view = ($namespace != null) ? FS::implode([$namespace, $view]) : $view;
    $view = FS::replace($view);
    #
    if ($layout) {

      $_ns = @array_key_exists('namespace', $data) ? $data['namespace'] : $namespace;
      $_ns = FS::trim($_ns);
      $_ns = ($_ns != null) ? FS::implode([$_ns, 'Layout', 'Main']) : FS::implode(['Layout', 'Main']);
      $_ns = FS::trim($_ns);
      $_ns = (FS::trim(self::$namespace) != null) ? FS::implode([FS::trim(self::$namespace), $_ns]) : $_ns;
      $file = FS::app($_ns . '.' . self::$extension);

      if (file_exists($file)) {

        $content = ($view != null ? self::_import([$namespace, $view], $data, $return) : null);

        ob_start();
        require_once $file;
        $ob_content = ob_get_contents();
        ob_end_clean();

        # Replace
        if (isset($data['page_title'])) {
          $ob_content = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . (empty($data['page_title']) ? '$2' : $data['page_title'] . ' | $2') . '$3', $ob_content);
        }

        self::$content = $ob_content;

        if ($render) {
          echo self::$content;
        } else {
          return new self;
        }
      } else {
        throw new Exception('View Error! | [' . @str_replace([FS::app()], "", $file) . '] Not Found');
      }
    } else {

      self::$content = self::_import([$namespace, $view], $data, $return);

      if ($render) {
        echo self::$content;
      } else {
        return new self;
      }
    }
  }

  /**
   * @param bool $return
   * @param $print
   * @return mixed|void
   */
  public static function render(bool $return = false, &$print = null)
  {
    if (@self::$content) {
      if ($return) {
        return $print = self::$content;
      } else {
        echo $print = self::$content;
      }
    }
  }

  /**
   * @param $action
   * @param $data
   * @param $return
   * @return false|string|void|null
   * @throws Exception
   */
  private static function _import($action, $data = null, &$return = null)
  {
    $data = array_merge((array)$data, self::$data);

    @extract($data);
    @$GLOBALS['view'] = $data;
    @$_REQUEST['vd'] = $data;

    if (@is_string($action)) {
      if (self::$separators != null) {
        foreach (self::$separators as $separator) {
          if (@is_string($action)) {
            if (@strstr($action, $separator)) {
              $action = @explode($separator, $action);
            }
          }
        }
      }
    }

    $view = null;
    if (@is_array($action) && count($action) > 1) {
      $view = @array_pop($action);
    } elseif (@is_string($action)) {
      $view = $action;
    }
    #
    $namespace = (($action != null && @is_array($action)) ? FS::implode($action) : null);
    $namespace = FS::replace($namespace);

    if ($view != null) {

      if (self::$engine == 'php') {
        return $return = self::_enginePHP($view, $namespace, $data);
      } elseif (self::$engine == 'blade') {
        return $return = self::_engineBLADE($view, $namespace, $data);
      }
    }
  }

  /**
   * @param string|null $view
   * @param string|null $namespace
   * @param $data
   * @param $return
   * @return array|false|string|string[]|null
   * @throws Exception
   */
  private static function _enginePHP(string $view = null, string $namespace = null, $data = null, &$return = null)
  {
    $_ns = (self::$namespace . $view);
    $file = FS::app($_ns . '.' . self::$extension);

    if (file_exists($file)) {

      # Cache
      if ($_ENV['VIEW_CACHE']) {
        $file = self::_cache((string)$view, $file, self::_cache_dir($namespace));
      }

      # Ob
      ob_start();
      require_once $file;
      $ob_content = ob_get_contents();
      ob_end_clean();

      # Replace
      if (isset($data['page_title'])) {
        $ob_content = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . (empty($data['page_title']) ? '$2' : $data['page_title'] . ' | $2') . '$3', $ob_content);
      }

      return $return = $ob_content;
    } else {
      throw new Exception('View Error! | [' . @str_replace([FS::app()], "", $file) . '] Not Found');
    }
  }

  /**
   * @param string|null $view
   * @param string|null $namespace
   * @param $data
   * @param $return
   * @return string
   */
  private static function _engineBLADE(string $view = null, string $namespace = null, $data = null, &$return = null): string
  {
    return $return = (new \Jenssegers\Blade\Blade(FS::app(self::$namespace), self::_cache_dir($namespace)))->make((string)$view, (array)$data)->render();
  }

  /**
   * @param string|null $namespace
   * @param $return
   * @return string
   */
  private static function _cache_dir(string $namespace = null, &$return = null): string
  {
    $_ns = (($namespace != null) ? FS::implode([self::$namespace . $namespace, 'Cache']) : (self::$namespace . 'Cache'));
    $dir = FS::app($_ns);

    FS::mk_dir($dir);

    return $return = $dir;
  }

  /**
   * @param string $view
   * @param string $file
   * @param string $dir
   * @return string
   */
  private static function _cache(string $view, string $file, string $dir): string
  {
    if (file_exists($file)) {

      $_view = FS::explode($view);
      $_view = @array_pop($_view);
      $_file = FS::implode([$dir, (md5($_view) . '.' . self::$extension)]);
      $expir = 120;

      if (!file_exists($_file) || (filemtime($_file) < (time() - $expir))) {

        $signature = "<?php\n/**\n * @file " . $file . "\n * @date " . date(DATE_RFC822) . "\n * @expire " . date(DATE_RFC822, time() + $expir) . "\n */\n?>\n";
        $content = $signature . file_get_contents($file);
        file_put_contents($_file, $content, LOCK_EX);
      }
      return $_file;
    }
    return $file;
  }

  /**
   * @param $index
   * @return mixed
   */
  public static function getData($index = null)
  {
    return $index ? self::$data[$index] : self::$data;
  }

  /**
   * @param array $data
   */
  public static function setData(array $data): void
  {
    self::$data = $data;
  }

  /**
   * @return string
   */
  public static function getExtension(): string
  {
    return self::$extension;
  }

  /**
   * @param string $extension
   */
  public static function setExtension(string $extension): void
  {
    self::$extension = $extension;
  }
}