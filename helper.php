<?php

/**
 * @param  string|null $type
 * @param  string|null $dir
 * @return mixed
 */
function _dir_(string $type=null, string $dir=null)
{
	return BMVC\Libs\Dir::get($type, $dir);
}

function get_404()
{
	BMVC\Core\Route::get_404();
}

/**
 * @param mixed $callback
 */
function set_404($callback)
{
	if (is_nem($callback)) {
		BMVC\Core\Route::set_404($callback);
	}
}

/**
 * @return boolean
 */
function is_cli(): bool
{
	if (defined('STDIN')) {
		return true;
	}
	if (php_sapi_name() === 'cli') {
		return true;
	}
	if (array_key_exists('SHELL', $_ENV)) {
		return true;
	}
	if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
		return true;
	} 
	if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
		return true;
	}
	return false;
}

/**
 * @return string
 */
function base_url(): string
{
	$host = ((((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);
	$host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_PORT'];
	$uri = $host . $_SERVER['REQUEST_URI'];
	$segments = explode('?', $uri, 2);
	$url = $segments[0];
	return $url;
}

/**
 * @return string|null
 */
function page_url()
{
	if (isset($_GET['url'])) {
		return trim($_GET['url'], '/');
	} elseif (isset($_SERVER['PATH_INFO'])) {
		return trim($_SERVER['PATH_INFO'], '/');
	} else {
		return null;
	}
}

/**
 * @param  mixed        $text
 * @param  mixed        $message
 * @param  bool|boolean $html
 * @param  mixed       $title
 * @param  string|null  $color
 * @param  bool|boolean $stop
 * @param  int|integer  $response_code
 */
function ep($text, $message, bool $html=false, $title=null, string $color=null, bool $stop=false, int $response_code=200)
{
	$colors = [
		'danger' => '244 67 54',
		'warning' => '255 235 59',
		'info' => '3 169 244',
		'success' => '76 175 80',
		'primary' => '33 150 243'
	];

	if ($color == null) {
		$color = $colors['primary'];
	} else {
		$color = isset($colors[$color]) ? $colors[$color] : $colors['primary'];
	}

	http_response_code($response_code);
	if (function_exists('mb_internal_encoding')) {
		mb_internal_encoding("UTF-8");
	}
	echo $html == true ? '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /><title>' . ($title ? $title : "System Error") . '</title></head><body>' : null;
	echo '<div style="padding: 15px; border-left: 5px solid rgb(' . $color . ' / 80%); border-top: 5px solid rgb(' . $color . ' / 60%); background: #f8f8f8; margin-bottom: 10px;border-radius: 5px 5px 0 3px;">';
	echo isset($text) && !empty($text) ? '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; font-size: 16px; font-weight: 500; color: black;">' . $text . "</div>" : null;
	echo isset($message) && !empty($message) ? '<div style="margin-top: 15px; font-size: 14px; font-family: Consolas, Monaco, Menlo, Lucida Console, Liberation Mono, DejaVu Sans Mono, Bitstream Vera Sans Mono, Courier New, monospace, sans-serif; color: #ac0e10;">' . $message . "</div>" : null; 
	echo "</div>";
	echo $html == true ? "</body></html>\n" : "\n";
	if ($stop === true) exit();
}

/**
 * @param  string $dir
 * @return bool
 */
function _is_dir(string $dir): bool
{
	if (is_dir($dir) && opendir($dir)) {
		return true;
	} else {
		return false;
	}
}

/**
 * @param  mixed  $method
 * @param  string $pattern
 * @param  mixed  $callback
 */
function _route($method, string $pattern, $callback)
{
	if (is_array($method)) {
		BMVC\Core\Route::match($method, $pattern, $callback);
	} else {
		$method = strtoupper($method);
		$methods = ['GET', 'POST', 'PATCH', 'DELETE', 'PUT', 'OPTIONS'];

		if (in_array($method, $methods)) {
			BMVC\Core\Route::$method($pattern, $callback);
		}
	}
}

/**
 * @param  string       $text
 * @param  mixed       $replace
 * @param  bool|boolean $return
 */
function _lang(string $text, $replace=null, bool $return=true)
{
	if ($return == true) {
		return BMVC\Libs\Lang::___($text, $replace);
	} else {
		BMVC\Libs\Lang::__($text, $replace);
	}
}

array_map(function ($file) {
	if ($file == _dir_('base') . '/Helpers/index.php') return false;
	require_once $file;
}, glob(_dir_('base') . "/Helpers/*.php"));