<?php

/**
 * View
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 4.7
 */

namespace BMVC\Core;

use Exception;
use Closure;
use BMVC\Libs\Dir;

final class View
{

	/**
	 * @var string
	 */
	public static $namespace = null;

	public function __construct()
	{
		self::init();
	}

	/**
	 * @param string|null $namespace
	 */
	public static function init(string $namespace=null): void
	{
		self::$namespace = $namespace ? $namespace : App::$namespaces['view'];
	}

	/**
	 * @param mixed       $action
	 * @param array       $data
	 * @param string      $engine
	 * @param object|null &$return
	 */
	private static function import($action, array $data=[], string $engine='php', object &$return=null)
	{
		self::init();
		$data ? extract($data) : null;
		@$_REQUEST['vd'] = $data;

		if (!in_array($engine, ['php', 'blade'])) {
			$engine = 'php';
		}

		if (@is_string($action)) {
			if (@strstr($action, '@')) {
				$action = explode('@', $action);
			} elseif (@strstr($action, '/')) {
				$action = explode('/', $action);
			} elseif (@strstr($action, '.')) {
				$action = explode('.', $action);
			} elseif (@strstr($action, ':')) {
				$action = explode(':', $action);
			}
		}

		if ($action > 1) {
			$view = !is_string($action) ? @array_pop($action) : $action;
		} else {
			$view = $action;
		}
		$namespace = ($action !== null && !is_string($action)) ? @implode('\\', $action) : null;

		if (($namespace === null || $namespace !== null) && $view != null) {

			$_nsv = ($namespace != null) ? implode(DIRECTORY_SEPARATOR, [$namespace, $view]) : $view;
			$cDir = Dir::app(self::$namespace . $namespace . DIRECTORY_SEPARATOR . 'Cache');

			if ($engine == 'php') {

				$file = Dir::app(self::$namespace . $_nsv . '.php');

				if (file_exists($file)) {

					if ($_ENV['VIEW_CACHE'] == true) {
						if (!_is_dir($cDir)) {
							@mkdir($cDir);
						}
						$file = self::cache($_nsv, $file, $cDir);
					}

					ob_start();
					require_once $file;
					$ob_content = ob_get_contents();
					ob_end_clean();

					if (isset($data['page_title'])) {
						$ob_content = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . (empty($data['page_title']) ? '$2' : $data['page_title'] . ' | $2') . '$3', $ob_content);
					}

					return $return = $ob_content;
				} else {
					throw new Exception('View File Found! | File: ' . $_nsv . '.php');
				}
			} elseif ($engine == 'blade') {
				if (!_is_dir($cDir)) {
					@mkdir($cDir);
				}

				$file = Dir::app(self::$namespace . $_nsv . '.blade.php');

				if (file_exists($file)) {
					$blade = new \Jenssegers\Blade\Blade(Dir::app(self::$namespace . $namespace, $cDir));
					return $blade->make($view, $data)->render();
				} else {
					throw new Exception('Blade View File Found! | File: ' . $_nsv . '.blade.php');
				}
			}
		}
	}

	/**
	 * @param mixed        $action
	 * @param array        $data
	 * @param bool|boolean $layout
	 * @param string       $engine
	 * @param object|null  &$return
	 */
	public static function load($action, array $data=[], bool $layout=false, string $engine='php', object &$return=null)
	{
		self::init();
		$view      = null;
		$namespace = null;

		if (@is_string($action)) {
			if (@strstr($action, '@')) {
				$action = explode('@', $action);
			} elseif (@strstr($action, '/')) {
				$action = explode('/', $action);
			} elseif (@strstr($action, '.')) {
				$action = explode('.', $action);
			} elseif (@strstr($action, ':')) {
				$action = explode(':', $action);
			}
		}

		if ($action > 1) {
			$view = !is_string($action) ? @array_pop($action) : $action;
		} else {
			$view = $action;
		}
		$namespace = ($action !== null && !is_string($action)) ? @implode('\\', $action) : null;

		if (($namespace === null || $namespace !== null) && $view != null) {

			if ($layout == true) {

				$namespacel = (array_key_exists('namespace', $data) ? $data['namespace'] : $namespace . DIRECTORY_SEPARATOR);

				$_file = ($namespacel . 'Layout' . DIRECTORY_SEPARATOR . 'Main.php');
				$file  = Dir::app(self::$namespace . $_file);

				if (file_exists($file)) {
					$content = $view != null ? self::import([$namespace, $view], $data, $engine, $return) : null;

					ob_start();
					require_once $file;
					$ob_content = ob_get_contents();
					ob_end_clean();

					if (isset($data['page_title'])) {
						$ob_content = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . (empty($data['page_title']) ? '$2' : $data['page_title'] . ' | $2') . '$3', $ob_content);
					}

					echo $return = $ob_content;
				} else {
					throw new Exception('Layout File Found! | File: ' . $_file);
				}
			} else {
				echo self::import([$namespace, $view], $data, $engine, $return);
			}
		}
	}

	/**
	 * @param Closure     $callback
	 * @param array       $data
	 * @param object|null &$return
	 */
	public static function layout(Closure $callback, array $data=[], object &$return=null)
	{
		$_vdir = ($_ENV['VIEW_DIR'] != null) ? $_ENV['VIEW_DIR'] : '/App/Http/View/';
		$_ns   = (array_key_exists('namespace', $data) ? $data['namespace'] : $_vdir);
		$_file = ($_ns . 'Layout' . DIRECTORY_SEPARATOR . 'Main.php');

		if (file_exists($file = Dir::app($_file))) {
			$content = call_user_func($callback);

			ob_start();
			require_once $file;
			$ob_content = ob_get_contents();
			ob_end_clean();

			if (isset($data['page_title'])) {
				$ob_content = preg_replace('/(<title>)(.*?)(<\/title>)/i', '$1' . (empty($data['page_title']) ? '$2' : $data['page_title'] . ' | $2') . '$3', $ob_content);
			}

			echo $return = $ob_content;
		} else {
			throw new Exception('Layout File Found! | File: ' . $_file);
		}
	}

	/**
	 * @param string $fileName
	 * @param string $fileContent
	 * @param string $cacheDir
	 */
	private static function cache(string $fileName, string $fileContent, string $cacheDir)
	{
		$file = ($cacheDir . '/' . md5($fileName) . '.php');
		$expire = 120;

		if (!file_exists($file) || (filemtime($file) < (time() - $expire))) {

			$content = file_get_contents($fileContent);
			$signature = "\n<?php /** FILE: " . $fileContent . " - DATE: " . date(DATE_RFC822) ." - EXPIRE: " . date(DATE_RFC822, time() + $expire) . " */ ?>";
			$content = $content . $signature;
			file_put_contents($file, $content, LOCK_EX);
		}
		return $file;
	}
}