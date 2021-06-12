<?php

/**
 * Lang
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 6.0
 */

namespace BMVC\Libs;

use Exception;
use BMVC\Core\Route;

class Lang
{

	/**
	 * @var string
	 */
	private static $dir;

	/**
	 * @var array
	 */
	private static $langs = [];

	/**
	 * @var string
	 */
	public static $lang = 'en';

	/**
	 * @var boolean
	 */
	public static $active = false;

	/**
	 * @var string
	 */
	private static $current_lang = 'en';

	public function __construct()
	{
		self::$dir = Dir::app('Languages');
		Dir::mk_dir(self::$dir);

		$_lang = $_ENV['LANG'];
		
		if ($_lang != null) {

			if (is_array($_lang)) {

				$func = array_shift($_lang);
				$_lang = call_user_func_array($func, $_lang);

				if ($_lang) {
					self::$current_lang = self::$lang = $_lang;
				}
			} else {
				self::$current_lang = self::$lang = $_lang;
			}
		}

		self::$langs = self::_get_langs();
		self::$current_lang = self::get();

		self::_routes();

		self::$active = true;
	}

	/**
	 * @param  string $lang
	 * @return array
	 */
	public static function get_lang(string $lang): array
	{
		$info = self::_get_lang_info($lang);
		$current = self::$current_lang == $lang ? true : false;
		$name = $current ? $info['name-local'] : $info['name-global'];
		$url = url('lang/set/' . $info['code']);

		if ($info == null) return [];

		return [
			'info' => $info,
			'name' => $name,
			'url' => $url,
			'current' => $current
		];
	}

	/**
	 * @return array
	 */
	public static function get_langs(): array
	{
		$_langs = [];
		foreach (self::$langs as $lang) {
			$_langs[$lang] = self::get_lang($lang);
		}
		return $_langs;
	}

	/**
	 * @return string
	 */
	public static function get(): string
	{
		if (isset($_SESSION[md5('language')])) {
			return $_SESSION[md5('language')];
		}
		$_SESSION[md5('language')] = self::$lang;
		return self::$lang;
	}

	/**
	 * @param string|null $lang
	 */
	public static function set(string $lang=null): void
	{
		if (empty($lang)) {
			$lang = self::$current_lang;
		} if (in_array($lang, self::$langs)) {
			$_SESSION[md5('language')] = $lang;
		}
	}

	/**
	 * @param string $text
	 * @param mixed $replace
	 */
	public static function __(string $text, $replace=null)
	{
		self::_init($text, false, $replace);
	}

	/**
	 * @param string $text
	 * @param mixed $replace
	 */
	public static function ___(string $text, $replace=null)
	{
		return self::_init($text, true, $replace);
	}
	
	#

	private static function _routes(): void
	{
		Route::prefix('lang')::group(function() {

			Route::match(['GET', 'POST'], 'set/{lowercase}', function($lang) {
				self::set($lang);
				if (check_method('GET')) {
					redirect(url());
				}
			});

			Route::match(['GET', 'POST'], 'get/{all}', function($url) {
				$par  = explode('/', $url);
				$text = array_shift($par);

				if (isset($par[0]) && $par[0] == "true") {
					self::___($text, Request::request('replace'));
				} else {
					self::__($text, Request::request('replace'));
				}
			});
		});
	}

	/**
	 * @param string       $text
	 * @param bool|boolean $return
	 * @param mixed  	     $replace
	 */
	private static function _init(string $text, bool $return=true, $replace=null)
	{
		if ($return == true) {
			if ($replace != null) {
				if (is_array($replace)) {
					return vsprintf(self::_get_text($text), $replace);
				} else {
					return sprintf(self::_get_text($text), $replace);
				}
			} else {
				return self::_get_text($text);
			}
		} else {
			if ($replace != null) {
				if (is_array($replace)) {
					vprintf(self::_get_text($text), $replace);
				} else {
					printf(self::_get_text($text), $replace);
				}
			} else {
				echo self::_get_text($text);
			}
		}
	}

	/**
	 * @param string $text
	 */
	private static function _get_text(string $text)
	{
		if (self::$active == false) return false;

		if (self::$current_lang == 'index') return;

		$_config = false;

		if (file_exists($file = Dir::implode([self::$dir, 'config.php']))) {

			$inc_file = include ($file);

			if (is_array($inc_file) && !empty($inc_file)) {

				$_config = true;
				$_lang = $inc_file[self::$current_lang];

				if (isset($_lang)) {
					$_lang = $_lang['langs'];
					if (isset($_lang[$text])) {
						return $_lang[$text];
					} else {
						return $text;
					}
				} else {
					throw new Exception('Language Not Found! | Language: ' . self::$current_lang);
				}
			}
		}

		if ($_config == false) {
			if (file_exists($file = Dir::implode([self::$dir, self::$current_lang . '.php']))) {

				$_lang = [];
				include $file;
				if (isset($_lang[$text])) {
					return $_lang[$text];
				} else {
					$text = ucfirst(str_replace(['-', '_'], ' ', $text));
					return $text;
				}
			} else {
				throw new Exception('Language Not Found! | Language: ' . implode('*', glob(Dir::implode([self::$dir, '*']))));
			}
		}
	}

	private static function _get_langs()
	{
		if (self::$active == false) return false;

		$_config = false;

		if (file_exists($file = Dir::implode([self::$dir, 'config.php']))) {

			$inc_file = include ($file);

			if (is_array($inc_file) && !empty($inc_file)) {

				$_config = true;

				if (array_keys($inc_file) != 'index') {
					return array_keys($inc_file);
				}
			}
		}

		if ($_config == false) {

			$files = [];
			foreach (glob(Dir::implode([self::$dir, '*.php'])) as $file) {
				if ($file != Dir::implode([self::$dir, 'index.php'])) {

					$_lang = [];
					include $file;
					if ($_lang != null) {
						$files[] = str_replace([self::$dir, '.php'], '', $file);
					}
				}
			}
			return $files;
		}
	}

	/**
	 * @param string      $_xlang
	 * @param string|null $par
	 */
	private static function _get_lang_info(string $_xlang, string $par=null)
	{
		if (self::$active == false) return false;

		if ($_xlang == 'index') return;

		$_config = false;
		$_data = [];
		$_lang = [];

		if (file_exists($file = Dir::implode([self::$dir, 'config.php']))) {

			$inc_file = include ($file);

			if (is_array($inc_file) && !empty($inc_file)) {

				$_lang_ = $inc_file[$_xlang];

				if (isset($_lang_) && isset($_lang_['info'])) {

					$_config = true;

					$_lang = $_lang_['langs'];

					$_data = [
						'code' => @$_xlang,
						'name-global' => @$_lang_['info']['name-global'],
						'name-local' => @$_lang_['info']['name-local']
					];
				} else {
					throw new Exception('Language Not Found! | Language: ' . $_xlang);
				}
			}
		}

		if ($_config == false) {
			if (file_exists($file = Dir::implode([self::$dir, $_xlang . '.php']))) {

				include $file;

				$_data = [
					'code' => @$_xlang,
					'name-global' => @$_lang_name[0],
					'name-local' => @$_lang_name[1]
				];
			} else {
				throw new Exception('Language Not Found! | Language: ' . $_xlang);
			}
		}

		if (@$_lang != null && @$_data['code'] != null && @$_data['name-global'] != null && @$_data['name-local'] != null) {
			if ($par != null) {
				return $_data[$par];
			} else {
				return $_data;
			}
		}
	}
}