<?php

/**
 * View
 *
 * Mirarus BMVC
 * @package BMVC\Core
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 4.0
 */

namespace BMVC\Core;

use Exception;
use BMVC\Libs\Dir;

final class View
{

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
		$viewDir = ($_ENV['VIEW_DIR'] != null) ? $_ENV['VIEW_DIR'] : '/App/Http/View/';

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
			$cacheDir = Dir::app($viewDir . $namespace . '/Cache');

			if (!_is_dir($cacheDir)) {
				@mkdir($cacheDir);
			}

			if ($engine == 'php') {

				if (file_exists($file = Dir::app($viewDir . $_nsv . '.php'))) {

					if ($_ENV['VIEW_CACHE'] == true) {
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

				if (file_exists($file = Dir::app($viewDir . $_nsv . '.blade.php'))) {
					$blade = new \Jenssegers\Blade\Blade(Dir::app($viewDir . $namespace, $cacheDir));
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
		$viewDir = ($_ENV['VIEW_DIR'] != null) ? $_ENV['VIEW_DIR'] : '/App/Http/View/';

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

				if (file_exists($file = Dir::app($viewDir . $namespace . '/Layout/Main.php'))) {
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