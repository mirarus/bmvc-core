<?php

BMVC\Libs\FS::setPath(__DIR__);

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
	if (!empty($url)) $base_url .= $url;

	$base_url = @str_replace(trim(@$_ENV['PUBLIC_DIR'], '/'), null, rtrim($base_url, '/'));
	$base_url = trim($base_url, '/') . '/';

	if ($parse) {
		$base_url = parse_url($base_url);
		if (trim(base_url(), "/") == $base_url) $base_url['path'] = "/";
	}
	return $base_url;
}

/**
 * @param  string|null  $url
 * @param  bool|boolean $parse
 * @return mixed
 */
function app_url(string $url=null, bool $parse=false)
{
	return base_url($url, false, false, $parse);
}

/**
 * @param string|null  $url
 * @param bool|boolean $return
 */
function url(string $url=null, bool $return=false)
{
	if ($url) {
		if ($return == false) {
			return base_url() . $url;
		} else {
			echo base_url() . $url;
		}
	} else {
		if ($return == false) {
			return base_url();
		} else {
			echo base_url();
		}
	}
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
 * @param mixed  $money
 * @param string $type
 * @param string $locale
 */
function _money($money, string $type='currency', string $locale='tr_TR')
{
	if (extension_loaded('intl') && class_exists("NumberFormatter")) {
		if ($type == 'decimal') {
			$fmt = new NumberFormatter($locale, NumberFormatter::DECIMAL);
		} elseif ($type == 'currency') {
			$fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
		}
		if ($type == 'currency') {
			return trim($fmt->format($money), '₺') . '₺';
		} else {
			return $fmt->format($money);
		}
	} else {

		if (!$money) { $money = 0; }

		// if ($locale == 'tr_TR') {
		if ($type == 'decimal') {
			return number_format($money, 2, ",", ".");
		} elseif ($type == 'currency') {
			return number_format($money, 2, ",", ".") . "₺";
		}
		// }
	}
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

/**
 * @param mixed        $data
 * @param bool|boolean $stop
 */
function dump($data, bool $stop=false)
{
	echo "<pre>";
	var_dump($data);
	echo "</pre>";
	if ($stop === true) {
		die();
	}
}

	/** *//* */

/**
 * @param  string $file
 * @return mixed
 */
function _ob(string $file)
{
	ob_start();
	require_once $file;
	$ob_content = ob_get_contents();
	ob_end_clean();
	return $ob_content;
}

/**
 * @param string $file
 */
function ob_template(string $file)
{
	ob_start();
	require_once $file;
	$ob_content = ob_get_contents();
	ob_end_clean();

	$ob_content = preg_replace([
		"/({{ url\(\) }})/i",
		"/({{ url\('(.*?)'\) }})/i",
		"/({{ url\(\"(.*?)\"\) }})/i",
		"/({{ url\((.*?)\) }})/i"
	], url('$2'), $ob_content);

	return $ob_content;
}

/**
 * @param  string|null $class
 * @param  string|null $method
 * @param  array       $params
 * @return mixed
 */
function app(string $class=null, string $method=null, array $params=[])
{
	$std = new \stdClass;
	if ($class) {
		if (isset($method) && !empty($method)) {
			return call_user_func_array([$std->$class, $method], $params);
		} else {
			return $std->$class;
		}
	}
	return $std;
}

/**
 * @param string       $par
 * @param int|integer  $time
 * @param bool|boolean $stop
 */
function redirect(string $par, int $time=0, bool $stop=true)
{
	if ($time == 0) {
		header("Location: " . $par);
	} else {
		header("Refresh: " . $time . "; url=" . $par);
	}
	if ($stop === true) {
		die();
	}
}

/**
 * @param string       $par
 * @param int|integer  $time
 * @param bool|boolean $stop
 */
function refresh(string $par, int $time=0, bool $stop=true)
{
	if ($time == 0) {
		echo "<meta http-equiv='refresh' content='URL=" . $par . "'>";
	} else {
		echo "<meta http-equiv='refresh' content='" . $time . ";URL=" . $par . "'>";
	}
	if ($stop === true) {
		die();
	}
}

/**
 * @param  string|null $url
 * @return bool
 */
function PageCheck(string $url=null): bool
{
	if (@$_GET['url'] == @$url) {
		return true;
	}
	return false;
}

/**
 * @param string       $url
 * @param bool|boolean $return
 */
function ct(string $url, bool $return=true)
{
	if ($return == true) {
		return $url . '?ct=' . time();
	} else {
		echo $url . '?ct=' . time();
	}
}

/**
 * @param  string $par
 * @return string
 */
function html_decode(string $par): string
{
	return htmlspecialchars_decode(html_entity_decode(htmlspecialchars_decode($par, ENT_QUOTES), ENT_QUOTES), ENT_QUOTES);
}

/**
 * @param string $date
 * @param string $format
 */
function datetotime(string $date, string $format='YYYY-MM-DD')
{
	if ($format == 'YYYY-MM-DD') list($year, $month, $day) = explode('-', $date);
	if ($format == 'YYYY/MM/DD') list($year, $month, $day) = explode('/', $date);
	if ($format == 'YYYY.MM.DD') list($year, $month, $day) = explode('.', $date);

	if ($format == 'DD-MM-YYYY') list($day, $month, $year) = explode('-', $date);
	if ($format == 'DD/MM/YYYY') list($day, $month, $year) = explode('/', $date);
	if ($format == 'DD.MM.YYYY') list($day, $month, $year) = explode('.', $date);

	if ($format == 'MM-DD-YYYY') list($month, $day, $year) = explode('-', $date);
	if ($format == 'MM/DD/YYYY') list($month, $day, $year) = explode('/', $date);
	if ($format == 'MM.DD.YYYY') list($month, $day, $year) = explode('.', $date);

	return mktime(0, 0, 0, $month, $day, $year);
}

function resize_image($file, $w, $h, $crop=false)
{
	list($width, $height) = getimagesize($file);
	$r = $width / $height;
	if ($crop) {
		if ($width > $height) {
			$width = ceil($width-($width * abs($r - $w / $h)));
		} else {
			$height = ceil($height-($height * abs($r - $w / $h)));
		}
		$newwidth = $w;
		$newheight = $h;
	} else {
		if ($w/$h > $r) {
			$newwidth = $h*$r;
			$newheight = $h;
		} else {
			$newheight = $w/$r;
			$newwidth = $w;
		}
	}
	$src = imagecreatefromjpeg($file);
	$dst = imagecreatetruecolor($newwidth, $newheight);
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	return $dst;
}