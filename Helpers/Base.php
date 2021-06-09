<?php

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
 * @param  string      $string
 * @param  int         $start
 * @param  int|null    $length
 * @param  string|null $encoding
 * @return string
 */
function _substr(string $string, int $start, int $length=null, string $encoding=null): string
{
	$encoding = $encoding == null ? "UTF-8" : null;

	if (function_exists('mb_substr')) {
		return mb_substr($string, $start, $length, $encoding);
	} else {
		return substr($string, $start, $length);
	}
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
 * @param  mixed  $data
 * @return boolean
 */
function is_nem($data)
{
	return isset($data) && !empty($data);
}

/**
 * @param  mixed  $data
 * @return boolean
 */
function nis_em($data)
{
	return !isset($data) && empty($data);
}

/**
 * @param  mixed  $data
 * @return boolean
 */
function is($data)
{
	return isset($data);
}

/**
 * @param  mixed  $data
 * @return boolean
 */
function nis($data)
{
	return !isset($data);
}

/**
 * @param  mixed  $data
 * @return boolean
 */
function nem($data)
{
	return !empty($data);
}

/**
 * @param  mixed  $data
 * @return boolean
 */
function em($data)
{
	return empty($data);
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
 * @param string|null $data
 */
function vd(string $data=null)
{
	if ($data == null) {
		if (isset($_REQUEST['vd'])) {
			return $_REQUEST['vd'];
		}
	} else {
		if (isset($_REQUEST['vd'][$data])) {
			return $_REQUEST['vd'][$data];
		}
	}
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
 * @param  string $email
 * @return bool
 */
function valid_email(string $email): bool
{
	return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * @return mixed
 */
function _password()
{
	$args = func_get_args();

	if ($args[0] == 'hash') {
		return password_hash(md5($args[1]), PASSWORD_DEFAULT, ['cost' => @$args[2] ? $args[2] : 12]);
	} elseif ($args[0] == 'verify') {
		return (bool) password_verify(md5($args[1]), $args[2]);
	}
}

/**
 * @param int    $var
 * @param string $pattern
 */
function code_gen(int $var, string $pattern='alpnum') 
{
	$chars = []; 
	if ($pattern == 'alpnum') {
		$chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
	} elseif ($pattern == 'alpha') {
		$chars = array_merge(range('a', 'z'), range('A', 'Z'));
	} elseif ($pattern == 'num') {
		$chars = array_merge(range(0, 9));
	} elseif ($pattern == 'lowercase') {
		$chars = array_merge(range('a', 'z'));
	} elseif ($pattern == 'uppercase') {
		$chars = array_merge(range('A', 'Z'));
	}
	srand((float) microtime() * 100000); 
	shuffle($chars); 
	$result = ''; 
	for ($i=0; $i < $var; $i++) { 
		$result .= $chars[$i]; 
	} 
	unset($chars); 
	return($result); 
}

/**
 * @param int|integer $int
 */
function unique_key(int $int=10)
{
	return hash('sha512', session_id() . bin2hex(openssl_random_pseudo_bytes($int)));
}

/**
 * @param mixed $text
 */
function replace_tr($text)
{
	$text = trim($text);
	$search = array('Ç', 'ç', 'Ğ', 'ğ', 'ı', 'İ', 'Ö', 'ö', 'Ş', 'ş', 'Ü', 'ü');
	$replace = array('C', 'c', 'G', 'g', 'i', 'I', 'O', 'o', 'S', 's', 'U', 'u');
	return str_replace($search, $replace, $text);
}

/**
 * @param string $str
 */
function c_mb_strtoupper(string $str) {
	$str = str_replace('i', 'İ', $str);
	$str = str_replace('ı', 'I', $str);

	if (function_exists('mb_strtoupper')) {
		return @mb_strtoupper($str, 'UTF-8');
	} else {
		return $str;
	}
}

/**
 * @param string $str
 * @param array  $options
 */
function slug(string $str, array $options=[])
{
	if (function_exists('mb_convert_encoding')) {
		$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
	}
	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => true
	);
	$options = array_merge($defaults, $options);
	$char_map = array(
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
		'ß' => 'ss',
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
		'ÿ' => 'y',
		'©' => '(c)',
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y',
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
		'Ž' => 'Z',
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z',
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
		'Ż' => 'Z',
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',
		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z'
	);
	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
	if ($options['transliterate']) {
		$str = str_replace(array_keys($char_map), $char_map, $str);
	}
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	if (function_exists('mb_substr')) {
		$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
	}
	$str = trim($str, $options['delimiter']);
	if (function_exists('mb_strtolower')) {
		$str = $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	}
	return $str;
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
 * @param array       $array
 * @param object|null &$xml
 */
function arrayToXml(array $array, object &$xml=null)
{
	if ($xml == null) {
		$xml = new SimpleXMLElement('<result/>');
	}

	foreach ($array as $key => $value) {
		if (is_array($value)) {
			arrayToXml($value, $xml->addChild($key));
		} else {
			$xml->addChild($key, $value);
		}
	}
	return $xml->asXML();
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