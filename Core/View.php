<?php

/**
 * View
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 3.7
 */

namespace BMVC\Core;

use Exception;

final class View
{

	/**
	 * @var string
	 */
	private static $dir = APPDIR . '/Http/View/';

	/**
	 * @param mixed       $action
	 * @param array       $data
	 * @param string      $engine
	 * @param object|null &$return
	 */
	private static function import($action, array $data=[], string $engine='php', object &$return=null)
	{
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
			}
		}

		if ($action > 1) {
			$view = !is_string($action) ? @array_pop($action) : $action;
		} else {
			$view = $action;
		}
		$namespace = ($action !== null && !is_string($action)) ? @implode('\\', $action) : null;

		if (($namespace === null || $namespace !== null) && $view != null) {

			$_nsv     = ($namespace != null) ? implode('/', [$namespace, $view]) : $view;
			$cacheDir = self::$dir . $namespace . '/Cache';

			if (!_dir($cacheDir)) {
				mkdir($cacheDir);
			}

			if ($engine == 'php') {

				if (file_exists($file = self::$dir . $_nsv . '.php')) {

					if (config('general/view/cache') == true) {
						$file = self::cache($_nsv, $file, $cacheDir);
					}

					ob_start();
					require_once $file;
					$ob_content = ob_get_contents();
					ob_end_clean();
					return $return = $ob_content;
				} else {
					throw new Exception('View File Found! | File: ' . $_nsv . '.php');
				}
			} elseif ($engine == 'blade') {

				if (file_exists($file = self::$dir . $_nsv . '.blade.php')) {
					$blade = new \Jenssegers\Blade\Blade(self::$dir . $namespace, $cacheDir);
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
		$view      = null;
		$namespace = null;

		if (@is_string($action)) {
			if (@strstr($action, '@')) {
				$action = explode('@', $action);
			} elseif (@strstr($action, '/')) {
				$action = explode('/', $action);
			} elseif (@strstr($action, '.')) {
				$action = explode('.', $action);
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

				if (file_exists($file = self::$dir . $namespace . '/Layout/Main.php')) {
					$content = $view != null ? self::import([$namespace, $view], $data, $engine, $return) : null;
					require_once $file;
				} else {
					throw new Exception('Layout File Found! | File: ' . $namespace . '/Layout/Main.php');
				}
			} else {
				echo self::import([$namespace, $view], $data, $engine, $return);
			}
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

		if (config('general/view/cacheExpire') != null) {
			$cacheExpire = config('general/view/cacheExpire');
		} else {
			$cacheExpire = 120;
		}

		if (!file_exists($file) || (filemtime($file) < (time() - $cacheExpire))) {

			$content = file_get_contents($fileContent);
			$signature = "\n<?php /** FILE: " . $fileContent . " - DATE: " . date(DATE_RFC822) ." - EXPIRE: " . date(DATE_RFC822, time() + $cacheExpire) . " */ ?>";
			$content = $content . $signature;
			file_put_contents($file, $content, LOCK_EX);
		}
		return $file;
	}
}