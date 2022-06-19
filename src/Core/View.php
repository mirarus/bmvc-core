<?php

/**
 * View
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 7.4
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
  private static $path = null;

  /**
   * @var null
   */
  private static $themes = null;

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
   * @var
   */
  private static $layout_content;

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
   * @return void
   * @throws Exception
   */
  public static function layout(Closure $callback, $data = null)
  {
    self::_data($data);

    $_theme = @array_key_exists('theme', self::$data) ? self::$data['theme'] : 'default';
    $_theme = @array_key_exists($_theme, self::$themes) ? $_theme : 'default';
    $_ns = FS::trim(FS::implode([self::$path, self::$themes[$_theme]['path']]));
    $_layout = FS::trim(FS::implode([$_ns, self::$themes[$_theme]['layout']]));
    $_lf = FS::app($_layout);

    ob_start();
    call_user_func($callback);
    self::$content = $content = ob_get_contents();
    ob_end_clean();

    self::_ob($_lf, $_layout, $data);
  }

  /**
   * @param $view
   * @param $data
   * @param bool $layout
   * @return void
   * @throws Exception
   */
  public static function load($view, $data = null, bool $layout = false)
  {
    self::_data($data);

    $_theme = @array_key_exists('theme', self::$data) ? self::$data['theme'] : 'default';
    $_theme = @array_key_exists($_theme, self::$themes) ? $_theme : 'default';
    $_ns = FS::trim(FS::implode([self::$path, self::$themes[$_theme]['path']]));
    $_layout = FS::trim(FS::implode([$_ns, self::$themes[$_theme]['layout']]));
    $_lf = FS::app($_layout);


    ob_start();
    self::_import($_ns, $view, $data);
    self::$content = $content = ob_get_contents();
    ob_end_clean();

    if ($layout) {
      self::_ob($_lf, $_layout, $data);
    } else {
      echo $content;
    }
  }

  /**
   * @param $path
   * @param $data
   * @param $return
   * @return false|string|void|null
   * @throws Exception
   */
  private static function _import($path, $view, $data = null, &$return = null)
  {
    self::_data($data);

    if (self::$engine == 'php') {
      return $return = self::_enginePHP($path, $view, $data);
    } elseif (self::$engine == 'blade') {
      return $return = self::_engineBLADE($path, $view, $data);
    }
  }

  /**
   * @param string|null $path
   * @param string|null $view
   * @param $data
   * @return false|string
   * @throws Exception
   */
  private static function _enginePHP(string $path = null, string $view = null, $data = null)
  {
    $_file = FS::trim(FS::implode([$path, $view]));
    $_file = ($_file . '.' . self::$extension);
    $_vf = FS::app($_file);

    if ($_ENV['VIEW_CACHE']) $_vf = self::_cache($view, $_vf, self::_cd($path));
    self::_ob($_vf, $_file, $data);
  }

  /**
   * @param string|null $path
   * @param string|null $view
   * @param $data
   * @return string
   */
  private static function _engineBLADE(string $path = null, string $view = null, $data = null): string
  {
    return (new \Jenssegers\Blade\Blade(FS::app($path), self::_cd($path)))->make($view, (array)$data)->render();
  }

  /**
   * @param string|null $path
   * @return string
   */
  private static function _cd(string $path = null): string
  {
    $path = FS::implode([($path ? $path : self::$path), 'Cache']);
    $path = FS::app($path);
    FS::mk_dir($path);
    return $path;
  }

  /**
   * @param string $view
   * @param string $file
   * @param string $cachePath
   * @return string
   */
  private static function _cache($view, string $file, string $cachePath)
  {
    if (file_exists($file)) {

      $_file = FS::implode([$cachePath, (md5($view) . '.' . self::$extension)]);
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
   * @param string $view
   * @param string $file
   * @param $data
   */
  private static function _ob(string $file, string $name, $data = null): void
  {
    self::_data($data);

    if (file_exists($file)) {

      ob_start();
      require_once $file;
      $ob_content = ob_get_contents();
      ob_end_clean();

      if (isset($data['page_title'])) {
        $ob_content = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . (empty($data['page_title']) ? '$2' : $data['page_title'] . ' | $2') . '$3', $ob_content);
      }

      echo self::$layout_content = $ob_content;
    } else {
      throw new Exception('View [' . $name . '] Not Found');
    }
  }

  /**
   * @param array $data
   */
  private static function _data(array &$data)
  {
    self::$data = $data = (array)array_merge($data, self::$data);
    @extract($data);
    @$GLOBALS['view'] = $data;
    @$_REQUEST['view'] = $data;
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

  /**
   * @return string
   */
  public static function getContent()
  {
    return self::$content;
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return self::getContent();
  }

  public static function config($arr)
  {
    self::$path = $arr['path'];
    self::$themes = $arr['themes'];
  }
}