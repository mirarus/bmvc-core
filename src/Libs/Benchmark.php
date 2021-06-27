<?php

/**
 * Benchmark
 *
 * Mirarus BMVC
 * @package BMVC\Libs
 * @author  Ali Güçlü (Mirarus) <aliguclutr@gmail.com>
 * @link https://github.com/mirarus/bmvc-core
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version 1.2
 */

namespace BMVC\Libs;

class Benchmark
{

	/**
	 * @param  int|integer $count
	 * @return string
	 */
	private static function test_Math(int $count=140000): string
	{
		$time_start = microtime(true);

		$functions = ["abs", "acos", "asin", "atan", "bindec", "floor", "exp", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt"];

		foreach ($functions as $key => $function) {
			if (!function_exists($function)) unset($functions[$key]);
		}
		for ($i=0; $i < $count; $i++) {
			foreach ($functions as $function) {
				$r = call_user_func_array($function, array($i));
			}
		}
		return number_format(microtime(true) - $time_start, 3);
	}

	/**
	 * @param  int|integer $count
	 * @return string
	 */
	private static function test_String(int $count=130000): string
	{
		$time_start = microtime(true);

		$functions = ["addslashes", "chunk_split", "metaphone", "strip_tags", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "soundex", "ord"];

		foreach ($functions as $key => $function) {
			if (!function_exists($function)) unset($functions[$key]);
		}
		$string = "the quick brown fox jumps over the lazy dog";
		for ($i=0; $i < $count; $i++) {
			foreach ($functions as $function) {
				$r = call_user_func_array($function, array($string));
			}
		}
		return number_format(microtime(true) - $time_start, 3);
	}

	/**
	 * @param  int|integer $count
	 * @return string
	 */
	private static function test_Loop(int $count=19000000): string
	{
		$time_start = microtime(true);

		for ($i = 0; $i < $count; ++$i);
			$i = 0; while($i < $count) ++$i;
		return number_format(microtime(true) - $time_start, 3);
	}

	/**
	 * @param  int|integer $count
	 * @return string
	 */
	private static function test_Conditional(int $count=9000000): string
	{
		$time_start = microtime(true);

		for ($i=0; $i < $count; $i++) {
			if ($i == -1) {
			} elseif ($i == -2) {
			} else if ($i == -3) {
			}
		}
		return number_format(microtime(true) - $time_start, 3);
	}

	/**
	 * @return string
	 */
	public static function memory($text=false): string
	{
		if ($text) {
			return "Memory Usage: " . round(memory_get_usage() / 1024, 2) . " KB";
		} else {
			return round(memory_get_usage() / 1024, 2) . " KB";
		}
	}

	/**
	 * @return string
	 */
	public static function Run(): string
	{
		$total = 0;
		$methods = get_class_methods(__CLASS__);
		$line = str_pad("-", 38, "-");

		$return = "<pre>$line\n| " . str_pad("Start", 12) . " : " . str_pad(date("Y-m-d H:i:s"), 19) . " |\n| " . str_pad("PHP version", 12) . " : " . str_pad(PHP_VERSION, 19) . " |\n| " . str_pad("Platform", 12) . " : " .  str_pad(PHP_OS, 19) . " |\n$line\n";

		foreach ($methods as $method) {
			if (preg_match('/^test_/', $method)) {
				$total += $result = self::$method();
				$return .= '| ' . str_pad(explode('test_', $method)[1], 12) . " : " . str_pad($result ." sec.", 19) . " |\n";
			}
		}

		$return .= $line . "\n| " . str_pad("Total time", 12) . " : " . str_pad($total ." sec.", 19) . " |\n| " . str_pad("Memory Usage", 12) . " : " . str_pad(self::memory(), 19) . " |\n$line </pre>";
		return $return;
	}
}