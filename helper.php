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
function xbase_urlx(): string
{
	$host = ((((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);
	$host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_PORT'];

	$url = $host . dirname($_SERVER['PHP_SELF']);
	$url = @str_replace(['Public', 'public'], null, $url);
	return $url;
}

/**
 * @param  string|null  $url
 * @param  bool|boolean $atRoot
 * @param  bool|boolean $atCore
 * @param  bool|boolean $parse
 * @return mixed
 */
function base_url(string $url=null, bool $atRoot=false, bool $atCore=false, bool $parse=false)
{
	if (isset($_SERVER['HTTP_HOST'])) {
		$http = (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $_SERVER['SERVER_PORT'] == 443 || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)) ? 'https' : 'http');
		$hostname = $_SERVER['HTTP_HOST'];
		$dir  = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
		$core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), NULL, PREG_SPLIT_NO_EMPTY);
		$core = $core[0];
		$tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
		$end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
		$base_url = sprintf($tmplt, $http, $hostname, $end);
	} else {
		$base_url = 'http://localhost/';
	}

	$base_url = rtrim($base_url, '/');
	if (!empty($url)) {
		$base_url .= $url;
	}

	$base_url = @str_replace(['/Public', '/public'], null, $base_url);
	$base_url = trim($base_url, '/') . '/';

	if ($parse) {
		if (trim(base_url(), "/") == $base_url) $base_url['path'] = "/";

		$base_url = parse_url($base_url);
		//if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
	}

	return $base_url;
}

/**
 * @param  array        $parsed_url
 * @param  bool|boolean $domain
 * @return string
 */
function unparse_url(array $parsed_url=[], bool $domain=false): string
{
	$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
	$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
	$pass     = ($user || $pass) ? "$pass@" : '';
	$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

	if ($domain == true) {
		return "$scheme$user$pass$host$port";
	} else {
		return "$scheme$user$pass$host$port$path$query$fragment";
	}
}

/**
 * @param  string $addr
 * @return string
 */
function get_host(string $addr): string
{
	$parseUrl = parse_url(trim($addr));
	return trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
}

/**
 * @return string|null
 */
function page_url()
{
	if (isset($_ENV['DIR'])) {
		return trim(str_replace($_ENV['DIR'], null, trim($_SERVER['REQUEST_URI'])), '/');
	} elseif (isset($_GET['url'])) {
		return trim($_GET['url'], '/');
	} elseif (isset($_SERVER['PATH_INFO'])) {
		return trim($_SERVER['PATH_INFO'], '/');
	} else {
		return null;
	}
}

/**
 * @param mixed        $text
 * @param mixed        $message
 * @param bool|boolean $html
 * @param mixed        $title
 * @param string|null  $color
 * @param bool|boolean $stop
 * @param int|integer  $response_code
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
			BMVC\Core\Route::match($method, $pattern, $callback);
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
	if ($file == BMVC\Libs\Dir::base('Helpers' . DIRECTORY_SEPARATOR . 'index.php')) return false;
	require_once $file;
}, glob(BMVC\Libs\Dir::base("Helpers" . DIRECTORY_SEPARATOR . "*.php")));


/**
 * @param  array  $array
 * @return object
 */
function arrayToObject($array): object
{
	$object = new stdClass();
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$value = arrayToObject($value);
			}
			$object->$key = $value;
		}
	}
	return $object;
}

/**
 * @param  object $object
 * @return array
 */
function objectToArray($object): array
{
	$array = [];
	if (is_object($object)) {
		foreach ($object as $key => $value) {
			if (is_object($value)) {
				$value = objectToArray($value);
			}
			$array[$key] = $value;
		}
	}
	return $array;
}

function _controller($action, object &$return=null)
{
	return BMVC\Core\Controller::import($action, $return);
}

function _model($action, object &$return=null)
{
	return BMVC\Core\Model::import($action, $return);
}

function _view($action, array $data=[], string $engine='php', object &$return=null)
{
	return BMVC\Core\View::import($action, $data, $engine, $return);
}

/**
 * @param string       $url
 * @param array        $array
 * @param bool|boolean $data
 * @param bool|boolean $option
 */
function _curl(string $url, array $array=[], bool $data=false, bool $option=false)
{
	if ($option) {
		$domain = base64_encode(get_host(base_url()));
		$ch = curl_init($url . "&domain=" . $domain);
	} else {
		$ch = curl_init($url);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if (is_array($array)) {
		$_array = [];
		foreach ($array as $key => $val) {
			$_array[] = $key . '=' . urlencode($val);
		}

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_array));
	}
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

	$result = curl_exec($ch);
	if (curl_errno($ch) != 0 && empty($result)) {
		$result = false;
	}
	$data = ($data == true ? json_decode($result, true) : $result);
	curl_close($ch);
	return $data;
}

/**
 * @param mixed        $data
 * @param bool|boolean $stop
 */
function pr($data, bool $stop=false)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
	if ($stop === true) {
		die();
	}
}