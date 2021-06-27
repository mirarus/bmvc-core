<?php

/**
 * Validate
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.0
 */

namespace BMVC\Libs;

class Validate
{

	/**
	 * @param  string $method
	 * @param  array  $args
	 * @return mixed
	 */
	public static function call(string $method, $args=[])
	{	
		$methods = get_class_methods(__CLASS__);
		if (in_array($method, $methods)) {
			if (is_array($args)) {
				return call_user_func_array([__CLASS__, $method], $args);
			} else {
				return call_user_func([__CLASS__, $method], $args);
			}
		}
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function is_nem($arg): bool
	{
		return (isset($arg) && !empty($arg));
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function nis_em($arg): bool
	{
		return (!isset($arg) && empty($arg));
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */	
	public static function is($arg): bool
	{
		return isset($arg);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function nis($arg): bool
	{
		return !isset($arg);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function nem($arg): bool
	{
		return !empty($arg);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function em($arg): bool
	{
		return empty($arg);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function integer($arg): bool
	{
		return filter_var($arg, FILTER_VALIDATE_INT);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function float($arg): bool
	{
		return filter_var($arg, FILTER_VALIDATE_FLOAT);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function numeric($arg): bool
	{
		return is_numeric($arg);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function url($arg): bool
	{
		return filter_var($arg, FILTER_VALIDATE_URL);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function ip_adress($arg): bool
	{
		return filter_var($arg, FILTER_VALIDATE_IP);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function ipv4($arg): bool
	{
		return filter_var($arg, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function ipv6($arg): bool
	{
		return filter_var($arg, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function email($arg): bool
	{
		return filter_var($arg, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * @param  mixed $arg
	 * @return mixed
	 */
	public static function sanitize($arg)
	{
		if (!is_array($arg)) {
			return filter_var(trim($arg), FILTER_SANITIZE_STRING);
		}
		foreach ($arg as $key => $val) {
			$arg[$key] = filter_var($val, FILTER_SANITIZE_STRING);
		}
		return $arg;
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function nullable($arg): bool
	{
		return is_array($arg) ? (empty($arg) === true) : (trim($arg) === '');
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function required($arg): bool
	{
		return is_array($arg) ? (empty($arg) === false) : (trim($arg) !== '');
	}

	/**
	 * @param  mixed $arg
	 * @param  mixed $length
	 * @return bool
	 */
	public static function min_len($arg, $length): bool
	{
		return (strlen(trim($arg)) < $length) === false;
	}

	/**
	 * @param  mixed $arg
	 * @param  mixed $length
	 * @return bool
	 */
	public static function max_len($arg, $length): bool
	{
		return (strlen(trim($arg)) > $length) === false;
	}

	/**
	 * @param  mixed $arg
	 * @param  mixed $length
	 * @return bool
	 */
	public static function exact_len($arg, $length): bool
	{
		return (strlen(trim($arg)) == $length) !== false;
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function alpha($arg): bool
	{
		if (!is_string($arg))
			return false;
		return ctype_alpha($arg);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function alpha_num($arg): bool
	{
		return ctype_alnum($arg);
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function alpha_dash($arg): bool
	{
		return (!preg_match("/^([-a-z0-9_-])+$/i", $arg)) ? false : true;
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function alpha_space($arg): bool
	{
		return (!preg_match("/^([A-Za-z0-9- ])+$/i", $arg)) ? false : true;
	}

	/**
	 * @param  mixed $arg
	 * @return bool
	 */
	public static function boolean($arg): bool
	{
		$acceptable = [true, false, 0, 1, '0', '1'];
		return in_array($arg, $acceptable, true);
	}

	/**
	 * @param  mixed $arg
	 * @param  mixed $min
	 * @return bool
	 */
	public static function min_numeric($arg, $min): bool
	{
		return (is_numeric($arg) && is_numeric($min) && $arg >= $min) !== false;
	}

	/**
	 * @param  mixed $arg
	 * @param  mixed $max
	 * @return bool
	 */
	public static function max_numeric($arg, $max): bool
	{
		return (is_numeric($arg) && is_numeric($max) && $arg <= $max) !== false;
	}

	/**
	 * @param  mixed $arg
	 * @param  mixed $part
	 * @return bool
	 */
	public static function contains($arg, $part): bool
	{
		return strpos($arg, $part) !== false;
	}

	/**
	 * @param  mixed $arg
	 * @param  mixed $field
	 * @return bool
	 */
	public static function matches($arg, $field): bool
	{
		return ($arg == $field) !== false;
	}

	/**
	 * @param  mixed $str
	 * @return mixed
	 */
	public static function initials($str)
	{
		$ret = '';
		foreach (explode(' ', $str) as $Word) {
			$ret .= strtoupper($Word[0]);
		}
		return $ret;
	}

	/**
	 * @param  mixed $var
	 * @return bool
	 */
	public static function check($var): bool
	{
		$var = str_replace("\n", " ", $var);
		$var = str_replace(" ", "", $var);
		return (isset($var) && !empty($var) && $var != '');
	}

	/**
	 * @param  mixed $arg
	 * @return mixed
	 */
	public static function cc($arg)
	{
		$number = preg_replace('/\D/', '', $arg);
		if (function_exists('mb_strlen')) {
			$length = mb_strlen($number);
		} else {
			$length = strlen($number);
		}
		$parity = $length % 2;
		$total = 0;
		for ($i=0; $i < $length; $i++) {
			$digit = $number[$i];
			if ($i % 2 == $parity) {
				$digit *= 2;
				if ($digit > 9) {
					$digit -= 9;
				}
			}
			$total += $digit;
		}
		return $total % 10 == 0;
	}
}